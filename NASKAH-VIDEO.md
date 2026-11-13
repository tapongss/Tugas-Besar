# Naskah Narasi Video — CRUD, MVC, dan Database (Modul Ruang & Fasilitas Sidang)

**Nama:** Tavi Reyhan Ath Thariq
**NIM:** 102022300101
**Modul:** Individu D — Ruang & Fasilitas Sidang
**Database:** MySQL `modul_d_new` (XAMPP/phpMyAdmin)
**Durasi target:** 9–10 menit

> Bagian dalam tanda kurung dan *miring* adalah arahan adegan, bukan untuk dibaca.

---

## Checklist Wajib Sebelum Merekam

Pastikan semua poin berikut benar-benar terekam. Ini menjawab tiga catatan penilaian:

- [ ] **Kondisi awal database** ditampilkan sebelum data diubah: jumlah baris, struktur kolom, dan contoh isi tabel.
- [ ] **Proses CRUD** dijelaskan: Create, Read, Update, Delete beserta route dan method yang dipakai.
- [ ] **Model, Controller, Route, dan Blade** dijelaskan dengan file aslinya.
- [ ] Kondisi database **sebelum dan sesudah** Create, Update, dan Delete terlihat.
- [ ] Nama dan NIM disebut di awal video.

**Persiapan:**

```bash
cd D:\xampp\htdocs\modul-d-new
php artisan serve
```

Buka `http://127.0.0.1:8000` dan `http://localhost/phpmyadmin`. Pastikan tabel `facilities` berisi **11 baris** sebelum merekam. Jika ada data uji sisa, hapus dulu agar kondisi awal bersih.

---

## 0:00 — Pembuka

*(Tampilkan halaman login, sorot nama dan NIM di layar)*

“Halo, perkenalkan. Saya **Tavi Reyhan Ath Thariq**, NIM **102022300101**. Saya mengambil **Huruf Modul Individual D**, yaitu **Modul Ruang dan Fasilitas Sidang**.

Pada video ini saya akan menjelaskan dan mendemonstrasikan **proses CRUD** pada aplikasi Laravel ini, yaitu **Create, Read, Update, dan Delete**. Saya juga akan menjelaskan peran **Route, Controller, Model, dan Blade** dalam alur MVC, serta menunjukkan **kondisi awal database sebelum data diubah** dan kondisi setelah setiap perubahan.”

---

## 0:40 — 1: Kondisi Awal Database Sebelum Data Diubah

*(Buka phpMyAdmin → database `modul_d_new` → klik tabel `facilities`)*

“Langkah pertama, saya tunjukkan **kondisi awal database sebelum ada data yang diubah**. Ini database `modul_d_new` di phpMyAdmin. Saya membuka tabel `facilities`, yaitu tabel master jenis fasilitas.”

*(Klik tab **Structure**. Sorot kolom satu per satu)*

“Struktur tabelnya terdiri dari `id` sebagai primary key, `code` sebagai kode unik fasilitas, `name`, `category`, `unit`, `description`, `is_active` untuk status aktif, serta `created_at` dan `updated_at` yang diisi otomatis oleh Laravel.”

*(Klik tab **Browse**)*

“Sekarang saya buka isinya. Kondisi awal berisi **11 baris data** hasil seeder. Saya catat beberapa data yang sudah ada, misalnya `FS-PRJ` untuk Proyektor Epson, `FS-PC` untuk PC All-in-One, dan `FS-AC` untuk AC Split. **Jumlah awal: 11 baris, id terakhir 11.**

Saya juga bisa membuktikannya lewat query SQL.”

*(Buka tab SQL, jalankan)*

```sql
SELECT COUNT(*) AS total FROM facilities;
```

“Hasilnya **11**. Angka inilah yang akan menjadi pembanding: setelah Create harus menjadi 12, setelah Delete harus kembali menjadi 11.”

*(Kembali ke browser aplikasi)*

