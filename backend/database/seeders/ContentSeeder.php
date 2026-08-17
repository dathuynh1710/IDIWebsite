<?php

namespace Database\Seeders;

use Database\Seeders\Concerns\InteractsWithSeedData;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ContentSeeder extends Seeder
{
    use InteractsWithSeedData;

    public function run(): void
    {
        $adminId = (int) DB::table('users')->where('email', 'admin@idiseafood.local')->value('id');
        $newsMediaId = (int) DB::table('media')->where('file_name', 'factory-news.jpg')->value('id');

        $categoryId = $this->seedPostCategory($adminId, $newsMediaId);
        $postIds = $this->seedPosts($adminId, $categoryId, $newsMediaId);
        $tagIds = $this->seedTags();
        $this->seedPostTags($postIds, $tagIds);
        $this->seedPages($adminId, $newsMediaId);
    }

    private function seedPostCategory(int $adminId, int $mediaId): int
    {
        return $this->upsertId('post_categories', ['code' => 'COMPANY_NEWS'], [
            'parent_id' => null,
            'featured_media_id' => $mediaId,
            'name' => $this->translations('Tin doanh nghiệp', 'Company news', '公司新闻'),
            'slug' => $this->translations('tin-doanh-nghiep', 'company-news', 'gongsi-xinwen'),
            'description' => $this->translations(
                'Thông tin mới nhất từ IDI Seafood.',
                'Latest updates from IDI Seafood.',
                'IDI Seafood 的最新动态。'
            ),
            'seo_title' => null,
            'meta_description' => null,
            'translation_status' => $this->publishedStatus(),
            'locale_published_at' => $this->publishedDates(),
            'sort_order' => 0,
            'is_active' => true,
            'created_by' => $adminId,
            'updated_by' => $adminId,
            'deleted_at' => null,
        ]);
    }

    /**
     * @return array<string, int>
     */
    private function seedPosts(int $adminId, int $categoryId, int $mediaId): array
    {
        return [
            'factory' => $this->upsertId('posts', ['code' => 'NEWS_FACTORY_2026'], [
                'post_category_id' => $categoryId,
                'featured_media_id' => $mediaId,
                'author_id' => $adminId,
                'title' => $this->translations(
                    'IDI Seafood nâng cấp dây chuyền chế biến',
                    'IDI Seafood upgrades its processing line',
                    'IDI Seafood 升级加工生产线'
                ),
                'slug' => $this->translations(
                    'idi-seafood-nang-cap-day-chuyen-che-bien',
                    'idi-seafood-upgrades-processing-line',
                    'idi-seafood-shengji-jiagongxian'
                ),
                'excerpt' => $this->translations(
                    'Dây chuyền mới giúp nâng cao năng suất và chất lượng sản phẩm.',
                    'The new line improves productivity and product quality.',
                    '新生产线提升了生产效率和产品质量。'
                ),
                'content' => $this->translations(
                    '<p>IDI Seafood tiếp tục đầu tư công nghệ nhằm phục vụ khách hàng toàn cầu.</p>',
                    '<p>IDI Seafood continues investing in technology to serve global customers.</p>',
                    '<p>IDI Seafood 持续投资技术，为全球客户提供服务。</p>'
                ),
                'seo_title' => null,
                'meta_description' => null,
                'og_title' => null,
                'og_description' => null,
                'schema_extra' => null,
                'translation_status' => $this->publishedStatus(),
                'locale_published_at' => $this->publishedDates(),
                'sort_order' => 0,
                'is_featured' => true,
                'is_active' => true,
                'created_by' => $adminId,
                'updated_by' => $adminId,
                'deleted_at' => null,
            ]),
            'market' => $this->upsertId('posts', ['code' => 'NEWS_MARKET_2026'], [
                'post_category_id' => $categoryId,
                'featured_media_id' => $mediaId,
                'author_id' => $adminId,
                'title' => $this->translations(
                    'Cá tra Việt Nam mở rộng thị trường quốc tế',
                    'Vietnamese pangasius expands internationally',
                    '越南巴沙鱼拓展国际市场'
                ),
                'slug' => $this->translations(
                    'ca-tra-viet-nam-mo-rong-thi-truong',
                    'vietnamese-pangasius-expands-internationally',
                    'yuenan-basha-yu-tuozhan-guoji-shichang'
                ),
                'excerpt' => $this->translations(
                    'Nhu cầu sản phẩm thủy sản bền vững tiếp tục tăng.',
                    'Demand for sustainable seafood continues to grow.',
                    '可持续水产品需求持续增长。'
                ),
                'content' => $this->translations(
                    '<p>IDI Seafood phát triển sản phẩm phù hợp với từng thị trường xuất khẩu.</p>',
                    '<p>IDI Seafood develops products for the needs of each export market.</p>',
                    '<p>IDI Seafood 针对不同出口市场开发产品。</p>'
                ),
                'seo_title' => null,
                'meta_description' => null,
                'og_title' => null,
                'og_description' => null,
                'schema_extra' => null,
                'translation_status' => $this->publishedStatus(),
                'locale_published_at' => $this->publishedDates(),
                'sort_order' => 1,
                'is_featured' => false,
                'is_active' => true,
                'created_by' => $adminId,
                'updated_by' => $adminId,
                'deleted_at' => null,
            ]),
        ];
    }

    /**
     * @return array<string, int>
     */
    private function seedTags(): array
    {
        return [
            'pangasius' => $this->upsertJsonId('tags', 'slug', 'vi', 'ca-tra', [
                'name' => $this->translations('Cá tra', 'Pangasius', '巴沙鱼'),
                'slug' => $this->translations('ca-tra', 'pangasius', 'basha-yu'),
                'is_active' => true,
                'deleted_at' => null,
            ]),
            'export' => $this->upsertJsonId('tags', 'slug', 'vi', 'xuat-khau', [
                'name' => $this->translations('Xuất khẩu', 'Export', '出口'),
                'slug' => $this->translations('xuat-khau', 'export', 'chukou'),
                'is_active' => true,
                'deleted_at' => null,
            ]),
            'sustainability' => $this->upsertJsonId('tags', 'slug', 'vi', 'ben-vung', [
                'name' => $this->translations('Bền vững', 'Sustainability', '可持续发展'),
                'slug' => $this->translations('ben-vung', 'sustainability', 'kechixu-fazhan'),
                'is_active' => true,
                'deleted_at' => null,
            ]),
        ];
    }

    /**
     * @param  array<string, int>  $posts
     * @param  array<string, int>  $tags
     */
    private function seedPostTags(array $posts, array $tags): void
    {
        foreach ([
            [$posts['factory'], $tags['pangasius']],
            [$posts['factory'], $tags['sustainability']],
            [$posts['market'], $tags['pangasius']],
            [$posts['market'], $tags['export']],
        ] as [$postId, $tagId]) {
            DB::table('post_tag')->updateOrInsert(
                ['post_id' => $postId, 'tag_id' => $tagId],
                ['created_at' => now(), 'updated_at' => now()]
            );
        }
    }

    private function seedPages(int $adminId, int $mediaId): void
    {
        $legacyAboutId = DB::table('pages')
            ->where('code', 'ABOUT')
            ->where('slug->vi', 've-chung-toi')
            ->value('id');
        if ($legacyAboutId && ! DB::table('pages')->where('code', 'ABOUT_MESSAGE')->exists()) {
            DB::table('pages')->where('id', $legacyAboutId)->update(['code' => 'ABOUT_MESSAGE']);
        }

        $messageMediaId = $this->upsertId('media', [
            'directory' => 'about',
            'file_name' => 'avatar.jpg',
        ], [
            'folder_id' => null,
            'disk' => 'public',
            'external_url' => 'https://idiseafood.com/vnt_upload/about/avatar.jpg',
            'original_name' => 'avatar.jpg',
            'mime_type' => 'image/jpeg',
            'extension' => 'jpg',
            'file_size' => null,
            'width' => null,
            'height' => null,
            'checksum' => null,
            'title' => $this->json(['vi' => 'Ông Lê Văn Chung']),
            'alt_text' => $this->json(['vi' => 'Ông Lê Văn Chung - Cố vấn điều hành IDI']),
            'caption' => null,
            'created_by' => $adminId,
            'deleted_at' => null,
        ]);

        $messageId = $this->upsertId('pages', ['code' => 'ABOUT_MESSAGE'], [
            'parent_id' => null,
            'featured_media_id' => $messageMediaId,
            'template' => 'about',
            'title' => $this->json(['vi' => 'Thông điệp của công ty']),
            'slug' => $this->json(['vi' => 'thong-diep-cua-cong-ty']),
            'summary' => $this->json(['vi' => 'Tại I.D.I - Chúng tôi tâm niệm rằng việc phát triển bền vững chính là nền tảng để xây dựng công ty. Chúng tôi vẫn và sẽ tiếp tục tạo nên những giá trị đa dạng cho con người, đồng thời thích nghi với các thay đổi của xã hội và môi trường.']),
            'content' => $this->json(['vi' => <<<'HTML'
<h2>Ông. Lê Văn Chung</h2>
<p><em>Cố vấn điều hành</em></p>
<p>Trong Thế Giới phát triển nhanh chóng như vậy, sứ mệnh của I.D.I vẫn không thay đổi và sẽ tiếp tục đi sát với những mục tiêu đề ra ban đầu: chúng tôi muốn góp phần nuôi dưỡng một xã hội lành mạnh bằng cách cung cấp các thực phẩm ngon, an toàn và tốt cho sức khỏe. Triết lý này là tiền đề cho những nỗ lực không mệt mỏi của chúng tôi trong hơn 10 năm qua, để trở thành người bạn đồng hành không thể thiếu trong xã hội.</p>
<p>Chúng tôi là những người may mắn được hưởng nguồn lợi lớn từ sông Mekong và đồng bằng Sông Cửu Long. Điều này đồng nghĩa với việc IDI cũng có trách nhiệm trong việc bảo vệ môi trường tự nhiên này. Bởi vì nguồn tài nguyên nuôi trồng thủy sản trên thế giới không phải là vô hạn, nên đây là thách thức để chúng tôi tìm ra cách sống hài hòa với nhiên thiên. IDI phải tạo ra những bước tiến dài hướng đến sự phát triển bền vững của môi trường, bảo vệ nguồn nước sạch và hệ sinh thái tự nhiên để thế hệ tương lai được kế thừa món quà vô giá đó. Lời cam kết này là kim chỉ nam điều hướng công ty vận hành qua từng ngày và ảnh hướng lớn đến mọi quyết định của I.D.I. Cùng lúc đó, chúng tôi cố gắng thúc đẩy quá trình phát triển nội tại bằng cách ban hành nhiều chính sách hỗ trợ nhân viên, tạo sự bình đẳng, cơ hội phát triển trong công việc và triển khai các chương trình phát triển năng lực lãnh đạo trong công ty.</p>
<p>Chúng tôi hiểu rằng I.D.I không nằm ngoài guồng quay của các thay đổi Kinh tế - Xã hội, dẫn đến việc dự đoán các xu hướng phát triển trong tương lai mang tính thử thách hơn. Tuy nhiên, lối sống tích cực và tư duy đa chiều sẽ mở ra các cơ hội mới nhờ quá trình tích lũy và tận dụng kinh nghiệm thực tiễn của chúng tôi. Tất cả những điểm mạnh trên sẽ là yếu tố bảo chứng cho một I.D.I còn phát triển mạnh mẽ và đi xa hơn nữa trên con đường đã chọn.</p>
HTML]),
            'seo_title' => $this->json(['vi' => 'Thông điệp của công ty']),
            'meta_description' => $this->json(['vi' => 'Thông điệp của Cố vấn điều hành Lê Văn Chung về phát triển bền vững, con người, xã hội và môi trường tại I.D.I.']),
            'meta_keywords' => $this->json(['vi' => 'I.D.I, IDI Seafood, thông điệp công ty, phát triển bền vững']),
            'og_title' => null,
            'og_description' => null,
            'translation_status' => $this->json(['vi' => 'published']),
            'locale_published_at' => $this->json(['vi' => now()->toIso8601String()]),
            'sort_order' => 0,
            'is_active' => true,
            'created_by' => $adminId,
            'updated_by' => $adminId,
            'deleted_at' => null,
        ]);

        $this->upsertId('pages', ['code' => 'ABOUT_HISTORY'], [
            'parent_id' => null,
            'featured_media_id' => null,
            'template' => 'about-history',
            'title' => $this->json(['vi' => 'Lịch sử hình thành và đổi mới']),
            'slug' => $this->json(['vi' => 'lich-su-doi-moi']),
            'summary' => $this->json(['vi' => 'Từ khi I.D.I bắt đầu cuộc hành trình của mình vào năm 2008, công ty đã liên tục trau dồi và chuyển đổi trên nhiều phương diện.']),
            'content' => $this->json(['vi' => <<<'HTML'
<p>Với khởi đầu khiêm tốn của mình, khi những người tiên phong nhận ra cơ hội tuyệt vời của loài cá tra sống ở Việt Nam, I.D.I đã phát triển để trở thành công ty hàng đầu trong lĩnh vực này.</p>
<p>I.D.I gặt hái được thành công của mình nhờ vào việc phát triển và tuân theo một chiến lược toàn diện, gắn kết các mục tiêu và nhiệm vụ với các tôn chỉ hoạt động vì Hành tinh, Con người và Sản phẩm.</p>
<p>Công ty sỡ hữu một tầm nhìn được xác định và đặt ra rõ ràng trong việc phát triển bền vững, thương hiệu lớn mạnh trên thế giới và kinh nghiệm lãnh đạo dày dặn. Chúng tôi cam kết xây dựng một tổ chức tuyệt vời, đồng thời lưu lại các giá trị lâu dài cho thế giới nói chung và ngành công nghiệp thực phẩm nói riêng.</p>
<h2>Các cột mốc phát triển</h2>
<h3>2008</h3>
<p>Một số người tiên phong đã nhận ra tiềm năng của loài cá tra và nhà máy chế biến đầu tiên được xây dựng để giới thiệu các sản phẩm chất lượng cao trên toàn thế giới.</p>
<h3>2010</h3>
<p>Trở thành 1 trong 10 doanh nghiệp sản xuất thủy sản của Việt Nam được Bộ Nông nghiệp và Phát triển nông thôn công nhận chất lượng tốt nhất trong ngành.</p>
<h3>2011</h3>
<p>Nhảy lên top 5 doanh nghiệp xuất khẩu cá tra lớn nhất Việt Nam.</p>
<p>Được niêm yết trên sàn giao dịch chứng khoán Việt Nam.</p>
<h3>2016</h3>
<p>Được xếp hạng trong 500 công ty phát triển nhanh nhất Việt Nam.</p>
<p>Top 50 công ty kinh doanh hiệu quả nhất Việt Nam do Vietnam Business Review bình chọn.</p>
<h3>2017</h3>
<p>Xây dựng thêm các cơ sở để nâng công suất chế biến lên 1000 tấn mỗi ngày nhằm đáp ứng tốt hơn nhu cầu đang tăng nhanh trên toàn cầu.</p>
<h3>2019</h3>
<p>Đầu tư vào chương trình nghiên cứu phát triển cơ sở sản xuất giống và chất lượng đàn giống, cũng như đổi mới quản lý ao nuôi.</p>
<h2>Một lịch sử đầy trách nhiệm</h2>
<p>Là một trong những nhà sản xuất cá tra lớn nhất trên thế giới, chúng tôi hiểu việc kinh doanh luôn đi kèm với trách nhiệm to lớn. Giá trị của chúng tôi được quyết định bởi các mối quan hệ bền chặt mà chúng tôi đã xây dựng với khách hàng bằng sự tin cậy và chất lượng qua thời gian.</p>
HTML]),
            'seo_title' => $this->json(['vi' => 'Lịch sử hình thành và đổi mới']),
            'meta_description' => $this->json(['vi' => 'Hành trình hình thành và đổi mới của I.D.I từ năm 2008 qua các cột mốc phát triển quan trọng.']),
            'meta_keywords' => $this->json(['vi' => 'I.D.I, IDI Seafood, lịch sử hình thành, lịch sử đổi mới']),
            'og_title' => null,
            'og_description' => null,
            'translation_status' => $this->json(['vi' => 'published']),
            'locale_published_at' => $this->json(['vi' => now()->toIso8601String()]),
            'sort_order' => 1,
            'is_active' => true,
            'created_by' => $adminId,
            'updated_by' => $adminId,
            'deleted_at' => null,
        ]);

        $this->upsertId('pages', ['code' => 'ABOUT_VALUES'], [
            'parent_id' => null,
            'featured_media_id' => null,
            'template' => 'about-values',
            'title' => $this->json(['vi' => 'Giá trị cốt lõi']),
            'slug' => $this->json(['vi' => 'gia-tri-cot-loi']),
            'summary' => $this->json(['vi' => 'Niềm đam mê, sự đổi mới, sự sẻ chia và tinh thần trách nhiệm là những giá trị định hình văn hóa doanh nghiệp I.D.I.']),
            'content' => $this->json(['vi' => <<<'HTML'
<h2>Giá trị cốt lõi</h2>
<h3>Niềm đam mê</h3>
<p>Niềm đam mê và sự cống hiến là yếu tố chủ chốt tạo nên thành công. Chúng gắn liền với mọi khía cạnh trong suốt quá trình hoạt động và là trái tim của văn hóa doanh nghiệp tại IDI.</p>
<p><img src="https://idiseafood.com/vnt_upload/about/gt1.jpg" alt="Passion"></p>
<h3>Sự đổi mới</h3>
<p>Tại I.D.I sự đổi mới là tiêu chuẩn cho mọi thứ chúng tôi thực hiện và tất cả những gì chúng tôi sản xuất. Yếu tố này sẽ dẫn dắt chúng tôi đến đỉnh cao phát triển trong ngành công nghiệp thủy sản.</p>
<p><img src="https://idiseafood.com/vnt_upload/about/07_2024/congnhan.png" alt="Innovation"></p>
<h3>Sự sẻ chia</h3>
<p>I.D.I khuyến khích xây dựng một môi trường cởi mở và minh bạch. Trong công ty I.D.I cơ hội được chia sẻ giữa tất cả nhân viên, cho phép họ có những đóng góp quan trọng trong phạm vi kinh doanh của chúng tôi.</p>
<p><img src="https://idiseafood.com/vnt_upload/about/gt3.jpg" alt="Share"></p>
<h3>Tinh thần trách nhiệm</h3>
<p>Thừa hưởng nguồn lợi thiên nhiên đi kèm với trách nhiệm xã hội lớn lao. Sự tôn trọng và đóng góp liên tục của I.D.I cho xã hội và môi trường vẫn là yếu tố cần thiết để duy trì bản sắc và thành công của chúng tôi.</p>
<p><img src="https://idiseafood.com/vnt_upload/about/gt4.jpg" alt="Responsibility"></p>
<h2>Thế mạnh</h2>
<h3>Chúng tôi đam mê</h3>
<p>Chúng tôi yêu mến loài cá tra. Chúng tôi muốn hiểu tất cả mọi thứ về nó, làm thế nào để khách hàng thưởng thức được hương vị ngon nhất và làm thế nào chúng tôi có thể sản xuất nó một cách thuần túy và có trách nhiệm nhất.</p>
<h3>Chúng tôi làm việc trung thực</h3>
<p>Chúng tôi tin tưởng vào việc hợp tác trung thực, minh bạch với các đối tác và các bên liên quan trong chuỗi giá trị. Đó là lý do chúng tôi tìm kiếm các đối tác lâu dài, bền vững cho các nguồn cung ứng, cũng như các khách hàng có cùng niềm tin như chúng tôi.</p>
<h3>Chúng tôi đáng tin cậy</h3>
<p>I.D.I cung cấp hàng loạt các mặt hàng cá tra tươi tự nhiên. Từ những sự lựa chọn đa dạng đó, chúng tôi tư vấn sản phẩm phù hợp nhất với nhu cầu của khách hàng. Chính vì thế, IDI đã trở thành một đối tác đáng tin cậy cho nhiều khách hàng - bán lẻ, bán buôn, và dịch vụ ăn uống.</p>
<h2>Nhiệm vụ</h2>
<h3>Nuôi trồng bền vững</h3>
<p>Chúng tôi muốn tăng sinh kế bền vững từ sản xuất nuôi trồng thủy sản mà không tạo ra các tác động xấu đến kinh tế xã hội hoặc môi trường.</p>
<h3>Chuỗi giá trị và dinh dưỡng cao</h3>
<p>Chúng tôi cố gắng khám phá hết tiềm năng tuyệt vời của loài cá tra, tăng cường khả năng tiếp cận và tiêu thụ các sản phẩm từ cá tra có giá trị dinh dưỡng cao, đồng thời nuôi trồng bền vững, đặc biệt là cung cấp cá tra cho các khu vực đang phát triển.</p>
<h3>Trách nhiệm xã hội</h3>
<p>Chúng tôi cũng nghĩ rằng việc công ty I.D.I đóng góp vào các chương trình xã hội mang ý nghĩa rất quan trọng, hướng đến việc phát triển các cơ sở nuôi trồng thủy sản quy mô nhỏ và nâng cao giáo dục trong cộng đồng nhằm xóa đói – giảm nghèo và đảm bảo an ninh lương thực trong các vùng địa lý trọng yếu.</p>
HTML]),
            'seo_title' => $this->json(['vi' => 'Giá trị cốt lõi']),
            'meta_description' => $this->json(['vi' => 'Các giá trị cốt lõi, thế mạnh và nhiệm vụ định hình văn hóa doanh nghiệp I.D.I.']),
            'meta_keywords' => $this->json(['vi' => 'I.D.I, IDI Seafood, giá trị cốt lõi, thế mạnh, nhiệm vụ']),
            'og_title' => null,
            'og_description' => null,
            'translation_status' => $this->json(['vi' => 'published']),
            'locale_published_at' => $this->json(['vi' => now()->toIso8601String()]),
            'sort_order' => 2,
            'is_active' => true,
            'created_by' => $adminId,
            'updated_by' => $adminId,
            'deleted_at' => null,
        ]);

        $sustainabilityId = $this->upsertId('pages', ['code' => 'SUSTAINABILITY'], [
            'parent_id' => null,
            'featured_media_id' => $mediaId,
            'template' => 'default',
            'title' => $this->translations('Phát triển bền vững', 'Sustainability', '可持续发展'),
            'slug' => $this->translations('phat-trien-ben-vung', 'sustainability', 'kechixu-fazhan'),
            'summary' => $this->translations(
                'Cam kết với môi trường, cộng đồng và chất lượng.',
                'Committed to the environment, communities, and quality.',
                '致力于环境、社区与品质。'
            ),
            'content' => null,
            'seo_title' => null,
            'meta_description' => null,
            'og_title' => null,
            'og_description' => null,
            'translation_status' => $this->publishedStatus(),
            'locale_published_at' => $this->publishedDates(),
            'sort_order' => 1,
            'is_active' => true,
            'created_by' => $adminId,
            'updated_by' => $adminId,
            'deleted_at' => null,
        ]);

        DB::table('page_sections')->where('page_id', $messageId)->delete();

        foreach ([
            [$sustainabilityId, 'environment', 'Môi trường', 'Environment', '环境', 0],
            [$sustainabilityId, 'community', 'Cộng đồng', 'Community', '社区', 1],
        ] as [$pageId, $type, $vi, $en, $zh, $sortOrder]) {
            $this->upsertId('page_sections', [
                'page_id' => $pageId,
                'section_type' => $type,
                'sort_order' => $sortOrder,
            ], [
                'title' => $this->translations($vi, $en, $zh),
                'content' => $this->translations(
                    "Nội dung mẫu cho phần {$vi}.",
                    "Sample content for {$en}.",
                    "{$zh}部分的示例内容。"
                ),
                'payload' => null,
                'is_active' => true,
            ]);
        }
    }

    private function publishedStatus(): string
    {
        return $this->json(['vi' => 'published', 'en' => 'published', 'zh' => 'published']);
    }

    private function publishedDates(): string
    {
        $date = now()->subDays(14)->toIso8601String();

        return $this->json(['vi' => $date, 'en' => $date, 'zh' => $date]);
    }
}
