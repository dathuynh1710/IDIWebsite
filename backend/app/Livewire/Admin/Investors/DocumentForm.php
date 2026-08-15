<?php

namespace App\Livewire\Admin\Investors;

use App\Livewire\AdminComponent;
use App\Models\DocumentCategory;
use App\Models\InvestorDocument;
use App\Models\InvestorDocumentFile;
use App\Models\Media;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Features\SupportFileUploads\WithFileUploads;

#[Layout('layouts.admin')]
class DocumentForm extends AdminComponent
{
    use WithFileUploads;

    public ?InvestorDocument $document = null;
    public ?int $document_category_id = null;
    public string $published_on = '';
    public bool $is_active = true;
    public string $slug = '';
    public string $seo_title = '';
    public string $meta_description = '';
    public array $enabled_locales = ['vi'];
    public array $title = ['vi' => '', 'en' => '', 'zh' => ''];
    public array $summary = ['vi' => '', 'en' => '', 'zh' => ''];
    public array $uploads = [];
    public array $removeFiles = ['vi' => false, 'en' => false, 'zh' => false];

    public function mount(?InvestorDocument $document = null): void
    {
        Gate::authorize($document?->exists ? 'investors.update' : 'investors.create');
        $this->document = $document?->exists ? $document->load('files.media') : null;
        $this->published_on = now()->toDateString();

        if (! $this->document) {
            return;
        }

        foreach (['title', 'summary'] as $field) {
            $this->{$field} = array_merge($this->{$field}, $this->document->getTranslations($field));
        }
        foreach (['document_category_id', 'is_active', 'slug', 'seo_title', 'meta_description'] as $field) {
            $this->{$field} = $this->document->{$field} ?? ($this->{$field} ?? '');
        }
        $this->published_on = $this->document->published_on?->toDateString() ?? '';
        $this->enabled_locales = collect(['vi', 'en', 'zh'])
            ->filter(fn (string $locale) => $locale === 'vi' || $this->hasLocalizedContent($locale))
            ->values()
            ->all();
    }

    public function updatedEnabledLocales(): void
    {
        $this->enabled_locales = collect($this->enabled_locales)
            ->push('vi')
            ->intersect(['vi', 'en', 'zh'])
            ->unique()
            ->sortBy(fn (string $locale): int => array_search($locale, ['vi', 'en', 'zh'], true))
            ->values()
            ->all();
    }

    public function removeFile(string $locale): void
    {
        abort_unless($locale === 'vi', 404);
        $this->uploads[$locale] = null;
        $this->removeFiles[$locale] = true;
    }

    public function generateSlug(): void
    {
        $this->slug = Str::slug($this->title['vi'] ?? '');
    }

    public function save()
    {
        Gate::authorize($this->document ? 'investors.update' : 'investors.create');
        $this->updatedEnabledLocales();
        $this->slug = Str::slug($this->slug ?: ($this->title['vi'] ?? ''));
        $hasUpload = filled($this->uploads['vi'] ?? null);
        $hasRemoval = $this->removeFiles['vi'];
        $maxKilobytes = $this->maxUploadMegabytes() * 1024;

        $rules = [
            'document_category_id' => ['required', Rule::exists('document_categories', 'id')->whereNull('deleted_at')],
            'published_on' => ['nullable', 'date'],
            'is_active' => ['boolean'],
            'enabled_locales' => ['required', 'array', 'min:1'],
            'enabled_locales.*' => ['required', Rule::in(['vi', 'en', 'zh'])],
            'slug' => ['required', 'string', 'max:255', Rule::unique('investor_documents', 'slug')->ignore($this->document?->id)],
            'seo_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string', 'max:500'],
            'uploads.vi' => ['nullable', 'file', 'mimes:pdf,doc,docx,xls,xlsx,ppt,pptx,zip', "max:{$maxKilobytes}"],
            'removeFiles.vi' => ['boolean'],
        ];
        foreach ($this->enabled_locales as $locale) {
            $rules["title.{$locale}"] = ['required', 'string', 'max:255'];
            $rules["summary.{$locale}"] = ['nullable', 'string', 'max:5000'];
        }
        $data = $this->validate($rules);

        if (! $this->document && ! $hasUpload) {
            $this->addError('uploads.vi', 'Vui lòng tải lên một tệp.');

            return null;
        }


