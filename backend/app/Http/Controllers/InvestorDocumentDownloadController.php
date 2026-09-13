<?php

namespace App\Http\Controllers;

use App\Models\InvestorDocumentFile;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;

class InvestorDocumentDownloadController extends Controller
{
    public function __invoke(InvestorDocumentFile $file): Response
    {
        $file->loadMissing('document.category', 'media');
        $publicDocument = $file->document?->is_active
            && (! $file->document->category || $file->document->category->is_active);
        abort_unless($file->document && $file->media && ($publicDocument || auth()->check()), 404);

        $media = $file->media;
        $path = trim($media->directory.'/'.$media->file_name, '/');
        if (Storage::disk($media->disk)->exists($path)) {
            return Storage::disk($media->disk)->download($path, $media->original_name, [
                'Content-Type' => $media->mime_type ?: 'application/octet-stream',
            ]);
        }

        if ($media->external_url) {
            return redirect()->away($media->external_url);
        }

        abort(404);
    }
}
