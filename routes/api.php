<?php

use App\Http\Controllers\Api\MidtransCallbackController;
use App\Http\Controllers\WilayahController;
use Illuminate\Support\Facades\Route;

Route::middleware('api')->group(function () {
    Route::get('/cities/{province_id}', [WilayahController::class, 'getCitiesByProvince']);
    Route::get('/cities-transaction/{province_id}', [WilayahController::class, 'getCitiesForTransaction']);
    Route::get('/districts/{city_id}', [WilayahController::class, 'getKecamatanByCity']);
    Route::get('/districts-transaction/{city_id}', [WilayahController::class, 'getKecamatanByTransactionCity']);

    // Midtrans Callback
    Route::get('/midtrans/callback', function () {
        return response()->json(['message' => 'Midtrans Callback Endpoint is active. Please use POST to send data.']);
    });
    Route::post('/midtrans/callback', [MidtransCallbackController::class, 'handle']);
});
