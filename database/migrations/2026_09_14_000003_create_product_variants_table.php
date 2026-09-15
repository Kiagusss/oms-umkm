<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->unsignedInteger('cost_price')->default(0)->after('price');
            $table->boolean('has_variants')->default(false)->after('status');
        });

        Schema::create('product_variants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->string('name');
            $table->string('sku')->nullable()->index();
            $table->unsignedInteger('price');
            $table->unsignedInteger('cost_price')->default(0);
            $table->integer('stock')->default(0);
            $table->string('barcode')->nullable();
            $table->json('attributes')->nullable();
            $table->string('image')->nullable();
            $table->string('status')->default('active');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_variants');
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['cost_price', 'has_variants']);
        });
    }
};
