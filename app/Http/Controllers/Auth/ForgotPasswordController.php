<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\OtpMail;
use App\Models\EmailOtp;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

/**
 * Modul: Authentication
 * Fitur: Lupa Password berbasis OTP email
 *
 * Tanggung jawab controller:
 * - Menampilkan halaman lupa password
 * - Mengirim dan memverifikasi OTP
 * - Mengelola sesi reset password sementara
 * - Memperbarui password pengguna
 */
class ForgotPasswordController extends Controller
{
    /**
     * Bagian: Halaman awal lupa password (UI entry point).
     */
    public function showForgotPasswordForm()
    {
        return view('auth.forgot-password');
    }

    /**
     * Bagian: OTP issuance (pengiriman kode OTP ke email pengguna).
     * Alur:
     * 1) Validasi format email
     * 2) Pastikan email terdaftar
     * 3) Simpan/update OTP beserta masa berlaku
     * 4) Kirim OTP melalui email
     */
    public function sendOtp(Request $request)
    {
        $request->validate(['email' => 'required|email'],
            ['email.email' => 'Format email tidak valid.']);

        // Cek apakah user terdaftar
        if (! User::where('email', $request->email)->exists()) {
            return response()->json(['message' => 'Email tidak terdaftar!'], 404);
        }

        $otp = rand(100000, 999999);

        // Simpan atau Update OTP di tabel
        EmailOtp::updateOrCreate(
            ['email' => $request->email, 'type' => 'forgot_password'],
            ['otp' => $otp, 'expires_at' => Carbon::now()->addMinutes(10)]
        );

        // Kirim Email
        Mail::to($request->email)->send(new OtpMail($otp));

        return response()->json(['message' => 'Kode OTP berhasil dikirim ke email!']);
    }

    /**
     * Bagian: OTP verification (validasi kode OTP).
     * Alur:
     * 1) Validasi input email dan OTP
     * 2) Cek OTP sesuai, tipe benar, dan belum kedaluwarsa
     * 3) Buat sesi reset password sementara
     * 4) Hapus OTP agar tidak bisa dipakai ulang
     */
    public function verifyOtp(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'otp' => 'required',
        ], [
            'otp.required' => 'Kode OTP wajib diisi.',
        ]);

        $check = EmailOtp::where('email', $request->email)
            ->where('otp', $request->otp)
            ->where('type', 'forgot_password')
            ->where('expires_at', '>', Carbon::now())
            ->first();

        if (! $check) {
            return response()->json(['message' => 'Kode OTP salah atau sudah kadaluarsa!'], 422);
        }

        session([
            'reset_email' => $request->email,
            'reset_email_expires_at' => Carbon::now()->addMinutes(10)->timestamp,
        ]);

        // Jika benar, hapus OTP agar tidak bisa dipakai lagi
        $check->delete();

        return response()->json(['message' => 'OTP Valid! Silahkan ganti password Anda.']);
    }

    /**
     * Bagian: Reset access gate.
     * Fungsi: Memastikan sesi verifikasi valid sebelum menampilkan form reset password.
     */
    public function showResetForm(Request $request)
    {
        if (! session()->has('reset_email') || ! session()->has('reset_email_expires_at')) {
            return redirect()->route('password.request')->with('error', 'Silahkan verifikasi email terlebih dahulu.');
        }

        if (Carbon::now()->timestamp > session('reset_email_expires_at')) {
            session()->forget(['reset_email', 'reset_email_expires_at']);

            return redirect()->route('password.request')->with('error', 'Sesi ubah kata sandi telah kedaluwarsa. Silahkan minta OTP baru.');
        }

        $email = session('reset_email');

        return view('auth.reset-password', compact('email'));
    }

    /**
     * Bagian: Password update handler.
     * Alur:
     * 1) Validasi sesi reset aktif
     * 2) Validasi email harus sama dengan email pada sesi reset
     * 3) Validasi password baru
     * 4) Simpan password baru (hash)
     * 5) Bersihkan sesi reset
     */
    public function updatePassword(Request $request)
    {
        // 1. GUARD: Pastikan akses berasal dari alur OTP yang valid
        if (! session()->has('reset_email') || ! session()->has('reset_email_expires_at')) {
            return response()->json([
                'message' => 'Akses ditolak. Silahkan verifikasi OTP kembali.',
            ], 403); // 403 Forbidden
        }

        if (Carbon::now()->timestamp > session('reset_email_expires_at')) {
            session()->forget(['reset_email', 'reset_email_expires_at']);

            return response()->json([
                'message' => 'Sesi ubah kata sandi telah kedaluwarsa. Silahkan minta OTP baru.',
            ], 403);
        }

        $sessionEmail = session('reset_email');

        // 2. VALIDASI: Pastikan email dan password memenuhi aturan
        $validator = Validator::make($request->all(), [
            // Email harus sama dengan yang ada di session tiket
            'email' => [
                'required',
                'email',
                function ($attribute, $value, $fail) use ($sessionEmail) {
                    if ($value !== $sessionEmail) {
                        $fail('Email tidak cocok dengan sesi verifikasi.');
                    }
                },
            ],
            'password' => 'required|min:8|confirmed',
        ], [
            'password.min' => 'Password minimal harus 8 karakter.',
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
        ]);

        // Jika input tidak valid (misal password kurang panjang)
        if ($validator->fails()) {
            return response()->json([
                'message' => $validator->errors()->first(),
            ], 422); // 422 Unprocessable Entity
        }

        try {
            // 3. PROSES: Update password user
            $user = User::where('email', $sessionEmail)->first();

            if (! $user) {
                return response()->json(['message' => 'User tidak ditemukan.'], 404);
            }

            // Enkripsi password baru
            $user->password = Hash::make($request->password);
            $user->save();

            // 4. CLEANUP: Hapus session "tiket" agar tidak bisa dipakai lagi
            session()->forget(['reset_email', 'reset_email_expires_at']);

            return response()->json([
                'message' => 'Password berhasil diperbarui!',
            ], 200);

        } catch (\Exception $e) {
            // Log error jika ada masalah database
            Log::error('Update Password Error: '.$e->getMessage());

            return response()->json([
                'message' => 'Terjadi kesalahan sistem saat menyimpan password.',
            ], 500);
        }
    }
}
