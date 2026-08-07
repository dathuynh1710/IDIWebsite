<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('job_positions', function (Blueprint $table): void {
            $table->json('contact')->nullable()->after('benefits');
            $table->json('meta_keywords')->nullable()->after('meta_description');
        });
    }

    public function down(): void
    {
        Schema::table('job_positions', function (Blueprint $table): void {
            $table->dropColumn(['contact', 'meta_keywords']);
        });
    }
};
