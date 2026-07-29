<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sliders', function (Blueprint $table) {
            $table->id();
            $table->string('code', 100)->unique();
            $table->string('name');
            $table->json('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
            $table->index('is_active');
        });

        Schema::create('slider_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('slider_id')->constrained('sliders')->cascadeOnDelete();
            $table->json('title')->nullable();
            $table->json('subtitle')->nullable();
            $table->json('button_label')->nullable();
            $table->json('link')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
            $table->index(['slider_id', 'is_active', 'sort_order'], 'slider_items_slider_active_sort_idx');
            $table->index(['starts_at', 'ends_at']);
        });

        Schema::create('slider_item_media', function (Blueprint $table) {
            $table->id();
            $table->foreignId('slider_item_id')->constrained('slider_items')->cascadeOnDelete();
            $table->string('locale', 10);
            $table->enum('device', ['desktop', 'mobile']);
            $table->foreignId('media_id')->constrained('media')->restrictOnDelete();
            $table->timestamps();
            $table->foreign('locale')->references('code')->on('locales')->restrictOnDelete();
            $table->unique(
                ['slider_item_id', 'locale', 'device'],
                'slider_item_media_identity_uq'
            );
            $table->index(['locale', 'device']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('slider_item_media');
        Schema::dropIfExists('slider_items');
        Schema::dropIfExists('sliders');
    }
};
