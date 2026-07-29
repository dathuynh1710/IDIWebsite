<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
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
        $this->actingAs(User::factory()->create())
            ->get('/admin')
            ->assertOk()
            ->assertSee('Bảng điều khiển')
            ->assertSee('IDI Seafood CMS');
    }

    public function test_user_with_product_permission_can_render_index_create_and_edit_pages(): void
    {
        $user = $this->productEditor();
        $category = $this->category();
        $product = $this->product($category);

        $this->actingAs($user)->get('/admin/products')
            ->assertOk()
            ->assertSee($product->sku)
            ->assertSee('Thêm sản phẩm');

        $this->actingAs($user)->get('/admin/products/create')
            ->assertOk()
            ->assertSee('Thêm sản phẩm mới')
            ->assertSee('Tiếng Việt')
            ->assertSee('English')
            ->assertSee('中文');

        $this->actingAs($user)->get("/admin/products/{$product->id}/edit")
            ->assertOk()
            ->assertSee("Sửa sản phẩm #{$product->sku}")
            ->assertDontSee('Không tìm thấy sản phẩm');
    }

    public function test_validation_errors_and_old_input_are_preserved(): void
    {
        $user = $this->productEditor();

        $response = $this->actingAs($user)
            ->from('/admin/products/create')
            ->post('/admin/products', [
                'sku' => 'IDI-INVALID-001',
                'title' => ['vi' => ''],
                'slug' => ['vi' => ''],
                'translation_status' => ['vi' => 'draft', 'en' => 'draft', 'zh' => 'draft'],
                'sort_order' => 0,
                'is_featured' => 0,
                'is_active' => 1,
            ]);

        $response->assertRedirect('/admin/products/create')
            ->assertSessionHasErrors(['title.vi', 'slug.vi'])
            ->assertSessionHasInput('sku', 'IDI-INVALID-001');
    }

    public function test_user_without_product_permission_is_forbidden(): void
    {
        $this->actingAs(User::factory()->create())
            ->get('/admin/products')
            ->assertForbidden();
    }

    public function test_product_can_be_created_with_multilingual_content(): void
    {
        $user = $this->productEditor();
        $category = $this->category();

        $response = $this->actingAs($user)->post('/admin/products', [
            'sku' => 'IDI-PAN-NEW',
            'product_category_id' => $category->id,
            'title' => ['vi' => 'Cá tra phi lê', 'en' => 'Pangasius fillet', 'zh' => '巴沙鱼柳'],
            'slug' => ['vi' => 'ca-tra-phi-le', 'en' => 'pangasius-fillet', 'zh' => 'basha-yu-liu'],
            'short_description' => ['vi' => 'Sản phẩm chất lượng cao'],
            'content' => ['vi' => '<p>Nội dung an toàn</p><script>alert(1)</script>'],
            'translation_status' => ['vi' => 'draft', 'en' => 'translating', 'zh' => 'draft'],
            'locale_published_at' => [],
            'sort_order' => 1,
            'is_featured' => 1,
            'is_active' => 1,
        ]);

        $product = Product::where('sku', 'IDI-PAN-NEW')->firstOrFail();
        $response->assertRedirect(route('admin.products.edit', $product));
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
