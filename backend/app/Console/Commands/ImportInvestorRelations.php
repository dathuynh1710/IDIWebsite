<?php

namespace App\Console\Commands;

use App\Models\DocumentCategory;
use App\Models\InvestorDocument;
use App\Models\InvestorDocumentFile;
use App\Models\Media;
use Carbon\Carbon;
use DOMDocument;
use DOMElement;
use DOMXPath;
use Illuminate\Console\Command;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;

class ImportInvestorRelations extends Command
{
    protected $signature = 'investors:import-idi
        {--category=* : Chỉ nhập các slug danh mục được chọn}
        {--dry-run : Chỉ đọc và thống kê, không ghi database}';

    protected $description = 'Nhập thư viện Quan hệ cổ đông công khai từ idiseafood.com';

    private const AJAX_URL = 'https://idiseafood.com/vn/quan-he-co-dong/ajax/load_news.html';

    private const CATEGORIES = [
        'thong-bao' => [
            'url' => 'https://idiseafood.com/vn/thong-bao.html',
            'name' => ['vi' => 'Thông báo', 'en' => 'Announcements', 'zh' => '公告'],
            'description' => ['vi' => 'Thông báo, nghị quyết và thông tin quản trị dành cho cổ đông.'],
            'sort_order' => 50,
        ],
        'bao-cao-tai-chinh' => [
            'url' => 'https://idiseafood.com/vn/bao-cao-tai-chinh.html',
            'name' => ['vi' => 'Báo cáo tài chính', 'en' => 'Financial reports', 'zh' => '财务报告'],
            'description' => ['vi' => 'Báo cáo tài chính riêng, hợp nhất và các văn bản giải trình.'],
            'sort_order' => 40,
        ],
        'bao-cao-thuong-nien' => [
            'url' => 'https://idiseafood.com/vn/bao-cao-thuong-nien.html',
            'name' => ['vi' => 'Báo cáo thường niên', 'en' => 'Annual reports', 'zh' => '年度报告'],
            'description' => ['vi' => 'Báo cáo thường niên của IDI qua từng năm.'],
            'sort_order' => 30,
        ],
        'dai-hoi-co-dong' => [
            'url' => 'https://idiseafood.com/vn/dai-hoi-co-dong.html',
            'name' => ['vi' => 'Đại hội đồng cổ đông', 'en' => 'General Meeting of Shareholders', 'zh' => '股东大会'],
            'description' => ['vi' => 'Thư mời, tài liệu, biểu mẫu, biên bản và nghị quyết Đại hội đồng cổ đông.'],
            'sort_order' => 20,
        ],
        'trai-phieu' => [
            'url' => 'https://idiseafood.com/vn/trai-phieu.html',
            'name' => ['vi' => 'Trái phiếu', 'en' => 'Bonds', 'zh' => '债券'],
            'description' => ['vi' => 'Thông tin phát hành, sử dụng vốn, cam kết và thanh toán trái phiếu.'],
            'sort_order' => 10,
        ],
    ];

