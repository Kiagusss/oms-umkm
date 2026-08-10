<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Categories
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('icon')->nullable();
            $table->integer('ord')->default(0);
            $table->string('status')->default('active');
            $table->timestamps();
        });

        // Products
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->unsignedBigInteger('category_id')->nullable();
            $table->foreign('category_id')->references('id')->on('categories')->nullOnDelete();
            $table->integer('price');
            $table->integer('price_strikethrough')->nullable();
            $table->text('short_description');
            $table->longText('description')->nullable();
            $table->string('composition')->nullable();
            $table->integer('stock')->default(0);
            $table->integer('weight')->default(0);
            $table->string('thumbnail')->nullable();
            $table->json('images')->nullable();
            $table->boolean('is_best_seller')->default(false);
            $table->boolean('is_featured')->default(false);
            $table->integer('ord')->default(0);
            $table->string('status')->default('active');
            $table->timestamps();
            $table->index(['status', 'category_id', 'is_featured']);
        });

        // Packages (Paket Hemat)
        Schema::create('packages', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description');
            $table->json('items')->nullable(); // [{quantity, name}]
            $table->integer('price');
            $table->integer('original_price')->nullable();
            $table->string('badge')->nullable();
            $table->boolean('is_featured')->default(false);
            $table->integer('ord')->default(0);
            $table->string('status')->default('active');
            $table->timestamps();
        });

        // Articles
        Schema::create('articles', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->string('thumbnail')->nullable();
            $table->string('category')->nullable();
            $table->longText('content');
            $table->string('author')->nullable();
            $table->date('date');
            $table->string('seo_title')->nullable();
            $table->string('seo_description')->nullable();
            $table->string('meta_keywords')->nullable();
            $table->string('status')->default('draft');
            $table->timestamps();
            $table->index(['status', 'date', 'category']);
        });

        // Testimonials
        Schema::create('testimonials', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('avatar')->nullable();
            $table->string('kombinasi')->nullable();
            $table->unsignedTinyInteger('rating')->default(3);
            $table->text('comment');
            $table->string('date')->nullable();
            $table->string('status')->default('active');
            $table->integer('ord')->default(0);
            $table->timestamps();
        });

        // FAQs
        Schema::create('faqs', function (Blueprint $table) {
            $table->id();
            $table->string('question');
            $table->text('answer');
            $table->string('status')->default('active');
            $table->integer('ord')->default(0);
            $table->timestamps();
        });

        // Banners
        Schema::create('banners', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('subtitle');
            $table->string('button_text')->nullable();
            $table->string('button_link')->nullable();
            $table->string('background_image');
            $table->string('status')->default('inactive');
            $table->integer('ord')->default(0);
            $table->timestamps();
        });

        // Gallery items
        Schema::create('gallery_items', function (Blueprint $table) {
            $table->id();
            $table->string('image')->nullable();
            $table->text('caption')->nullable();
            $table->integer('ord')->default(0);
            $table->timestamps();
        });

        // Order
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('whatsapp');
            $table->text('products')->nullable(); // JSON: [{id, name, quantity, price, thumbnail}]
            $table->text('notes')->nullable();
            $table->date('date');
            $table->string('status')->default('pending'); // pending, confirmed, completed, cancelled
            $table->timestamps();
            $table->index(['status', 'date']);
        });

        // Page Views // Visitor Tracker
        Schema::create('page_views', function (Blueprint $table) {
            $table->id();
            $table->string('path');
            $table->string('ip');
            $table->string('user_agent')->nullable();
            $table->timestamps();
            $table->index(['path', 'created_at']);
        });

        // Settings
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->longText('value')->nullable();
            $table->timestamps();
        });

        // Sessions (untuk login admin)
        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });

        // Cache (rate limiter, dll)
        Schema::create('cache', function (Blueprint $table) {
            $table->string('key')->primary();
            $table->mediumText('value');
            $table->integer('expiration');
        });

        // SEO
        Schema::create('seos', function (Blueprint $table) {
            $table->id();
            $table->string('site_name')->default('Pempek Palembang');
            $table->string('site_url')->nullable();
            $table->string('default_title');
            $table->string('default_description');
            $table->string('favicon')->nullable();
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->text('keywords')->nullable();
            $table->string('og_image')->nullable();
            $table->string('canonical_url')->nullable();
            $table->string('robots')->default('index, follow');
            $table->string('google_verification')->nullable();
            $table->text('schema_json_ld')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('seos');
        Schema::dropIfExists('settings');
        Schema::dropIfExists('page_views');
        Schema::dropIfExists('sessions');
        Schema::dropIfExists('cache');
        Schema::dropIfExists('orders');
        Schema::dropIfExists('gallery_items');
        Schema::dropIfExists('banners');
        Schema::dropIfExists('faqs');
        Schema::dropIfExists('testimonials');
        Schema::dropIfExists('articles');
        Schema::dropIfExists('packages');
        Schema::dropIfExists('categories');
        Schema::dropIfExists('products');
    }
};