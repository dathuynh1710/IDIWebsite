<?php

namespace Tests\Feature;

use App\Models\DocumentCategory;
use App\Models\InvestorDocument;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategorizeInvestorDocumentsTest extends TestCase
{
    use RefreshDatabase;

    public function test_all_roots_use_their_own_year_categories_and_create_only_missing_years(): void
    {
        $reports = $this->category('bao-cao-thuong-nien');
        $notices = $this->category('thong-bao');
        $empty = $this->category('ban-cao-bach');
        $duplicate = $this->category('bao-cao-thuong-nien-nam-2025-moi', $reports->id);
        $year = $this->category('bao-cao-thuong-nien-nam-2025', $reports->id);
        $report = $this->document($reports->id, 2025);
        $notice = $this->document($notices->id, 2025);
        $alreadyFiled = $this->document($year->id, 2025);

        $this->artisan('investors:categorize-by-year')->assertSuccessful();
        $this->assertSame($reports->id, $report->fresh()->document_category_id);
        $this->assertSame(0, $notices->children()->count());
        $this->artisan('investors:categorize-by-year --apply')->assertSuccessful();
        $this->assertSame($year->id, $report->fresh()->document_category_id);
        $this->assertSame($year->id, $alreadyFiled->fresh()->document_category_id);
        $noticeYear = $notice->fresh()->category;
        $this->assertSame($notices->id, $noticeYear->parent_id);
        $this->assertSame('thong-bao-nam-2025', $noticeYear->getTranslation('slug', 'vi'));
        $this->assertSame(0, $empty->children()->count());
        $this->assertSame(0, $duplicate->documents()->count());
        $this->artisan('investors:categorize-by-year --apply')->expectsOutput('Đã chuyển: 0 tài liệu.')->assertSuccessful();
        $this->assertSame(1, $notices->children()->count());
    }

    public function test_invalid_year_prevents_partial_creation_or_assignment(): void
    {
        $root = $this->category('trai-phieu');
        $this->document($root->id, 2025);
        $this->document($root->id, null);
        $this->artisan('investors:categorize-by-year --apply')->assertFailed();
        $this->assertSame(0, $root->children()->count());
        $this->assertSame(2, $root->documents()->count());
    }

    private function category(string $slug, ?int $parent = null): DocumentCategory
    {
        return DocumentCategory::create(['name' => ['vi' => $parent ? 'Năm 2025' : $slug], 'slug' => ['vi' => $slug], 'parent_id' => $parent, 'is_active' => true]);
    }

    private function document(int $category, ?int $year): InvestorDocument
    {
        return InvestorDocument::create(['document_category_id' => $category, 'year' => $year, 'title' => ['vi' => 'Tài liệu'], 'published_on' => '2026-01-01']);
    }
}
