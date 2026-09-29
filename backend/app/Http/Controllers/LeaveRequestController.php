<?php

namespace App\Http\Controllers;

use App\Models\LeaveRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LeaveRequestController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        
        $query = LeaveRequest::with('user');
        
        if ($user->role !== 'admin') {
            $query->where('user_id', $user->id);
        }

        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where(function($q) use ($search) {
                $q->whereHas('user', function ($q2) use ($search) {
                    $q2->where('name', 'like', "%{$search}%");
                })->orWhere('reason', 'like', "%{$search}%");
            });
        }

        if ($request->has('status') && $request->input('status') !== 'all') {
            $query->where('status', $request->input('status'));
        }

        if ($request->has('type') && $request->input('type') !== 'all') {
            $query->where('type', $request->input('type'));
        }

        $sortBy = $request->input('sort_by', 'created_at');
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
            'type' => 'required|string|in:annual,sick,permission,maternity',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'reason' => 'required|string',
            'attachment' => 'nullable|file|mimes:jpeg,png,jpg,pdf|max:2048',
        ]);

        if ($request->hasFile('attachment')) {
            $path = $request->file('attachment')->store('leave_attachments', 'public');
            $validated['attachment'] = $path;
        }

        if ($validated['type'] === 'annual') {
            $start = \Carbon\Carbon::parse($validated['start_date']);
            $end = \Carbon\Carbon::parse($validated['end_date']);
            
            $days = 0;
            while ($start->lte($end)) {
                if (!$start->isWeekend()) {
                    $days++;
                }
                $start->addDay();
            }

            if (Auth::user()->leave_quota < $days) {
                return response()->json(['message' => 'Your remaining leave quota is insufficient (Remaining: ' . Auth::user()->leave_quota . ' days)'], 400);
            }
        }

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

        if ($leaveRequest->type === 'annual') {
            $start = \Carbon\Carbon::parse($leaveRequest->start_date);
            $end = \Carbon\Carbon::parse($leaveRequest->end_date);
            $days = 0;
            while ($start->lte($end)) {
                if (!$start->isWeekend()) {
                    $days++;
                }
                $start->addDay();
            }
            
            if ($validated['status'] === 'approved' && $leaveRequest->status !== 'approved') {
                if ($leaveRequest->user->leave_quota < $days) {
                    return response()->json(['message' => 'Employee leave quota is insufficient (Remaining: ' . $leaveRequest->user->leave_quota . ' days)'], 400);
                }
                $leaveRequest->user->decrement('leave_quota', $days);
            } elseif ($validated['status'] !== 'approved' && $leaveRequest->status === 'approved') {
                $leaveRequest->user->increment('leave_quota', $days);
            }
        }

        $leaveRequest->update($validated);

        return response()->json($leaveRequest);
    }

    public function destroy(LeaveRequest $leaveRequest)
    {
        if (Auth::user()->role !== 'admin' && Auth::id() !== $leaveRequest->user_id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
        
        if ($leaveRequest->status !== 'pending') {
            return response()->json(['message' => 'Only pending requests can be cancelled.'], 400);
        }

        $leaveRequest->delete();

        return response()->json(['message' => 'Request successfully cancelled.']);
    }
}
