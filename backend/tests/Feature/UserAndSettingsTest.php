<?php

namespace Tests\Feature;

use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UserAndSettingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_user_with_hashed_password(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'status' => 'active']);

        $response = $this->actingAs($admin)->postJson('/api/users', [
            'name' => 'New Employee',
            'email' => 'new.employee@example.com',
            'password' => 'secret123',
            'role' => 'employee',
            'status' => 'active',
        ]);

        $response->assertCreated();
        $user = User::where('email', 'new.employee@example.com')->firstOrFail();

        $this->assertTrue(Hash::check('secret123', $user->password));
        $this->assertNotSame('secret123', $user->password);
    }

    public function test_duplicate_user_email_is_rejected(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'status' => 'active']);
        User::factory()->create(['email' => 'existing@example.com']);

        $this->actingAs($admin)->postJson('/api/users', [
            'name' => 'Duplicate',
            'email' => 'existing@example.com',
            'password' => 'secret123',
            'role' => 'employee',
            'status' => 'active',
        ])->assertUnprocessable()->assertJsonValidationErrors('email');
    }

    public function test_employee_cannot_access_employee_management(): void
    {
        $employee = User::factory()->create(['role' => 'employee', 'status' => 'active']);

        $this->actingAs($employee)->getJson('/api/users')->assertForbidden();

        $this->actingAs($employee)->postJson('/api/users', [
            'name' => 'Unauthorized User',
            'email' => 'unauthorized@example.com',
            'password' => 'secret123',
            'role' => 'employee',
            'status' => 'active',
        ])->assertForbidden();

        $this->assertDatabaseMissing('users', ['email' => 'unauthorized@example.com']);
    }

    public function test_only_admin_can_update_settings(): void
    {
        $employee = User::factory()->create(['role' => 'employee', 'status' => 'active']);
        $admin = User::factory()->create(['role' => 'admin', 'status' => 'active']);
        Setting::create(['company_name' => 'Original', 'attendance_radius' => 100]);

        $this->actingAs($employee)->postJson('/api/settings', [
            'company_name' => 'Changed by employee',
        ])->assertForbidden();

        $this->actingAs($admin)->postJson('/api/settings', [
            'company_name' => 'Changed by admin',
            'attendance_radius' => 250,
        ])->assertOk();

        $this->assertDatabaseHas('settings', [
            'company_name' => 'Changed by admin',
            'attendance_radius' => 250,
        ]);
    }
}
