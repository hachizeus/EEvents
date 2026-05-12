<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $tables = [
            // Delete in dependency order (children first)
            'attendee_check_ins',
            'attendees',
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
            'event_statistics',
            'event_daily_statistics',
            'promo_codes',
            'affiliates',
            'product_capacity_assignments',
            'capacity_assignments',
            'product_check_in_lists',
            'check_in_lists',
            'waitlist_entries',
            'outgoing_messages',
            'messages',
            'webhook_logs',
            'ticket_lookup_tokens',
            'failed_jobs',
            'job_batches',
        ];

        foreach ($tables as $table) {
            try {
                DB::table($table)->delete();
            } catch (\Exception $e) {
                // Table might not exist or have constraints, skip
            }
        }
    }

    public function down(): void
    {
        // Cannot restore deleted data
    }
};
