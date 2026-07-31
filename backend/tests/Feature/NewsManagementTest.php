<?php

namespace Tests\Feature;

use App\Livewire\Admin\News\PostForm;
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
    }

    public function test_post_is_saved_in_three_languages_with_safe_content_and_routes(): void
    {
        Storage::fake('public');
        $category = $this->category();

        Livewire::actingAs($this->editor())->test(PostForm::class)
            ->set('post_category_id', $category->id)
            ->set('code', 'NEWS_EXPORT_2026')
            ->set('featured_image', UploadedFile::fake()->createWithContent(
                'news.png',
                base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVQIHWP4z8DwHwAFgAI/ScLzWQAAAABJRU5ErkJggg==')
            ))
            ->set('title', ['vi' => 'IDI mở rộng xuất khẩu', 'en' => 'IDI expands exports', 'zh' => 'IDI扩大出口'])
            ->set('slug', ['vi' => 'idi-mo-rong-xuat-khau', 'en' => 'idi-expands-exports', 'zh' => 'idi-kuoda-chukou'])
            ->set('content.vi', '<p>Nội dung an toàn</p><script>alert(1)</script>')
            ->set('translation_status', ['vi' => 'published', 'en' => 'published', 'zh' => 'draft'])
            ->call('save')
            ->assertHasNoErrors();

        $post = Post::where('code', 'NEWS_EXPORT_2026')->firstOrFail();
        $this->assertSame('IDI expands exports', $post->getTranslation('title', 'en'));
        $this->assertStringNotContainsString('<script', $post->getTranslation('content', 'vi'));
        $this->assertDatabaseHas('localized_routes', [
            'routeable_type' => Post::class, 'routeable_id' => $post->id,
            'locale' => 'zh', 'full_path' => '/zh/xinwen/idi-kuoda-chukou',
        ]);
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
