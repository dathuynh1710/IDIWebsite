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
            ->assertJsonPath('categories.0.image', 'https://idiseafood.com/vnt_upload/product/10_2020/dm2.jpg')
            ->assertJsonCount(6, 'categories.0.products')
            ->assertJsonPath('categories.0.products.0.name', 'Cá Fillet, Tạo Hình Sạch')
            ->assertJsonPath('categories.0.products.0.sortOrder', 14)
            ->assertJsonPath('categories.0.products.0.sizes.0', '60g-120g')
            ->assertJsonPath('categories.0.products.0.image', 'https://idiseafood.com/vnt_upload/product/10_2020/dm2.jpg')
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
            ->assertJsonPath('data.shelfLife', '24 tháng');
    }
}
