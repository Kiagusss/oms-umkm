<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('catalog_imports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->constrained('stores')->cascadeOnDelete();
            $table->unsignedBigInteger('user_id')->nullable()->index();
            $table->string('source_type'); // 'mock', 'marketplace', 'csv', 'json'
            $table->text('source_url')->nullable();
            $table->string('status')->default('pending')->index(); // pending, processing, completed, partial, failed, cancelled
            $table->integer('total_found')->default(0);
            $table->integer('imported_count')->default(0);
            $table->integer('skipped_count')->default(0);
            $table->integer('failed_count')->default(0);
            $table->json('summary')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamps();
        });

        Schema::create('catalog_import_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('catalog_import_id')->constrained('catalog_imports')->cascadeOnDelete();
            $table->string('external_id')->nullable()->index();
            $table->string('sku')->nullable()->index();
            $table->string('name');
            $table->string('category_name')->nullable();
            $table->bigInteger('price')->default(0);
            $table->string('action_taken')->default('created'); // created, updated, skipped, failed
            $table->string('status')->default('success'); // success, skipped, failed
            $table->text('error_message')->nullable();
            $table->json('raw_data')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('catalog_import_items');
        Schema::dropIfExists('catalog_imports');
    }
};
