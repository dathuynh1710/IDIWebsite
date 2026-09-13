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
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;

class ImportInvestorRelations extends Command
{
  protected $signature = 'investors:import-idi
        {--category=* : Chỉ nhập các slug danh mục được chọn}
        {--locale=* : Chỉ nhập các locale vi, en hoặc zh được chọn}
        {--dry-run : Chỉ đọc và thống kê, không ghi database}';

  protected $description = 'Nhập thư viện Quan hệ cổ đông đa ngôn ngữ công khai từ idiseafood.com';

  private const CATEGORIES = [
    'thong-bao' => [
      'sources' => [
        'vi' => ['url' => 'https://idiseafood.com/vn/thong-bao.html', 'lang' => 'vn', 'root' => 'vn/quan-he-co-dong'],
        'en' => ['url' => 'https://idiseafood.com/en/notification.html', 'lang' => 'en', 'root' => 'en/investor-relations'],
      ],
      'name' => ['vi' => 'Thông báo', 'en' => 'Announcements', 'zh' => '公告'],
      'description' => [
        'vi' => 'Thông báo, nghị quyết và thông tin quản trị dành cho cổ đông.',
        'en' => 'Announcements, resolutions and governance information for shareholders.',
        'zh' => '面向股东的公告、决议和治理信息。',
      ],
      'sort_order' => 50,
    ],
    'bao-cao-tai-chinh' => [
      'sources' => [
        'vi' => ['url' => 'https://idiseafood.com/vn/bao-cao-tai-chinh.html', 'lang' => 'vn', 'root' => 'vn/quan-he-co-dong'],
        'en' => ['url' => 'https://idiseafood.com/en/financial-report.html', 'lang' => 'en', 'root' => 'en/investor-relations'],
      ],
      'name' => ['vi' => 'Báo cáo tài chính', 'en' => 'Financial reports', 'zh' => '财务报告'],
      'description' => [
        'vi' => 'Báo cáo tài chính riêng, hợp nhất và các văn bản giải trình.',
        'en' => 'Separate and consolidated financial statements and explanatory documents.',
        'zh' => '单独及合并财务报表和说明文件。',
      ],
      'sort_order' => 40,
    ],
    'bao-cao-thuong-nien' => [
      'sources' => [
        'vi' => ['url' => 'https://idiseafood.com/vn/bao-cao-thuong-nien.html', 'lang' => 'vn', 'root' => 'vn/quan-he-co-dong'],
        'en' => ['url' => 'https://idiseafood.com/en/annual-report.html', 'lang' => 'en', 'root' => 'en/investor-relations'],
      ],
      'name' => ['vi' => 'Báo cáo thường niên', 'en' => 'Annual reports', 'zh' => '年度报告'],
      'description' => [
        'vi' => 'Báo cáo thường niên của IDI qua từng năm.',
        'en' => 'IDI annual reports by year.',
        'zh' => 'IDI 历年年度报告。',
      ],
      'sort_order' => 30,
    ],
    'dai-hoi-co-dong' => [
      'sources' => [
        'vi' => ['url' => 'https://idiseafood.com/vn/dai-hoi-co-dong.html', 'lang' => 'vn', 'root' => 'vn/quan-he-co-dong'],
        'en' => ['url' => 'https://idiseafood.com/en/shareholders-meeting.html', 'lang' => 'en', 'root' => 'en/investor-relations'],
      ],
      'name' => ['vi' => 'Đại hội đồng cổ đông', 'en' => 'General Meeting of Shareholders', 'zh' => '股东大会'],
      'description' => [
        'vi' => 'Thư mời, tài liệu, biểu mẫu, biên bản và nghị quyết Đại hội đồng cổ đông.',
        'en' => 'Invitations, documents, forms, minutes and shareholder meeting resolutions.',
        'zh' => '股东大会邀请函、文件、表格、会议记录和决议。',
      ],
      'sort_order' => 20,
    ],
    'trai-phieu' => [
      'sources' => [
        'vi' => ['url' => 'https://idiseafood.com/vn/trai-phieu.html', 'lang' => 'vn', 'root' => 'vn/quan-he-co-dong'],
        'en' => ['url' => 'https://idiseafood.com/en/bonds.html', 'lang' => 'en', 'root' => 'en/investor-relations'],
      ],
      'name' => ['vi' => 'Trái phiếu', 'en' => 'Bonds', 'zh' => '债券'],
      'description' => [
        'vi' => 'Thông tin phát hành, sử dụng vốn, cam kết và thanh toán trái phiếu.',
        'en' => 'Bond issuance, use of proceeds, commitments and payment information.',
        'zh' => '债券发行、资金使用、承诺和付款信息。',
      ],
      'sort_order' => 10,
    ],
  ];

