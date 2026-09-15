<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventory_items', function (Blueprint $table) {
            $table->id();
            $table->string('sku')->unique();
            $table->string('name');
            $table->string('item_type')->default('raw_material'); // raw_material, finished_product, packaging, other
            $table->string('unit')->default('pcs'); // kg, g, ml, liter, pcs, unit
            $table->unsignedInteger('cost_per_unit')->default(0);
            $table->decimal('minimum_stock', 12, 2)->default(0);
            $table->string('status')->default('active');
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('branch_inventories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained('branches')->cascadeOnDelete();
            $table->foreignId('inventory_item_id')->nullable()->constrained('inventory_items')->cascadeOnDelete();
            $table->foreignId('product_id')->nullable()->constrained('products')->cascadeOnDelete();
            $table->foreignId('product_variant_id')->nullable()->constrained('product_variants')->cascadeOnDelete();
            $table->decimal('quantity', 12, 2)->default(0);
            $table->decimal('minimum_stock', 12, 2)->default(0);
            $table->timestamps();

            $table->index(['branch_id', 'inventory_item_id']);
            $table->index(['branch_id', 'product_id', 'product_variant_id']);
        });

        Schema::create('stock_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained('branches')->cascadeOnDelete();
            $table->foreignId('inventory_item_id')->nullable()->constrained('inventory_items')->nullOnDelete();
            $table->foreignId('product_id')->nullable()->constrained('products')->nullOnDelete();
            $table->foreignId('product_variant_id')->nullable()->constrained('product_variants')->nullOnDelete();
            $table->string('type'); // purchase, sale, sale_return, purchase_return, adjustment, damaged, expired, transfer_out, transfer_in, production, initial
            $table->decimal('quantity', 12, 2); // can be negative or positive
            $table->decimal('balance_after', 12, 2);
            $table->string('reference_type')->nullable();
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['branch_id', 'created_at']);
            $table->index(['type', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_movements');
        Schema::dropIfExists('branch_inventories');
        Schema::dropIfExists('inventory_items');
    }
};
