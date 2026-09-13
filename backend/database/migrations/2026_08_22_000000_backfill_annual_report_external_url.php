<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('media')
            ->where('disk', 'public')
            ->where('directory', 'documents')
            ->where('file_name', 'annual-report-2025.pdf')
            ->update([
                'disk' => 'public_assets',
                'external_url' => null,
            ]);
    }

    public function down(): void
    {
        DB::table('media')
            ->where('disk', 'public_assets')
            ->where('directory', 'documents')
            ->where('file_name', 'annual-report-2025.pdf')
            ->update(['disk' => 'public']);
    }
};