“Perlu saya tegaskan, inilah kondisi database **sebelum** saya melakukan perubahan apa pun. Selanjutnya saya jelaskan dulu bagaimana aplikasi Laravel ini dibangun.”

---

## 1:30 — 2. Penjelasan CRUD, MVC, Route, Controller, Model, dan Blade

*(Buka `routes/web.php`, tunjukkan baris `Route::resource('fasilitas', ...)`)*

### 2A. Route

“Di Laravel, **Route** adalah pintu masuk request dari browser. Saya membuka `routes/web.php`. Di sini ada baris:

```php
Route::resource('fasilitas', FacilityController::class)
    ->parameters(['fasilitas' => 'facility'])
    ->names('facilities');
```

`Route::resource` otomatis membuat **tujuh route CRUD** sekaligus. Pemetaannya seperti ini.”

*(Tampilkan tabel atau teks di layar)*

| Operasi | HTTP Method | URL | Method Controller | Nama Route |
|---|---|---|---|---|
| Read (daftar) | GET | `/fasilitas` | `index` | `facilities.index` |
| Read (detail) | GET | `/fasilitas/{id}` | `show` | `facilities.show` |
| Create (form) | GET | `/fasilitas/create` | `create` | `facilities.create` |
| Create (simpan) | POST | `/fasilitas` | `store` | `facilities.store` |
| Update (form) | GET | `/fasilitas/{id}/edit` | `edit` | `facilities.edit` |
| Update (simpan) | PUT/PATCH | `/fasilitas/{id}` | `update` | `facilities.update` |
| Delete | DELETE | `/fasilitas/{id}` | `destroy` | `facilities.destroy` |

“Route untuk create, store, edit, dan update dilindungi middleware `can:manage-module`, jadi hanya admin dan operator yang boleh menambah serta mengubah data. Khusus route destroy dilindungi `can:delete-module`, sehingga hanya admin yang boleh menghapus. Viewer hanya bisa membaca.”

### 2B. Controller

*(Buka `app/Http/Controllers/FacilityController.php`)*

“**Controller** adalah tempat logika aplikasi. File `FacilityController` memiliki method yang sesuai dengan route tadi: `index` untuk menampilkan daftar, `create` untuk membuka form, `store` untuk menyimpan data baru, `show` untuk detail, `edit` untuk membuka form edit, `update` untuk menyimpan perubahan, dan `destroy` untuk menghapus. Controller inilah yang memanggil Model dan mengirim data ke Blade.”

### 2C. Model

*(Buka `app/Models/Facility.php`)*

“**Model** adalah representasi tabel database dalam bentuk class PHP. File `Facility` mewarisi `Illuminate\Database\Eloquent\Model`, sehingga otomatis terhubung ke tabel `facilities`. Di dalamnya ada properti `$fillable` yang mendaftarkan kolom yang boleh diisi massal: `code`, `name`, `category`, `unit`, `description`, dan `is_active`. Model juga mendefinisikan relasi `rooms()` ke tabel ruang, serta scope `filter()` untuk pencarian.”

“Dengan Model, saya tidak menulis query SQL manual. Cukup panggil `Facility::create()`, `Facility::query()`, atau `$facility->update()`, lalu Eloquent yang menerjemahkannya menjadi perintah SQL.”

### 2D. Blade

*(Buka `resources/views/facilities/index.blade.php`)*

“**Blade** adalah template engine Laravel untuk membuat tampilan. File berekstensi `.blade.php`. Di dalamnya ada `@extends('layouts.app')` untuk memakai layout utama, `@section('content')` untuk mengisi bagian konten, `@include(...)` untuk memanggil potongan tampilan lain, dan `@foreach` untuk menampilkan data dari controller menjadi baris tabel. Blade dikompilasi menjadi HTML yang dikirim ke browser.”

### 2E. Alur MVC

*(Tampilkan diagram sederhana di layar)*

