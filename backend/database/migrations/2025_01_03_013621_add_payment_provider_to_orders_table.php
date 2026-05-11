<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('orders', static function (Blueprint $table) {
            $table->string('payment_provider')->nullable();
        });

        // Backfill existing paid orders - use raw string since STRIPE enum was removed
        DB::table('orders')
            ->where('total_gross', '>', 0)
            ->whereNull('payment_provider')
            ->update(['payment_provider' => 'STRIPE']);
    }

    public function down(): void
    {
        Schema::table('orders', static function (Blueprint $table) {
            $table->dropColumn('payment_provider');
        });
    }
};
