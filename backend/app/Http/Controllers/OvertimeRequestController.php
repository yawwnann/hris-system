<?php

namespace App\Http\Controllers;

use App\Models\OvertimeRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OvertimeRequestController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        if ($user->role === 'admin') {
            return response()->json(OvertimeRequest::with('user')->orderBy('date', 'desc')->get());
        }
        return response()->json(OvertimeRequest::where('user_id', $user->id)->orderBy('date', 'desc')->get());
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'date' => 'required|date',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'reason' => 'required|string',
        ]);

        $start = \Carbon\Carbon::parse($validated['start_time']);
        $end = \Carbon\Carbon::parse($validated['end_time']);
        $duration = $start->diffInMinutes($end) / 60;

        $overtime = OvertimeRequest::create(array_merge($validated, [
            'user_id' => Auth::id(),
            'total_duration' => round($duration, 2),
            'status' => 'pending'
        ]));

        return response()->json($overtime, 201);
    }

    public function updateStatus(Request $request, OvertimeRequest $overtimeRequest)
    {
        if (Auth::user()->role !== 'admin') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'status' => 'required|in:approved,rejected',
            'admin_note' => 'nullable|string',
        ]);

        $overtimeRequest->update($validated);

        return response()->json($overtimeRequest);
    }
}