“Jadi alurnya: **Browser → Route → Controller → Model/Eloquent → Database MySQL → Controller → Blade → HTML ke browser**. Itulah pola MVC yang dipakai aplikasi ini.”

---

## 3:30 — 3. Create (Menambah Data)

*(Login sebagai admin → menu **Fasilitas** → tombol **Tambah Fasilitas**)*

“Sekarang saya demonstrasikan operasi **Create**. Saya login sebagai admin, membuka menu Fasilitas, lalu klik **Tambah Fasilitas**. Halaman ini memakai route `facilities.create` yang memanggil method `create` di controller, lalu menampilkan Blade `facilities/create.blade.php`.”

*(Isi form perlahan)*

“Saya isi data berikut:

- Kode: `FS-LSR`
- Nama: **Proyektor Laser Epson EB-L200**
- Kategori: **Display & Proyeksi**
- Satuan: `unit`
- Deskripsi: **Proyektor laser 4500 lumens dengan koneksi HDMI, USB, dan LAN**
- Status aktif: **menyala**”

*(Scroll ke bagian kode form, tunjukkan `@csrf` dan `@error`)*

“Form ini ada di `resources/views/facilities/_form.blade.php`. Perhatikan tiga hal penting. Pertama, `method="POST"` dan `action` mengarah ke route `facilities.store`. Kedua, `@csrf` membuat token keamanan agar form tidak bisa dipalsukan. Ketiga, setiap input memakai `old('code')`, `old('name')`, dan seterusnya supaya data yang sudah diketik tidak hilang jika validasi gagal. `@error` menampilkan pesan kesalahan di bawah field terkait.”

*(Klik **Simpan Fasilitas**)*

“Saya klik Simpan. Request POST dikirim ke route `facilities.store`, lalu diteruskan ke method `store`.”

*(Buka `app/Http/Controllers/FacilityController.php`, sorot method `store`)*

“Ini method `store`. Parameter `FacilityRequest` menjalankan validasi terlebih dahulu. Jika lolos, baris `Facility::create($request->validated())` memerintahkan Model meng-insert data ke tabel `facilities`. Setelah itu aktivitas dicatat, dan aplikasi redirect ke halaman daftar dengan pesan sukses.”

*(Buka `app/Http/Requests/FacilityRequest.php`)*

“Aturan validasinya ada di `FacilityRequest`: `code` wajib, maksimal 20 karakter, hanya huruf/angka/strip/garis bawah, dan harus unik; `name` wajib maksimal 120 karakter; `category` harus salah satu kategori yang tersedia; `unit` wajib; `description` opsional; `is_active` berupa boolean. Method `prepareForValidation` juga mengubah kode menjadi huruf besar secara otomatis.”

*(Tunjukkan halaman daftar dengan pesan sukses dan baris FS-LSR)*

“Data **FS-LSR** sekarang muncul di daftar. Berarti Create berhasil.”

*(Kembali ke phpMyAdmin → Refresh tabel `facilities`)*

“Saya kembali ke phpMyAdmin dan refresh. Jumlah baris berubah dari **11 menjadi 12**. Baris baru berisi `FS-LSR`, nama **Proyektor Laser Epson EB-L200**, kategori `display`, satuan `unit`, status aktif `1`, serta `created_at` dan `updated_at` yang terisi otomatis. Inilah bukti data dari form benar-benar masuk ke MySQL.”

---

## 4:55 — 4. Read (Menampilkan Data)

*(Buka halaman **Fasilitas**)*

“Operasi kedua adalah **Read**. Halaman daftar ini ditangani method `index` di controller.”

*(Buka `FacilityController.php`, sorot method `index`)*

“Method `index` memakai `Facility::query()` lalu menerapkan `filter()` untuk pencarian, kategori, dan status, mengurutkan berdasarkan kategori dan nama, serta `paginate(10)` untuk membagi data per halaman. Hasilnya dikirim ke Blade `facilities.index`.”

*(Buka `resources/views/facilities/index.blade.php`, sorot `@foreach`)*

