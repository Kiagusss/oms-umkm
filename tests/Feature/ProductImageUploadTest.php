<?php

namespace Tests\Feature;

use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ProductImageUploadTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\PreventRequestForgery::class);
        $this->withSession(['admin_authenticated' => true]);
        Storage::fake('public');
    }

    #[Test]
    public function edit_route_renders_with_produk_variable(): void
    {
        $produk = Product::factory()->create([
            'name' => 'Pempek Lenjer',
            'slug' => 'pempek-lenjer',
        ]);

        $this->get(route('admin.produk.edit', $produk))
            ->assertOk()
            ->assertSee('Edit Produk')
            ->assertSee('Pempek Lenjer')
            ->assertSee('Gambar Utama');
    }

    #[Test]
    public function admin_can_upload_thumbnail_and_gallery_on_create(): void
    {
        $thumbnail = UploadedFile::fake()->image('hero.png');
        $galleryA  = UploadedFile::fake()->image('a.jpg');
        $galleryB  = UploadedFile::fake()->image('b.webp');

        $response = $this->post(route('admin.produk.store'), [
            'name'              => 'Pempek Kapal Selam',
            'slug'              => 'pempek-kapal-selam',
            'price'             => 18000,
            'short_description' => 'Enak dan gurih.',
            'stock'             => 15,
            'status'            => 'active',
            'thumbnail'         => $thumbnail,
            'images'            => [$galleryA, $galleryB],
        ]);

        $response->assertRedirect(route('admin.produk.index'))
                 ->assertSessionHas('success');

        $product = Product::where('slug', 'pempek-kapal-selam')->firstOrFail();

        $this->assertNotNull($product->thumbnail);
        $this->assertStringStartsWith('/storage/products/', $product->thumbnail);
        $relativePath = ltrim(str_replace('/storage/', '', $product->thumbnail), '/');
        Storage::disk('public')->assertExists($relativePath);

        $this->assertIsArray($product->images);
        $this->assertCount(2, $product->images);
        foreach ($product->images as $imgPath) {
            $this->assertStringStartsWith('/storage/products/', $imgPath);
            Storage::disk('public')->assertExists(ltrim(str_replace('/storage/', '', $imgPath), '/'));
        }
    }

    #[Test]
    public function jpeg_uploads_are_accepted(): void
    {
        // Pastikan JPEG (image/jpeg) — baik .jpg maupun .jpeg — tetap diterima
        $thumbJpg  = UploadedFile::fake()->image('thumb.jpg');
        $thumbJpeg = UploadedFile::fake()->image('thumb.jpeg');

        $this->post(route('admin.produk.store'), [
            'name'              => 'Pempek JPEG',
            'slug'              => 'pempek-jpeg',
            'price'             => 12000,
            'short_description' => 'uji',
            'stock'             => 5,
            'status'            => 'active',
            'thumbnail'         => $thumbJpg,
            'images'            => [$thumbJpeg],
        ])->assertRedirect(route('admin.produk.index'))
          ->assertSessionHas('success');

        $product = Product::where('slug', 'pempek-jpeg')->firstOrFail();

        $this->assertStringStartsWith('/storage/products/', $product->thumbnail);
        $this->assertStringEndsWith('.jpg', $product->thumbnail); // normalisasi ekstensi

        $this->assertIsArray($product->images);
        $this->assertCount(1, $product->images);
        $this->assertStringEndsWith('.jpg', $product->images[0]);
    }

    #[Test]
    public function admin_can_replace_thumbnail_on_update(): void
    {
        $produk = Product::factory()->create([
            'name'      => 'Pempek Adaan',
            'slug'      => 'pempek-adaan',
            'thumbnail' => '/storage/products/legacy/old-thumb.png',
        ]);

        $newThumb = UploadedFile::fake()->image('new.png');

        $this->put(route('admin.produk.update', $produk), [
            'name'              => 'Pempek Adaan',
            'slug'              => 'pempek-adaan',
            'price'             => 15000,
            'short_description' => 'Manis pedas.',
            'stock'             => 10,
            'status'            => 'active',
            'thumbnail'         => $newThumb,
        ])->assertRedirect(route('admin.produk.index'));

        $produk->refresh();

        $this->assertStringStartsWith('/storage/products/', $produk->thumbnail);
        $this->assertNotEquals('/storage/products/legacy/old-thumb.png', $produk->thumbnail);

        $newRel = ltrim(str_replace('/storage/', '', $produk->thumbnail), '/');
        Storage::disk('public')->assertExists($newRel);
    }

    #[Test]
    public function admin_can_remove_specific_gallery_items_via_checkbox(): void
    {
        $produk = Product::factory()->create([
            'name'   => 'Pempek Pistel',
            'slug'   => 'pempek-pistel',
            'images' => [
                '/storage/products/g1.jpg',
                '/storage/products/g2.jpg',
                '/storage/products/g3.jpg',
            ],
        ]);

        $this->put(route('admin.produk.update', $produk), [
            'name'              => 'Pempek Pistel',
            'slug'              => 'pempek-pistel',
            'price'             => 12000,
            'short_description' => 'Berselimut tipis.',
            'stock'             => 8,
            'status'            => 'active',
            'remove_images'     => ['0', '2'],
        ])->assertRedirect(route('admin.produk.index'));

        $produk->refresh();
        $this->assertIsArray($produk->images);
        $this->assertCount(1, $produk->images);
        $this->assertEquals('/storage/products/g2.jpg', $produk->images[0]);
    }

    #[Test]
    public function remove_thumbnail_checkbox_clears_image(): void
    {
        $produk = Product::factory()->create([
            'thumbnail' => '/storage/products/old.jpg',
        ]);

        $this->put(route('admin.produk.update', $produk), [
            'name'              => 'No Image',
            'slug'              => 'no-image',
            'price'             => 10000,
            'short_description' => 'Tanpa gambar.',
            'stock'             => 1,
            'status'            => 'active',
            'remove_thumbnail'  => '1',
        ])->assertRedirect(route('admin.produk.index'));

        $produk->refresh();
        $this->assertNull($produk->thumbnail);
    }

    #[Test]
    public function non_image_file_is_rejected_for_thumbnail(): void
    {
        $evil = UploadedFile::fake()->create('virus.exe', 10, 'application/octet-stream');

        $this->post(route('admin.produk.store'), [
            'name'              => 'Suspect',
            'slug'              => 'suspect',
            'price'             => 1000,
            'short_description' => 'evil',
            'stock'             => 1,
            'status'            => 'active',
            'thumbnail'         => $evil,
        ])->assertSessionHasErrors('thumbnail');
    }

    #[Test]
    public function gallery_max_8_items_enforced(): void
    {
        $files = [];
        for ($i = 0; $i < 9; $i++) {
            $files[] = UploadedFile::fake()->image("g{$i}.jpg");
        }

        $this->post(route('admin.produk.store'), [
            'name'              => 'TooManyImgs',
            'slug'              => 'too-many-imgs',
            'price'             => 1000,
            'short_description' => 'over',
            'stock'             => 1,
            'status'            => 'active',
            'images'            => $files,
        ])->assertSessionHasErrors('images');
    }

    #[Test]
    public function file_over_2mb_is_rejected(): void
    {
        // 3 MB > 2048 KB = batas atas validation. (PHP ini upload_max_filesize=2M,
        // jadi di production pun file >2MB ditolak PHP sebelum Laravel lihat.)
        $big = UploadedFile::fake()->image('huge.jpg')->size(3072);

        $this->post(route('admin.produk.store'), [
            'name'              => 'TooBig',
            'slug'              => 'too-big',
            'price'             => 1000,
            'short_description' => 'big',
            'stock'             => 1,
            'status'            => 'active',
            'thumbnail'         => $big,
        ])->assertSessionHasErrors('thumbnail');
    }

    #[Test]
    public function upload_failure_shows_actionable_message(): void
    {
        // Mensimulasikan skenario user: file ditolak PHP karena upload_max_filesize.
        // Pesan error harus informatif (bukan generic "failed to upload" saja).
        // Kita test via validation max rule dengan file size >2MB.
        $big = UploadedFile::fake()->image('big.jpg')->size(3072);

        $response = $this->post(route('admin.produk.store'), [
            'name'              => 'Big',
            'slug'              => 'big',
            'price'             => 1000,
            'short_description' => 'big',
            'stock'             => 1,
            'status'            => 'active',
            'thumbnail'         => $big,
        ]);

        // Assert session has thumbnail error (validates) — Laravel's
        // assertSessionHasErrors handles bag conversion.
        $response->assertSessionHasErrors('thumbnail');

        // Verify error message mentions "2 MB" — proves the custom message
        // (which mentions "2 MB") was used, not Laravel's generic
        // "failed to upload".
        $errors = session('errors');
        $this->assertNotNull($errors);
        $bag = $errors->getBag('default');
        $this->assertTrue($bag->has('thumbnail'));
        $messages = $bag->get('thumbnail');
        $this->assertNotEmpty($messages);
        $this->assertStringContainsString('2 MB', implode(' | ', $messages));
    }
}
