# Lembar Observasi Praktikum & Tugas Mandiri
**Mata Kuliah:** Teknologi Web Service  
**Pertemuan:** 4 — Merancang dan Membangun REST API Dasar (CRUD)  
**Program Studi:** Informatika • Semester Gasal 2026/2027  

---

### Bagian I: Lembar Observasi Praktikum Mahasiswa (`mahasiswa.php`)

Isi tabel berikut berdasarkan hasil pengujian sistematis pada Postman:

| No | Skenario Pengujian | Method | Endpoint / URL | Body / Payload | Status Diharapkan | Status Didapat | Kesimpulan |
|---|---|---|---|---|---|---|---|
| 1 | Tambah data lengkap | `POST` | `http://localhost:8000/mahasiswa.php` | `{"nim":"2024003","name":"Budi Santoso","major":"Informatika"}` | `201 Created` | `201 Created` | **Valid** (Header `Location` terbentuk, data JSON baru dikembalikan) |
| 2 | Tambah data tidak lengkap | `POST` | `http://localhost:8000/mahasiswa.php` | `{"nim":"2024004","major":"Informatika"}` | `400 Bad Request` | `400 Bad Request` | **Valid** (Pesan error validasi field wajib) |
| 3 | Ambil semua data | `GET` | `http://localhost:8000/mahasiswa.php` | - | `200 OK` | `200 OK` | **Valid** (Array seluruh data mahasiswa tampil, data baru tersimpan) |
| 4 | Ambil data by ID valid | `GET` | `http://localhost:8000/mahasiswa.php?id=1` | - | `200 OK` | `200 OK` | **Valid** (Data mahasiswa ID 1 tampil) |
| 5 | Ambil data by ID tidak ada | `GET` | `http://localhost:8000/mahasiswa.php?id=999` | - | `404 Not Found` | `404 Not Found` | **Valid** (Pesan error "Mahasiswa tidak ditemukan") |
| 6 | Ubah data (PATCH) | `PATCH` | `http://localhost:8000/mahasiswa.php?id=1` | `{"major":"Sistem Informasi"}` | `200 OK` | `200 OK` | **Valid** (Hanya field `major` yang diperbarui) |
| 7 | Hapus data | `DELETE` | `http://localhost:8000/mahasiswa.php?id=1` | - | `204 No Content` | `204 No Content` | **Valid** (Data berhasil dihapus tanpa response body) |
| 8 | Hapus data yang sudah terhapus / ID tidak ada | `DELETE` | `http://localhost:8000/mahasiswa.php?id=1` | - | `404 Not Found` | `404 Not Found` | **Valid** (Idempotensi teruji: penghapusan berulang menghasilkan 404) |

**Pengujian Tambahan Praktikum (Full CRUD & Keamanan):**
| No | Skenario Tambahan | Method | Endpoint / URL | Body / Payload | Status Diharapkan | Status Didapat | Kesimpulan |
|---|---|---|---|---|---|---|---|
| 9 | Ganti Total Data Mahasiswa | `PUT` | `http://localhost:8000/mahasiswa.php?id=2` | JSON lengkap seluruh field | `200 OK` | `200 OK` | **Valid** (Seluruh field diperbarui) |
| 10 | Tambah Mahasiswa NIM Duplikat | `POST` | `http://localhost:8000/mahasiswa.php` | JSON dengan NIM sama | `409 Conflict` | `409 Conflict` | **Valid** (Mencegah integritas data duplikat) |
| 11 | Method Tidak Diizinkan | `OPTIONS` | `http://localhost:8000/mahasiswa.php` | - | `405 Method Not Allowed` | `405 Method Not Allowed` | **Valid** (Header `Allow` memuat method legal) |

**Identitas Mahasiswa:**  
- **Nama Mahasiswa:** ……………………………………………………  
- **NIM:** ……………………………………………………  

---

### Bagian II: Lembar Observasi Tugas Mandiri Resource Produk (`produk.php`)

Pengujian REST API resource mandiri (`produk`) mencakup skenario sukses, skenario gagal, validasi, dan penanganan konflik:

