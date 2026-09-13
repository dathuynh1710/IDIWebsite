<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\ProductCategory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class ProductsApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_seeded_catalog_is_published_to_the_frontend_api(): void
    {
        $this->seed();

        $response = $this->getJson('/api/products?locale=vi');

        $response
            ->assertOk()
            ->assertJsonPath('total', 14)
            ->assertJsonCount(4, 'categories')
            ->assertJsonPath('categories.0.slug', 'pangasius-fillet')
            ->assertJsonPath('categories.0.name', 'Cá Fillet')
            ->assertJsonPath('categories.0.image', config('app.url').'/assets/media/products/dm2.jpg')
            ->assertJsonCount(6, 'categories.0.products')
            ->assertJsonPath('categories.0.products.0.name', 'Cá Fillet, Tạo Hình Sạch')
            ->assertJsonPath('categories.0.products.0.sortOrder', 14)
            ->assertJsonPath('categories.0.products.0.sizes.0', '60g-120g')
            ->assertJsonPath('categories.0.products.0.productSpecification', 'Phi lê có da, không xương, tách da, tách mỡ')
            ->assertJsonPath('categories.0.products.0.presentation.0', 'Đông lạnh nhanh riêng lẻ')
            ->assertJsonPath('categories.0.products.0.presentation.1', 'Đông lạnh khối')
            ->assertJsonPath('categories.0.products.0.packaging', 'Bán sỉ đóng gói trong túi PE trơn, hoặc bán lẻ đóng gói trong túi in, gói hút chân không, v.v.')
            ->assertJsonPath('categories.0.products.0.nutrition.calories', '82.7 Kcal')
            ->assertJsonPath('categories.0.products.0.nutrition.protein', '15.50g')
            ->assertJsonPath('categories.0.products.0.nutrition.fat', '1.86g')
            ->assertJsonPath('categories.0.products.0.nutrition.saturated_fat', '0.66g')
            ->assertJsonPath('categories.0.products.0.image', config('app.url').'/assets/media/products/dm2.jpg')
            ->assertJsonPath('categories.3.name', 'Các sản phẩm khác')
            ->assertJsonCount(3, 'categories.3.products');

        $this->assertSame(14, Product::where('is_active', true)->count());
        $this->assertSame(4, ProductCategory::where('is_active', true)->count());
        $this->assertFalse(Schema::hasColumn('products', 'scientific_name'));
    }

    public function test_catalog_can_be_filtered_and_a_product_can_be_opened_by_slug(): void
    {
        $this->seed();

        $this->getJson('/api/products?locale=vi&category=whole-fish')
            ->assertOk()
            ->assertJsonPath('total', 2)
            ->assertJsonCount(1, 'categories')
            ->assertJsonPath('categories.0.name', 'Cá Nguyên Con');

        $this->getJson('/api/products/uc-ca-tra?locale=vi')
            ->assertOk()
            ->assertJsonPath('data.name', 'Ức Cá Tra')
            ->assertJsonPath('data.productSpecification', 'Phi lê có da, không xương, tách da, tách mỡ')
            ->assertJsonMissingPath('data.storageTemperature')
            ->assertJsonMissingPath('data.certifications')
            ->assertJsonMissingPath('data.origin')
            ->assertJsonMissingPath('data.shelfLife');

        $this->getJson('/api/products/uc-ca-tra?locale=zh-CN')
            ->assertOk()
            ->assertJsonPath('data.name', 'Pangasius Belly')
            ->assertJsonPath('data.productSpecification', '带皮、去骨、去腹肉、去脂鱼片')
            ->assertJsonPath('data.presentation.0', '单体速冻')
            ->assertJsonPath('data.packaging', '批发采用普通 PE 袋包装，零售采用印刷袋、真空包装等。');

        $this->getJson('/api/products/uc-ca-tra?locale=en')
            ->assertOk()
            ->assertJsonPath('data.productSpecification', 'Skin-on, Boneless, Belly-off, Fat-off Fillets')
            ->assertJsonPath('data.presentation.0', 'Individually Quick Frozen')
            ->assertJsonPath('data.packaging', 'Wholesales packaging in plain PE bags, or Retail packaging in printed bags, vacuum pack, etc.');
    }
}
