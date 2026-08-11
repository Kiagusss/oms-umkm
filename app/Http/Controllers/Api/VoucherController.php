<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Voucher;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class VoucherController extends Controller
{
    public function apply(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:50',
            'total' => 'required|numeric|min:0',
        ]);

        $voucher = Voucher::where('code', $validated['code'])
            ->where('is_active', true)
            ->first();

        if (!$voucher) {
            throw ValidationException::withMessages([
                'code' => 'Kode voucher tidak valid atau tidak aktif.',
            ]);
        }

        // Check validity period
        $now = now();
        if ($voucher->valid_from && $now->lt($voucher->valid_from)) {
            throw ValidationException::withMessages([
                'code' => 'Kode voucher belum berlaku.',
            ]);
        }
        if ($voucher->valid_until && $now->gt($voucher->valid_until)) {
            throw ValidationException::withMessages([
                'code' => 'Kode voucher sudah kadaluarsa.',
            ]);
        }

        // Check max uses
        if ($voucher->max_uses !== null && $voucher->used_count >= $voucher->max_uses) {
            throw ValidationException::withMessages([
                'code' => 'Kode voucher sudah mencapai maksimal penggunaan.',
            ]);
        }

        // Check min order
        if ($voucher->min_order && $validated['total'] < $voucher->min_order) {
            throw ValidationException::withMessages([
                'total' => 'Minimal pesanan untuk menggunakan voucher ini adalah Rp ' . number_format($voucher->min_order, 0, ',', '.'),
            ]);
        }

        // Calculate discount
        if ($voucher->type === 'percentage') {
            $discount = $validated['total'] * ($voucher->value / 100);
        } else {
            $discount = $voucher->value;
        }

        // Ensure discount does not exceed total
        $discount = min($discount, $validated['total']);
        $newTotal = $validated['total'] - $discount;

        return response()->json([
            'ok' => true,
            'voucher' => [
                'code' => $voucher->code,
                'type' => $voucher->type,
                'value' => $voucher->value,
            ],
            'discount' => $discount,
            'new_total' => $newTotal,
        ]);
    }
}