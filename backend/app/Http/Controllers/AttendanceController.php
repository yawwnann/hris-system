<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Setting;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AttendanceController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        
        $query = Attendance::with('user');
        
        if ($user->role !== 'admin') {
            $query->where('user_id', $user->id);
        }

        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where(function($q) use ($search) {
                $q->whereHas('user', function ($q2) use ($search) {
                    $q2->where('name', 'like', "%{$search}%");
                })->orWhere('status', 'like', "%{$search}%");
            });
        }

        $sortBy = $request->input('sort_by', 'date');
        $sortDir = $request->input('sort_dir', 'desc');
        $query->orderBy($sortBy, $sortDir);

        $perPage = $request->input('per_page', 10);
        
        if ($perPage == -1 || $request->input('paginate') === 'false') {
            return response()->json($query->get());
        }

        return response()->json($query->paginate($perPage));
    }

    public function today()
    {
        $user = Auth::user();
        $today = Carbon::today()->toDateString();
        $attendance = Attendance::where('user_id', $user->id)->where('date', $today)->first();
        return response()->json($attendance);
    }

    public function checkIn(Request $request)
    {
        $request->validate([
            'lat' => 'required|numeric',
            'long' => 'required|numeric',
        ]);

        $user = Auth::user();
        $today = Carbon::today()->toDateString();

        // Check if already checked in
        $existing = Attendance::where('user_id', $user->id)->where('date', $today)->first();
        if ($existing) {
            return response()->json(['message' => 'You have already checked in today.'], 400);
        }

        // Geofencing Validation
        $setting = Setting::first();
        if (!$setting || !$setting->office_lat || !$setting->office_long) {
            return response()->json(['message' => 'Office location not set in system.'], 500);
        }

        $distance = $this->calculateDistance(
            $request->lat, $request->long,
            $setting->office_lat, $setting->office_long
        );

        if ($distance > $setting->attendance_radius) {
            return response()->json([
                'message' => 'You are outside the attendance radius.',
                'distance' => round($distance, 2) . ' meters',
                'allowed_radius' => $setting->attendance_radius . ' meters'
            ], 400);
        }

        // Determine Status (Present or Late)
        $timeIn = Carbon::now()->toTimeString();
        $status = 'present';
        
        $expectedTimeIn = $user->shift ? $user->shift->time_in : $setting->default_time_in;
        if ($expectedTimeIn && $timeIn > $expectedTimeIn) {
            $status = 'late';
        }

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => $today,
            'time_in' => $timeIn,
            'lat_in' => $request->lat,
            'long_in' => $request->long,
            'status' => $status,
            'device' => $request->header('User-Agent'),
            'ip_address' => $request->ip(),
        ]);

        return response()->json(['message' => 'Check In successful', 'data' => $attendance], 201);
    }

    public function checkOut(Request $request)
    {
        $request->validate([
            'lat' => 'required|numeric',
            'long' => 'required|numeric',
        ]);

        $user = Auth::user();
        $today = Carbon::today()->toDateString();

        $attendance = Attendance::where('user_id', $user->id)->where('date', $today)->first();
        
        if (!$attendance) {
            return response()->json(['message' => 'No Check In record found for today.'], 400);
        }

        if ($attendance->time_out) {
            return response()->json(['message' => 'You have already checked out today.'], 400);
        }

        $timeOut = Carbon::now()->toTimeString();
        
        // Calculate Total Hours
        $timeInCarbon = Carbon::parse($attendance->time_in);
        $timeOutCarbon = Carbon::parse($timeOut);
        $totalHours = $timeInCarbon->diffInMinutes($timeOutCarbon) / 60;

        $attendance->update([
            'time_out' => $timeOut,
            'lat_out' => $request->lat,
            'long_out' => $request->long,
            'total_hours' => round($totalHours, 2),
        ]);

        return response()->json(['message' => 'Check Out successful', 'data' => $attendance], 200);
    }

    /**
     * Calculate distance between two coordinates in meters using Haversine formula
     */
    private function calculateDistance($lat1, $lon1, $lat2, $lon2)
    {
        $earthRadius = 6371000; // in meters

        $latDelta = deg2rad($lat2 - $lat1);
        $lonDelta = deg2rad($lon2 - $lon1);

        $a = sin($latDelta / 2) * sin($latDelta / 2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($lonDelta / 2) * sin($lonDelta / 2);
             
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }
}
