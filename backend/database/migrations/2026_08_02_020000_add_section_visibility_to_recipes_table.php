<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('recipes', 'difficulty')) {
            return;
        }

        Schema::table('recipes', function (Blueprint $table) {
            $table->boolean('show_ingredients')->default(true)->after('difficulty');
            $table->boolean('show_steps')->default(true)->after('show_ingredients');
        });
    }

    public function down(): void
    {
        if (! Schema::hasColumn('recipes', 'show_ingredients')) {
            return;
        }

        Schema::table('recipes', function (Blueprint $table) {
            $table->dropColumn(['show_ingredients', 'show_steps']);
        });
    }
};