| No | Skenario Pengujian | Method | Endpoint / URL | Payload / Body | Status Diharapkan | Status Didapat | Kesimpulan |
|---|---|---|---|---|---|---|---|
| 1 | Tambah produk lengkap | `POST` | `http://localhost:8000/produk.php` | JSON lengkap (kode, nama, kategori, harga, stok) | `201 Created` | `201 Created` | **Lolos** (Header `Location`, status 201) |
| 2 | Tambah produk kode duplikat | `POST` | `http://localhost:8000/produk.php` | JSON dengan kode sama | `409 Conflict` | `409 Conflict` | **Lolos** (Cegah konflik kode unik) |
| 3 | Tambah produk field kurang | `POST` | `http://localhost:8000/produk.php` | JSON tanpa field wajib | `400 Bad Request` | `400 Bad Request` | **Lolos** (Pesan error field wajib) |
| 4 | Tambah produk harga negatif | `POST` | `http://localhost:8000/produk.php` | JSON dengan `harga: -150000` | `400 Bad Request` | `400 Bad Request` | **Lolos** (Validasi nilai non-negatif) |
| 5 | Tambah produk body kosong / malformed | `POST` | `http://localhost:8000/produk.php` | Body bukan JSON valid | `400 Bad Request` | `400 Bad Request` | **Lolos** (Validasi parser JSON) |
| 6 | Ambil semua produk | `GET` | `http://localhost:8000/produk.php` | - | `200 OK` | `200 OK` | **Lolos** (Array seluruh produk) |
| 7 | Ambil produk by ID valid | `GET` | `http://localhost:8000/produk.php?id=2` | Query `id=2` | `200 OK` | `200 OK` | **Lolos** (Detail produk objek JSON) |
| 8 | Ambil produk by ID tidak ada | `GET` | `http://localhost:8000/produk.php?id=999` | Query `id=999` | `404 Not Found` | `404 Not Found` | **Lolos** (Pesan error produk tidak ada) |
| 9 | Ambil produk by ID bukan angka | `GET` | `http://localhost:8000/produk.php?id=xyz` | Query `id=xyz` | `400 Bad Request` | `400 Bad Request` | **Lolos** (Validasi tipe parameter query) |
| 10 | Ganti total data produk (PUT) | `PUT` | `http://localhost:8000/produk.php?id=1` | JSON lengkap baru | `200 OK` | `200 OK` | **Lolos** (Replace seluruh field) |
| 11 | Ganti total kode bentrok | `PUT` | `http://localhost:8000/produk.php?id=1` | JSON dengan kode milik produk lain | `409 Conflict` | `409 Conflict` | **Lolos** (Cegah duplikasi kode produk) |
| 12 | Ganti total field kurang | `PUT` | `http://localhost:8000/produk.php?id=1` | JSON sebagian field saja | `400 Bad Request` | `400 Bad Request` | **Lolos** (PUT mewajibkan full payload) |
| 13 | Ganti total ID tidak ditemukan | `PUT` | `http://localhost:8000/produk.php?id=999` | JSON lengkap | `404 Not Found` | `404 Not Found` | **Lolos** (ID tidak ada) |
| 14 | Ubah harga & stok (PATCH) | `PATCH` | `http://localhost:8000/produk.php?id=1` | `{"harga":8250000,"stok":10}` | `200 OK` | `200 OK` | **Lolos** (Update parsial) |
| 15 | Ubah dengan harga negatif | `PATCH` | `http://localhost:8000/produk.php?id=1` | `{"harga": -2000}` | `400 Bad Request` | `400 Bad Request` | **Lolos** (Validasi nilai negatif) |
| 16 | Ubah produk ID tidak ada | `PATCH` | `http://localhost:8000/produk.php?id=999` | `{"harga": 500000}` | `404 Not Found` | `404 Not Found` | **Lolos** (ID tidak ada) |
| 17 | Hapus produk ID valid | `DELETE` | `http://localhost:8000/produk.php?id=1` | Query `id=1` | `204 No Content` | `204 No Content` | **Lolos** (Berhasil dihapus tanpa body) |
| 18 | Hapus ulang produk sama | `DELETE` | `http://localhost:8000/produk.php?id=1` | Query `id=1` | `404 Not Found` | `404 Not Found` | **Lolos** (Idempotensi teruji) |
| 19 | Hapus produk tanpa query ID | `DELETE` | `http://localhost:8000/produk.php` | - | `400 Bad Request` | `400 Bad Request` | **Lolos** (Validasi parameter wajib) |
| 20 | Method tidak diizinkan | `OPTIONS` | `http://localhost:8000/produk.php` | - | `405 Method Not Allowed` | `405 Method Not Allowed` | **Lolos** (Header `Allow` terpasang) |
