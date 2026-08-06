<?php

namespace Tests\Feature;

use App\Livewire\Admin\News\CategoryForm;
use App\Livewire\Admin\News\CategoryIndex;
use App\Livewire\Admin\News\Featured;
use App\Livewire\Admin\News\PostForm;
use App\Livewire\Admin\News\PostIndex;
use App\Livewire\Admin\News\Settings;
use App\Models\Post;
use App\Models\PostCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class NewsManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_news_editor_can_open_every_news_screen(): void
    {
        $user = $this->editor();
        $category = $this->category();
        $post = $this->makePost($category);

        foreach ([
            '/admin/news', '/admin/news/settings', '/admin/news/categories',
            '/admin/news/categories/create', "/admin/news/categories/{$category->id}/edit",
            '/admin/news/create', "/admin/news/{$post->id}/edit", '/admin/news/featured',
        ] as $url) {
            $this->actingAs($user)->get($url)->assertOk();
        }
        $this->actingAs($user)->get("/admin/news/{$post->id}/preview?locale=en")
            ->assertOk()->assertSee('IDI opens a new factory');
        $this->actingAs($user)->get('/admin/news/categories/create')
            ->assertOk()->assertDontSee('Trạng thái bản dịch');
    }

    public function test_post_is_saved_in_three_languages_with_safe_content_and_routes(): void
    {
        Storage::fake('public');
        $category = $this->category();

        Livewire::actingAs($this->editor())->test(PostForm::class)
            ->assertDontSee('Trạng thái bản dịch')
            ->assertSeeHtml('ckeditor5-textarea')
            ->assertSee('Ngôn ngữ bài viết')
            ->assertSet('enabled_locales', ['vi'])
            ->set('enabled_locales', ['vi', 'en', 'zh'])
            ->set('post_category_id', $category->id)
            ->set('code', 'NEWS_EXPORT_2026')
            ->set('featured_image', UploadedFile::fake()->createWithContent(
                'news.png',
                base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVQIHWP4z8DwHwAFgAI/ScLzWQAAAABJRU5ErkJggg==')
            ))
            ->set('title', ['vi' => 'IDI mở rộng xuất khẩu', 'en' => 'IDI expands exports', 'zh' => 'IDI扩大出口'])
            ->set('slug', ['vi' => 'idi-mo-rong-xuat-khau', 'en' => 'idi-expands-exports', 'zh' => 'idi-kuoda-chukou'])
            ->set('content.vi', '<p>Nội dung an toàn</p><script>alert(1)</script>')
            ->call('save')
            ->assertHasNoErrors();

        $post = Post::where('code', 'NEWS_EXPORT_2026')->firstOrFail();
        $this->assertSame('IDI expands exports', $post->getTranslation('title', 'en'));
        $this->assertSame('published', $post->getTranslation('translation_status', 'en', false));
        $this->assertSame('published', $post->getTranslation('translation_status', 'zh', false));
        $this->assertStringNotContainsString('<script', $post->getTranslation('content', 'vi'));
        $this->assertDatabaseHas('localized_routes', [
            'routeable_type' => Post::class, 'routeable_id' => $post->id,
            'locale' => 'zh', 'full_path' => '/zh/xinwen/idi-kuoda-chukou',
        ]);
    }

    public function test_existing_post_without_featured_image_can_be_updated(): void
    {
        $post = $this->makePost($this->category());

        Livewire::actingAs($this->editor())->test(PostForm::class, ['post' => $post])
            ->set('title.vi', 'Tiêu đề đã cập nhật')
            ->call('save')
            ->assertHasNoErrors()
            ->assertRedirect(route('admin.news.posts.index'));

        $this->assertSame('Tiêu đề đã cập nhật', $post->refresh()->getTranslation('title', 'vi'));
        $this->assertNull($post->featured_media_id);
    }

    public function test_new_post_without_featured_image_has_a_vietnamese_validation_message(): void
    {
        Livewire::actingAs($this->editor())->test(PostForm::class)
            ->set('post_category_id', $this->category()->id)
            ->set('title.vi', 'Tin mới')
            ->set('slug.vi', 'tin-moi')
            ->call('save')
            ->assertHasErrors(['featured_image' => 'required'])
            ->assertSee('Vui lòng chọn ảnh đại diện.');
    }

    public function test_news_language_toggles_follow_available_translations(): void
    {
        $post = $this->makePost($this->category());

        Livewire::actingAs($this->editor())->test(PostForm::class, ['post' => $post])
            ->assertSet('enabled_locales', ['vi', 'en'])
            ->set('enabled_locales', ['vi', 'zh'])
            ->assertSet('enabled_locales', ['vi', 'zh'])
            ->set('enabled_locales', [])
            ->assertSet('enabled_locales', ['vi']);
    }

    public function test_news_index_reflects_disabled_translations(): void
    {
        $this->makePost($this->category());

        Livewire::actingAs($this->editor())->test(PostIndex::class)
            ->set('locale', 'zh')
            ->assertSee('Bản dịch đang tắt')
            ->assertSeeHtml('title="ZH: Bản dịch đang tắt"')
            ->assertSeeHtml('title="EN: Đang bật"');
    }

    public function test_news_delete_uses_confirmation_modal_for_single_and_bulk_actions(): void
    {
        $post = $this->makePost($this->category());
        $component = Livewire::actingAs($this->editor())->test(PostIndex::class)
            ->call('requestDelete', $post->id)
            ->assertSet('pendingDeleteId', $post->id)
            ->assertSet('pendingDeleteName', 'IDI mở nhà máy mới')
            ->assertSee('Xóa tin tức?')
            ->call('cancelDelete')
            ->assertSet('pendingDeleteId', null);

        $component->set('selected', [$post->id])
            ->call('requestBulkDelete')
            ->assertSet('pendingBulkDelete', true)
            ->assertSee('1 tin tức')
            ->call('confirmDelete')
            ->assertSet('pendingBulkDelete', false)
            ->assertSet('selected', []);

        $this->assertSoftDeleted('posts', ['id' => $post->id]);
    }

    public function test_news_index_has_configurable_pagination(): void
    {
        $category = $this->category();
        foreach (range(1, 12) as $index) {
            $this->makePost($category)->update(['sort_order' => $index]);
        }

        Livewire::actingAs($this->editor())->test(PostIndex::class)
            ->assertSet('perPage', 10)
            ->assertSeeHtml('wire:model.live="perPage"')
            ->assertViewHas('posts', fn ($posts) => $posts->count() === 10 && $posts->total() === 12)
            ->set('perPage', 20)
            ->assertSet('perPage', 20)
            ->assertViewHas('posts', fn ($posts) => $posts->count() === 12 && $posts->total() === 12);
    }

    public function test_featured_news_library_is_paginated_and_articles_can_be_added(): void
    {
        $category = $this->category();
        $posts = collect(range(1, 12))->map(fn ($index) => $this->makePost($category));

        Livewire::actingAs($this->editor())->test(Featured::class)
            ->assertSet('perPage', 10)
            ->assertSee('vị trí còn trống')
            ->assertSeeHtml('wire:model.live="perPage"')
            ->assertViewHas('available', fn ($available) => $available->count() === 10 && $available->total() === 12)
            ->set('perPage', 20)
            ->assertViewHas('available', fn ($available) => $available->count() === 12 && $available->total() === 12)
            ->set('selected', $posts->take(2)->pluck('id')->all())
            ->call('addFeatured')
            ->assertSet('selected', [])
            ->assertHasNoErrors();

        $this->assertSame(2, Post::where('is_featured', true)->count());
    }

    public function test_category_delete_uses_confirmation_modal(): void
    {
        $category = $this->category();
        $post = $this->makePost($category);

        Livewire::actingAs($this->editor())->test(CategoryIndex::class)
            ->call('requestDelete', $category->id)
            ->assertSet('pendingDeleteId', $category->id)
            ->assertSet('pendingDeleteName', 'Tin hoạt động')
            ->assertSet('pendingDeletePostsCount', 1)
            ->assertSee('Xóa danh mục tin tức?')
            ->assertSee('Chưa phân loại')
            ->assertSee('Có, xóa danh mục')
            ->call('confirmDelete')
            ->assertSet('pendingDeleteId', null)
            ->assertHasNoErrors();

        $this->assertSoftDeleted('post_categories', ['id' => $category->id]);
        $this->assertNull($post->refresh()->post_category_id);
    }

    public function test_category_translations_are_published_automatically_without_status_field(): void
    {
        Livewire::actingAs($this->editor())->test(CategoryForm::class)
            ->set('name.vi', 'Tin thị trường')
            ->set('slug.vi', 'tin-thi-truong')
            ->set('name.en', 'Market news')
            ->set('slug.en', 'market-news')
            ->call('save')
            ->assertHasNoErrors();

        $category = PostCategory::where('slug->vi', 'tin-thi-truong')->firstOrFail();
        $this->assertSame('published', $category->getTranslation('translation_status', 'vi', false));
        $this->assertSame('published', $category->getTranslation('translation_status', 'en', false));
        $this->assertDatabaseHas('localized_routes', [
            'routeable_type' => PostCategory::class,
            'routeable_id' => $category->id,
            'locale' => 'en',
            'status' => 'published',
        ]);
    }

    public function test_category_index_is_paginated_and_page_size_can_be_changed(): void
    {
        $user = $this->editor();
        foreach (range(1, 12) as $index) {
            PostCategory::create([
                'name' => ['vi' => "Danh mục {$index}"],
                'slug' => ['vi' => "danh-muc-{$index}"],
                'is_active' => true,
            ]);
        }

        Livewire::actingAs($user)->test(CategoryIndex::class)
            ->assertSet('perPage', 10)
            ->assertViewHas('categories', fn ($categories) => $categories->count() === 10 && $categories->total() === 12)
            ->set('perPage', 20)
            ->assertViewHas('categories', fn ($categories) => $categories->count() === 12 && $categories->total() === 12);
    }

    public function test_news_general_settings_are_saved_with_multilingual_seo_and_display_options(): void
    {
        Livewire::actingAs($this->editor())->test(Settings::class)
            ->assertDontSee('Từ khóa SEO')
            ->set('page_title', ['vi' => 'Tin tức', 'en' => 'News', 'zh' => '新闻'])
            ->set('description.en', 'Latest updates from IDI Seafood')
            ->set('meta_keywords.en', 'IDI Seafood, company news')
            ->set('og_title.zh', 'IDI Seafood 新闻')
            ->set('items_per_page', 18)
            ->set('thumbnail_height', 220)
            ->set('show_author', false)
            ->set('max_upload_size', 15)
            ->set('lazy_load_images', false)
            ->set('moderate_comments', false)
            ->set('module_enabled', false)
            ->call('save')
            ->assertHasNoErrors();

        $module = DB::table('modules')->where('code', 'news')->first();
        $this->assertFalse((bool) $module->is_active);
        $this->assertSame('Latest updates from IDI Seafood', json_decode($module->description, true)['en']);
        $this->assertSame('IDI Seafood 新闻', json_decode($module->og_title, true)['zh']);
        $this->assertDatabaseHas('module_settings', [
            'module_id' => $module->id,
            'setting_key' => 'items_per_page',
            'setting_value' => '18',
        ]);
        $this->assertDatabaseHas('module_settings', [
            'module_id' => $module->id,
            'setting_key' => 'show_author',
            'setting_value' => 'false',
        ]);
        $this->assertDatabaseHas('module_settings', [
            'module_id' => $module->id,
            'setting_key' => 'max_upload_size',
            'setting_value' => '15',
        ]);
        $this->assertDatabaseHas('module_settings', [
            'module_id' => $module->id,
            'setting_key' => 'lazy_load_images',
            'setting_value' => 'false',
        ]);
        $this->assertSame(
            'IDI Seafood, company news',
            json_decode(DB::table('module_settings')->where('module_id', $module->id)->where('setting_key', 'meta_keywords')->value('setting_value'), true)['en']
        );
        $this->getJson('/api/news')->assertNotFound();
    }

    public function test_user_without_news_permission_is_forbidden(): void
    {
        $this->actingAs(User::factory()->create())->get('/admin/news')->assertForbidden();
        $this->actingAs(User::factory()->create())->get('/admin/news/settings')->assertForbidden();
    }

    private function editor(): User
    {
        foreach ([['vi', 'Vietnamese', 'Tiếng Việt'], ['en', 'English', 'English'], ['zh', 'Chinese', '中文']] as $index => [$code, $name, $native]) {
            DB::table('locales')->updateOrInsert(['code' => $code], [
                'name' => $name, 'native_name' => $native, 'direction' => 'ltr',
                'is_default' => $code === 'vi', 'is_active' => true, 'sort_order' => $index,
                'created_at' => now(), 'updated_at' => now(),
            ]);
        }
        $user = User::factory()->create();
        $user->givePermissionTo(Permission::findOrCreate('posts.manage', 'web'));

        return $user;
    }

    private function category(): PostCategory
    {
        return PostCategory::create([
            'name' => ['vi' => 'Tin hoạt động', 'en' => 'Company news', 'zh' => '公司新闻'],
            'slug' => ['vi' => 'tin-hoat-dong', 'en' => 'company-news', 'zh' => 'gongsi-xinwen'],
            'translation_status' => ['vi' => 'published', 'en' => 'published', 'zh' => 'published'],
            'is_active' => true,
        ]);
    }

    private function makePost(PostCategory $category): Post
    {
        return Post::create([
            'post_category_id' => $category->id,
            'title' => ['vi' => 'IDI mở nhà máy mới', 'en' => 'IDI opens a new factory', 'zh' => 'IDI新工厂'],
            'slug' => ['vi' => 'idi-mo-nha-may-moi', 'en' => 'idi-new-factory', 'zh' => 'idi-xin-gongchang'],
            'translation_status' => ['vi' => 'published', 'en' => 'published', 'zh' => 'draft'],
            'is_active' => true,
        ]);
    }
}
