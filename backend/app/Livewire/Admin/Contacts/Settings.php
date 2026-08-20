<?php

namespace App\Livewire\Admin\Contacts;

use App\Livewire\AdminComponent;
use App\Models\OfficeLocation;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Features\SupportFileUploads\WithFileUploads;

#[Layout('layouts.admin')]
#[Title('Cấu hình liên lạc')]
class Settings extends AdminComponent
{
    use WithFileUploads;

    public array $page_title = ['vi' => '', 'en' => '', 'zh' => ''];

    public array $description = ['vi' => '', 'en' => '', 'zh' => ''];

    public array $seo_title = ['vi' => '', 'en' => '', 'zh' => ''];

    public array $meta_description = ['vi' => '', 'en' => '', 'zh' => ''];

    public array $success_message = ['vi' => '', 'en' => '', 'zh' => ''];

    public bool $is_active = true;

    public bool $form_enabled = true;

    public bool $spam_protection = true;

    public string $notification_email = '';

    public int $items_per_page = 15;

    public bool $showLocationForm = false;

    public ?int $editingLocationId = null;

    public string $location_code = '';

    public array $location_name = ['vi' => '', 'en' => '', 'zh' => ''];

    public array $location_company = ['vi' => '', 'en' => '', 'zh' => ''];

    public array $location_address = ['vi' => '', 'en' => '', 'zh' => ''];

    public string $location_phone = '';

    public string $location_fax = '';

    public string $location_email = '';

    public string $location_map_type = 'embed';

    public string $location_map_embed = '';

    public string $location_map_url = '';

    public $location_map_image;

    public ?string $location_existing_map_image = null;

    public int $location_sort_order = 0;

    public bool $location_is_active = true;

    public ?int $pendingDeleteId = null;

    public string $pendingDeleteName = '';

    public function mount(): void
    {
        Gate::authorize('contacts.manage');
        $module = DB::table('modules')->where('code', 'contact')->first();
        if (! $module) {
            return;
        }
        foreach (['page_title', 'description', 'seo_title', 'meta_description'] as $field) {
            $this->{$field} = array_replace($this->{$field}, json_decode($module->{$field} ?: '[]', true) ?: []);
        }
        $this->is_active = (bool) $module->is_active;
        $settings = DB::table('module_settings')->where('module_id', $module->id)->pluck('setting_value', 'setting_key');
        foreach (['form_enabled', 'spam_protection', 'notification_email', 'items_per_page', 'success_message'] as $key) {
            if ($settings->has($key)) {
                $this->{$key} = json_decode($settings[$key], true);
            }
        }
    }

    public function saveSettings(): void
    {
        Gate::authorize('contacts.manage');
        $validated = $this->validate([
            'page_title.vi' => ['required', 'string', 'max:255'],
            'page_title.en' => ['nullable', 'string', 'max:255'],
            'page_title.zh' => ['nullable', 'string', 'max:255'],
            'description.*' => ['nullable', 'string', 'max:5000'],
            'seo_title.*' => ['nullable', 'string', 'max:255'],
            'meta_description.*' => ['nullable', 'string', 'max:500'],
            'success_message.*' => ['nullable', 'string', 'max:1000'],
            'is_active' => ['required', 'boolean'],
            'form_enabled' => ['required', 'boolean'],
            'spam_protection' => ['required', 'boolean'],
            'notification_email' => ['nullable', 'email', 'max:255'],
            'items_per_page' => ['required', 'integer', 'min:5', 'max:100'],
        ], [], [
            'page_title.vi'       => 'Tiêu đề trang (Tiếng Việt)',
            'page_title.en'       => 'Tiêu đề trang (Tiếng Anh)',
            'page_title.zh'       => 'Tiêu đề trang (Tiếng Trung)',
            'description.vi'      => 'Mô tả (Tiếng Việt)',
            'description.en'      => 'Mô tả (Tiếng Anh)',
            'description.zh'      => 'Mô tả (Tiếng Trung)',
            'seo_title.vi'        => 'Tiêu đề SEO (Tiếng Việt)',
            'seo_title.en'        => 'Tiêu đề SEO (Tiếng Anh)',
            'seo_title.zh'        => 'Tiêu đề SEO (Tiếng Trung)',
            'meta_description.vi' => 'Meta description (Tiếng Việt)',
            'meta_description.en' => 'Meta description (Tiếng Anh)',
            'meta_description.zh' => 'Meta description (Tiếng Trung)',
            'success_message.vi'  => 'Thông báo gửi thành công (Tiếng Việt)',
            'success_message.en'  => 'Thông báo gửi thành công (Tiếng Anh)',
            'success_message.zh'  => 'Thông báo gửi thành công (Tiếng Trung)',
            'is_active'           => 'Trạng thái module',
            'form_enabled'        => 'Kích hoạt biểu mẫu',
            'spam_protection'     => 'Bảo vệ spam',
            'notification_email'  => 'Email nhận thông báo',
            'items_per_page'      => 'Số thư mỗi trang',
        ]);
        $validated['description'] = collect($validated['description'])
            ->map(fn ($html) => $this->sanitizeHtml((string) $html))->all();

        DB::transaction(function () use ($validated): void {
            DB::table('modules')->updateOrInsert(['code' => 'contact'], [
                'name' => 'Contact',
                'module_type' => 'content',
                'page_title' => json_encode($validated['page_title'], JSON_UNESCAPED_UNICODE),
                'description' => json_encode($validated['description'], JSON_UNESCAPED_UNICODE),
                'seo_title' => json_encode($validated['seo_title'], JSON_UNESCAPED_UNICODE),
                'meta_description' => json_encode($validated['meta_description'], JSON_UNESCAPED_UNICODE),
                'is_active' => $validated['is_active'],
                'updated_at' => now(),
                'created_at' => now(),
            ]);
            $moduleId = (int) DB::table('modules')->where('code', 'contact')->value('id');
            foreach ([
                'form_enabled' => 'boolean',
                'spam_protection' => 'boolean',
                'notification_email' => 'text',
                'items_per_page' => 'number',
                'success_message' => 'json',
            ] as $key => $type) {
                DB::table('module_settings')->updateOrInsert([
                    'module_id' => $moduleId,
                    'setting_key' => $key,
                ], [
                    'setting_value' => json_encode($validated[$key], JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR),
                    'setting_type' => $type,
                    'updated_at' => now(),
                    'created_at' => now(),
                ]);
            }
        });
        $this->toast('Đã cập nhật cấu hình liên lạc.');
    }

