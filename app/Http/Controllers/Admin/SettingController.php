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

            // Happy hour
            'happy_hour_enabled' => 'nullable|in:on,1,true',
            'happy_hour_discount_percent' => 'nullable|integer|min:1|max:100',
            'happy_hour_start' => 'nullable|date_format:H:i',
            'happy_hour_end' => 'nullable|date_format:H:i',
        ]);

        // Checkbox tidak terkirim saat tidak dicentang → simpan '0'.
        // Form lama (tanpa field happy hour sama sekali) tidak menyentuh key ini.
        $hasHappyHourInput = $request->exists('happy_hour_enabled')
            || $request->exists('happy_hour_discount_percent')
            || $request->exists('happy_hour_start')
            || $request->exists('happy_hour_end');

        if ($hasHappyHourInput) {
            $validated['happy_hour_enabled'] = isset($validated['happy_hour_enabled']) ? '1' : '0';
        }

        foreach ($validated as $key => $value) {
            Setting::updateOrCreate(['key' => $key], ['value' => (string) $value]);
        }

        return redirect()->route('admin.pengaturan')->with('success', 'Pengaturan berhasil diperbarui.');
    }
}
