# DocGen Implementation WP Class

- saya sudah upload sejumlah file, file file itu adalah refactories dari plugin Wordpress yang ada di githubnya arisciwek dengan repo DocGen Implementation Plugin.

- repo itu merupakan plugin yang bisa diaktivasi, memang tujuannya sebagai contoh implementasi.

- yang ingin saya kerjakan sakarang adalah mengubah plugin itu menjadi class, dengan file utama dari plugin itu menjadi main class.

- sehingga plugin yang bermaksud menggunakan class ini tinggal menyertakannya pada activator.

- class ini akan tetap bergantung kepada plugin lain, yakni yang ada di github arisciwek dengan repo WP Document Generator (WP DocGen), yang berfungsi sebagai processor.

- Repo DocGen Implementation Plugin juga menggunakan processor yang sama.

- kita sudah ada repo tersendiri untuk maintain codenya yakni github arisciwek dengan repo DocGen Implementation WP Class.

silahkan anda baca dulu referensinya.

Setelah membaca referensi, saya melihat struktur dan tujuannya:

1. WP DocGen (Processor) - Plugin yang berperan sebagai document processor
2. DocGen Implementation Plugin - Plugin contoh implementasi WP DocGen  
3. DocGen Implementation WP Class - Target refactoring ke class library

Dependensi:
- Class library tetap membutuhkan WP DocGen sebagai processor
- Tidak membutuhkan DocGen Implementation Plugin karena fungsionalitasnya akan direfactor ke class

Isi utama yang perlu dipertahankan:
- Directory handling (temp & template)
- Module system untuk extensibility
- Provider system untuk data source
- Security & validation
- AJAX handlers untuk UI

===
Di activator plugin Anggota Asosiasi perlu:

1. Include file class-dwpc.php
```php
require_once plugin_dir_path(__FILE__) . 'includes/class-dwpc.php';
```

2. Setup DocGen dengan konfigurasi
```php
docgen_wpclass()->setup([
    'temp_dir' => 'asosiasi-temp',
    'template_dir' => 'asosiasi-templates'
]);
```

3. Cek WP DocGen dependency

