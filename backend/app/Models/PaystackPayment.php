<?php

namespace HiEvents\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaystackPayment extends BaseModel
{
    protected $table = 'paystack_payments';

    protected function getTimestampsEnabled(): bool
    {
        return true;
    }

    protected function getCastMap(): array
    {
        return [];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