“Di Blade, data ditampilkan dengan `@foreach ($facilities as $facility)`. Setiap item menjadi satu baris tabel: nama, kode, kategori, jumlah ruang, total unit, dan status. Ini adalah operasi Read dari CRUD.”

*(Klik nama FS-LSR untuk membuka detail)*

“Saya klik nama fasilitas untuk membuka halaman detail. Ini memakai route `facilities.show` dan method `show`, yang mengambil satu data berdasarkan id beserta relasi ruang yang memakainya. Jadi Read terdiri dari dua tampilan: daftar dan detail.”

---

## 6:00 — 5. Update (Mengubah Data)

*(Buka phpMyAdmin, tunjukkan baris FS-LSR sebelum diubah)*

“Sebelum mengubah data, saya tunjukkan dulu **kondisi database sebelum Update**. Baris `FS-LSR` masih memiliki deskripsi **Proyektor laser 4500 lumens dengan koneksi HDMI, USB, dan LAN** dan `updated_at` tertentu. Saya catat kondisi ini.”

*(Kembali ke aplikasi → klik ikon Edit pada baris FS-LSR)*

“Sekarang saya klik Edit. Halaman ini memakai route `facilities.edit` dan method `edit`. Form yang sama dipakai ulang, tetapi karena datanya sudah ada, `action` berubah ke route `facilities.update` dan Blade menambahkan `@method('PUT')`.”

*(Ubah deskripsi dan status)*

“Saya ubah deskripsinya menjadi **Proyektor laser 5000 lumens dengan HDMI, USB, LAN, dan Wi-Fi**, lalu saya matikan status aktif untuk sementara. Saya klik **Simpan Perubahan**.”

*(Buka controller, sorot method `update`)*

“Method `update` menerima `FacilityRequest` dan objek `Facility` yang dicari otomatis berdasarkan id. Baris `$facility->update($request->validated())` menjalankan perintah UPDATE ke database.”

*(Kembali ke phpMyAdmin → Refresh)*

“Setelah refresh, **jumlah baris tetap 12** karena ini bukan penambahan data. Yang berubah adalah kolom `description` dan `is_active`, serta `updated_at` yang diperbarui otomatis. Ini membuktikan operasi Update berjalan.”

*(Kembalikan status aktif menjadi menyala agar data demo tetap rapi)*

“Saya aktifkan kembali statusnya supaya data demo konsisten.”

---

## 7:20 — 6. Delete (Menghapus Data)

*(Buka phpMyAdmin, tunjukkan baris FS-LSR masih ada dan jumlah 12)*

“Terakhir adalah **Delete**. Sebelum menghapus, saya tunjukkan lagi kondisi database: baris `FS-LSR` masih ada dan jumlahnya **12**. Perlu dicatat, hak hapus hanya dimiliki admin; akun operator hanya bisa melihat, menambah, dan mengubah data.”

*(Kembali ke aplikasi → klik ikon Hapus pada baris FS-LSR)*

“Saya klik ikon Hapus. Aplikasi menampilkan modal konfirmasi **Hapus fasilitas?** dengan pesan bahwa data akan dihapus permanen. Saya konfirmasi.”

*(Buka controller, sorot method `destroy`)*

“Method `destroy` menerima objek `Facility`. Ada pengaman: jika fasilitas masih terpasang di ruang, penghapusan ditolak dengan pesan agar dilepas dari ruang terlebih dahulu. Jika tidak, `$facility->delete()` menjalankan perintah DELETE. Form hapus di Blade mengirim method `DELETE` melalui `@method('DELETE')` dan route `facilities.destroy`.”

*(Tunjukkan daftar tanpa FS-LSR, lalu phpMyAdmin → Refresh)*

“Di daftar, **FS-LSR** sudah tidak ada. Di phpMyAdmin, jumlah baris kembali dari **12 menjadi 11**, sama seperti kondisi awal. Ini membuktikan siklus CRUD lengkap: Create menambah, Read menampilkan, Update mengubah, dan Delete menghapus.”

