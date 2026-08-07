<?php

namespace App\Http\Controllers;

use App\Models\LeaveRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LeaveRequestController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        if ($user->role === 'admin') {
            return response()->json(LeaveRequest::with('user')->orderBy('created_at', 'desc')->get());
        }
        return response()->json(LeaveRequest::where('user_id', $user->id)->orderBy('created_at', 'desc')->get());
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'type' => 'required|string|in:annual,sick,permission',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'reason' => 'required|string',
        ]);

        $leave = LeaveRequest::create(array_merge($validated, [
            'user_id' => Auth::id(),
            'status' => 'pending'
        ]));

        return response()->json($leave, 201);
    }

    public function updateStatus(Request $request, LeaveRequest $leaveRequest)
    {
        if (Auth::user()->role !== 'admin') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'status' => 'required|in:approved,rejected',
            'admin_note' => 'nullable|string',
        ]);

        $leaveRequest->update($validated);

        return response()->json($leaveRequest);
    }
}
