<?php

namespace Tests\Feature;

use App\Mail\PasswordResetOtpMail;
use App\Models\PasswordResetOtp;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class ForgotPasswordTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_request_otp_via_email(): void
    {
        Mail::fake();

        $user = User::factory()->create([
            'email' => 'user@example.com',
            'status' => 'active',
        ]);

        $response = $this->postJson('/api/forgot-password/request-otp', [
            'email' => 'user@example.com',
        ]);

        $response->assertOk()
            ->assertJsonStructure(['message']);

        Mail::assertQueued(PasswordResetOtpMail::class, function ($mail) use ($user) {
            return $mail->hasTo($user->email);
        });

        $this->assertDatabaseHas('password_reset_otps', [
            'email' => $user->email,
        ]);
    }

    public function test_forgot_password_full_flow(): void
    {
        Mail::fake();

        $user = User::factory()->create([
            'email' => 'employee@example.com',
            'password' => Hash::make('oldpassword'),
            'status' => 'active',
        ]);

        // 1. Request OTP
        $this->postJson('/api/forgot-password/request-otp', [
            'email' => 'employee@example.com',
        ])->assertOk();

        $otpRecord = PasswordResetOtp::where('email', 'employee@example.com')->first();
        $this->assertNotNull($otpRecord);

        // We can inspect the mail sent to get the plain OTP or generate a known record for test
        // Let's force update code hash for deterministic test
        $plainOtp = '654321';
        $otpRecord->update(['code' => Hash::make($plainOtp)]);

        // 2. Verify OTP
        $verifyResponse = $this->postJson('/api/forgot-password/verify-otp', [
            'email' => 'employee@example.com',
            'otp' => $plainOtp,
        ]);

        $verifyResponse->assertOk()
            ->assertJsonStructure(['reset_token']);

        $resetToken = $verifyResponse->json('reset_token');

        // 3. Reset Password
        $resetResponse = $this->postJson('/api/forgot-password/reset', [
            'email' => 'employee@example.com',
            'reset_token' => $resetToken,
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ]);

        $resetResponse->assertOk();

        // Verify login works with new password
        $this->postJson('/api/login', [
            'email' => 'employee@example.com',
            'password' => 'newpassword123',
        ])->assertOk();
    }
}
