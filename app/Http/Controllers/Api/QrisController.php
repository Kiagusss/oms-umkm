<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\JsonResponse;

/**
 * QRIS simulasi — admin-only (dilindungi middleware admin.api).
 *
 * ponytail: settle manual mewakili webhook payment gateway.
 * Upgrade path: ganti settle() dengan verifikasi webhook Midtrans/Xendit.
 */
class QrisController extends Controller
{
    /** POST /api/qris/create → payload QR + info order pending. */
    public function create(\Illuminate\Http\Request $request): JsonResponse
    {
        $validated = $request->validate([
            'order_id' => 'required|integer|exists:orders,id',
        ]);

        $order = Order::findOrFail($validated['order_id']);

        if ($order->status !== 'pending') {
            return response()->json(['error' => 'Order sudah tidak pending'], 409);
        }

        $payload = \App\Services\QrisService::fromSettings()->dynamicPayload(
            $order->total() - (int) ($order->discount ?? 0),
            $order->id,
        );

        return response()->json([
            'ok'        => true,
            'order_id'  => $order->id,
            'total'     => $order->total() - (int) ($order->discount ?? 0),
            'qris_payload' => $payload,
        ]);
    }

    /** POST /api/qris/settle → simulasi "pelanggan sudah scan & bayar". */
    public function settle(\Illuminate\Http\Request $request): JsonResponse
    {
        $validated = $request->validate([
            'order_id' => 'required|integer|exists:orders,id',
        ]);

        $order = Order::findOrFail($validated['order_id']);

        if ($order->payment_method !== 'QRIS') {
            return response()->json(['error' => 'Bukan order QRIS'], 422);
        }
        if ($order->status !== 'pending') {
            return response()->json(['error' => 'Order sudah diproses sebelumnya'], 409);
        }

        $order->update(['status' => 'completed']);

        return response()->json([
            'ok'       => true,
            'order_id' => $order->id,
            'status'   => $order->status,
        ]);
    }
}
