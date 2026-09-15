<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained('branches')->cascadeOnDelete();
            $table->string('expense_number')->nullable();
            $table->string('category'); // Gaji, Sewa, Listrik, Air, Gas, Packaging, Transportasi, Pemasaran, Pemeliharaan, Perlengkapan, Lainnya
            $table->unsignedInteger('amount');
            $table->date('date');
            $table->string('payment_method')->default('Tunai');
            $table->text('description')->nullable();
            $table->string('receipt_image')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['branch_id', 'date']);
            $table->index(['category', 'date']);
        });

        Schema::create('stock_transfers', function (Blueprint $table) {
            $table->id();
            $table->string('transfer_number')->unique();
            $table->foreignId('from_branch_id')->constrained('branches')->cascadeOnDelete();
            $table->foreignId('to_branch_id')->constrained('branches')->cascadeOnDelete();
            $table->string('status')->default('draft'); // draft, requested, approved, in_transit, received, cancelled
            $table->date('date');
            $table->dateTime('shipped_at')->nullable();
            $table->dateTime('received_at')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['from_branch_id', 'to_branch_id', 'status']);
        });

        Schema::create('stock_transfer_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stock_transfer_id')->constrained('stock_transfers')->cascadeOnDelete();
            $table->foreignId('inventory_item_id')->nullable()->constrained('inventory_items')->cascadeOnDelete();
            $table->foreignId('product_id')->nullable()->constrained('products')->cascadeOnDelete();
            $table->foreignId('product_variant_id')->nullable()->constrained('product_variants')->cascadeOnDelete();
            $table->decimal('quantity', 12, 2);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_transfer_items');
        Schema::dropIfExists('stock_transfers');
        Schema::dropIfExists('expenses');
    }
};
