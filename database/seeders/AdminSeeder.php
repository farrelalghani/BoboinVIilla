<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'guest', 'guard_name' => 'web']);

        $admin = User::firstOrCreate(
            ['email' => 'admin@boboin.com'],
            [
                'name' => 'Admin Boboin',
                'password' => bcrypt('password'),
                'phone_number' => '081234567890',
            ]
        );

        $admin->assignRole('admin');
    }
}
