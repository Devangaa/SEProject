<?php

namespace Database\Factories;

use App\Models\Keuangan;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Keuangan>
 */
class KeuanganFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'keterangan' => $this->faker->sentence(),
            'nominal' => $this->faker->numberBetween(10000, 5000000),
            'tipe_laporan' => $this->faker->randomElement(['pendapatan', 'pengeluaran']),
            'tanggal' => Carbon::now()->subDays($this->faker->numberBetween(0, 30)),
            'is_delete' => false,
        ];
    }
}
