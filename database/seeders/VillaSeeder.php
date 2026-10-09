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
                'price_per_night' => 1500000,
                'capacity' => 8,
                'city' => 'Bali',
                'address' => 'Jl. Pantai Kuta No. 12, Kuta, Bali',
                'status' => 'active',
            ],
            [
                'name' => 'Villa Hijau Puncak',
                'description' => 'Villa asri di pegunungan dengan udara sejuk dan pemandangan kebun teh.',
                'price_per_night' => 900000,
                'capacity' => 6,
                'city' => 'Bogor',
                'address' => 'Jl. Raya Puncak Km 87, Cisarua, Bogor',
                'status' => 'active',
            ],
            [
                'name' => 'Villa Sunrise Lombok',
                'description' => 'Villa eksklusif di Lombok dengan akses langsung ke pantai berpasir putih.',
                'price_per_night' => 2000000,
                'capacity' => 10,
                'city' => 'Lombok',
                'address' => 'Jl. Senggigi No. 5, Lombok Barat, NTB',
                'status' => 'active',
            ],
        ];

        foreach ($villas as $data) {
            $data['slug'] = Str::slug($data['name']);
            Villa::firstOrCreate(['slug' => $data['slug']], $data);
        }
    }
}
