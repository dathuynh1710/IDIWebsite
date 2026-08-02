<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('site_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key_name', 150)->unique();
            $table->json('value')->nullable();
            $table->enum('type', ['text', 'number', 'boolean', 'json', 'media'])->default('json');
            $table->boolean('is_translatable')->default(false);
            $table->string('group_name', 100)->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
            $table->index(['group_name', 'sort_order']);
        });

        Schema::create('social_links', function (Blueprint $table) {
            $table->id();
            $table->string('platform', 100);
            $table->json('label')->nullable();
            $table->string('url', 500);
            $table->string('icon')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->index('platform');
            $table->index(['is_active', 'sort_order']);
        });

        Schema::create('sitemap_logs', function (Blueprint $table) {
            $table->id();
            $table->string('sitemap_type', 100);
            $table->string('locale', 10)->nullable();
            $table->string('file_path', 500);
            $table->unsignedInteger('url_count')->default(0);
            $table->enum('status', ['pending', 'processing', 'completed', 'failed'])->default('pending');
            $table->longText('error_message')->nullable();
            $table->timestamp('generated_at')->nullable();
            $table->timestamps();
            $table->foreign('locale')->references('code')->on('locales')->restrictOnDelete();
            $table->index(['sitemap_type', 'locale']);
            $table->index('status');
            $table->index('generated_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sitemap_logs');
        Schema::dropIfExists('social_links');
        Schema::dropIfExists('site_settings');
    }
};
