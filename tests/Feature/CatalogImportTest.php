<?php

namespace Tests\Feature;

use App\Models\CatalogImport;
use App\Models\CatalogImportItem;
use App\Models\Category;
use App\Models\Product;
use App\Models\Store;
use App\Services\Import\CatalogImportService;
use App\Services\Import\DTO\CategoryData;
use App\Services\Import\DTO\ProductData;
use App\Services\Import\DTO\StoreData;
use App\Services\Import\DTO\VariantData;
use App\Services\Import\ImporterFactory;
use App\Services\Import\Importers\MarketplaceImporter;
use App\Services\Import\Importers\MockImporter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use InvalidArgumentException;
use Tests\TestCase;

class CatalogImportTest extends TestCase
{
    use RefreshDatabase;

    private Store $store;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\PreventRequestForgery::class);
        $this->withSession(['admin_authenticated' => true]);

        $this->store = Store::create([
            'name' => 'Toko Uji Import',
            'slug' => 'toko-uji-import',
            'city' => 'Palembang',
            'status' => 'active',
        ]);
    }

    public function test_mock_importer_produces_exact_authentic_catalog_dataset(): void
    {
        $importer = new MockImporter();
        $data = $importer->fetchAndNormalize('mock');
        $products = $data['products'];
        $categories = $data['categories'];

        // 47 products
        $this->assertCount(47, $products);

        // 8 categories
        $this->assertCount(8, $categories);

        // Calculate total variants & images
        $totalVariants = 0;
        $totalImages = 0;
        foreach ($products as $p) {
            $totalVariants += count($p->variants);
            $totalImages += count($p->images);
        }

        $this->assertEquals(25, $totalVariants);
        $this->assertEquals(117, $totalImages);
    }

    public function test_marketplace_importer_blocks_ssrf_attacks(): void
    {
        $importer = new MarketplaceImporter();

        $dangerousUrls = [
            'http://localhost/test',
            'http://127.0.0.1:8000/api',
            'http://127.0.0.1',
            'http://0.0.0.0:80',
            'http://192.168.1.100/admin',
            'http://10.0.0.5/secret',
            'http://172.20.0.1/metadata',
            'http://169.254.169.254/latest/meta-data/',
        ];

        foreach ($dangerousUrls as $url) {
            $validation = $importer->validateSource($url);
            $this->assertFalse(
                $validation['valid'],
                "Failed asserting that {$url} is blocked by SSRF check"
            );
        }

        // Public URLs must be allowed
        $this->assertTrue($importer->validateSource('https://google.com')['valid']);
        $this->assertTrue($importer->validateSource('https://shopee.co.id/toko')['valid']);
        $this->assertTrue($importer->validateSource('https://tokopedia.com/toko')['valid']);
    }

    public function test_catalog_import_service_detects_duplicates(): void
    {
        // Existing product in store
        Product::create([
            'store_id' => $this->store->id,
            'name' => 'Pempek Lenjer Asli',
            'slug' => 'pempek-lenjer-asli',
            'sku' => 'PMPK-LNJ-01',
            'price' => 15000,
            'stock' => 10,
            'status' => 'active',
            'short_description' => 'Asli',
            'sold' => 0,
        ]);

        $incoming = [
            new ProductData(
                title: 'Pempek Lenjer Asli',
                description: 'Deskripsi baru',
                price: 18000,
                stock: 25,
                sku: 'PMPK-LNJ-01',
                categoryName: 'Pempek Basah'
            ),
            new ProductData(
                title: 'Pempek Keriting Fresh',
                description: 'Deskripsi keriting',
                price: 12000,
                stock: 30,
                sku: 'PMPK-KRT-99',
                categoryName: 'Pempek Basah'
            ),
        ];

        $service = app(CatalogImportService::class);
        $analysis = $service->analyzeDuplicates($this->store->id, $incoming);

        $this->assertEquals(2, $analysis['total_incoming']);
        $this->assertEquals(1, $analysis['duplicates_count']);
        $this->assertEquals(1, $analysis['new_count']);
        $this->assertNotEmpty($analysis['duplicate_items']);
        $this->assertContains($analysis['duplicate_items'][0]['match_type'], ['sku', 'name']);
    }

    public function test_catalog_import_execution_with_skip_strategy(): void
    {
        // Existing product
        $existing = Product::create([
            'store_id' => $this->store->id,
            'name' => 'Pempek Kapal Selam Sedang',
            'slug' => 'pempek-kapal-selam-sedang',
            'sku' => 'PMPK-KS-01',
            'price' => 20000,
            'stock' => 10,
            'status' => 'active',
            'short_description' => 'Lama',
            'sold' => 0,
        ]);

        $incoming = [
            // Duplicate
            new ProductData(
                title: 'Pempek Kapal Selam Sedang',
                description: 'Baru',
                price: 25000,
                stock: 50,
                sku: 'PMPK-KS-01',
            ),
            // New
            new ProductData(
                title: 'Pempek Panggang Enak',
                description: 'Panggang',
                price: 10000,
                stock: 20,
                sku: 'PMPK-PNG-01',
            ),
        ];

        $service = app(CatalogImportService::class);
        $importRecord = $service->execute($this->store->id, 'mock', $incoming, 'skip');

        $this->assertEquals('completed', $importRecord->status);
        $this->assertEquals(1, $importRecord->items_created);
        $this->assertEquals(1, $importRecord->items_skipped);
        $this->assertEquals(0, $importRecord->items_updated);

        // Verify existing was untouched
        $this->assertEquals(20000, $existing->fresh()->price);

        // Verify new was created
        $this->assertDatabaseHas('products', [
            'store_id' => $this->store->id,
            'name' => 'Pempek Panggang Enak',
        ]);
        $this->assertDatabaseHas('catalog_import_items', [
            'catalog_import_id' => $importRecord->id,
            'sku' => 'PMPK-PNG-01',
            'action_taken' => 'created',
        ]);
    }

    public function test_catalog_import_execution_with_update_strategy(): void
    {
        $existing = Product::create([
            'store_id' => $this->store->id,
            'name' => 'Pempek Kulit Spesial',
            'slug' => 'pempek-kulit-spesial',
            'sku' => 'PMPK-KLT-01',
            'price' => 15000,
            'stock' => 10,
            'status' => 'active',
            'short_description' => 'Kulit lama',
            'sold' => 0,
        ]);

        $incoming = [
            new ProductData(
                title: 'Pempek Kulit Spesial',
                description: 'Kulit baru renyah',
                price: 18000,
                stock: 45,
                sku: 'PMPK-KLT-01',
            ),
        ];

        $service = app(CatalogImportService::class);
        $importRecord = $service->execute($this->store->id, 'mock', $incoming, 'update');

        $this->assertEquals('completed', $importRecord->status);
        $this->assertEquals(0, $importRecord->items_created);
        $this->assertEquals(1, $importRecord->items_updated);

        // Existing product price and stock must be updated
        $fresh = $existing->fresh();
        $this->assertEquals(18000, $fresh->price);
        $this->assertEquals(45, $fresh->stock);
    }

    public function test_admin_import_wizard_and_history_routes(): void
    {
        // 1. Wizard index
        $resIndex = $this->get(route('admin.import.index'));
        $resIndex->assertStatus(200);
        $resIndex->assertSee('Mock Data Pempek');

        // 2. Validate URL endpoint
        $resSsrfSafe = $this->postJson(route('admin.import.validate'), [
            'url' => 'https://tokopedia.com/toko-pempek',
        ]);
        $resSsrfSafe->assertJson(['valid' => true]);

        $resSsrfBlock = $this->postJson(route('admin.import.validate'), [
            'url' => 'http://127.0.0.1:8000/secret',
        ]);
        $resSsrfBlock->assertJson(['valid' => false]);

        // 3. Wizard preview with mock source
        $resPreview = $this->post(route('admin.import.preview'), [
            'source_type' => 'mock',
        ]);
        $resPreview->assertStatus(200);
        $resPreview->assertSee('Total Produk');
        $resPreview->assertSee('Strategi Penanganan Duplikat');

        // 4. Import history
        $resHistory = $this->get(route('admin.import.history'));
        $resHistory->assertStatus(200);
    }

    public function test_shopee_wings_official_shop_validation_and_preview(): void
    {
        // Test 1: Full https URL via validate endpoint
        $res1 = $this->postJson(route('admin.import.validate'), [
            'source_type' => 'marketplace',
            'marketplace_url' => 'https://shopee.co.id/wingsofficialshop',
        ]);
        $res1->assertOk();
        $res1->assertJson([
            'valid' => true,
            'type' => 'marketplace',
        ]);
        $this->assertStringContainsString('Wings Official Shop', $res1->json('message'));

        // Test 2: URL without scheme (shopee.co.id/wingsofficialshop)
        $res2 = $this->postJson(route('admin.import.validate'), [
            'source_type' => 'marketplace',
            'marketplace_url' => 'shopee.co.id/wingsofficialshop',
        ]);
        $res2->assertOk();
        $res2->assertJson(['valid' => true]);

        // Test 3: Shopee specific product/store slug with ID
        $res3 = $this->postJson(route('admin.import.validate'), [
            'source_type' => 'marketplace',
            'marketplace_url' => 'https://shopee.co.id/Wings-Official-Shop-i.14088921.1293847',
        ]);
        $res3->assertOk();
        $res3->assertJson(['valid' => true]);

        // Test 4: Preview page loads authentic Wings catalog
        $resPreview = $this->post(route('admin.import.preview'), [
            'source_type' => 'marketplace',
            'marketplace_url' => 'https://shopee.co.id/wingsofficialshop',
            'target_store_id' => $this->store->id,
        ]);
        $resPreview->assertStatus(200);
        $resPreview->assertSee('So Klin Liquid');
        $resPreview->assertSee('Daia Deterjen');
        $resPreview->assertSee('Mama Lemon');
    }
}
