<?php

namespace Tests\Feature;

use App\Models\InventoryItem;
use App\Models\Permission;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\Role;
use App\Models\Supplier;
use App\Models\Branch;
use App\Models\User;
use App\Services\TransactionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuditFixesTest extends TestCase
{
    use RefreshDatabase;

    public function test_auth_middleware_does_not_escalate_to_first_user_without_user_id(): void
    {
        $owner = User::create([
            'name' => 'Owner Root',
            'email' => 'root@pempek.com',
            'password' => bcrypt('secret'),
            'role_id' => null,
            'status' => 'active',
        ]);

        // Simulasikan session admin_authenticated tanpa admin_user_id
        $response = $this->withSession(['admin_authenticated' => true])
            ->get('/admin/cabang');

        // Harus 403 karena tidak ada user valid yang terautentikasi (tidak boleh fallback ke $owner)
        $response->assertStatus(403);
    }

    public function test_purchase_cannot_be_double_received(): void
    {
        $branch = Branch::create(['name' => 'Cabang Test', 'code' => 'TEST-01', 'status' => 'active']);
        $supplier = Supplier::create(['code' => 'SUP-001', 'name' => 'Supplier A', 'phone' => '0812345', 'status' => 'active']);
        $item = InventoryItem::create(['sku' => 'SAGU-01', 'name' => 'Tepung Sagu', 'unit' => 'kg', 'cost_per_unit' => 10000]);

        $purchase = Purchase::create([
            'purchase_number' => 'PO-001',
            'supplier_id' => $supplier->id,
            'branch_id' => $branch->id,
            'total_amount' => 100000,
            'date' => now()->toDateString(),
            'status' => 'pending',
        ]);

        PurchaseItem::create([
            'purchase_id' => $purchase->id,
            'inventory_item_id' => $item->id,
            'quantity' => 10,
            'unit' => 'kg',
            'unit_price' => 10000,
            'subtotal' => 100000,
        ]);

        $purchase->receive();
        $this->assertEquals('received', $purchase->fresh()->status);

        // Percobaan receive kedua tidak boleh melipatgandakan stok
        $purchase->receive();
        $branchStock = \App\Models\BranchInventory::where('branch_id', $branch->id)
            ->where('inventory_item_id', $item->id)
            ->first();

        $this->assertEquals(10, (float) $branchStock->quantity);
    }

    public function test_transaction_service_guards_against_variant_oversell(): void
    {
        $product = Product::factory()->create([
            'name' => 'Pempek Lenjer',
            'price' => 10000,
            'stock' => 10,
            'status' => 'active',
        ]);

        $variant = ProductVariant::create([
            'product_id' => $product->id,
            'sku' => 'PL-JUMBO',
            'name' => 'Jumbo',
            'price' => 15000,
            'stock' => 2,
            'status' => 'active',
        ]);

        $service = app(TransactionService::class);

        $this->expectException(\InvalidArgumentException::class);
        $service->checkout([
            'items' => [
                [
                    'id' => $product->id,
                    'variant_id' => $variant->id,
                    'quantity' => 5, // melebihi stock 2
                ]
            ],
            'payment_method' => 'Tunai',
            'cash_received' => 100000,
        ]);
    }
}
