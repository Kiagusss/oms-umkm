<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Package;
use Illuminate\Http\Request;

class PackageController extends Controller
{
    public function index()
    {
        $packages = Package::orderBy('ord')->paginate(15);

        return view('admin.paket.index', compact('packages'));
    }

    public function create()
    {
        return view('admin.paket.create');
    }

    public function store(Request $request)
    {
        $validated = $this->validatePackage($request);

        Package::create($validated);

        return redirect()->route('admin.paket.index')->with('success', 'Paket berhasil ditambahkan.');
    }

    public function show(Package $paket)
    {
        return view('admin.paket.show', compact('paket'));
    }

    public function edit(Package $paket)
    {
        return view('admin.paket.edit', compact('paket'));
    }

    public function update(Request $request, Package $paket)
    {
        $validated = $this->validatePackage($request);

        $paket->update($validated);

        return redirect()->route('admin.paket.index')->with('success', 'Paket berhasil diperbarui.');
    }

    public function destroy(Package $paket)
    {
        $paket->delete();

        return redirect()->route('admin.paket.index')->with('success', 'Paket berhasil dihapus.');
    }

    protected function validatePackage(Request $request): array
    {
        return $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'price' => 'required|integer|min:0',
            'original_price' => 'nullable|integer|min:0',
            'badge' => 'nullable|string|max:255',
            'is_featured' => 'sometimes|boolean',
            'items' => 'nullable|array',
            'items.*.quantity' => 'required_with:items|integer|min:1',
            'items.*.name' => 'required_with:items|string',
            'status' => 'required|in:active,inactive',
            'ord' => 'nullable|integer',
        ]);
    }
}
