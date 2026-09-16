<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\Store;
use App\Models\User;
use App\Services\Tenant\TenantContext;
use App\Services\TransactionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use InvalidArgumentException;
use Tests\TestCase;

class MultiTenantStoreTest extends TestCase
{
    use RefreshDatabase;

    private Store $storeA;
    private Store $storeB;

    protected function setUp(): void
    {
        parent::setUp();

        // Create two distinct stores
        $this->storeA = Store::create([
            'name' => 'Pempek Palembang Asli',
            'slug' => 'pempek-palembang-asli',
            'city' => 'Palembang',
            'phone' => '081234567890',
            'email' => 'tokoa@pempek.com',
            'status' => 'active',
        ]);

        $this->storeB = Store::create([
            'name' => 'Pempek Beringin Jaya',
            'slug' => 'pempek-beringin-jaya',
            'city' => 'Jakarta',
            'phone' => '081298765432',
            'email' => 'tokob@pempek.com',
            'status' => 'active',
        ]);
    }

    public function test_public_stores_directory_can_be_rendered(): void
    {
        $response = $this->get('/stores');
        $response->assertStatus(200);
        $response->assertSee('Pempek Palembang Asli');
        $response->assertSee('Pempek Beringin Jaya');
    }

    public function test_storefront_displays_store_specific_products(): void
    {
        // Product in Store A
        $prodA = Product::create([
            'store_id' => $this->storeA->id,
            'name' => 'Kapal Selam Spesial A',
            'slug' => 'kapal-selam-spesial-a',
            'price' => 25000,
            'stock' => 15,
            'status' => 'active',
            'short_description' => 'Deskripsi A',
            'sold' => 0,
        ]);

        // Product in Store B
        $prodB = Product::create([
            'store_id' => $this->storeB->id,
            'name' => 'Lenjer Super B',
            'slug' => 'lenjer-super-b',
            'price' => 20000,
            'stock' => 20,
            'status' => 'active',
            'short_description' => 'Deskripsi B',
            'sold' => 0,
        ]);

        // Visit storefront A
        $resA = $this->get('/store/' . $this->storeA->slug);
        $resA->assertStatus(200);
        $resA->assertSee('Kapal Selam Spesial A');
        $resA->assertDontSee('Lenjer Super B');

        // Visit storefront B
        $resB = $this->get('/store/' . $this->storeB->slug);
        $resB->assertStatus(200);
        $resB->assertSee('Lenjer Super B');
        $resB->assertDontSee('Kapal Selam Spesial A');
    }

    public function test_product_detail_shows_store_context(): void
    {
        $prod = Product::create([
            'store_id' => $this->storeA->id,
            'name' => 'Adaan Crispy A',
            'slug' => 'adaan-crispy-a',
            'price' => 15000,
            'stock' => 10,
            'status' => 'active',
            'short_description' => 'Adaan renyah',
            'sold' => 0,
        ]);

        $response = $this->get("/store/{$this->storeA->slug}/produk/{$prod->slug}");
        $response->assertStatus(200);
        $response->assertSee('Adaan Crispy A');
        $response->assertSee($this->storeA->name);
    }

    public function test_marketplace_search_finds_products_across_stores(): void
    {
        Product::create([
            'store_id' => $this->storeA->id,
            'name' => 'Pempek Kulit Spesial',
            'slug' => 'pempek-kulit-spesial-a',
            'price' => 18000,
            'stock' => 10,
            'status' => 'active',
            'short_description' => 'Kulit enak',
            'sold' => 0,
        ]);

        Product::create([
            'store_id' => $this->storeB->id,
            'name' => 'Pempek Kulit Gurih',
            'slug' => 'pempek-kulit-gurih-b',
            'price' => 19000,
            'stock' => 12,
            'status' => 'active',
            'short_description' => 'Kulit gurih',
            'sold' => 0,
        ]);

        $response = $this->get('/search?q=Kulit');
        $response->assertStatus(200);
        $response->assertSee('Pempek Kulit Spesial');
        $response->assertSee('Pempek Kulit Gurih');
    }

