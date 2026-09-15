<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Branch;
use Illuminate\Http\Request;

class BranchController extends Controller
{
    public function index()
    {
        $branches = Branch::withCount(['orders', 'inventories', 'users'])->paginate(15);
        $selectedBranchId = session('selected_branch_id');

        return view('admin.cabang.index', compact('branches', 'selectedBranchId'));
    }

    public function create()
    {
        return view('admin.cabang.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'      => 'required|string|max:255',
            'code'      => 'required|string|max:20|unique:branches,code',
            'phone'     => 'nullable|string|max:50',
            'address'   => 'nullable|string|max:500',
            'city'      => 'nullable|string|max:100',
            'is_main'   => 'sometimes|boolean',
            'is_active' => 'sometimes|boolean',
        ]);

        $validated['is_main'] = $request->boolean('is_main');
        $validated['is_active'] = $request->has('is_active') ? $request->boolean('is_active') : true;

        if ($validated['is_main']) {
            Branch::where('is_main', true)->update(['is_main' => false]);
        }

        $branch = Branch::create($validated);

        AuditLog::log(auth()->user(), 'branch.create', "Membuat cabang baru: {$branch->name} ({$branch->code})");

        return redirect()->route('admin.cabang.index')->with('success', "Cabang {$branch->name} berhasil ditambahkan.");
    }

    public function edit(Branch $cabang)
    {
        return view('admin.cabang.edit', ['branch' => $cabang]);
    }

    public function update(Request $request, Branch $cabang)
    {
        $validated = $request->validate([
            'name'      => 'required|string|max:255',
            'code'      => 'required|string|max:20|unique:branches,code,' . $cabang->id,
            'phone'     => 'nullable|string|max:50',
            'address'   => 'nullable|string|max:500',
            'city'      => 'nullable|string|max:100',
            'is_main'   => 'sometimes|boolean',
            'is_active' => 'sometimes|boolean',
        ]);

        $validated['is_main'] = $request->boolean('is_main');
        $validated['is_active'] = $request->has('is_active') ? $request->boolean('is_active') : true;

        if ($validated['is_main'] && !$cabang->is_main) {
            Branch::where('id', '!=', $cabang->id)->update(['is_main' => false]);
        }

        $cabang->update($validated);

        AuditLog::log(auth()->user(), 'branch.update', "Memperbarui cabang: {$cabang->name}");

        return redirect()->route('admin.cabang.index')->with('success', "Cabang {$cabang->name} berhasil diperbarui.");
    }

    public function destroy(Branch $cabang)
    {
        if ($cabang->is_main) {
            return back()->with('error', 'Cabang utama tidak boleh dihapus.');
        }

        if ($cabang->orders()->exists()) {
            // Soft-deactivate if has orders
            $cabang->update(['is_active' => false]);
            return redirect()->route('admin.cabang.index')->with('success', "Cabang dinonaktifkan karena memiliki riwayat transaksi.");
        }

        $name = $cabang->name;
        $cabang->delete();

        AuditLog::log(auth()->user(), 'branch.delete', "Menghapus cabang: {$name}");

        return redirect()->route('admin.cabang.index')->with('success', "Cabang {$name} berhasil dihapus.");
    }

    public function switchBranch(Request $request)
    {
        $validated = $request->validate([
            'branch_id' => 'nullable|integer|exists:branches,id',
        ]);

        $branchId = $validated['branch_id'] ?: null;
        session(['selected_branch_id' => $branchId]);

        $branchName = $branchId ? (Branch::find($branchId)?->name ?? 'Cabang Terpilih') : 'Semua Cabang';

        return back()->with('success', "Beralih ke {$branchName}.");
    }
}