        foreach (['title', 'summary'] as $field) {
            $submitted = collect($data[$field] ?? [])
                ->only($this->enabled_locales)
                ->map(fn ($value) => trim((string) $value))
                ->filter(fn ($value) => $value !== '')
                ->all();
            $data[$field] = $this->mergeEnabledTranslations($field, $submitted);
        }
        unset($data['uploads'], $data['removeFiles'], $data['enabled_locales']);
        foreach (['slug', 'seo_title', 'meta_description'] as $field) {
            $data[$field] = trim((string) ($data[$field] ?? '')) ?: null;
        }
        $data['created_by'] = $this->document?->created_by ?? auth()->id();
        $data['updated_by'] = auth()->id();

        DB::transaction(function () use ($data): void {
            $this->document = InvestorDocument::updateOrCreate(['id' => $this->document?->id], $data);
            $existing = $this->document->files()->where('locale', 'vi')->with('media')->first();

            if ($this->removeFiles['vi'] && ! isset($this->uploads['vi'])) {
                $this->deleteStoredFile($existing);
            } elseif (isset($this->uploads['vi'])) {
                $upload = $this->uploads['vi'];
                $path = $upload->store('investor-documents', 'public');
                $media = Media::create([
                    'disk' => 'public', 'directory' => dirname($path), 'file_name' => basename($path),
                    'original_name' => $upload->getClientOriginalName(), 'mime_type' => $upload->getMimeType(),
                    'extension' => $upload->getClientOriginalExtension(), 'file_size' => $upload->getSize(),
                    'title' => $this->title, 'alt_text' => $this->title, 'created_by' => auth()->id(),
                ]);
                $this->deleteStoredFile($existing);
                InvestorDocumentFile::create([
                    'investor_document_id' => $this->document->id, 'media_id' => $media->id, 'locale' => 'vi',
                    'display_name' => ['vi' => $upload->getClientOriginalName()], 'sort_order' => 0,
                ]);
            }
        });

        $this->flashToast(match (true) {
            $hasUpload && $hasRemoval => 'Đã tải tệp mới lên và xóa tệp cũ thành công.',
            $hasUpload => 'Đã tải tệp lên thành công.',
            $hasRemoval => 'Đã xóa tệp thành công.',
            default => 'Đã lưu tài liệu quan hệ cổ đông.',
        });

        return $this->redirectRoute('admin.investors.documents.index', navigate: true);
    }

    private function deleteStoredFile(?InvestorDocumentFile $file): void
    {
        if (! $file) {
            return;
        }
        $media = $file->media;
        $file->delete();
        if ($media) {
            Storage::disk($media->disk)->delete(trim($media->directory.'/'.$media->file_name, '/'));
            $media->delete();
        }
    }

    private function maxUploadMegabytes(): int
    {
        return (int) (DB::table('module_settings')->join('modules', 'modules.id', '=', 'module_settings.module_id')
            ->where('modules.code', 'investors')->where('setting_key', 'max_upload_size')->value('setting_value') ?: 20);
    }

    private function mergeEnabledTranslations(string $field, array $submitted): array
    {
        $translations = $this->document?->getTranslations($field) ?? [];

        foreach (['vi', 'en', 'zh'] as $locale) {
            if (in_array($locale, $this->enabled_locales, true)) {
                if (array_key_exists($locale, $submitted)) {
                    $translations[$locale] = $submitted[$locale];
                } else {
                    unset($translations[$locale]);
                }
            } else {
                unset($translations[$locale]);
            }
        }

        return $translations;
    }

    private function hasLocalizedContent(string $locale): bool
    {
        return filled($this->document?->getTranslation('title', $locale, false))
            || filled($this->document?->getTranslation('summary', $locale, false));
    }

    public function render()
    {
        return view('livewire.admin.investors.document-form', [
            'categories' => DocumentCategory::where('is_active', true)->orderByDesc('sort_order')->get(),
            'maxUploadMegabytes' => $this->maxUploadMegabytes(),
            'currentFile' => $this->document?->files?->firstWhere('locale', 'vi'),
            'locales' => ['vi' => 'Tiếng Việt', 'en' => 'English', 'zh' => '中文'],
            'breadcrumbs' => [['label' => 'Bảng điều khiển', 'route' => 'admin.dashboard'], ['label' => 'Quan hệ cổ đông', 'route' => 'admin.investors.documents.index'], ['label' => $this->document ? 'Cập nhật' : 'Thêm mới']],
        ]);
    }
}
