# Praktikum & Tugas Mandiri: REST API CRUD dengan PHP dan Postman

**Mata Kuliah:** Teknologi Web Service  
**Pertemuan:** 4 — Create, Read, Update, Delete dengan PHP dan Postman  
**Program Studi:** S1 Informatika  

---

## Deskripsi Singkat

Proyek ini merupakan penyelesaian komprehensif untuk **Praktikum Pertemuan 4** dan **Tugas Mandiri (Wajib)**. Implementasi dibangun secara *robust* dengan persistensi data berbasis file storage JSON, validasi masukan ketat, penanganan kode status HTTP yang presisi, serta pengujian sistematis menggunakan Postman Collection.

### Fitur & Keunggulan Implementasi:
1. **Full CRUD (C-R-U-D):**
   - **Create (`POST`)**: Pembuatan data baru, auto-increment ID, header `Location`, status `201 Created`.
   - **Read (`GET`)**: Ambil semua data (`200 OK`) dan ambil data berdasarkan ID spesifik (`200 OK` / `404 Not Found`).
   - **Update Total (`PUT`)**: Penggantian seluruh field data resource (`200 OK` / `400 Bad Request` jika field tidak lengkap).
   - **Update Parsial (`PATCH`)**: Pembaruan field spesifik tanpa mengubah field lainnya (`200 OK`).
   - **Delete (`DELETE`)**: Penghapusan resource (`204 No Content` tanpa body respon).
2. **Persistensi Data Antar-Request**: Perubahan data (tambah, ubah, hapus) benar-benar tersimpan ke file JSON lokal, sehingga pengujian berurutan (misal `DELETE` berulang) bekerja sesuai ekspektasi.
3. **Validasi & Penanganan Kasus Gagal (Edge Cases)**:
   - `400 Bad Request`: Field wajib kosong, harga/stok bernilai negatif, parameter ID bukan integer, JSON malformed.
   - `404 Not Found`: Resource dengan ID target tidak ditemukan / sudah dihapus.
   - `409 Conflict`: Pencegahan duplikasi data unik (NIM mahasiswa atau Kode Produk).
   - `405 Method Not Allowed`: Penolakan metode HTTP di luar router lengkap dengan header `Allow: GET, POST, PUT, PATCH, DELETE`.

---

## Struktur Direktori Proyek

```text
tugas-web-service/
├── praktikum-crud/                    # Bagian 1: Praktikum Mahasiswa Terbimbing
│   ├── data.php                       # Storage helper & inisialisasi awal data mahasiswa
│   ├── students.json                  # Data tersimpan (auto-generated saat runtime)
│   └── mahasiswa.php                  # Endpoint REST API CRUD Mahasiswa
├── tugas-crud/                        # Bagian 2: Tugas Mandiri Resource Produk
│   ├── data_produk.php                # Storage helper & inisialisasi awal data produk
│   ├── products.json                  # Data tersimpan (auto-generated saat runtime)
│   └── produk.php                     # Endpoint REST API CRUD Produk
├── postman/                           # Koleksi Postman Siap Import (.json)
│   ├── Praktikum_Mahasiswa.postman_collection.json
│   └── Tugas_CRUD_Produk.postman_collection.json
├── LEMBAR_OBSERVASI.md                # Tabel Lembar Observasi Praktikum & Tugas
├── test_runner.php                    # Otomasi Pengujian CLI (36 Test Cases - 100% PASS)
└── README.md                          # Dokumentasi & Panduan Proyek
```

---

## Panduan Menjalankan Server PHP

Gunakan PHP built-in web server sesuai panduan modul.

### 1. Menjalankan Endpoint Praktikum (`mahasiswa.php`)
Buka terminal dan jalankan:
```bash
php -S localhost:8000 -t praktikum-crud
```
Akses di browser atau Postman: `http://localhost:8000/mahasiswa.php`

### 2. Menjalankan Endpoint Tugas Mandiri (`produk.php`)
Buka terminal dan jalankan:
```bash
php -S localhost:8000 -t tugas-crud
```
Akses di browser atau Postman: `http://localhost:8000/produk.php`

