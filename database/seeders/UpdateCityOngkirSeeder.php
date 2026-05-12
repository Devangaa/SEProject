<?php

namespace Database\Seeders;

use App\Models\City;
use App\Models\Province;
use Illuminate\Database\Seeder;

class UpdateCityOngkirSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $allowedProvinces = [
            'Banten',
            'Daerah Khusus Ibukota Jakarta',
            'Jawa Barat',
            'Jawa Tengah',
            'Daerah Istimewa Yogyakarta',
            'Jawa Timur',
        ];

        // 1. Ambil ID provinsi yang diizinkan
        $allowedProvinceIds = Province::whereIn('name', $allowedProvinces)->pluck('id');

        // 2. Set ongkir NULL untuk kota di dalam provinsi tersebut
        City::whereIn('province_id', $allowedProvinceIds)->update(['ongkir' => null]);

        // 3. Set ongkir 3.000.000 untuk kota di LUAR provinsi tersebut
        City::whereNotIn('province_id', $allowedProvinceIds)->update(['ongkir' => 3000000]);

        $this->command->info('✓ Seeder ongkir berhasil diperbarui: '.count($allowedProvinceIds).' provinsi diatur NULL, sisanya 3jt.');
    }
}
