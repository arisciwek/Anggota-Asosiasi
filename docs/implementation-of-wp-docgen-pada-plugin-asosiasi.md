# Implementation WP DocGen pada plugin Asosiasi.md

## WP_DocGen_Provider

File example-implementation-of-wp-docgen.md meunjukkan cara integrasi dan penggunaan wp-docgen. Dokumen tersebut menunjukkan:

1. Struktur integrasi yang diharapkan melalui interface `WP_DocGen_Provider`
2. Method-method yang harus diimplementasikan:
   - `get_data()`
   - `get_template_path()`
   - `get_output_filename()` 
   - `get_output_format()`
   - `get_temp_dir()`
3. Format fields yang didukung seperti:
   - Tanggal Indonesia
   - Format uang 
   - Terbilang
   - QR Code
   - Data user
4. Cara pengecekan dependensi dan error handling
5. Struktur file dan direktori yang diharapkan

Sebelum membuat implementasi, sebaiknya kita bahas dulu:

1. Path untuk template sertifikat - akan disimpan di mana?
2. Format data yang akan dimasukkan ke sertifikat
3. Nama class dan file untuk provider

## Analisis kode yang ada dan konteks yang diberikan:

1. Direktori Upload:
- Sudah ada struktur di `wp-upload/asosiasi-certificates/` (lihat di class-asosiasi-activator.php)
- Template sertifikat sebaiknya di `ASOSIASI_DIR . 'templates/'` sesuai struktur plugin

2. Data Member:
- Dari class-asosiasi-crud.php dan form member, data yang tersedia:
  - company_name
  - contact_person
  - email
  - phone
  - company_leader
  - leader_position 
  - business_field
  - ahu_number
  - npwp
  - company_address
  - city
  - postal_code
- Juga perlu memasukkan nomor sertifikat dan tanggal terbit

3. Penamaan yang Sesuai:
- Class: `Asosiasi_Member_Certificate` 
- Interface: `WP_DocGen_Provider`
- Template path helper: `member-certificate-templates.php`
- Log table: `asosiasi_certificate_log`

SQL dan aktivasi sudah disiapkan di activator. Selanjutnya kita perlu implementasikan:

1. Class utama untuk sertifikat 
2. Helper untuk template
3. Enqueue script untuk UI
4. AJAX handler untuk generate

## Lokasi template dan Penamaan class

1. Mengenai lokasi template:
- Di `/uploads/` bersifat publik dan bisa diakses langsung via URL
- File template .docx sebaiknya dilindungi dari akses langsung
- Lebih aman di dalam folder plugin `ASOSIASI_DIR . 'templates/'`
- Output sertifikat yang bisa didownload tetap di `/uploads/asosiasi-certificates/`

2. Penamaan lebih spesifik:
- `Asosiasi_Member_Certificate` - lebih jelas scopenya untuk member
- `member-certificate-templates.php` - konsisten dengan penandaan untuk member

Jadi kesimpulannya:
1. Template di `ASOSIASI_DIR . 'templates/'` (protected)
2. Output di `/uploads/asosiasi-certificates/` (downloadable)
3. Penamaan dengan prefix `member-certificate` 

## Template sertifikat harus bisa diupload 

1. Template sertifikat harus bisa diupload/diganti oleh pengguna, bukan hard-coded dalam plugin
2. Tetapi tetap perlu perlindungan agar file tidak bisa diakses langsung

Solusinya:
1. Buat folder khusus di uploads: `/uploads/asosiasi-members/templates/` 
2. Lindungi dengan .htaccess
3. Sediakan template default dalam plugin yang akan dicopy saat aktivasi
4. Buat form upload untuk mengganti template

Jadi flownya:
1. Saat aktivasi, copy template default ke folder uploads
2. User bisa upload template baru melalui form admin
3. Generate sertifikat menggunakan template aktif di uploads

Ini lebih fleksibel karena:
1. Template bisa diupdate tanpa mengubah plugin
2. Bisa punya multiple template
3. Tetap aman karena folder dilindungi

## Form uploads

Melihat konteks plugin yang ada:

1. Lokasi Form Upload:
- Lebih tepat di menu Settings sebagai tab baru
- Sudah ada struktur tab di `admin-settings-page.php`
- Bisa tambah tab "Certificate Templates" sejajar dengan:
  ```php
  $tabs = array(
      'general' => __('Pengaturan Umum', 'asosiasi'),
      'services' => __('Kelola Layanan', 'asosiasi'),
      'permissions' => __('Hak Akses Role', 'asosiasi'),
      'certificate' => __('Template Sertifikat', 'asosiasi') // Tab baru
  );
  ```

2. Nama Form:
- `member-certificate-template-form.php`
- Path: `admin/views/tabs/tab-certificate.php` (konsisten dengan tab lain)

Ini akan membuat:
1. Manajemen template terintegrasi dengan settings lainnya
2. UX konsisten dengan fitur lain
3. Penamaan file mengikuti pola yang sudah ada

## Tahapan Implementasi


Langkah pertama, saya akan membuat helper `member-certificate-templates.php` untuk mengelola template. File ini akan berisi fungsi-fungsi dasar untuk verifikasi dan manajemen template.

Boleh saya tunjukkan draft kode untuk `includes/helpers/member-certificate-templates.php` terlebih dahulu? Kita bisa review bersama sebelum lanjut ke langkah berikutnya.

Kode yang sudah saya siapkan mempunyai:
1. Fungsi untuk mendapatkan direktori template
2. Fungsi untuk verifikasi keberadaan template
3. Fungsi untuk menyalin template default
4. Dokumentasi lengkap dengan versi dan changelog
