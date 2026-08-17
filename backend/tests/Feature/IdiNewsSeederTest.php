<?php

namespace Tests\Feature;

use App\Models\Post;
use App\Models\PostCategory;
use Database\Seeders\CoreSeeder;
use Database\Seeders\IdiNewsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class IdiNewsSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_seeds_the_current_idi_news_snapshot_idempotently(): void
    {
        $this->seed(CoreSeeder::class);
        $this->seed(IdiNewsSeeder::class);
        $this->seed(IdiNewsSeeder::class);

        $this->assertSame(13, Post::query()->count());
        $this->assertSame(3, PostCategory::query()->count());
        $this->assertSame(13, DB::table('localized_routes')->where('routeable_type', Post::class)->count());
        $this->assertSame(9, DB::table('localized_routes')->where('routeable_type', PostCategory::class)->count());

        $latest = Post::query()->where('code', 'IDI_NEWS_20260427_AGM')->firstOrFail();
        $this->assertSame(
            'I.D.I TỔ CHỨC ĐẠI HỘI ĐỒNG CỔ ĐÔNG THƯỜNG NIÊN NĂM 2026',
            $latest->getTranslation('title', 'vi')
        );
        $this->assertSame('2026-04-27 00:00:00', $latest->getTranslation('locale_published_at', 'vi'));
        $this->assertNull($latest->post_category_id);

        $award = Post::query()
            ->with(['featuredMedia', 'tags'])
            ->where('code', 'IDI_NEWS_20250415_GREEN_BOND_AWARD')
            ->firstOrFail();
        $this->assertTrue($award->is_featured);
        $this->assertSame(
            'https://idiseafood.com/vnt_upload/news/04_2025/SDAW25_LOGO_WIN_GBOTY_CAPAC.jpg',
            $award->featuredMedia?->url
        );
        $this->assertSame(3793, $award->schema_extra['view_count']);
        $this->assertSame('https://www.idiseafood.com/vn/trai-phieu-xanh-cua-nam-khoi-doanh-nghiep-khu-vuc-chau-a-thai-binh-duong-i-d-i-sao-mai.html', $award->schema_extra['source_url']);
        $this->assertGreaterThanOrEqual(2, $award->tags->count());

        $moduleId = DB::table('modules')->where('code', 'news')->value('id');
        $itemsPerPage = DB::table('module_settings')
            ->where('module_id', $moduleId)
            ->where('setting_key', 'items_per_page')
            ->value('setting_value');
        $this->assertSame(12, json_decode($itemsPerPage, true));
    }

    public function test_it_retires_only_the_known_legacy_demo_news(): void
    {
        $this->seed(CoreSeeder::class);

        $categoryId = DB::table('post_categories')->insertGetId([
            'code' => 'COMPANY_NEWS',
            'name' => json_encode(['vi' => 'Tin doanh nghiệp']),
            'slug' => json_encode(['vi' => 'tin-doanh-nghiep']),
            'translation_status' => json_encode(['vi' => 'published']),
            'locale_published_at' => json_encode(['vi' => now()->subDay()->toDateTimeString()]),
            'sort_order' => 0,
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('posts')->insert([
            'post_category_id' => $categoryId,
            'code' => 'NEWS_FACTORY_2026',
            'title' => json_encode(['vi' => 'Bài mẫu']),
            'slug' => json_encode(['vi' => 'bai-mau']),
            'translation_status' => json_encode(['vi' => 'published']),
            'locale_published_at' => json_encode(['vi' => now()->subDay()->toDateTimeString()]),
            'sort_order' => 0,
            'is_featured' => false,
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->seed(IdiNewsSeeder::class);

        $this->assertSoftDeleted('posts', ['code' => 'NEWS_FACTORY_2026']);
        $this->assertSoftDeleted('post_categories', ['code' => 'COMPANY_NEWS']);
        $this->assertSame(13, Post::query()->count());
    }
}
