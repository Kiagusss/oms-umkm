<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Branch;
use App\Models\BranchInventory;
use App\Models\InventoryItem;
use App\Models\Product;
use App\Models\StockMovement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InventoryController extends Controller
{
    public function index(Request $request)
    {
        $selectedBranchId = session('selected_branch_id');
        $query = InventoryItem::with(['branchInventories.branch']);

        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('sku', 'like', "%{$search}%");
            });
        }

        if ($type = $request->get('type')) {
            $query->where('item_type', $type);
        }

        $items = $query->orderBy('name')->paginate(20);
        $branches = Branch::where('is_active', true)->get();

        // Low stock count across branches
        $lowStockQuery = BranchInventory::lowStock();
        if ($selectedBranchId) {
            $lowStockQuery->where('branch_id', $selectedBranchId);
        }
        $lowStockCount = $lowStockQuery->count();

        return view('admin.inventori.index', compact('items', 'branches', 'selectedBranchId', 'lowStockCount'));
    }

    public function create()
    {
        $branches = Branch::where('is_active', true)->get();
        return view('admin.inventori.create', compact('branches'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'          => 'required|string|max:255',
            'sku'           => 'required|string|max:50|unique:inventory_items,sku',
            'item_type'     => 'required|in:raw_material,packaging,finished_good,semi_finished',
            'unit'          => 'required|string|max:20',
            'cost_per_unit' => 'required|numeric|min:0',
            'minimum_stock' => 'required|numeric|min:0',
            'notes'         => 'nullable|string',
            'initial_stocks'=> 'nullable|array',
            'initial_stocks.*' => 'nullable|numeric|min:0',
        ]);

        $item = DB::transaction(function () use ($validated, $request) {
            $item = InventoryItem::create([
                'name'          => $validated['name'],
                'sku'           => $validated['sku'],
                'item_type'     => $validated['item_type'],
                'unit'          => $validated['unit'],
                'cost_per_unit' => $validated['cost_per_unit'],
                'minimum_stock' => $validated['minimum_stock'],
                'notes'         => $validated['notes'] ?? null,
            ]);

            if (!empty($validated['initial_stocks'])) {
                foreach ($validated['initial_stocks'] as $branchId => $qty) {
                    $qty = (float) $qty;
                    if ($qty > 0) {
                        BranchInventory::create([
                            'branch_id'         => $branchId,
                            'inventory_item_id' => $item->id,
                            'quantity'          => $qty,
                            'minimum_stock'     => $validated['minimum_stock'],
                        ]);

                        StockMovement::create([
                            'branch_id'         => $branchId,
                            'inventory_item_id' => $item->id,
                            'type'              => 'initial',
                            'quantity'          => $qty,
                            'balance_after'     => $qty,
                            'user_id'           => auth()->id(),
                            'notes'             => 'Stok awal input bahan',
                        ]);
                    }
                }
            }

            return $item;
        });

        AuditLog::log(auth()->user(), 'inventory.create', "Membuat item inventori: {$item->name} ({$item->sku})");

        return redirect()->route('admin.inventori.index')->with('success', "Item {$item->name} berhasil ditambahkan.");
    }

    public function edit(InventoryItem $inventori)
    {
        return view('admin.inventori.edit', ['item' => $inventori]);
    }

    public function update(Request $request, InventoryItem $inventori)
    {
        $validated = $request->validate([
            'name'          => 'required|string|max:255',
            'sku'           => 'required|string|max:50|unique:inventory_items,sku,' . $inventori->id,
            'item_type'     => 'required|in:raw_material,packaging,finished_good,semi_finished',
            'unit'          => 'required|string|max:20',
            'cost_per_unit' => 'required|numeric|min:0',
            'minimum_stock' => 'required|numeric|min:0',
            'notes'         => 'nullable|string',
        ]);

        $inventori->update($validated);

        // Update minimum stock on branch records
        BranchInventory::where('inventory_item_id', $inventori->id)
            ->update(['minimum_stock' => $validated['minimum_stock']]);

        AuditLog::log(auth()->user(), 'inventory.update', "Memperbarui item inventori: {$inventori->name}");

        return redirect()->route('admin.inventori.index')->with('success', "Item {$inventori->name} berhasil diperbarui.");
    }

    public function destroy(InventoryItem $inventori)
    {
        $name = $inventori->name;
        $inventori->delete();

        AuditLog::log(auth()->user(), 'inventory.delete', "Menghapus item inventori: {$name}");

        return redirect()->route('admin.inventori.index')->with('success', "Item {$name} berhasil dihapus.");
    }

    /**
     * Stock opname / adjustment.
     */
    public function adjust(Request $request)
    {
        $validated = $request->validate([
            'branch_id'         => 'required|exists:branches,id',
            'inventory_item_id' => 'required|exists:inventory_items,id',
            'actual_quantity'   => 'required|numeric|min:0',
            'type'              => 'required|in:adjustment,damaged,expired',
            'reason'            => 'required|string|max:500',
        ]);

        DB::transaction(function () use ($validated) {
            $inv = BranchInventory::firstOrCreate(
                [
                    'branch_id'         => $validated['branch_id'],
                    'inventory_item_id' => $validated['inventory_item_id'],
                ],
                ['quantity' => 0, 'minimum_stock' => 0]
            );

            $diff = (float) $validated['actual_quantity'] - (float) $inv->quantity;
            $inv->update(['quantity' => $validated['actual_quantity']]);

            StockMovement::create([
                'branch_id'         => $validated['branch_id'],
                'inventory_item_id' => $validated['inventory_item_id'],
                'type'              => $validated['type'],
                'quantity'          => $diff,
                'balance_after'     => $validated['actual_quantity'],
                'user_id'           => auth()->id(),
                'notes'             => "Stock Opname / {$validated['type']}: " . $validated['reason'],
            ]);
        });

        return back()->with('success', 'Penyesuaian stok berhasil disimpan.');
    }

    /**
     * Ledger riwayat pergerakan stok.
     */
    public function movements(Request $request)
    {
        $selectedBranchId = session('selected_branch_id');
        $query = StockMovement::with(['branch', 'inventoryItem', 'product', 'productVariant', 'user']);

        if ($branchId = ($request->get('branch_id') ?: $selectedBranchId)) {
            $query->where('branch_id', $branchId);
        }

        if ($type = $request->get('type')) {
            $query->where('type', $type);
        }

        if ($startDate = $request->get('start_date')) {
            $query->whereDate('created_at', '>=', $startDate);
        }
        if ($endDate = $request->get('end_date')) {
            $query->whereDate('created_at', '<=', $endDate);
        }

        $movements = $query->latest('created_at')->paginate(25);
        $branches = Branch::where('is_active', true)->get();

        return view('admin.inventori.movements', compact('movements', 'branches', 'selectedBranchId'));
    }
}
