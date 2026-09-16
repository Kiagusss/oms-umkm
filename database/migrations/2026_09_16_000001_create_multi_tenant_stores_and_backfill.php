<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Create stores table
        Schema::create('stores', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('owner_id')->nullable()->index();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('logo')->nullable();
            $table->string('banner')->nullable();
            $table->string('phone')->nullable();
            $table->string('whatsapp')->nullable();
            $table->string('email')->nullable();
            $table->text('address')->nullable();
            $table->string('city')->nullable();
            $table->string('province')->nullable();
            $table->string('postal_code')->nullable();
            $table->string('status')->default('active')->index(); // draft, active, suspended
            $table->boolean('is_featured')->default(false);
            $table->json('settings')->nullable();
            $table->timestamps();
        });

        // 2. Create store_members table
        Schema::create('store_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->constrained('stores')->cascadeOnDelete();
            $table->unsignedBigInteger('user_id')->index();
            $table->string('role')->default('owner'); // owner, manager, staff
            $table->string('status')->default('active'); // active, inactive
            $table->timestamps();

            $table->unique(['store_id', 'user_id']);
        });

        // 3. Create default store for backward compatibility
        $defaultStoreId = DB::table('stores')->insertGetId([
            'name' => 'Pempek UMKM',
            'slug' => 'pempek-umkm',
            'description' => 'Toko resmi aneka pempek asli Palembang dan kuliner nusantara fresh setiap hari.',
            'phone' => '081234567890',
            'whatsapp' => '6281234567890',
            'email' => 'toko@pempek-umkm.com',
            'city' => 'Palembang',
            'province' => 'Sumatera Selatan',
            'status' => 'active',
            'is_featured' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 4. Add store_id to existing tenant-scoped tables
        $tables = [
            'products',
            'categories',
            'orders',
            'branches',
            'vouchers',
            'inventory_items',
            'recipes',
            'suppliers',
            'expenses',
            'stock_transfers',
            'banners',
            'articles',
            'users',
        ];

        foreach ($tables as $tbl) {
            if (Schema::hasTable($tbl) && !Schema::hasColumn($tbl, 'store_id')) {
                Schema::table($tbl, function (Blueprint $table) use ($tbl) {
                    $table->unsignedBigInteger('store_id')->nullable()->index()->after('id');
                });

                // Backfill existing data with default store
                DB::table($tbl)->whereNull('store_id')->update(['store_id' => $defaultStoreId]);
            }
        }
    }

    public function down(): void
    {
        $tables = [
            'products',
            'categories',
            'orders',
            'branches',
            'vouchers',
            'inventory_items',
            'recipes',
            'suppliers',
            'expenses',
            'stock_transfers',
            'banners',
            'articles',
            'users',
        ];

        foreach ($tables as $tbl) {
            if (Schema::hasTable($tbl) && Schema::hasColumn($tbl, 'store_id')) {
                Schema::table($tbl, function (Blueprint $table) {
                    $table->dropColumn('store_id');
                });
            }
        }

        Schema::dropIfExists('store_members');
        Schema::dropIfExists('stores');
    }
};
