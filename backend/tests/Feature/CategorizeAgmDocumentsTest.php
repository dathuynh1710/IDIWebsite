<?php

namespace Tests\Feature;

use App\Models\DocumentCategory;
use App\Models\InvestorDocument;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategorizeAgmDocumentsTest extends TestCase
{
    use RefreshDatabase;

    public function test_moves_by_document_year_into_canonical_children_and_is_repeatable(): void
    {
        $root = $this->category('dai-hoi-co-dong');
        $duplicate = $this->category('dai-hoi-co-dong-nam-2026-moi', $root->id);
        $year = $this->category('dai-hoi-co-dong-nam-2026', $root->id);
        $other = $this->category('bao-cao-thuong-nien');
        $document = InvestorDocument::create(['document_category_id' => $root->id, 'year' => 2026, 'published_on' => '2025-01-01', 'title' => ['vi' => 'Tài liệu']]);
        $untouched = InvestorDocument::create(['document_category_id' => $other->id, 'year' => 2026, 'title' => ['vi' => 'Khác']]);

        $this->artisan('investors:categorize-agm')->assertSuccessful();
        $this->assertSame($root->id, $document->fresh()->document_category_id);
        $this->artisan('investors:categorize-agm --apply')->assertSuccessful();
        $this->assertSame($year->id, $document->fresh()->document_category_id);
        $this->assertSame($other->id, $untouched->fresh()->document_category_id);
        $this->assertSame(0, $duplicate->documents()->count());
        $this->artisan('investors:categorize-agm --apply')->expectsOutput('Đã chuyển: 0 tài liệu.')->assertSuccessful();

        $this->getJson('/api/investors/documents?category=dai-hoi-co-dong')->assertOk()
            ->assertJsonPath('total', 1)->assertJsonPath('years.0', 2026)
            ->assertJsonPath('items.0.id', $document->id);
        $categories = collect($this->getJson('/api/investors/documents')->json('categories'))->keyBy('id');
        $this->assertSame(1, $categories[$root->id]['count']);
        $this->assertSame(1, $categories[$year->id]['count']);
        $this->getJson('/api/investors/documents?category='.$year->id)->assertOk()->assertJsonPath('total', 1);
        $this->getJson('/api/investors/documents?category='.$duplicate->id)->assertOk()->assertJsonPath('total', 0);
    }

    public function test_missing_year_category_prevents_partial_changes(): void
    {
        $root = $this->category('dai-hoi-co-dong');
        $this->category('dai-hoi-co-dong-nam-2026', $root->id);
        foreach ([2026, 2025] as $year) {
            InvestorDocument::create(['document_category_id' => $root->id, 'year' => $year, 'title' => ['vi' => 'Tài liệu']]);
        }
        $this->artisan('investors:categorize-agm --apply')->assertFailed();
        $this->assertSame(2, $root->documents()->count());
    }

    private function category(string $slug, ?int $parentId = null): DocumentCategory
    {
        return DocumentCategory::create(['name' => ['vi' => $slug], 'slug' => ['vi' => $slug], 'parent_id' => $parentId, 'is_active' => true]);
    }
}
