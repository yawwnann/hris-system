@component('mail::message')
# Kode Verifikasi Lupa Kata Sandi

Halo,

Anda menerima email ini karena kami menerima permintaan pemulihan kata sandi untuk akun HRIS System Anda.

Berikut adalah kode OTP verifikasi Anda:

@component('mail::panel')
# {!! $otp !!}
@endcomponent

Kode ini berlaku selama **10 menit**. Jangan berikan kode OTP ini kepada siapa pun, termasuk pihak HRIS System.

Jika Anda tidak merasa melakukan permintaan ini, abaikan email ini.

Salam,<br>
{{ config('app.name') }}
@component('mail::subcopy')
Jika Anda mengalami kendala, silakan hubungi tim dukungan perusahaan.
@endcomponent
@endcomponent
