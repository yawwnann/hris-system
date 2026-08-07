<?php

namespace App\Http\Controllers;

use App\Models\Division;
use Illuminate\Http\Request;

class DivisionController extends Controller
{
    public function index(Request $request)
    {
        $query = Division::withCount('users');

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
        $validated = $request->validate(['name' => 'required|string|max:255']);
        $division = Division::create($validated);
        return response()->json($division, 201);
    }

    public function show(Division $division)
    {
        return response()->json($division->load('users'));
    }

    public function update(Request $request, Division $division)
    {
        $validated = $request->validate(['name' => 'required|string|max:255']);
        $division->update($validated);
        return response()->json($division);
    }

    public function destroy(Division $division)
    {
        $division->delete();
        return response()->json(null, 204);
    }
}
