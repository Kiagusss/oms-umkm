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
            'variant_id'         => 'nullable|exists:product_variants,id',
            'packaging_cost'     => 'nullable|numeric|min:0',
            'additional_cost'    => 'nullable|numeric|min:0',
            'labor_cost'         => 'nullable|numeric|min:0',
            'overhead_cost'      => 'nullable|numeric|min:0',
            'instructions'       => 'nullable|string',
            'notes'              => 'nullable|string',
            'items'              => 'required|array|min:1',
            'items.*.inventory_item_id' => 'required|exists:inventory_items,id',
            'items.*.quantity'   => 'required|numeric|min:0.0001',
        ]);

        $variantId = $validated['product_variant_id'] ?? $validated['variant_id'] ?? null;
        $product = Product::findOrFail($validated['product_id']);
        $recipeName = 'Resep ' . $product->name;
        if ($variantId) {
            $variant = ProductVariant::find($variantId);
            if ($variant) {
                $recipeName .= ' (' . $variant->name . ')';
            }
        }

        $packagingCost = (int) ($validated['packaging_cost'] ?? 0);
        $additionalCost = (int) ($validated['additional_cost'] ?? (($validated['labor_cost'] ?? 0) + ($validated['overhead_cost'] ?? 0)));
        $notes = $validated['notes'] ?? $validated['instructions'] ?? null;

        $recipe = DB::transaction(function () use ($validated, $product, $variantId, $recipeName, $packagingCost, $additionalCost, $notes) {
            $recipe = Recipe::create([
                'product_id'         => $product->id,
                'product_variant_id' => $variantId,
                'name'               => $recipeName,
                'packaging_cost'     => $packagingCost,
                'additional_cost'    => $additionalCost,
                'notes'              => $notes,
            ]);

            foreach ($validated['items'] as $item) {
                $invItem = InventoryItem::find($item['inventory_item_id']);
                $costPerUnit = $invItem ? (int) $invItem->cost_per_unit : 0;
                $qty = (float) $item['quantity'];

                RecipeItem::create([
                    'recipe_id'         => $recipe->id,
                    'inventory_item_id' => $item['inventory_item_id'],
                    'quantity'          => $qty,
                    'unit'              => $invItem?->unit ?? 'unit',
                    'cost_per_unit'     => $costPerUnit,
                    'subtotal'          => (int) round($qty * $costPerUnit),
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
            'product_id'         => 'sometimes|required|exists:products,id',
            'product_variant_id' => 'nullable|exists:product_variants,id',
            'variant_id'         => 'nullable|exists:product_variants,id',
            'packaging_cost'     => 'nullable|numeric|min:0',
            'additional_cost'    => 'nullable|numeric|min:0',
            'labor_cost'         => 'nullable|numeric|min:0',
            'overhead_cost'      => 'nullable|numeric|min:0',
            'instructions'       => 'nullable|string',
            'notes'              => 'nullable|string',
            'items'              => 'required|array|min:1',
            'items.*.inventory_item_id' => 'required|exists:inventory_items,id',
            'items.*.quantity'   => 'required|numeric|min:0.0001',
        ]);

        $productId = $validated['product_id'] ?? $resep->product_id;
        $variantId = $validated['product_variant_id'] ?? $validated['variant_id'] ?? $resep->product_variant_id;
        $product = Product::findOrFail($productId);
        $recipeName = 'Resep ' . $product->name;
        if ($variantId) {
            $variant = ProductVariant::find($variantId);
            if ($variant) {
                $recipeName .= ' (' . $variant->name . ')';
            }
        }

        $packagingCost = isset($validated['packaging_cost']) ? (int) $validated['packaging_cost'] : (int) $resep->packaging_cost;
        $additionalCost = isset($validated['additional_cost']) 
            ? (int) $validated['additional_cost'] 
            : (isset($validated['labor_cost']) || isset($validated['overhead_cost']) 
                ? (int) (($validated['labor_cost'] ?? 0) + ($validated['overhead_cost'] ?? 0)) 
                : (int) $resep->additional_cost);
        $notes = $validated['notes'] ?? $validated['instructions'] ?? $resep->notes;

        DB::transaction(function () use ($validated, $resep, $productId, $variantId, $recipeName, $packagingCost, $additionalCost, $notes) {
            $resep->update([
                'product_id'         => $productId,
                'product_variant_id' => $variantId,
                'name'               => $recipeName,
                'packaging_cost'     => $packagingCost,
                'additional_cost'    => $additionalCost,
                'notes'              => $notes,
            ]);

            $resep->items()->delete();
            foreach ($validated['items'] as $item) {
                $invItem = InventoryItem::find($item['inventory_item_id']);
                $costPerUnit = $invItem ? (int) $invItem->cost_per_unit : 0;
                $qty = (float) $item['quantity'];

                RecipeItem::create([
                    'recipe_id'         => $resep->id,
                    'inventory_item_id' => $item['inventory_item_id'],
                    'quantity'          => $qty,
                    'unit'              => $invItem?->unit ?? 'unit',
                    'cost_per_unit'     => $costPerUnit,
                    'subtotal'          => (int) round($qty * $costPerUnit),
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