    private const FILE_EXTENSIONS = ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'zip', 'rar'];

    public function handle(): int
    {
        $selected = collect($this->option('category'))->filter()->values();
        $definitions = collect(self::CATEGORIES)
            ->when($selected->isNotEmpty(), fn ($items) => $items->only($selected));

        if ($definitions->isEmpty()) {
            $this->error('Không tìm thấy danh mục cần nhập.');

            return self::FAILURE;
        }

        $dryRun = (bool) $this->option('dry-run');
        $totalImported = 0;
        $totalSkipped = 0;

        foreach ($definitions as $slug => $definition) {
            $this->newLine();
            $this->info("Đang đọc {$definition['name']['vi']}...");

            try {
                $items = $this->fetchCategory($definition['url']);
            } catch (Throwable $exception) {
                $this->error("Không thể đọc {$definition['url']}: {$exception->getMessage()}");

                return self::FAILURE;
            }

            if ($dryRun) {
                $this->line("Tìm thấy {$items->count()} tài liệu (dry-run).");
                $totalImported += $items->count();

                continue;
            }

            $category = $this->upsertCategory($slug, $definition);
            $imported = 0;
            $skipped = 0;

            foreach ($items as $position => $item) {
                try {
                    $this->upsertDocument($category, $item, $items->count() - $position);
                    $imported++;
                } catch (Throwable $exception) {
                    $skipped++;
                    $this->warn("Bỏ qua “{$item['title']}”: {$exception->getMessage()}");
                }
            }

            $this->line("Đã đồng bộ {$imported}/{$items->count()} tài liệu; bỏ qua {$skipped}.");
            $totalImported += $imported;
            $totalSkipped += $skipped;
        }

        if (! $dryRun) {
            $this->updateModuleContent();
        }

        $this->newLine();
        $this->info("Hoàn tất: {$totalImported} tài liệu, {$totalSkipped} lỗi.");

        return $totalSkipped === 0 ? self::SUCCESS : self::FAILURE;
    }

    private function fetchCategory(string $url)
    {
        $html = $this->http()->get($url)->throw()->body();
        $metadata = $this->pageMetadata($html);
        $items = collect($this->parseItems($html));

        while ($items->count() < $metadata['totals']) {
            $offset = $items->count();
            $response = $this->http()->asForm()->post(self::AJAX_URL, [
                'totals' => $metadata['totals'],
                'npage' => $metadata['npage'],
                'offset' => $offset,
                'cat_id' => $metadata['cat_id'],
                'lang' => 'vn',
            ])->throw()->json();

            $nextItems = $this->parseItems((string) ($response['html'] ?? ''));
            if ($nextItems === []) {
                break;
            }

            $items = $items->concat($nextItems);
        }

        return $items
            ->filter(fn (array $item): bool => filled($item['title']) && filled($item['file_url']))
            ->unique(fn (array $item): string => $item['detail_url'] ?: $item['title'].'|'.$item['published_on'])
            ->values();
    }

    private function pageMetadata(string $html): array
    {
        $xpath = $this->xpath($html);
        $value = fn (string $id, int $default): int => (int) ($xpath->evaluate("string(//input[@id='{$id}']/@value)") ?: $default);

        return [
            'npage' => max(1, $value('npage', 20)),
            'totals' => max(0, $value('totals', 0)),
            'cat_id' => max(0, $value('cat_id', 0)),
        ];
    }

    private function parseItems(string $html): array
    {
        if (trim($html) === '') {
            return [];
        }

        $xpath = $this->xpath($html);
        $nodes = $xpath->query("//li[contains(concat(' ', normalize-space(@class), ' '), ' itemshare ')]");
        $items = [];

        foreach ($nodes ?: [] as $node) {
            if (! $node instanceof DOMElement) {
                continue;
            }

            $titleNode = $xpath->query(".//div[contains(@class, 'hcol')]//h3/a", $node)?->item(0);
            $dateNode = $xpath->query(".//div[contains(@class, 'date')]//span", $node)?->item(0);
            $fileNode = $xpath->query(".//div[contains(@class, 'mmdown')]//a", $node)?->item(0);
            $title = $this->cleanText($titleNode?->textContent);
            $detailUrl = $titleNode instanceof DOMElement ? trim($titleNode->getAttribute('href')) : '';
            $fileUrl = $fileNode instanceof DOMElement ? trim($fileNode->getAttribute('href')) : '';

            if ($fileUrl === '' && $detailUrl !== '') {
                $fileUrl = $this->resolveFileUrl($detailUrl) ?: $detailUrl;
            } elseif ($fileUrl !== '' && ! $this->isFileUrl($fileUrl)) {
                $fileUrl = $this->resolveFileUrl($fileUrl) ?: $fileUrl;
            }

            $items[] = [
                'title' => $title,
                'published_on' => $this->parseDate($this->cleanText($dateNode?->textContent)),
                'detail_url' => $detailUrl,
                'file_url' => $fileUrl,
            ];
        }

        return $items;
    }

    private function resolveFileUrl(string $detailUrl): ?string
    {
        try {
            $xpath = $this->xpath($this->http()->get($detailUrl)->throw()->body());
            $links = $xpath->query("//a[contains(@href, '/vnt_upload/')]/@href");

            foreach ($links ?: [] as $link) {
                $url = trim($link->nodeValue);
                if ($this->isFileUrl($url)) {
                    return $url;
                }
            }
        } catch (Throwable) {
            return null;
        }

        return null;
    }

    private function upsertCategory(string $slug, array $definition): DocumentCategory
    {
        $category = DocumentCategory::withTrashed()
            ->where('slug->vi', $slug)
            ->first() ?? new DocumentCategory;

        $category->fill([
            'name' => $definition['name'],
            'slug' => [
                'vi' => $slug,
                'en' => Str::slug($definition['name']['en']),
                'zh' => Str::slug($definition['name']['en']).'-zh',
            ],
            'description' => $definition['description'],
            'sort_order' => $definition['sort_order'],
            'is_active' => true,
        ]);
        $category->deleted_at = null;
        $category->save();

        return $category;
    }

    private function upsertDocument(DocumentCategory $category, array $item, int $sortOrder): void
    {
        DB::transaction(function () use ($category, $item, $sortOrder): void {
            $media = Media::withTrashed()->where('external_url', $item['file_url'])->first();
            $slug = $this->documentSlug($item);
            $document = InvestorDocument::withTrashed()->where('slug', $slug)->first();
            if (! $document && $media) {
                $document = InvestorDocumentFile::with('document')
                    ->where('media_id', $media->id)
                    ->get()
                    ->pluck('document')
                    ->first(fn (?InvestorDocument $candidate): bool => $candidate?->getTranslation('title', 'vi', false) === $item['title']);
            }
            $document ??= new InvestorDocument;

            $title = ['vi' => $item['title']];
            $document->fill([
                'document_category_id' => $category->id,
                'title' => array_merge($document->getTranslations('title'), $title),
                'summary' => $document->summary,
                'slug' => $document->slug ?: $slug,
                'year' => $this->documentYear($item),
                'quarter' => $this->documentQuarter($item['title']),
                'published_on' => $item['published_on'],
                'sort_order' => $sortOrder,
                'is_active' => true,
            ]);
            $document->deleted_at = null;
            $document->save();

            $media ??= new Media;
            $extension = $this->extension($item['file_url']);
            $fileName = $this->fileName($item['file_url'], $extension);
            $media->fill([
                'disk' => 'public',
                'directory' => 'investor-imports',
                'file_name' => $fileName,
                'external_url' => $item['file_url'],
                'original_name' => $fileName,
                'mime_type' => $this->mimeType($extension),
                'extension' => $extension,
                'title' => array_merge(is_array($media->title) ? $media->title : [], $title),
            ]);
            $media->deleted_at = null;
            $media->save();

            InvestorDocumentFile::updateOrCreate([
                'investor_document_id' => $document->id,
                'media_id' => $media->id,
                'locale' => 'vi',
            ], [
                'display_name' => $title,
                'sort_order' => 0,
            ]);

            InvestorDocumentFile::with('document')
                ->where('media_id', $media->id)
                ->where('investor_document_id', '!=', $document->id)
                ->get()
                ->filter(fn (InvestorDocumentFile $file): bool => blank($file->document?->slug))
                ->each(function (InvestorDocumentFile $file): void {
                    $legacyDocument = $file->document;
                    $file->delete();
                    $legacyDocument?->delete();
                });
        });
    }

    private function updateModuleContent(): void
    {
        DB::table('modules')->where('code', 'investors')->update([
            'page_title' => json_encode([
                'vi' => 'Quan hệ cổ đông',
                'en' => 'Investor Relations',
                'zh' => '投资者关系',
            ], JSON_UNESCAPED_UNICODE),
            'description' => json_encode([
                'vi' => 'Mang thành tâm biến thành lợi nhuận. I.D.I phát triển theo chiến lược toàn diện, gắn kết mục tiêu kinh doanh với Hành tinh, Con người và Sản phẩm.',
                'en' => 'IDI creates long-term value through a comprehensive strategy connecting business goals with Planet, People and Product.',
                'zh' => 'IDI 通过将商业目标与地球、人类和产品相结合的综合战略创造长期价值。',
            ], JSON_UNESCAPED_UNICODE),
            'updated_at' => now(),
        ]);
    }

    private function http(): PendingRequest
    {
        return Http::withHeaders([
            'Accept' => 'text/html,application/json',
            'User-Agent' => 'IDI-Seafood-CMS/1.0 (+https://idiseafood.com)',
        ])->withoutVerifying()->retry(3, 500)->timeout(60);
    }

    private function xpath(string $html): DOMXPath
    {
        $document = new DOMDocument('1.0', 'UTF-8');
        $loaded = @$document->loadHTML('<?xml encoding="UTF-8">'.$html, LIBXML_NOERROR | LIBXML_NOWARNING);
        if (! $loaded) {
            throw new RuntimeException('HTML nguồn không hợp lệ.');
        }

        return new DOMXPath($document);
    }

    private function cleanText(?string $value): string
    {
        return trim(preg_replace('/\s+/u', ' ', html_entity_decode((string) $value, ENT_QUOTES | ENT_HTML5, 'UTF-8')) ?? '');
    }

    private function parseDate(string $date): ?string
    {
        try {
            return Carbon::createFromFormat('d/m/Y', $date)->toDateString();
        } catch (Throwable) {
            return null;
        }
    }

    private function documentYear(array $item): ?int
    {
        if (preg_match_all('/\b(20\d{2})\b/u', $item['title'], $matches) && $matches[1] !== []) {
            return (int) end($matches[1]);
        }

        return $item['published_on'] ? (int) substr($item['published_on'], 0, 4) : null;
    }

    private function documentQuarter(string $title): ?int
    {
        return preg_match('/(?:quý|q)\s*([1-4])\b/iu', $title, $match) ? (int) $match[1] : null;
    }

    private function documentSlug(array $item): string
    {
        $path = trim((string) parse_url($item['detail_url'], PHP_URL_PATH), '/');
        $base = Str::slug(preg_replace('/^vn\//', '', $path) ?: $item['title']);

        return Str::limit($base, 220, '').'-'.substr(sha1($item['file_url']), 0, 10);
    }

    private function isFileUrl(string $url): bool
    {
        return in_array($this->extension($url), self::FILE_EXTENSIONS, true);
    }

    private function extension(string $url): string
    {
        return strtolower(pathinfo((string) parse_url($url, PHP_URL_PATH), PATHINFO_EXTENSION));
    }

    private function fileName(string $url, string $extension): string
    {
        $name = rawurldecode(basename((string) parse_url($url, PHP_URL_PATH)));

        return $name !== '' && $name !== '/' ? Str::limit($name, 240, '') : sha1($url).($extension ? ".{$extension}" : '');
    }

    private function mimeType(string $extension): string
    {
        return match ($extension) {
            'pdf' => 'application/pdf',
            'doc' => 'application/msword',
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'xls' => 'application/vnd.ms-excel',
            'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'ppt' => 'application/vnd.ms-powerpoint',
            'pptx' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
            'zip' => 'application/zip',
            'rar' => 'application/vnd.rar',
            default => 'application/octet-stream',
        };
    }
}
