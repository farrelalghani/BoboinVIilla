<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    protected $fillable = [
        'booking_id',
        'payment_type',
        'bank',
        'account_number',
        'receipt_image',
        'gross_amount',
        'payment_status',
        'xendit_invoice_id',
        'xendit_invoice_url',
    ];

    protected function casts(): array
    {
        return [
            'gross_amount' => 'decimal:2',
        ];
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }
}
