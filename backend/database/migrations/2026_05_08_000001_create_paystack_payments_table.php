<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('paystack_payments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('order_id');
            $table->string('reference')->unique();
            $table->string('transaction_id')->nullable();
            $table->unsignedBigInteger('amount')->nullable()->comment('Amount in smallest currency unit (kobo, pesewas, etc.)');
            $table->string('currency', 10)->nullable();
            $table->string('status', 50)->nullable()->default('pending');
            $table->string('gateway_response')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();

            $table->foreign('order_id')->references('id')->on('orders')->onDelete('cascade');
            $table->index('order_id');
            $table->index('reference');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('paystack_payments');
    }
};
