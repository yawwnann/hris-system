<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SettingController extends Controller
{
    public function show()
    {
        return response()->json(Setting::first() ?? new Setting());
    }

    public function update(Request $request)
    {
        if (Auth::user()->role !== 'admin') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'company_name' => 'nullable|string',
            'office_location' => 'nullable|string',
            'office_lat' => 'nullable|string',
            'office_long' => 'nullable|string',
            'attendance_radius' => 'nullable|integer',
            'default_time_in' => 'nullable|date_format:H:i:s',
            'default_time_out' => 'nullable|date_format:H:i:s',
            'timezone' => 'nullable|string',
        ]);

        $setting = Setting::first();
        if ($setting) {
            $setting->update($validated);
        } else {
            $setting = Setting::create($validated);
        }

        return response()->json($setting);
    }
}
