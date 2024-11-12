# Panduan Patch untuk Plugin WordPress

## Prinsip Dasar
1. Setiap patch harus:
   - Mencakup file lengkap dengan path relatif
   - Menyertakan changelog di header file
   - Mengikuti sistem versioning yang konsisten
   - Menghindari penambahan fitur yang tidak diminta
   - Memastikan kompatibilitas dengan kode existing

## Format Versioning & Changelog
```php
/**
 * Plugin Name
 * 
 * @package YourPlugin
 * @version 1.2.3
 * 
 * Changelog:
 * 1.2.3 - 2024-03-15
 * - Fixed specific issue
 * - Updated specific function
 * 
 * 1.2.2 - 2024-03-14
 * - Previous changes
 */
```

## Struktur Patch yang Benar
```patch
diff --git a/plugin-dir/file.php b/plugin-dir/file.php
index abc123..def456 100644
--- a/plugin-dir/file.php
+++ b/plugin-dir/file.php
@@ -10,7 +10,7 @@ FunctionName
 // Content changes
```

## Langkah Membuat Patch

### 1. Persiapan
```bash
# Review file yang akan diubah
cat existing-file.php

# Cek status git
git status
```

### 2. Generate Patch
```bash
# Dari staged changes (DIREKOMENDASIKAN)
git add path/to/file.php
git diff --cached --no-prefix > revisi.patch

# Atau dari working directory (TIDAK DIREKOMENDASIKAN)
git diff --no-prefix path/to/file.php > revisi.patch
```

### 3. Verifikasi Format
- Header lengkap (diff --git, index, ---, +++)
- Chunk header dengan context (@@ -line,count +line,count @@ FunctionName)
- No trailing whitespace
- Consistent line endings (LF)

### 4. Validasi Patch
```bash
# Test patch
git apply --check revisi.patch

# Check whitespace issues
git diff --check
```

## Checklist Sebelum Submit

### Dokumentasi
- [ ] Version number diupdate
- [ ] Changelog ditambahkan
- [ ] DocBlocks diupdate jika diperlukan

### Format
- [ ] Tidak ada trailing whitespace
- [ ] Line endings konsisten (LF)
- [ ] Indentasi sesuai dengan file asli
- [ ] Path menggunakan prefix a/ dan b/

### Konten
- [ ] Hanya memperbaiki issue yang diminta
- [ ] Tidak ada penambahan fitur yang tidak perlu
- [ ] Kompatibel dengan kode existing
- [ ] Tidak mengubah struktur database tanpa perlu

## Troubleshooting

### 1. Error "corrupt patch"
```
error: corrupt patch at line 24
```
Penyebab umum:
- Indentasi di header patch
- Mixed line endings
- Trailing whitespace
- Format chunk header tidak sesuai

### 2. Error "patch fragment without header"
```
error: patch fragment without header at line 22
```
Solusi:
- Tambahkan function context di chunk header
- Gunakan `git diff -p` untuk generate

### 3. Empty Patch
Periksa:
- File sudah di-stage (`git add`)
- Path file benar
- Ada perubahan yang belum di-commit

## Best Practices

1. Update Version Number
```php
define('PLUGIN_VERSION', '1.2.3');
```

2. Maintain Changelog
```php
/**
 * Changelog:
 * 1.2.3 - 2024-03-15
 * - Fixed {specific issue}
 * - Updated {specific function}
 */
```

3. Document Functions
```php
/**
 * Function description
 * 
 * @since 1.2.3 Added new parameter
 * @param string $param Description
 * @return void
 */
```

## Workflow yang Direkomendasikan

1. Review Issue
   - Baca issue dengan teliti
   - Identifikasi file yang perlu diubah
   - Pastikan scope perubahan

2. Backup & Prepare
   ```bash
   # Backup file asli
   cp file.php file.php.bak
   
   # Review existing code
   cat file.php
   ```

3. Buat Perubahan
   - Update version number
   - Tambah changelog
   - Buat perubahan yang diperlukan
   - Test perubahan

4. Generate & Verify Patch
   ```bash
   # Generate patch
   git diff --no-prefix > revisi.patch
   
   # Verify
   git apply --check revisi.patch
   ```

5. Review Final
   - Cek format patch
   - Validasi perubahan
   - Test fungsionalitas

Remember: Patch yang baik adalah patch yang minimal, fokus pada perbaikan yang diminta, dan tidak menimbulkan masalah baru.