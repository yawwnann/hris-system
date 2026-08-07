<?php

namespace App\Http\Controllers;

use App\Models\Shift;
use Illuminate\Http\Request;

class ShiftController extends Controller
{
    public function index(Request $request)
    {
        $query = Shift::withCount('users');

        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where('name', 'like', "%{$search}%");
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
            'name' => 'required|string|max:255',
            'time_in' => 'required|date_format:H:i',
            'time_out' => 'required|date_format:H:i',
            'tolerance' => 'integer',
            'break_time' => 'integer',
        ]);
        $shift = Shift::create($validated);
        return response()->json($shift, 201);
    }

    public function show(Shift $shift)
    {
        return response()->json($shift->load('users'));
    }

    public function update(Request $request, Shift $shift)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'time_in' => 'required|date_format:H:i',
            'time_out' => 'required|date_format:H:i',
            'tolerance' => 'integer',
            'break_time' => 'integer',
        ]);
        $shift->update($validated);
        return response()->json($shift);
    }

    public function destroy(Shift $shift)
    {
        $shift->delete();
        return response()->json(null, 204);
    }
}
