<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private const REPORT_URL = 'https://idiseafood.com/vnt_upload/service/04_2026/BCTN_NAM_2025.pdf';

    public function up(): void
    {
        DB::table('media')
            ->where('disk', 'public')
            ->where('directory', 'documents')
            ->where('file_name', 'annual-report-2025.pdf')
            ->whereNull('external_url')
            ->update(['external_url' => self::REPORT_URL]);
    }

    public function down(): void
    {
        DB::table('media')
            ->where('disk', 'public')
            ->where('directory', 'documents')
            ->where('file_name', 'annual-report-2025.pdf')
            ->where('external_url', self::REPORT_URL)
            ->update(['external_url' => null]);
    }
};
