# Sistem Tugas Akhir & Skripsi — Modul Ruang & Fasilitas Sidang

Modul **Individu D** untuk pengelolaan master gedung, ruang ujian/sidang, fasilitas
ruangan (proyektor, PC, audio, jaringan, dan lainnya), serta penjadwalan sidang
beserta laporan dan API RESTful.

Aplikasi dibangun dengan **Laravel 12**, **Tailwind CSS 4**, dan **Vite**, memakai
database **MySQL** (XAMPP) sehingga data dapat dipantau melalui **phpMyAdmin**.

---

## Fitur Utama

| Area | Cakupan |
| --- | --- |
| Autentikasi | Login/register, logout, profil, ganti password, token API pribadi |
| Hak akses | `admin`, `operator`, `viewer` dengan pembatasan tulis melalui Gate `manage-module` |
| Master gedung | CRUD gedung, kode unik, jumlah lantai, alamat, status aktif |
| Master ruang | CRUD ruang ujian/sidang, gedung, lantai, kapasitas, jenis ruang, status |
| Master fasilitas | CRUD fasilitas, kategori, satuan, status aktif |
| Fasilitas per ruang | Penempatan fasilitas pada ruang dengan jumlah, kondisi, dan catatan |
| Penjadwalan sidang | CRUD jadwal, validasi bentrok ruang, status sidang, filter tanggal/ruang |
| Dashboard | Ringkasan statistik, jadwal berlangsung, agenda mingguan, kondisi fasilitas |
| Laporan | Rekap ruang & jadwal, filter periode, cetak, ekspor CSV |
| API | Endpoint baca publik dan endpoint tulis dengan Bearer token |
| Audit | Log aktivitas perubahan data pada dashboard dan halaman detail |

### Aturan penjadwalan

- Jadwal pada ruang yang sama tidak boleh tumpang tindih waktu.
- Jadwal yang bersambung (selesai 10.00, mulai 10.00) diperbolehkan.
- Ruang berstatus perbaikan tidak dapat dijadwalkan.
- Jadwal berstatus **dibatalkan** tidak menghalangi ruang.

---

## Akun Demo

Jalankan seeder, lalu masuk melalui `/login`.

| Peran | Email | Password | Token API |
| --- | --- | --- | --- |
| Admin | `admin@ta.test` | `password` | `ta-admin-demo-token` |
| Operator | `operator@ta.test` | `password` | `ta-operator-demo-token` |
| Viewer (hanya baca) | `viewer@ta.test` | `password` | `ta-viewer-demo-token` |

> Token hanya ditampilkan sekali pada halaman profil setelah dibuat ulang.
> Nilai token di atas adalah token demo dari seeder; di database disimpan sebagai hash SHA-256.

---

## Instalasi

Prasyarat: PHP 8.2+, Composer, Node.js 20+, dan XAMPP (MySQL/MariaDB + phpMyAdmin).

1. Aktifkan **Apache** dan **MySQL** melalui XAMPP Control Panel.
2. Buat database `modul_d_new` melalui phpMyAdmin atau terminal:

   ```sql
   CREATE DATABASE modul_d_new CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   ```

3. Salin `.env.example` menjadi `.env`, lalu sesuaikan:

   ```env
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=modul_d_new
   DB_USERNAME=root
   DB_PASSWORD=
   ```

4. Jalankan migrasi, seeder, dan build aset:

   ```bash
   composer install
   copy .env.example .env
   php artisan key:generate
   php artisan migrate:fresh --seed
   npm install
   npm run build
   php artisan serve
   ```

Buka `http://127.0.0.1:8000`. phpMyAdmin dapat dibuka di
`http://localhost/phpmyadmin` dan pilih database `modul_d_new`.

Untuk pengembangan frontend:

```bash
npm run dev
```

### Menjalankan pengujian

```bash
php artisan test
```

Seluruh fitur utama tercakup dalam 47 pengujian (autentikasi, gedung, ruang,
fasilitas, jadwal, laporan, profil, dan API).

---

## Struktur Halaman

| URL | Keterangan |
| --- | --- |
| `/` | Dashboard |
| `/gedung` | Master gedung |
| `/ruang` | Master ruang & fasilitas per ruang |
| `/fasilitas` | Master fasilitas |
| `/jadwal` | Penjadwalan sidang |
| `/laporan` | Laporan & ekspor |
| `/laporan/cetak` | Tampilan laporan siap cetak |
| `/profil` | Profil, password, token API |
| `/dokumentasi-api` | Dokumentasi API interaktif |

---

## API RESTful

Base URL: `/api/v1`

### Endpoint baca (publik)

```http
GET /api/v1
GET /api/v1/buildings
GET /api/v1/buildings/{id}
GET /api/v1/rooms
GET /api/v1/rooms/{id}
GET /api/v1/facilities
GET /api/v1/facilities/{id}
GET /api/v1/schedules
GET /api/v1/schedules/{id}
```

Parameter filter yang tersedia:

- Semua resource: `q`, `per_page` (1–100), `page`
- `buildings`: `status`
- `rooms`: `building_id`, `type`, `status`, `facility_id`, `min_capacity`
- `facilities`: `category`, `status`
- `schedules`: `room_id`, `building_id`, `status`, `type`, `date_from`, `date_to`

### Endpoint tulis (Bearer token)

```http
POST   /api/v1/buildings
PUT    /api/v1/buildings/{id}
DELETE /api/v1/buildings/{id}

POST   /api/v1/rooms
PUT    /api/v1/rooms/{id}
DELETE /api/v1/rooms/{id}

POST   /api/v1/facilities
PUT    /api/v1/facilities/{id}
DELETE /api/v1/facilities/{id}

POST   /api/v1/schedules
PUT    /api/v1/schedules/{id}
DELETE /api/v1/schedules/{id}
```

Contoh:

```bash
curl -X POST http://127.0.0.1:8000/api/v1/buildings \
  -H "Authorization: Bearer ta-admin-demo-token" \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -d "{\"code\":\"GD-04\",\"name\":\"Gedung D\",\"floors\":3,\"is_active\":true}"
```

Token `viewer` hanya dapat membaca; endpoint tulis akan mengembalikan `403`.

---

## Struktur Data

- `users` — akun, peran, status aktif, hash token API
- `buildings` — master gedung
- `rooms` — master ruang, relasi ke gedung
- `facilities` — master fasilitas
- `facility_room` — pivot penempatan fasilitas (`quantity`, `condition`, `note`)
- `defense_sessions` — jadwal sidang, relasi ke ruang
- `activity_logs` — jejak aktivitas perubahan data

---

## Teknologi

- Laravel 12 (MVC, Eloquent, Form Request, middleware, Gate)
- Tailwind CSS 4 + Vite
- Alpine-free vanilla JavaScript untuk interaksi UI (modal konfirmasi, filter otomatis, cek ketersediaan jadwal)
- MySQL / MariaDB (XAMPP) + phpMyAdmin
- PHPUnit / Pest-style Laravel feature tests

---

## Catatan Pengembangan

- Aset produksi dibangun ke `public/build` melalui `npm run build`.
- Cache view dapat dibersihkan dengan `php artisan view:clear`.
- Semua label antarmuka menggunakan Bahasa Indonesia.
- Modul ini berdiri sendiri dan tidak bergantung pada modul anggota kelompok lain.
