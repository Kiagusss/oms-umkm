# Pempek Palembang

Website penjualan pempek Palembang dengan admin panel, POS, voucher, happy hour, dan AI chatbot "Dia Pempek".

**Stack**: Laravel 13 · PHP 8.3 · Tailwind CSS 4 · Vite · SQLite (dev) / MySQL (prod) · DomPDF

---

## Features

### Public site
- 🏠 Landing page dengan hero, banner, produk, paket, testimoni, FAQ, artikel
- 📦 Halaman detail produk (per kategori), artikel SEO-friendly
- � AI chatbot publik "Dia Pempek" (rate-limited 20 req/menit)
- 📱 WhatsApp-first order flow
- 🔍 Schema.org `Restaurant` + sitemap.xml + robots.txt
- 📊 Visitor tracker (privacy-friendly, hash IP)

### Admin panel (`/admin`)
- 📊 Dashboard dengan filter periode (harian/mingguan/bulanan)
- 🛒 **POS** dengan happy hour otomatis + voucher + localStorage cart hold
- 📝 CRUD: Produk, Kategori, Paket, Artikel, Banner, FAQ, Galeri, Testimoni, Voucher
- 📦 Manajemen pesanan + export struk PDF (DomPDF)
- ⚙️ Pengaturan (alamat, kontak, jam buka, social media)
- 🔧 SEO meta per halaman
- � AI chatbot internal admin (rate-limited 60 req/menit, baca seluruh DB, bisa mutasi via tools)

---

## Quick Start

### Requirements
- PHP 8.3+
- Composer 2.x
- Node.js 20+ & npm
- SQLite (dev) atau MySQL 8 (prod)

### Install
```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
php artisan storage:link
npm run build

# Run dev
php artisan serve          # → http://localhost:8000
npm run dev                # Vite HMR untuk asset
```

### Admin Login (default)
```
URL:      /admin/login
Email:    admin@pempek.com
Password: admin123
```
**⚠️ WAJIB ubah di production** — lihat section Production.

---

## Production Deployment

### 1. Environment
Salin `.env.example` → `.env` lalu isi sesuai environment production:
```bash
APP_ENV=production
APP_DEBUG=false             # WAJIB false
APP_URL=https://domain.com  # WAJIB pakai https
LOG_LEVEL=warning
DB_CONNECTION=mysql         # SQLite = bottleneck untuk concurrent write
```

### 2. Generate Secrets
```bash
# APP_KEY
php artisan key:generate

# Admin password (BUKAN plain text — pakai bcrypt hash)
php -r "echo password_hash('PASSWORD_ANDA', PASSWORD_BCRYPT);"
# → masukkan hash ke ADMIN_PASSWORD di .env

# Session & cache storage
php artisan session:table
php artisan cache:table
php artisan queue:table
php artisan migrate
```

### 3. Optimize
```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
npm run build
php artisan storage:link
```

### 4. Web Server
Pastikan document root mengarah ke `public/`, bukan root project.

**Nginx contoh** (lihat `deploy/nginx.conf`):
```nginx
root /var/www/pempek2/public;
```

### 5. HTTPS
- Provision SSL (Let's Encrypt via Certbot recommended)
- Middleware `ForceHttps` sudah aktif dengan status 308 (preserve POST method)

### 6. Backup
Setup cron untuk backup SQLite/MySQL ke S3 atau remote:
```bash
# Contoh cron harian jam 3 pagi
0 3 * * * cd /var/www/pempek2 && php artisan backup:run
```

---

## Security

- ✅ HTTPS redirect via middleware (308, bukan 301)
- ✅ CSRF protection (Laravel default + meta token di layout)
- ✅ Security headers (X-Frame-Options, X-Content-Type-Options, Referrer-Policy, HSTS)
- ✅ Rate limit chat API (publik 20/menit, admin 60/menit)
- ✅ Rate limit login (5 attempt/menit per IP — lihat `AuthController`)
- ✅ Upload validation: `image|mimes:jpeg,png,jpg,webp,gif|max:4096`
- ✅ Admin password via bcrypt hash (di-set lewat env, bukan plain text)

**Diluar scope (TODO)**:
- 2FA untuk admin
- Audit log untuk perubahan data sensitif
- File upload virus scan

---

## Testing

```bash
php artisan test                    # semua test
php artisan test --filter=Pos       # test POS
php artisan test --filter=Voucher   # test voucher
```

10 Feature tests tersedia:
- `PosCheckoutTest`, `PosCheckoutVoucherTest`, `PosCheckoutVoucherTest`
- `VoucherApplyTest`, `VoucherAdminTest`
- `HappyHourSettingTest`, `HappyHourCheckoutTest`, `HappyHourPosTest`
- `StrukExportTest`

---

## Project Structure

```
app/
  Http/Controllers/    # Admin + Api controllers
  Http/Middleware/     # AdminAuth, ForceHttps, SecurityHeaders
  Models/              # 13 Eloquent models
  Services/            # TransactionService, HappyHourService, SalesReportService
database/
  migrations/          # 4 migrations (all_tables + payment_method + vouchers)
  seeders/             # DatabaseSeeder
resources/
  views/               # Public + admin Blade templates
routes/
  web.php              # Landing + admin routes
  api.php              # JSON API (chat, upload, POS, voucher)
  sitemap.php          # robots.txt + sitemap.xml
tests/
  Feature/             # 10 feature tests
```

---

## Environment Variables

Lihat `.env.example` untuk daftar lengkap. Yang **WAJIB** diisi di production:

| Variable | Wajib | Keterangan |
|---|---|---|
| `APP_KEY` | ✅ | Generate: `php artisan key:generate` |
| `APP_URL` | ✅ | Harus pakai `https://` di production |
| `DB_*` | ✅ | MySQL untuk production |
| `ADMIN_EMAIL` | ✅ | Default: `admin@pempek.com` (UBAH!) |
| `ADMIN_PASSWORD` | ✅ | Bcrypt hash, BUKAN plain text |
| `WA_NUMBER` | ✅ | Format intl tanpa `+`, misal `6281234567890` |
| `SESSION_ENCRYPT` | ✅ | Set `true` di production |
| `LOG_LEVEL` | ✅ | `warning` atau `error` di production |

---

## License

MIT — lihat `LICENSE`.

---

## Credits

Built with [Laravel](https://laravel.com). UI mengikuti design system internal Pempek (`design-system/pempek`).
