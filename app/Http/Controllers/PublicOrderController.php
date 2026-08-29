<?php

namespace App\Http\Controllers;

use App\Http\Requests\CheckoutRequest;
use App\Models\Order;
use App\Services\OngkirService;
use App\Services\TransactionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PublicOrderController extends Controller
{
    /** GET /checkout — form data diri + ongkir. */
    public function form(): View
    {
        return view('pesanan.checkout');
    }

    /** POST /checkout — buat order dari form, redirect ke halaman QRIS. */
    public function checkout(CheckoutRequest $request, TransactionService $service, OngkirService $ongkir): RedirectResponse
    {
        $data = $request->validated();

        // Verifikasi ongkir server-side — jangan percaya biaya dari client
        if (!empty($data['shipping'])) {
            try {
                $rates = $ongkir->rates($data['shipping']['destination'], (int) $data['shipping']['weight']);
            } catch (\RuntimeException $e) {
                return back()->withInput()->with('error', 'Gagal memverifikasi ongkir, silakan cek ongkir lagi.');
            }
            $match = collect($rates)->first(fn($r) => $r['courier'] === $data['shipping']['courier'] && $r['service'] === $data['shipping']['service']);
            if (!$match) {
                return back()->withInput()->with('error', 'Pilihan kurir tidak valid, silakan cek ongkir lagi.');
            }
            $data['shipping_courier'] = $match['courier'] . ' - ' . $match['service'];
            $data['shipping_cost']    = (int) $match['cost'];
        }

        try {
            $order = $service->checkout($data);
        } catch (\InvalidArgumentException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        return redirect()->route('pesanan.show', $order)
            ->with('qris_just_created', true);
    }

    public function show(Order $order): View
    {
        $payload = null;
        $qrisToken = null;
        if ($order->payment_method === 'QRIS' && $order->status === 'pending') {
            $payload = \App\Services\QrisService::fromSettings()->dynamicPayload(
                $order->grandTotal(),
                (int) $order->id
            );
            $qrisToken = hash('sha256', $order->id . config('app.key') . $order->created_at);
        }

        return view('pesanan.show', [
            'order' => $order,
            'qrisPayload' => $payload,
            'qrisToken' => $qrisToken,
            'justPaid' => session('qris_paid_' . $order->id),
            'justCreated' => session('qris_just_created'),
        ]);
    }

    /** POST /api/public/qris/settle — customer konfirmasi bayar (token rahasia). */
    public function settleQris(Request $request): \Illuminate\Http\JsonResponse
    {
        $validated = $request->validate([
            'order_id' => 'required|integer|exists:orders,id',
            'token' => 'required|string',
        ]);

        $order = Order::findOrFail($validated['order_id']);

        $expected = hash('sha256', $order->id . config('app.key') . $order->created_at);
        if (!hash_equals($expected, $validated['token'])) {
            return response()->json(['error' => 'Token tidak valid'], 403);
        }

        if ($order->payment_method !== 'QRIS') {
            return response()->json(['error' => 'Bukan order QRIS'], 422);
        }
        if ($order->status !== 'pending') {
            return response()->json(['error' => 'Order sudah diproses sebelumnya'], 409);
        }

        $order->update(['status' => 'processing']);
        session(['qris_paid_' . $order->id => true]);

        return response()->json(['ok' => true, 'order_id' => $order->id, 'status' => $order->status]);
    }
}