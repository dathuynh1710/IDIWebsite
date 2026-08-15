<?php

namespace Tests\Feature;

use App\Livewire\Admin\Investors\CategoryForm;
use App\Livewire\Admin\Investors\CategoryIndex;
use App\Livewire\Admin\Investors\DocumentForm;
use App\Livewire\Admin\Investors\DocumentIndex;
use App\Livewire\Admin\Investors\Settings;
use App\Models\DocumentCategory;
use App\Models\InvestorDocument;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class InvestorRelationsManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_investor_editor_can_open_every_management_screen(): void
    {
        $user = $this->editor();
        $category = $this->category();
        $document = $this->document($category);

        foreach ([
            '/admin/investors', '/admin/investors/settings', '/admin/investors/categories',
            '/admin/investors/categories/create', "/admin/investors/categories/{$category->id}/edit",
            '/admin/investors/create', "/admin/investors/{$document->id}/edit",
        ] as $url) {
            $this->actingAs($user)->get($url)->assertOk();
        }
    }

    public function test_investor_settings_header_only_displays_the_heading(): void
    {
        $this->actingAs($this->editor())
            ->get('/admin/investors/settings')
            ->assertOk()
            ->assertSee('Cấu hình quan hệ cổ đông')
            ->assertDontSee('Thiết lập nội dung giới thiệu, SEO và cách hiển thị thư viện tài liệu.')
            ->assertDontSee('class="breadcrumb"', false);
    }

    public function test_category_and_document_are_saved_in_three_languages(): void
    {
        Storage::fake('public');
        $user = $this->editor();

        Livewire::actingAs($user)->test(CategoryForm::class)
            ->set('name', ['vi' => 'Báo cáo tài chính', 'en' => 'Financial reports', 'zh' => '财务报告'])
            ->set('slug', ['vi' => 'bao-cao-tai-chinh', 'en' => 'financial-reports', 'zh' => 'caiwu-baogao'])
            ->call('save')
            ->assertHasNoErrors();

        $category = DocumentCategory::firstOrFail();
        Livewire::actingAs($user)->test(DocumentForm::class)
            ->set('document_category_id', $category->id)
            ->set('enabled_locales', ['vi', 'en', 'zh'])
            ->set('title', ['vi' => 'Báo cáo quý 2', 'en' => 'Second quarter report', 'zh' => '第二季度报告'])
            ->set('summary', ['vi' => 'Bản tiếng Việt', 'en' => 'English edition', 'zh' => '中文版'])
            ->set('slug', 'bao-cao-quy-2-2026')
            ->set('uploads.vi', UploadedFile::fake()->create('report.pdf', 200, 'application/pdf'))
            ->call('save')
            ->assertHasNoErrors();

        $document = InvestorDocument::with('files.media')->where('slug', 'bao-cao-quy-2-2026')->firstOrFail();
        $this->assertSame('Second quarter report', $document->getTranslation('title', 'en'));
        $this->assertSame('财务报告', $category->getTranslation('name', 'zh'));
        $this->assertCount(1, $document->files);
        Storage::disk('public')->assertExists($document->files->first()->media->directory.'/'.$document->files->first()->media->file_name);
    }

    public function test_settings_are_multilingual_and_unauthorized_user_is_forbidden(): void
    {
        $user = $this->editor();
        Livewire::actingAs($user)->test(Settings::class)
            ->set('page_title', ['vi' => 'Quan hệ cổ đông', 'en' => 'Investor Relations', 'zh' => '投资者关系'])
            ->set('description.en', 'Transparent investor information')
            ->set('items_per_page', 20)
            ->set('default_year', 2026)
            ->set('max_upload_size', 25)
            ->call('save')
            ->assertHasNoErrors();

        $module = DB::table('modules')->where('code', 'investors')->first();
        $this->assertSame('投资者关系', json_decode($module->page_title, true)['zh']);
        $this->actingAs(User::factory()->create())->get('/admin/investors')->assertForbidden();
    }

    public function test_document_summary_can_be_cleared_when_updating(): void
    {
        $user = $this->editor();
        $category = $this->category();
        $document = $this->document($category);
        $document->update([
            'slug' => 'bao-cao-2025',
            'summary' => ['vi' => 'Nội dung tin cũ'],
        ]);

        Livewire::actingAs($user)->test(DocumentForm::class, ['document' => $document])
            ->set('summary.vi', '')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertArrayNotHasKey('vi', $document->refresh()->getTranslations('summary'));
    }

    public function test_document_seo_fields_are_saved_and_slug_can_be_generated(): void
    {
        Storage::fake('public');
        $user = $this->editor();
        $category = $this->category();

        Livewire::actingAs($user)->test(DocumentForm::class)
            ->set('document_category_id', $category->id)
            ->set('title.vi', 'Báo cáo thường niên 2026')
            ->call('generateSlug')
            ->assertSet('slug', 'bao-cao-thuong-nien-2026')
            ->set('seo_title', 'Báo cáo thường niên 2026 | IDI Seafood')
            ->set('meta_description', 'Thông tin tài chính, hoạt động nổi bật và định hướng phát triển của IDI Seafood trong năm 2026.')
            ->set('uploads.vi', UploadedFile::fake()->create('report-2026.pdf', 200, 'application/pdf'))
            ->call('save')
            ->assertHasNoErrors();

        $document = InvestorDocument::where('slug', 'bao-cao-thuong-nien-2026')->firstOrFail();
        $this->assertSame('Báo cáo thường niên 2026 | IDI Seafood', $document->seo_title);
        $this->assertStringContainsString('Thông tin tài chính', $document->meta_description);
    }

    public function test_document_is_deleted_only_after_modal_confirmation(): void
    {
        $user = $this->editor();
        $document = $this->document($this->category());

        Livewire::actingAs($user)->test(DocumentIndex::class)
            ->call('requestDelete', $document->id)
            ->assertSet('pendingDeleteId', $document->id)
            ->assertSet('pendingDeleteName', 'Báo cáo 2025')
            ->assertSee('Xóa tài liệu QHCĐ?')
            ->call('cancelDelete')
            ->assertSet('pendingDeleteId', null);

        $this->assertNotSoftDeleted($document);

        Livewire::actingAs($user)->test(DocumentIndex::class)
            ->call('requestDelete', $document->id)
            ->call('confirmDelete')
            ->assertSet('pendingDeleteId', null);

        $this->assertSoftDeleted($document);
    }

    public function test_document_list_has_configurable_pagination(): void
    {
        $category = $this->category();

        foreach (range(1, 12) as $index) {
            InvestorDocument::create([
                'document_category_id' => $category->id,
                'title' => ['vi' => "Tài liệu QHCĐ {$index}"],
                'year' => 2026,
                'sort_order' => $index,
                'is_active' => true,
            ]);
        }

        Livewire::actingAs($this->editor())->test(DocumentIndex::class)
            ->assertSet('perPage', 15)
            ->assertSeeHtml('wire:model.live="perPage"')
            ->assertViewHas('documents', fn ($documents): bool => $documents->total() === 12 && $documents->count() === 12)
            ->set('perPage', 5)
            ->assertSet('perPage', 5)
            ->assertViewHas('documents', fn ($documents): bool => $documents->total() === 12 && $documents->count() === 5)
            ->call('nextPage')
            ->assertSet('paginators.page', 2);
    }

    public function test_category_list_displays_created_and_updated_times(): void
    {
        $user = $this->editor();
        $category = $this->category();

        Livewire::actingAs($user)->test(CategoryIndex::class)
            ->assertSee($category->created_at->format('H:i - d/m/Y'))
            ->assertSee($category->updated_at->format('H:i - d/m/Y'))
            ->assertDontSee('Tạo:')
            ->assertDontSee('Sửa:');
    }

    public function test_category_list_has_configurable_pagination(): void
    {
        foreach (range(1, 16) as $index) {
            DocumentCategory::create([
                'name' => ['vi' => "Danh mục QHCĐ {$index}"],
                'slug' => ['vi' => "danh-muc-qhcd-{$index}"],
                'sort_order' => $index,
                'is_active' => true,
            ]);
        }

        Livewire::actingAs($this->editor())->test(CategoryIndex::class)
            ->assertSet('perPage', 15)
            ->assertSeeHtml('wire:model.live="perPage"')
            ->assertViewHas('categories', fn ($categories): bool => $categories->total() === 16 && $categories->count() === 15)
            ->set('selected', [1])
            ->set('perPage', 5)
            ->assertSet('selected', [])
            ->assertViewHas('categories', fn ($categories): bool => $categories->total() === 16 && $categories->count() === 5)
            ->call('nextPage')
            ->assertSet('paginators.page', 2);
    }

    public function test_investor_category_delete_waits_for_custom_modal_confirmation(): void
    {
        $category = $this->category();

        Livewire::actingAs($this->editor())->test(CategoryIndex::class)
            ->call('requestDelete', $category->id)
            ->assertSet('pendingDeleteId', $category->id)
            ->assertSee('Xóa danh mục cổ đông?')
            ->call('cancelDelete');
        $this->assertNotSoftDeleted($category);

        Livewire::actingAs($this->editor())->test(CategoryIndex::class)
            ->call('requestDelete', $category->id)
            ->call('confirmDelete');
        $this->assertSoftDeleted($category);
    }

    public function test_investor_category_with_content_shows_error_toast_instead_of_http_exception(): void
    {
        $category = $this->category();
        $this->document($category);

        Livewire::actingAs($this->editor())->test(CategoryIndex::class)
            ->call('requestDelete', $category->id)
            ->assertSet('pendingDeleteId', null)
            ->assertDispatched(
                'toast',
                type: 'error',
                message: 'Không thể xóa danh mục vì vẫn còn danh mục con hoặc tài liệu.'
            )
            ->assertHasNoErrors();

        $this->assertNotSoftDeleted($category);
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
        $user->givePermissionTo(Permission::findOrCreate('investor-documents.manage', 'web'));

        return $user;
    }

    private function category(): DocumentCategory
    {
        return DocumentCategory::create([
            'name' => ['vi' => 'Báo cáo thường niên', 'en' => 'Annual reports', 'zh' => '年度报告'],
            'slug' => ['vi' => 'bao-cao-thuong-nien', 'en' => 'annual-reports', 'zh' => 'niandu-baogao'],
            'is_active' => true,
        ]);
    }

    private function document(DocumentCategory $category): InvestorDocument
    {
        return InvestorDocument::create([
            'document_category_id' => $category->id,
            'title' => ['vi' => 'Báo cáo 2025', 'en' => 'Report 2025', 'zh' => '2025 年报告'],
            'year' => 2025,
            'is_active' => true,
        ]);
    }
}
