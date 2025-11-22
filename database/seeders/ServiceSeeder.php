<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ServiceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $services = [
            [
                'name' => 'Cuci Reguler',
                'price' => 7000,
                'unit' => 'kg',
                'description' => 'Layanan cuci reguler dengan proses 2-3 hari. Cocok untuk laundry sehari-hari.',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Cuci Express',
                'price' => 10000,
                'unit' => 'kg',
                'description' => 'Layanan cuci express selesai dalam 1 hari. Prioritas untuk kebutuhan mendesak.',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Setrika Saja',
                'price' => 5000,
                'unit' => 'kg',
                'description' => 'Hanya setrika tanpa cuci. Untuk pakaian yang sudah bersih tapi perlu rapi.',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Cuci + Setrika',
                'price' => 12000,
                'unit' => 'kg',
                'description' => 'Paket lengkap cuci dan setrika. Pakaian bersih dan rapi siap pakai.',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Cuci Express + Setrika',
                'price' => 15000,
                'unit' => 'kg',
                'description' => 'Paket express cuci dan setrika selesai dalam 1 hari.',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Dry Cleaning',
                'price' => 25000,
                'unit' => 'pcs',
                'description' => 'Dry cleaning untuk pakaian khusus seperti jas, gaun, atau bahan sensitif.',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Selimut / Bed Cover',
                'price' => 30000,
                'unit' => 'pcs',
                'description' => 'Cuci khusus untuk selimut, bed cover, atau sprei ukuran besar.',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Cuci Helm',
                'price' => 20000,
                'unit' => 'pcs',
                'description' => 'Cuci khusus helm dengan bahan pembersih yang aman.',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Cuci Tas',
                'price' => 35000,
                'unit' => 'pcs',
                'description' => 'Cuci tas dengan treatment khusus sesuai bahan (kulit, kanvas, dll).',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Cuci Khusus Bayi',
                'price' => 8000,
                'unit' => 'kg',
                'description' => 'Cuci dengan deterjen khusus untuk bayi, hypoallergenic dan lembut.',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Cuci Premium',
                'price' => 18000,
                'unit' => 'kg',
                'description' => 'Cuci dengan pewangi premium dan treatment khusus untuk pakaian putih.',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('services')->insert($services);
    }
}
