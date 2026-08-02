<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\JobApplication;
use App\Models\JobPosition;
use App\Models\Media;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CareersController extends Controller
{
    public function index(Request $request)
    {
        $locale = $this->locale($request);
        $limit = min(100, max(1, $request->integer('limit', 20)));
        $positions = JobPosition::query()
            ->where('is_active', true)
            ->where("translation_status->{$locale}", 'published')
            ->where(fn ($q) => $q->whereNull('expires_at')->orWhereDate('expires_at', '>=', today()))
            ->when($request->filled('department'), fn ($q) => $q->where('department', $request->string('department')))
            ->orderByDesc('sort_order')->latest('created_at')->paginate($limit);

        return response()->json([
            'items' => collect($positions->items())->map(fn (JobPosition $position) => $this->position($position, $locale)),
            'total' => $positions->total(), 'page' => $positions->currentPage(), 'limit' => $positions->perPage(),
        ]);
    }

    public function show(Request $request, string $slug)
    {
        $locale = $this->locale($request);
        $position = JobPosition::where('is_active', true)->where("slug->{$locale}", $slug)->firstOrFail();
        return response()->json(['data' => $this->position($position, $locale, true)]);
    }

    public function store(Request $request)
    {
        $enabled = DB::table('module_settings')->join('modules', 'modules.id', '=', 'module_settings.module_id')
            ->where('modules.code', 'careers')->where('setting_key', 'application_enabled')->value('setting_value');
        abort_if($enabled !== null && json_decode($enabled, true) === false, 403, 'Applications are currently closed.');

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

        return response()->json([
            'message' => 'Application received.',
            'referenceId' => 'IDI-'.now()->format('Ym').'-'.str_pad((string) $application->id, 6, '0', STR_PAD_LEFT),
        ], 201);
    }

    private function locale(Request $request): string
    {
        $locale = $request->string('locale', $request->string('lang', 'vi')->toString())->toString();
        return in_array($locale, ['vi', 'en', 'zh'], true) ? $locale : 'vi';
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
                'benefits' => $value('benefits'),
                'seo' => ['title' => $value('seo_title'), 'description' => $value('meta_description')],
            ];
        }
        return $result;
    }
}
