<?php

namespace Tests\Feature;

use App\Models\DocumentCategory;
use App\Models\InvestorDocument;
use App\Models\InvestorDocumentFile;
use App\Models\Media;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class InvestorRelationsApiTest extends TestCase
{
    use RefreshDatabase;

    private DocumentCategory $financials;

    protected function setUp(): void
    {
        parent::setUp();

        foreach ([['vi', 'Vietnamese', 'Tiếng Việt'], ['en', 'English', 'English']] as $index => [$code, $name, $nativeName]) {
            DB::table('locales')->insert([
                'code' => $code,
                'name' => $name,
                'native_name' => $nativeName,
                'direction' => 'ltr',
                'is_default' => $code === 'vi',
                'is_active' => true,
                'sort_order' => $index,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        DB::table('modules')->insert([
            'code' => 'investors',
            'name' => 'Investor Relations',
            'module_type' => 'documents',
            'page_title' => json_encode(['vi' => 'Quan hệ cổ đông', 'en' => 'Investor Relations']),
            'description' => json_encode(['vi' => 'Thông tin minh bạch dành cho cổ đông.']),
            'seo_title' => json_encode(['vi' => 'Quan hệ cổ đông IDI']),
            'meta_description' => json_encode(['vi' => 'Công bố chính thức từ IDI Seafood.']),
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->financials = DocumentCategory::create([
            'name' => ['vi' => 'Báo cáo tài chính', 'en' => 'Financial reports'],
            'slug' => ['vi' => 'bao-cao-tai-chinh', 'en' => 'financial-reports'],
            'description' => ['vi' => 'Báo cáo riêng và hợp nhất'],
            'sort_order' => 20,
            'is_active' => true,
        ]);
    }

    public function test_index_returns_public_documents_with_filters_and_download_metadata(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('investor-documents/report.pdf', 'pdf-content');

        $newest = $this->document($this->financials, [
            'title' => ['vi' => 'Báo cáo tài chính hợp nhất Quý 2 năm 2026'],
            'document_number' => 'BCTC-Q2-2026',
            'year' => 2026,
            'quarter' => 2,
            'published_on' => '2026-07-30',
        ]);
        $media = Media::create([
            'disk' => 'public',
            'directory' => 'investor-documents',
            'file_name' => 'report.pdf',
            'original_name' => 'BCTC-Q2-2026.pdf',
            'mime_type' => 'application/pdf',
            'extension' => 'pdf',
            'file_size' => 1258291,
        ]);
        $file = InvestorDocumentFile::create([
            'investor_document_id' => $newest->id,
            'media_id' => $media->id,
            'locale' => 'vi',
            'display_name' => ['vi' => 'Báo cáo tài chính Quý 2'],
        ]);
        $this->document($this->financials, [
            'title' => ['vi' => 'Báo cáo tài chính năm 2025'],
            'year' => 2025,
            'published_on' => '2026-03-28',
        ]);
        $this->document($this->financials, ['is_active' => false]);

        $this->getJson('/api/investors/documents?locale=vi&category=bao-cao-tai-chinh&year=2026&search='.urlencode('hợp nhất'))
            ->assertOk()
            ->assertJsonPath('total', 1)
            ->assertJsonPath('items.0.id', $newest->id)
            ->assertJsonPath('items.0.title', 'Báo cáo tài chính hợp nhất Quý 2 năm 2026')
            ->assertJsonPath('items.0.category.slug', 'bao-cao-tai-chinh')
            ->assertJsonPath('items.0.quarter', 2)
            ->assertJsonPath('items.0.publishedOn', '2026-07-30')
            ->assertJsonPath('items.0.file.id', $file->id)
            ->assertJsonPath('items.0.file.url', "/investor-documents/{$file->id}/download")
            ->assertJsonPath('items.0.file.extension', 'pdf')
            ->assertJsonPath('items.0.file.size', 1258291)
            ->assertJsonPath('categories.0.count', 2)
            ->assertJsonPath('years.0', 2026)
            ->assertJsonPath('years.1', 2025)
            ->assertJsonPath('pageConfig.title', 'Quan hệ cổ đông')
            ->assertJsonPath('pageConfig.seo.title', 'Quan hệ cổ đông IDI');

        $this->get(route('investors.documents.download', $file))
            ->assertOk()
            ->assertDownload('BCTC-Q2-2026.pdf');
    }

    public function test_external_document_file_uses_its_public_url(): void
    {
        $document = $this->document($this->financials);
        $media = Media::create([
            'disk' => 'public',
            'directory' => 'documents',
            'file_name' => 'annual-report-2025.pdf',
            'external_url' => 'https://idiseafood.com/reports/annual-report-2025.pdf',
            'original_name' => 'annual-report-2025.pdf',
            'mime_type' => 'application/pdf',
            'extension' => 'pdf',
        ]);
        $file = InvestorDocumentFile::create([
            'investor_document_id' => $document->id,
            'media_id' => $media->id,
            'locale' => 'vi',
        ]);

        $this->getJson('/api/investors/documents')
            ->assertOk()
            ->assertJsonPath('items.0.file.url', $media->external_url);

        $this->get(route('investors.documents.download', $file))
            ->assertRedirect($media->external_url);
    }

    public function test_index_supports_locale_fallback_pagination_and_oldest_sort(): void
    {
        $oldest = $this->document($this->financials, [
            'title' => ['vi' => 'Báo cáo cũ'],
            'year' => 2024,
            'published_on' => '2024-03-01',
        ]);
        $this->document($this->financials, [
            'title' => ['vi' => 'Báo cáo mới'],
            'year' => 2025,
            'published_on' => '2025-03-01',
        ]);

        $this->getJson('/api/investors/documents?locale=en&category=financial-reports&sort=oldest&limit=1')
            ->assertOk()
            ->assertJsonPath('total', 2)
            ->assertJsonPath('page', 1)
            ->assertJsonPath('lastPage', 2)
            ->assertJsonPath('items.0.id', $oldest->id)
            ->assertJsonPath('items.0.locale', 'vi')
            ->assertJsonPath('items.0.title', 'Báo cáo cũ')
            ->assertJsonPath('items.0.category.name', 'Financial reports');
    }

    public function test_hidden_category_and_disabled_module_are_not_public(): void
    {
        $hiddenCategory = DocumentCategory::create([
            'name' => ['vi' => 'Nội bộ'],
            'slug' => ['vi' => 'noi-bo'],
            'is_active' => false,
        ]);
        $hiddenDocument = $this->document($hiddenCategory);
        $media = Media::create([
            'disk' => 'public',
            'directory' => 'investor-documents',
            'file_name' => 'hidden.pdf',
            'original_name' => 'hidden.pdf',
            'mime_type' => 'application/pdf',
            'extension' => 'pdf',
        ]);
        $file = InvestorDocumentFile::create([
            'investor_document_id' => $hiddenDocument->id,
            'media_id' => $media->id,
            'locale' => 'vi',
        ]);

        $this->getJson('/api/investors/documents')->assertOk()->assertJsonPath('total', 0);
        $this->get(route('investors.documents.download', $file))->assertNotFound();

        DB::table('modules')->where('code', 'investors')->update(['is_active' => false]);
        $this->getJson('/api/investors/documents')->assertNotFound();
    }

    private function document(?DocumentCategory $category, array $overrides = []): InvestorDocument
    {
        static $sequence = 0;
        $sequence++;

        return InvestorDocument::create(array_replace([
            'document_category_id' => $category?->id,
            'title' => ['vi' => "Tài liệu {$sequence}"],
            'slug' => "tai-lieu-{$sequence}",
            'year' => 2026,
            'published_on' => '2026-01-01',
            'sort_order' => 0,
            'is_featured' => false,
            'is_active' => true,
        ], $overrides));
    }
}
