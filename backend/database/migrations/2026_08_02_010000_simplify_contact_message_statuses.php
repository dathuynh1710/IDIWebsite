<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('contact_messages')
            ->whereIn('status', ['resolved', 'spam'])
            ->update([
                'status' => 'in_progress',
                'updated_at' => now(),
            ]);
    }

    public function down(): void
    {
        // The previous resolved/spam distinction cannot be reconstructed safely.
    }
};
