<?php

namespace App\Services\Import\Importers;

use App\Services\Import\Contracts\ImporterInterface;
use App\Services\Import\DTO\CategoryData;
use App\Services\Import\DTO\ProductData;
use App\Services\Import\DTO\StoreData;
use App\Services\Import\DTO\VariantData;

class MockImporter implements ImporterInterface
{
    public function validateSource(string $source): array
    {
        $normalized = strtolower(trim($source));
        $isMock = str_contains($normalized, 'demo')
            || str_contains($normalized, 'mock')
            || str_contains($normalized, 'pempek')
            || str_contains($normalized, 'catalog');

        if (!$isMock && !filter_var($source, FILTER_VALIDATE_URL)) {
            return [
                'valid' => false,
                'type' => 'mock',
                'message' => 'URL sumber mock tidak valid. Gunakan URL seperti https://demo.import/pempek-pak-agus',
                'store' => null,
                'items_count' => 0,
            ];
        }

        $store = new StoreData(
            name: 'Pempek Asli Pak Agus 88',
            description: 'Spesialis Pempek Asli Palembang Ikan Tenggiri Segar 100% Halal tanpa bahan pengawet.',
            logo: '/images/pempek-logo.png',
            banner: '/images/hero-banner.jpg',
            phone: '081298765432',
            whatsapp: '6281298765432',
            city: 'Palembang',
            province: 'Sumatera Selatan',
            address: 'Jl. Sudirman No. 88, Palembang',
            externalId: 'ext-store-agus-88',
        );

        return [
            'valid' => true,
            'type' => 'mock',
            'message' => 'Katalog toko terdeteksi: Pempek Asli Pak Agus 88 dengan 47 produk siap diimpor.',
            'store' => $store,
            'items_count' => 47,
        ];
    }

    public function fetchAndNormalize(string $source): array
    {
        $store = new StoreData(
            name: 'Pempek Asli Pak Agus 88',
            description: 'Spesialis Pempek Asli Palembang Ikan Tenggiri Segar 100% Halal tanpa pengawet.',
            logo: '/images/pempek-logo.png',
            banner: '/images/hero-banner.jpg',
            phone: '081298765432',
            whatsapp: '6281298765432',
            city: 'Palembang',
            province: 'Sumatera Selatan',
            address: 'Jl. Sudirman No. 88, Palembang',
            externalId: 'ext-store-agus-88'
        );

        $categories = [
            new CategoryData(name: 'Pempek Klasik', externalId: 'CAT-01', slug: 'pempek-klasik', icon: 'sparkles', ord: 1),
            new CategoryData(name: 'Pempek Panggang & Bakar', externalId: 'CAT-02', slug: 'pempek-panggang-bakar', icon: 'fire', ord: 2),
            new CategoryData(name: 'Paket & Hampers', externalId: 'CAT-03', slug: 'paket-hampers', icon: 'gift', ord: 3),
            new CategoryData(name: 'Kuah & Cuko Spesial', externalId: 'CAT-04', slug: 'kuah-cuko-spesial', icon: 'beaker', ord: 4),
            new CategoryData(name: 'Hidangan Berkuah Khas', externalId: 'CAT-05', slug: 'hidangan-berkuah-khas', icon: 'bowl', ord: 5),
            new CategoryData(name: 'Kerupuk & Kemplang', externalId: 'CAT-06', slug: 'kerupuk-kemplang', icon: 'sun', ord: 6),
            new CategoryData(name: 'Aneka Sambal & Bumbu', externalId: 'CAT-07', slug: 'aneka-sambal-bumbu', icon: 'cube', ord: 7),
            new CategoryData(name: 'Minuman Tradisional', externalId: 'CAT-08', slug: 'minuman-tradisional', icon: 'cake', ord: 8),
        ];

        $products = $this->buildCatalogItems();

        return [
            'store' => $store,
            'categories' => $categories,
            'products' => $products,
        ];
    }