    public function test_tenant_context_isolates_product_creation_and_scoping(): void
    {
        TenantContext::setStoreId($this->storeB->id);

        $product = Product::create([
            'name' => 'Pempek Keriting B',
            'slug' => 'pempek-keriting-b',
            'price' => 12000,
            'stock' => 10,
            'status' => 'active',
            'short_description' => 'Keriting mantap',
            'sold' => 0,
        ]);

        $this->assertEquals($this->storeB->id, $product->store_id);

        $storeAProducts = Product::forStore($this->storeA->id)->pluck('slug')->all();
        $this->assertNotContains('pempek-keriting-b', $storeAProducts);

        $storeBProducts = Product::forStore($this->storeB->id)->pluck('slug')->all();
        $this->assertContains('pempek-keriting-b', $storeBProducts);

        TenantContext::reset();
    }

    public function test_single_store_cart_constraint_allows_same_store(): void
    {
        $p1 = Product::create([
            'store_id' => $this->storeA->id,
            'name' => 'Item 1',
            'slug' => 'item-1',
            'price' => 10000,
            'stock' => 10,
            'status' => 'active',
            'short_description' => 'Item 1 desc',
            'sold' => 0,
        ]);

        $p2 = Product::create([
            'store_id' => $this->storeA->id,
            'name' => 'Item 2',
            'slug' => 'item-2',
            'price' => 15000,
            'stock' => 10,
            'status' => 'active',
            'short_description' => 'Item 2 desc',
            'sold' => 0,
        ]);

        $service = app(TransactionService::class);
        $result = $service->calculate([
            ['id' => $p1->id, 'quantity' => 1],
            ['id' => $p2->id, 'quantity' => 2],
        ]);

        $this->assertEquals(40000, $result['subtotal']);
    }

    public function test_single_store_cart_constraint_rejects_mixed_store_items(): void
    {
        $pA = Product::create([
            'store_id' => $this->storeA->id,
            'name' => 'Item Store A',
            'slug' => 'item-store-a',
            'price' => 10000,
            'stock' => 10,
            'status' => 'active',
            'short_description' => 'Item Store A desc',
            'sold' => 0,
        ]);

        $pB = Product::create([
            'store_id' => $this->storeB->id,
            'name' => 'Item Store B',
            'slug' => 'item-store-b',
            'price' => 15000,
            'stock' => 10,
            'status' => 'active',
            'short_description' => 'Item Store B desc',
            'sold' => 0,
        ]);

        $service = app(TransactionService::class);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Semua produk dalam satu pesanan harus berasal dari toko yang sama.');

        $service->calculate([
            ['id' => $pA->id, 'quantity' => 1],
            ['id' => $pB->id, 'quantity' => 1],
        ]);
    }

    public function test_admin_platform_stores_management_and_switching(): void
    {
        $this->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\PreventRequestForgery::class);
        $this->withSession(['admin_authenticated' => true]);

        // List stores
        $res = $this->get(route('admin.platform.index'));
        $res->assertStatus(200);
        $res->assertSee($this->storeA->name);
        $res->assertSee($this->storeB->name);

        // Switch store
        $resSwitch = $this->post(route('admin.platform.switch'), [
            'store_id' => $this->storeB->id,
        ]);
        $resSwitch->assertSessionHas('selected_store_id', $this->storeB->id);

        // Create new store
        $resCreate = $this->post(route('admin.platform.store'), [
            'name' => 'Pempek Candy Baru',
            'slug' => 'pempek-candy-baru',
            'city' => 'Palembang',
        ]);
        $resCreate->assertRedirect(route('admin.platform.index'));
        $this->assertDatabaseHas('stores', ['slug' => 'pempek-candy-baru']);

        // Toggle status
        $resToggle = $this->post(route('admin.platform.toggle', $this->storeB->id));
        $resToggle->assertRedirect();
        $this->assertEquals('suspended', $this->storeB->fresh()->status);
    }
}
