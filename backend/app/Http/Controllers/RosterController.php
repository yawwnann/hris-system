<?php

namespace App\Http\Controllers;

use App\Models\Roster;
use Illuminate\Http\Request;

class RosterController extends Controller
{
    public function index(Request $request)
    {
        $query = Roster::with(['user', 'shift']);
        if ($request->has('month') && $request->has('year')) {
            $query->whereMonth('date', $request->month)
                  ->whereYear('date', $request->year);
        }
        return response()->json($query->get());
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'date' => 'required|date',
            'user_ids' => 'required|array',
            'user_ids.*' => 'exists:users,id',
            'shift_id' => 'required|exists:shifts,id'
        ]);

        $rosters = [];
        foreach ($validated['user_ids'] as $userId) {
            $roster = Roster::updateOrCreate(
                ['user_id' => $userId, 'date' => $validated['date']],
                ['shift_id' => $validated['shift_id']]
            );
            $rosters[] = $roster;
        }

        return response()->json(['message' => 'Rosters updated', 'data' => $rosters], 200);
    }
}
