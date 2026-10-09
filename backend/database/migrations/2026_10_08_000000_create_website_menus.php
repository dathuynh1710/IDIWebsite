<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('menus', function (Blueprint $table): void {
            $table->id();
            $table->string('location', 100)->unique();
            $table->string('name');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
        Schema::create('menu_items', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('menu_id')->constrained()->restrictOnDelete();
            $table->foreignId('parent_id')->nullable()->constrained('menu_items')->restrictOnDelete();
            $table->json('label');
            $table->string('link_type', 30)->default('internal');
            $table->string('url', 2048)->nullable();
            $table->foreignId('page_id')->nullable()->constrained()->restrictOnDelete();
            $table->foreignId('product_category_id')->nullable()->constrained()->restrictOnDelete();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->index(['menu_id', 'parent_id', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('menu_items');
        Schema::dropIfExists('menus');
    }
};
