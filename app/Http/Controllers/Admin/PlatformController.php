<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Store;
use App\Models\StoreMember;
use App\Models\User;
use App\Services\Tenant\TenantContext;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PlatformController extends Controller
{
    /**
     * Platform administration dashboard.
     */
    public function index()
    {
        $stores = Store::withCount(['products', 'orders', 'members'])
            ->with('owner')
            ->latest()
            ->paginate(15);

        $totalStores = Store::count();
        $activeStores = Store::where('status', 'active')->count();
        $totalProducts = \App\Models\Product::count();
        $totalOrders = \App\Models\Order::count();
        $currentStoreId = TenantContext::getStoreId();

        return view('admin.platform.index', compact('stores', 'totalStores', 'activeStores', 'totalProducts', 'totalOrders', 'currentStoreId'));
    }

    /**
     * Create a new store tenant.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'city' => 'nullable|string|max:100',
            'phone' => 'nullable|string|max:30',
            'whatsapp' => 'nullable|string|max:30',
            'description' => 'nullable|string',
        ]);

        $baseSlug = Str::slug($request->name);
        $slug = $baseSlug;
        $counter = 1;
        while (Store::where('slug', $slug)->exists()) {
            $slug = "{$baseSlug}-{$counter}";
            $counter++;
        }

        $store = Store::create([
            'owner_id' => auth()->id(),
            'name' => $request->name,
            'slug' => $slug,
            'city' => $request->city,
            'phone' => $request->phone,
            'whatsapp' => $request->whatsapp,
            'description' => $request->description,
            'status' => 'active',
        ]);

        if (auth()->check()) {
            StoreMember::firstOrCreate([
                'store_id' => $store->id,
                'user_id' => auth()->id(),
            ], [
                'role' => 'owner',
                'status' => 'active',
            ]);
        }

        return redirect()->route('admin.platform.index')->with('success', "Toko '{$store->name}' berhasil dibuat.");
    }

    /**
     * Switch active store for current session.
     */
    public function switchStore(Request $request)
    {
        $storeId = (int) $request->input('store_id');
        $store = Store::findOrFail($storeId);

        TenantContext::setStore($store);

        return back()->with('success', "Toko aktif beralih ke: {$store->name}");
    }

    /**
     * Toggle store status (active / suspended).
     */
    public function toggleStatus(Store $store)
    {
        $store->status = $store->status === 'active' ? 'suspended' : 'active';
        $store->save();

        return back()->with('success', "Status toko '{$store->name}' diperbarui menjadi {$store->status}.");
    }
}
