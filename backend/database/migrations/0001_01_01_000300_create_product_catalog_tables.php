<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('parent_id')->nullable()->constrained('product_categories')->nullOnDelete();
            $table->foreignId('featured_media_id')->nullable()->constrained('media')->nullOnDelete();
            $table->string('code', 100)->nullable()->unique();
            $table->json('name');
            $table->json('slug');
            $table->json('description')->nullable();
            $table->json('seo_title')->nullable();
            $table->json('meta_description')->nullable();
            $table->json('translation_status')->nullable();
            $table->json('locale_published_at')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['parent_id', 'is_active', 'sort_order'], 'product_categories_tree_idx');
            $table->index('is_active');
        });

        Schema::create('attributes', function (Blueprint $table) {
            $table->id();
            $table->string('code', 100)->unique();
            $table->json('name');
            $table->enum('type', ['text', 'number', 'boolean', 'select', 'multiselect']);
            $table->json('unit')->nullable();
            $table->json('options')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->index(['is_active', 'sort_order']);
        });

        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_category_id')->nullable()->constrained('product_categories')->nullOnDelete();
            $table->foreignId('featured_media_id')->nullable()->constrained('media')->nullOnDelete();
            $table->string('sku', 100)->unique();
            $table->string('scientific_name')->nullable();
            $table->json('title');
            $table->json('slug');
            $table->json('short_description')->nullable();
            $table->json('description')->nullable();
            $table->json('content')->nullable();
            $table->json('seo_title')->nullable();
            $table->json('meta_description')->nullable();
            $table->json('og_title')->nullable();
            $table->json('og_description')->nullable();
            $table->json('schema_extra')->nullable();
            $table->json('translation_status')->nullable();
            $table->json('locale_published_at')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
            $table->index(
                ['product_category_id', 'is_active', 'sort_order'],
                'products_category_active_sort_idx'
            );
            $table->index(['is_featured', 'is_active']);
            $table->index('created_at');
            $table->index('updated_at');
        });

        Schema::create('product_attributes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->foreignId('attribute_id')->constrained('attributes')->cascadeOnDelete();
            $table->json('value')->nullable();
            $table->decimal('numeric_value', 18, 4)->nullable();
            $table->boolean('boolean_value')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
            $table->index(['product_id', 'sort_order'], 'product_attributes_product_sort_idx');
            $table->index('attribute_id');
            $table->index('numeric_value');
        });

        Schema::create('product_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->foreignId('media_id')->constrained('media')->restrictOnDelete();
            $table->json('title')->nullable();
            $table->string('document_type', 100)->nullable();
            $table->string('locale', 10)->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->foreign('locale')->references('code')->on('locales')->restrictOnDelete();
            $table->index(['product_id', 'sort_order'], 'product_documents_product_sort_idx');
            $table->index('document_type');
            $table->index('locale');
        });

        Schema::create('product_view_statistics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->string('locale', 10);
            $table->date('view_date');
            $table->unsignedBigInteger('view_count')->default(0);
            $table->timestamps();
            $table->foreign('locale')->references('code')->on('locales')->restrictOnDelete();
            $table->unique(
                ['product_id', 'locale', 'view_date'],
                'product_view_stats_identity_uq'
            );
            $table->index('view_date');
            $table->index(['locale', 'view_date'], 'product_view_stats_locale_date_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_view_statistics');
        Schema::dropIfExists('product_documents');
        Schema::dropIfExists('product_attributes');
        Schema::dropIfExists('products');
        Schema::dropIfExists('attributes');
        Schema::dropIfExists('product_categories');
    }
};
