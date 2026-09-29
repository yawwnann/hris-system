<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AnnouncementController extends Controller
{
    public function index(Request $request)
    {
        $query = Announcement::query();

        if (Auth::user()->role !== 'admin') {
            $user = Auth::user();
            $query->where('status', 'published')
                  ->where(function ($q) {
                      $q->whereNull('publish_date')
                        ->orWhere('publish_date', '<=', now());
                  })
                  ->where(function($q) use ($user) {
                      $q->whereNull('division_id')
                        ->orWhere('division_id', $user->division_id);
                  });
        }

        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('content', 'like', "%{$search}%");
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
        if (Auth::user()->role !== 'admin') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'status' => 'required|in:draft,published',
            'publish_date' => 'nullable|date',
            'category' => 'nullable|string|max:255',
            'division_id' => 'nullable|exists:divisions,id',
        ]);

        $announcement = Announcement::create($validated);

        return response()->json($announcement, 201);
    }

    public function show(Announcement $announcement)
    {
        if (Auth::user()->role !== 'admin') {
            if ($announcement->status !== 'published' || ($announcement->publish_date && $announcement->publish_date > now())) {
                return response()->json(['message' => 'Unauthorized'], 403);
            }
        }
        return response()->json($announcement);
    }

    public function update(Request $request, Announcement $announcement)
    {
        if (Auth::user()->role !== 'admin') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'status' => 'required|in:draft,published',
            'publish_date' => 'nullable|date',
            'category' => 'nullable|string|max:255',
            'division_id' => 'nullable|exists:divisions,id',
        ]);

        $announcement->update($validated);

        return response()->json($announcement);
    }

    public function destroy(Announcement $announcement)
    {
        if (Auth::user()->role !== 'admin') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $announcement->delete();

        return response()->json(null, 204);
    }
}
