Penambahan field untuk Anggota

1. Database related:
- Perlu menambah kolom baru di tabel `asosiasi_members`
- sql/members.sql akan perlu diupdate untuk struktur tabel terbaru

2. Form & Display related:
- admin-add-member-page.php (form input utama)
- admin-view-member-page.php (tampilan detail member)

3. CRUD related:
- includes/class-asosiasi-crud.php (perlu menambah field baru di method create_member() dan update_member())

4. CSS related: 
- admin/css/form-style.css (mungkin perlu penyesuaian styling untuk field baru, terutama untuk field panjang seperti alamat)
- admin/css/view-member-style.css (mungkin perlu penyesuaian layout untuk menampilkan field tambahan)

5. Upgrade/Migration:
- includes/class-asosiasi-activator.php (untuk menangani upgrade database)

Setelah menganalisis existing code, ada beberapa hal yang perlu diperhatikan:

1. Struktur form yang ada menggunakan tabel layout dengan class "form-table"
2. Semua field saat ini menggunakan class "regular-text" untuk width standar
3. Ada sistem validasi existing yang perlu dipertahankan
4. Ada sistem sanitasi data yang konsisten
5. Ada logging system yang sudah terpasang

Dari field yang ingin ditambahkan, beberapa perlu perlakuan khusus:
- Alamat Perusahaan: Mungkin perlu textarea
- NPWP & Kode Pos: Perlu validasi format
- Kabupaten/Kota: Mungkin lebih baik pakai dropdown/select

Apakah ada preferensi khusus untuk validasi atau format dari field-field tersebut? Misalnya format NPWP atau batasan karakter untuk bidang usaha?
