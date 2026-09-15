<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\BranchInventory;
use App\Models\InventoryItem;
use App\Models\Permission;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Recipe;
use App\Models\RecipeItem;
use App\Models\Role;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class OmsManagementSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Branches
        $depok = Branch::firstOrCreate(
            ['code' => 'BR-DPK'],
            [
                'name' => 'Depok',
                'address' => 'Jl. Margonda Raya No. 12, Depok',
                'phone' => '08123456701',
                'operating_hours' => '08:00 - 21:00',
                'manager_name' => 'Budi Santoso',
                'status' => 'active',
                'is_main' => true,
            ]
        );

        $jakarta = Branch::firstOrCreate(
            ['code' => 'BR-JKT'],
            [
                'name' => 'Jakarta',
                'address' => 'Jl. Senopati No. 45, Jakarta Selatan',
                'phone' => '08123456702',
                'operating_hours' => '09:00 - 22:00',
                'manager_name' => 'Dewi Lestari',
                'status' => 'active',
                'is_main' => false,
            ]
        );

        $bekasi = Branch::firstOrCreate(
            ['code' => 'BR-BKS'],
            [
                'name' => 'Bekasi',
                'address' => 'Jl. Ahmad Yani No. 88, Bekasi',
                'phone' => '08123456703',
                'operating_hours' => '08:00 - 20:00',
                'manager_name' => 'Rian Hidayat',
                'status' => 'active',
                'is_main' => false,
            ]
        );

        // 2. Roles & Permissions
        $rolesData = [
            'owner' => ['label' => 'Owner', 'description' => 'Pemilik bisnis dengan akses penuh ke seluruh cabang dan data keuangan'],
            'manager' => ['label' => 'Manager', 'description' => 'Manajer cabang untuk operasional, produk, inventori, dan pesanan'],
            'cashier' => ['label' => 'Kasir', 'description' => 'Petugas kasir POS dan layanan pelanggan'],
            'warehouse' => ['label' => 'Staff Gudang', 'description' => 'Staff pengelola inventori, transfer stok, dan penerimaan barang'],
        ];

        $roles = [];
        foreach ($rolesData as $name => $data) {
            $roles[$name] = Role::firstOrCreate(['name' => $name], $data);
        }

        $permissionsData = [
            // Branches
            ['name' => 'branch.view', 'label' => 'Lihat Cabang', 'group' => 'Cabang'],
            ['name' => 'branch.manage', 'label' => 'Kelola Cabang', 'group' => 'Cabang'],
            // Products & Variants
            ['name' => 'product.view', 'label' => 'Lihat Produk', 'group' => 'Produk'],
            ['name' => 'product.manage', 'label' => 'Kelola Produk & Varian', 'group' => 'Produk'],
            // Inventory & Stock
            ['name' => 'inventory.view', 'label' => 'Lihat Inventori', 'group' => 'Inventori'],
            ['name' => 'inventory.manage', 'label' => 'Kelola Inventori', 'group' => 'Inventori'],
            ['name' => 'inventory.adjust', 'label' => 'Penyesuaian Stok', 'group' => 'Inventori'],
            ['name' => 'inventory.transfer', 'label' => 'Transfer Antar Cabang', 'group' => 'Inventori'],
            ['name' => 'transfer.manage', 'label' => 'Kelola Transfer Cabang', 'group' => 'Inventori'],
            // Recipe & HPP
            ['name' => 'recipe.view', 'label' => 'Lihat Resep & HPP', 'group' => 'HPP & Resep'],
            ['name' => 'recipe.manage', 'label' => 'Kelola Resep & HPP', 'group' => 'HPP & Resep'],
            // Purchasing & Supplier
            ['name' => 'purchase.view', 'label' => 'Lihat Pembelian & Supplier', 'group' => 'Pembelian'],
            ['name' => 'purchase.manage', 'label' => 'Kelola Pembelian & Supplier', 'group' => 'Pembelian'],
            // Expenses
            ['name' => 'expense.view', 'label' => 'Lihat Pengeluaran', 'group' => 'Keuangan'],
            ['name' => 'expense.manage', 'label' => 'Kelola Pengeluaran', 'group' => 'Keuangan'],
            // Reports
            ['name' => 'report.view', 'label' => 'Lihat Laporan Penjualan & Stok', 'group' => 'Laporan'],
            ['name' => 'report.profit', 'label' => 'Lihat Laporan Profit & Margin', 'group' => 'Laporan'],
            // POS
            ['name' => 'pos.access', 'label' => 'Akses POS Kasir', 'group' => 'POS'],
            // User management & Settings
            ['name' => 'user.manage', 'label' => 'Kelola Pengguna & Akses', 'group' => 'Pengguna'],
            ['name' => 'settings.manage', 'label' => 'Kelola Pengaturan Sistem', 'group' => 'Sistem'],
        ];

        $permissions = [];
        foreach ($permissionsData as $p) {
            $permissions[$p['name']] = Permission::firstOrCreate(['name' => $p['name']], $p);
        }

        // Attach permissions to roles
        $roles['owner']->permissions()->sync(collect($permissions)->pluck('id'));

        $roles['manager']->permissions()->sync(
            collect($permissions)->filter(fn($p, $k) => !in_array($k, ['user.manage', 'settings.manage']))->pluck('id')
        );

        $roles['cashier']->permissions()->sync([
            $permissions['pos.access']->id,
            $permissions['product.view']->id,
        ]);

        $roles['warehouse']->permissions()->sync([
            $permissions['inventory.view']->id,
            $permissions['inventory.manage']->id,
            $permissions['inventory.adjust']->id,
            $permissions['inventory.transfer']->id,
            $permissions['transfer.manage']->id,
            $permissions['purchase.view']->id,
            $permissions['purchase.manage']->id,
            $permissions['product.view']->id,
        ]);

        // 3. Default Users
        User::firstOrCreate(
            ['email' => 'owner@pempek.com'],
            [
                'name' => 'Owner Pempek',
                'password' => Hash::make('password'),
                'role_id' => $roles['owner']->id,
                'branch_id' => null, // all branches
                'status' => 'active',
            ]
        );

        User::firstOrCreate(
            ['email' => 'manager@pempek.com'],
            [
                'name' => 'Manajer Depok',
                'password' => Hash::make('password'),
                'role_id' => $roles['manager']->id,
                'branch_id' => $depok->id,
                'status' => 'active',
            ]
        );

        User::firstOrCreate(
            ['email' => 'kasir@pempek.com'],
            [
                'name' => 'Kasir Depok',
                'password' => Hash::make('password'),
                'role_id' => $roles['cashier']->id,
                'branch_id' => $depok->id,
                'status' => 'active',
            ]
        );

        User::firstOrCreate(
            ['email' => 'gudang@pempek.com'],
            [
                'name' => 'Staff Gudang Depok',
                'password' => Hash::make('password'),
                'role_id' => $roles['warehouse']->id,
                'branch_id' => $depok->id,
                'status' => 'active',
            ]
        );

        // 4. Suppliers
        $supFish = Supplier::firstOrCreate(
            ['code' => 'SUP-001'],
            [
                'name' => 'Supplier Ikan Jaya',
                'contact_person' => 'H. Sukardi',
                'phone' => '08111223344',
                'email' => 'ikanjaya@supplier.com',
                'address' => 'Muara Baru, Jakarta Utara',
                'status' => 'active',
            ]
        );

        $supFlour = Supplier::firstOrCreate(
            ['code' => 'SUP-002'],
            [
                'name' => 'Toko Bahan Kue Sinar',
                'contact_person' => 'Ko Hendra',
                'phone' => '08122334455',
                'email' => 'tokosinar@supplier.com',
                'address' => 'Pasar Minggu, Jakarta Selatan',
                'status' => 'active',
            ]
        );

        // 5. Raw Materials (Inventory Items)
        $items = [
            ['sku' => 'RAW-IKAN', 'name' => 'Daging Ikan Tenggiri Giling', 'item_type' => 'raw_material', 'unit' => 'g', 'cost_per_unit' => 50, 'minimum_stock' => 5000],
            ['sku' => 'RAW-SAGU', 'name' => 'Tepung Sagu Tani', 'item_type' => 'raw_material', 'unit' => 'g', 'cost_per_unit' => 15, 'minimum_stock' => 5000],
            ['sku' => 'RAW-TELUR', 'name' => 'Telur Ayam', 'item_type' => 'raw_material', 'unit' => 'unit', 'cost_per_unit' => 2000, 'minimum_stock' => 50],
            ['sku' => 'RAW-MINYAK', 'name' => 'Minyak Goreng Sawit', 'item_type' => 'raw_material', 'unit' => 'ml', 'cost_per_unit' => 18, 'minimum_stock' => 2000],
            ['sku' => 'RAW-CUKO', 'name' => 'Cuko Pempek Asli', 'item_type' => 'raw_material', 'unit' => 'ml', 'cost_per_unit' => 25, 'minimum_stock' => 3000],
            ['sku' => 'PKG-KOTAK', 'name' => 'Kotak Mika + Plastik Vacuum', 'item_type' => 'packaging', 'unit' => 'unit', 'cost_per_unit' => 1200, 'minimum_stock' => 100],
        ];

        $invItems = [];
        foreach ($items as $itemData) {
            $invItems[$itemData['sku']] = InventoryItem::firstOrCreate(['sku' => $itemData['sku']], $itemData);

            // Seed initial stock across branches
            BranchInventory::firstOrCreate(
                ['branch_id' => $depok->id, 'inventory_item_id' => $invItems[$itemData['sku']]->id],
                ['quantity' => 10000, 'minimum_stock' => $itemData['minimum_stock']]
            );
            BranchInventory::firstOrCreate(
                ['branch_id' => $jakarta->id, 'inventory_item_id' => $invItems[$itemData['sku']]->id],
                ['quantity' => 5000, 'minimum_stock' => $itemData['minimum_stock']]
            );
        }

        // 6. Connect recipes and variants to existing products
        $products = Product::all();
        foreach ($products as $prod) {
            // Seed branch inventories for finished products
            BranchInventory::firstOrCreate(
                ['branch_id' => $depok->id, 'product_id' => $prod->id, 'product_variant_id' => null],
                ['quantity' => 20, 'minimum_stock' => 5]
            );
            BranchInventory::firstOrCreate(
                ['branch_id' => $jakarta->id, 'product_id' => $prod->id, 'product_variant_id' => null],
                ['quantity' => 5, 'minimum_stock' => 5]
            );

            // Add sample recipe if Kapal Selam or Lenjer
            if (str_contains(strtolower($prod->name), 'kapal selam') || str_contains(strtolower($prod->name), 'lenjer')) {
                $prod->update(['has_variants' => true]);

                // Create variants: Regular & Jumbo
                $vRegular = ProductVariant::firstOrCreate(
                    ['product_id' => $prod->id, 'name' => 'Regular'],
                    [
                        'sku' => 'PL-REG',
                        'price' => (int) $prod->price,
                        'cost_price' => 8950,
                        'stock' => 25,
                        'attributes' => ['Ukuran' => 'Regular', 'Sajian' => 'Goreng'],
                    ]
                );

                $vJumbo = ProductVariant::firstOrCreate(
                    ['product_id' => $prod->id, 'name' => 'Jumbo'],
                    [
                        'sku' => 'PL-JMB',
                        'price' => (int) ($prod->price * 1.5),
                        'cost_price' => 13450,
                        'stock' => 15,
                        'attributes' => ['Ukuran' => 'Jumbo', 'Sajian' => 'Goreng'],
                    ]
                );

                // Branch stock for variants: Depok = 20, Jakarta = 5
                BranchInventory::firstOrCreate(
                    ['branch_id' => $depok->id, 'product_id' => $prod->id, 'product_variant_id' => $vRegular->id],
                    ['quantity' => 20, 'minimum_stock' => 5]
                );
                BranchInventory::firstOrCreate(
                    ['branch_id' => $jakarta->id, 'product_id' => $prod->id, 'product_variant_id' => $vRegular->id],
                    ['quantity' => 5, 'minimum_stock' => 5]
                );
                BranchInventory::firstOrCreate(
                    ['branch_id' => $depok->id, 'product_id' => $prod->id, 'product_variant_id' => $vJumbo->id],
                    ['quantity' => 20, 'minimum_stock' => 5]
                );
                BranchInventory::firstOrCreate(
                    ['branch_id' => $jakarta->id, 'product_id' => $prod->id, 'product_variant_id' => $vJumbo->id],
                    ['quantity' => 5, 'minimum_stock' => 5]
                );

                // Base recipe
                $recipe = Recipe::firstOrCreate(
                    ['product_id' => $prod->id, 'product_variant_id' => null],
                    [
                        'name' => 'Resep ' . $prod->name,
                        'additional_cost' => 1500, // gas & tenaga
                        'packaging_cost' => 1200,
                    ]
                );

                // Recipe items: 100g fish, 50g sagu, 1 egg, 20ml oil, 50ml cuko, 1 packaging
                RecipeItem::firstOrCreate(
                    ['recipe_id' => $recipe->id, 'inventory_item_id' => $invItems['RAW-IKAN']->id],
                    ['quantity' => 100, 'unit' => 'g', 'cost_per_unit' => 50, 'subtotal' => 5000]
                );
                RecipeItem::firstOrCreate(
                    ['recipe_id' => $recipe->id, 'inventory_item_id' => $invItems['RAW-SAGU']->id],
                    ['quantity' => 50, 'unit' => 'g', 'cost_per_unit' => 15, 'subtotal' => 750]
                );
                RecipeItem::firstOrCreate(
                    ['recipe_id' => $recipe->id, 'inventory_item_id' => $invItems['RAW-TELUR']->id],
                    ['quantity' => 1, 'unit' => 'unit', 'cost_per_unit' => 2000, 'subtotal' => 2000]
                );
                RecipeItem::firstOrCreate(
                    ['recipe_id' => $recipe->id, 'inventory_item_id' => $invItems['RAW-MINYAK']->id],
                    ['quantity' => 20, 'unit' => 'ml', 'cost_per_unit' => 18, 'subtotal' => 360]
                );
                RecipeItem::firstOrCreate(
                    ['recipe_id' => $recipe->id, 'inventory_item_id' => $invItems['RAW-CUKO']->id],
                    ['quantity' => 50, 'unit' => 'ml', 'cost_per_unit' => 25, 'subtotal' => 1250]
                );

                $recipe->recalculateHpp(null, 'initial_seed');
            } elseif ($prod->cost_price === 0) {
                // Approximate 45% default HPP for existing products
                $prod->update(['cost_price' => (int) round($prod->price * 0.45)]);
            }
        }
    }
}
