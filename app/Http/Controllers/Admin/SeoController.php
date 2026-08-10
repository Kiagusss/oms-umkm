<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Seo;
use Illuminate\Http\Request;

class SeoController extends Controller
{
    public function edit()
    {
        return view('admin.seo', [
            'seo' => Seo::firstOrNew(['id' => 1]),
        ]);
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'site_name' => 'required|string|max:255',
            'site_url' => 'nullable|string|max:255',
            'default_title' => 'required|string|max:255',
            'default_description' => 'required|string',
            'favicon' => 'nullable|string|max:255',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string',
            'keywords' => 'nullable|string',
            'og_image' => 'nullable|string|max:255',
            'canonical_url' => 'nullable|string|max:255',
            'robots' => 'nullable|string|max:255',
            'google_verification' => 'nullable|string|max:255',
            'schema_json_ld' => 'nullable|string',
        ]);

        Seo::updateOrCreate(['id' => 1], $validated);

        return redirect()->route('admin.seo')->with('success', 'Optimasi SEO berhasil diperbarui.');
    }
}
