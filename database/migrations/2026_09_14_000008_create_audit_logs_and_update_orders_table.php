<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('action'); // create, update, delete, stock_adjust, transfer, price_change, login
            $table->string('auditable_type');
            $table->unsignedBigInteger('auditable_id')->nullable();
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['auditable_type', 'auditable_id']);
            $table->index(['action', 'created_at']);
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->foreignId('branch_id')->nullable()->after('id')->constrained('branches')->nullOnDelete();
            $table->unsignedInteger('total_cogs')->default(0)->after('discount');
            $table->integer('gross_profit')->default(0)->after('total_cogs');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropConstrainedForeignId('branch_id');
            $table->dropColumn(['total_cogs', 'gross_profit']);
        });
        Schema::dropIfExists('audit_logs');
    }
};
