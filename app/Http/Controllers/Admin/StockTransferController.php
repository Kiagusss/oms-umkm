<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Branch;
use App\Models\InventoryItem;
use App\Models\StockTransfer;
use App\Models\StockTransferItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class StockTransferController extends Controller
{
    public function index(Request $request)
    {
        $selectedBranchId = session('selected_branch_id');
        $query = StockTransfer::with(['fromBranch', 'toBranch', 'user']);

        if ($branchId = ($request->get('branch_id') ?: $selectedBranchId)) {
            $query->where(function ($q) use ($branchId) {
                $q->where('from_branch_id', $branchId)
                  ->orWhere('to_branch_id', $branchId);
            });
        }

        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }

        $transfers = $query->latest('date')->paginate(15);
        $branches = Branch::where('is_active', true)->get();

        return view('admin.transfer.index', compact('transfers', 'branches', 'selectedBranchId'));
    }

    public function create()
    {
        $branches = Branch::where('is_active', true)->get();
        $inventoryItems = InventoryItem::orderBy('name')->get();
        $selectedBranchId = session('selected_branch_id') ?? Branch::where('is_main', true)->value('id');

        return view('admin.transfer.create', compact('branches', 'inventoryItems', 'selectedBranchId'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'from_branch_id' => 'required|exists:branches,id|different:to_branch_id',
            'to_branch_id'   => 'required|exists:branches,id',
            'date'           => 'required|date',
            'notes'          => 'nullable|string',
            'items'          => 'required|array|min:1',
            'items.*.inventory_item_id' => 'required|exists:inventory_items,id',
            'items.*.quantity'          => 'required|numeric|min:0.01',
        ]);

        $transfer = DB::transaction(function () use ($validated) {
            $transfer = StockTransfer::create([
                'transfer_number' => 'TRF-' . date('Ymd') . '-' . strtoupper(Str::random(4)),
                'from_branch_id'  => $validated['from_branch_id'],
                'to_branch_id'    => $validated['to_branch_id'],
                'status'          => StockTransfer::STATUS_DRAFT,
                'date'            => $validated['date'],
                'notes'           => $validated['notes'] ?? null,
                'created_by'      => auth()->id(),
            ]);

            foreach ($validated['items'] as $item) {
                StockTransferItem::create([
                    'stock_transfer_id' => $transfer->id,
                    'inventory_item_id' => $item['inventory_item_id'],
                    'quantity'          => $item['quantity'],
                ]);
            }

            return $transfer;
        });

        AuditLog::log(auth()->user(), 'transfer.create', "Membuat draft transfer stok #{$transfer->transfer_number}");

        return redirect()->route('admin.transfer.show', $transfer)->with('success', 'Transfer stok berhasil dibuat sebagai draft.');
    }

    public function show(StockTransfer $transfer)
    {
        $transfer->load(['fromBranch', 'toBranch', 'items.inventoryItem', 'user']);
        return view('admin.transfer.show', compact('transfer'));
    }

    public function ship(StockTransfer $transfer)
    {
        try {
            $transfer->ship(auth()->id());
            AuditLog::log(auth()->user(), 'transfer.ship', "Mengirim transfer stok #{$transfer->transfer_number}");
            return redirect()->route('admin.transfer.show', $transfer)->with('success', 'Barang berhasil dikirim (stok cabang asal telah berkurang).');
        } catch (\DomainException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function receive(StockTransfer $transfer)
    {
        try {
            $transfer->receive(auth()->id());
            AuditLog::log(auth()->user(), 'transfer.receive', "Menerima transfer stok #{$transfer->transfer_number}");
            return redirect()->route('admin.transfer.show', $transfer)->with('success', 'Barang berhasil diterima (stok cabang tujuan telah bertambah).');
        } catch (\DomainException $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
