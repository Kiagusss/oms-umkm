<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Supplier;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
    public function index()
    {
        $suppliers = Supplier::withCount('purchases')->orderBy('name')->paginate(15);
        return view('admin.supplier.index', compact('suppliers'));
    }

    public function create()
    {
        return view('admin.supplier.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'           => 'required|string|max:255',
            'contact_person' => 'nullable|string|max:255',
            'phone'          => 'nullable|string|max:50',
            'email'          => 'nullable|email|max:255',
            'address'        => 'nullable|string|max:500',
            'notes'          => 'nullable|string',
            'is_active'      => 'sometimes|boolean',
        ]);

        $validated['is_active'] = $request->has('is_active') ? $request->boolean('is_active') : true;

        $supplier = Supplier::create($validated);

        AuditLog::log(auth()->user(), 'supplier.create', "Menambahkan supplier: {$supplier->name}");

        return redirect()->route('admin.supplier.index')->with('success', "Supplier {$supplier->name} berhasil ditambahkan.");
    }

    public function edit(Supplier $supplier)
    {
        return view('admin.supplier.edit', compact('supplier'));
    }

    public function update(Request $request, Supplier $supplier)
    {
        $validated = $request->validate([
            'name'           => 'required|string|max:255',
            'contact_person' => 'nullable|string|max:255',
            'phone'          => 'nullable|string|max:50',
            'email'          => 'nullable|email|max:255',
            'address'        => 'nullable|string|max:500',
            'notes'          => 'nullable|string',
            'is_active'      => 'sometimes|boolean',
        ]);

        $validated['is_active'] = $request->has('is_active') ? $request->boolean('is_active') : true;

        $supplier->update($validated);

        AuditLog::log(auth()->user(), 'supplier.update', "Memperbarui supplier: {$supplier->name}");

        return redirect()->route('admin.supplier.index')->with('success', "Supplier {$supplier->name} berhasil diperbarui.");
    }

    public function destroy(Supplier $supplier)
    {
        if ($supplier->purchases()->exists()) {
            $supplier->update(['is_active' => false]);
            return redirect()->route('admin.supplier.index')->with('success', "Supplier dinonaktifkan karena memiliki riwayat pembelian.");
        }

        $name = $supplier->name;
        $supplier->delete();

        AuditLog::log(auth()->user(), 'supplier.delete', "Menghapus supplier: {$name}");

        return redirect()->route('admin.supplier.index')->with('success', "Supplier {$name} berhasil dihapus.");
    }
}
