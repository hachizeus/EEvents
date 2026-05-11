<?php

use Illuminate\Database\Migrations\Migration;

return new class extends Migration {
    public function up(): void
    {
        // This migration backfilled VAT settings from Stripe account data.
        // Stripe has been removed - this is now a no-op.
        // Existing VAT settings remain unaffected.
    }

    public function down(): void
    {
        //no-op
    }
};
