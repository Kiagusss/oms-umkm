<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Setting;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $query = Order::query();

        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('whatsapp', 'like', "%{$search}%");
            });
        }
        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }

        $orders = $query->orderBy('date', 'desc')->paginate(15);

        $stats = [
            'total' => Order::count(),
            'pending' => Order::where('status', 'pending')->count(),
            'processing' => Order::where('status', 'processing')->count(),
            'completed' => Order::where('status', 'completed')->count(),
            'cancelled' => Order::where('status', 'cancelled')->count(),
        ];

        return view('admin.pesanan.index', compact('orders', 'stats'));
    }

    public function create()
    {
        return view('admin.pesanan.create');
    }

    public function store(Request $request)
    {
        $validated = $this->validateOrder($request);
        $validated['products'] = json_encode($request->input('products', []));

        Order::create($validated);

        return redirect()->route('admin.pesanan.index')->with('success', 'Pesanan berhasil ditambahkan.');
    }

    public function show(Order $pesanan)
    {
        return view('admin.pesanan.show', compact('pesanan'));
    }

    public function edit(Order $pesanan)
    {
        return view('admin.pesanan.edit', compact('pesanan'));
    }

    public function update(Request $request, Order $pesanan)
    {
        $validated = $this->validateOrder($request, $pesanan);
        if ($request->has('products')) {
            $validated['products'] = json_encode($request->input('products', []));
        }

        // Restore stock if transitioning to cancelled
        if ($pesanan->status !== 'cancelled' && $validated['status'] === 'cancelled') {
            $items = is_array($pesanan->products) ? $pesanan->products : json_decode((string) $pesanan->products, true) ?? [];
            foreach ($items as $item) {
                $productId = $item['productId'] ?? null;
                $variantId = $item['variantId'] ?? null;
                $qty = (int) ($item['quantity'] ?? 0);
                if ($productId && $qty > 0) {
                    \App\Models\Product::where('id', $productId)->increment('stock', $qty);
                    if ($variantId) {
                        \App\Models\ProductVariant::where('id', $variantId)->increment('stock', $qty);
                    }
                    if ($pesanan->branch_id) {
                        $bi = \App\Models\BranchInventory::where('branch_id', $pesanan->branch_id)
                            ->where('product_id', $productId)
                            ->where('product_variant_id', $variantId)
                            ->first();
                        $balanceAfter = 0;
                        if ($bi) {
                            $bi->increment('quantity', $qty);
                            $balanceAfter = (float) $bi->fresh()->quantity;
                        }
                        \App\Models\StockMovement::create([
                            'branch_id'          => $pesanan->branch_id,
                            'inventory_item_id'  => null,
                            'product_id'         => $productId,
                            'product_variant_id' => $variantId,
                            'type'               => 'sale_return',
                            'quantity'           => $qty,
                            'balance_after'      => $balanceAfter,
                            'reference_type'     => Order::class,
                            'reference_id'       => $pesanan->id,
                            'notes'              => "Pembatalan Order #{$pesanan->id}",
                            'user_id'            => auth()->id() ?? session('admin_user_id'),
                        ]);
                    }
                }
            }
        }

        $pesanan->update($validated);

        return redirect()->route('admin.pesanan.index')->with('success', 'Pesanan berhasil diperbarui.');
    }

    public function destroy(Order $pesanan)
    {
        $pesanan->delete();

        return redirect()->route('admin.pesanan.index')->with('success', 'Pesanan berhasil dihapus.');
    }

    public function struk(Order $pesanan)
    {
        $settings = Setting::pluck('value', 'key')->toArray();
        $items = is_array($pesanan->products) ? $pesanan->products : json_decode((string) $pesanan->products, true) ?? [];
        $subtotal = (int) $pesanan->total();
        $discount = (int) ($pesanan->discount ?? 0);

        $pdf = Pdf::loadView('admin.pesanan.struk', compact('pesanan', 'settings', 'items', 'subtotal', 'discount'));

        return $pdf->download('struk-' . $pesanan->id . '.pdf');
    }

    protected function validateOrder(Request $request, ?Order $order = null): array
    {
        return $request->validate([
            'name' => 'required|string|max:255',
            'whatsapp' => 'required|string|max:255',
            'notes' => 'nullable|string',
            'date' => 'required|date',
            'status' => 'required|in:pending,processing,completed,cancelled',
        ]);
    }
}
