<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
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

        $pesanan->update($validated);

        return redirect()->route('admin.pesanan.index')->with('success', 'Pesanan berhasil diperbarui.');
    }

    public function destroy(Order $pesanan)
    {
        $pesanan->delete();

        return redirect()->route('admin.pesanan.index')->with('success', 'Pesanan berhasil dihapus.');
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
