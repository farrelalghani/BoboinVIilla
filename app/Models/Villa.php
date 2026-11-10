<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

class Villa extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'description',
        'weekday_price',
        'weekend_price',
        'capacity',
        'city',
        'address',
        'status',
        'image',
        'images',
        'facilities',
        'nearby_destinations',
        'house_rules',
    ];

    protected function casts(): array
    {
        return [
            'weekday_price'        => 'decimal:2',
            'weekend_price'        => 'decimal:2',
            'images'               => 'array',
            'facilities'          => 'array',
            'nearby_destinations' => 'array',
            'house_rules'         => 'array',
        ];
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    public function peakSeasons(): HasMany
    {
        return $this->hasMany(VillaPeakSeason::class);
    }

    /**
     * Harga per malam untuk 1 tanggal tertentu: peak season > weekend > weekday.
     * Weekend = Jumat & Sabtu.
     */
    public function priceInfoForDate(Carbon $date): array
    {
        $peak = $this->peakSeasons->first(
            fn (VillaPeakSeason $p) => $date->betweenIncluded($p->start_date, $p->end_date)
        );

        if ($peak) {
            return ['price' => (float) $peak->price, 'label' => $peak->label ?: 'Peak Season'];
        }

        $isWeekend = in_array($date->dayOfWeek, [Carbon::FRIDAY, Carbon::SATURDAY]);

        return $isWeekend
            ? ['price' => (float) $this->weekend_price, 'label' => 'Weekend']
            : ['price' => (float) $this->weekday_price, 'label' => 'Weekday'];
    }

    public function getStartingPriceAttribute(): float
    {
        return (float) min($this->weekday_price, $this->weekend_price);
    }

    /**
     * Mengembalikan seluruh foto villa (foto utama ditaruh di awal, diikuti foto galeri).
     *
     * @return array<int, string>
     */
    public function getAllImagesAttribute(): array
    {
        $list = [];
        if (!empty($this->image)) {
            $list[] = $this->image;
        }

        if (is_array($this->images)) {
            foreach ($this->images as $img) {
                if (!empty($img) && !in_array($img, $list, true)) {
                    $list[] = $img;
                }
            }
        }

        return $list;
    }
}
