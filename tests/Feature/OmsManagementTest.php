<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\BranchInventory;
use App\Models\Expense;
use App\Models\HppHistory;
use App\Models\InventoryItem;
use App\Models\Order;
use App\Models\Permission;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\Recipe;
use App\Models\RecipeItem;
use App\Models\Role;
use App\Models\StockMovement;
use App\Models\StockTransfer;
use App\Models\StockTransferItem;
use App\Models\Supplier;
use App\Models\User;
use App\Services\TransactionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class OmsManagementTest extends TestCase
{
    use RefreshDatabase;

    private User $adminUser;
    private Role $ownerRole;
    private Role $cashierRole;
    private Branch $branchA;
    private Branch $branchB;

    protected function setUp(): void
    {
        parent::setUp();

        $this->ownerRole = Role::create([
            'name' => 'owner',
            'label' => 'Owner',
            'description' => 'Owner with full access',
        ]);

        $this->cashierRole = Role::create([
            'name' => 'cashier',
            'label' => 'Kasir',
            'description' => 'Cashier POS access only',
        ]);

        $this->adminUser = User::create([
            'name' => 'Owner User',
            'email' => 'owner@test.com',
            'password' => bcrypt('password'),
            'role_id' => $this->ownerRole->id,
            'status' => 'active',
        ]);

        $this->branchA = Branch::create([
            'code' => 'PLG-01',
            'name' => 'Cabang Palembang Pusat',
            'address' => 'Jl. Sudirman No. 12',
            'status' => 'active',
        ]);

        $this->branchB = Branch::create([
            'code' => 'JKT-01',
            'name' => 'Cabang Jakarta Selatan',
            'address' => 'Jl. Fatmawati No. 88',
            'status' => 'active',
        ]);
    }

    #[Test]
    public function test_multi_branch_inventory_isolation(): void
    {
        $item = InventoryItem::create([
            'sku' => 'IKAN-TENGGIRI',
            'name' => 'Daging Ikan Tenggiri Giling',
            'item_type' => 'raw_material',
            'unit' => 'kg',
            'cost_per_unit' => 85000,
            'minimum_stock' => 10,
        ]);

        // Stock in Branch A
        $invA = BranchInventory::create([
            'branch_id' => $this->branchA->id,
            'inventory_item_id' => $item->id,
            'quantity' => 50,
            'minimum_stock' => 10,
        ]);

        // Stock in Branch B
        $invB = BranchInventory::create([
            'branch_id' => $this->branchB->id,
            'inventory_item_id' => $item->id,
            'quantity' => 20,
            'minimum_stock' => 5,
        ]);

        $this->assertEquals(50, BranchInventory::where('branch_id', $this->branchA->id)->where('inventory_item_id', $item->id)->value('quantity'));
        $this->assertEquals(20, BranchInventory::where('branch_id', $this->branchB->id)->where('inventory_item_id', $item->id)->value('quantity'));

        // Modifying Branch A does not alter Branch B
        $invA->adjust(-15, 'damaged', null, null, null, $this->adminUser->id);

        $this->assertEquals(35, (float) $invA->fresh()->quantity);
        $this->assertEquals(20, (float) $invB->fresh()->quantity);
    }

    #[Test]
    public function test_recipe_hpp_calculation_and_ingredient_price_change(): void
    {
        $ikan = InventoryItem::create([
            'sku' => 'IKAN-01',
            'name' => 'Ikan Tenggiri',
            'item_type' => 'raw_material',
            'unit' => 'kg',
            'cost_per_unit' => 80000,
            'minimum_stock' => 5,
        ]);

        $tepung = InventoryItem::create([
            'sku' => 'TPG-01',
            'name' => 'Tepung Tapioka',
            'item_type' => 'raw_material',
            'unit' => 'kg',
            'cost_per_unit' => 15000,
            'minimum_stock' => 5,
        ]);

        $product = Product::factory()->create([
            'name' => 'Pempek Lenjer',
            'price' => 12000,
            'stock' => 100,
            'cost_price' => 0,
        ]);

        $recipe = Recipe::create([
            'product_id' => $product->id,
            'name' => 'Resep Pempek Lenjer',
            'packaging_cost' => 0,
            'additional_cost' => 0,
        ]);

        // 0.5 kg Ikan (40.000) + 0.2 kg Tepung (3.000) = 43.000
        RecipeItem::create([
            'recipe_id' => $recipe->id,
            'inventory_item_id' => $ikan->id,
            'quantity' => 0.5,
            'unit' => 'kg',
        ]);
        RecipeItem::create([
            'recipe_id' => $recipe->id,
            'inventory_item_id' => $tepung->id,
            'quantity' => 0.2,
            'unit' => 'kg',
        ]);

        $newHpp = $recipe->recalculateHpp($this->adminUser->id, 'recipe');
        $this->assertEquals(43000, (float) $newHpp);
        $this->assertEquals(43000, (float) $product->fresh()->cost_price);

        // Ingredient price changes: Ikan becomes 100.000/kg
        $ikan->update(['cost_per_unit' => 100000]);

        // 0.5 * 100.000 (50.000) + 0.2 * 15.000 (3.000) = 53.000
        $updatedHpp = $recipe->recalculateHpp($this->adminUser->id, 'manual');
        $this->assertEquals(53000, (float) $updatedHpp);
        $this->assertEquals(53000, (float) $product->fresh()->cost_price);

        // Verify HPP history audit log
        $this->assertDatabaseHas('hpp_histories', [
            'product_id' => $product->id,
            'cost_price' => 53000,
            'created_by' => $this->adminUser->id,
        ]);
    }

    #[Test]
    public function test_pos_checkout_stock_deduction_and_cogs_snapshot(): void
    {
        $ikan = InventoryItem::create([
            'sku' => 'IKAN-POS',
            'name' => 'Ikan Tenggiri Segar',
            'item_type' => 'raw_material',
            'unit' => 'kg',
            'cost_per_unit' => 80000,
            'minimum_stock' => 2,
        ]);

        // Stock in Branch A = 10 kg
        BranchInventory::create([
            'branch_id' => $this->branchA->id,
            'inventory_item_id' => $ikan->id,
            'quantity' => 10.0,
            'minimum_stock' => 2.0,
        ]);

        $product = Product::factory()->create([
            'name' => 'Pempek Kapal Selam',
            'price' => 25000,
            'stock' => 50,
            'cost_price' => 10000,
        ]);

        $variant = ProductVariant::create([
            'product_id' => $product->id,
            'sku' => 'PKS-SUPER',
            'name' => 'Super Jumbo Telur Bebek',
            'price' => 28000,
            'cost_price' => 12000,
            'stock' => 20,
        ]);

        BranchInventory::create([
            'branch_id' => $this->branchA->id,
            'product_id' => $product->id,
            'product_variant_id' => $variant->id,
            'quantity' => 20,
            'minimum_stock' => 2,
        ]);

        // Recipe for variant: 1 portion uses 0.25 kg ikan
        $recipe = Recipe::create([
            'product_id' => $product->id,
            'product_variant_id' => $variant->id,
            'name' => 'Resep Pempek Kapal Selam Super Jumbo',
            'total_hpp' => 12000,
        ]);

        RecipeItem::create([
            'recipe_id' => $recipe->id,
            'inventory_item_id' => $ikan->id,
            'quantity' => 0.25,
            'unit' => 'kg',
        ]);

        // Perform POS checkout for 2 portions of the variant
        $response = $this->withSession([
            'admin_authenticated' => true,
            'admin_user_id' => $this->adminUser->id,
            'selected_branch_id' => $this->branchA->id,
        ])->postJson('/api/pos-checkout', [
            'customer_name' => 'Pak Rudi',
            'customer_whatsapp' => '081299998888',
            'branch_id' => $this->branchA->id,
            'payment_method' => 'Tunai',
            'cash_received' => 60000,
            'items' => [
                [
                    'id' => $product->id,
                    'variant_id' => $variant->id,
                    'quantity' => 2,
                ],
            ],
        ]);

        $response->assertOk()->assertJsonFragment(['ok' => true]);
        $orderId = $response->json('order_id');

        $order = Order::find($orderId);
        $this->assertNotNull($order);
        $this->assertEquals($this->branchA->id, $order->branch_id);

        // COGS snapshot: 2 * 12000 = 24000
        $this->assertEquals(24000, (float) $order->cogs);

        // Stock deduction: 10 - (2 * 0.25) = 9.5 kg
        $branchInv = BranchInventory::where('branch_id', $this->branchA->id)
            ->where('inventory_item_id', $ikan->id)
            ->first();
        $this->assertEquals(9.5, (float) $branchInv->quantity);

        // Verify stock movement ledger
        $this->assertDatabaseHas('stock_movements', [
            'branch_id' => $this->branchA->id,
            'inventory_item_id' => $ikan->id,
            'type' => 'usage_order',
            'quantity' => -0.5,
            'balance_after' => 9.5,
        ]);
    }

    #[Test]
    public function test_branch_to_branch_stock_transfer_workflow(): void
    {
        $cuka = InventoryItem::create([
            'sku' => 'CUKO-BOTOL',
            'name' => 'Cuko Kental Botol 500ml',
            'item_type' => 'semi_finished',
            'unit' => 'botol',
            'cost_per_unit' => 15000,
            'minimum_stock' => 10,
        ]);

        // Branch A has 100 bottles, Branch B has 10 bottles
        $invA = BranchInventory::create([
            'branch_id' => $this->branchA->id,
            'inventory_item_id' => $cuka->id,
            'quantity' => 100,
            'minimum_stock' => 10,
        ]);
        $invB = BranchInventory::create([
            'branch_id' => $this->branchB->id,
            'inventory_item_id' => $cuka->id,
            'quantity' => 10,
            'minimum_stock' => 10,
        ]);

        // Create Stock Transfer
        $transfer = StockTransfer::create([
            'transfer_number' => 'TRF-' . uniqid(),
            'from_branch_id' => $this->branchA->id,
            'to_branch_id' => $this->branchB->id,
            'date' => now()->toDateString(),
            'created_by' => $this->adminUser->id,
            'status' => 'draft',
            'notes' => 'Supply weekend rush',
        ]);

        StockTransferItem::create([
            'stock_transfer_id' => $transfer->id,
            'inventory_item_id' => $cuka->id,
            'quantity' => 40,
        ]);

        // Ship transfer
        $transfer->ship($this->adminUser->id);

        $this->assertEquals('in_transit', $transfer->fresh()->status);
        $this->assertNotNull($transfer->fresh()->shipped_at);
        $this->assertEquals(60, (float) $invA->fresh()->quantity);
        $this->assertEquals(10, (float) $invB->fresh()->quantity); // Not received yet

        // Receive transfer
        $transfer->receive($this->adminUser->id);

        $this->assertEquals('received', $transfer->fresh()->status);
        $this->assertNotNull($transfer->fresh()->received_at);
        $this->assertEquals(60, (float) $invA->fresh()->quantity);
        $this->assertEquals(50, (float) $invB->fresh()->quantity); // 10 + 40 = 50

        // Verify stock movement logs for both branches
        $this->assertDatabaseHas('stock_movements', [
            'branch_id' => $this->branchA->id,
            'inventory_item_id' => $cuka->id,
            'type' => 'transfer_out',
            'quantity' => -40,
            'balance_after' => 60,
        ]);

        $this->assertDatabaseHas('stock_movements', [
            'branch_id' => $this->branchB->id,
            'inventory_item_id' => $cuka->id,
            'type' => 'transfer_in',
            'quantity' => 40,
            'balance_after' => 50,
        ]);
    }

    #[Test]
    public function test_supplier_purchase_receiving_and_moving_average_cost_update(): void
    {
        $supplier = Supplier::create([
            'code' => 'SUP-001',
            'name' => 'Supplier Tepung Sagu Tani',
            'contact_person' => 'Koh Ahong',
            'phone' => '0811223344',
            'status' => 'active',
        ]);

        $sagu = InventoryItem::create([
            'sku' => 'SAGU-01',
            'name' => 'Sagu Tani Asli',
            'item_type' => 'raw_material',
            'unit' => 'kg',
            'cost_per_unit' => 10000,
            'minimum_stock' => 5,
        ]);

        // Branch has 10 kg with cost_per_unit 10.000 (total current valuation = 100.000)
        $inv = BranchInventory::create([
            'branch_id' => $this->branchA->id,
            'inventory_item_id' => $sagu->id,
            'quantity' => 10,
            'minimum_stock' => 5,
        ]);

        $purchase = Purchase::create([
            'purchase_number' => 'PO-' . uniqid(),
            'branch_id' => $this->branchA->id,
            'supplier_id' => $supplier->id,
            'created_by' => $this->adminUser->id,
            'date' => now()->toDateString(),
            'total_amount' => 140000, // 10 kg @ 14.000
            'status' => 'pending',
        ]);

        PurchaseItem::create([
            'purchase_id' => $purchase->id,
            'inventory_item_id' => $sagu->id,
            'quantity' => 10,
            'unit' => 'kg',
            'unit_price' => 14000,
            'subtotal' => 140000,
        ]);

        // Receive PO
        $purchase->receive($this->adminUser->id);

        $this->assertEquals('received', $purchase->fresh()->status);
        $this->assertEquals(20, (float) $inv->fresh()->quantity);

        // Weighted moving average:
        // (10 * 10.000 + 10 * 14.000) / 20 = (100.000 + 140.000) / 20 = 240.000 / 20 = 12.000
        $this->assertEquals(12000, (float) $sagu->fresh()->cost_per_unit);

        $this->assertDatabaseHas('stock_movements', [
            'branch_id' => $this->branchA->id,
            'inventory_item_id' => $sagu->id,
            'type' => 'purchase',
            'quantity' => 10,
            'balance_after' => 20,
        ]);
    }

    #[Test]
    public function test_financial_report_calculations(): void
    {
        // Gross sales 100.000, discount 10.000, cogs 40.000
        Order::create([
            'branch_id' => $this->branchA->id,
            'name' => 'Order Test Financial',
            'whatsapp' => '0812000000',
            'total_amount' => 90000,
            'discount' => 10000,
            'total_cogs' => 40000,
            'status' => 'completed',
            'date' => now()->toDateString(),
            'products' => json_encode([
                [
                    'productId' => 1,
                    'productName' => 'Paket Pempek',
                    'price' => 100000,
                    'quantity' => 1,
                ],
            ]),
        ]);

        // Operating Expense 25.000
        Expense::create([
            'branch_id' => $this->branchA->id,
            'category' => 'utilities',
            'amount' => 25000,
            'date' => now()->toDateString(),
            'description' => 'Listrik cabang',
            'created_by' => $this->adminUser->id,
        ]);

        $response = $this->withSession([
            'admin_authenticated' => true,
            'admin_user_id' => $this->adminUser->id,
        ])->get(route('admin.laporan.keuangan', [
            'branch_id' => $this->branchA->id,
            'start_date' => now()->toDateString(),
            'end_date' => now()->toDateString(),
        ]));

        $response->assertOk();
        $response->assertViewHas('grossSales', 100000);
        $response->assertViewHas('totalDiscount', 10000);
        $response->assertViewHas('netSales', 90000);
        $response->assertViewHas('totalCogs', 40000);
        $response->assertViewHas('grossProfit', 50000); // 90.000 - 40.000
        $response->assertViewHas('totalExpenses', 25000);
        $response->assertViewHas('netProfit', 25000); // 50.000 - 25.000
    }

    #[Test]
    public function test_role_based_permission_enforcement(): void
    {
        $cashier = User::create([
            'name' => 'Cashier Test',
            'email' => 'cashier@test.com',
            'password' => bcrypt('password'),
            'role_id' => $this->cashierRole->id,
            'branch_id' => $this->branchA->id,
            'status' => 'active',
        ]);

        // Cashier attempts to access User Management -> 403 Forbidden
        $response = $this->withSession([
            'admin_authenticated' => true,
            'admin_user_id' => $cashier->id,
        ])->get(route('admin.pengguna.index'));

        $response->assertForbidden();

        // Cashier attempts to access Branch Management -> 403 Forbidden
        $branchResponse = $this->withSession([
            'admin_authenticated' => true,
            'admin_user_id' => $cashier->id,
        ])->get(route('admin.cabang.index'));

        $branchResponse->assertForbidden();

        // Super Admin / Owner can access both without restriction
        $adminResponse = $this->withSession([
            'admin_authenticated' => true,
            'admin_user_id' => $this->adminUser->id,
        ])->get(route('admin.pengguna.index'));

        $adminResponse->assertOk();
    }
}
