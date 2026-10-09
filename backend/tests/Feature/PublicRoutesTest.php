<?php

namespace Tests\Feature;

use App\Http\Controllers\Api\PublicRoutesController;
use App\Models\Page;
use App\Models\Post;
use App\Models\PostCategory;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Recipe;
use App\Support\PublicRoutes;
use Database\Seeders\CoreSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class PublicRoutesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(CoreSeeder::class);
    }

    private function record(string $class, string $suffix = 'one')
    {
        $title = in_array($class, [PostCategory::class, ProductCategory::class]) ? 'name' : 'title';
        $attributes = ['code' => 'TEST_'.$suffix, 'sku' => 'TEST_'.$suffix, $title => ['vi' => 'Vietnamese', 'en' => 'English', 'zh' => 'Chinese'], 'slug' => ['vi' => 'vi-'.$suffix, 'en' => 'en-'.$suffix, 'zh' => 'zh-'.$suffix], 'translation_status' => ['vi' => 'published', 'en' => 'published', 'zh' => 'published'], 'is_active' => true];
        if ($class === Page::class) {
            $attributes['template'] = 'about';
        }
        if ($class !== Product::class) {
            unset($attributes['sku']);
        } else {
            unset($attributes['code']);
        }

        return $class::create($attributes);
    }

    public function test_all_content_types_sync_resolve_and_switch_by_identity(): void
    {
        foreach (array_keys(PublicRoutes::TYPES) as $class) {
            $record = $this->record($class, strtolower(class_basename($class)));
            foreach (['vi', 'en', 'zh'] as $locale) {
                $this->assertDatabaseHas('localized_routes', ['routeable_type' => $class, 'routeable_id' => $record->id, 'locale' => $locale, 'full_path' => PublicRoutes::path($record, $locale)]);
                $entry = collect($this->getJson('/api/public-routes')->assertOk()->json('entries'))->firstWhere('key', $class.':'.$record->id);
                $this->assertSame(PublicRoutes::path($record, $locale), $entry['paths'][$locale]);
            }
        }
    }

    public function test_slug_change_redirects_are_flattened_reversible_and_preserve_query(): void
    {
        $post = $this->record(Post::class);
        $post->setTranslation('slug', 'en', 'second')->save();
        $post->setTranslation('slug', 'en', 'third')->save();
        $this->get('/en/news/en-one?utm_source=test')->assertStatus(301)->assertRedirect('/en/news/third?utm_source=test');
        $this->get('/en/news/second')->assertRedirect('/en/news/third');
        $post->setTranslation('slug', 'en', 'en-one')->save();
        $this->assertDatabaseMissing('redirects', ['from_path' => '/en/news/en-one']);
        $this->get('/en/news/third')->assertRedirect('/en/news/en-one');
        $this->get('/news/vi-one?ref=old')->assertStatus(301)->assertRedirect('/vi/tin-tuc/vi-one?ref=old');
        $post->delete();
        $this->get('/en/news/third')->assertNotFound();
    }

    public function test_unavailable_translations_and_future_dates_are_excluded_from_sitemap(): void
    {
        $recipe = $this->record(Recipe::class);
        $recipe->setTranslation('translation_status', 'zh', 'draft');
        $recipe->setTranslation('locale_published_at', 'en', now()->addDays(2)->toIso8601String())->save();
        $xml = $this->get('/sitemap.xml')->assertOk()->getContent();
        $this->assertStringContainsString('/vi/cong-thuc/vi-one', $xml);
        $this->assertStringNotContainsString('/en/recipes/en-one', $xml);
        $this->assertStringNotContainsString('/zh/shipu/zh-one', $xml);
        $this->get('/zh/shipu/zh-one')->assertNotFound();
        $this->travel(3)->days();
        $this->assertStringContainsString('/en/recipes/en-one', $this->get('/sitemap.xml')->getContent());
        $recipe->update(['is_active' => false]);
        $this->assertStringNotContainsString('/vi/cong-thuc/vi-one', $this->get('/sitemap.xml')->getContent());
    }

    public function test_category_collision_is_rejected_before_content_is_saved(): void
    {
        $this->record(PostCategory::class);
        try {
            $this->record(Post::class);
            $this->fail('Expected collision validation');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('slug.vi', $exception->errors());
            $this->assertSame(0, Post::count());
        }
    }

    public function test_static_routes_legacy_urls_and_safe_backfill(): void
    {
        foreach (PublicRoutes::statics() as $legacy => $paths) {
            $this->get($legacy.'?x=1')->assertStatus(301)->assertRedirect($paths['vi'].'?x=1');
            foreach ($paths as $path) {
                $this->assertSame($path, app(PublicRoutesController::class)->resolve($path));
            }
        }
        $this->record(Page::class);
        $this->artisan('routes:sync-public')->assertSuccessful();
        $count = DB::table('localized_routes')->count();
        $this->artisan('routes:sync-public')->assertSuccessful();
        $this->assertSame($count, DB::table('localized_routes')->count());
    }

    public function test_document_entry_serves_spa_on_reload_and_rejects_unknown_or_unpublished_paths(): void
    {
        $index = tempnam(sys_get_temp_dir(), 'idi-spa-');
        file_put_contents($index, '<!doctype html><div id="root"></div>');
        config(['app.frontend_index' => $index]);
        try {
            $post = $this->record(Post::class);
            foreach (['vi', 'en', 'zh'] as $locale) {
                $this->get(PublicRoutes::path($post, $locale))->assertOk();
                $this->get(PublicRoutes::path($post, $locale))->assertOk();
            }
            $this->get('http://localhost/en/news/en-one/?ref=slash')->assertRedirect('/en/news/en-one?ref=slash');
            $this->get('/en/news/unknown')->assertNotFound();
            $post->setTranslation('translation_status', 'en', 'draft')->save();
            $this->get('/en/news/en-one')->assertNotFound();
        } finally {
            unlink($index);
        }
    }

    public function test_category_urls_legacy_filters_and_sitemap_flags(): void
    {
        $category = $this->record(ProductCategory::class);
        $this->get('/products?category=en-one&ref=menu')->assertRedirect('/vi/san-pham/vi-one?category=en-one&ref=menu');
        DB::table('localized_routes')->where('routeable_type', ProductCategory::class)->where('locale', 'en')->update(['include_in_sitemap' => false]);
        $xml = $this->get('/sitemap.xml')->getContent();
        $this->assertStringNotContainsString('<loc>'.config('app.frontend_url').'/en/products/en-one</loc>', $xml);
        $category->update(['is_active' => false]);
        $this->assertStringNotContainsString('/vi/san-pham/vi-one', $this->get('/sitemap.xml')->getContent());
    }
}
