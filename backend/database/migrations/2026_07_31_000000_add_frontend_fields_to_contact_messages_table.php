<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('contact_messages', function (Blueprint $table) {
            $table->string('inquiry_type', 150)->nullable()->after('id');
            $table->text('address')->nullable()->after('phone');
            $table->timestamp('consented_at')->nullable()->after('locale');
        });
    }

    public function down(): void
    {
        Schema::table('contact_messages', function (Blueprint $table) {
            $table->dropColumn(['inquiry_type', 'address', 'consented_at']);
        });
    }
};
