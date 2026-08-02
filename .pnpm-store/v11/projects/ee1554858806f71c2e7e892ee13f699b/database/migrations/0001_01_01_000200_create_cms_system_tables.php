<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('modules', function (Blueprint $table) {
            $table->id();
            $table->string('code', 100)->unique();
            $table->string('name', 150);
            $table->string('module_type', 50);
            $table->json('page_title')->nullable();
            $table->json('description')->nullable();
            $table->json('seo_title')->nullable();
            $table->json('meta_description')->nullable();
            $table->json('og_title')->nullable();
            $table->json('og_description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->index(['module_type', 'is_active']);
        });

        Schema::create('module_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('module_id')->constrained('modules')->cascadeOnDelete();
            $table->string('setting_key', 150);
            $table->json('setting_value')->nullable();
            $table->string('setting_type', 50)->default('json');
            $table->timestamps();
            $table->unique(['module_id', 'setting_key'], 'module_settings_module_key_uq');
        });

        Schema::create('localized_routes', function (Blueprint $table) {
            $table->id();
            $table->string('routeable_type');
            $table->unsignedBigInteger('routeable_id');
            $table->string('locale', 10);
            $table->string('route_name', 100)->nullable();
            $table->string('slug');
            $table->string('full_path', 500);
            $table->enum('status', [
                'draft', 'translating', 'review', 'scheduled',
                'published', 'hidden', 'archived',
            ])->default('draft');
            $table->timestamp('published_at')->nullable();
            $table->boolean('robots_index')->default(true);
            $table->boolean('robots_follow')->default(true);
            $table->boolean('include_in_sitemap')->default(true);
            $table->string('canonical_override', 500)->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->foreign('locale')->references('code')->on('locales')->restrictOnDelete();
            $table->unique(['locale', 'full_path'], 'localized_routes_locale_path_uq');
            $table->index(['routeable_type', 'routeable_id'], 'localized_routes_type_id_idx');
            $table->index(['locale', 'slug'], 'localized_routes_locale_slug_idx');
            $table->index(['locale', 'status', 'published_at'], 'localized_routes_publish_idx');
            $table->index(['include_in_sitemap', 'status'], 'localized_routes_sitemap_idx');
        });

        Schema::create('redirects', function (Blueprint $table) {
            $table->id();
            $table->string('from_path', 500);
            $table->string('to_path', 500);
            $table->unsignedSmallInteger('status_code')->default(301);
            $table->string('redirect_type', 50)->default('slug_changed');
            $table->unsignedBigInteger('hit_count')->default(0);
            $table->timestamp('last_hit_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->unique('from_path', 'redirects_from_path_uq');
            $table->index('is_active');
            $table->index('status_code');
            $table->index('last_hit_at');
        });

        Schema::create('content_revisions', function (Blueprint $table) {
            $table->id();
            $table->string('revisionable_type');
            $table->unsignedBigInteger('revisionable_id');
            $table->string('locale', 10)->nullable();
            $table->enum('event', ['created', 'updated', 'published', 'restored']);
            $table->json('snapshot');
            $table->json('changed_fields')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('created_at')->useCurrent();
            $table->index(
                ['revisionable_type', 'revisionable_id'],
                'content_revisions_type_id_idx'
            );
            $table->index('locale');
            $table->index('event');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('content_revisions');
        Schema::dropIfExists('redirects');
        Schema::dropIfExists('localized_routes');
        Schema::dropIfExists('module_settings');
        Schema::dropIfExists('modules');
    }
};
