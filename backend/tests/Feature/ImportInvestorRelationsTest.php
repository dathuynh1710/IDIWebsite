<?php

namespace Tests\Feature;

use App\Models\InvestorDocument;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ImportInvestorRelationsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        foreach ([['vi', 'Vietnamese', 'Tiếng Việt'], ['en', 'English', 'English'], ['zh', 'Chinese', '中文']] as $index => [$code, $name, $nativeName]) {
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

            if (str_ends_with($request->url(), '.pdf')) {
                return Http::response('%PDF-1.4 imported-document', 200, ['Content-Type' => 'application/pdf']);
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
                '--locale' => ['vi'],
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

    public function test_command_merges_vietnamese_and_english_versions_and_is_idempotent(): void
    {
        Storage::fake('public');
        $viDetail = 'https://idiseafood.com/vn/bao-cao-tai-chinh-rieng-giua-nien-do-2026.html';
        $enDetail = 'https://idiseafood.com/en/interim-separate-financial-statements-for-2026.html';
        $viFile = 'https://idiseafood.com/vnt_upload/service/08_2026/BCTC_rieng_giua_nien_do_nam_2026.pdf';
        $enFile = 'https://idiseafood.com/vnt_upload/service/08_2026/Interim_Separate_Financial_Statements_for_2026.pdf';

        Http::fake(function ($request) use ($viDetail, $enDetail, $viFile, $enFile) {
            return match ($request->url()) {
                'https://idiseafood.com/vn/bao-cao-tai-chinh.html' => Http::response(
                    $this->item('Báo cáo tài chính riêng giữa niên độ năm 2026', '28/08/2026', $viDetail, $viFile)
                ),
                'https://idiseafood.com/en/financial-report.html' => Http::response(
                    $this->item('Interim Separate Financial Statements for 2026', 'August 28, 2026', $enDetail, $enFile)
                ),
                $enDetail => Http::response("<ul><li><a href=\"{$viDetail}\">VN</a></li></ul>"),
                $viDetail => Http::response("<div class=\"titleL\"><h1>Báo cáo tài chính riêng giữa niên độ năm 2026</h1></div><a href=\"{$viFile}\">Tải</a>"),
                $viFile, $enFile => Http::response('%PDF-1.4 localized-document', 200, ['Content-Type' => 'application/pdf']),
                default => Http::response('', 404),
            };
        });

        foreach (range(1, 2) as $run) {
            $exitCode = Artisan::call('investors:import-idi', [
                '--category' => ['bao-cao-tai-chinh'],
                '--locale' => ['vi', 'en'],
            ]);

            $this->assertSame(0, $exitCode, Artisan::output());
            $this->assertDatabaseCount('investor_documents', 1);
            $this->assertDatabaseCount('investor_document_files', 2);
            $this->assertDatabaseCount('media', 2);
        }

        $document = InvestorDocument::with('files.media')->firstOrFail();
        $this->assertSame('Báo cáo tài chính riêng giữa niên độ năm 2026', $document->getTranslation('title', 'vi'));
        $this->assertSame('Interim Separate Financial Statements for 2026', $document->getTranslation('title', 'en'));
        $this->assertSame('idi:vn/bao-cao-tai-chinh-rieng-giua-nien-do-2026.html', $document->source_key);
        $this->assertSame(['en', 'vi'], $document->files->pluck('locale')->sort()->values()->all());
        $this->assertTrue(Storage::disk('public')->exists($document->files->firstWhere('locale', 'vi')->media->directory.'/'.$document->files->firstWhere('locale', 'vi')->media->file_name));
    }

    public function test_chinese_feed_does_not_label_vietnamese_content_as_zh(): void
    {
        Http::fake([
            'https://idiseafood.com/cn/investor-relations.html' => Http::response($this->item(
                'Nghị quyết thông qua hạn mức tín dụng năm 2026',
                'September 12, 2026',
                'https://idiseafood.com/cn/nghi-quyet-han-muc-2026.html',
                'https://idiseafood.com/vnt_upload/service/09_2026/nghi_quyet_han_muc_2026.pdf'
            )),
        ]);

        $exitCode = Artisan::call('investors:import-idi', [
            '--category' => ['thong-bao'],
            '--locale' => ['zh-CN'],
        ]);

        $this->assertSame(0, $exitCode, Artisan::output());
        $this->assertDatabaseCount('document_categories', 0);
        $this->assertDatabaseCount('investor_documents', 0);
        $this->assertDatabaseCount('investor_document_files', 0);
    }

    public function test_english_metadata_with_the_same_source_file_uses_vietnamese_file_fallback(): void
    {
        Storage::fake('public');
        $viDetail = 'https://idiseafood.com/vn/nghi-quyet-trieu-tap-dhdcd-bat-thuong-2026.html';
        $enDetail = 'https://idiseafood.com/en/resolution-on-convening-the-2026-egm.html';
        $sharedFile = 'https://idiseafood.com/vnt_upload/service/09_2026/20260910_IDI_EGM_2026.pdf';

        Http::fake(function ($request) use ($viDetail, $enDetail, $sharedFile) {
            return match ($request->url()) {
                'https://idiseafood.com/vn/dai-hoi-co-dong.html' => Http::response(
                    $this->item('Nghị quyết triệu tập ĐHĐCĐ bất thường 2026', '10/09/2026', $viDetail, $sharedFile)
                ),
                'https://idiseafood.com/en/shareholders-meeting.html' => Http::response(
                    $this->item('Resolution on Convening the 2026 EGM', 'September 10, 2026', $enDetail, $sharedFile)
                ),
                $enDetail => Http::response("<a href=\"{$viDetail}\">VN</a>"),
                $viDetail => Http::response("<div class=\"titleL\"><h1>Nghị quyết triệu tập ĐHĐCĐ bất thường 2026</h1></div><a href=\"{$sharedFile}\">Tải</a>"),
                $sharedFile => Http::response('%PDF-1.4 shared-vietnamese-document'),
                default => Http::response('', 404),
            };
        });

        $exitCode = Artisan::call('investors:import-idi', [
            '--category' => ['dai-hoi-co-dong'],
            '--locale' => ['vi', 'en'],
        ]);

        $this->assertSame(0, $exitCode, Artisan::output());
        $document = InvestorDocument::with('files')->firstOrFail();
        $this->assertSame('Resolution on Convening the 2026 EGM', $document->getTranslation('title', 'en'));
        $this->assertSame(1, $document->files->count());
        $this->assertSame('vi', $document->files->first()->locale);
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
