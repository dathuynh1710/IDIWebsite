<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('recipes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('featured_media_id')->nullable()->constrained('media')->nullOnDelete();
            $table->string('code', 100)->nullable()->unique();
            $table->json('title');
            $table->json('slug');
            $table->json('summary')->nullable();
            $table->json('content')->nullable();
            $table->string('servings', 100)->nullable();
            $table->unsignedInteger('preparation_time')->nullable();
            $table->unsignedInteger('cooking_time')->nullable();
            $table->string('difficulty', 50)->nullable();
            $table->json('seo_title')->nullable();
            $table->json('meta_description')->nullable();
            $table->json('translation_status')->nullable();
            $table->json('locale_published_at')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['is_featured', 'is_active']);
            $table->index(['is_active', 'sort_order']);
        });

        Schema::create('recipe_ingredients', function (Blueprint $table) {
            $table->id();
            $table->foreignId('recipe_id')->constrained('recipes')->cascadeOnDelete();
            $table->json('name');
            $table->string('quantity', 100)->nullable();
            $table->json('unit')->nullable();
            $table->json('note')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
            $table->index(['recipe_id', 'sort_order']);
        });

        Schema::create('recipe_steps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('recipe_id')->constrained('recipes')->cascadeOnDelete();
            $table->foreignId('media_id')->nullable()->constrained('media')->nullOnDelete();
            $table->json('instruction');
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
            $table->index(['recipe_id', 'sort_order']);
        });

        Schema::create('product_recipe', function (Blueprint $table) {
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->foreignId('recipe_id')->constrained('recipes')->cascadeOnDelete();
            $table->unique(['product_id', 'recipe_id'], 'product_recipe_identity_uq');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_recipe');
        Schema::dropIfExists('recipe_steps');
        Schema::dropIfExists('recipe_ingredients');
        Schema::dropIfExists('recipes');
    }
};
