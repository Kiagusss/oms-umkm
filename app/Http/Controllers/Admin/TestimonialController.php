<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Testimonial;
use Illuminate\Http\Request;

class TestimonialController extends Controller
{
    public function index()
    {
        $testimonials = Testimonial::orderBy('ord')->paginate(15);

        return view('admin.testimoni.index', compact('testimonials'));
    }

    public function create()
    {
        return view('admin.testimoni.create');
    }

    public function store(Request $request)
    {
        $validated = $this->validateTestimonial($request);

        Testimonial::create($validated);

        return redirect()->route('admin.testimoni.index')->with('success', 'Testimoni berhasil ditambahkan.');
    }

    public function show(Testimonial $testimoni)
    {
        return view('admin.testimoni.show', compact('testimoni'));
    }

    public function edit(Testimonial $testimoni)
    {
        return view('admin.testimoni.edit', compact('testimoni'));
    }

    public function update(Request $request, Testimonial $testimoni)
    {
        $validated = $this->validateTestimonial($request);

        $testimoni->update($validated);

        return redirect()->route('admin.testimoni.index')->with('success', 'Testimoni berhasil diperbarui.');
    }

    public function destroy(Testimonial $testimoni)
    {
        $testimoni->delete();

        return redirect()->route('admin.testimoni.index')->with('success', 'Testimoni berhasil dihapus.');
    }

    protected function validateTestimonial(Request $request): array
    {
        return $request->validate([
            'name' => 'required|string|max:255',
            'photo' => 'nullable|string|max:255',
            'kombinasi' => 'nullable|string|max:255',
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'required|string',
            'status' => 'required|in:active,inactive',
            'ord' => 'nullable|integer',
        ]);
    }
}