---

## Pengujian Otomatis (Test Runner)

Untuk memverifikasi seluruh 36 skenario (sukses, gagal, validasi, persistensi, dan edge cases) secara instan:
```bash
php test_runner.php
```

Hasil pengujian:
- **15 / 15** skenario pada `mahasiswa.php` lulus 100%.
- **21 / 21** skenario pada `produk.php` lulus 100%.

---

## Menggunakan Koleksi Postman

1. Buka aplikasi **Postman** (Desktop atau Web).
2. Klik tombol **Import** (sudut kiri atas).
3. Pilih berkas dari folder `postman/`:
   - `Praktikum_Mahasiswa.postman_collection.json`
   - `Tugas_CRUD_Produk.postman_collection.json`
4. Variabel `{{base_url}}` sudah diset ke `http://localhost:8000`.
5. Jalankan request secara berurutan atau gunakan fitur **Run Collection** untuk mengeksekusi semua assert script (`pm.test`).

---

## Matriks Status Code REST API Produk (`produk.php`)

| Method | Endpoint | Query / Body | HTTP Status Code | Keterangan |
|---|---|---|---|---|
| `POST` | `/produk.php` | JSON lengkap | `201 Created` | Header `Location: /produk.php?id={id}`, return data baru |
| `POST` | `/produk.php` | Kode duplikat | `409 Conflict` | Error: kode produk sudah ada |
| `POST` | `/produk.php` | Field kurang / nilai negatif | `400 Bad Request` | Error validasi masukan |
| `GET` | `/produk.php` | - | `200 OK` | Array seluruh produk |
| `GET` | `/produk.php?id={id}` | `id` valid | `200 OK` | Object data produk |
| `GET` | `/produk.php?id={id}` | `id` tidak ada | `404 Not Found` | Error: produk tidak ditemukan |
| `GET` | `/produk.php?id=xyz` | `id` bukan angka | `400 Bad Request` | Error validasi parameter ID |
| `PUT` | `/produk.php?id={id}` | JSON semua field | `200 OK` | Replace seluruh isi produk |
| `PUT` | `/produk.php?id={id}` | Field tidak lengkap | `400 Bad Request` | PUT mewajibkan semua field |
| `PUT` | `/produk.php?id={id}` | Kode bentrok produk lain | `409 Conflict` | Error duplikasi kode unik |
| `PUT` | `/produk.php?id={id}` | `id` tidak ada | `404 Not Found` | Error: produk tidak ditemukan |
| `PATCH` | `/produk.php?id={id}` | JSON sebagian field | `200 OK` | Update field tertentu |
| `PATCH` | `/produk.php?id={id}` | Nilai harga negatif | `400 Bad Request` | Error validasi masukan |
| `PATCH` | `/produk.php?id={id}` | `id` tidak ada | `404 Not Found` | Error: produk tidak ditemukan |
| `DELETE`| `/produk.php?id={id}` | `id` valid | `204 No Content` | Berhasil dihapus (tanpa response body) |
| `DELETE`| `/produk.php?id={id}` | `id` sudah terhapus | `404 Not Found` | Error: produk tidak ditemukan (idempotensi) |
| `DELETE`| `/produk.php` | Tanpa parameter `id` | `400 Bad Request` | Error: parameter `id` wajib |
| `OPTIONS`| `/produk.php` | - | `405 Method Not Allowed` | Header `Allow: GET, POST, PUT, PATCH, DELETE` |

---

## Berkas yang Dikumpulkan
Sesuai instruksi tugas pada modul:
1. **Kode Sumber PHP**: Direktori `tugas-crud/` (`data_produk.php` & `produk.php`).
2. **Koleksi Postman (.json)**: Direktori `postman/` (`Tugas_CRUD_Produk.postman_collection.json`).
3. **Lembar Observasi**: Berkas `LEMBAR_OBSERVASI.md`.
