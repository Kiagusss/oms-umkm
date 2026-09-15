<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\HppHistory;
use App\Models\InventoryItem;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Recipe;
use App\Models\RecipeItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RecipeController extends Controller
{
    public function index()
    {
        $recipes = Recipe::with(['product', 'productVariant', 'items.inventoryItem'])->paginate(15);
        return view('admin.resep.index', compact('recipes'));
    }

    public function create()
    {
        $products = Product::with('variants')->orderBy('name')->get();
        $inventoryItems = InventoryItem::orderBy('name')->get();

        return view('admin.resep.create', compact('products', 'inventoryItems'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'product_id'         => 'required|exists:products,id',
            'product_variant_id' => 'nullable|exists:product_variants,id',
            'yield_quantity'     => 'required|numeric|min:0.01',
            'labor_cost'         => 'nullable|numeric|min:0',
            'overhead_cost'      => 'nullable|numeric|min:0',
            'notes'              => 'nullable|string',
            'items'              => 'required|array|min:1',
            'items.*.inventory_item_id' => 'required|exists:inventory_items,id',
            'items.*.quantity'   => 'required|numeric|min:0.0001',
            'items.*.item_type'  => 'required|in:ingredient,packaging,other',
        ]);

        $recipe = DB::transaction(function () use ($validated) {
            $recipe = Recipe::create([
                'product_id'         => $validated['product_id'],
                'product_variant_id' => $validated['product_variant_id'] ?? null,
                'yield_quantity'     => $validated['yield_quantity'],
                'labor_cost'         => $validated['labor_cost'] ?? 0,
                'overhead_cost'      => $validated['overhead_cost'] ?? 0,
                'notes'              => $validated['notes'] ?? null,
            ]);

            foreach ($validated['items'] as $item) {
                RecipeItem::create([
                    'recipe_id'         => $recipe->id,
                    'inventory_item_id' => $item['inventory_item_id'],
                    'quantity'          => $item['quantity'],
                    'item_type'         => $item['item_type'],
                ]);
            }

            $recipe->recalculateHpp(auth()->id(), 'manual_update');

            return $recipe;
        });

        AuditLog::log(auth()->user(), 'recipe.create', "Membuat resep baru untuk produk ID #{$recipe->product_id}");

        return redirect()->route('admin.resep.index')->with('success', 'Resep berhasil dibuat dan HPP dihitung otomatis.');
    }

    public function edit(Recipe $resep)
    {
        $products = Product::with('variants')->orderBy('name')->get();
        $inventoryItems = InventoryItem::orderBy('name')->get();
        $resep->load(['items.inventoryItem', 'product', 'productVariant']);

        return view('admin.resep.edit', [
            'recipe'         => $resep,
            'products'       => $products,
            'inventoryItems' => $inventoryItems,
        ]);
    }

    public function update(Request $request, Recipe $resep)
    {
        $validated = $request->validate([
            'yield_quantity'     => 'required|numeric|min:0.01',
            'labor_cost'         => 'nullable|numeric|min:0',
            'overhead_cost'      => 'nullable|numeric|min:0',
            'notes'              => 'nullable|string',
            'items'              => 'required|array|min:1',
            'items.*.inventory_item_id' => 'required|exists:inventory_items,id',
            'items.*.quantity'   => 'required|numeric|min:0.0001',
            'items.*.item_type'  => 'required|in:ingredient,packaging,other',
        ]);

        DB::transaction(function () use ($validated, $resep) {
            $resep->update([
                'yield_quantity' => $validated['yield_quantity'],
                'labor_cost'     => $validated['labor_cost'] ?? 0,
                'overhead_cost'  => $validated['overhead_cost'] ?? 0,
                'notes'          => $validated['notes'] ?? null,
            ]);

            $resep->items()->delete();
            foreach ($validated['items'] as $item) {
                RecipeItem::create([
                    'recipe_id'         => $resep->id,
                    'inventory_item_id' => $item['inventory_item_id'],
                    'quantity'          => $item['quantity'],
                    'item_type'         => $item['item_type'],
                ]);
            }

            $resep->recalculateHpp(auth()->id(), 'manual_update');
        });

        AuditLog::log(auth()->user(), 'recipe.update', "Memperbarui resep ID #{$resep->id}");

        return redirect()->route('admin.resep.index')->with('success', 'Resep dan HPP berhasil diperbarui.');
    }

    public function recalculate(Recipe $resep)
    {
        $cost = $resep->recalculateHpp(auth()->id(), 'manual_recalc');
        return back()->with('success', "HPP berhasil dihitung ulang: Rp " . number_format($cost, 0, ',', '.'));
    }

    public function history(Recipe $resep)
    {
        $history = HppHistory::where('product_id', $resep->product_id)
            ->when($resep->product_variant_id, fn($q) => $q->where('product_variant_id', $resep->product_variant_id))
            ->with('user')
            ->latest('created_at')
            ->paginate(20);

        return view('admin.resep.history', compact('resep', 'history'));
    }

    public function destroy(Recipe $resep)
    {
        $resep->items()->delete();
        $resep->delete();

        return redirect()->route('admin.resep.index')->with('success', 'Resep berhasil dihapus.');
    }
}
