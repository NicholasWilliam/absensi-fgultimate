<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function edit()
    {
        $lat = Setting::get('rental_latitude');
        $lng = Setting::get('rental_longitude');
        $radius = Setting::get('rental_radius_meters');

        return view('admin.settings', compact('lat', 'lng', 'radius'));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'rental_latitude' => ['required', 'numeric'],
            'rental_longitude' => ['required', 'numeric'],
            'rental_radius_meters' => ['required', 'integer', 'min:1'],
        ]);

        foreach ($validated as $key => $value) {
            Setting::set($key, $value);
        }

        return back()->with('success', true);
    }
}
