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
        $aboutId = $this->upsertId('pages', ['code' => 'ABOUT'], [
            'parent_id' => null,
            'featured_media_id' => $mediaId,
            'template' => 'about',
            'title' => $this->translations('Về IDI Seafood', 'About IDI Seafood', '关于 IDI Seafood'),
            'slug' => $this->translations('ve-chung-toi', 'about-us', 'guanyu-women'),
            'summary' => $this->translations(
                'Doanh nghiệp thủy sản Việt Nam hướng đến thị trường toàn cầu.',
                'A Vietnamese seafood company serving global markets.',
                '服务全球市场的越南水产企业。'
            ),
            'content' => $this->translations(
                '<p>IDI Seafood phát triển chuỗi giá trị cá tra khép kín và bền vững.</p>',
                '<p>IDI Seafood develops an integrated and sustainable pangasius value chain.</p>',
                '<p>IDI Seafood 建设一体化、可持续的巴沙鱼价值链。</p>'
            ),
            'seo_title' => null,
            'meta_description' => null,
            'og_title' => null,
            'og_description' => null,
            'translation_status' => $this->publishedStatus(),
            'locale_published_at' => $this->publishedDates(),
            'sort_order' => 0,
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

        foreach ([
            [$aboutId, 'intro', 'Giới thiệu', 'Introduction', '公司介绍', 0],
            [$aboutId, 'values', 'Giá trị cốt lõi', 'Core values', '核心价值观', 1],
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
