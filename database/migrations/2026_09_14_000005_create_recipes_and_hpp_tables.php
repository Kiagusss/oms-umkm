<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('recipes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->foreignId('product_variant_id')->nullable()->constrained('product_variants')->cascadeOnDelete();
            $table->string('name');
            $table->unsignedInteger('additional_cost')->default(0);
            $table->unsignedInteger('packaging_cost')->default(0);
            $table->unsignedInteger('total_material_cost')->default(0);
            $table->unsignedInteger('total_hpp')->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('recipe_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('recipe_id')->constrained('recipes')->cascadeOnDelete();
            $table->foreignId('inventory_item_id')->constrained('inventory_items')->cascadeOnDelete();
            $table->decimal('quantity', 12, 2);
            $table->string('unit');
            $table->unsignedInteger('cost_per_unit')->default(0);
            $table->unsignedInteger('subtotal')->default(0);
            $table->timestamps();
        });

        Schema::create('hpp_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->foreignId('product_variant_id')->nullable()->constrained('product_variants')->cascadeOnDelete();
            $table->unsignedInteger('cost_price');
            $table->unsignedInteger('material_cost')->default(0);
            $table->unsignedInteger('packaging_cost')->default(0);
            $table->unsignedInteger('additional_cost')->default(0);
            $table->string('source')->default('recipe'); // recipe, manual, purchase_recalc
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['product_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hpp_histories');
        Schema::dropIfExists('recipe_items');
        Schema::dropIfExists('recipes');
    }
};
