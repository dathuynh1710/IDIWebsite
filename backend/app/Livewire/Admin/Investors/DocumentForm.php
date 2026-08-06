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
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Features\SupportFileUploads\WithFileUploads;

#[Layout('layouts.admin')]
class DocumentForm extends AdminComponent
{
    use WithFileUploads;

    public ?InvestorDocument $document = null;

    public ?int $document_category_id = null;

    public string $document_number = '';

    public ?int $year = null;

    public ?int $quarter = null;

    public string $published_on = '';

    public int $sort_order = 0;

    public bool $is_featured = false;

    public bool $is_active = true;

    public array $title = ['vi' => '', 'en' => '', 'zh' => ''];

    public array $summary = ['vi' => '', 'en' => '', 'zh' => ''];

    public array $display_name = ['vi' => '', 'en' => '', 'zh' => ''];

    public array $uploads = [];

    public array $removeFiles = ['vi' => false, 'en' => false, 'zh' => false];

    public function mount(?InvestorDocument $document = null): void
    {
        Gate::authorize($document?->exists ? 'investors.update' : 'investors.create');
        $this->document = $document?->exists ? $document->load('files.media') : null;
        $this->year = (int) now()->year;
        $this->published_on = now()->toDateString();
        if ($this->document) {
            foreach (['title', 'summary'] as $field) {
                $this->{$field} = array_merge($this->{$field}, $this->document->getTranslations($field));
            }
            foreach (['document_category_id', 'year', 'quarter', 'sort_order', 'is_featured', 'is_active'] as $field) {
                $this->{$field} = $this->document->{$field};
            }
            $this->document_number = (string) ($this->document->document_number ?? '');
            $this->published_on = $this->document->published_on?->toDateString() ?? '';
            foreach ($this->document->files as $file) {
                if (isset($this->display_name[$file->locale])) {
                    $this->display_name[$file->locale] = $file->getTranslation('display_name', $file->locale, false)
                        ?: $file->media->original_name;
                }
            }
        }
    }

    public function removeFile(string $locale): void
    {
        abort_unless(in_array($locale, ['vi', 'en', 'zh'], true), 404);
        $this->uploads[$locale] = null;
        $this->removeFiles[$locale] = true;
    }

    public function save()
    {
        Gate::authorize($this->document ? 'investors.update' : 'investors.create');
        $hasUploads = collect($this->uploads)->contains(fn ($upload) => filled($upload));
        $hasRemovals = in_array(true, $this->removeFiles, true);
        $maxKilobytes = $this->maxUploadMegabytes() * 1024;
        $data = $this->validate([
            'document_category_id' => ['required', Rule::exists('document_categories', 'id')->whereNull('deleted_at')],
            'document_number' => ['nullable', 'string', 'max:100'],
            'year' => ['nullable', 'integer', 'min:2000', 'max:2100'],
            'quarter' => ['nullable', 'integer', 'min:1', 'max:4'],
            'published_on' => ['nullable', 'date'],
            'sort_order' => ['required', 'integer', 'min:0'],
            'is_featured' => ['boolean'],
            'is_active' => ['boolean'],
            'title.vi' => ['required', 'string', 'max:255'],
            'title.en' => ['nullable', 'string', 'max:255'],
            'title.zh' => ['nullable', 'string', 'max:255'],
            'summary.*' => ['nullable', 'string', 'max:5000'],
            'display_name.*' => ['nullable', 'string', 'max:255'],
            'uploads.*' => ['nullable', 'file', 'mimes:pdf,doc,docx,xls,xlsx,ppt,pptx,zip', "max:{$maxKilobytes}"],
            'removeFiles.*' => ['boolean'],
        ]);
        if (! $this->document && empty(array_filter($this->uploads))) {
            $this->addError('uploads.vi', 'Vui lòng tải lên ít nhất một tệp.');

            return null;
        }
        foreach (['title', 'summary'] as $field) {
            $data[$field] = collect($data[$field] ?? [])->map(fn ($value) => trim((string) $value))->filter(fn ($value) => $value !== '')->all();
        }
        unset($data['display_name'], $data['uploads'], $data['removeFiles']);
        $data['document_number'] = trim((string) $data['document_number']) ?: null;
        $data['created_by'] = $this->document?->created_by ?? auth()->id();
        $data['updated_by'] = auth()->id();

        DB::transaction(function () use ($data): void {
            $this->document = InvestorDocument::updateOrCreate(['id' => $this->document?->id], $data);
            foreach (['vi', 'en', 'zh'] as $locale) {
                $existing = $this->document->files()->where('locale', $locale)->with('media')->first();
                if ($this->removeFiles[$locale] && ! isset($this->uploads[$locale])) {
                    $this->deleteStoredFile($existing);

                    continue;
                }
                if (! isset($this->uploads[$locale])) {
                    if ($existing && $this->display_name[$locale] !== '') {
                        $existing->update(['display_name' => [$locale => trim($this->display_name[$locale])]]);
                    }

                    continue;
                }
                $upload = $this->uploads[$locale];
                $path = $upload->store('investor-documents/'.($this->year ?: 'general'), 'public');
                $media = Media::create([
                    'disk' => 'public', 'directory' => dirname($path), 'file_name' => basename($path),
                    'original_name' => $upload->getClientOriginalName(), 'mime_type' => $upload->getMimeType(),
                    'extension' => $upload->getClientOriginalExtension(), 'file_size' => $upload->getSize(),
                    'title' => $this->title, 'alt_text' => $this->title, 'created_by' => auth()->id(),
                ]);
                $this->deleteStoredFile($existing);
                InvestorDocumentFile::create([
                    'investor_document_id' => $this->document->id, 'media_id' => $media->id, 'locale' => $locale,
                    'display_name' => [$locale => trim($this->display_name[$locale]) ?: $upload->getClientOriginalName()],
                    'sort_order' => array_search($locale, ['vi', 'en', 'zh'], true),
                ]);
            }
        });

        $this->flashToast(match (true) {
            $hasUploads && $hasRemovals => 'Đã tải tệp mới lên và xóa tệp cũ thành công.',
            $hasUploads => 'Đã tải tệp lên thành công.',
            $hasRemovals => 'Đã xóa tệp thành công.',
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

    public function render()
    {
        return view('livewire.admin.investors.document-form', [
            'categories' => DocumentCategory::where('is_active', true)->orderByDesc('sort_order')->get(),
            'locales' => ['vi' => 'Tiếng Việt', 'en' => 'English', 'zh' => '中文'],
            'maxUploadMegabytes' => $this->maxUploadMegabytes(),
            'breadcrumbs' => [['label' => 'Bảng điều khiển', 'route' => 'admin.dashboard'], ['label' => 'Quan hệ cổ đông', 'route' => 'admin.investors.documents.index'], ['label' => $this->document ? 'Cập nhật' : 'Thêm mới']],
        ]);
    }
}
