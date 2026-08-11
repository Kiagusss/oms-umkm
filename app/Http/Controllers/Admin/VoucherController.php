<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Voucher;
use Illuminate\Http\Request;

class VoucherController extends Controller
{
    public function index()
    {
        $vouchers = Voucher::orderByDesc('created_at')->paginate(15);

        return view('admin.voucher.index', compact('vouchers'));
    }

    public function create()
    {
        return view('admin.voucher.create');
    }

    public function store(Request $request)
    {
        $validated = $this->validateVoucher($request);

        $validated['is_active'] = $request->boolean('is_active');

        Voucher::create($validated);

        return redirect()->route('admin.voucher.index')->with('success', 'Voucher berhasil ditambahkan.');
    }

    public function edit(Voucher $voucher)
    {
        return view('admin.voucher.edit', compact('voucher'));
    }

    public function update(Request $request, Voucher $voucher)
    {
        $validated = $this->validateVoucher($request, $voucher);

        $validated['is_active'] = $request->boolean('is_active');

        $voucher->update($validated);

        return redirect()->route('admin.voucher.index')->with('success', 'Voucher berhasil diperbarui.');
    }

    public function destroy(Voucher $voucher)
    {
        $voucher->delete();

        return redirect()->route('admin.voucher.index')->with('success', 'Voucher berhasil dihapus.');
    }

    protected function validateVoucher(Request $request, ?Voucher $voucher = null): array
    {
        // Normalisasi kode ke uppercase sebelum validasi unique (cek duplikat case-insensitive)
        $request->merge(['code' => strtoupper(trim((string) $request->input('code')))]);

        $uniqueCode = 'unique:vouchers,code' . ($voucher ? ',' . $voucher->id : '');

        return $request->validate([
            'code'       => ['required', 'string', 'max:50', $uniqueCode],
            'type'       => ['required', 'in:percentage,fixed'],
            'value'      => ['required', 'numeric', 'min:0.01', 'max:100000000'],
            'min_order'  => ['nullable', 'numeric', 'min:0'],
            'max_uses'   => ['nullable', 'integer', 'min:1'],
            'valid_from' => ['nullable', 'date'],
            'valid_until' => ['nullable', 'date', 'after_or_equal:valid_from'],
            'is_active'  => ['sometimes', 'boolean'],
        ], [
            'code.required'        => 'Kode voucher wajib diisi',
            'code.unique'          => 'Kode voucher sudah dipakai',
            'type.required'        => 'Tipe voucher wajib diisi',
            'type.in'              => 'Tipe voucher tidak valid',
            'value.required'       => 'Nilai diskon wajib diisi',
            'value.min'            => 'Nilai diskon harus lebih dari 0',
            'max_uses.min'         => 'Maksimal penggunaan minimal 1',
            'valid_until.after_or_equal' => 'Tanggal berakhir harus setelah atau sama dengan tanggal mulai',
        ]);
    }
}
