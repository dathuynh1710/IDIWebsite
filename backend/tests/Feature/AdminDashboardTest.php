<?php

namespace Tests\Feature;

use App\Livewire\Admin\Products\Form as ProductForm;
use App\Livewire\Admin\Products\Index as ProductIndex;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class AdminDashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_from_admin(): void
    {
        $this->get('/admin')->assertRedirect('/login');
    }

    public function test_authenticated_user_can_view_dashboard(): void
    {
        $this->actingAs(User::factory()->create())->get('/admin')
            ->assertOk()->assertSee('Bảng điều khiển')->assertSee('IDI Seafood CMS');
    }

    public function test_user_with_product_permission_can_render_index_create_and_edit_pages(): void
    {
        $user = $this->productEditor();
        $category = $this->category();
        $product = $this->product($category);

        $this->actingAs($user)->get('/admin/products')->assertOk()->assertSee($product->sku)->assertSee('Thêm sản phẩm');
        $this->actingAs($user)->get('/admin/products/create')->assertOk()->assertSee('Thêm sản phẩm mới')->assertSee('Tiếng Việt')->assertSee('English')->assertSee('中文');
        $this->actingAs($user)->get("/admin/products/{$product->id}/edit")->assertOk()->assertSee("Sửa sản phẩm #{$product->sku}");
    }

    public function test_livewire_validation_preserves_component_state(): void
    {
        Livewire::actingAs($this->productEditor())
            ->test(ProductForm::class)
            ->set('sku', 'IDI-INVALID-001')
            ->set('title.vi', '')
            ->set('slug.vi', '')
            ->call('save')
            ->assertHasErrors(['title.vi', 'slug.vi'])
            ->assertSet('sku', 'IDI-INVALID-001');
    }

    public function test_product_create_modal_opens_without_navigation(): void
    {
        Livewire::actingAs($this->productEditor())
            ->test(ProductIndex::class)
            ->call('openCreateModal')
            ->assertSet('showFormModal', true)
            ->assertSee('Biểu mẫu sản phẩm');
    }

    public function test_user_without_product_permission_is_forbidden(): void
    {
        $this->actingAs(User::factory()->create())->get('/admin/products')->assertForbidden();
    }

    public function test_product_can_be_created_with_livewire_multilingual_content(): void
    {
        $user = $this->productEditor();
        $category = $this->category();

        Livewire::actingAs($user)
            ->test(ProductForm::class)
            ->set('sku', 'IDI-PAN-NEW')
            ->set('product_category_id', $category->id)
            ->set('title', ['vi' => 'Cá tra phi lê', 'en' => 'Pangasius fillet', 'zh' => '巴沙鱼柳'])
            ->set('slug', ['vi' => 'ca-tra-phi-le', 'en' => 'pangasius-fillet', 'zh' => 'basha-yu-liu'])
            ->set('short_description.vi', 'Sản phẩm chất lượng cao')
            ->set('content.vi', '<p>Nội dung an toàn</p><script>alert(1)</script>')
            ->set('translation_status', ['vi' => 'draft', 'en' => 'translating', 'zh' => 'draft'])
            ->set('sort_order', 1)
            ->set('is_featured', true)
            ->set('is_active', true)
            ->call('save')
            ->assertHasNoErrors();

        $product = Product::where('sku', 'IDI-PAN-NEW')->firstOrFail();
        $this->assertSame('Pangasius fillet', $product->getTranslation('title', 'en'));
        $this->assertStringNotContainsString('<script', $product->getTranslation('content', 'vi'));
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
        return ProductCategory::create([
            'code' => 'PANGASIUS',
            'name' => ['vi' => 'Cá tra', 'en' => 'Pangasius', 'zh' => '巴沙鱼'],
            'slug' => ['vi' => 'ca-tra', 'en' => 'pangasius', 'zh' => 'basha-yu'],
            'sort_order' => 0,
            'is_active' => true,
        ]);
    }

    private function product(ProductCategory $category): Product
    {
        return Product::create([
            'product_category_id' => $category->id,
            'sku' => 'IDI-PAN-001',
            'title' => ['vi' => 'Cá tra phi lê', 'en' => 'Pangasius fillet', 'zh' => '巴沙鱼柳'],
            'slug' => ['vi' => 'ca-tra-phi-le', 'en' => 'pangasius-fillet', 'zh' => 'basha-yu-liu'],
            'translation_status' => ['vi' => 'published', 'en' => 'published', 'zh' => 'review'],
            'locale_published_at' => [],
            'sort_order' => 0,
            'is_featured' => true,
            'is_active' => true,
        ]);
    }
}
