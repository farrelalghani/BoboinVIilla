<?php

namespace Database\Seeders;

use App\Models\Villa;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class VillaSeeder extends Seeder
{
    public function run(): void
    {
        $villas = [
            [
                'name' => 'Villa Pantai Indah',
                'description' => 'Villa mewah dengan pemandangan pantai yang indah, cocok untuk keluarga maupun pasangan.',
                'weekday_price' => 1500000,
                'weekend_price' => 1800000,
                'capacity' => 8,
                'city' => 'Lembang Bandung',
                'address' => 'Jl. Raya Lembang No. 12, Bandung',
                'status' => 'active',
            ],
            [
                'name' => 'Villa Hijau Puncak',
                'description' => 'Villa asri di pegunungan dengan udara sejuk dan pemandangan kebun teh.',
                'weekday_price' => 900000,
                'weekend_price' => 1200000,
                'capacity' => 6,
                'city' => 'Bogor',
                'address' => 'Jl. Raya Puncak Km 87, Cisarua, Bogor',
                'status' => 'active',
            ],
            [
                'name' => 'Villa Sunrise Dago',
                'description' => 'Villa eksklusif di kawasan Dago dengan pemandangan perbukitan yang asri.',
                'weekday_price' => 2000000,
                'weekend_price' => 2500000,
                'capacity' => 10,
                'city' => 'Dago Bandung',
                'address' => 'Jl. Dago Atas No. 5, Bandung',
                'status' => 'active',
            ],
        ];

        foreach ($villas as $data) {
            $data['slug'] = Str::slug($data['name']);
            Villa::firstOrCreate(['slug' => $data['slug']], $data);
        }
    }
}
