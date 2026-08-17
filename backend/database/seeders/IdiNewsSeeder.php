<?php

namespace Database\Seeders;

use App\Models\Post;
use App\Models\PostCategory;
use App\Support\PostRoutes;
use Database\Seeders\Concerns\InteractsWithSeedData;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class IdiNewsSeeder extends Seeder
{
    use InteractsWithSeedData;

    private const LEGACY_SAMPLE_CODES = ['NEWS_FACTORY_2026', 'NEWS_MARKET_2026'];

    public function run(): void
    {
        DB::transaction(function (): void {
            $adminId = (int) DB::table('users')
                ->where('email', 'admin@idiseafood.local')
                ->value('id');

            $this->retireLegacySamples();
            $this->configureNewsModule();

            $categoryIds = $this->seedCategories($adminId);
            $tagIds = $this->seedTags();

            foreach ($this->articles() as $index => $article) {
                $mediaId = $article['image']
                    ? $this->seedMedia($adminId, $article['code'], $article['title'], $article['image'])
                    : null;
                $publishedAt = $article['published_at'].' 00:00:00';
                $postId = $this->upsertId('posts', ['code' => $article['code']], [
                    'post_category_id' => $article['category'] ? $categoryIds[$article['category']] : null,
                    'featured_media_id' => $mediaId,
                    'author_id' => $adminId ?: null,
                    'title' => $this->json(['vi' => $article['title']]),
                    'slug' => $this->json(['vi' => $article['slug']]),
                    'excerpt' => $this->json(['vi' => $article['excerpt']]),
                    'content' => $this->json(['vi' => $article['content']]),
                    'seo_title' => $this->json(['vi' => $article['title']]),
                    'meta_description' => $this->json(['vi' => $article['excerpt']]),
                    'og_title' => $this->json(['vi' => $article['title']]),
                    'og_description' => $this->json(['vi' => $article['excerpt']]),
                    'schema_extra' => $this->json([
                        'source_name' => 'IDI Seafood',
                        'source_url' => $article['source_url'],
                        'original_published_at' => $article['published_at'],
                        'read_time' => $article['read_time'],
                        'view_count' => $article['view_count'],
                    ]),
                    'translation_status' => $this->json([
                        'vi' => 'published',
                        'en' => 'draft',
                        'zh' => 'draft',
                    ]),
                    'locale_published_at' => $this->json(['vi' => $publishedAt]),
                    'sort_order' => 130 - $index,
                    'is_featured' => $article['featured'],
                    'is_active' => true,
                    'created_by' => $adminId ?: null,
                    'updated_by' => $adminId ?: null,
                    'deleted_at' => null,
                ]);

                // Keep source chronology intact; upsertId intentionally timestamps new rows with now().
                DB::table('posts')->where('id', $postId)->update(['created_at' => $publishedAt]);
                DB::table('post_tag')->where('post_id', $postId)->delete();
                foreach ($article['tags'] as $tag) {
                    $this->upsertPivot('post_tag', [
                        'post_id' => $postId,
                        'tag_id' => $tagIds[$tag],
                    ]);
                }

                PostRoutes::syncPost(Post::findOrFail($postId));
            }

            foreach ($categoryIds as $categoryId) {
                PostRoutes::syncCategory(PostCategory::findOrFail($categoryId));
            }
        });
    }

    private function retireLegacySamples(): void
    {
        $sampleIds = DB::table('posts')
            ->whereIn('code', self::LEGACY_SAMPLE_CODES)
            ->pluck('id');

        if ($sampleIds->isNotEmpty()) {
            DB::table('posts')->whereIn('id', $sampleIds)->update([
                'is_active' => false,
                'deleted_at' => now(),
                'updated_at' => now(),
            ]);
            DB::table('localized_routes')
                ->where('routeable_type', Post::class)
                ->whereIn('routeable_id', $sampleIds)
                ->delete();
        }

        $legacyCategory = DB::table('post_categories')->where('code', 'COMPANY_NEWS')->first();
        if ($legacyCategory && ! DB::table('posts')
            ->where('post_category_id', $legacyCategory->id)
            ->whereNotIn('code', self::LEGACY_SAMPLE_CODES)
            ->whereNull('deleted_at')
            ->exists()) {
            DB::table('post_categories')->where('id', $legacyCategory->id)->update([
                'is_active' => false,
                'deleted_at' => now(),
                'updated_at' => now(),
            ]);
            DB::table('localized_routes')
                ->where('routeable_type', PostCategory::class)
                ->where('routeable_id', $legacyCategory->id)
                ->delete();
        }
    }

    private function configureNewsModule(): void
    {
        $moduleId = $this->upsertId('modules', ['code' => 'news'], [
            'name' => 'News',
            'module_type' => 'content',
            'page_title' => $this->translations('Tin tức', 'News', '新闻'),
            'description' => $this->translations(
                'Cập nhật hoạt động, thị trường và những dấu ấn mới nhất của I.D.I.',
                'Company, market and latest updates from I.D.I.',
                'I.D.I 的企业动态、市场资讯与最新消息。'
            ),
            'seo_title' => $this->translations(
                'Tin tức I.D.I Seafood',
                'I.D.I Seafood News',
                'I.D.I Seafood 新闻'
            ),
            'meta_description' => $this->translations(
                'Tin tức mới nhất về hoạt động kinh doanh, xuất khẩu, thị trường cá tra và phát triển bền vững của I.D.I.',
                'The latest business, export, pangasius market and sustainability news from I.D.I.',
                'I.D.I 的商业、出口、巴沙鱼市场与可持续发展最新资讯。'
            ),
            'og_title' => $this->translations('Tin tức I.D.I Seafood', 'I.D.I Seafood News', 'I.D.I Seafood 新闻'),
            'og_description' => $this->translations(
                'Theo dõi những thông tin mới nhất từ I.D.I Seafood.',
                'Follow the latest updates from I.D.I Seafood.',
                '关注 I.D.I Seafood 的最新动态。'
            ),
            'is_active' => true,
        ]);

        foreach ([
            'items_per_page' => [12, 'number'],
            'category_items_per_page' => [12, 'number'],
            'featured_limit' => [3, 'number'],
            'related_limit' => [5, 'number'],
            'show_featured_section' => [true, 'boolean'],
            'show_category_navigation' => [true, 'boolean'],
            'show_related_articles' => [true, 'boolean'],
            'show_author' => [true, 'boolean'],
            'show_published_date' => [true, 'boolean'],
            'show_reading_time' => [true, 'boolean'],
            'show_tags' => [true, 'boolean'],
            'show_article_source' => [true, 'boolean'],
            'show_breadcrumb' => [true, 'boolean'],
            'show_social_share' => [true, 'boolean'],
            'show_placeholder_image' => [true, 'boolean'],
            'lazy_load_images' => [true, 'boolean'],
            'meta_keywords' => [[
                'vi' => 'I.D.I, IDI Seafood, tin tức thủy sản, cá tra, xuất khẩu',
                'en' => 'I.D.I, IDI Seafood, seafood news, pangasius, export',
                'zh' => 'I.D.I, IDI Seafood, 水产新闻, 巴沙鱼, 出口',
            ], 'json'],
        ] as $key => [$value, $type]) {
            $this->upsertId('module_settings', [
                'module_id' => $moduleId,
                'setting_key' => $key,
            ], [
                'setting_value' => json_encode(
                    $value,
                    JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR
                ),
                'setting_type' => $type,
            ]);
        }
    }

    /**
     * @return array<string, int>
     */
    private function seedCategories(int $adminId): array
    {
        $definitions = [
            'latest' => [
                'code' => 'LATEST_NEWS',
                'name' => ['Tin mới', 'Latest News', '最新消息'],
                'slug' => ['tin-moi', 'latest-news', 'zuixin-xiaoxi'],
                'description' => ['Thông tin mới nhất từ I.D.I.', 'The latest updates from I.D.I.', 'I.D.I 的最新消息。'],
                'sort' => 30,
            ],
            'activity' => [
                'code' => 'ACTIVITY_NEWS',
                'name' => ['Tin hoạt động', 'Activity News', '企业动态'],
                'slug' => ['tin-tuc-hoat-dong', 'activity-news', 'qiye-dongtai'],
                'description' => ['Hoạt động doanh nghiệp và sự kiện của I.D.I.', 'I.D.I company activities and events.', 'I.D.I 的企业活动与事件。'],
                'sort' => 20,
            ],
            'market' => [
                'code' => 'MARKET_NEWS',
                'name' => ['Tin thị trường', 'Market News', '市场资讯'],
                'slug' => ['tin-tuc-thi-truong', 'market-news', 'shichang-zixun'],
                'description' => ['Tin tức thị trường cá tra và thủy sản.', 'Pangasius and seafood market news.', '巴沙鱼与水产市场资讯。'],
                'sort' => 10,
            ],
        ];

        $ids = [];
        foreach ($definitions as $key => $category) {
            $ids[$key] = $this->upsertId('post_categories', ['code' => $category['code']], [
                'parent_id' => null,
                'featured_media_id' => null,
                'name' => $this->translations(...$category['name']),
                'slug' => $this->translations(...$category['slug']),
                'description' => $this->translations(...$category['description']),
                'seo_title' => $this->translations(...$category['name']),
                'meta_description' => $this->translations(...$category['description']),
                'translation_status' => $this->json([
                    'vi' => 'published',
                    'en' => 'published',
                    'zh' => 'published',
                ]),
                'locale_published_at' => $this->json([
                    'vi' => '2022-01-01 00:00:00',
                    'en' => '2022-01-01 00:00:00',
                    'zh' => '2022-01-01 00:00:00',
                ]),
                'sort_order' => $category['sort'],
                'is_active' => true,
                'created_by' => $adminId ?: null,
                'updated_by' => $adminId ?: null,
                'deleted_at' => null,
            ]);
        }

        return $ids;
    }

    /**
     * @return array<string, int>
     */
    private function seedTags(): array
    {
        $definitions = [
            'idi' => ['I.D.I', 'I.D.I', 'I.D.I'],
            'pangasius' => ['Cá tra', 'Pangasius', '巴沙鱼'],
            'export' => ['Xuất khẩu', 'Export', '出口'],
            'green' => ['Tài chính xanh', 'Green finance', '绿色金融'],
            'us_market' => ['Thị trường Mỹ', 'U.S. market', '美国市场'],
            'event' => ['Sự kiện', 'Events', '活动'],
            'feed' => ['Thức ăn thủy sản', 'Aquafeed', '水产饲料'],
        ];

        $ids = [];
        foreach ($definitions as $key => $names) {
            $slugs = match ($key) {
                'idi' => ['idi', 'idi', 'idi'],
                'pangasius' => ['ca-tra', 'pangasius', 'basha-yu'],
                'export' => ['xuat-khau', 'export', 'chukou'],
                'green' => ['tai-chinh-xanh', 'green-finance', 'lvse-jinrong'],
                'us_market' => ['thi-truong-my', 'us-market', 'meiguo-shichang'],
                'event' => ['su-kien', 'events', 'huodong'],
                default => ['thuc-an-thuy-san', 'aquafeed', 'shuichan-siliao'],
            };
            $ids[$key] = $this->upsertJsonId('tags', 'slug', 'vi', $slugs[0], [
                'name' => $this->translations(...$names),
                'slug' => $this->translations(...$slugs),
                'is_active' => true,
                'deleted_at' => null,
            ]);
        }

        return $ids;
    }

    private function seedMedia(int $adminId, string $code, string $title, string $url): int
    {
        $path = parse_url($url, PHP_URL_PATH) ?: '';
        $fileName = basename($path) ?: strtolower($code).'.jpg';
        $extension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION) ?: 'jpg');
        $mimeType = $extension === 'png' ? 'image/png' : 'image/jpeg';

        return $this->upsertId('media', [
            'disk' => 'public',
            'directory' => 'news/idi-source',
            'file_name' => $fileName,
        ], [
            'folder_id' => DB::table('media_folders')->where('path', '/news')->value('id'),
            'external_url' => $url,
            'original_name' => $fileName,
            'mime_type' => $mimeType,
            'extension' => $extension,
            'file_size' => null,
            'width' => null,
            'height' => null,
            'checksum' => null,
            'title' => $this->json(['vi' => $title]),
            'alt_text' => $this->json(['vi' => $title]),
            'caption' => null,
            'created_by' => $adminId ?: null,
            'deleted_at' => null,
        ]);
    }

    /**
     * Content is editorially condensed from the official IDI articles so the
     * development database stays useful without carrying an opaque HTML dump.
     *
     * @return array<int, array<string, mixed>>
     */
    private function articles(): array
    {
        return [
            [
                'code' => 'IDI_NEWS_20260427_AGM',
                'category' => null,
                'published_at' => '2026-04-27',
                'title' => 'I.D.I TỔ CHỨC ĐẠI HỘI ĐỒNG CỔ ĐÔNG THƯỜNG NIÊN NĂM 2026',
                'slug' => 'i-d-i-to-chuc-dai-hoi-dong-co-dong-thuong-nien-nam-2026',
                'excerpt' => 'Đại hội cổ đông thường niên 2026 của I.D.I nhìn lại kết quả năm qua và thống nhất các trọng tâm phát triển trong giai đoạn mới.',
                'content' => <<<'HTML'
<p>Sáng 25/04/2026, Công ty Cổ phần Đầu tư và Phát triển Đa Quốc Gia I.D.I, mã chứng khoán IDI và là thành viên Tập đoàn Sao Mai, tổ chức Đại hội đồng cổ đông thường niên năm 2026.</p>
<h2>Thống nhất định hướng cho năm 2026</h2>
<p>Đại hội tổng kết chặng đường hoạt động của năm trước và thảo luận những mục tiêu trọng tâm trong bối cảnh ngành thủy sản ngày càng đòi hỏi cao về chất lượng, công nghệ và năng lực cạnh tranh. Các nội dung trong chương trình nhận được sự đồng thuận cao từ cổ đông.</p>
<p>Trên nền tảng hệ thống nhà máy, kho lạnh và dây chuyền chế biến hiện đại, I.D.I tiếp tục tối ưu hiệu quả vận hành, củng cố vị thế trong nhóm doanh nghiệp thủy sản xuất khẩu hàng đầu và theo đuổi tăng trưởng bền vững.</p>
HTML,
                'image' => null,
                'featured' => false,
                'read_time' => 2,
                'view_count' => null,
                'tags' => ['idi', 'event'],
                'source_url' => 'https://idiseafood.com/vn/i-d-i-to-chuc-dai-hoi-dong-co-dong-thuong-nien-nam-2026.html',
            ],
            [
                'code' => 'IDI_NEWS_20250415_GREEN_BOND_AWARD',
                'category' => 'latest',
                'published_at' => '2025-04-15',
                'title' => 'TRÁI PHIẾU XANH CỦA NĂM – KHỐI DOANH NGHIỆP (KHU VỰC CHÂU Á – THÁI BÌNH DƯƠNG): I.D.I - SAO MAI',
                'slug' => 'trai-phieu-xanh-cua-nam-khoi-doanh-nghiep-khu-vuc-chau-a-thai-binh-duong-i-d-i-sao-mai',
                'excerpt' => 'I.D.I - Sao Mai được vinh danh Trái phiếu xanh của năm dành cho doanh nghiệp khu vực Châu Á - Thái Bình Dương.',
                'content' => <<<'HTML'
<p>I.D.I - Sao Mai được trao danh hiệu “Trái phiếu xanh của năm – Khối doanh nghiệp (APAC)” sau thương vụ trái phiếu tiên phong cho lĩnh vực nuôi trồng thủy sản tại Châu Á.</p>
<h2>Nguồn lực mới cho chuỗi giá trị xanh</h2>
<p>Trái phiếu được GuarantCo bảo lãnh thanh toán toàn bộ, giúp I.D.I tiếp cận nguồn vốn dài hạn với mức lãi suất cạnh tranh và thu hút sự quan tâm của các nhà đầu tư tổ chức.</p>
<p>Nguồn vốn huy động được phân bổ theo khung trái phiếu xanh của doanh nghiệp, tập trung vào cơ sở giống, nhà máy chế biến, nâng cao năng lực sản xuất và tăng cường liên kết trong chuỗi giá trị cá tra.</p>
HTML,
                'image' => 'https://idiseafood.com/vnt_upload/news/04_2025/SDAW25_LOGO_WIN_GBOTY_CAPAC.jpg',
                'featured' => true,
                'read_time' => 2,
                'view_count' => 3793,
                'tags' => ['idi', 'green'],
                'source_url' => 'https://www.idiseafood.com/vn/trai-phieu-xanh-cua-nam-khoi-doanh-nghiep-khu-vuc-chau-a-thai-binh-duong-i-d-i-sao-mai.html',
            ],
            [
                'code' => 'IDI_NEWS_20250108_US_FACTORY',
                'category' => 'latest',
                'published_at' => '2025-01-08',
                'title' => 'GẦN 700 TỶ ĐỒNG, IDI XÂY DỰNG NHÀ MÁY CHẾ BIẾN THỦY SẢN HOA KỲ',
                'slug' => 'gan-700-ty-dong-idi-xay-dung-nha-may-che-bien-thuy-san-hoa-ky',
                'excerpt' => 'Nhà máy số 3 tại Khu công nghiệp Sao Mai - Vàm Cống có vốn đầu tư gần 700 tỷ đồng và công suất 120 tấn nguyên liệu mỗi ngày.',
                'content' => <<<'HTML'
<p>Ngày 07/01/2025, I.D.I khởi công Nhà máy Chế biến Thủy sản Hoa Kỳ tại Đồng Tháp với tổng vốn đầu tư gần 700 tỷ đồng. Đây là nhà máy chế biến thứ ba của doanh nghiệp.</p>
<h2>Thiết bị Châu Âu, công suất 120 tấn mỗi ngày</h2>
<p>Công trình rộng gần 24.000 m² tại Khu công nghiệp Sao Mai - Vàm Cống, được thiết kế cho 120 tấn nguyên liệu/ngày và khoảng 20.000 tấn cá fillet đông lạnh/năm. Dây chuyền nhập khẩu từ Châu Âu tích hợp nạp liệu tự động và hệ thống cấp đông hiện đại.</p>
<p>Thời gian xây dựng dự kiến từ 12 đến 14 tháng. Khi đi vào hoạt động, dự án mở rộng đáng kể năng lực chế biến của I.D.I và tạo thêm dư địa phục vụ thị trường Hoa Kỳ, Nam Mỹ.</p>
<h2>Một mắt xích trong hành trình phát triển xanh</h2>
<p>Dự án sử dụng nguồn lực từ lô trái phiếu xanh trị giá 1.000 tỷ đồng, đồng thời nối tiếp thỏa thuận hợp tác với Patagonia Cuisine LLC và lợi thế thuế suất từ kết quả POR19.</p>
HTML,
                'image' => 'https://idiseafood.com/vnt_upload/news/01_2025/Thumb_dang_bai_idi.jpg',
                'featured' => true,
                'read_time' => 3,
                'view_count' => 7480,
                'tags' => ['idi', 'export', 'us_market', 'green'],
                'source_url' => 'https://www.idiseafood.com/vn/gan-700-ty-dong-idi-xay-dung-nha-may-che-bien-thuy-san-hoa-ky.html',
            ],
            [
                'code' => 'IDI_NEWS_20241129_GREEN_BOND',
                'category' => 'latest',
                'published_at' => '2024-11-29',
                'title' => 'LỄ CÔNG BỐ IDI PHÁT HÀNH THÀNH CÔNG TRÁI PHIẾU XANH THỦY SẢN ĐẦU TIÊN Ở CHÂU Á - THÁI BÌNH DƯƠNG',
                'slug' => 'le-cong-bo-idi-phat-hanh-thanh-cong-trai-phieu-xanh-thuy-san-dau-tien-o-chau-a-thai-binh-duong',
                'excerpt' => 'Lô trái phiếu xanh 1.000 tỷ đồng tạo nguồn lực cho nhà máy số 3 và chuỗi giá trị cá tra bền vững của I.D.I.',
                'content' => <<<'HTML'
<p>Ngày 26/11/2024 tại Thành phố Hồ Chí Minh, I.D.I công bố phát hành thành công lô trái phiếu xanh thủy sản đầu tiên tại khu vực Châu Á - Thái Bình Dương.</p>
<h2>Lô trái phiếu dài hạn trị giá 1.000 tỷ đồng</h2>
<p>Lô IDIH2432001 gồm 1.000 trái phiếu, mệnh giá 1 tỷ đồng mỗi trái phiếu, kỳ hạn 8 năm và lãi suất cố định 5,58%/năm. Trái phiếu phát hành ngày 31/10/2024, dự kiến đáo hạn ngày 31/10/2032.</p>
<p><img src="https://www.idiseafood.com/vnt_upload/news/12_2024/4z6079751883555_a57bc70602549e4b733727ea95391927.jpg" alt="Lễ công bố trái phiếu xanh I.D.I"></p>
<p>Nguồn vốn hướng đến nhà máy chế biến số 3 có công suất 120 tấn nguyên liệu/ngày, sản lượng dự kiến 20.000 tấn fillet mỗi năm, cùng các dự án tăng cường tính bền vững của chuỗi cá tra.</p>
<p><img src="https://www.idiseafood.com/vnt_upload/news/12_2024/7z6079816528177_fc866584d6c64b838bff000c86fea749.jpg" alt="Đại diện I.D.I tại sự kiện trái phiếu xanh"></p>
<p>Sau khi dự án hoàn thành, tổng công suất chế biến của I.D.I được kỳ vọng tiến gần mốc 600 tấn nguyên liệu/ngày.</p>
HTML,
                'image' => 'https://idiseafood.com/vnt_upload/news/12_2024/z6122228610437_a23bd95445e225017c694fcf4e5d247f.jpg',
                'featured' => true,
                'read_time' => 3,
                'view_count' => 4083,
                'tags' => ['idi', 'green', 'pangasius'],
                'source_url' => 'https://www.idiseafood.com/vn/le-cong-bo-idi-phat-hanh-thanh-cong-trai-phieu-xanh-thuy-san-dau-tien-o-chau-a-thai-binh-duong.html',
            ],
            [
                'code' => 'IDI_NEWS_20241108_CUSTOMER_CONFERENCE',
                'category' => 'latest',
                'published_at' => '2024-11-08',
                'title' => 'IDI: HỘI NGHỊ KHÁCH HÀNG THỦY SẢN 2024',
                'slug' => 'idi-hoi-nghi-khach-hang-thuy-san-2024',
                'excerpt' => 'Hội nghị khách hàng tại Thanh Hóa kết nối I.D.I, Sao Mai Super Feed cùng các hộ nuôi và đối tác trong chuỗi thủy sản.',
                'content' => <<<'HTML'
<p>Hội nghị khách hàng thủy sản 2024 của I.D.I diễn ra từ ngày 01 đến 04/11 tại LAMORI Resort &amp; Spa, Thanh Hóa, trong bối cảnh nhu cầu cá tra tại nhiều thị trường xuất khẩu có dấu hiệu tích cực.</p>
<h2>Cùng đối tác hoạch định giai đoạn tăng tốc</h2>
<p>I.D.I chia sẻ định hướng thị trường, vùng nguyên liệu và kế hoạch đầu tư với các hộ nuôi, khách hàng. Doanh nghiệp có năng lực khoảng 100.000 tấn cá nguyên liệu/năm; sau 10 tháng năm 2024, lượng nguyên liệu đưa vào sản xuất đạt gần 90% kế hoạch và vùng nuôi liên kết cung cấp khoảng 80%.</p>
<p>Chương trình cũng cập nhật kế hoạch trung tâm giống chất lượng cao, nhà máy chế biến số 3 và vai trò của thức ăn thủy sản Sao Mai Super Feed trong chuỗi giá trị.</p>
<p>Sự kiện là dịp để I.D.I tri ân các đối tác đã đồng hành và cùng thống nhất giải pháp ứng phó với biến động của thị trường tiêu thụ.</p>
HTML,
                'image' => 'https://saomaigroup.com/vnt_upload/news/11_2024/aDSC04488.jpg',
                'featured' => false,
                'read_time' => 3,
                'view_count' => null,
                'tags' => ['idi', 'event', 'feed'],
                'source_url' => 'https://www.idiseafood.com/vn/idi-hoi-nghi-khach-hang-thuy-san-2024.html',
            ],
            [
                'code' => 'IDI_NEWS_20240729_PATAGONIA_PARTNERSHIP',
                'category' => null,
                'published_at' => '2024-07-29',
                'title' => 'KÝ KẾT ĐỐI TÁC CHIẾN LƯỢC PHÁT TRIỂN THỊ TRƯỜNG MỸ VÀ NAM MỸ',
                'slug' => 'ky-ket-doi-tac-chien-luoc-phat-trien-thi-truong-my-va-nam-my',
                'excerpt' => 'I.D.I và Patagonia Cuisine LLC ký kết hợp tác chiến lược nhằm mở rộng thương hiệu và thị phần tại Hoa Kỳ, Nam Mỹ.',
                'content' => <<<'HTML'
<p>Ngày 25/07/2024 tại Long Xuyên, I.D.I Seafood Corporation và Patagonia Cuisine LLC tại Atlanta, bang Georgia ký kết thỏa thuận phát triển thị trường Mỹ và Nam Mỹ.</p>
<h2>Tăng tốc hiện diện tại các thị trường trọng điểm</h2>
<p>Thỏa thuận được thực hiện khi kết quả POR19 tạo thêm lợi thế cho các doanh nghiệp cá tra Việt Nam. I.D.I là một trong sáu doanh nghiệp được hưởng mức thuế chống bán phá giá thấp.</p>
<p>Hai bên thống nhất phối hợp xây dựng thương hiệu I.D.I, phát triển mạng lưới khách hàng và đáp ứng nhu cầu đa dạng về sản phẩm tại Hoa Kỳ, Châu Âu và Nam Mỹ.</p>
<p>Cùng với năng lực khoảng 100.000 tấn nguyên liệu/năm, sản phẩm hiện diện tại 50 quốc gia và kế hoạch đầu tư nhà máy số 3, hợp tác này bổ sung một trụ cột cho chiến lược xuất khẩu dài hạn của I.D.I.</p>
HTML,
                'image' => null,
                'featured' => false,
                'read_time' => 3,
                'view_count' => null,
                'tags' => ['idi', 'export', 'us_market'],
                'source_url' => 'https://www.idiseafood.com/vn/ky-ket-doi-tac-chien-luoc-phat-trien-thi-truong-My-va-Nam-my.html',
            ],
            [
                'code' => 'IDI_NEWS_20240606_THAIFEX',
                'category' => null,
                'published_at' => '2024-06-06',
                'title' => 'I.D.I CÓ MẶT THAM GIA HỘI CHỢ F&B THAIFEX – ANUGA ASIA 2024 TẠI THÁI LAN',
                'slug' => 'i-d-i-co-mat-tham-gia-hoi-cho-f-b-thaifex-1717682545',
                'excerpt' => 'I.D.I giới thiệu sản phẩm và kết nối khách hàng quốc tế tại THAIFEX - Anuga Asia 2024 ở Bangkok, Thái Lan.',
                'content' => <<<'HTML'
<p>THAIFEX - Anuga Asia 2024 diễn ra tại Trung tâm triển lãm IMPACT Muang Thong Thani ở Bangkok, quy tụ khoảng 6.000 gian hàng và 3.000 doanh nghiệp trong khu vực cùng thế giới.</p>
<h2>Đưa sản phẩm I.D.I đến gần hơn với khách hàng quốc tế</h2>
<p>Tại gian hàng của mình, I.D.I đón tiếp các đoàn khách đến tham quan, trải nghiệm và tìm hiểu danh mục sản phẩm thủy sản tiêu biểu đã được doanh nghiệp nghiên cứu, phát triển trong nhiều năm.</p>
<p>Hoạt động xúc tiến thương mại giúp I.D.I tìm kiếm thêm đối tác tiềm năng, mở rộng mạng lưới khách hàng và quảng bá sản phẩm thủy sản Việt Nam tại thị trường quốc tế.</p>
HTML,
                'image' => null,
                'featured' => false,
                'read_time' => 2,
                'view_count' => null,
                'tags' => ['idi', 'event', 'export'],
                'source_url' => 'https://idiseafood.com/vn/i-d-i-co-mat-tham-gia-hoi-cho-f-b-thaifex-1717682545.html',
            ],
            [
                'code' => 'IDI_NEWS_20240510_NORTH_AMERICA_EXPO',
                'category' => 'activity',
                'published_at' => '2024-05-10',
                'title' => 'I.D.I TẠI HỘI CHỢ TRIỂN LÃM THỦY HẢI SẢN BẮC MỸ 2024',
                'slug' => 'i-d-i-tai-hoi-cho-trien-lam-thuy-hai-san-bac-my-2024',
                'excerpt' => 'I.D.I tham gia gian hàng Việt Nam tại Seafood Expo North America 2024 để tiếp cận khách hàng và mở rộng mạng lưới đối tác.',
                'content' => <<<'HTML'
<p>Seafood Expo North America 2024 khai mạc tại Trung tâm Hội chợ và Triển lãm Boston, Massachusetts. Đây là sự kiện thương mại hải sản thường niên lớn nhất khu vực Bắc Mỹ.</p>
<h2>Kết nối trực tiếp với thị trường Hoa Kỳ</h2>
<p>I.D.I tham gia khu gian hàng chung cùng VASEP và các doanh nghiệp Việt Nam tại các vị trí 1033 và 1041 trong thời gian diễn ra hội chợ từ ngày 10 đến 12/03/2024.</p>
<p>Sự kiện tạo điều kiện để doanh nghiệp gặp gỡ nhà cung cấp, khách hàng tiềm năng, mở rộng đối tác và cập nhật xu hướng sản phẩm trong một thị trường nhiều tiềm năng nhưng có mức cạnh tranh cao.</p>
HTML,
                'image' => null,
                'featured' => false,
                'read_time' => 2,
                'view_count' => null,
                'tags' => ['idi', 'event', 'us_market'],
                'source_url' => 'https://www.idiseafood.com/vn/i-d-i-tai-hoi-cho-trien-lam-thuy-hai-san-bac-my-2024.html',
            ],
            [
                'code' => 'IDI_NEWS_20240404_POR19',
                'category' => 'activity',
                'published_at' => '2024-04-04',
                'title' => 'TIN VUI I.D.I ĐƯỢC ÁP THUẾ THẤP KHI XUẤT KHẨU CÁ TRA VÀO MỸ',
                'slug' => 'tin-vui-did-duoc-ap-thue-thap-khi-xuat-khau-ca-vao-my',
                'excerpt' => 'Kết quả cuối cùng của POR19 áp mức thuế 0,18 USD/kg cho I.D.I, cải thiện lợi thế cạnh tranh của cá tra xuất khẩu sang Hoa Kỳ.',
                'content' => <<<'HTML'
<p>Bộ Thương mại Hoa Kỳ công bố kết quả cuối cùng của đợt rà soát thuế chống bán phá giá lần thứ 19 đối với cá tra phi lê đông lạnh nhập khẩu từ Việt Nam.</p>
<h2>I.D.I được áp mức thuế 0,18 USD/kg</h2>
<p>Theo kết quả POR19, I.D.I được áp mức thuế chống bán phá giá 0,18 USD/kg. Mức thuế cuối cùng đã giảm đáng kể so với kỳ rà soát trước, qua đó tăng lợi thế cạnh tranh cho sản phẩm của doanh nghiệp.</p>
<p>Tín hiệu này xuất hiện khi nhu cầu thủy sản và cá tra tại Hoa Kỳ, Liên minh Châu Âu có dấu hiệu phục hồi. Thị trường Mỹ tiếp tục giữ vai trò quan trọng đối với xuất khẩu cá tra Việt Nam.</p>
<p>I.D.I đồng thời chuẩn bị mở rộng năng lực chế biến, đa dạng hóa thị trường và nâng cao tiêu chuẩn sản phẩm để tận dụng cơ hội mới.</p>
HTML,
                'image' => null,
                'featured' => false,
                'read_time' => 2,
                'view_count' => null,
                'tags' => ['idi', 'export', 'us_market'],
                'source_url' => 'https://www.idiseafood.com/vn/tin-vui-did-duoc-ap-thue-thap-khi-xuat-khau-ca-vao-my.html',
            ],
            [
                'code' => 'IDI_NEWS_20230706_PROFIT_OUTLOOK',
                'category' => 'latest',
                'published_at' => '2023-07-06',
                'title' => 'LỢI NHUẬN DOANH NGHIỆP CÁ TRA SẼ CẢI THIỆN TRONG NỬA CUỐI NĂM 2023',
                'slug' => 'loi-nhuan-doanh-nghiep-ca-tra-se-cai-thien-trong-nua-cuoi-nam-2023',
                'excerpt' => 'Triển vọng đơn hàng và chi phí đầu vào tạo kỳ vọng kết quả của các doanh nghiệp cá tra sẽ tích cực hơn trong nửa cuối năm 2023.',
                'content' => <<<'HTML'
<p>Sau giai đoạn thị trường tiêu thụ chậm lại, ngành cá tra bước vào nửa cuối năm 2023 với kỳ vọng nhu cầu tại các thị trường lớn dần cải thiện.</p>
<h2>Kỳ vọng phục hồi theo chu kỳ đơn hàng</h2>
<p>Việc điều chỉnh hàng tồn kho của khách hàng, diễn biến chi phí nguyên liệu và hoạt động mở rộng thị trường được xem là những yếu tố có thể hỗ trợ biên lợi nhuận của doanh nghiệp.</p>
<p>Các doanh nghiệp chủ động vùng nuôi, công suất chế biến và mạng lưới xuất khẩu như I.D.I có thêm điều kiện để thích ứng khi đơn hàng phục hồi.</p>
HTML,
                'image' => null,
                'featured' => false,
                'read_time' => 2,
                'view_count' => null,
                'tags' => ['pangasius', 'export'],
                'source_url' => 'https://www.idiseafood.com/vn/loi-nhuan-doanh-nghiep-ca-tra-se-cai-thien-trong-nua-cuoi-nam-2023.html',
            ],
            [
                'code' => 'IDI_NEWS_20230706_SUPER_FEED',
                'category' => 'latest',
                'published_at' => '2023-07-06',
                'title' => 'SAO MAI SUPER FEED- THƯƠNG HIỆU NỔI TIẾNG NGÀNH HÀNG THỨC ĂN THỦY SẢN',
                'slug' => 'sao-mai-super-feed-thuong-hieu-noi-tieng-nganh-hang-thuc-an-thuy-san',
                'excerpt' => 'Sao Mai Super Feed phát triển thức ăn cá tra theo công nghệ Châu Âu, tối ưu dinh dưỡng và hiệu quả cho người nuôi.',
                'content' => <<<'HTML'
<p>Sao Mai Super Feed là thương hiệu thức ăn thủy sản nổi bật, đặc biệt trong phân khúc cá tra, với định hướng cân bằng chất lượng, chi phí và dịch vụ hỗ trợ người nuôi.</p>
<h2>Công nghệ và quản trị cho hiệu quả chăn nuôi</h2>
<p>Sản phẩm được phát triển trên dây chuyền công nghệ Châu Âu, chú trọng dinh dưỡng và chỉ số chuyển đổi thức ăn. Doanh nghiệp áp dụng tư duy LEAN để giảm lãng phí, chuẩn hóa quy trình và nâng cao hiệu quả sản xuất.</p>
<p>Hệ thống phân phối rộng cùng các tiêu chuẩn HACCP, ISO, GlobalG.A.P., ASC và BAP góp phần củng cố độ tin cậy của sản phẩm trong chuỗi giá trị cá tra.</p>
<p>Song song với thị trường trong nước, Sao Mai Super Feed tiếp tục đầu tư nghiên cứu, đào tạo nhân sự và phát triển quan hệ với đối tác quốc tế.</p>
HTML,
                'image' => null,
                'featured' => false,
                'read_time' => 3,
                'view_count' => null,
                'tags' => ['pangasius', 'feed'],
                'source_url' => 'https://www.idiseafood.com/vn/sao-mai-super-feed-thuong-hieu-noi-tieng-nganh-hang-thuc-an-thuy-san.html',
            ],
            [
                'code' => 'IDI_NEWS_20230705_INTERNAL_STRENGTH',
                'category' => 'latest',
                'published_at' => '2023-07-05',
                'title' => 'KHẲNG ĐỊNH THƯƠNG HIỆU TỪ SỨC MẠNH NỘI SINH',
                'slug' => 'khang-dinh-thuong-hieu-tu-suc-manh-noi-sinh',
                'excerpt' => 'Nhân lực, công nghệ và sản phẩm là ba nền tảng giúp Sao Mai Super Feed xây dựng thương hiệu bền vững.',
                'content' => <<<'HTML'
<p>Một thương hiệu mạnh cần được xây dựng từ năng lực bên trong thay vì chỉ dựa vào hoạt động quảng bá ngắn hạn.</p>
<h2>Ba trụ cột nhân lực, công nghệ và sản phẩm</h2>
<p>Sao Mai Super Feed đầu tư vào đội ngũ chuyên môn, nghiên cứu công nghệ và cải tiến sản phẩm. Hệ thống ERP, kiểm soát chất lượng tự động và dây chuyền thông minh giúp nâng năng suất, giảm lãng phí và ổn định chất lượng.</p>
<p>Hoạt động nghiên cứu phát triển liên tục cập nhật công thức thức ăn theo nhu cầu của người nuôi và các tiêu chuẩn mới của thị trường.</p>
<p>Từ nền tảng nội lực đó, doanh nghiệp hướng đến mở rộng xuất khẩu và nâng vị thế của thương hiệu thức ăn thủy sản Việt Nam.</p>
HTML,
                'image' => null,
                'featured' => false,
                'read_time' => 2,
                'view_count' => null,
                'tags' => ['feed'],
                'source_url' => 'https://www.idiseafood.com/vn/khang-dinh-thuong-hieu-tu-suc-manh-noi-sinh.html',
            ],
            [
                'code' => 'IDI_NEWS_20220914_UK_MARKET',
                'category' => 'market',
                'published_at' => '2022-09-14',
                'title' => 'CÁ TRA VIỆT NAM XUẤT KHẨU SANG ANH TĂNG ĐỘT BIẾN',
                'slug' => 'ca-tra-viet-nam-xuat-khau-sang-anh-tang-dot-bien',
                'excerpt' => 'Cá tra Việt Nam tăng hiện diện tại Anh nhờ mức giá phù hợp và nhu cầu thay thế nguồn cá thịt trắng thiếu hụt.',
                'content' => <<<'HTML'
<p>Trong tháng 08/2022, lượng cá tra Việt Nam xuất khẩu sang thị trường Anh ghi nhận mức tăng đáng chú ý.</p>
<h2>Cá tra trở thành lựa chọn thay thế phù hợp</h2>
<p>Lạm phát cao làm người tiêu dùng Anh thận trọng hơn với các loại thực phẩm có giá cao. Cá tra duy trì lợi thế nhờ mức giá hợp lý và nguồn cung ổn định.</p>
<p>Việc thị trường thiếu một phần nguồn cá thịt trắng từ Nga cũng mở thêm cơ hội cho sản phẩm cá tra Việt Nam trong bán lẻ và dịch vụ thực phẩm.</p>
HTML,
                'image' => null,
                'featured' => false,
                'read_time' => 2,
                'view_count' => null,
                'tags' => ['pangasius', 'export'],
                'source_url' => 'https://www.idiseafood.com/vn/ca-tra-viet-nam-xuat-khau-sang-anh-tang-dot-bien.html',
            ],
        ];
    }
}
