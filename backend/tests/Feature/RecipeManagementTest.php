<?php

namespace Tests\Feature;

use App\Livewire\Admin\Recipes\Form;
use App\Livewire\Admin\Recipes\Index;
use App\Livewire\Admin\Recipes\Settings;
use App\Models\Recipe;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class RecipeManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_recipe_editor_can_open_all_management_pages_in_three_languages(): void
    {
        $user = $this->recipeEditor();
        $recipe = $this->recipe();

        $this->actingAs($user)->get('/admin/recipes')
            ->assertOk()
            ->assertSee('Quản lý Recipes')
            ->assertSee('Tiếng Việt')
            ->assertSee('English')
            ->assertSee('中文')
            ->assertSee('wire:model.live="perPage"', false)
            ->assertDontSee('RECIPE_GRILLED');
        $this->actingAs($user)->get('/admin/recipes/settings')
            ->assertOk()
            ->assertSee('Cấu hình Recipes')
            ->assertSee('English')
            ->assertSee('中文')
            ->assertDontSee('Áp dụng cho toàn bộ module Recipes')
            ->assertDontSee('Số công thức mỗi trang');
        $this->actingAs($user)->get('/admin/recipes/create')
            ->assertOk()
            ->assertSee('Thêm Recipe mới')
            ->assertSee('Mô tả trái')
            ->assertSee('Mô tả phải')
            ->assertDontSee('Khẩu phần')
            ->assertDontSee('Độ khó')
            ->assertSee('ckeditor5-textarea', false)
            ->assertDontSee('Trạng thái bản dịch')
            ->assertDontSee('Sản phẩm liên quan');
        $this->actingAs($user)->get("/admin/recipes/{$recipe->id}/edit")->assertOk()->assertSee('Cập nhật Recipe');
        $this->actingAs($user)->get("/admin/recipes/{$recipe->id}/preview?locale=en")->assertOk()->assertSee('Grilled fish');
    }

    public function test_recipe_can_be_created_with_multilingual_two_column_content_and_routes(): void
    {
        Livewire::actingAs($this->recipeEditor())->test(Form::class)
            ->set('code', 'RECIPE_FISH_SOUP')
            ->set('title', ['vi' => 'Canh cá', 'en' => 'Fish soup', 'zh' => '鱼汤'])
            ->set('slug', ['vi' => 'canh-ca', 'en' => 'fish-soup', 'zh' => 'yu-tang'])
            ->set('summary.vi', 'Món canh thanh nhẹ.')
            ->set('content_left.vi', '<h2>Thành phần</h2><p>Cá tra</p><script>alert(1)</script>')
            ->set('content_right', ['vi' => '<h2>Cách làm</h2><p>Nấu cá chín.</p>', 'en' => '<p>Cook the fish.</p>', 'zh' => '<p>把鱼煮熟。</p>'])
            ->set('published_at', '2026-08-20T09:05')
            ->call('save')
            ->assertHasNoErrors();

        $recipe = Recipe::where('code', 'RECIPE_FISH_SOUP')->firstOrFail();
        $this->assertSame('Fish soup', $recipe->getTranslation('title', 'en'));
        $this->assertStringContainsString('Cá tra', $recipe->getTranslation('content_left', 'vi'));
        $this->assertStringNotContainsString('<script', $recipe->getTranslation('content_left', 'vi'));
        $this->assertStringContainsString('Cook the fish.', $recipe->getTranslation('content_right', 'en'));
        $this->assertSame('published', $recipe->getTranslation('translation_status', 'en'));
        $this->assertStringStartsWith('2026-08-20T09:05', $recipe->getTranslation('locale_published_at', 'vi'));
        $this->assertDatabaseHas('localized_routes', [
            'routeable_type' => Recipe::class,
            'routeable_id' => $recipe->id,
            'locale' => 'vi',
            'full_path' => '/vi/cong-thuc/canh-ca',
            'status' => 'published',
        ]);
        $this->assertDatabaseHas('localized_routes', [
            'routeable_id' => $recipe->id,
            'locale' => 'zh',
            'full_path' => '/zh/shipu/yu-tang',
        ]);
    }

    public function test_recipe_supports_visibility_duplicate_delete_and_bulk_order(): void
    {
        $user = $this->recipeEditor();
        $first = $this->recipe();
        $second = Recipe::create([
            'code' => 'RECIPE_STEAMED',
            'title' => ['vi' => 'Cá hấp'],
            'slug' => ['vi' => 'ca-hap'],
            'translation_status' => ['vi' => 'draft', 'en' => 'draft', 'zh' => 'draft'],
            'sort_order' => 8,
            'is_active' => true,
        ]);

        Livewire::actingAs($user)->test(Index::class)
            ->call('toggleVisibility', $first->id)
            ->call('duplicate', $first->id)
            ->assertHasNoErrors();
        $this->assertFalse($first->fresh()->is_active);
        $this->assertSame(3, Recipe::count());

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
            ->assertSee('Xóa Recipe?')
            ->call('confirmDelete')
            ->assertSet('pendingDeleteId', null)
            ->assertHasNoErrors();
        $this->assertSoftDeleted('recipes', ['id' => $first->id]);
    }

    public function test_recipe_settings_are_saved_for_all_languages(): void
    {
        Livewire::actingAs($this->recipeEditor())->test(Settings::class)
            ->set('page_title', ['vi' => 'Công thức', 'en' => 'Recipes', 'zh' => '食谱'])
            ->set('description.en', 'Recipes from IDI Seafood')
            ->set('seo_title.zh', 'IDI Seafood 食谱')
            ->set('items_per_page', 16)
            ->call('save')
            ->assertHasNoErrors();

        $module = DB::table('modules')->where('code', 'recipes')->first();
        $this->assertSame('Recipes', json_decode($module->page_title, true)['en']);
        $this->assertDatabaseHas('module_settings', [
            'module_id' => $module->id,
            'setting_key' => 'items_per_page',
            'setting_value' => '16',
        ]);
    }

    public function test_user_without_recipe_permission_is_forbidden(): void
    {
        $this->actingAs(User::factory()->create())->get('/admin/recipes')->assertForbidden();
        $this->actingAs(User::factory()->create())->get('/admin/recipes/settings')->assertForbidden();
    }

    private function recipeEditor(): User
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
        $permission = Permission::findOrCreate('recipes.manage', 'web');
        $user = User::factory()->create();
        $user->givePermissionTo($permission);

        return $user;
    }

    private function recipe(): Recipe
    {
        $recipe = Recipe::create([
            'code' => 'RECIPE_GRILLED',
            'title' => ['vi' => 'Cá nướng', 'en' => 'Grilled fish', 'zh' => '烤鱼'],
            'slug' => ['vi' => 'ca-nuong', 'en' => 'grilled-fish', 'zh' => 'kao-yu'],
            'summary' => ['vi' => 'Món cá nướng.'],
            'translation_status' => ['vi' => 'published', 'en' => 'published', 'zh' => 'review'],
            'sort_order' => 2,
            'content_left' => ['vi' => '<p>Cá 500 g</p>'],
            'content_right' => ['vi' => '<p>Nướng cá.</p>', 'en' => '<p>Grill the fish.</p>'],
            'is_active' => true,
        ]);

        return $recipe;
    }
}