---

## 8:40 — 7. Ringkasan dan Penutup

*(Kembali ke halaman daftar fasilitas atau dashboard)*

“Sebagai ringkasan, aplikasi ini menggunakan pola MVC Laravel:

- **Route** di `routes/web.php` menerima request dan mengarahkannya ke controller.
- **Controller** `FacilityController` menjalankan logika Create, Read, Update, dan Delete.
- **Model** `Facility` menghubungkan aplikasi ke tabel `facilities` lewat Eloquent.
- **Blade** di `resources/views/facilities` menampilkan form dan daftar data.

Saya telah menunjukkan **kondisi awal database 11 baris sebelum data diubah**, lalu Create menjadi 12 baris, Update mengubah isi baris tanpa menambah jumlah, dan Delete mengembalikannya menjadi 11 baris. Dengan demikian, seluruh proses CRUD dan hubungan Route, Controller, Model, serta Blade sudah didemonstrasikan.

Aplikasi ini juga telah diuji dengan **47 pengujian otomatis** dan seluruhnya berhasil. Terima kasih.”

---

## Lampiran A — Query Bukti untuk Direkam

Jalankan di tab SQL phpMyAdmin pada momen yang disebutkan.

**Sebelum Create:**

```sql
SELECT COUNT(*) AS total FROM facilities;
-- hasil: 11

SELECT id, code, name, category, unit, is_active
FROM facilities
ORDER BY id;
```

**Sesudah Create:**

```sql
SELECT COUNT(*) AS total FROM facilities;
-- hasil: 12

SELECT * FROM facilities WHERE code = 'FS-LSR';
```

**Sesudah Update:**

```sql
SELECT code, name, description, is_active, updated_at
FROM facilities
WHERE code = 'FS-LSR';
```

**Sesudah Delete:**

```sql
SELECT COUNT(*) AS total FROM facilities;
-- hasil: 11

SELECT * FROM facilities WHERE code = 'FS-LSR';
-- hasil: kosong
```

---

## Lampiran B — File yang Ditunjukkan di Video

| Bagian | File | Yang disorot |
|---|---|---|
| Route | `routes/web.php` | `Route::resource('fasilitas', ...)`, middleware `can:manage-module` |
| Controller | `app/Http/Controllers/FacilityController.php` | `index`, `create`, `store`, `show`, `edit`, `update`, `destroy` |
| Model | `app/Models/Facility.php` | `class Facility extends Model`, `$fillable`, relasi `rooms()` |
| Validasi | `app/Http/Requests/FacilityRequest.php` | `authorize`, `prepareForValidation`, `rules` |
| Form Blade | `resources/views/facilities/_form.blade.php` | `@csrf`, `@method('PUT')`, `old()`, `@error` |
| Daftar Blade | `resources/views/facilities/index.blade.php` | `@foreach`, route `edit`/`show`/`destroy`, `data-confirm` |
| Detail Blade | `resources/views/facilities/show.blade.php` | data fasilitas dan relasi ruang |

---

## Lampiran C — Nilai Demo yang Konsisten

Gunakan nilai ini agar narasi dan hasil di database cocok:

| Field | Nilai |
|---|---|
| Kode | `FS-LSR` |
| Nama | `Proyektor Laser Epson EB-L200` |
| Kategori | `Display & Proyeksi` (`display`) |
| Satuan | `unit` |
| Deskripsi awal | `Proyektor laser 4500 lumens dengan koneksi HDMI, USB, dan LAN` |
| Deskripsi setelah Update | `Proyektor laser 5000 lumens dengan HDMI, USB, LAN, dan Wi-Fi` |
| Status | aktif (`1`), sempat dimatikan saat Update, lalu diaktifkan kembali |
| Jumlah awal | 11 baris |
| Setelah Create | 12 baris |
| Setelah Delete | 11 baris |
