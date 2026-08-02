<?php

namespace Tests\Feature;

use App\Livewire\Admin\ProductCategories\Form as ProductCategoryForm;
use App\Livewire\Admin\ProductCategories\Index as ProductCategoryIndex;
use App\Models\ProductCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class ProductCategoryManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_product_editor_can_view_create_and_edit_category_pages(): void
    {
        $user = $this->productEditor();
        $category = $this->category();

        $this->actingAs($user)->get('/admin/product-categories')->assertOk()
            ->assertSee('Danh mục sản phẩm')
            ->assertSee('Cá Fillet')
            ->assertSee('Thêm danh mục')
            ->assertSee('page-heading card page-heading-card', false);
        $this->actingAs($user)->get('/admin/product-categories/create')->assertOk()->assertSee('Thêm danh mục sản phẩm');
        $this->actingAs($user)->get("/admin/product-categories/{$category->id}/edit")->assertOk()->assertSee('Sửa danh mục');
    }

    public function test_category_can_be_created_and_updated_with_livewire(): void
    {
        $user = $this->productEditor();

        Livewire::actingAs($user)->test(ProductCategoryForm::class)
            ->set('code', 'ca-cat-khuc')
            ->set('name', ['vi' => 'Cá cắt khúc', 'en' => 'Fish steaks', 'zh' => ''])
            ->set('slug', ['vi' => 'Cá cắt khúc', 'en' => 'fish-steaks', 'zh' => ''])
            ->set('description.vi', 'Danh mục cá cắt khúc.')
            ->set('sort_order', 7)->set('is_active', true)
            ->call('save')->assertHasNoErrors();

        $category = ProductCategory::where('code', 'CA-CAT-KHUC')->firstOrFail();
        $this->assertSame('ca-cat-khuc', $category->getTranslation('slug', 'vi'));

        Livewire::actingAs($user)->test(ProductCategoryForm::class, ['category' => $category])
            ->set('name.vi', 'Cá cắt khúc cập nhật')
            ->set('slug.vi', 'ca-cat-khuc-cap-nhat')
            ->set('sort_order', 3)->set('is_active', false)
            ->call('save')->assertHasNoErrors();

        $category->refresh();
        $this->assertSame('Cá cắt khúc cập nhật', $category->getTranslation('name', 'vi'));
        $this->assertFalse($category->is_active);
        $this->assertSame(3, $category->sort_order);
    }

    public function test_category_create_button_navigates_to_full_page_form(): void
    {
        Livewire::actingAs($this->productEditor())
            ->test(ProductCategoryIndex::class)
            ->assertSeeHtml('href="'.route('admin.product-categories.create').'"');
    }

    public function test_category_can_be_hidden_deleted_and_restored_with_livewire(): void
    {
        $user = $this->productEditor();
        $category = $this->category();

        Livewire::actingAs($user)->test(ProductCategoryIndex::class)->call('toggleVisibility', $category->id)->assertHasNoErrors();
        $this->assertFalse($category->fresh()->is_active);

        Livewire::actingAs($user)->test(ProductCategoryIndex::class)->call('delete', $category->id)->assertHasNoErrors();
        $this->assertSoftDeleted('product_categories', ['id' => $category->id]);

        Livewire::actingAs($user)->test(ProductCategoryIndex::class)->call('restore', $category->id)->assertHasNoErrors();
        $this->assertNotSoftDeleted('product_categories', ['id' => $category->id]);
    }

    public function test_bulk_actions_update_selected_categories_only(): void
    {
        $user = $this->productEditor();
        $first = $this->category();
        $second = ProductCategory::create(['code' => 'WHOLE-FISH', 'name' => ['vi' => 'Cá nguyên con'], 'slug' => ['vi' => 'ca-nguyen-con'], 'sort_order' => 8, 'is_active' => true]);

        Livewire::actingAs($user)->test(ProductCategoryIndex::class)
            ->set('selected', [$first->id])->call('bulk', 'hide')->assertHasNoErrors();
        $this->assertFalse($first->fresh()->is_active);
        $this->assertTrue($second->fresh()->is_active);

        Livewire::actingAs($user)->test(ProductCategoryIndex::class)
            ->set('selected', [$first->id, $second->id])
            ->set('sortOrders', [$first->id => 2, $second->id => 1])
            ->call('bulk', 'reorder')->assertHasNoErrors();
        $this->assertSame(2, $first->fresh()->sort_order);
        $this->assertSame(1, $second->fresh()->sort_order);
    }

    private function productEditor(): User
    {
        $permission = Permission::findOrCreate('products.manage', 'web');
        $user = User::factory()->create();
        $user->givePermissionTo($permission);

        return $user;
    }

    private function category(): ProductCategory
    {
        return ProductCategory::create(['code' => 'FILLETS', 'name' => ['vi' => 'Cá Fillet'], 'slug' => ['vi' => 'ca-fillets'], 'sort_order' => 5, 'is_active' => true]);
    }
}
