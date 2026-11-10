<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;

class Booking extends Model
{
    protected $fillable = [
        'booking_code',
        'guest_id',
        'guest_name',
        'guest_email',
        'guest_phone',
        'guest_city',
        'adult_count',
        'child_count',
        'villa_id',
        'check_in',
        'check_out',
        'total_price',
        'status',
        'rejection_note',
    ];

    protected static function booted(): void
    {
        static::creating(function (Booking $booking) {
            if (empty($booking->booking_code)) {
                do {
                    $code = 'BVL-' . strtoupper(Str::random(6));
                } while (static::where('booking_code', $code)->exists());

                $booking->booking_code = $code;
            }
        });
    }

    protected function casts(): array
    {
        return [
            'check_in' => 'date',
            'check_out' => 'date',
            'total_price' => 'decimal:2',
        ];
    }

    public function guest(): BelongsTo
    {
        return $this->belongsTo(User::class, 'guest_id');
    }

    public function villa(): BelongsTo
    {
        return $this->belongsTo(Villa::class);
    }

    public function payment(): HasOne
    {
        return $this->hasOne(Payment::class);
    }
}
