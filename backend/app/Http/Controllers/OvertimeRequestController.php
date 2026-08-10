<?php

namespace App\Http\Controllers;

use App\Models\OvertimeRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OvertimeRequestController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        
        $query = OvertimeRequest::with('user');
        
        if ($user->role !== 'admin') {
            $query->where('user_id', $user->id);
        }

        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where(function($q) use ($search) {
                $q->whereHas('user', function ($q2) use ($search) {
                    $q2->where('name', 'like', "%{$search}%");
                })->orWhere('reason', 'like', "%{$search}%")
                  ->orWhere('status', 'like', "%{$search}%");
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

    public function destroy(OvertimeRequest $overtimeRequest)
    {
        if (Auth::user()->role !== 'admin' && Auth::id() !== $overtimeRequest->user_id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
        
        if ($overtimeRequest->status !== 'pending') {
            return response()->json(['message' => 'Only pending requests can be cancelled.'], 400);
        }

        $overtimeRequest->delete();

        return response()->json(['message' => 'Request successfully cancelled.']);
    }
}
