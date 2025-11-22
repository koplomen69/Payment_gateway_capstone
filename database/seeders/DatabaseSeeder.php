<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            ServiceSeeder::class,
        ]);

        // Create default user
        \App\Models\User::factory()->create([
            'name' => 'Admin Ananda Laundry',
            'email' => 'admin@anandalaundry.com',
            'password' => bcrypt('password123'),
        ]);

        // Create sample customer
        \App\Models\Customer::create([
            'name' => 'Pelanggan Umum',
            'phone' => '-',
            'address' => '-',
        ]);
    }
}
