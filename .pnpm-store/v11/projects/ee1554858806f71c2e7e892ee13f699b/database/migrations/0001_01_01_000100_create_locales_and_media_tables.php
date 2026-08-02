<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('locales', function (Blueprint $table) {
            $table->string('code', 10)->primary();
            $table->string('name', 100);
            $table->string('native_name', 100);
            $table->enum('direction', ['ltr', 'rtl'])->default('ltr');
            $table->boolean('is_default')->default(false);
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
            $table->index(['is_active', 'sort_order']);
        });

        Schema::create('media_folders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('parent_id')->nullable()->constrained('media_folders')->nullOnDelete();
            $table->string('name');
            $table->string('path', 500)->unique('media_folders_path_uq');
            $table->unsignedInteger('sort_order')->default(0);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('media', function (Blueprint $table) {
            $table->id();
            $table->foreignId('folder_id')->nullable()->constrained('media_folders')->nullOnDelete();
            $table->string('disk', 50)->default('public');
            $table->string('directory', 500)->nullable();
            $table->string('file_name');
            $table->string('original_name');
            $table->string('mime_type', 150);
            $table->string('extension', 20)->nullable();
            $table->unsignedBigInteger('file_size')->nullable();
            $table->unsignedInteger('width')->nullable();
            $table->unsignedInteger('height')->nullable();
            $table->char('checksum', 64)->nullable();
            $table->json('title')->nullable();
            $table->json('alt_text')->nullable();
            $table->json('caption')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
            $table->index('mime_type');
            $table->index('checksum');
            // directory(500) makes the requested three-column unique key exceed
            // MySQL's utf8mb4 index-byte budget; this narrower lookup index is safe.
            $table->index(['disk', 'file_name'], 'media_disk_filename_idx');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('avatar_media_id')
                ->nullable()
                ->after('password')
                ->constrained('media')
                ->nullOnDelete();
        });

        Schema::create('media_variants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('media_id')->constrained('media')->cascadeOnDelete();
            $table->string('preset', 100);
            $table->string('disk', 50)->default('public');
            $table->string('path', 500);
            $table->unsignedInteger('width')->nullable();
            $table->unsignedInteger('height')->nullable();
            $table->string('mime_type', 150)->nullable();
            $table->unsignedBigInteger('file_size')->nullable();
            $table->timestamps();
            $table->unique(['media_id', 'preset'], 'media_variants_media_preset_uq');
        });

        Schema::create('mediables', function (Blueprint $table) {
            $table->id();
            $table->foreignId('media_id')->constrained('media')->cascadeOnDelete();
            $table->string('mediable_type');
            $table->unsignedBigInteger('mediable_id');
            $table->string('locale', 10)->nullable();
            $table->string('role', 50)->default('gallery');
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
            $table->index(['mediable_type', 'mediable_id'], 'mediables_type_id_idx');
            $table->index(['locale', 'role'], 'mediables_locale_role_idx');
            // MySQL permits multiple NULL locale values in a unique key; callers
            // must enforce global-media de-duplication when locale is NULL.
            $table->unique(
                ['media_id', 'mediable_type', 'mediable_id', 'locale', 'role'],
                'mediables_identity_uq'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mediables');
        Schema::dropIfExists('media_variants');
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('avatar_media_id');
        });
        Schema::dropIfExists('media');
        Schema::dropIfExists('media_folders');
        Schema::dropIfExists('locales');
    }
};
