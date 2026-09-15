<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Branch;
use App\Models\Expense;
use Illuminate\Http\Request;

class ExpenseController extends Controller
{
    public function index(Request $request)
    {
        $selectedBranchId = session('selected_branch_id');
        $query = Expense::with(['branch', 'user']);

        if ($branchId = ($request->get('branch_id') ?: $selectedBranchId)) {
            $query->where('branch_id', $branchId);
        }

        if ($category = $request->get('category')) {
            $query->where('category', $category);
        }

        if ($startDate = $request->get('start_date')) {
            $query->whereDate('date', '>=', $startDate);
        }
        if ($endDate = $request->get('end_date')) {
            $query->whereDate('date', '<=', $endDate);
        }

        $totalExpenses = (clone $query)->sum('amount');
        $expenses = $query->latest('date')->paginate(15);
        $branches = Branch::where('is_active', true)->get();
        $categories = Expense::categories();

        return view('admin.biaya.index', compact('expenses', 'branches', 'categories', 'totalExpenses', 'selectedBranchId'));
    }

    public function create()
    {
        $branches = Branch::where('is_active', true)->get();
        $categories = Expense::categories();
        $selectedBranchId = session('selected_branch_id') ?? Branch::where('is_main', true)->value('id');

        return view('admin.biaya.create', compact('branches', 'categories', 'selectedBranchId'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'branch_id'     => 'nullable|exists:branches,id',
            'category'      => 'required|string|max:100',
            'amount'        => 'required|numeric|min:1',
            'date'          => 'required|date',
            'description'   => 'required|string|max:500',
            'receipt_image' => 'nullable|image|max:2048',
        ]);

        $receiptPath = null;
        if ($request->hasFile('receipt_image')) {
            $receiptPath = $request->file('receipt_image')->store('receipts', 'public');
        }

        $expense = Expense::create([
            'branch_id'     => $validated['branch_id'] ?: null,
            'category'      => $validated['category'],
            'amount'        => $validated['amount'],
            'date'          => $validated['date'],
            'description'   => $validated['description'],
            'receipt_image' => $receiptPath,
            'created_by'    => auth()->id(),
        ]);

        AuditLog::log(auth()->user(), 'expense.create', "Mencatat pengeluaran: {$expense->category} sebesar Rp " . number_format($expense->amount, 0, ',', '.'));

        return redirect()->route('admin.biaya.index')->with('success', 'Biaya operasional berhasil dicatat.');
    }

    public function destroy(Expense $biaya)
    {
        $desc = "{$biaya->category} - Rp " . number_format($biaya->amount, 0, ',', '.');
        $biaya->delete();

        AuditLog::log(auth()->user(), 'expense.delete', "Menghapus biaya: {$desc}");

        return redirect()->route('admin.biaya.index')->with('success', 'Biaya operasional berhasil dihapus.');
    }
}
