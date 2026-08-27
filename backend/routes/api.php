<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DivisionController;
use App\Http\Controllers\PositionController;
use App\Http\Controllers\ShiftController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AgendaController;
use App\Http\Controllers\RosterController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\CalendarEventController;
use App\Http\Controllers\AnnouncementController;

Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('logout', [App\Http\Controllers\AuthController::class, 'logout']);
    Route::get('me', [App\Http\Controllers\AuthController::class, 'me']);
    
    // Dashboard
    Route::get('dashboard/admin', [App\Http\Controllers\DashboardController::class, 'adminSummary']);
    Route::get('dashboard/employee', [App\Http\Controllers\DashboardController::class, 'employeeSummary']);
    
    // Master Data
    Route::apiResource('divisions', DivisionController::class);
    Route::apiResource('positions', PositionController::class);
    Route::apiResource('shifts', ShiftController::class);
    Route::apiResource('agendas', AgendaController::class);
    Route::apiResource('rosters', RosterController::class)->only(['index', 'store']);
    
    // Employee Management
    Route::apiResource('users', UserController::class);

    // Attendance
    Route::get('attendance', [App\Http\Controllers\AttendanceController::class, 'index']);
    Route::get('attendance/today', [App\Http\Controllers\AttendanceController::class, 'today']);
    Route::post('attendance/check-in', [App\Http\Controllers\AttendanceController::class, 'checkIn']);
    Route::post('attendance/check-out', [App\Http\Controllers\AttendanceController::class, 'checkOut']);

    // Leave Requests
    Route::get('leave-requests', [App\Http\Controllers\LeaveRequestController::class, 'index']);
    Route::post('leave-requests', [App\Http\Controllers\LeaveRequestController::class, 'store']);
    Route::put('leave-requests/{leaveRequest}/status', [App\Http\Controllers\LeaveRequestController::class, 'updateStatus']);
    Route::delete('leave-requests/{leaveRequest}', [App\Http\Controllers\LeaveRequestController::class, 'destroy']);

    // Overtime Requests
    Route::get('overtime-requests', [App\Http\Controllers\OvertimeRequestController::class, 'index']);
    Route::post('overtime-requests', [App\Http\Controllers\OvertimeRequestController::class, 'store']);
    Route::put('overtime-requests/{overtimeRequest}/status', [App\Http\Controllers\OvertimeRequestController::class, 'updateStatus']);
    Route::delete('overtime-requests/{overtimeRequest}', [App\Http\Controllers\OvertimeRequestController::class, 'destroy']);

    // Announcements
    Route::apiResource('announcements', AnnouncementController::class);

    // Calendar Events
    Route::apiResource('calendar-events', CalendarEventController::class);

    // Reports
    Route::get('reports/export', [App\Http\Controllers\ReportController::class, 'export']);

    // Settings
    Route::get('settings', [App\Http\Controllers\SettingController::class, 'show']);
    Route::post('settings', [App\Http\Controllers\SettingController::class, 'update']);
});
