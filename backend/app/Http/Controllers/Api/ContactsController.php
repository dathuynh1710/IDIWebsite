<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ContactMessage;
use App\Support\Toast;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ContactsController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $module = DB::table('modules')->where('code', 'contact')->first();
        abort_if($module && ! $module->is_active, 403, 'Contact form is currently unavailable.');

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
            'locale' => ['nullable', 'string', 'in:vi,en,zh'],
        ]);

        $locale = $data['locale'] ?? 'vi';
        $locale = DB::table('locales')->where('code', $locale)->exists() ? $locale : null;

        $message = ContactMessage::create([
            'inquiry_type' => trim($data['inquiryType']),
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