    public function createLocation(): void
    {
        $this->resetLocationForm();
        $this->resetValidation();
        $this->showLocationForm = true;
    }

    public function editLocation(int $locationId): void
    {
        $this->resetValidation();
        $location = OfficeLocation::findOrFail($locationId);
        $this->editingLocationId = $location->id;
        $this->location_code = $location->code ?? '';
        $this->location_name = array_replace($this->emptyTranslations(), $location->getTranslations('name'));
        $this->location_company = array_replace($this->emptyTranslations(), $location->getTranslations('company'));
        $this->location_address = array_replace($this->emptyTranslations(), $location->getTranslations('address'));
        $this->location_phone = $location->phone ?? '';
        $this->location_fax = $location->fax ?? '';
        $this->location_email = $location->email ?? '';
        $this->location_map_type = $location->map_type ?? 'embed';
        $this->location_map_embed = $location->map_embed ?? '';
        $this->location_map_url = $location->map_url ?? '';
        $this->location_existing_map_image = $location->map_image;
        $this->location_sort_order = $location->sort_order;
        $this->location_is_active = $location->is_active;
        $this->showLocationForm = true;
    }

    public function closeLocationForm(): void
    {
        $this->resetLocationForm();
        $this->resetValidation();
    }

    public function saveLocation(): void
    {
        Gate::authorize('contacts.manage');
        $validated = $this->validate([
            'location_code' => ['nullable', 'string', 'max:100', Rule::unique('office_locations', 'code')->withoutTrashed()->ignore($this->editingLocationId)],
            'location_name.vi' => ['required', 'string', 'max:255'],
            'location_name.en' => ['nullable', 'string', 'max:255'],
            'location_name.zh' => ['nullable', 'string', 'max:255'],
            'location_company.*' => ['nullable', 'string', 'max:255'],
            'location_address.vi' => ['required', 'string', 'max:1000'],
            'location_address.en' => ['nullable', 'string', 'max:1000'],
            'location_address.zh' => ['nullable', 'string', 'max:1000'],
            'location_phone' => ['nullable', 'string', 'max:100'],
            'location_fax' => ['nullable', 'string', 'max:100'],
            'location_email' => ['nullable', 'email', 'max:255'],
            'location_map_type' => ['required', Rule::in(['embed', 'google_maps', 'image', 'none'])],
            'location_map_embed' => [Rule::requiredIf($this->location_map_type === 'embed'), 'nullable', 'string', 'max:10000'],
            'location_map_url' => [Rule::requiredIf($this->location_map_type === 'google_maps'), 'nullable', 'string', 'max:2048'],
            'location_map_image' => [Rule::requiredIf($this->location_map_type === 'image' && blank($this->location_existing_map_image)), 'nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:8192'],
            'location_sort_order' => ['required', 'integer', 'min:0', 'max:999999'],
            'location_is_active' => ['required', 'boolean'],
        ], [], [
            'location_code'        => 'Mã quản trị',
            'location_name.vi'     => 'Tên văn phòng (Tiếng Việt)',
            'location_name.en'     => 'Tên văn phòng (Tiếng Anh)',
            'location_name.zh'     => 'Tên văn phòng (Tiếng Trung)',
            'location_company.vi'  => 'Công ty (Tiếng Việt)',
            'location_company.en'  => 'Công ty (Tiếng Anh)',
            'location_company.zh'  => 'Công ty (Tiếng Trung)',
            'location_address.vi'  => 'Địa chỉ (Tiếng Việt)',
            'location_address.en'  => 'Địa chỉ (Tiếng Anh)',
            'location_address.zh'  => 'Địa chỉ (Tiếng Trung)',
            'location_phone'       => 'Điện thoại',
            'location_fax'         => 'Fax',
            'location_email'       => 'Email',
            'location_map_type'    => 'Kiểu bản đồ',
            'location_map_embed'   => 'Mã nhúng Google Maps',
            'location_map_url'     => 'Liên kết Google Maps',
            'location_map_image'   => 'Ảnh bản đồ',
            'location_sort_order'  => 'Thứ tự hiển thị',
            'location_is_active'   => 'Trạng thái hiển thị',
        ]);

        $mapEmbed = null;
        $mapUrl = null;
        if ($validated['location_map_type'] === 'embed') {
            $mapEmbed = $this->sanitizeMapEmbed($validated['location_map_embed'] ?? '');
            if ($mapEmbed === null) {
                return;
            }
        } elseif ($validated['location_map_type'] === 'google_maps') {
            $mapUrl = $this->sanitizeGoogleMapsUrl($validated['location_map_url'] ?? '');
            if ($mapUrl === null) {
                return;
            }
        }

        $mapImage = $this->location_existing_map_image;
        if ($validated['location_map_type'] === 'image' && $this->location_map_image) {
            $mapImage = $this->location_map_image->store('contact-maps', 'public');
        }
        $wasEditing = $this->editingLocationId !== null;
        $data = [
            'code' => filled($validated['location_code']) ? Str::upper(trim($validated['location_code'])) : null,
            'name' => $this->cleanTranslations($validated['location_name']),
            'company' => $this->cleanTranslations($validated['location_company']),
            'address' => $this->cleanTranslations($validated['location_address']),
            'phone' => filled($validated['location_phone']) ? trim($validated['location_phone']) : null,
            'fax' => filled($validated['location_fax']) ? trim($validated['location_fax']) : null,
            'email' => filled($validated['location_email']) ? trim($validated['location_email']) : null,
            'map_type' => $validated['location_map_type'],
            'map_embed' => $mapEmbed,
            'map_url' => $mapUrl,
            'map_image' => $validated['location_map_type'] === 'image' ? $mapImage : null,
            'sort_order' => $validated['location_sort_order'],
            'is_active' => $validated['location_is_active'],
        ];
        if ($wasEditing) {
            OfficeLocation::findOrFail($this->editingLocationId)->update($data);
        } else {
            $location = filled($data['code']) ? OfficeLocation::withTrashed()->where('code', $data['code'])->first() : null;
            if ($location) {
                $location->restore();
                $location->update($data);
            } else {
                OfficeLocation::create($data);
            }
        }
        $this->resetLocationForm();
        $this->toast($wasEditing ? 'Đã cập nhật địa chỉ liên hệ.' : 'Đã thêm địa chỉ liên hệ.');
    }

