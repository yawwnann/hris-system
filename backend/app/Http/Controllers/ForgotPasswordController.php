<?php

namespace App\Http\Controllers;

use App\Mail\PasswordResetOtpMail;
use App\Models\PasswordResetOtp;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ForgotPasswordController extends Controller
{
    public function requestOtp(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $email = strtolower(trim($request->email));
        $user = User::where('email', $email)->first();

        // Always return success response to prevent email enumeration, but only send if user exists and active
        if ($user && $user->status === 'active') {
            // Invalidate prior unused OTPs for this email
            PasswordResetOtp::where('email', $email)
                ->whereNull('verified_at')
                ->delete();

            $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

            PasswordResetOtp::create([
                'email' => $email,
                'code' => Hash::make($code),
                'expires_at' => now()->addMinutes(10),
                'attempts' => 0,
            ]);

            Mail::to($email)->send(new PasswordResetOtpMail($code));
        }

        return response()->json([
            'message' => 'Jika email terdaftar dan aktif, kode OTP telah dikirimkan ke email Anda.',
        ]);
    }

    public function verifyOtp(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'otp' => 'required|string|size:6',
        ]);

        $email = strtolower(trim($request->email));
        $otpRecord = PasswordResetOtp::where('email', $email)
            ->whereNull('verified_at')
            ->latest()
            ->first();

        if (! $otpRecord || $otpRecord->expires_at->isPast()) {
            throw ValidationException::withMessages([
                'otp' => ['Kode OTP tidak valid atau sudah kedaluwarsa.'],
            ]);
        }

        if ($otpRecord->attempts >= 5) {
            throw ValidationException::withMessages([
                'otp' => ['Terlalu banyak percobaan yang salah. Silakan minta kode OTP baru.'],
            ]);
        }

        if (! Hash::check($request->otp, $otpRecord->code)) {
            $otpRecord->increment('attempts');
            throw ValidationException::withMessages([
                'otp' => ['Kode OTP salah. Sisa percobaan: ' . (5 - $otpRecord->attempts)],
            ]);
        }

        $resetToken = Str::random(64);
        $otpRecord->update([
            'verified_at' => now(),
            'reset_token' => $resetToken,
        ]);

        return response()->json([
            'message' => 'OTP berhasil diverifikasi.',
            'reset_token' => $resetToken,
        ]);
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'reset_token' => 'required|string',
            'password' => 'required|string|min:6|confirmed',
        ]);

        $email = strtolower(trim($request->email));
        $otpRecord = PasswordResetOtp::where('email', $email)
            ->where('reset_token', $request->reset_token)
            ->whereNotNull('verified_at')
            ->latest()
            ->first();

        if (! $otpRecord || $otpRecord->expires_at->isPast()) {
            throw ValidationException::withMessages([
                'reset_token' => ['Sesi reset sandi tidak valid atau sudah kedaluwarsa.'],
            ]);
        }

        $user = User::where('email', $email)->first();

        if (! $user) {
            throw ValidationException::withMessages([
                'email' => ['Pengguna tidak ditemukan.'],
            ]);
        }

        $user->update([
            'password' => $request->password, // automatically hashed via User model cast
        ]);

        // Clean up all reset records for this email
        PasswordResetOtp::where('email', $email)->delete();

        return response()->json([
            'message' => 'Kata sandi berhasil diperbarui. Silakan masuk dengan kata sandi baru.',
        ]);
    }
}
