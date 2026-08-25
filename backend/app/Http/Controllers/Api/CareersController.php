<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\JobApplication;
use App\Models\JobPosition;
use App\Models\Media;
use App\Support\Locale;
use App\Support\Toast;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class CareersController extends Controller
{
    public function index(Request $request)
    {
        $locale = $this->locale($request);
        $settings = $this->moduleSettings();
        $limit = min(100, max(1, $request->integer('limit', 10)));
        $positions = JobPosition::query()
            ->where('is_active', true)
            ->where("translation_status->{$locale}", 'published')
            ->where(fn ($q) => $q->whereNull('expires_at')->orWhereDate('expires_at', '>=', today()))
            ->when($request->filled('department'), fn ($q) => $q->where('department', $request->string('department')))
            ->orderByDesc('sort_order')->latest('created_at')->paginate($limit);

        return response()->json([
            'items' => collect($positions->items())->map(fn (JobPosition $position) => $this->position($position, $locale)),
            'total' => $positions->total(), 'page' => $positions->currentPage(), 'limit' => $positions->perPage(),
            'pageConfig' => $this->pageConfig($locale, $settings),
        ]);
    }

    public function show(Request $request, string $slug)
    {
        $locale = $this->locale($request);
        $position = JobPosition::where('is_active', true)
            ->where("translation_status->{$locale}", 'published')
            ->where("slug->{$locale}", $slug)
            ->firstOrFail();

        return response()->json(['data' => $this->position($position, $locale, true)]);
    }

    public function store(Request $request)
    {
        $locale = $this->locale($request);
        $messages = match ($locale) {
            'en' => [
                'closed' => 'Applications are currently closed.',
                'success' => 'Your CV was uploaded and your application was submitted successfully.',
            ],
            'zh' => [
                'closed' => '在线申请通道目前已关闭。',
                'success' => '您的简历已上传，申请已成功提交。',
            ],
            default => [
                'closed' => 'Cổng ứng tuyển hiện đang tạm đóng.',
                'success' => 'Tải CV và gửi hồ sơ thành công.',
            ],
        };

        $enabled = DB::table('module_settings')->join('modules', 'modules.id', '=', 'module_settings.module_id')
            ->where('modules.code', 'careers')->where('setting_key', 'application_enabled')->value('setting_value');
        if ($enabled !== null && json_decode($enabled, true) === false) {
            return Toast::json($messages['closed'], 'warning', [], 403);
        }

        $data = $request->validate([
            'jobPositionId' => ['nullable', 'integer', 'exists:job_positions,id'],
            'fullName' => ['required', 'string', 'min:2', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['required', 'string', 'max:50'],
            'address' => ['required', 'string', 'max:2000'],
            'coverLetter' => ['nullable', 'string', 'max:20000'],
            'cv' => ['required', 'file', 'mimes:pdf,doc,docx', 'max:10240'],
        ]);
        $file = $data['cv'];
        $path = $file->store('recruitment/cv', 'public');
        $media = Media::create([
            'disk' => 'public', 'directory' => dirname($path), 'file_name' => basename($path),
            'original_name' => $file->getClientOriginalName(), 'mime_type' => $file->getMimeType(),
            'extension' => $file->getClientOriginalExtension(), 'file_size' => $file->getSize(),
            'title' => ['vi' => 'CV '.$data['fullName']],
        ]);
        $application = JobApplication::create([
            'job_position_id' => $data['jobPositionId'] ?? null,
            'full_name' => trim($data['fullName']), 'email' => trim($data['email']),
            'phone' => trim($data['phone']), 'address' => trim($data['address']),
            'cover_letter' => trim($data['coverLetter'] ?? ''), 'cv_media_id' => $media->id,
        ]);

        return Toast::json($messages['success'], 'success', [
            'message' => 'Application received.',
            'referenceId' => 'IDI-'.now()->format('Ym').'-'.str_pad((string) $application->id, 6, '0', STR_PAD_LEFT),
        ], 201);
    }

    private function locale(Request $request): string
    {
        return Locale::fromRequest($request);
    }

    private function position(JobPosition $position, string $locale, bool $full = false): array
    {
        $value = fn (string $field) => $position->getTranslation($field, $locale, false)
            ?: $position->getTranslation($field, 'vi', false);
        $result = [
            'id' => $position->id, 'code' => $position->code, 'department' => $position->department,
            'title' => $value('title'), 'slug' => $value('slug'), 'location' => $value('location'),
            'summary' => $value('summary'), 'quantity' => $position->quantity,
            'expiresAt' => $position->expires_at?->toDateString(),
        ];
        if ($full) {
            $result += [
                'description' => $value('description'), 'requirements' => $value('requirements'),
                'benefits' => $value('benefits'), 'contact' => $value('contact'),
                'seo' => ['title' => $value('seo_title'), 'description' => $value('meta_description'), 'keywords' => $value('meta_keywords')],
            ];
        }

        return $result;
    }

    private function moduleSettings(): array
    {
        $moduleId = DB::table('modules')->where('code', 'careers')->value('id');
        if (! $moduleId) {
            return [];
        }

        return DB::table('module_settings')->where('module_id', $moduleId)
            ->pluck('setting_value', 'setting_key')
            ->map(fn ($value) => json_decode($value, true))
            ->all();
    }

    private function pageConfig(string $locale, array $settings): array
    {
        $module = DB::table('modules')->where('code', 'careers')->first();
        $localized = function (?string $json) use ($locale): string {
            $values = json_decode($json ?: '{}', true) ?: [];

            return (string) ($values[$locale] ?? $values['vi'] ?? '');
        };
        $settingLocalized = fn (string $key): string => (string) (($settings[$key][$locale] ?? null) ?: ($settings[$key]['vi'] ?? ''));
        $assetUrl = fn (?string $path): ?string => $path ? Storage::disk('public')->url($path) : null;

        return [
            'title' => $localized($module?->page_title),
            'description' => $localized($module?->description),
            'benefitsContent' => $settingLocalized('benefits_content'),
            'contactContent' => $settingLocalized('contact_content'),
            'seoTitle' => $localized($module?->seo_title),
            'metaDescription' => $localized($module?->meta_description),
            'metaKeywords' => $settingLocalized('meta_keywords'),
            'heroDesktop' => $assetUrl($settings['hero_desktop'] ?? null),
            'heroMobile' => $assetUrl($settings['hero_mobile'] ?? null),
            'gallery' => collect($settings['gallery_images'] ?? [])->filter()->map($assetUrl)->values()->all(),
            'applicationEnabled' => (bool) ($settings['application_enabled'] ?? true),
        ];
    }
}
