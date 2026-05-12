<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Update all accounts that still have USD as currency to KES
        DB::table('accounts')
            ->where('currency_code', 'USD')
            ->update(['currency_code' => 'KES']);

        // Update all events that still have USD as currency to KES
        DB::table('events')
            ->where('currency', 'USD')
            ->update(['currency' => 'KES']);

        // Update all organizers that still have USD as currency to KES
        DB::table('organizers')
            ->where('currency', 'USD')
            ->update(['currency' => 'KES']);
    }

    public function down(): void
    {
        // Intentionally left empty
    }
};
