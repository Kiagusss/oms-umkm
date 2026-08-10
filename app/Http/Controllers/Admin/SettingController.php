<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function edit()
    {
        return view('admin.pengaturan', [
            'settings' => Setting::pluck('value', 'key')->toArray(),
        ]);
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'logo' => 'nullable|string|max:255',
            'site_name' => 'required|string|max:255',
            'address' => 'required|string',
            'whatsapp' => 'required|string|max:255',
            'instagram' => 'nullable|string|max:255',
            'facebook' => 'nullable|string|max:255',
            'tiktok' => 'nullable|string|max:255',
            'google_maps_embed' => 'nullable|string',
            'email' => 'required|email',
            'operating_hours' => 'required|string|max:255',
            'footer_text' => 'required|string|max:255',
            'about_us' => 'required|string',
        ]);

        foreach ($validated as $key => $value) {
            Setting::updateOrCreate(['key' => $key], ['value' => $value]);
        }

        return redirect()->route('admin.pengaturan')->with('success', 'Pengaturan berhasil diperbarui.');
    }
}
