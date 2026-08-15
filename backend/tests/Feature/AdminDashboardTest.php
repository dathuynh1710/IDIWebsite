<?php

namespace Tests\Feature;

use App\Livewire\Admin\Products\Form as ProductForm;
use App\Livewire\Admin\Products\Index as ProductIndex;
use App\Models\ContactMessage;
use App\Models\JobApplication;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
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
            ->assertOk()
            ->assertSee('Bảng điều khiển')
            ->assertSee('IDI Seafood CMS')
            ->assertSee('rel="icon"', false)
            ->assertSee('favicon/favicon-96x96.png', false)
            ->assertSee('favicon/favicon.svg', false)
            ->assertSee('favicon/favicon.ico', false)
            ->assertSee('favicon/apple-touch-icon.png', false)
            ->assertSee('favicon/site.webmanifest', false)
            ->assertSee('apple-mobile-web-app-title', false)
            ->assertSee('images/brand/idi-logo.png', false);
    }

    public function test_admin_page_headers_hide_breadcrumbs_and_descriptions(): void
    {
        $response = $this->actingAs(User::factory()->create())->get('/admin');

        $response
            ->assertOk()
            ->assertSee('Bảng điều khiển')
            ->assertDontSee('class="breadcrumb"', false)
            ->assertDontSee('Những thông tin quan trọng của IDI Seafood CMS được tổng hợp tại đây.');
    }

    public function test_dashboard_prioritizes_actionable_work_and_recent_content(): void
    {
        $user = User::factory()->create();
        $user->givePermissionTo([
            Permission::findOrCreate('contacts.manage', 'web'),
            Permission::findOrCreate('recruitment.view', 'web'),
            Permission::findOrCreate('products.view', 'web'),
        ]);
        ContactMessage::create([
            'full_name' => 'Nguyễn Minh Anh',
            'email' => 'minhanh@example.com',
            'message' => 'Tôi cần thông tin sản phẩm.',
            'status' => 'new',
        ]);
        JobApplication::create([
            'full_name' => 'Trần Hải Nam',
            'email' => 'hainam@example.com',
            'status' => 'new',
        ]);
        $product = $this->product($this->category());

        $this->actingAs($user)->get('/admin')
            ->assertOk()
            ->assertSee('Thư liên hệ chưa xem')
            ->assertSee('Hồ sơ ứng tuyển mới')
            ->assertSee('Bản dịch cần duyệt')
            ->assertSee('Cập nhật gần đây')
            ->assertSee($product->getTranslation('title', 'vi'))
            ->assertSee(route('admin.contacts.index'), false)
            ->assertSee(route('admin.recruitment.applications.index'), false);
    }

    public function test_dashboard_only_shows_information_within_the_user_permissions(): void
    {
        $user = User::factory()->create();
        $user->givePermissionTo(Permission::findOrCreate('products.view', 'web'));
        $this->product($this->category());

        $this->actingAs($user)->get('/admin')
            ->assertOk()
            ->assertSee('Sản phẩm')
            ->assertDontSee('Thư liên hệ chưa xem')
            ->assertDontSee('Hồ sơ ứng tuyển mới')
            ->assertDontSee('Hoạt động gần đây');
    }

    public function test_user_with_product_permission_can_render_index_create_and_edit_pages(): void
    {
        $user = $this->productEditor();
        $category = $this->category();
        $product = $this->product($category);

        $this->actingAs($user)->get('/admin/products')->assertOk()
            ->assertSee($product->sku)
            ->assertSee('Danh sách sản phẩm')
            ->assertSee('Thêm sản phẩm')
            ->assertSee('page-heading card page-heading-card', false);
        $this->actingAs($user)->get('/admin/products/create')
            ->assertOk()
            ->assertSee('Thêm sản phẩm mới')
            ->assertSee('Tiếng Việt')
            ->assertSee('English')
            ->assertSee('中文')
            ->assertSee('ckeditor5-textarea', false)
            ->assertSee('aria-label="Nội dung sản phẩm"', false)
            ->assertDontSee('Mô tả chi tiết')
            ->assertDontSee('Trạng thái bản dịch');
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

    public function test_product_create_button_navigates_to_full_page_form(): void
    {
        Livewire::actingAs($this->productEditor())
            ->test(ProductIndex::class)
            ->assertSeeHtml('href="'.route('admin.products.create').'"')
            ->assertDontSee('role="dialog"', false);
    }

    public function test_product_index_has_configurable_pagination(): void
    {
        $category = $this->category();

        foreach (range(1, 11) as $index) {
            Product::create([
                'product_category_id' => $category->id,
                'sku' => 'IDI-PAGE-'.str_pad((string) $index, 2, '0', STR_PAD_LEFT),
                'title' => ['vi' => "Sản phẩm {$index}"],
                'slug' => ['vi' => "san-pham-{$index}"],
                'translation_status' => ['vi' => 'published'],
                'sort_order' => $index,
                'is_active' => true,
            ]);
        }

        Livewire::actingAs($this->productEditor())
            ->test(ProductIndex::class)
            ->assertSet('perPage', 10)
            ->assertSeeHtml('wire:model.live="perPage"')
            ->assertViewHas('products', fn ($products): bool => $products->total() === 11 && $products->count() === 10)
            ->set('perPage', 20)
            ->assertViewHas('products', fn ($products): bool => $products->total() === 11 && $products->count() === 11);
    }

    public function test_product_table_is_compact_and_shows_created_and_updated_times(): void
    {
        $product = $this->product($this->category());
        DB::table('products')->where('id', $product->id)->update([
            'created_at' => '2021-03-18 10:22:00',
            'updated_at' => '2021-04-05 14:10:00',
        ]);

        $this->actingAs($this->productEditor())
            ->get('/admin/products')
            ->assertOk()
            ->assertSee('10:22 - 18/03/2021')
            ->assertSee('14:10 - 05/04/2021')
            ->assertSee('compact-product-table', false)
            ->assertSee('wire:model="sortOrders.'.$product->id.'"', false)
            ->assertSee('wire:model.live="featured"', false)
            ->assertSee('class="featured-column">Nổi bật</th>', false)
            ->assertSee('title="Ngày tạo"', false)
            ->assertSee('title="Ngày cập nhật"', false);
    }

    public function test_product_list_can_filter_featured_products(): void
    {
        $category = $this->category();
        $featuredProduct = $this->product($category);
        $regularProduct = Product::create([
            'product_category_id' => $category->id,
            'sku' => 'IDI-PAN-REGULAR',
            'title' => ['vi' => 'Sản phẩm thông thường'],
            'slug' => ['vi' => 'san-pham-thong-thuong'],
            'translation_status' => ['vi' => 'published'],
            'sort_order' => 1,
            'is_featured' => false,
            'is_active' => true,
        ]);

        Livewire::actingAs($this->productEditor())
            ->test(ProductIndex::class)
            ->set('featured', '1')
            ->assertViewHas('products', fn ($products): bool => $products->total() === 1 && $products->first()->is($featuredProduct))
            ->set('featured', '0')
            ->assertViewHas('products', fn ($products): bool => $products->total() === 1 && $products->first()->is($regularProduct));
    }

    public function test_product_order_and_visibility_can_be_updated_from_the_list(): void
    {
        $product = $this->product($this->category());

        Livewire::actingAs($this->productEditor())
            ->test(ProductIndex::class)
            ->set("sortOrders.{$product->id}", 12)
            ->call('saveSortOrders')
            ->assertHasNoErrors()
            ->set('selected', [$product->id])
            ->call('bulk', 'hide')
            ->assertHasNoErrors();

        $product->refresh();
        $this->assertSame(12, $product->sort_order);
        $this->assertFalse($product->is_active);
    }

    public function test_product_featured_status_can_be_toggled_from_the_list(): void
    {
        $product = $this->product($this->category());

        Livewire::actingAs($this->productEditor())
            ->test(ProductIndex::class)
            ->assertSeeHtml('role="switch"')
            ->assertSeeHtml('aria-checked="true"')
            ->call('toggleFeatured', $product->id)
            ->assertHasNoErrors()
            ->assertSeeHtml('aria-checked="false"');

        $this->assertFalse($product->fresh()->is_featured);
    }

    public function test_product_delete_waits_for_custom_modal_confirmation(): void
    {
        $product = $this->product($this->category());

        Livewire::actingAs($this->productEditor())->test(ProductIndex::class)
            ->call('requestDelete', $product->id)
            ->assertSet('pendingDeleteId', $product->id)
            ->assertSee('Xóa sản phẩm?')
            ->call('cancelDelete')
            ->assertSet('pendingDeleteId', null);

        $this->assertNotSoftDeleted($product);

        Livewire::actingAs($this->productEditor())->test(ProductIndex::class)
            ->call('requestDelete', $product->id)
            ->call('confirmDelete');

        $this->assertSoftDeleted($product);
    }

    public function test_sidebar_parent_menus_share_a_single_accordion_state(): void
    {
        $response = $this->actingAs($this->productEditor())->get('/admin/products');

        $response
            ->assertOk()
            ->assertSee('x-data="{ openMenu: null }"', false)
            ->assertSee('openMenu = openMenu ===', false)
            ->assertSee('x-bind:class="{ \'is-expanded\': openMenu ===', false)
            ->assertSee('x-collapse.duration.220ms', false);

        $this->assertSame(1, substr_count($response->getContent(), 'class="sidebar-link is-active"'));
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
            ->set('enabled_locales', ['vi', 'en', 'zh'])
            ->set('title', ['vi' => 'Cá tra phi lê', 'en' => 'Pangasius fillet', 'zh' => '巴沙鱼柳'])
            ->set('slug', ['vi' => 'ca-tra-phi-le', 'en' => 'pangasius-fillet', 'zh' => 'basha-yu-liu'])
            ->set('short_description.vi', '<p>Sản phẩm chất lượng cao</p><script>alert(1)</script>')
            ->set('content.vi', '<p>Nội dung an toàn</p><script>alert(1)</script>')
            ->set('sort_order', 1)
            ->set('is_featured', true)
            ->set('is_active', true)
            ->call('save')
            ->assertHasNoErrors();

        $product = Product::where('sku', 'IDI-PAN-NEW')->firstOrFail();
        $this->assertSame('Pangasius fillet', $product->getTranslation('title', 'en'));
        $this->assertSame(['vi' => 'published', 'en' => 'published', 'zh' => 'published'], $product->getTranslations('translation_status'));
        $this->assertStringNotContainsString('<script', $product->getTranslation('short_description', 'vi'));
        $this->assertStringNotContainsString('<script', $product->getTranslation('content', 'vi'));
    }

    public function test_product_can_be_saved_in_vietnamese_only(): void
    {
        $category = $this->category();
        $product = $this->product($category);

        Livewire::actingAs($this->productEditor())
            ->test(ProductForm::class, ['product' => $product])
            ->assertSet('enabled_locales', ['vi', 'en', 'zh'])
            ->set('enabled_locales', ['vi'])
            ->set('title.vi', 'Cá tra Việt Nam')
            ->set('slug.vi', 'ca-tra-viet-nam')
            ->call('save')
            ->assertHasNoErrors();

        $product->refresh();
        $this->assertSame(['vi' => 'Cá tra Việt Nam'], $product->getTranslations('title'));
        $this->assertSame(['vi' => 'ca-tra-viet-nam'], $product->getTranslations('slug'));
        $this->assertSame(['vi' => 'published'], $product->getTranslations('translation_status'));
    }

    public function test_updating_product_preserves_legacy_detailed_description(): void
    {
        $product = $this->product($this->category());
        $product->setTranslation('description', 'vi', '<p>Dữ liệu mô tả cũ</p>')->save();

        Livewire::actingAs($this->productEditor())
            ->test(ProductForm::class, ['product' => $product])
            ->call('save')
            ->assertHasNoErrors();

        $this->assertSame('<p>Dữ liệu mô tả cũ</p>', $product->fresh()->getTranslation('description', 'vi'));
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
