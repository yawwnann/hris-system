<?php

namespace Tests\Feature;

use App\Models\Setting;
use App\Models\User;
use App\Models\Attendance;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;
use Tests\TestCase;

class AttendanceTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $setting;
    protected $officeLat = -6.200000;
    protected $officeLong = 106.816666;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create([
            'password' => Hash::make('password'),
        ]);

        $this->setting = Setting::create([
            'office_lat' => $this->officeLat,
            'office_long' => $this->officeLong,
            'attendance_radius' => 100, // 100 meters
            'default_time_in' => '09:00:00',
            'default_time_out' => '17:00:00',
        ]);
    }

    public function test_user_can_check_in_within_radius()
    {
        Carbon::setTestNow(Carbon::createFromTime(8, 30, 0)); // Before default time in (09:00:00)

        $response = $this->actingAs($this->user)->postJson('/api/attendance/check-in', [
            'lat' => $this->officeLat,
            'long' => $this->officeLong,
        ]);

        $response->assertStatus(201)
                 ->assertJsonFragment(['message' => 'Check In successful']);
        
        $this->assertDatabaseHas('attendances', [
            'user_id' => $this->user->id,
            'status' => 'present',
        ]);

        Carbon::setTestNow(); // Reset Mock
    }

    public function test_user_is_marked_late_if_check_in_after_time_in()
    {
        Carbon::setTestNow(Carbon::createFromTime(9, 30, 0)); // After default time in (09:00:00)

        $response = $this->actingAs($this->user)->postJson('/api/attendance/check-in', [
            'lat' => $this->officeLat,
            'long' => $this->officeLong,
        ]);

        $response->assertStatus(201)
                 ->assertJsonFragment(['message' => 'Check In successful']);
        
        $this->assertDatabaseHas('attendances', [
            'user_id' => $this->user->id,
            'status' => 'late',
        ]);

        Carbon::setTestNow(); // Reset Mock
    }

    public function test_user_cannot_check_in_outside_radius()
    {
        $response = $this->actingAs($this->user)->postJson('/api/attendance/check-in', [
            'lat' => $this->officeLat + 0.1, // far away
            'long' => $this->officeLong + 0.1, // far away
        ]);

        $response->assertStatus(400)
                 ->assertJsonFragment(['message' => 'You are outside the attendance radius.']);
        
        $this->assertDatabaseMissing('attendances', [
            'user_id' => $this->user->id,
        ]);
    }

    public function test_user_cannot_check_in_twice_in_one_day()
    {
        Attendance::create([
            'user_id' => $this->user->id,
            'date' => Carbon::today()->toDateString(),
            'time_in' => '08:00:00',
            'lat_in' => $this->officeLat,
            'long_in' => $this->officeLong,
            'status' => 'present',
        ]);

        $response = $this->actingAs($this->user)->postJson('/api/attendance/check-in', [
            'lat' => $this->officeLat,
            'long' => $this->officeLong,
        ]);

        $response->assertStatus(400)
                 ->assertJsonFragment(['message' => 'You have already checked in today.']);
    }

    public function test_user_can_check_out()
    {
        Attendance::create([
            'user_id' => $this->user->id,
            'date' => Carbon::today()->toDateString(),
            'time_in' => Carbon::now()->subHours(8)->toTimeString(),
            'lat_in' => $this->officeLat,
            'long_in' => $this->officeLong,
            'status' => 'present',
        ]);

        $response = $this->actingAs($this->user)->postJson('/api/attendance/check-out', [
            'lat' => $this->officeLat,
            'long' => $this->officeLong,
        ]);

        $response->assertStatus(200)
                 ->assertJsonFragment(['message' => 'Check Out successful']);
        
        $this->assertDatabaseHas('attendances', [
            'user_id' => $this->user->id,
            'time_out' => Carbon::now()->toTimeString(),
        ]);
    }

    public function test_user_cannot_check_out_if_no_check_in()
    {
        $response = $this->actingAs($this->user)->postJson('/api/attendance/check-out', [
            'lat' => $this->officeLat,
            'long' => $this->officeLong,
        ]);

        $response->assertStatus(400)
                 ->assertJsonFragment(['message' => 'No Check In record found for today.']);
    }

    public function test_user_cannot_check_out_twice()
    {
        Attendance::create([
            'user_id' => $this->user->id,
            'date' => Carbon::today()->toDateString(),
            'time_in' => Carbon::now()->subHours(8)->toTimeString(),
            'time_out' => Carbon::now()->toTimeString(),
            'lat_in' => $this->officeLat,
            'long_in' => $this->officeLong,
            'status' => 'present',
        ]);

        $response = $this->actingAs($this->user)->postJson('/api/attendance/check-out', [
            'lat' => $this->officeLat,
            'long' => $this->officeLong,
        ]);

        $response->assertStatus(400)
                 ->assertJsonFragment(['message' => 'You have already checked out today.']);
    }

    public function test_check_in_requires_numeric_coordinates(): void
    {
        $this->actingAs($this->user)
            ->postJson('/api/attendance/check-in', ['lat' => 'invalid', 'long' => null])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['lat', 'long']);

        $this->assertDatabaseCount('attendances', 0);
    }

    public function test_employee_can_only_view_own_attendance(): void
    {
        $otherUser = User::factory()->create(['status' => 'active']);
        Attendance::create([
            'user_id' => $this->user->id,
            'date' => Carbon::today(),
            'time_in' => '08:00:00',
            'status' => 'present',
        ]);
        Attendance::create([
            'user_id' => $otherUser->id,
            'date' => Carbon::today()->subDay(),
            'time_in' => '08:00:00',
            'status' => 'present',
        ]);

        $response = $this->actingAs($this->user)->getJson('/api/attendance?paginate=false');

        $response->assertOk()
            ->assertJsonCount(1)
            ->assertJsonPath('0.user_id', $this->user->id);
    }
}
