<?php

use App\Models\EmailOtp;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

beforeEach(function () {
    DB::table('provinces')->insert([
        'id' => 1,
        'name' => 'Provinsi Test',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    DB::table('cities')->insert([
        'id' => 1,
        'province_id' => 1,
        'name' => 'Kota Test',
        'ongkir' => 10000,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    DB::table('kecamatans')->insert([
        'id' => 1,
        'city_id' => 1,
        'name' => 'Kecamatan Test',
        'created_at' => now(),
        'updated_at' => now(),
    ]);
});

test('verify otp returns error when empty', function () {
    $user = User::factory()->create();

    $response = $this->postJson('/forgot-password/verify-otp', [
        'email' => $user->email,
        'otp' => '',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['otp'])
        ->assertJsonFragment([
            'message' => 'Kode OTP wajib diisi.',
        ]);
});

test('verify otp returns error when otp is wrong and less than 6 digits', function () {
    $user = User::factory()->create();

    // Create a valid OTP in database
    EmailOtp::create([
        'email' => $user->email,
        'otp' => '123456',
        'type' => 'forgot_password',
        'expires_at' => Carbon::now()->addMinutes(10),
    ]);

    // Send a wrong OTP that is less than 6 digits (e.g. 123)
    $response = $this->postJson('/forgot-password/verify-otp', [
        'email' => $user->email,
        'otp' => '123',
    ]);

    $response->assertStatus(422)
        ->assertJsonFragment([
            'message' => 'Kode OTP salah atau sudah kadaluarsa!',
        ]);
});

test('verify otp returns error when otp is wrong and exactly 6 digits', function () {
    $user = User::factory()->create();

    // Create a valid OTP in database
    EmailOtp::create([
        'email' => $user->email,
        'otp' => '123456',
        'type' => 'forgot_password',
        'expires_at' => Carbon::now()->addMinutes(10),
    ]);

    // Send a wrong OTP of exactly 6 digits
    $response = $this->postJson('/forgot-password/verify-otp', [
        'email' => $user->email,
        'otp' => '999999',
    ]);

    $response->assertStatus(422)
        ->assertJsonFragment([
            'message' => 'Kode OTP salah atau sudah kadaluarsa!',
        ]);
});

test('verify otp succeeds with correct otp', function () {
    $user = User::factory()->create();

    // Create a valid OTP in database
    EmailOtp::create([
        'email' => $user->email,
        'otp' => '123456',
        'type' => 'forgot_password',
        'expires_at' => Carbon::now()->addMinutes(10),
    ]);

    $response = $this->postJson('/forgot-password/verify-otp', [
        'email' => $user->email,
        'otp' => '123456',
    ]);

    $response->assertSuccessful()
        ->assertJsonFragment([
            'message' => 'OTP Valid! Silahkan ganti password Anda.',
        ]);
});
