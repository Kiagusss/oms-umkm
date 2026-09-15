<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Branch;
use App\Models\InventoryItem;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PurchaseController extends Controller
{
    public function index(Request $request)
    {
        $selectedBranchId = session('selected_branch_id');
        $query = Purchase::with(['branch', 'supplier', 'user']);

        if ($branchId = ($request->get('branch_id') ?: $selectedBranchId)) {
            $query->where('branch_id', $branchId);
        }

        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }

        if ($supplierId = $request->get('supplier_id')) {
            $query->where('supplier_id', $supplierId);
        }

        $purchases = $query->latest('date')->paginate(15);
        $branches = Branch::where('is_active', true)->get();
        $suppliers = Supplier::where('is_active', true)->get();

        return view('admin.pembelian.index', compact('purchases', 'branches', 'suppliers', 'selectedBranchId'));
    }

    public function create()
    {
        $branches = Branch::where('is_active', true)->get();
        $suppliers = Supplier::where('is_active', true)->get();
        $inventoryItems = InventoryItem::orderBy('name')->get();
        $selectedBranchId = session('selected_branch_id') ?? Branch::where('is_main', true)->value('id');

        return view('admin.pembelian.create', compact('branches', 'suppliers', 'inventoryItems', 'selectedBranchId'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'branch_id'   => 'required|exists:branches,id',
            'supplier_id' => 'required|exists:suppliers,id',
            'date'        => 'required|date',
            'notes'       => 'nullable|string',
            'items'       => 'required|array|min:1',
            'items.*.inventory_item_id' => 'required|exists:inventory_items,id',
            'items.*.quantity'   => 'required|numeric|min:0.01',
            'items.*.unit_price' => 'required|numeric|min:0',
        ]);

        $purchase = DB::transaction(function () use ($validated) {
            $totalAmount = 0;
            foreach ($validated['items'] as $item) {
                $totalAmount += $item['quantity'] * $item['unit_price'];
            }

            $purchase = Purchase::create([
                'purchase_number' => 'PO-' . date('Ymd') . '-' . strtoupper(Str::random(4)),
                'branch_id'       => $validated['branch_id'],
                'supplier_id'     => $validated['supplier_id'],
                'date'            => $validated['date'],
                'status'          => 'pending',
                'total_amount'    => $totalAmount,
                'notes'           => $validated['notes'] ?? null,
                'created_by'      => auth()->id(),
            ]);

            foreach ($validated['items'] as $item) {
                $subtotal = $item['quantity'] * $item['unit_price'];
                PurchaseItem::create([
                    'purchase_id'       => $purchase->id,
                    'inventory_item_id' => $item['inventory_item_id'],
                    'quantity'          => $item['quantity'],
                    'unit_price'        => $item['unit_price'],
                    'subtotal'          => $subtotal,
                ]);
            }

            return $purchase;
        });

        AuditLog::log(auth()->user(), 'purchase.create', "Membuat Purchase Order #{$purchase->purchase_number}");

        return redirect()->route('admin.pembelian.show', $purchase)->with('success', 'Purchase Order berhasil dibuat.');
    }

    public function show(Purchase $pembelian)
    {
        $pembelian->load(['branch', 'supplier', 'items.inventoryItem', 'user']);
        return view('admin.pembelian.show', ['purchase' => $pembelian]);
    }

    public function receive(Purchase $pembelian)
    {
        if (in_array($pembelian->status, ['received', 'completed'], true)) {
            return back()->with('error', 'Purchase Order sudah berstatus diterima sebelumnya.');
        }

        $pembelian->receive(auth()->id());

        AuditLog::log(auth()->user(), 'purchase.receive', "Menerima barang untuk PO #{$pembelian->purchase_number}. Stok & HPP terupdate otomatis.");

        return redirect()->route('admin.pembelian.show', $pembelian)->with('success', 'Barang berhasil diterima! Stok cabang bertambah dan HPP resep diperbarui otomatis.');
    }
}
