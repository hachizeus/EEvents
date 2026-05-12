<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Disable foreign key checks
        DB::statement('SET session_replication_role = replica;');

        $tables = [
            'order_items',
            'order_refunds',
            'order_audit_logs',
            'order_application_fees',
            'order_payment_platform_fees',
            'paystack_payments',
            'stripe_payments',
            'stripe_payouts',
            'stripe_customers',
            'invoices',
            'orders',
            'attendee_check_ins',
            'attendees',
            'event_statistics',
            'event_daily_statistics',
            'promo_codes',
            'affiliates',
            'capacity_assignments',
            'product_capacity_assignments',
            'check_in_lists',
            'product_check_in_lists',
            'waitlist_entries',
            'messages',
            'outgoing_messages',
            'webhook_logs',
            'ticket_lookup_tokens',
            'failed_jobs',
            'job_batches',
        ];

        foreach ($tables as $table) {
            try {
                DB::statement("TRUNCATE TABLE \"{$table}\" CASCADE");
            } catch (\Exception $e) {
                // Table might not exist, skip
            }
        }

        // Re-enable foreign key checks
        DB::statement('SET session_replication_role = DEFAULT;');
    }

    public function down(): void
    {
        // Cannot restore deleted data
    }
};
