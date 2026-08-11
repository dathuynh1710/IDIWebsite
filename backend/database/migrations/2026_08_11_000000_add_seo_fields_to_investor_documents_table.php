<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('investor_documents', function (Blueprint $table): void {
            $table->string('slug')->nullable()->unique()->after('summary');
            $table->string('seo_title')->nullable()->after('slug');
            $table->text('meta_description')->nullable()->after('seo_title');
            $table->text('meta_keywords')->nullable()->after('meta_description');
        });
    }

    public function down(): void
    {
        Schema::table('investor_documents', function (Blueprint $table): void {
            $table->dropUnique(['slug']);
            $table->dropColumn(['slug', 'seo_title', 'meta_description', 'meta_keywords']);
        });
    }
};