    public function toggleLocation(int $locationId): void
    {
        $location = OfficeLocation::findOrFail($locationId);
        $location->update(['is_active' => ! $location->is_active]);
        $this->toast($location->is_active ? 'Đã hiển thị địa chỉ.' : 'Đã ẩn địa chỉ.');
    }

    public function updateLocationSortOrder(int $locationId, mixed $sortOrder): void
    {
        Gate::authorize('contacts.manage');
        $errorKey = "location_sort_orders.{$locationId}";
        $this->resetErrorBag($errorKey);
        $validatedOrder = filter_var($sortOrder, FILTER_VALIDATE_INT);

        if ($validatedOrder === false || $validatedOrder < 0 || $validatedOrder > 999999) {
            $this->addError($errorKey, 'Thứ tự phải là số nguyên từ 0 đến 999999.');

            return;
        }

        OfficeLocation::findOrFail($locationId)->update(['sort_order' => $validatedOrder]);
        $this->toast('Đã cập nhật thứ tự hiển thị.');
    }

    public function deleteLocation(int $locationId): void
    {
        OfficeLocation::findOrFail($locationId)->delete();
        $this->toast('Đã chuyển địa chỉ liên hệ vào thùng rác.');
    }

    public function requestDelete(int $locationId): void
    {
        Gate::authorize('contacts.manage');
        $location = OfficeLocation::findOrFail($locationId);
        $this->pendingDeleteId = $location->id;
        $this->pendingDeleteName = $location->getTranslation('name', 'vi', false) ?: $location->code ?: '#'.$location->id;
    }

