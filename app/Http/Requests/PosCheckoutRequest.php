<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PosCheckoutRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // ponytail: add admin auth check when middleware applied to API routes
    }

    public function rules(): array
    {
        return [
            'customer_name'     => 'required|string|max:255',
            'customer_whatsapp' => 'nullable|string|max:50',
            'items'             => 'required|array|min:1',
            'items.*.id'        => 'required|integer|exists:products,id',
            'items.*.quantity'  => 'required|integer|min:1',
            'notes'             => 'nullable|string|max:500',
            'payment_method'    => 'required|in:Tunai,Transfer,QRIS',
            'cash_received'     => 'nullable|integer|min:0',
        ];
    }

    public function messages(): array
    {
        return [
            'customer_name.required'  => 'Nama pelanggan wajib diisi',
            'items.required'          => 'Keranjang kosong',
            'items.min'               => 'Minimal 1 produk harus dipilih',
            'items.*.id.exists'       => 'Produk tidak ditemukan',
            'items.*.quantity.min'    => 'Jumlah minimal 1',
            'payment_method.required' => 'Metode pembayaran wajib dipilih',
            'payment_method.in'       => 'Metode pembayaran tidak valid',
            'cash_received.min'       => 'Jumlah uang diterima tidak valid',
        ];
    }
}