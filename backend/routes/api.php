<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DivisionController;
use App\Http\Controllers\PositionController;
use App\Http\Controllers\ShiftController;
use App\Http\Controllers\UserController;

Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);
    
    // Dashboard
    Route::get('dashboard/admin', [App\Http\Controllers\DashboardController::class, 'adminSummary']);
    Route::get('dashboard/employee', [App\Http\Controllers\DashboardController::class, 'employeeSummary']);
    
    // Master Data
    Route::apiResource('divisions', DivisionController::class);
    Route::apiResource('positions', PositionController::class);
    Route::apiResource('shifts', ShiftController::class);
    
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

    // Overtime Requests
    Route::get('overtime-requests', [App\Http\Controllers\OvertimeRequestController::class, 'index']);
    Route::post('overtime-requests', [App\Http\Controllers\OvertimeRequestController::class, 'store']);
    Route::put('overtime-requests/{overtimeRequest}/status', [App\Http\Controllers\OvertimeRequestController::class, 'updateStatus']);

    // Settings
    Route::get('settings', [App\Http\Controllers\SettingController::class, 'show']);
    Route::post('settings', [App\Http\Controllers\SettingController::class, 'update']);
});
