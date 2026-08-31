<?php

namespace Tests\Feature;

use App\Models\LeaveRequest;
use App\Models\OvertimeRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LeaveAndOvertimeTest extends TestCase
{
    use RefreshDatabase;

    public function test_annual_leave_requires_quota_and_approval_decrements_it_once(): void
    {
        $employee = User::factory()->create([
            'role' => 'employee',
            'status' => 'active',
            'leave_quota' => 5,
        ]);
        $admin = User::factory()->create(['role' => 'admin', 'status' => 'active']);

        $leave = $this->actingAs($employee)->postJson('/api/leave-requests', [
            'type' => 'annual',
            'start_date' => '2026-09-01',
            'end_date' => '2026-09-03',
            'reason' => 'Family event',
        ])->assertCreated()->json('id');

        $this->actingAs($admin)->putJson("/api/leave-requests/{$leave}/status", [
            'status' => 'approved',
        ])->assertOk();

        $this->assertDatabaseHas('users', ['id' => $employee->id, 'leave_quota' => 2]);

        $this->actingAs($admin)->putJson("/api/leave-requests/{$leave}/status", [
            'status' => 'approved',
        ])->assertOk();

        $this->assertDatabaseHas('users', ['id' => $employee->id, 'leave_quota' => 2]);
    }

    public function test_employee_cannot_change_leave_status_or_cancel_another_users_request(): void
    {
        $employee = User::factory()->create(['role' => 'employee', 'status' => 'active']);
        $otherEmployee = User::factory()->create(['role' => 'employee', 'status' => 'active']);
        $leave = LeaveRequest::create([
            'user_id' => $otherEmployee->id,
            'type' => 'sick',
            'start_date' => '2026-09-01',
            'end_date' => '2026-09-01',
            'reason' => 'Sick leave',
            'status' => 'pending',
        ]);

        $this->actingAs($employee)
            ->putJson("/api/leave-requests/{$leave->id}/status", ['status' => 'approved'])
            ->assertForbidden();

        $this->actingAs($employee)
            ->deleteJson("/api/leave-requests/{$leave->id}")
            ->assertForbidden();
    }

    public function test_overtime_calculates_duration_and_requires_admin_for_approval(): void
    {
        $employee = User::factory()->create(['role' => 'employee', 'status' => 'active']);
        $admin = User::factory()->create(['role' => 'admin', 'status' => 'active']);

        $overtime = $this->actingAs($employee)->postJson('/api/overtime-requests', [
            'date' => '2026-09-01',
            'start_time' => '17:30',
            'end_time' => '20:00',
            'reason' => 'Production deployment',
        ])->assertCreated()->json('id');

        $this->assertDatabaseHas('overtime_requests', [
            'id' => $overtime,
            'user_id' => $employee->id,
            'total_duration' => 2.5,
            'status' => 'pending',
        ]);

        $this->actingAs($employee)
            ->putJson("/api/overtime-requests/{$overtime}/status", ['status' => 'approved'])
            ->assertForbidden();

        $this->actingAs($admin)
            ->putJson("/api/overtime-requests/{$overtime}/status", ['status' => 'approved'])
            ->assertOk();
    }

    public function test_leave_and_overtime_validation_rejects_invalid_ranges(): void
    {
        $employee = User::factory()->create(['role' => 'employee', 'status' => 'active']);

        $this->actingAs($employee)->postJson('/api/leave-requests', [
            'type' => 'annual',
            'start_date' => '2026-09-03',
            'end_date' => '2026-09-01',
            'reason' => 'Invalid range',
        ])->assertUnprocessable()->assertJsonValidationErrors('end_date');

        $this->actingAs($employee)->postJson('/api/overtime-requests', [
            'date' => '2026-09-01',
            'start_time' => '20:00',
            'end_time' => '19:00',
            'reason' => 'Invalid range',
        ])->assertUnprocessable()->assertJsonValidationErrors('end_time');

        $this->assertDatabaseCount('leave_requests', 0);
        $this->assertDatabaseCount('overtime_requests', 0);
    }
}