  private const FILE_EXTENSIONS = ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'zip', 'rar'];

  private const CHINESE_SOURCE = [
    'url' => 'https://idiseafood.com/cn/investor-relations.html',
    'lang' => 'cn',
    'root' => 'cn/investor-relations',
  ];

  public function handle(): int
  {
    $selectedCategories = collect($this->option('category'))->filter()->values();
    $definitions = collect(self::CATEGORIES)
      ->when($selectedCategories->isNotEmpty(), fn(Collection $items) => $items->only($selectedCategories));

    if ($definitions->isEmpty()) {
      $this->error('Không tìm thấy danh mục cần nhập.');

      return self::FAILURE;
    }

    $locales = $this->selectedLocales();
    if ($locales->isEmpty()) {
      $this->error('Không có locale vi, en hoặc zh khả dụng để nhập.');

      return self::FAILURE;
    }

    $dryRun = (bool) $this->option('dry-run');
    $totalImported = 0;
    $totalSkipped = 0;

    foreach ($definitions as $slug => $definition) {
      $category = null;

      foreach ($locales as $locale) {
        $source = $definition['sources'][$locale] ?? null;
        if (! $source) {
          continue;
        }

        $category ??= $dryRun ? null : $this->upsertCategory($slug, $definition);

        $this->newLine();
        $this->info("Đang đọc {$definition['name'][$locale]} ({$locale})...");

        try {
          $items = $this->fetchCategory($source, $locale, $slug);
        } catch (Throwable $exception) {
          $this->error("Không thể đọc {$source['url']}: {$exception->getMessage()}");

          return self::FAILURE;
        }

        if ($dryRun) {
          $this->line("Tìm thấy {$items->count()} tài liệu {$locale} (dry-run).");
          $totalImported += $items->count();

          continue;
        }

        $imported = 0;
        $skipped = 0;

        foreach ($items as $position => $item) {
          try {
            $this->upsertDocument($category, $item, $locale, $items->count() - $position);
            $imported++;
          } catch (Throwable $exception) {
            $skipped++;
            $this->warn("Bỏ qua “{$item['title']}” ({$locale}): {$exception->getMessage()}");
          }
        }

        $this->line("Đã đồng bộ {$imported}/{$items->count()} tài liệu {$locale}; bỏ qua {$skipped}.");
        $totalImported += $imported;
        $totalSkipped += $skipped;
      }
    }

    if ($locales->contains('zh')) {
      [$imported, $skipped] = $this->importChineseFeed($dryRun);
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

  private function importChineseFeed(bool $dryRun): array
  {
    $this->newLine();
    $this->info('Đang kiểm tra nguồn tiếng Trung (zh)...');

    try {
      $html = $this->http()->get(self::CHINESE_SOURCE['url'])->throw()->body();
      $items = collect($this->parseItems($html, 'zh'))
        ->filter(fn(array $item): bool => filled($item['title'])
          && filled($item['file_url'])
          && preg_match('/\p{Han}/u', $item['title']) === 1)
        ->map(fn(array $item): array => $this->enrichItem($item, 'zh', 'zh-feed'))
        ->values();
    } catch (Throwable $exception) {
      $this->warn("Không thể kiểm tra nguồn tiếng Trung: {$exception->getMessage()}");

      return [0, 1];
    }

    if ($dryRun) {
      $this->line("Tìm thấy {$items->count()} tài liệu thực sự có metadata tiếng Trung (dry-run).");

      return [$items->count(), 0];
    }

    $imported = 0;
    $skipped = 0;
    foreach ($items as $item) {
      $media = Media::withTrashed()->whereIn('external_url', array_filter([
        $item['file_url'],
        $item['canonical_file_url'],
      ]))->first();
      $document = InvestorDocument::withTrashed()->where('source_key', $item['source_key'])->first()
        ?? $this->documentForMedia($media);

      if (! $document?->category) {
        $skipped++;
        $this->warn("Chưa thể ghép tài liệu tiếng Trung “{$item['title']}” với bản Việt; không tạo duplicate.");

        continue;
      }

      try {
        $this->upsertDocument($document->category, $item, 'zh', $document->sort_order);
        $imported++;
      } catch (Throwable $exception) {
        $skipped++;
        $this->warn("Bỏ qua “{$item['title']}” (zh): {$exception->getMessage()}");
      }
    }

    if ($items->isEmpty()) {
      $this->line('Trang /cn hiện chỉ lặp lại tiêu đề/file tiếng Việt; không có file nào bị gắn nhãn zh.');
    } else {
      $this->line("Đã đồng bộ {$imported}/{$items->count()} tài liệu zh; bỏ qua {$skipped}.");
    }

    return [$imported, $skipped];
  }

  private function selectedLocales(): Collection
  {
    $requested = collect($this->option('locale'))
      ->filter()
      ->map(fn(string $locale): string => match (strtolower($locale)) {
        'zh-cn', 'zh-hans' => 'zh',
        default => strtolower($locale),
      })
      ->intersect(['vi', 'en', 'zh'])
      ->unique()
      ->values();

    $available = DB::table('locales')
      ->whereIn('code', ['vi', 'en', 'zh'])
      ->where('is_active', true)
      ->pluck('code');

    return ($requested->isEmpty() ? collect(['vi', 'en', 'zh']) : $requested)
      ->intersect($available)
      ->values();
  }

  private function fetchCategory(array $source, string $locale, string $categorySlug): Collection
  {
    $html = $this->http()->get($source['url'])->throw()->body();
    $metadata = $this->pageMetadata($html);
    $items = collect($this->parseItems($html, $locale));

    while ($items->count() < $metadata['totals']) {
      $offset = $items->count();
      $response = $this->http()->asForm()->post($this->ajaxUrl($source), [
        'totals' => $metadata['totals'],
        'npage' => $metadata['npage'],
        'offset' => $offset,
        'cat_id' => $metadata['cat_id'],
        'lang' => $source['lang'],
      ])->throw()->json();

      $nextItems = $this->parseItems((string) ($response['html'] ?? ''), $locale);
      if ($nextItems === []) {
        break;
      }

      $items = $items->concat($nextItems);
    }

    return $items
      ->filter(fn(array $item): bool => filled($item['title']) && filled($item['file_url']))
      ->unique(fn(array $item): string => $item['detail_url'] ?: $item['title'] . '|' . $item['published_on'])
      ->map(fn(array $item): array => $this->enrichItem($item, $locale, $categorySlug))
      ->values();
  }

  private function ajaxUrl(array $source): string
  {
    return 'https://idiseafood.com/' . trim($source['root'], '/') . '/ajax/load_news.html';
  }

  private function pageMetadata(string $html): array
  {
    $xpath = $this->xpath($html);
    $value = fn(string $id, int $default): int => (int) ($xpath->evaluate("string(//input[@id='{$id}']/@value)") ?: $default);

    return [
      'npage' => max(1, $value('npage', 20)),
      'totals' => max(0, $value('totals', 0)),
      'cat_id' => max(0, $value('cat_id', 0)),
    ];
  }

  private function parseItems(string $html, string $locale): array
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
      $detailUrl = $titleNode instanceof DOMElement ? $this->sourceUrl($titleNode->getAttribute('href')) : '';
      $fileUrl = $fileNode instanceof DOMElement ? $this->sourceUrl($fileNode->getAttribute('href')) : '';

      if ($fileUrl === '' && $detailUrl !== '') {
        $fileUrl = $this->resolveFileUrl($detailUrl) ?: '';
      } elseif ($fileUrl !== '' && ! $this->isFileUrl($fileUrl)) {
        $fileUrl = $this->resolveFileUrl($fileUrl) ?: '';
      }

      $items[] = [
        'title' => $title,
        'published_on' => $this->parseDate($this->cleanText($dateNode?->textContent), $locale),
        'detail_url' => $detailUrl,
        'file_url' => $fileUrl,
      ];
    }

    return $items;
  }

  private function enrichItem(array $item, string $locale, string $categorySlug): array
  {
    $canonicalDetailUrl = $locale === 'vi' ? $item['detail_url'] : null;
    $canonicalFileUrl = $locale === 'vi' ? $item['file_url'] : null;
    $canonicalTitle = $locale === 'vi' ? $item['title'] : null;

    if ($locale !== 'vi' && $item['detail_url']) {
      try {
        $detailXpath = $this->xpath($this->http()->get($item['detail_url'])->throw()->body());
        $candidate = trim((string) $detailXpath->evaluate("string(//a[normalize-space(.)='VN']/@href)"));
        $candidate = $this->sourceUrl($candidate);

        if ($this->isVietnameseDetailUrl($candidate)) {
          $canonicalDetailUrl = $candidate;
          $canonicalHtml = $this->http()->get($candidate)->throw()->body();
          $canonicalXpath = $this->xpath($canonicalHtml);
          $canonicalTitle = $this->cleanText($canonicalXpath->evaluate('string(//div[contains(@class, "titleL")]//h1)'));
          $canonicalFileUrl = $this->fileUrlFromXpath($canonicalXpath);
        }
      } catch (Throwable) {
        // Keep the localized source item importable even when a legacy detail page is malformed.
      }
    }

    $item['source_key'] = $this->sourceKey($canonicalDetailUrl, $canonicalFileUrl, $categorySlug, $item);
    $item['canonical_file_url'] = $canonicalFileUrl;
    $item['canonical_title'] = $canonicalTitle;

    return $item;
  }

  private function resolveFileUrl(string $detailUrl): ?string
  {
    try {
      return $this->fileUrlFromXpath($this->xpath($this->http()->get($detailUrl)->throw()->body()));
    } catch (Throwable) {
      return null;
    }
  }

  private function fileUrlFromXpath(DOMXPath $xpath): ?string
  {
    $links = $xpath->query("//a[contains(@href, '/vnt_upload/')]/@href");

    foreach ($links ?: [] as $link) {
      $url = $this->sourceUrl($link->nodeValue);
      if ($this->isFileUrl($url)) {
        return $url;
      }
    }

    return null;
  }

  private function upsertCategory(string $slug, array $definition): DocumentCategory
  {
    $category = DocumentCategory::withTrashed()
      ->where('slug->vi', $slug)
      ->first() ?? new DocumentCategory;

    $category->fill([
      'name' => array_merge($category->getTranslations('name'), $definition['name']),
      'slug' => array_merge($category->getTranslations('slug'), [
        'vi' => $slug,
        'en' => Str::slug($definition['name']['en']),
        'zh' => $slug,
      ]),
      'description' => array_merge($category->getTranslations('description'), $definition['description']),
      'sort_order' => $definition['sort_order'],
      'is_active' => true,
    ]);
    $category->deleted_at = null;
    $category->save();

    return $category;
  }

  private function upsertDocument(DocumentCategory $category, array $item, string $locale, int $sortOrder): void
  {
    DB::transaction(function () use ($category, $item, $locale, $sortOrder): void {
      $media = Media::withTrashed()->where('external_url', $item['file_url'])->first();
      $document = InvestorDocument::withTrashed()->where('source_key', $item['source_key'])->first()
        ?? $this->documentForMedia($media)
        ?? $this->legacyDocument($category, $item, $locale)
        ?? new InvestorDocument;

      $item['canonical_title'] = $item['canonical_title']
        ?: $document->getTranslation('title', 'vi', false);
      $titles = $document->getTranslations('title');
      if ($this->isLocalizedTitle($item, $locale)) {
        $titles[$locale] = $item['title'];
      }

      $sharedValues = [
        'document_category_id' => $category->id,
        'year' => $this->documentYear($item),
        'quarter' => $this->documentQuarter($item['title']),
        'published_on' => $item['published_on'],
        'sort_order' => $sortOrder,
      ];
      if ($document->exists && $locale !== 'vi') {
        $sharedValues = collect($sharedValues)
          ->mapWithKeys(fn($value, string $field): array => [$field => $document->{$field} ?? $value])
          ->all();
      }

      $document->fill(array_merge($sharedValues, [
        'title' => $titles,
        'summary' => $document->summary,
        'slug' => $document->slug ?: $this->documentSlug($item),
        'source_key' => $document->source_key ?: $item['source_key'],
        'is_active' => true,
      ]));
      $document->deleted_at = null;
      $document->save();

      if (! $this->hasLocalizedFile($item, $locale)) {
        return;
      }

      if ($locale !== 'vi' && $media && $document->files()->where('locale', 'vi')->where('media_id', $media->id)->exists()) {
        return;
      }

      $existingFile = $document->files()->where('locale', $locale)->with('media')->first();
      if ($existingFile?->media) {
        $existingPath = trim($existingFile->media->directory . '/' . $existingFile->media->file_name, '/');
        if ($existingPath !== '' && Storage::disk($existingFile->media->disk)->exists($existingPath)) {
          return;
        }
      }

      $remotePath = null;
      if (! $this->mediaHasLocalFile($media)) {
        $remotePath = $this->fetchRemoteFile($item['file_url'], $this->extension($item['file_url']));
        if ($remotePath === null) {
          return;
        }
      }

      $media ??= new Media;
      $extension = $this->extension($item['file_url']);
      $fileName = $this->fileName($item['file_url'], $extension);
      $mediaTitles = is_array($media->title) ? $media->title : [];
      if ($this->isLocalizedTitle($item, $locale)) {
        $mediaTitles[$locale] = $item['title'];
      }

      $media->fill([
        'disk' => $media->disk ?: 'public',
        'directory' => $media->directory ?: 'investor-imports',
        'file_name' => $media->file_name ?: $fileName,
        'external_url' => $item['file_url'],
        'original_name' => $media->original_name ?: $fileName,
        'mime_type' => $this->mimeType($extension),
        'extension' => $extension,
        'title' => $mediaTitles,
      ]);
      $media->deleted_at = null;
      $media->save();
      if ($remotePath !== null) {
        $this->storeRemoteFile($media, $item['file_url'], $locale, $extension, $remotePath);
      }

      if ($locale === 'vi') {
        $document->files()
          ->where('media_id', $media->id)
          ->whereNotNull('locale')
          ->where('locale', '!=', 'vi')
          ->delete();
      }

      $displayName = $this->localizedFileName($item['title'], $extension);
      $documentFile = InvestorDocumentFile::firstOrNew([
        'investor_document_id' => $document->id,
        'locale' => $locale,
      ]);
      $displayNames = $documentFile->getTranslations('display_name');
      $displayNames[$locale] = $displayName;
      $documentFile->fill([
        'media_id' => $media->id,
        'display_name' => $displayNames,
        'sort_order' => 0,
      ])->save();
    });
  }

  private function documentForMedia(?Media $media): ?InvestorDocument
  {
    if (! $media) {
      return null;
    }

    return InvestorDocumentFile::with('document')
      ->where('media_id', $media->id)
      ->get()
      ->pluck('document')
      ->filter()
      ->first();
  }

  private function legacyDocument(DocumentCategory $category, array $item, string $locale): ?InvestorDocument
  {
    $query = InvestorDocument::withTrashed()
      ->where('document_category_id', $category->id)
      ->when($this->documentYear($item), fn($query, int $year) => $query->where('year', $year));
    $document = (clone $query)->where("title->{$locale}", $item['title'])->first();

    if ($document || $category->getTranslation('slug', 'vi', false) !== 'bao-cao-thuong-nien') {
      return $document;
    }

    return $query->first();
  }

  private function mediaHasLocalFile(?Media $media): bool
  {
    if (! $media) {
      return false;
    }

    $currentPath = trim($media->directory . '/' . $media->file_name, '/');

    try {
      return $currentPath !== '' && Storage::disk($media->disk)->exists($currentPath);
    } catch (Throwable) {
      return false;
    }
  }

  private function fetchRemoteFile(string $url, string $extension): ?string
  {
    $temporaryPath = tempnam(sys_get_temp_dir(), 'idi-investor-');
    if ($temporaryPath === false) {
      return null;
    }

    try {
      $this->http()->sink($temporaryPath)->get($url)->throw();

      if (! $this->looksLikeFile($temporaryPath, $extension)) {
        @unlink($temporaryPath);

        return null;
      }

      return $temporaryPath;
    } catch (Throwable) {
      @unlink($temporaryPath);

      return null;
    }
  }

  private function storeRemoteFile(Media $media, string $url, string $locale, string $extension, string $temporaryPath): void
  {
    try {
      $safeBase = Str::slug(pathinfo($this->fileName($url, $extension), PATHINFO_FILENAME)) ?: sha1($url);
      $fileName = substr(sha1($url), 0, 12) . '-' . Str::limit($safeBase, 180, '') . ($extension ? ".{$extension}" : '');
      $directory = "investor-documents/imported/{$locale}";
      $stream = fopen($temporaryPath, 'rb');
      if ($stream === false) {
        return;
      }

      try {
        Storage::disk('public')->put("{$directory}/{$fileName}", $stream);
      } finally {
        fclose($stream);
      }

      $media->forceFill([
        'disk' => 'public',
        'directory' => $directory,
        'file_name' => $fileName,
        'file_size' => filesize($temporaryPath) ?: null,
      ])->save();
    } catch (Throwable) {
      // The source was verified and remains available through the internal download route.
    } finally {
      @unlink($temporaryPath);
    }
  }

  private function looksLikeFile(string $path, string $extension): bool
  {
    $stream = @fopen($path, 'rb');
    if ($stream === false) {
      return false;
    }

    try {
      $signature = fread($stream, 8) ?: '';
    } finally {
      fclose($stream);
    }

    return match ($extension) {
      'pdf' => str_starts_with(ltrim($signature), '%PDF-'),
      'zip', 'docx', 'xlsx', 'pptx' => str_starts_with($signature, 'PK'),
      'rar' => str_starts_with($signature, 'Rar!'),
      default => $signature !== '',
    };
  }

  private function hasLocalizedFile(array $item, string $locale): bool
  {
    if ($locale === 'vi' || blank($item['canonical_file_url'])) {
      return true;
    }

    return $this->normalizeUrl($item['file_url']) !== $this->normalizeUrl($item['canonical_file_url']);
  }

  private function isLocalizedTitle(array $item, string $locale): bool
  {
    if ($locale === 'vi') {
      return true;
    }

    if ($locale === 'zh') {
      return preg_match('/\p{Han}/u', $item['title']) === 1;
    }

    return preg_match('/[A-Za-z]/', $item['title']) === 1
      && preg_match('/[À-ỹĐđ]/u', $item['title']) !== 1
      && $this->cleanText($item['title']) !== $this->cleanText($item['canonical_title']);
  }

  private function sourceKey(?string $detailUrl, ?string $fileUrl, string $categorySlug, array $item): string
  {
    if ($detailUrl) {
      $path = trim((string) parse_url($detailUrl, PHP_URL_PATH), '/');
      if ($path !== '') {
        return Str::limit('idi:' . $path, 500, '');
      }
    }

    if ($fileUrl) {
      return 'idi-file:' . sha1($this->normalizeUrl($fileUrl));
    }

    return 'idi-item:' . sha1($categorySlug . '|' . $item['published_on'] . '|' . $item['detail_url'] . '|' . $item['file_url']);
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
        'vi' => 'Mang thành tâm biến thành lợi nhuận. I.D.I gặt hái được thành công của mình nhờ vào việc phát triển và tuân theo một chiến lược toàn diện, gắn kết các mục tiêu và nhiệm vụ với các tôn chỉ hoạt động vì Hành tinh, Con người và Sản phẩm.',
        'en' => 'IDI creates long-term value through a comprehensive strategy connecting business goals with Planet, People and Product.',
        'zh' => 'IDI 通过将商业目标与地球、人类和产品相结合的综合战略创造长期价值。',
      ], JSON_UNESCAPED_UNICODE),
      'updated_at' => now(),
    ]);
  }

  private function http(): PendingRequest
  {
    return Http::withHeaders([
      'Accept' => 'text/html,application/json,application/pdf,application/octet-stream',
      'User-Agent' => 'IDI-Seafood-CMS/1.0 (+https://idiseafood.com)',
    ])->withoutVerifying()->retry(3, 500)->timeout(60);
  }

  private function xpath(string $html): DOMXPath
  {
    $document = new DOMDocument('1.0', 'UTF-8');
    $loaded = @$document->loadHTML('<?xml encoding="UTF-8">' . $html, LIBXML_NOERROR | LIBXML_NOWARNING);
    if (! $loaded) {
      throw new RuntimeException('HTML nguồn không hợp lệ.');
    }

    return new DOMXPath($document);
  }

  private function cleanText(?string $value): string
  {
    return trim(preg_replace('/\s+/u', ' ', html_entity_decode((string) $value, ENT_QUOTES | ENT_HTML5, 'UTF-8')) ?? '');
  }

  private function parseDate(string $date, string $locale): ?string
  {
    try {
      return ($locale === 'vi'
        ? Carbon::createFromFormat('d/m/Y', $date)
        : Carbon::createFromFormat('F j, Y', $date, 'en'))
        ->toDateString();
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
    return preg_match('/(?:quý|quarter|q)\s*[.\-]?\s*([1-4])\b/iu', $title, $match) ? (int) $match[1] : null;
  }

  private function documentSlug(array $item): string
  {
    $identity = preg_replace('/^idi:/', '', $item['source_key']);
    $base = Str::slug(basename((string) $identity, '.html') ?: $item['title']);

    return Str::limit($base, 220, '') . '-' . substr(sha1($item['source_key']), 0, 10);
  }

  private function isFileUrl(string $url): bool
  {
    return $this->isSourceUrl($url) && in_array($this->extension($url), self::FILE_EXTENSIONS, true);
  }

  private function isVietnameseDetailUrl(string $url): bool
  {
    return $this->isSourceUrl($url)
      && str_starts_with((string) parse_url($url, PHP_URL_PATH), '/vn/')
      && ! str_contains(substr($url, 8), 'https://');
  }

  private function isSourceUrl(string $url): bool
  {
    $host = strtolower((string) parse_url($url, PHP_URL_HOST));

    return parse_url($url, PHP_URL_SCHEME) === 'https'
      && in_array($host, ['idiseafood.com', 'www.idiseafood.com'], true);
  }

  private function sourceUrl(?string $url): string
  {
    $url = trim((string) $url);
    if (str_starts_with($url, '/')) {
      $url = 'https://idiseafood.com' . $url;
    }

    return $this->isSourceUrl($url) ? $url : '';
  }

  private function normalizeUrl(?string $url): string
  {
    $url = trim((string) $url);
    $parts = parse_url($url);
    if (! is_array($parts)) {
      return $url;
    }

    return strtolower((string) ($parts['scheme'] ?? '')) . '://' . strtolower((string) ($parts['host'] ?? ''))
      . '/' . ltrim((string) ($parts['path'] ?? ''), '/');
  }

  private function extension(string $url): string
  {
    return strtolower(pathinfo((string) parse_url($url, PHP_URL_PATH), PATHINFO_EXTENSION));
  }

  private function fileName(string $url, string $extension): string
  {
    $name = rawurldecode(basename((string) parse_url($url, PHP_URL_PATH)));

    return $name !== '' && $name !== '/' ? Str::limit($name, 240, '') : sha1($url) . ($extension ? ".{$extension}" : '');
  }

  private function localizedFileName(string $title, string $extension): string
  {
    $suffix = $extension ? '.' . strtolower($extension) : '';

    return Str::limit($title, 240 - strlen($suffix), '') . $suffix;
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
