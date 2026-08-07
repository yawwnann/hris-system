<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use App\Models\User;
use App\Models\Setting;
use App\Models\Attendance;
use Carbon\Carbon;

class AttendanceTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        
        Setting::query()->delete();

        Setting::create([
            'office_lat' => -6.200000,
            'office_long' => 106.816666,
            'attendance_radius' => 100, // 100 meters
            'default_time_in' => '09:00:00',
            'default_time_out' => '17:00:00',
        ]);
    }

    public function test_can_get_today_attendance()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        Attendance::create([
            'user_id' => $user->id,
            'date' => Carbon::today()->toDateString(),
            'time_in' => '08:00:00',
            'lat_in' => -6.200000,
            'long_in' => 106.816666,
            'status' => 'present',
        ]);

        $response = $this->getJson('/api/attendance/today');

        $response->assertStatus(200)
                 ->assertJsonPath('time_in', '08:00:00');
    }

    public function test_can_check_in_within_radius()
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        
        Carbon::setTestNow(Carbon::today()->setTime(8, 0, 0));

        $response = $this->postJson('/api/attendance/check-in', [
            'lat' => -6.200000,
            'long' => 106.816666,
        ]);

        $response->assertStatus(201)
                 ->assertJsonPath('message', 'Check In successful')
                 ->assertJsonPath('data.status', 'present');
                 
        $this->assertDatabaseHas('attendances', [
            'user_id' => $user->id,
            'date' => Carbon::today()->toDateString(),
        ]);
    }

    public function test_cannot_check_in_outside_radius()
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        
        // Far away coordinates (e.g. Bandung)
        $response = $this->postJson('/api/attendance/check-in', [
            'lat' => -6.917464,
            'long' => 107.619123,
        ]);

        $response->assertStatus(400)
                 ->assertJsonPath('message', 'You are outside the attendance radius.');
    }

    public function test_check_in_late_status()
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        
        Carbon::setTestNow(Carbon::today()->setTime(10, 0, 0)); // After 09:00

        $response = $this->postJson('/api/attendance/check-in', [
            'lat' => -6.200000,
            'long' => 106.816666,
        ]);

        $response->assertStatus(201)
                 ->assertJsonPath('data.status', 'late');
    }

    public function test_cannot_check_in_twice()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        Attendance::create([
            'user_id' => $user->id,
            'date' => Carbon::today()->toDateString(),
            'time_in' => '08:00:00',
            'lat_in' => -6.200000,
            'long_in' => 106.816666,
            'status' => 'present',
        ]);

        $response = $this->postJson('/api/attendance/check-in', [
            'lat' => -6.200000,
            'long' => 106.816666,
        ]);

        $response->assertStatus(400)
                 ->assertJsonPath('message', 'You have already checked in today.');
    }

    public function test_can_check_out()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        Attendance::create([
            'user_id' => $user->id,
            'date' => Carbon::today()->toDateString(),
            'time_in' => '08:00:00',
            'lat_in' => -6.200000,
            'long_in' => 106.816666,
            'status' => 'present',
        ]);

        Carbon::setTestNow(Carbon::today()->setTime(17, 0, 0)); // 9 hours difference

        $response = $this->postJson('/api/attendance/check-out', [
            'lat' => -6.200000,
            'long' => 106.816666,
        ]);

        $response->assertStatus(200)
                 ->assertJsonPath('message', 'Check Out successful')
                 ->assertJsonPath('data.total_hours', 9);
    }
}
