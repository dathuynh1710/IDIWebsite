<?php

namespace App\Http\Controllers;

use App\Models\InvestorDocumentFile;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class InvestorDocumentDownloadController extends Controller
{
    public function __invoke(InvestorDocumentFile $file): StreamedResponse
    {
        $file->loadMissing('document', 'media');
        abort_unless($file->document && $file->media && ($file->document->is_active || auth()->check()), 404);

        $media = $file->media;
        $path = trim($media->directory.'/'.$media->file_name, '/');
        abort_unless(Storage::disk($media->disk)->exists($path), 404);

        return Storage::disk($media->disk)->download($path, $media->original_name, [
            'Content-Type' => $media->mime_type ?: 'application/octet-stream',
        ]);
    }
}
