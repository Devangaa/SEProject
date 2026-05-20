{{-- ============================================================================= --}}
{{-- FILE: emails/otp.blade.php --}}
{{-- HALAMAN: Email OTP --}}
{{-- DESKRIPSI: Template email berisi kode OTP untuk reset password atau verifikasi akun. --}}
{{-- ============================================================================= --}}

<x-mail::message>
{{-- Bagian: Judul & Pembuka --}}
# Kode Verifikasi Anda

Halo,

Anda menerima email ini karena kami menerima permintaan pengaturan ulang kata sandi atau verifikasi untuk akun Anda.

Berikut adalah kode OTP Anda:

{{-- Bagian: Panel Kode OTP --}}
<x-mail::panel>
<div style="text-align: center; font-size: 32px; font-weight: bold; letter-spacing: 5px;">
{{ $otp }}
</div>
</x-mail::panel>

Kode ini hanya berlaku selama **10 menit**. Jangan berikan kode ini kepada siapapun demi keamanan akun Anda.

Jika Anda tidak merasa melakukan permintaan ini, Anda dapat mengabaikan email ini.

Terima kasih,<br>
{{ config('app.name') }}
</x-mail::message>
