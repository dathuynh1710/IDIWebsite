<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ImportInvestorRelationsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        DB::table('locales')->insert([
            'code' => 'vi',
            'name' => 'Vietnamese',
            'native_name' => 'Tiếng Việt',
            'direction' => 'ltr',
            'is_default' => true,
            'is_active' => true,
            'sort_order' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('modules')->insert([
            'code' => 'investors',
            'name' => 'Investor Relations',
            'module_type' => 'documents',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function test_command_imports_every_page_and_can_be_run_again_without_duplicates(): void
    {
        Http::fake(function ($request) {
            if ($request->method() === 'POST') {
                return Http::response([
                    'ok' => 1,
                    'html' => $this->item(
                        'Báo cáo thường niên năm 2024',
                        '15/04/2025',
                        'https://idiseafood.com/vn/bao-cao-thuong-nien-2024.html',
                        'https://idiseafood.com/vnt_upload/service/BCTN_2024.pdf'
                    ),
                ]);
            }

            return Http::response(
                $this->item(
                    'Báo cáo thường niên năm 2025',
                    '15/04/2026',
                    'https://idiseafood.com/vn/bao-cao-thuong-nien-2025.html',
                    'https://idiseafood.com/vnt_upload/service/BCTN_2025.pdf'
                ).'<input id="npage" value="1"><input id="totals" value="2"><input id="cat_id" value="15">'
            );
        });

        foreach (range(1, 2) as $run) {
            $exitCode = Artisan::call('investors:import-idi', [
                '--category' => ['bao-cao-thuong-nien'],
            ]);

            $this->assertSame(0, $exitCode, Artisan::output());
            $this->assertDatabaseCount('document_categories', 1);
            $this->assertDatabaseCount('investor_documents', 2);
            $this->assertDatabaseCount('investor_document_files', 2);
            $this->assertDatabaseCount('media', 2);
        }

        $this->assertDatabaseHas('media', [
            'external_url' => 'https://idiseafood.com/vnt_upload/service/BCTN_2025.pdf',
            'mime_type' => 'application/pdf',
        ]);
        $this->assertDatabaseHas('investor_documents', [
            'year' => 2025,
            'published_on' => '2026-04-15 00:00:00',
            'is_active' => true,
        ]);
    }

    private function item(string $title, string $date, string $detailUrl, string $fileUrl): string
    {
        return <<<HTML
            <li class="col itemshare">
                <div class="date"><a href="{$detailUrl}"><span>{$date}</span></a></div>
                <div class="tt mmgrip">
                    <div class="hcol"><h3><a href="{$detailUrl}">{$title}</a></h3></div>
                    <div class="bcol"><div class="mmdown"><a href="{$fileUrl}">Tải</a></div></div>
                </div>
            </li>
        HTML;
    }
}
