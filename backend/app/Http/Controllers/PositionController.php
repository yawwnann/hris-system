<?php

namespace App\Http\Controllers;

use App\Models\Position;
use Illuminate\Http\Request;

class PositionController extends Controller
{
    public function index(Request $request)
    {
        $query = Position::withCount('users');

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
            'level' => 'required|integer',
        ]);
        $position = Position::create($validated);
        return response()->json($position, 201);
    }

    public function show(Position $position)
    {
        return response()->json($position->load('users'));
    }

    public function update(Request $request, Position $position)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'level' => 'required|integer',
        ]);
        $position->update($validated);
        return response()->json($position);
    }

    public function destroy(Position $position)
    {
        $position->delete();
        return response()->json(null, 204);
    }
}
