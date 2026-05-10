<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        // Simple text replacement: replace "STRIPE" with "PAYSTACK" in the JSON column
        // Works regardless of whether the column is json or jsonb
        DB::statement("
            UPDATE event_settings
            SET payment_providers = REPLACE(payment_providers::text, '\"STRIPE\"', '\"PAYSTACK\"')::json
            WHERE payment_providers::text LIKE '%STRIPE%'
        ");
    }

    public function down(): void
    {
        DB::statement("
            UPDATE event_settings
            SET payment_providers = REPLACE(payment_providers::text, '\"PAYSTACK\"', '\"STRIPE\"')::json
            WHERE payment_providers::text LIKE '%PAYSTACK%'
        ");
    }
};
