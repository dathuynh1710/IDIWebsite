<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('investor_documents', function (Blueprint $table): void {
            $table->string('source_key', 500)->nullable()->unique()->after('slug');
        });
    }

    public function down(): void
    {
        Schema::table('investor_documents', function (Blueprint $table): void {
            $table->dropUnique(['source_key']);
            $table->dropColumn('source_key');
        });
    }
};
