<?php

namespace App\Http\Controllers;

use App\Models\City;
use App\Models\Kecamatan;
use App\Models\Province;
use Illuminate\Routing\Controller;

class WilayahController extends Controller
{
    /**
     * Get cities by province_id
     */
    public function getCitiesByProvince($provinceId)
    {
        $cities = City::where('province_id', $provinceId)
            ->orderBy('name')
            ->select('id', 'name')
            ->get();

        return response()->json($cities);
    }

    /**
     * Get cities by province_id for transaction
     */
    public function getCitiesForTransaction($provinceId)
    {
        $cities = City::where('province_id', $provinceId)
            ->where('name', '!=', 'Kabupaten Administrasi Kepulauan Seribu')
            ->orderBy('name')
            ->select('id', 'name')
            ->get();

        return response()->json($cities);
    }

    /**
     * Get kecamatan by city_id (untuk profil dan lainnya)
     */
    public function getKecamatanByCity($cityId)
    {
        $kecamatan = Kecamatan::where('city_id', $cityId)
            ->orderBy('name')
            ->select('id', 'name')
            ->get();

        return response()->json($kecamatan);
    }

    /**
     * Get kecamatan by city_id untuk transaksi
     * Hanya return kecamatan yang memiliki rajaongkir_id (sudah disinkronisasi)
     */
    public function getKecamatanByTransactionCity($cityId)
    {
        $kecamatan = Kecamatan::where('city_id', $cityId)
            ->orderBy('name')
            ->select('id', 'name')
            ->get();

        return response()->json($kecamatan);
    }

    /**
     * Get all provinces (untuk initial dropdown akun/profil)
     */
    public function getProvinces()
    {
        $provinces = Province::orderBy('name')->select('id', 'name')->get();

        return response()->json($provinces);
    }

    /**
     * Get provinces untuk transaksi (hanya 6 provinsi yang diizinkan)
     */
    public function getProvincesForTransaction()
    {
        $allowedProvinces = ['Banten', 'Daerah Khusus Ibukota Jakarta', 'Jawa Barat', 'Jawa Tengah', 'Daerah Istimewa Yogyakarta', 'Jawa Timur'];
        $provinces = Province::whereIn('name', $allowedProvinces)
            ->orderBy('name')
            ->select('id', 'name')
            ->get();

        return response()->json($provinces);
    }
}
