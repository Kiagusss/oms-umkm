# Laravel File Upload — Size Limit Trap

## Gejala
User upload JPEG → muncul error generik "The images.0 failed to upload." (atau setara `validation.uploaded`).

## Akar masalah
1. **Batas PHP `upload_max_filesize` di php.ini** lebih kecil dari `max:` rule di Laravel. Mis. PHP=2M, Laravel=max:5120 (KB) = 5MB.
2. PHP menolak file sebelum sampai ke Laravel, sehingga `UploadedFile::isValid()` = false.
3. Laravel otomatis memicu rule `uploaded` (bukan `max`) → pesan default generik.

## Cara cek
```bash
php -r "echo 'upload_max_filesize='.ini_get('upload_max_filesize').PHP_EOL;"
php -r "echo 'post_max_size='.ini_get('post_max_size').PHP_EOL;"
```

## Solusi
1. **Selaraskan `max:` rule dengan PHP limit** (jangan set lebih besar dari `upload_max_filesize`).
   - Default `upload_max_filesize=2M` → pakai `max:2048` (KB).
2. **Custom message untuk rule `uploaded`** agar user tahu alasannya:
   ```php
   'thumbnail.uploaded' => 'Gambar gagal diunggah. Pastikan ukuran file ≤ 2 MB dan server mengizinkan upload.',
   ```
3. Kalau mau support file lebih besar, naikkan `upload_max_filesize` di php.ini.

## Pesan default vs pesan kustom
- Default: `'uploaded' => 'The :attribute failed to upload.'` — tidak informatif.
- Pesan muncul di `vendor/laravel/framework/src/Illuminate/Translation/lang/en/validation.php:166`.

## Rule fired
- `vendor/laravel/framework/src/Illuminate/Validation/Validator.php:715` — ketika `UploadedFile->isValid() === false`.

## Test pattern
- `UploadedFile::fake()->image()->size(3072)` mensimulasikan file 3 MB (di atas max 2 MB).
- Cek error message mengandung "2 MB" (bukan generic).

## Referensi pempek2
- Controller: `app/Http/Controllers/Admin/ProductController.php` (validateProduct)
- Tests: `tests/Feature/ProductImageUploadTest.php` (`file_over_2mb_is_rejected`, `upload_failure_shows_actionable_message`)