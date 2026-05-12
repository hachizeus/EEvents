<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Disable foreign key checks so we can truncate in any order
        DB::statement('SET session_replication_role = replica;');

        $tables = [
            // Orders and payments
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

            // Attendees
            'attendee_check_ins',
            'attendees',

            // Event data (keep events/organizers but clear stats)
            'event_statistics',
            'event_daily_statistics',

            // Promo codes usage
            'promo_codes',

            // Affiliates
            'affiliates',

            // Capacity assignments
            'capacity_assignments',
            'product_capacity_assignments',

            // Check-in lists
            'check_in_lists',
            'product_check_in_lists',

            // Waitlist
            'waitlist_entries',

            // Messages
            'messages',
            'outgoing_messages',

            // Webhooks logs
            'webhook_logs',

            // Ticket lookup tokens
            'ticket_lookup_tokens',

            // Failed jobs
            'failed_jobs',
            'job_batches',
        ];

        foreach ($tables as $table) {
            try {
                DB::table($table)->truncate();
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
