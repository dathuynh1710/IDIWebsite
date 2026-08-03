<?php

namespace Tests\Feature;

use App\Livewire\Admin\AboutPages\Form;
use App\Livewire\Admin\AboutPages\Index;
use App\Livewire\Admin\AboutPages\Settings;
use App\Models\Page;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class AboutManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_page_editor_can_open_all_about_management_pages_in_three_languages(): void
    {
        $user = $this->pageEditor();
        $page = $this->aboutPage();

        $this->actingAs($user)->get('/admin/about')
            ->assertOk()->assertSee('Quản lý giới thiệu')->assertSee('Tiếng Việt')->assertSee('English')->assertSee('中文')
            ->assertSee('Mở trang xem trước')->assertSee('Ẩn khỏi website');
        $this->actingAs($user)->get('/admin/about/settings')
            ->assertOk()->assertSee('Cấu hình giới thiệu')->assertSee('English')->assertSee('中文');
        $this->actingAs($user)->get('/admin/about/create')
            ->assertOk()->assertSee('Thêm giới thiệu mới')->assertSee('English')->assertSee('中文')
            ->assertSee('ckeditor5-textarea', false)->assertDontSee('Chia sẻ Facebook / Open Graph')
            ->assertDontSee('Trạng thái bản dịch');
        $this->actingAs($user)->get("/admin/about/{$page->id}/edit")
            ->assertOk()->assertSee('Cập nhật giới thiệu');
        $this->actingAs($user)->get("/admin/about/{$page->id}/preview?locale=en")
            ->assertOk()->assertSee('About IDI');
    }

    public function test_about_page_can_be_created_with_multilingual_content_and_seo_routes(): void
    {
        Livewire::actingAs($this->pageEditor())->test(Form::class)
            ->set('template', 'about-history')
            ->set('code', 'ABOUT_HISTORY')
            ->set('title', ['vi' => 'Lịch sử phát triển', 'en' => 'Our history', 'zh' => '发展历程'])
            ->set('slug', ['vi' => 'lich-su-phat-trien', 'en' => 'our-history', 'zh' => 'fazhan-licheng'])
            ->set('summary.vi', 'Hành trình phát triển của IDI.')
            ->set('content.vi', '<p>Nội dung an toàn</p><script>alert(1)</script>')
            ->set('meta_keywords.vi', 'IDI Seafood, lịch sử phát triển')
            ->set('sort_order', 2)
            ->set('is_active', true)
            ->call('save')
            ->assertHasNoErrors();

        $page = Page::where('code', 'ABOUT_HISTORY')->firstOrFail();
        $this->assertSame('Our history', $page->getTranslation('title', 'en'));
        $this->assertSame('IDI Seafood, lịch sử phát triển', $page->getTranslation('meta_keywords', 'vi'));
        $this->assertStringNotContainsString('<script', $page->getTranslation('content', 'vi'));
        $this->assertDatabaseHas('localized_routes', [
            'routeable_type' => Page::class,
            'routeable_id' => $page->id,
            'locale' => 'vi',
            'full_path' => '/vi/gioi-thieu/lich-su-phat-trien',
            'status' => 'published',
        ]);
        $this->assertDatabaseHas('localized_routes', [
            'routeable_id' => $page->id,
            'locale' => 'zh',
            'full_path' => '/zh/guanyu/fazhan-licheng',
        ]);
    }

    public function test_about_page_supports_visibility_duplicate_delete_restore_and_bulk_order(): void
    {
        $user = $this->pageEditor();
        $first = $this->aboutPage();
        $second = Page::create([
            'template' => 'about-values',
            'code' => 'ABOUT_VALUES',
            'title' => ['vi' => 'Giá trị cốt lõi'],
            'slug' => ['vi' => 'gia-tri-cot-loi'],
            'sort_order' => 8,
            'is_active' => true,
        ]);

        Livewire::actingAs($user)->test(Index::class)
            ->call('toggleVisibility', $first->id)
            ->call('duplicate', $first->id)
            ->assertHasNoErrors();
        $this->assertFalse($first->fresh()->is_active);
        $this->assertSame(3, Page::query()->about()->count());

        Livewire::actingAs($user)->test(Index::class)
            ->set('selected', [$first->id, $second->id])
            ->set('sortOrders', [$first->id => 5, $second->id => 1])
            ->call('bulk', 'reorder')
            ->assertHasNoErrors();
        $this->assertSame(5, $first->fresh()->sort_order);
        $this->assertSame(1, $second->fresh()->sort_order);

        Livewire::actingAs($user)->test(Index::class)
            ->call('requestDelete', $first->id)
            ->assertSet('pendingDeleteId', $first->id)
            ->assertSee('Xóa nội dung giới thiệu?')
            ->call('confirmDelete')
            ->assertSet('pendingDeleteId', null)
            ->assertHasNoErrors();
        $this->assertSoftDeleted('pages', ['id' => $first->id]);
        Livewire::actingAs($user)->test(Index::class)->call('restore', $first->id)->assertHasNoErrors();
        $this->assertNotSoftDeleted('pages', ['id' => $first->id]);
    }

    public function test_about_settings_are_saved_for_all_languages(): void
    {
        $user = $this->pageEditor();

        Livewire::actingAs($user)->test(Settings::class)
            ->set('page_title', ['vi' => 'Giới thiệu', 'en' => 'About us', 'zh' => '关于我们'])
            ->set('description.en', 'About IDI Seafood')
            ->set('seo_title.zh', '关于 IDI Seafood')
            ->call('save')
            ->assertHasNoErrors();

        $module = DB::table('modules')->where('code', 'about')->first();
        $this->assertSame('About us', json_decode($module->page_title, true)['en']);
        $this->assertDatabaseMissing('module_settings', ['module_id' => $module->id]);
    }

    public function test_about_index_uses_default_pagination_and_can_change_page_size(): void
    {
        $user = $this->pageEditor();
        foreach (range(1, 7) as $index) {
            Page::create([
                'template' => 'about',
                'title' => ['vi' => "Giới thiệu {$index}"],
                'slug' => ['vi' => "gioi-thieu-{$index}"],
                'sort_order' => $index,
                'is_active' => true,
            ]);
        }

        Livewire::actingAs($user)->test(Index::class)
            ->assertSet('perPage', 10)
            ->assertViewHas('pages', fn ($pages) => $pages->count() === 7 && $pages->total() === 7)
            ->set('perPage', 5)
            ->assertViewHas('pages', fn ($pages) => $pages->count() === 5 && $pages->total() === 7);
    }

    public function test_user_without_page_permission_is_forbidden(): void
    {
        $this->actingAs(User::factory()->create())->get('/admin/about')->assertForbidden();
        $this->actingAs(User::factory()->create())->get('/admin/about/settings')->assertForbidden();
    }

    private function pageEditor(): User
    {
        foreach ([
            ['code' => 'vi', 'name' => 'Vietnamese', 'native_name' => 'Tiếng Việt', 'sort_order' => 0, 'is_default' => true],
            ['code' => 'en', 'name' => 'English', 'native_name' => 'English', 'sort_order' => 1, 'is_default' => false],
            ['code' => 'zh', 'name' => 'Chinese', 'native_name' => '中文', 'sort_order' => 2, 'is_default' => false],
        ] as $locale) {
            DB::table('locales')->updateOrInsert(['code' => $locale['code']], $locale + [
                'direction' => 'ltr',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
        $permission = Permission::findOrCreate('pages.manage', 'web');
        $user = User::factory()->create();
        $user->givePermissionTo($permission);

        return $user;
    }

    private function aboutPage(): Page
    {
        return Page::create([
            'template' => 'about',
            'code' => 'ABOUT_MESSAGE',
            'title' => ['vi' => 'Thông điệp công ty', 'en' => 'About IDI', 'zh' => '公司简介'],
            'slug' => ['vi' => 'thong-diep-cong-ty', 'en' => 'about-idi', 'zh' => 'gongsi-jianjie'],
            'summary' => ['vi' => 'Thông điệp từ công ty.'],
            'content' => ['vi' => '<p>Nội dung giới thiệu.</p>'],
            'sort_order' => 0,
            'is_active' => true,
        ]);
    }
}
