<?php

namespace Tests\Feature;

use App\Models\Media;
use App\Models\Post;
use App\Models\PostCategory;
use App\Models\Tag;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class NewsApiTest extends TestCase
{
    use RefreshDatabase;

    private int $postSequence = 0;

    protected function setUp(): void
    {
        parent::setUp();

        Carbon::setTestNow('2026-08-17 12:00:00');
        $moduleId = DB::table('modules')->insertGetId([
            'code' => 'news',
            'name' => 'News',
            'module_type' => 'content',
            'page_title' => json_encode(['vi' => 'Tin tức IDI', 'en' => 'IDI News']),
            'description' => json_encode(['vi' => 'Thông tin mới nhất từ IDI']),
            'seo_title' => json_encode(['vi' => 'Tin tức IDI Seafood']),
            'meta_description' => json_encode(['vi' => 'Tin tức chính thức từ IDI Seafood']),
            'og_title' => json_encode(['vi' => 'I.D.I trên mạng xã hội']),
            'og_description' => json_encode(['vi' => 'Cập nhật chính thức từ I.D.I']),
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        foreach ([
            'items_per_page' => 9,
            'category_items_per_page' => 6,
            'featured_limit' => 2,
            'related_limit' => 4,
            'show_author' => false,
            'show_tags' => true,
            'lazy_load_images' => false,
            'meta_keywords' => ['vi' => 'IDI Seafood, tin tức'],
        ] as $key => $value) {
            DB::table('module_settings')->insert([
                'module_id' => $moduleId,
                'setting_key' => $key,
                'setting_value' => json_encode($value),
                'setting_type' => is_bool($value) ? 'boolean' : (is_int($value) ? 'number' : 'json'),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    public function test_index_returns_public_articles_featured_categories_and_page_metadata(): void
    {
        $category = $this->category();
        $author = User::factory()->create(['name' => 'Ban Biên tập IDI']);
        $media = Media::create([
            'disk' => 'public',
            'directory' => 'news',
            'file_name' => 'green-bond.jpg',
            'external_url' => 'https://idiseafood.com/vnt_upload/news/green-bond.jpg',
            'original_name' => 'green-bond.jpg',
            'mime_type' => 'image/jpeg',
            'extension' => 'jpg',
            'title' => ['vi' => 'Trái phiếu xanh'],
            'alt_text' => ['vi' => 'Lễ trao giải trái phiếu xanh'],
            'caption' => ['vi' => 'Đại diện IDI nhận giải thưởng'],
        ]);
        $post = $this->makePost($category, [
            'code' => 'IDI_GREEN_BOND_2025',
            'featured_media_id' => $media->id,
            'author_id' => $author->id,
            'title' => ['vi' => 'Trái phiếu xanh của năm'],
            'slug' => ['vi' => 'trai-phieu-xanh-cua-nam'],
            'excerpt' => ['vi' => 'IDI được vinh danh tại khu vực Châu Á - Thái Bình Dương.'],
            'content' => ['vi' => '<h2>Dấu ấn xanh</h2><p>Nội dung bài viết chính thức.</p>'],
            'seo_title' => ['vi' => 'Giải thưởng trái phiếu xanh IDI'],
            'meta_description' => ['vi' => 'Thông tin giải thưởng của IDI'],
            'is_featured' => true,
            'schema_extra' => [
                'source_url' => 'https://idiseafood.com/vn/trai-phieu-xanh.html',
                'view_count' => 3793,
                'read_time' => 7,
                'author_role' => 'Phòng Truyền thông',
            ],
        ]);
        $activeTag = Tag::create([
            'name' => ['vi' => 'Trái phiếu xanh'],
            'slug' => ['vi' => 'trai-phieu-xanh'],
            'is_active' => true,
        ]);
        $inactiveTag = Tag::create([
            'name' => ['vi' => 'Không hiển thị'],
            'slug' => ['vi' => 'khong-hien-thi'],
            'is_active' => false,
        ]);
        $post->tags()->attach([$activeTag->id, $inactiveTag->id]);

        $this->makePost($category, [
            'translation_status' => ['vi' => 'draft'],
            'slug' => ['vi' => 'ban-nhap'],
        ]);
        $this->makePost($category, [
            'locale_published_at' => ['vi' => now()->addDay()->toIso8601String()],
            'slug' => ['vi' => 'tin-hen-gio'],
        ]);

        $response = $this->getJson('/api/news?locale=vi&limit=10')
            ->assertOk()
            ->assertJsonPath('total', 1)
            ->assertJsonPath('page', 1)
            ->assertJsonPath('limit', 10)
            ->assertJsonPath('lastPage', 1)
            ->assertJsonPath('items.0.id', $post->id)
            ->assertJsonPath('items.0.code', 'IDI_GREEN_BOND_2025')
            ->assertJsonPath('items.0.locale', 'vi')
            ->assertJsonPath('items.0.category.code', 'NEWS_LATEST')
            ->assertJsonPath('items.0.image.url', 'https://idiseafood.com/vnt_upload/news/green-bond.jpg')
            ->assertJsonPath('items.0.image.alt', 'Lễ trao giải trái phiếu xanh')
            ->assertJsonPath('items.0.image.caption', 'Đại diện IDI nhận giải thưởng')
            ->assertJsonPath('items.0.author.name', 'Ban Biên tập IDI')
            ->assertJsonPath('items.0.author.role', 'Phòng Truyền thông')
            ->assertJsonPath('items.0.readTime', 7)
            ->assertJsonPath('items.0.tags.0', 'Trái phiếu xanh')
            ->assertJsonPath('items.0.sourceUrl', 'https://idiseafood.com/vn/trai-phieu-xanh.html')
            ->assertJsonPath('items.0.viewCount', 3793)
            ->assertJsonPath('items.0.seo.title', 'Giải thưởng trái phiếu xanh IDI')
            ->assertJsonPath('featured.0.id', $post->id)
            ->assertJsonPath('categories.0.id', $category->id)
            ->assertJsonPath('categories.0.count', 1)
            ->assertJsonPath('pageConfig.title', 'Tin tức IDI')
            ->assertJsonPath('pageConfig.seo.keywords', 'IDI Seafood, tin tức')
            ->assertJsonPath('pageConfig.seo.ogTitle', 'I.D.I trên mạng xã hội')
            ->assertJsonPath('pageConfig.seo.ogDescription', 'Cập nhật chính thức từ I.D.I')
            ->assertJsonPath('pageConfig.itemsPerPage', 9)
            ->assertJsonPath('pageConfig.categoryItemsPerPage', 6)
            ->assertJsonPath('pageConfig.featuredLimit', 2)
            ->assertJsonPath('pageConfig.relatedLimit', 4)
            ->assertJsonPath('pageConfig.presentation.showAuthor', false)
            ->assertJsonPath('pageConfig.presentation.showTags', true)
            ->assertJsonPath('pageConfig.presentation.lazyLoadImages', false);

        $this->assertArrayNotHasKey('contentHtml', $response->json('items.0'));
        $this->assertCount(1, $response->json('items.0.tags'));
    }

    public function test_show_returns_full_html_but_rejects_draft_future_and_hidden_category_articles(): void
    {
        $category = $this->category();
        $public = $this->makePost(null, [
            'title' => ['vi' => 'Tin không phân loại'],
            'slug' => ['vi' => 'tin-khong-phan-loai'],
            'content' => ['vi' => '<h2>Nội dung đầy đủ</h2><p>Thông tin chi tiết.</p>'],
        ]);
        $draft = $this->makePost($category, [
            'translation_status' => ['vi' => 'draft'],
            'slug' => ['vi' => 'tin-ban-nhap'],
        ]);
        $future = $this->makePost($category, [
            'locale_published_at' => ['vi' => now()->addHour()->format('Y-m-d H:i:s')],
            'slug' => ['vi' => 'tin-trong-tuong-lai'],
        ]);
        $hiddenCategory = $this->category([
            'code' => 'NEWS_HIDDEN',
            'slug' => ['vi' => 'tin-an'],
            'is_active' => false,
        ]);
        $hidden = $this->makePost($hiddenCategory, ['slug' => ['vi' => 'tin-thuoc-danh-muc-an']]);
        $draftCategory = $this->category([
            'code' => 'NEWS_CATEGORY_DRAFT',
            'slug' => ['vi' => 'danh-muc-nhap'],
            'translation_status' => ['vi' => 'draft'],
        ]);
        $inDraftCategory = $this->makePost($draftCategory, ['slug' => ['vi' => 'tin-thuoc-danh-muc-nhap']]);

        $this->getJson('/api/news/'.$public->getTranslation('slug', 'vi'))
            ->assertOk()
            ->assertJsonPath('data.id', $public->id)
            ->assertJsonPath('data.category', null)
            ->assertJsonPath('data.contentHtml', '<h2>Nội dung đầy đủ</h2><p>Thông tin chi tiết.</p>')
            ->assertJsonPath('pageConfig.title', 'Tin tức IDI');

        foreach ([$draft, $future, $hidden, $inDraftCategory] as $unpublished) {
            $this->getJson('/api/news/'.$unpublished->getTranslation('slug', 'vi'))->assertNotFound();
        }

        $this->getJson('/api/news?locale=vi')
            ->assertOk()
            ->assertJsonPath('total', 1)
            ->assertJsonPath('items.0.id', $public->id);
    }

    public function test_index_filters_by_category_search_featured_and_exclusion(): void
    {
        $latest = $this->category();
        $market = $this->category([
            'code' => 'NEWS_MARKET',
            'name' => ['vi' => 'Tin thị trường'],
            'slug' => ['vi' => 'tin-thi-truong'],
        ]);
        $award = $this->makePost($latest, [
            'code' => 'GREEN_AWARD',
            'title' => ['vi' => 'Giải thưởng xanh quốc tế'],
            'slug' => ['vi' => 'giai-thuong-xanh'],
            'is_featured' => true,
        ]);
        $activity = $this->makePost($latest, [
            'code' => 'IDI_ACTIVITY',
            'title' => ['vi' => 'Hoạt động thường niên'],
            'slug' => ['vi' => 'hoat-dong-thuong-nien'],
        ]);
        $marketPost = $this->makePost($market, [
            'code' => 'MARKET_NEWS',
            'title' => ['vi' => 'Thị trường cá tra'],
            'slug' => ['vi' => 'thi-truong-ca-tra'],
            'is_featured' => true,
        ]);

        foreach ([$latest->id, 'NEWS_LATEST', 'tin-moi', 'Tin mới'] as $identifier) {
            $this->getJson('/api/news?locale=vi&category='.urlencode((string) $identifier))
                ->assertOk()
                ->assertJsonPath('total', 2);
        }

        $this->getJson('/api/news?locale=vi&search='.urlencode('thưởng xanh'))
            ->assertOk()
            ->assertJsonPath('total', 1)
            ->assertJsonPath('items.0.id', $award->id);
        $this->getJson('/api/news?locale=vi&featured=1')
            ->assertOk()
            ->assertJsonPath('total', 2);
        $this->getJson('/api/news?locale=vi&featured=0')
            ->assertOk()
            ->assertJsonPath('total', 1)
            ->assertJsonPath('items.0.id', $activity->id);
        $this->getJson('/api/news?locale=vi&category=tin-thi-truong&exclude='.$marketPost->id)
            ->assertOk()
            ->assertJsonPath('total', 0);
        $this->getJson('/api/news?locale=vi&category=tin-moi&exclude='.$award->getTranslation('slug', 'vi'))
            ->assertOk()
            ->assertJsonPath('total', 1)
            ->assertJsonPath('items.0.id', $activity->id);
    }

    public function test_index_orders_by_localized_publication_date_and_returns_pagination_metadata(): void
    {
        $category = $this->category();
        $oldest = $this->makePost($category, [
            'slug' => ['vi' => 'tin-cu-nhat'],
            'locale_published_at' => ['vi' => '2026-01-01T00:00:00+00:00'],
        ]);
        $middle = $this->makePost($category, [
            'slug' => ['vi' => 'tin-o-giua'],
            'locale_published_at' => ['vi' => '2026-04-01T00:00:00+00:00'],
        ]);
        $newest = $this->makePost($category, [
            'slug' => ['vi' => 'tin-moi-nhat'],
            'locale_published_at' => ['vi' => '2026-08-01T00:00:00+00:00'],
        ]);

        $this->getJson('/api/news?locale=vi&sort=newest&limit=2&page=1')
            ->assertOk()
            ->assertJsonPath('items.0.id', $newest->id)
            ->assertJsonPath('items.1.id', $middle->id)
            ->assertJsonPath('total', 3)
            ->assertJsonPath('page', 1)
            ->assertJsonPath('limit', 2)
            ->assertJsonPath('lastPage', 2);

        $this->getJson('/api/news?locale=vi&sort=oldest&limit=2&page=1')
            ->assertOk()
            ->assertJsonPath('items.0.id', $oldest->id)
            ->assertJsonPath('items.1.id', $middle->id);
    }

    public function test_index_uses_editorial_order_when_articles_share_a_publication_date(): void
    {
        $category = $this->category();
        $lowerPriority = $this->makePost($category, [
            'slug' => ['vi' => 'tin-cung-ngay-uu-tien-thap'],
            'locale_published_at' => ['vi' => '2026-07-01 00:00:00'],
            'sort_order' => 10,
        ]);
        $higherPriority = $this->makePost($category, [
            'slug' => ['vi' => 'tin-cung-ngay-uu-tien-cao'],
            'locale_published_at' => ['vi' => '2026-07-01 00:00:00'],
            'sort_order' => 20,
        ]);

        $this->getJson('/api/news?locale=vi&sort=newest')
            ->assertOk()
            ->assertJsonPath('items.0.id', $higherPriority->id)
            ->assertJsonPath('items.1.id', $lowerPriority->id);
    }

    public function test_disabled_news_module_returns_not_found(): void
    {
        DB::table('modules')->where('code', 'news')->update(['is_active' => false]);

        $this->getJson('/api/news')->assertNotFound();
        $this->getJson('/api/news/any-slug')->assertNotFound();
    }

    private function category(array $overrides = []): PostCategory
    {
        return PostCategory::create(array_replace([
            'code' => 'NEWS_LATEST',
            'name' => ['vi' => 'Tin mới'],
            'slug' => ['vi' => 'tin-moi'],
            'description' => ['vi' => 'Những tin tức mới nhất'],
            'translation_status' => ['vi' => 'published'],
            'locale_published_at' => ['vi' => '2026-01-01T00:00:00+00:00'],
            'sort_order' => 10,
            'is_active' => true,
        ], $overrides));
    }

    private function makePost(?PostCategory $category, array $overrides = []): Post
    {
        $this->postSequence++;
        $sequence = $this->postSequence;

        return Post::create(array_replace([
            'post_category_id' => $category?->id,
            'code' => "NEWS_TEST_{$sequence}",
            'title' => ['vi' => "Tin thử nghiệm {$sequence}"],
            'slug' => ['vi' => "tin-thu-nghiem-{$sequence}"],
            'excerpt' => ['vi' => "Mô tả tin thử nghiệm {$sequence}"],
            'content' => ['vi' => "<p>Nội dung tin thử nghiệm {$sequence}</p>"],
            'translation_status' => ['vi' => 'published'],
            'locale_published_at' => ['vi' => now()->subDays($sequence)->toIso8601String()],
            'sort_order' => 0,
            'is_featured' => false,
            'is_active' => true,
        ], $overrides));
    }
}
