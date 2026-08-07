<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::with(['division', 'position', 'shift']);

        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('nik', 'like', "%{$search}%");
            });
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
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6',
            'nik' => 'nullable|string|unique:users',
            'phone' => 'nullable|string',
            'address' => 'nullable|string',
            'dob' => 'nullable|date',
            'gender' => 'nullable|in:male,female',
            'division_id' => 'nullable|exists:divisions,id',
            'position_id' => 'nullable|exists:positions,id',
            'shift_id' => 'nullable|exists:shifts,id',
            'status' => 'required|in:active,inactive',
            'join_date' => 'nullable|date',
            'role' => 'required|in:admin,employee',
        ]);

        $validated['password'] = Hash::make($validated['password']);

        $user = User::create($validated);
        return response()->json($user->load(['division', 'position', 'shift']), 201);
    }

    public function show(User $user)
    {
        return response()->json($user->load(['division', 'position', 'shift']));
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'email' => ['sometimes', 'required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'password' => 'nullable|string|min:6',
            'nik' => ['nullable', 'string', Rule::unique('users')->ignore($user->id)],
            'phone' => 'nullable|string',
            'address' => 'nullable|string',
            'dob' => 'nullable|date',
            'gender' => 'nullable|in:male,female',
            'division_id' => 'nullable|exists:divisions,id',
            'position_id' => 'nullable|exists:positions,id',
            'shift_id' => 'nullable|exists:shifts,id',
            'status' => 'sometimes|required|in:active,inactive',
            'join_date' => 'nullable|date',
            'role' => 'sometimes|required|in:admin,employee',
        ]);

        if (isset($validated['password']) && !empty($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        $user->update($validated);
        return response()->json($user->load(['division', 'position', 'shift']));
    }

    public function destroy(User $user)
    {
        $user->delete();
        return response()->json(null, 204);
    }
}
