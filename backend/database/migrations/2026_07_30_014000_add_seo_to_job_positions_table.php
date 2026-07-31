<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('job_positions', function (Blueprint $table): void {
            $table->json('seo_title')->nullable()->after('benefits');
            $table->json('meta_description')->nullable()->after('seo_title');
        });
    }

    public function down(): void
    {
        Schema::table('job_positions', function (Blueprint $table): void {
            $table->dropColumn(['seo_title', 'meta_description']);
        });
    }
};
