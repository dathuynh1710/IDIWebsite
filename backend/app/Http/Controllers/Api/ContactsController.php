<?php

namespace App\Http\Controllers\Api;

use App\Enums\ContactInquiryType;
use App\Http\Controllers\Controller;
use App\Models\ContactMessage;
use App\Models\OfficeLocation;
use App\Support\Locale;
use App\Support\Toast;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class ContactsController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $module = DB::table('modules')->where('code', 'contact')->first();

        $locale = Locale::fromRequest($request);
        $localized = static function (?string $value) use ($locale): ?string {
            $translations = json_decode($value ?: '[]', true) ?: [];

            return $translations[$locale] ?? $translations['vi'] ?? null;
        };
        $settings = $module
            ? DB::table('module_settings')->where('module_id', $module->id)->pluck('setting_value', 'setting_key')
            : collect();
        $setting = static fn (string $key, mixed $default = null): mixed => $settings->has($key)
            ? json_decode($settings[$key], true)
            : $default;

        $locations = OfficeLocation::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get()
            ->map(function (OfficeLocation $location) use ($locale): array {
                $embedUrl = null;
                if ($location->map_type === 'embed' && $location->map_embed) {
                    preg_match('/\bsrc=["\']([^"\']+)["\']/i', $location->map_embed, $match);
                    $embedUrl = isset($match[1]) ? html_entity_decode($match[1], ENT_QUOTES) : null;
                }

                return [
                    'id' => $location->id,
                    'code' => $location->code,
                    'name' => $location->getTranslation('name', $locale, false)
                        ?: $location->getTranslation('name', 'vi', false),
                    'company' => $location->getTranslation('company', $locale, false)
                        ?: $location->getTranslation('company', 'vi', false),
                    'address' => $location->getTranslation('address', $locale, false)
                        ?: $location->getTranslation('address', 'vi', false),
                    'phone' => $location->phone,
                    'fax' => $location->fax,
                    'email' => $location->email,
                    'map' => [
                        'type' => $location->map_type ?: 'none',
                        'embedUrl' => $embedUrl,
                        'url' => $location->map_url,
                        'imageUrl' => $location->map_image
                            ? Storage::disk('public')->url($location->map_image)
                            : null,
                    ],
                ];
            })
            ->values();

        return response()->json([
            'pageConfig' => [
                'title' => $module ? $localized($module->page_title) : null,
                'description' => $module ? $localized($module->description) : null,
                'seo' => [
                    'title' => $module ? $localized($module->seo_title) : null,
                    'description' => $module ? $localized($module->meta_description) : null,
                ],
                'formEnabled' => (bool) $setting('form_enabled', true),
                'successMessage' => $localized(json_encode($setting('success_message', []), JSON_UNESCAPED_UNICODE)),
            ],
            'locations' => $locations,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $module = DB::table('modules')->where('code', 'contact')->first();

        $settings = $module
            ? DB::table('module_settings')
                ->where('module_id', $module->id)
                ->whereIn('setting_key', ['form_enabled', 'spam_protection'])
                ->pluck('setting_value', 'setting_key')
            : collect();

        $formEnabled = $settings->has('form_enabled')
            ? json_decode($settings['form_enabled'], true)
            : true;
        abort_if($formEnabled === false, 403, 'Contact form is currently unavailable.');

        $spamProtection = $settings->has('spam_protection')
            ? json_decode($settings['spam_protection'], true)
            : true;
        if ($spamProtection && filled($request->input('companyWebsite'))) {
            return Toast::json('Gửi liên hệ thành công.', 'success', [
                'success' => true,
                'message' => 'Contact received.',
                'referenceId' => 'IDI-CON-'.now()->format('Ym').'-000000',
            ], 201);
        }

        $data = $request->validate([
            'inquiryType' => ['required', 'string', 'max:150'],
            'fullName' => ['required', 'string', 'min:2', 'max:255'],
            'phone' => ['required', 'string', 'regex:/^\+?[\d\s\-().]{7,20}$/', 'max:50'],
            'email' => ['required', 'email', 'max:255'],
            'address' => ['required', 'string', 'max:2000'],
            'subject' => ['required', 'string', 'min:3', 'max:255'],
            'message' => ['required', 'string', 'min:10', 'max:1000'],
            'consent' => ['accepted'],
            'companyWebsite' => ['nullable', 'string', 'max:255'],
            'locale' => ['nullable', 'string', 'in:vi,en,zh,zh-CN'],
        ]);

        $inquiryType = ContactInquiryType::normalize($data['inquiryType']);
        if (! $inquiryType) {
            throw ValidationException::withMessages([
                'inquiryType' => 'Loại yêu cầu không hợp lệ.',
            ]);
        }

        $locale = Locale::normalize($data['locale'] ?? 'vi');
        $locale = DB::table('locales')->where('code', $locale)->exists() ? $locale : null;

        $message = ContactMessage::create([
            'inquiry_type' => $inquiryType->value,
            'full_name' => trim($data['fullName']),
            'phone' => trim($data['phone']),
            'email' => trim($data['email']),
            'address' => trim($data['address']),
            'subject' => trim($data['subject']),
            'message' => trim($data['message']),
            'locale' => $locale,
            'consented_at' => now(),
        ]);

        return Toast::json('Gửi liên hệ thành công.', 'success', [
            'success' => true,
            'message' => 'Contact received.',
            'referenceId' => 'IDI-CON-'.now()->format('Ym').'-'.str_pad((string) $message->id, 6, '0', STR_PAD_LEFT),
        ], 201);
    }
}
