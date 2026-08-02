<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('office_locations', function (Blueprint $table) {
            $table->string('map_type', 30)->default('embed')->after('email');
            $table->string('map_url', 2048)->nullable()->after('map_embed');
            $table->string('map_image')->nullable()->after('map_url');
        });
    }

    public function down(): void
    {
        Schema::table('office_locations', function (Blueprint $table) {
            $table->dropColumn(['map_type', 'map_url', 'map_image']);
        });
    }
};
