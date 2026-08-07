<?php

namespace App\Http\Controllers;

use App\Models\Shift;
use Illuminate\Http\Request;

class ShiftController extends Controller
{
    public function index()
    {
        return response()->json(Shift::withCount('users')->get());
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
