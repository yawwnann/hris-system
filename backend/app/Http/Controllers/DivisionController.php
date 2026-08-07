<?php

namespace App\Http\Controllers;

use App\Models\Division;
use Illuminate\Http\Request;

class DivisionController extends Controller
{
    public function index()
    {
        return response()->json(Division::withCount('users')->get());
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
