<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CheckoutRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Endpoint publik — siapa pun boleh checkout.
        return true;
    }

    public function rules(): array
    {
        return [
            'customer_name'     => 'required|string|max:255',
            'customer_whatsapp' => 'required|string|max:50',
            'address'           => 'required|string|max:1000',
            'items'             => 'required|array|min:1|max:50',
            'items.*.id'        => 'required|integer|exists:products,id',
            'items.*.quantity'  => 'required|integer|min:1|max:99',
            'notes'             => 'nullable|string|max:500',
            'payment_method'    => 'required|in:QRIS,Transfer',
            // Ongkir opsional — kalau diisi, biaya diverifikasi ulang server-side
            'shipping'          => 'nullable|array',
            'shipping.destination' => 'required_with:shipping|string|max:20',
            'shipping.weight'   => 'required_with:shipping|integer|min:1|max:100000',
            'shipping.courier'  => 'required_with:shipping|string|max:20',
            'shipping.service'  => 'required_with:shipping|string|max:50',
        ];
    }

    public function messages(): array
    {
        return [
            'customer_name.required'     => 'Nama wajib diisi',
            'customer_whatsapp.required' => 'Nomor HP wajib diisi',
            'address.required'           => 'Alamat wajib diisi',
            'items.required'             => 'Keranjang kosong',
            'items.*.quantity.max'       => 'Maksimal 99 per item',
            'payment_method.in'          => 'Metode pembayaran tidak valid',
        ];
    }
}