    public function cancelDelete(): void
    {
        $this->reset('pendingDeleteId', 'pendingDeleteName');
    }

    public function confirmDelete(): void
    {
        Gate::authorize('contacts.manage');
        if (! $this->pendingDeleteId) {
            $this->toast('Không tìm thấy địa chỉ liên hệ cần xóa. Vui lòng thử lại.', 'error');
            $this->cancelDelete();

            return;
        }
        $this->deleteLocation($this->pendingDeleteId);
        $this->cancelDelete();
    }

    private function resetLocationForm(): void
    {
        $this->reset([
            'editingLocationId', 'location_code', 'location_phone', 'location_fax', 'location_email',
            'location_map_type', 'location_map_embed', 'location_map_url', 'location_map_image',
            'location_existing_map_image',
            'location_sort_order', 'location_is_active', 'showLocationForm',
        ]);
        $this->location_name = $this->emptyTranslations();
        $this->location_company = $this->emptyTranslations();
        $this->location_address = $this->emptyTranslations();
    }

    private function emptyTranslations(): array
    {
        return ['vi' => '', 'en' => '', 'zh' => ''];
    }

    private function cleanTranslations(array $values): array
    {
        return collect($values)->map(fn ($value) => trim((string) $value))->filter()->all();
    }

    private function sanitizeMapEmbed(string $html): ?string
    {
        $html = trim($html);
        if ($html === '') {
            return null;
        }
        if (! preg_match('/<iframe\b[^>]*\bsrc=(["\'])(https:\/\/(?:www\.)?google\.[^"\']+|https:\/\/maps\.google\.[^"\']+)\1[^>]*><\/iframe>/i', $html, $match)) {
            $this->addError('location_map_embed', 'Mã bản đồ phải là iframe HTTPS từ Google Maps.');

            return null;
        }

        return '<iframe src="'.e($match[2]).'" loading="lazy" allowfullscreen></iframe>';
    }

    private function sanitizeGoogleMapsUrl(string $url): ?string
    {
        $url = trim($url);
        $host = strtolower((string) parse_url($url, PHP_URL_HOST));
        $scheme = strtolower((string) parse_url($url, PHP_URL_SCHEME));
        $isGoogleHost = preg_match('/(^|\.)google\.[a-z.]+$/i', $host)
            || in_array($host, ['maps.app.goo.gl', 'goo.gl'], true);

        if ($scheme !== 'https' || ! $isGoogleHost) {
            $this->addError('location_map_url', 'Liên kết phải là địa chỉ HTTPS hợp lệ từ Google Maps.');

            return null;
        }

        return $url;
    }

    private function sanitizeHtml(string $html): string
    {
        $html = preg_replace('#<(script|style|iframe|object|embed)[^>]*>.*?</\1>#is', '', $html) ?? '';
        $html = preg_replace('/\son\w+\s*=\s*(["\']).*?\1/is', '', $html) ?? '';
        $html = preg_replace('/(href|src)\s*=\s*(["\'])\s*javascript:.*?\2/is', '$1="#"', $html) ?? '';

        return trim(strip_tags($html, '<p><br><h1><h2><h3><h4><strong><b><em><i><u><s><sub><sup><span><ul><ol><li><a><blockquote><figure><figcaption><img><table><thead><tbody><tfoot><tr><th><td><pre><code><hr><oembed>'));
    }

    public function render()
    {
        return view('livewire.admin.contacts.settings', [
            'locales' => config('admin.locales'),
            'locations' => OfficeLocation::orderBy('sort_order')->latest('updated_at')->get(),
            'existingMapImageUrl' => $this->location_existing_map_image
                ? Storage::disk('public')->url($this->location_existing_map_image)
                : null,
            'breadcrumbs' => [
                ['label' => 'Bảng điều khiển', 'route' => 'admin.dashboard'],
                ['label' => 'Quản lý liên lạc', 'route' => 'admin.contacts.index'],
                ['label' => 'Cấu hình liên lạc'],
            ],
        ]);
    }
}
