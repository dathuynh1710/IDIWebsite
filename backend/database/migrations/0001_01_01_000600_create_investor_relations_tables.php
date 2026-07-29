<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('document_categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('parent_id')->nullable()->constrained('document_categories')->nullOnDelete();
            $table->json('name');
            $table->json('slug');
            $table->json('description')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['parent_id', 'is_active', 'sort_order'], 'document_categories_tree_idx');
        });

        Schema::create('investor_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('document_category_id')->nullable()->constrained('document_categories')->nullOnDelete();
            $table->json('title');
            $table->json('summary')->nullable();
            $table->string('document_number', 100)->nullable();
            $table->unsignedSmallInteger('year')->nullable();
            $table->unsignedTinyInteger('quarter')->nullable();
            $table->date('published_on')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
            $table->index(
                ['document_category_id', 'year', 'quarter'],
                'investor_documents_category_period_idx'
            );
            $table->index('published_on');
            $table->index(['is_featured', 'is_active']);
        });

        Schema::create('investor_document_files', function (Blueprint $table) {
            $table->id();
            $table->foreignId('investor_document_id')->constrained('investor_documents')->cascadeOnDelete();
            $table->foreignId('media_id')->constrained('media')->restrictOnDelete();
            $table->string('locale', 10)->nullable();
            $table->json('display_name')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
            $table->foreign('locale')->references('code')->on('locales')->restrictOnDelete();
            $table->index(
                ['investor_document_id', 'sort_order'],
                'investor_document_files_document_sort_idx'
            );
            $table->index('locale');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('investor_document_files');
        Schema::dropIfExists('investor_documents');
        Schema::dropIfExists('document_categories');
    }
};
