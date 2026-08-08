<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\LeaveRequest;
use App\Models\OvertimeRequest;
use App\Models\CalendarEvent;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function adminSummary()
    {
        if (Auth::user()->role !== 'admin') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $today = Carbon::today()->toDateString();
        $last7Days = Carbon::today()->subDays(6)->toDateString();

        $totalEmployees = User::where('role', 'employee')->where('status', 'active')->count();

        $attendancesToday = Attendance::where('date', $today)->get();
        $presentToday = $attendancesToday->whereIn('status', ['present', 'late'])->count();
        $lateToday = $attendancesToday->where('status', 'late')->count();

        $onLeaveToday = LeaveRequest::where('status', 'approved')
            ->whereDate('start_date', '<=', $today)
            ->whereDate('end_date', '>=', $today)
            ->count();

        $absentToday = $totalEmployees - ($presentToday + $onLeaveToday);
        if ($absentToday < 0) $absentToday = 0;

        $pendingLeaves = LeaveRequest::where('status', 'pending')->count();
        $pendingOvertimes = OvertimeRequest::where('status', 'pending')->count();

        // Chart Data (Last 7 Days detailed)
        $chartData = [];
        for ($i = 6; $i >= 0; $i--) {
            $dateStr = Carbon::today()->subDays($i)->toDateString();
            
            $dayAttendances = Attendance::where('date', $dateStr)->get();
            $pres = $dayAttendances->where('status', 'present')->count();
            $lt = $dayAttendances->where('status', 'late')->count();
            
            $onLv = LeaveRequest::where('status', 'approved')
                ->whereDate('start_date', '<=', $dateStr)
                ->whereDate('end_date', '>=', $dateStr)
                ->count();
                
            $abs = $totalEmployees - ($pres + $lt + $onLv);
            if ($abs < 0) $abs = 0;

            $chartData[] = [
                'date' => $dateStr,
                'day' => Carbon::parse($dateStr)->format('D'),
                'present' => $pres,
                'late' => $lt,
                'on_leave' => $onLv,
                'absent' => $abs
            ];
        }

        // Division Stats
        $divisionStats = User::select('division_id', DB::raw('count(*) as count'))
            ->where('role', 'employee')
            ->where('status', 'active')
            ->groupBy('division_id')
            ->with('division')
            ->get();

        // Upcoming Calendar Events
        $upcomingEvents = CalendarEvent::whereDate('start_datetime', '>=', $today)
            ->with(['users:id,name', 'divisions:id,name'])
            ->orderBy('start_datetime', 'asc')
            ->limit(5)
            ->get()->map(function($event) {
                return [
                    'id' => $event->id,
                    'title' => $event->title,
                    'description' => $event->title, // mapping untuk frontend lama
                    'type' => $event->category,
                    'date' => Carbon::parse($event->start_datetime)->format('Y-m-d H:i'),
                    'color' => $event->color,
                    'users' => $event->users,
                    'divisions' => $event->divisions,
                    'total_participants' => $event->users->count() + $event->divisions->count()
                ];
            });

        return response()->json([
            'total_employees' => $totalEmployees,
            'present_today' => $presentToday,
            'late_today' => $lateToday,
            'on_leave_today' => $onLeaveToday,
            'absent_today' => $absentToday,
            'pending_requests' => $pendingLeaves + $pendingOvertimes,
            'attendance_chart' => $chartData,
            'division_stats' => $divisionStats,
            'upcoming_events' => $upcomingEvents,
        ]);
    }

    public function employeeSummary()
    {
        $user = Auth::user();
        $today = Carbon::today()->toDateString();
        $startOfWeek = Carbon::now()->startOfWeek()->toDateString();
        $endOfWeek = Carbon::now()->endOfWeek()->toDateString();

        $todayAttendance = Attendance::where('user_id', $user->id)
            ->where('date', $today)
            ->first();

        $thisWeekHours = Attendance::where('user_id', $user->id)
            ->whereBetween('date', [$startOfWeek, $endOfWeek])
            ->sum('total_hours');

        $pendingLeaves = LeaveRequest::where('user_id', $user->id)
            ->where('status', 'pending')->count();
            
        $pendingOvertimes = OvertimeRequest::where('user_id', $user->id)
            ->where('status', 'pending')->count();

        // Upcoming Calendar Events
        $upcomingEvents = CalendarEvent::whereDate('start_datetime', '>=', $today)
            ->with(['users:id,name', 'divisions:id,name'])
            ->orderBy('start_datetime', 'asc')
            ->limit(3)
            ->get()->map(function($event) {
                return [
                    'id' => $event->id,
                    'title' => $event->title,
                    'description' => $event->title,
                    'type' => $event->category,
                    'date' => Carbon::parse($event->start_datetime)->format('Y-m-d H:i'),
                    'color' => $event->color,
                    'users' => $event->users,
                    'divisions' => $event->divisions,
                    'total_participants' => $event->users->count() + $event->divisions->count()
                ];
            });

        return response()->json([
            'leave_quota' => $user->leave_quota,
            'today_status' => $todayAttendance ? $todayAttendance->status : 'Not Checked In',
            'this_week_hours' => round($thisWeekHours, 2),
            'pending_requests' => $pendingLeaves + $pendingOvertimes,
            'recent_attendances' => Attendance::where('user_id', $user->id)
                ->orderBy('date', 'desc')
                ->limit(5)
                ->get(),
            'upcoming_events' => $upcomingEvents,
        ]);
    }
}
