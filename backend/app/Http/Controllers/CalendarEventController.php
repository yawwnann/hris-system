<?php

namespace App\Http\Controllers;

use App\Models\CalendarEvent;
use Illuminate\Http\Request;

class CalendarEventController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = CalendarEvent::with(['creator:id,name', 'users:id,name', 'divisions:id,name']);

        if ($request->has('start') && $request->has('end')) {
            $query->where(function($q) use ($request) {
                $q->whereBetween('start_datetime', [$request->start, $request->end])
                  ->orWhereBetween('end_datetime', [$request->start, $request->end]);
            });
        }
        
        if ($request->has('categories')) {
            $categories = explode(',', $request->categories);
            $query->whereIn('category', $categories);
        }

        $events = $query->orderBy('start_datetime', 'asc')->get();

        return response()->json([
            'status' => 'success',
            'data' => $events
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        if ($request->user()->role !== 'admin') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category' => 'required|string',
            'start_datetime' => 'required|date',
            'end_datetime' => 'required|date|after_or_equal:start_datetime',
            'location' => 'nullable|string',
            'color' => 'nullable|string',
            'is_working_day' => 'boolean',
            'is_all_day' => 'boolean',
            'is_recurring' => 'boolean',
            'recurrence_rule' => 'nullable|string',
            'visibility' => 'nullable|string',
            'user_ids' => 'nullable|array',
            'user_ids.*' => 'exists:users,id',
            'division_ids' => 'nullable|array',
            'division_ids.*' => 'exists:divisions,id',
        ]);

        $validated['created_by'] = $request->user()->id;

        $event = CalendarEvent::create($validated);

        if ($request->has('user_ids')) {
            $event->users()->attach($request->user_ids);
        }
        if ($request->has('division_ids')) {
            $event->divisions()->attach($request->division_ids);
        }

        $event->load(['users:id,name', 'divisions:id,name']);

        return response()->json([
            'status' => 'success',
            'message' => 'Event successfully added',
            'data' => $event
        ], 201);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        if ($request->user()->role !== 'admin') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $event = CalendarEvent::findOrFail($id);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category' => 'required|string',
            'start_datetime' => 'required|date',
            'end_datetime' => 'required|date|after_or_equal:start_datetime',
            'location' => 'nullable|string',
            'color' => 'nullable|string',
            'is_working_day' => 'boolean',
            'is_all_day' => 'boolean',
            'is_recurring' => 'boolean',
            'recurrence_rule' => 'nullable|string',
            'visibility' => 'nullable|string',
            'user_ids' => 'nullable|array',
            'user_ids.*' => 'exists:users,id',
            'division_ids' => 'nullable|array',
            'division_ids.*' => 'exists:divisions,id',
        ]);

        $event->update($validated);

        if ($request->has('user_ids')) {
            $event->users()->sync($request->user_ids);
        }
        if ($request->has('division_ids')) {
            $event->divisions()->sync($request->division_ids);
        }

        $event->load(['users:id,name', 'divisions:id,name']);

        return response()->json([
            'status' => 'success',
            'message' => 'Event successfully updated',
            'data' => $event
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, string $id)
    {
        if ($request->user()->role !== 'admin') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $event = CalendarEvent::findOrFail($id);
        $event->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Event successfully deleted'
        ]);
    }
}
