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
        // --- QUICK APPROVALS ---
        $pendingLeaveRequests = LeaveRequest::with('user:id,name,position_id')->where('status', 'pending')->limit(5)->get()->map(function($req) {
            $role = $req->user->position ? $req->user->position->name : 'Employee';
            return [
                'id' => 'L' . $req->id,
                'original_id' => $req->id,
                'type' => 'Leave',
                'user' => $req->user->name,
                'role' => $role,
                'date' => Carbon::parse($req->start_date)->format('d M') . ' - ' . Carbon::parse($req->end_date)->format('d M'),
                'duration' => null,
                'reason' => $req->reason,
                'status' => 'pending',
                'created_at' => clone $req->created_at
            ];
        });

        $pendingOvertimeRequests = OvertimeRequest::with('user:id,name,position_id')->where('status', 'pending')->limit(5)->get()->map(function($req) {
            $role = $req->user->position ? $req->user->position->name : 'Employee';
            return [
                'id' => 'O' . $req->id,
                'original_id' => $req->id,
                'type' => 'Overtime',
                'user' => $req->user->name,
                'role' => $role,
                'date' => Carbon::parse($req->date)->format('d M'),
                'duration' => $req->duration_hours . ' Hours',
                'reason' => $req->notes,
                'status' => 'pending',
                'created_at' => clone $req->created_at
            ];
        });

        $quickApprovals = $pendingLeaveRequests->concat($pendingOvertimeRequests)->sortBy('created_at')->values()->take(5);

        // --- EMPLOYEE STATUS ---
        $awayToday = LeaveRequest::with('user:id,name')->where('status', 'approved')
            ->whereDate('start_date', '<=', $today)
            ->whereDate('end_date', '>=', $today)
            ->get()->map(function($req) {
                return [
                    'id' => $req->id,
                    'user' => $req->user->name,
                    'type' => $req->type, 
                    'is_sick' => strtolower($req->type) === 'sick' || strtolower($req->type) === 'sick leave'
                ];
            });

        $activeUsers = User::where('status', 'active')->get();
        $upcomingBirthdays = [];
        $upcomingAnniversaries = [];

        foreach ($activeUsers as $u) {
            if ($u->dob) {
                $dob = Carbon::parse($u->dob)->year(Carbon::now()->year);
                if ($dob->isPast() && !$dob->isToday()) $dob->addYear();
                if ($dob->diffInDays(Carbon::now()) <= 7 && $dob->isAfter(Carbon::now()->subDay())) {
                    $upcomingBirthdays[] = [
                        'id' => 'B'.$u->id,
                        'user' => $u->name,
                        'type' => 'Birthday' . ($dob->isToday() ? ' (Today)' : ' ('. $dob->format('d M') .')'),
                        'date' => $dob->format('Y-m-d')
                    ];
                }
            }
            if ($u->join_date) {
                $jd = Carbon::parse($u->join_date)->year(Carbon::now()->year);
                if ($jd->isPast() && !$jd->isToday()) $jd->addYear();
                if ($jd->diffInDays(Carbon::now()) <= 7 && $jd->isAfter(Carbon::now()->subDay())) {
                    $years = $jd->year - Carbon::parse($u->join_date)->year;
                    if ($years > 0) {
                        $upcomingAnniversaries[] = [
                            'id' => 'A'.$u->id,
                            'user' => $u->name,
                            'type' => $years . ' Year Anniversary' . ($jd->isToday() ? ' (Today)' : ' ('. $jd->format('d M') .')'),
                            'date' => $jd->format('Y-m-d')
                        ];
                    }
                }
            }
        }
        $celebrations = collect(array_merge($upcomingBirthdays, $upcomingAnniversaries))->sortBy('date')->values();
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
            'quick_approvals' => $quickApprovals,
            'away_today' => $awayToday,
            'celebrations' => $celebrations,
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
        // --- EMPLOYEE STATUS ---
        $awayToday = LeaveRequest::with('user:id,name')->where('status', 'approved')
            ->whereDate('start_date', '<=', $today)
            ->whereDate('end_date', '>=', $today)
            ->get()->map(function($req) {
                return [
                    'id' => $req->id,
                    'user' => $req->user->name,
                    'type' => $req->type, 
                    'is_sick' => strtolower($req->type) === 'sick' || strtolower($req->type) === 'sick leave'
                ];
            });

        $activeUsers = User::where('status', 'active')->get();
        $upcomingBirthdays = [];
        $upcomingAnniversaries = [];

        foreach ($activeUsers as $u) {
            if ($u->dob) {
                $dob = Carbon::parse($u->dob)->year(Carbon::now()->year);
                if ($dob->isPast() && !$dob->isToday()) $dob->addYear();
                if ($dob->diffInDays(Carbon::now()) <= 7 && $dob->isAfter(Carbon::now()->subDay())) {
                    $upcomingBirthdays[] = [
                        'id' => 'B'.$u->id,
                        'user' => $u->name,
                        'type' => 'Birthday' . ($dob->isToday() ? ' (Today)' : ' ('. $dob->format('d M') .')'),
                        'date' => $dob->format('Y-m-d')
                    ];
                }
            }
            if ($u->join_date) {
                $jd = Carbon::parse($u->join_date)->year(Carbon::now()->year);
                if ($jd->isPast() && !$jd->isToday()) $jd->addYear();
                if ($jd->diffInDays(Carbon::now()) <= 7 && $jd->isAfter(Carbon::now()->subDay())) {
                    $years = $jd->year - Carbon::parse($u->join_date)->year;
                    if ($years > 0) {
                        $upcomingAnniversaries[] = [
                            'id' => 'A'.$u->id,
                            'user' => $u->name,
                            'type' => $years . ' Year Anniversary' . ($jd->isToday() ? ' (Today)' : ' ('. $jd->format('d M') .')'),
                            'date' => $jd->format('Y-m-d')
                        ];
                    }
                }
            }
        }
        $celebrations = collect(array_merge($upcomingBirthdays, $upcomingAnniversaries))->sortBy('date')->values();
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
            'away_today' => $awayToday,
            'celebrations' => $celebrations,
        ]);
    }
}