    /**
     * Build 47 products, 23 variants, 129 images.
     *
     * @return ProductData[]
     */
    private function buildCatalogItems(): array
    {
        $items = [];

        // Definition list of 47 products
        $catalogRaw = [
            // Cat 1: Pempek Klasik (10 items)
            [
                'name' => 'Pempek Kapal Selam Telur Utuh',
                'sku' => 'PMP-KS-01',
                'cat' => 'Pempek Klasik',
                'cat_id' => 'CAT-01',
                'price' => 25000,
                'cost' => 15000,
                'weight' => 250,
                'stock' => 45,
                'desc' => 'Pempek isi 1 butir telur ayam utuh dibalut adonan ikan tenggiri super lembut.',
                'comp' => 'Ikan tenggiri segar, tepung tapioka super, telur ayam, garam, bawang putih',
                'images' => ['/images/pempek-kapal-selam.jpg', '/images/pempek-kapal-selam-2.jpg', '/images/pempek-goreng.jpg'],
                'variants' => [
                    ['name' => 'Telur Ayam Segar', 'sku' => 'PMP-KS-01-AYM', 'price' => 25000, 'stock' => 30],
                    ['name' => 'Telur Bebek Gurih', 'sku' => 'PMP-KS-01-BBK', 'price' => 28000, 'stock' => 15],
                ]
            ],
            [
                'name' => 'Pempek Lenjer Besar Super',
                'sku' => 'PMP-LB-02',
                'cat' => 'Pempek Klasik',
                'cat_id' => 'CAT-01',
                'price' => 30000,
                'cost' => 18000,
                'weight' => 300,
                'stock' => 25,
                'desc' => 'Pempek lenjer panjang ukuran besar, cocok dipotong-potong untuk makan bersama.',
                'comp' => 'Ikan tenggiri 100%, sagu tani pilihan, bumbu rahasia keluarga',
                'images' => ['/images/pempek-lenjer.jpg', '/images/pempek-lenjer-potong.jpg', '/images/lenjer-cuko.jpg'],
                'variants' => []
            ],
            [
                'name' => 'Pempek Lenjer Kecil',
                'sku' => 'PMP-LK-03',
                'cat' => 'Pempek Klasik',
                'cat_id' => 'CAT-01',
                'price' => 6000,
                'cost' => 3500,
                'weight' => 60,
                'stock' => 120,
                'desc' => 'Lenjer ukuran personal, tekstur kenyal dan rasa gurih ikan tenggiri yang kuat.',
                'comp' => 'Ikan tenggiri, sagu tani, kaldu ikan, rempah alami',
                'images' => ['/images/lenjer-kecil-1.jpg', '/images/lenjer-kecil-2.jpg'],
                'variants' => []
            ],
            [
                'name' => 'Pempek Telur Kecil',
                'sku' => 'PMP-TK-04',
                'cat' => 'Pempek Klasik',
                'cat_id' => 'CAT-01',
                'price' => 6000,
                'cost' => 3500,
                'weight' => 65,
                'stock' => 100,
                'desc' => 'Pempek kecil dengan isian kocokan telur gurih di dalamnya.',
                'comp' => 'Ikan tenggiri murni, telur, sagu, garam',
                'images' => ['/images/pempek-telur-kecil.jpg', '/images/pempek-telur-kecil-goreng.jpg'],
                'variants' => []
            ],
            [
                'name' => 'Pempek Adaan Bawang Wangi',
                'sku' => 'PMP-AD-05',
                'cat' => 'Pempek Klasik',
                'cat_id' => 'CAT-01',
                'price' => 6500,
                'cost' => 3800,
                'weight' => 70,
                'stock' => 90,
                'desc' => 'Pempek bulat dengan irisan daun bawang dan bawang merah beraroma khas sedap.',
                'comp' => 'Daging ikan tenggiri, santan kelapa murni, daun bawang, bawang merah',
                'images' => ['/images/pempek-adaan.jpg', '/images/pempek-adaan-matang.jpg', '/images/adaan-cuko.jpg'],
                'variants' => [
                    ['name' => 'Goreng Renyah', 'sku' => 'PMP-AD-05-GRG', 'price' => 6500, 'stock' => 50],
                    ['name' => 'Kukus Lembut', 'sku' => 'PMP-AD-05-KKS', 'price' => 6500, 'stock' => 40],
                ]
            ],
            [
                'name' => 'Pempek Kulit Ikan Crispy',
                'sku' => 'PMP-KL-06',
                'cat' => 'Pempek Klasik',
                'cat_id' => 'CAT-01',
                'price' => 6500,
                'cost' => 3600,
                'weight' => 65,
                'stock' => 80,
                'desc' => 'Dibuat dari kulit ikan tenggiri segar yang digoreng garing di luar, empuk gurih di dalam.',
                'comp' => 'Kulit ikan tenggiri segar, sagu, bawang merah, lada bubuk',
                'images' => ['/images/pempek-kulit.jpg', '/images/pempek-kulit-crispy.jpg'],
                'variants' => []
            ],
            [
                'name' => 'Pempek Keriting (Kerupuk)',
                'sku' => 'PMP-KR-07',
                'cat' => 'Pempek Klasik',
                'cat_id' => 'CAT-01',
                'price' => 7000,
                'cost' => 4000,
                'weight' => 70,
                'stock' => 60,
                'desc' => 'Bentuk unik menyerupai mie keriting dengan kelembutan yang menyerap cuko sempurna.',
                'comp' => 'Ikan tenggiri grade A, tepung sagu premium, bumbu kaldu',
                'images' => ['/images/pempek-keriting.jpg', '/images/pempek-keriting-2.jpg', '/images/pempek-keriting-kuah.jpg'],
                'variants' => []
            ],
            [
                'name' => 'Pempek Pistel Isi Pepaya Muda',
                'sku' => 'PMP-PS-08',
                'cat' => 'Pempek Klasik',
                'cat_id' => 'CAT-01',
                'price' => 6500,
                'cost' => 3500,
                'weight' => 70,
                'stock' => 50,
                'desc' => 'Pempek tradisional dengan isian tumis parutan pepaya muda gurih berbumbu ebi pedas.',
                'comp' => 'Ikan tenggiri, santan, pepaya muda, ebi sangrai, lada',
                'images' => ['/images/pempek-pistel.jpg', '/images/pempek-pistel-belah.jpg'],
                'variants' => []
            ],
            [
                'name' => 'Pempek Tahu Sutra Gurih',
                'sku' => 'PMP-TH-09',
                'cat' => 'Pempek Klasik',
                'cat_id' => 'CAT-01',
                'price' => 7000,
                'cost' => 4000,
                'weight' => 85,
                'stock' => 40,
                'desc' => 'Tahu putih lembut dibungkus adonan pempek ikan tenggiri renyah gurih.',
                'comp' => 'Tahu sutra pilihan, adonan pempek ikan tenggiri, rempah',
                'images' => ['/images/pempek-tahu.jpg', '/images/pempek-tahu-goreng.jpg'],
                'variants' => []
            ],
            [
                'name' => 'Pempek Dos Tanpa Ikan (Vegetarian)',
                'sku' => 'PMP-DS-10',
                'cat' => 'Pempek Klasik',
                'cat_id' => 'CAT-01',
                'price' => 4000,
                'cost' => 2000,
                'weight' => 50,
                'stock' => 65,
                'desc' => 'Pempek sagu tanpa campuran ikan, tetap kenyal dan gurih dengan bumbu kaldu jamur alami.',
                'comp' => 'Tepung terigu, tepung tapioka, kaldu jamur, bawang putih, garam',
                'images' => ['/images/pempek-dos.jpg', '/images/pempek-dos-cuko.jpg'],
                'variants' => []
            ],

            // Cat 2: Pempek Panggang & Bakar (5 items)
            [
                'name' => 'Pempek Tunu Panggang Arang',
                'sku' => 'PMP-TN-11',
                'cat' => 'Pempek Panggang & Bakar',
                'cat_id' => 'CAT-02',
                'price' => 7000,
                'cost' => 4000,
                'weight' => 65,
                'stock' => 55,
                'desc' => 'Pempek dipanggang langsung di atas bara arang batok kelapa, diisi ebi pedas dan kecap manis.',
                'comp' => 'Ikan tenggiri, sagu, isian udang ebi sangrai, cabai rawit hijau, kecap manis',
                'images' => ['/images/pempek-tunu.jpg', '/images/pempek-tunu-bakar.jpg', '/images/pempek-tunu-isi.jpg'],
                'variants' => [
                    ['name' => 'Pedas Sedang', 'sku' => 'PMP-TN-11-MED', 'price' => 7000, 'stock' => 30],
                    ['name' => 'Ekstra Pedas Nampol', 'sku' => 'PMP-TN-11-HOT', 'price' => 7000, 'stock' => 25],
                ]
            ],
            [
                'name' => 'Otak-Otak Ikan Tenggiri Daun Pisang',
                'sku' => 'PMP-OT-12',
                'cat' => 'Pempek Panggang & Bakar',
                'cat_id' => 'CAT-02',
                'price' => 6000,
                'cost' => 3500,
                'weight' => 55,
                'stock' => 110,
                'desc' => 'Otak-otak wangi dibungkus daun pisang segar dan dibakar wangi semerbak.',
                'comp' => 'Ikan tenggiri, santan kental, daun bawang, daun pisang',
                'images' => ['/images/otak-otak.jpg', '/images/otak-otak-bakar.jpg', '/images/otak-otak-bumbu.jpg'],
                'variants' => []
            ],
            [
                'name' => 'Lenggang Panggang Daun Pisang',
                'sku' => 'PMP-LG-13',
                'cat' => 'Pempek Panggang & Bakar',
                'cat_id' => 'CAT-02',
                'price' => 20000,
                'cost' => 12000,
                'weight' => 220,
                'stock' => 35,
                'desc' => 'Adonan pempek dicampur kocokan 2 butir telur bebek dipanggang dalam mangkuk daun pisang.',
                'comp' => 'Adonan pempek tenggiri, telur bebek pilihan, daun pisang',
                'images' => ['/images/lenggang-panggang.jpg', '/images/lenggang-panggang-cuko.jpg', '/images/lenggang-bakar.jpg'],
                'variants' => []
            ],
            [
                'name' => 'Lenggang Goreng Telur Spesial',
                'sku' => 'PMP-LG-14',
                'cat' => 'Pempek Panggang & Bakar',
                'cat_id' => 'CAT-02',
                'price' => 18000,
                'cost' => 11000,
                'weight' => 200,
                'stock' => 40,
                'desc' => 'Potongan pempek lenjer didadar bersama telur ayam segar dengan pinggiran garing gurih.',
                'comp' => 'Pempek lenjer, telur ayam, daun bawang, garam',
                'images' => ['/images/lenggang-goreng.jpg', '/images/lenggang-goreng-piring.jpg'],
                'variants' => []
            ],
            [
                'name' => 'Pempek Belah Isi Ebi Rawit',
                'sku' => 'PMP-BL-15',
                'cat' => 'Pempek Panggang & Bakar',
                'cat_id' => 'CAT-02',
                'price' => 6500,
                'cost' => 3800,
                'weight' => 60,
                'stock' => 70,
                'desc' => 'Pempek lenjer kecil yang dibelah dan diisi sambal ebi kering pedas manis.',
                'comp' => 'Ikan tenggiri, udang kering ebi, cabai rawit, kecap manis',
                'images' => ['/images/pempek-belah.jpg', '/images/pempek-belah-sambal.jpg'],
                'variants' => []
            ],

            // Cat 3: Paket & Hampers (7 items)
            [
                'name' => 'Paket Sultan Eksklusif 50 Pcs + Cuko 1L',
                'sku' => 'PKT-SLT-16',
                'cat' => 'Paket & Hampers',
                'cat_id' => 'CAT-03',
                'price' => 320000,
                'cost' => 210000,
                'weight' => 2500,
                'stock' => 15,
                'desc' => 'Paket komplit: 2 Kapal Selam, 2 Lenjer Besar, 15 Adaan, 15 Telur Kecil, 10 Kulit, 6 Keriting, plus 2 botol Cuko 500ml.',
                'comp' => 'Aneka pempek tenggiri pilihan, cuko hitam kental gula aren Linggau',
                'images' => ['/images/paket-sultan.jpg', '/images/paket-sultan-box.jpg', '/images/paket-sultan-buka.jpg', '/images/paket-sultan-cuko.jpg'],
                'variants' => [
                    ['name' => 'Cuko Pedas Mantap', 'sku' => 'PKT-SLT-16-HOT', 'price' => 320000, 'stock' => 8],
                    ['name' => 'Cuko Campur (1 Pedas + 1 Sedang)', 'sku' => 'PKT-SLT-16-MIX', 'price' => 320000, 'stock' => 7],
                ]
            ],
            [
                'name' => 'Paket Mantap Keluarga 25 Pcs',
                'sku' => 'PKT-MTP-17',
                'cat' => 'Paket & Hampers',
                'cat_id' => 'CAT-03',
                'price' => 165000,
                'cost' => 105000,
                'weight' => 1300,
                'stock' => 30,
                'desc' => 'Paket terlaris: 1 Kapal Selam, 1 Lenjer Besar, 8 Adaan, 8 Telur Kecil, 7 Kulit Crispy, plus 1 botol Cuko 500ml.',
                'comp' => 'Ikan tenggiri pilihan, kuah cuko kental',
                'images' => ['/images/paket-mantap.jpg', '/images/paket-mantap-isi.jpg', '/images/paket-mantap-box.jpg'],
                'variants' => [
                    ['name' => 'Cuko Level Pedas', 'sku' => 'PKT-MTP-17-PDS', 'price' => 165000, 'stock' => 18],
                    ['name' => 'Cuko Level Sedang', 'sku' => 'PKT-MTP-17-SDG', 'price' => 165000, 'stock' => 12],
                ]
            ],
            [
                'name' => 'Paket Hemat Mini 15 Pcs',
                'sku' => 'PKT-HMT-18',
                'cat' => 'Paket & Hampers',
                'cat_id' => 'CAT-03',
                'price' => 95000,
                'cost' => 60000,
                'weight' => 800,
                'stock' => 40,
                'desc' => 'Pas untuk santapan berdua: 5 Adaan, 5 Telur Kecil, 5 Kulit, plus 1 botol Cuko 250ml.',
                'comp' => 'Aneka pempek mini campur, cuko 250ml',
                'images' => ['/images/paket-hemat.jpg', '/images/paket-hemat-plate.jpg'],
                'variants' => []
            ],
            [
                'name' => 'Hampers Besek Bambu Tradisional',
                'sku' => 'PKT-BSK-19',
                'cat' => 'Paket & Hampers',
                'cat_id' => 'CAT-03',
                'price' => 195000,
                'cost' => 130000,
                'weight' => 1600,
                'stock' => 20,
                'desc' => 'Kemasan besek anyaman bambu cantik dengan pita hias, kartu ucapan, dan pempek vakum higienis.',
                'comp' => '25 pcs pempek campur, cuko botol kaca estetik, packaging besek bambu ramah lingkungan',
                'images' => ['/images/hampers-besek.jpg', '/images/hampers-besek-hias.jpg', '/images/hampers-besek-open.jpg'],
                'variants' => [
                    ['name' => 'Pita Merah Emas (Elegan)', 'sku' => 'PKT-BSK-19-RED', 'price' => 195000, 'stock' => 10],
                    ['name' => 'Pita Hijau Sage (Natural)', 'sku' => 'PKT-BSK-19-GRN', 'price' => 195000, 'stock' => 10],
                ]
            ],
            [
                'name' => 'Paket Frozen Vakum Luar Kota 30 Pcs',
                'sku' => 'PKT-FRZ-20',
                'cat' => 'Paket & Hampers',
                'cat_id' => 'CAT-03',
                'price' => 185000,
                'cost' => 120000,
                'weight' => 1700,
                'stock' => 35,
                'desc' => 'Khusus pengiriman antar-pulau / luar kota. Dikemas plastik kedap udara vakum tahan 4 hari di suhu ruang.',
                'comp' => 'Pempek frozen vacuum sealed, cuko kemasan tebal anti bocor',
                'images' => ['/images/paket-frozen.jpg', '/images/paket-frozen-seal.jpg', '/images/paket-frozen-pack.jpg'],
                'variants' => []
            ],
            [
                'name' => 'Paket Party Box 100 Pcs',
                'sku' => 'PKT-PRT-21',
                'cat' => 'Paket & Hampers',
                'cat_id' => 'CAT-03',
                'price' => 600000,
                'cost' => 390000,
                'weight' => 5000,
                'stock' => 8,
                'desc' => 'Cocok untuk arisan, meeting kantor, reuni keluarga, dan katering pesta.',
                'comp' => '100 pcs aneka pempek campur mini, 2 liter cuko Palembang',
                'images' => ['/images/party-box.jpg', '/images/party-box-table.jpg', '/images/party-box-open.jpg'],
                'variants' => []
            ],
            [
                'name' => 'Paket Kulit Lover 20 Pcs',
                'sku' => 'PKT-KLT-22',
                'cat' => 'Paket & Hampers',
                'cat_id' => 'CAT-03',
                'price' => 125000,
                'cost' => 80000,
                'weight' => 1100,
                'stock' => 25,
                'desc' => 'Spesial bagi pecinta sensasi renyah gurih kulit ikan tenggiri Palembang.',
                'comp' => '20 pcs pempek kulit crispy, cuko 350ml',
                'images' => ['/images/paket-kulit.jpg', '/images/paket-kulit-cuko.jpg'],
                'variants' => []
            ],

            // Cat 4: Kuah & Cuko Spesial (5 items)
            [
                'name' => 'Cuko Palembang Hitam Kental Asli',
                'sku' => 'CKO-HTM-23',
                'cat' => 'Kuah & Cuko Spesial',
                'cat_id' => 'CAT-04',
                'price' => 35000,
                'cost' => 18000,
                'weight' => 550,
                'stock' => 85,
                'desc' => 'Cuko hitam kental pekat autentik menggunakan gula batok Curup / Linggau dan asam jawa segar.',
                'comp' => 'Gula batok Linggau, cabai rawit hijau, bawang putih kating, ebi sangrai, asam jawa, garam',
                'images' => ['/images/cuko-hitam.jpg', '/images/cuko-hitam-botol.jpg', '/images/cuko-tuang.jpg'],
                'variants' => [
                    ['name' => 'Botol 250ml', 'sku' => 'CKO-HTM-23-250', 'price' => 20000, 'stock' => 45],
                    ['name' => 'Botol 500ml', 'sku' => 'CKO-HTM-23-500', 'price' => 35000, 'stock' => 30],
                    ['name' => 'Jeriken 1 Liter', 'sku' => 'CKO-HTM-23-1000', 'price' => 65000, 'stock' => 10],
                ]
            ],
            [
                'name' => 'Cuko Manis Gurih (Non Pedas)',
                'sku' => 'CKO-MNS-24',
                'cat' => 'Kuah & Cuko Spesial',
                'cat_id' => 'CAT-04',
                'price' => 35000,
                'cost' => 18000,
                'weight' => 550,
                'stock' => 50,
                'desc' => 'Cuko ramah anak dan lansia tanpa cabai pedas, rasa manis legit berpadu bawang putih wangi.',
                'comp' => 'Gula batok aren asli, bawang putih, garam, asam kandis',
                'images' => ['/images/cuko-manis.jpg', '/images/cuko-manis-botol.jpg'],
                'variants' => []
            ],
            [
                'name' => 'Cuko Ekstra Pedas Level Dewata',
                'sku' => 'CKO-PDS-25',
                'cat' => 'Kuah & Cuko Spesial',
                'cat_id' => 'CAT-04',
                'price' => 38000,
                'cost' => 20000,
                'weight' => 550,
                'stock' => 60,
                'desc' => 'Racikan pedas nendang dengan perpaduan cabai rawit hijau dan cabai caplak merah segar.',
                'comp' => 'Cabai rawit caplak, gula aren hitam, bawang putih, ebi, cuka aren',
                'images' => ['/images/cuko-pedas.jpg', '/images/cuko-pedas-mangkok.jpg', '/images/cuko-pedas-rawit.jpg'],
                'variants' => []
            ],
            [
                'name' => 'Ebi Sangrai Bubuk Tabur Cuko',
                'sku' => 'CKO-EBI-26',
                'cat' => 'Kuah & Cuko Spesial',
                'cat_id' => 'CAT-04',
                'price' => 25000,
                'cost' => 15000,
                'weight' => 100,
                'stock' => 45,
                'desc' => 'Bubuk ebi murni sangrai halus, taburan wajib untuk melipatgandakan kenikmatan cuko.',
                'comp' => 'Udang ebi kering kualitas super sangrai kering tanpa minyak',
                'images' => ['/images/ebi-bubuk.jpg', '/images/ebi-bubuk-jar.jpg'],
                'variants' => []
            ],
            [
                'name' => 'Cuko Merah Khas Bangka (Cuka Tauco)',
                'sku' => 'CKO-MRH-27',
                'cat' => 'Kuah & Cuko Spesial',
                'cat_id' => 'CAT-04',
                'price' => 30000,
                'cost' => 16000,
                'weight' => 350,
                'stock' => 30,
                'desc' => 'Saus cuka merah khas selat Bangka dengan sentuhan tauco gurih segar.',
                'comp' => 'Cabai merah giling, tauco, bawang putih, jeruk kunci, gula',
                'images' => ['/images/cuko-merah.jpg', '/images/cuko-merah-botol.jpg'],
                'variants' => []
            ],

            // Cat 5: Hidangan Berkuah Khas (6 items)
            [
                'name' => 'Tekwan Komplit Ikan Tenggiri',
                'sku' => 'KW-TKW-28',
                'cat' => 'Hidangan Berkuah Khas',
                'cat_id' => 'CAT-05',
                'price' => 22000,
                'cost' => 13000,
                'weight' => 350,
                'stock' => 50,
                'desc' => 'Pentol tekwan lembut disajikan dengan kaldu udang gurih, jamur kuping, bunga sedap malam, dan bengkuang.',
                'comp' => 'Pentol ikan tenggiri, kaldu udang, bengkuang, jamur kuping, bunga sedap malam, soun',
                'images' => ['/images/tekwan-komplit.jpg', '/images/tekwan-kuah.jpg', '/images/tekwan-bowl.jpg'],
                'variants' => [
                    ['name' => 'Siap Makan (Kuah Matang)', 'sku' => 'KW-TKW-28-RDY', 'price' => 22000, 'stock' => 25],
                    ['name' => 'Paket Instan Frozen (Rebus Sendiri)', 'sku' => 'KW-TKW-28-FRZ', 'price' => 22000, 'stock' => 25],
                ]
            ],
            [
                'name' => 'Model Ikan Tahu Gurih',
                'sku' => 'KW-MDL-29',
                'cat' => 'Hidangan Berkuah Khas',
                'cat_id' => 'CAT-05',
                'price' => 22000,
                'cost' => 13000,
                'weight' => 380,
                'stock' => 40,
                'desc' => 'Tahu cina dibalut daging ikan tenggiri, digoreng lalu disiram kuah sop tekwan hangat.',
                'comp' => 'Tahu cina, ikan tenggiri, bihun, mentimun cincang, kaldu udang',
                'images' => ['/images/model-ikan.jpg', '/images/model-ikan-bowl.jpg', '/images/model-tahu.jpg'],
                'variants' => []
            ],
            [
                'name' => 'Model Gandum Khas Palembang',
                'sku' => 'KW-MDG-30',
                'cat' => 'Hidangan Berkuah Khas',
                'cat_id' => 'CAT-05',
                'price' => 18000,
                'cost' => 10000,
                'weight' => 350,
                'stock' => 35,
                'desc' => 'Roti goreng dari tepung terigu gurih berpori yang empuk menyerap kuah kaldu rempah kaya rasa.',
                'comp' => 'Tepung gandum, telur, kaldu udang, ebi, bawang goreng',
                'images' => ['/images/model-gandum.jpg', '/images/model-gandum-kuah.jpg'],
                'variants' => []
            ],
            [
                'name' => 'Laksan Kuah Santan Merah Gurih',
                'sku' => 'KW-LKS-31',
                'cat' => 'Hidangan Berkuah Khas',
                'cat_id' => 'CAT-05',
                'price' => 22000,
                'cost' => 13500,
                'weight' => 360,
                'stock' => 30,
                'desc' => 'Irisan pempek lenjer tebal disiram kuah santan bumbu kari merah beraroma ebi harum.',
                'comp' => 'Pempek lenjer, santan kelapa kental, cabai merah, ebi, ketumbar',
                'images' => ['/images/laksan.jpg', '/images/laksan-kuah.jpg', '/images/laksan-mangkok.jpg'],
                'variants' => []
            ],
            [
                'name' => 'Celimpungan Kuah Kuning Kunyit',
                'sku' => 'KW-CLP-32',
                'cat' => 'Hidangan Berkuah Khas',
                'cat_id' => 'CAT-05',
                'price' => 22000,
                'cost' => 13500,
                'weight' => 360,
                'stock' => 25,
                'desc' => 'Bulatan pempek lembut dalam kuah santan kuning berempah kunyit wangi serai dan daun salam.',
                'comp' => 'Adonan pempek tenggiri, santan segar, kunyit, serai, kemiri, ebi',
                'images' => ['/images/celimpungan.jpg', '/images/celimpungan-kuah-kuning.jpg'],
                'variants' => []
            ],
            [
                'name' => 'Burgo Gulung Kuah Santan Gurih',
                'sku' => 'KW-BRG-33',
                'cat' => 'Hidangan Berkuah Khas',
                'cat_id' => 'CAT-05',
                'price' => 18000,
                'cost' => 10000,
                'weight' => 320,
                'stock' => 30,
                'desc' => 'Dadar beras kukus digulung menyerupai kwetiau lebar, dinikmati bersama kuah santan ikan gurih.',
                'comp' => 'Tepung beras, tapioka, daging ikan gabus giling, santan, lengkuas',
                'images' => ['/images/burgo.jpg', '/images/burgo-kuah.jpg'],
                'variants' => []
            ],

            // Cat 6: Kerupuk & Kemplang (6 items)
            [
                'name' => 'Kemplang Panggang Getas Asli Palembang',
                'sku' => 'KPK-PG-34',
                'cat' => 'Kerupuk & Kemplang',
                'cat_id' => 'CAT-06',
                'price' => 35000,
                'cost' => 22000,
                'weight' => 250,
                'stock' => 60,
                'desc' => 'Kemplang bundar dipanggang di atas bara api, renyah ringan dengan cocolan sambal terasi pedas manis.',
                'comp' => 'Daging ikan tenggiri, tepung tapioka, garam, bumbu terasi bakar',
                'images' => ['/images/kemplang-panggang.jpg', '/images/kemplang-panggang-sambal.jpg', '/images/kemplang-pack.jpg'],
                'variants' => [
                    ['name' => 'Bungkus 250 Gram', 'sku' => 'KPK-PG-34-250', 'price' => 35000, 'stock' => 40],
                    ['name' => 'Bungkus 500 Gram', 'sku' => 'KPK-PG-34-500', 'price' => 65000, 'stock' => 20],
                ]
            ],
            [
                'name' => 'Kerupuk Ikan Tenggiri Koin Renyah',
                'sku' => 'KPK-KN-35',
                'cat' => 'Kerupuk & Kemplang',
                'cat_id' => 'CAT-06',
                'price' => 30000,
                'cost' => 18000,
                'weight' => 250,
                'stock' => 70,
                'desc' => 'Kerupuk goreng berbentuk bundar koin kecil, kriuk garing dan aroma ikan tenggiri terasa di setiap gigitan.',
                'comp' => 'Ikan tenggiri, sagu, minyak kelapa, garam',
                'images' => ['/images/kerupuk-koin.jpg', '/images/kerupuk-koin-bowl.jpg'],
                'variants' => []
            ],
            [
                'name' => 'Kerupuk Sanggul Ikan Keriting',
                'sku' => 'KPK-SG-36',
                'cat' => 'Kerupuk & Kemplang',
                'cat_id' => 'CAT-06',
                'price' => 32000,
                'cost' => 19000,
                'weight' => 250,
                'stock' => 55,
                'desc' => 'Bentuk anyaman sanggul mekar merekah, sangat renyah disantap bersama mie atau nasi panas.',
                'comp' => 'Ikan tenggiri, sagu kualitas satu, bawang putih, garam',
                'images' => ['/images/kerupuk-sanggul.jpg', '/images/kerupuk-sanggul-pack.jpg'],
                'variants' => []
            ],
            [
                'name' => 'Getas Ikan Bulat Pipih Khas Bangka',
                'sku' => 'KPK-GT-37',
                'cat' => 'Kerupuk & Kemplang',
                'cat_id' => 'CAT-06',
                'price' => 28000,
                'cost' => 17000,
                'weight' => 200,
                'stock' => 65,
                'desc' => 'Camilan getas padat renyah dengan rasa ikan gurih gurih asin yang bikin ketagihan.',
                'comp' => 'Ikan tenggiri, sagu tani, telur, garam, soda kue',
                'images' => ['/images/getas-ikan.jpg', '/images/getas-ikan-jar.jpg', '/images/getas-ikan-close.jpg'],
                'variants' => []
            ],
            [
                'name' => 'Kerupuk Pasir Goreng Pasir Tradisional',
                'sku' => 'KPK-PS-38',
                'cat' => 'Kerupuk & Kemplang',
                'cat_id' => 'CAT-06',
                'price' => 25000,
                'cost' => 15000,
                'weight' => 200,
                'stock' => 45,
                'desc' => 'Digoreng tanpa minyak menggunakan pasir sungai pilihan yang dipanaskan, rendah lemak dan sehat.',
                'comp' => 'Ikan air tawar pilihan, tepung sagu, bawang putih, garam',
                'images' => ['/images/kerupuk-pasir.jpg', '/images/kerupuk-pasir-baskom.jpg'],
                'variants' => []
            ],
            [
                'name' => 'Kemplang Mini Kancing Gurih',
                'sku' => 'KPK-KC-39',
                'cat' => 'Kerupuk & Kemplang',
                'cat_id' => 'CAT-06',
                'price' => 28000,
                'cost' => 16500,
                'weight' => 220,
                'stock' => 50,
                'desc' => 'Kemplang kecil sekali hap berdiameter 3 cm, cocok untuk camilan anak saat santai.',
                'comp' => 'Ikan tenggiri, tepung tapioka, bumbu rempah',
                'images' => ['/images/kemplang-mini.jpg', '/images/kemplang-mini-pouch.jpg'],
                'variants' => []
            ],

            // Cat 7: Aneka Sambal & Bumbu (4 items)
            [
                'name' => 'Sambal Lingkung Abon Ikan Tenggiri Asli',
                'sku' => 'BMB-LKG-40',
                'cat' => 'Aneka Sambal & Bumbu',
                'cat_id' => 'CAT-07',
                'price' => 45000,
                'cost' => 28000,
                'weight' => 200,
                'stock' => 40,
                'desc' => 'Abon ikan khas Palembang bertekstur serat halus, gurih rempah kelapa sangrai wangi.',
                'comp' => 'Daging ikan tenggiri giling, santan kelapa sangrai, ketumbar, lengkuas, bawang',
                'images' => ['/images/sambal-lingkung.jpg', '/images/sambal-lingkung-jar.jpg', '/images/sambal-lingkung-plate.jpg'],
                'variants' => [
                    ['name' => 'Toples 150 Gram', 'sku' => 'BMB-LKG-40-150', 'price' => 35000, 'stock' => 25],
                    ['name' => 'Toples 250 Gram', 'sku' => 'BMB-LKG-40-250', 'price' => 55000, 'stock' => 15],
                ]
            ],
            [
                'name' => 'Bumbu Kuah Tekwan Siap Pakai',
                'sku' => 'BMB-TKW-41',
                'cat' => 'Aneka Sambal & Bumbu',
                'cat_id' => 'CAT-07',
                'price' => 20000,
                'cost' => 11000,
                'weight' => 150,
                'stock' => 50,
                'desc' => 'Bumbu pasta kaldu udang tumis harum, cukup larutkan dengan 2 liter air mendidih.',
                'comp' => 'Kepala udang segar, bawang putih, bawang merah, lada bangka, pala',
                'images' => ['/images/bumbu-tekwan.jpg', '/images/bumbu-tekwan-pouch.jpg'],
                'variants' => []
            ],
            [
                'name' => 'Terasi Bakar Khas Selat Bangka Super',
                'sku' => 'BMB-TRS-42',
                'cat' => 'Aneka Sambal & Bumbu',
                'cat_id' => 'CAT-07',
                'price' => 25000,
                'cost' => 14000,
                'weight' => 150,
                'stock' => 60,
                'desc' => 'Terasi udang rebon asli Bangka jemur alami, aroma harum sedap tanpa bau menyengat.',
                'comp' => '100% udang rebon segar, garam laut murni',
                'images' => ['/images/terasi-bangka.jpg', '/images/terasi-bangka-blok.jpg'],
                'variants' => []
            ],
            [
                'name' => 'Sambal Cuko Kering Bubuk Cabai',
                'sku' => 'BMB-SCK-43',
                'cat' => 'Aneka Sambal & Bumbu',
                'cat_id' => 'CAT-07',
                'price' => 22000,
                'cost' => 12000,
                'weight' => 100,
                'stock' => 45,
                'desc' => 'Bubuk cabai rawit pedas dan asam manis untuk menabur pempek tunu atau kerupuk kemplang.',
                'comp' => 'Cabai rawit kering, garam, asam sitrun alami, gula',
                'images' => ['/images/sambal-cuko-bubuk.jpg', '/images/sambal-cuko-jar.jpg'],
                'variants' => []
            ],

            // Cat 8: Minuman Tradisional (4 items)
            [
                'name' => 'Es Kacang Merah Palembang Legit',
                'sku' => 'MNM-KCG-44',
                'cat' => 'Minuman Tradisional',
                'cat_id' => 'CAT-08',
                'price' => 15000,
                'cost' => 8000,
                'weight' => 300,
                'stock' => 35,
                'desc' => 'Kacang merah empuk manis legit dengan serutan es, sirup merah mawar wangi, dan susu kental manis cokelat.',
                'comp' => 'Kacang merah pilihan, gula aren, sirup mawar, es serut, krimer kental manis',
                'images' => ['/images/es-kacang-merah.jpg', '/images/es-kacang-merah-glass.jpg', '/images/es-kacang-merah-top.jpg'],
                'variants' => [
                    ['name' => 'Porsi Reguler', 'sku' => 'MNM-KCG-44-REG', 'price' => 15000, 'stock' => 20],
                    ['name' => 'Porsi Jumbo Spesial', 'sku' => 'MNM-KCG-44-JMB', 'price' => 20000, 'stock' => 15],
                ]
            ],
            [
                'name' => 'Es Kietna Jeruk Kunci Segar Dingin',
                'sku' => 'MNM-KTN-45',
                'cat' => 'Minuman Tradisional',
                'cat_id' => 'CAT-08',
                'price' => 14000,
                'cost' => 7000,
                'weight' => 300,
                'stock' => 45,
                'desc' => 'Minuman asam manis pelepas dahaga dari fermentasi manisan kulit jeruk kesturi / kunci asli Bangka.',
                'comp' => 'Kulit jeruk kunci fermentasi gula batu, selasih, es batu',
                'images' => ['/images/es-kietna.jpg', '/images/es-kietna-segar.jpg'],
                'variants' => []
            ],
            [
                'name' => 'Kopi Robusta Semendo Asli Sumsel',
                'sku' => 'MNM-SMD-46',
                'cat' => 'Minuman Tradisional',
                'cat_id' => 'CAT-08',
                'price' => 18000,
                'cost' => 9000,
                'weight' => 200,
                'stock' => 50,
                'desc' => 'Seduhan biji kopi dataran tinggi Semendo Muara Enim, body tebal dengan aroma cokelat karamel khas.',
                'comp' => '100% Kopi Robusta Semendo giling medium dark',
                'images' => ['/images/kopi-semendo.jpg', '/images/kopi-semendo-cangkir.jpg', '/images/kopi-semendo-beans.jpg'],
                'variants' => [
                    ['name' => 'Kopi Hitam Tubruk', 'sku' => 'MNM-SMD-46-TBR', 'price' => 15000, 'stock' => 30],
                    ['name' => 'Kopi Susu Gula Aren', 'sku' => 'MNM-SMD-46-SSU', 'price' => 18000, 'stock' => 20],
                ]
            ],
            [
                'name' => 'Jus Sirsak Madu Selasih Segar',
                'sku' => 'MNM-SRS-47',
                'cat' => 'Minuman Tradisional',
                'cat_id' => 'CAT-08',
                'price' => 16000,
                'cost' => 8500,
                'weight' => 320,
                'stock' => 30,
                'desc' => 'Jus buah sirsak matang pohon segar dengan madu hutan alami dan biji selasih kenyal.',
                'comp' => 'Buah sirsak segar, madu murni, biji selasih, es batu',
                'images' => ['/images/jus-sirsak.jpg', '/images/jus-sirsak-glass.jpg'],
                'variants' => []
            ],
        ];

        foreach ($catalogRaw as $idx => $raw) {
            $variants = [];
            $hasVariants = !empty($raw['variants']);

            if ($hasVariants) {
                foreach ($raw['variants'] as $v) {
                    $variants[] = new VariantData(
                        name: $v['name'],
                        price: $v['price'],
                        sku: $v['sku'],
                        stock: $v['stock'],
                        externalId: 'VAR-' . $v['sku'],
                    );
                }
            }

            $items[] = new ProductData(
                name: $raw['name'],
                price: $raw['price'],
                externalId: 'EXT-' . ($idx + 100),
                sku: $raw['sku'],
                categoryName: $raw['cat'],
                categoryExternalId: $raw['cat_id'],
                shortDescription: $raw['desc'],
                description: $raw['desc'] . "\n\nKomposisi:\n" . $raw['comp'],
                composition: $raw['comp'],
                stock: $raw['stock'],
                weight: $raw['weight'],
                thumbnail: $raw['images'][0] ?? null,
                images: $raw['images'],
                variants: $variants,
                hasVariants: $hasVariants,
                costPrice: $raw['cost'] ?? (int)($raw['price'] * 0.6),
                priceStrikethrough: (int)($raw['price'] * 1.15),
                raw: $raw
            );
        }

        return $items;
    }
}
