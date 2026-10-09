<?php
/**
 * Test Runner Otomatis Komprehensif
 * Praktikum & Tugas Mandiri - Pertemuan 4 (Teknologi Web Service)
 * Menguji Persistensi Data, Validasi Input, Status Code HTTP, & Edge Cases
 */

echo "======================================================================\n";
echo " TEST RUNNER OTOMATIS: PERTEMUAN 4 REST API CRUD LENGKAP & ROBUST \n";
echo "======================================================================\n\n";

function runCurl($url, $method = 'GET', $data = null) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HEADER, true);

    $headers = [];
    if ($data !== null) {
        $headers[] = 'Content-Type: application/json';
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    }
    if (!empty($headers)) {
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    }

    $response = curl_exec($ch);
    $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    $headerStr = substr($response, 0, $headerSize);
    $body = substr($response, $headerSize);

    return [
        'status' => $httpCode,
        'headers' => $headerStr,
        'body' => $body
    ];
}

function runTestSuite($suiteName, $docRoot, $storageFile, $tests, $port) {
    echo "----------------------------------------------------------------------\n";
    echo " Menjalankan Test Suite: $suiteName (Port: $port)\n";
    echo "----------------------------------------------------------------------\n";

    // Bersihkan storage agar pengujian berawal dari kondisi default yang konsisten
    if (file_exists($storageFile)) {
        unlink($storageFile);
    }

    $logFile = sys_get_temp_dir() . "/php_test_{$port}.log";
    $cmd = sprintf('php -S 127.0.0.1:%d -t %s > %s 2>&1 & echo $!', $port, escapeshellarg($docRoot), escapeshellarg($logFile));
    $pid = trim(shell_exec($cmd));
    usleep(500000); // 500ms agar server siap

    $passed = 0;
    $total = count($tests);

    foreach ($tests as $i => $t) {
        $fullUrl = "http://127.0.0.1:{$port}" . $t['endpoint'];
        $res = runCurl($fullUrl, $t['method'], $t['data'] ?? null);

        $statusOk = ($res['status'] === $t['expected_status']);
        $headerOk = true;
        if (isset($t['expected_header'])) {
            $headerOk = (stripos($res['headers'], $t['expected_header']) !== false);
        }

        $allOk = $statusOk && $headerOk;
        if ($allOk) {
            $passed++;
            $statusBadge = "\033[32m[PASS]\033[0m";
        } else {
            $statusBadge = "\033[31m[FAIL]\033[0m";
        }

        printf("%s #%02d %-7s %-32s -> Got: %d, Expected: %d | %s\n",
            $statusBadge,
            $i + 1,
            $t['method'],
            substr($t['endpoint'], 0, 32),
            $res['status'],
            $t['expected_status'],
            $t['desc']
        );

        if (!$allOk) {
            echo "       Response Body: " . substr(trim($res['body']), 0, 100) . "\n";
        }
    }

    if (!empty($pid)) {
        posix_kill((int)$pid, SIGTERM);
    }

    echo "Hasil: $passed / $total pengujian lolos.\n\n";
    return $passed === $total;
}

$mahasiswaTests = [
    [
        'desc' => 'Tambah Mahasiswa Lengkap',
        'endpoint' => '/mahasiswa.php',
        'method' => 'POST',
        'data' => json_encode(['nim' => '2024003', 'name' => 'Budi Santoso', 'major' => 'Informatika']),
        'expected_status' => 201,
        'expected_header' => 'Location: /mahasiswa.php?id=3'
    ],
    [
        'desc' => 'Tambah Mahasiswa NIM Duplikat',
        'endpoint' => '/mahasiswa.php',
        'method' => 'POST',
        'data' => json_encode(['nim' => '2024003', 'name' => 'Budi KW', 'major' => 'Informatika']),
        'expected_status' => 409
    ],
    [
        'desc' => 'Tambah Mahasiswa Field Kurang',
        'endpoint' => '/mahasiswa.php',
        'method' => 'POST',
        'data' => json_encode(['nim' => '2024004', 'major' => 'Informatika']),
        'expected_status' => 400
    ],
    [
        'desc' => 'Ambil Semua Mahasiswa (Harus Memuat ID 3)',
        'endpoint' => '/mahasiswa.php',
        'method' => 'GET',
        'expected_status' => 200
    ],
    [
        'desc' => 'Ambil Mahasiswa by ID Valid (id=3)',
        'endpoint' => '/mahasiswa.php?id=3',
        'method' => 'GET',
        'expected_status' => 200
    ],
    [
        'desc' => 'Ambil Mahasiswa by ID Tidak Ada (id=999)',
        'endpoint' => '/mahasiswa.php?id=999',
        'method' => 'GET',
        'expected_status' => 404
    ],
    [
        'desc' => 'Ambil Mahasiswa by ID Bukan Angka (id=abc)',
        'endpoint' => '/mahasiswa.php?id=abc',
        'method' => 'GET',
        'expected_status' => 400
    ],
    [
        'desc' => 'PUT Ganti Total Data Mahasiswa',
        'endpoint' => '/mahasiswa.php?id=3',
        'method' => 'PUT',
        'data' => json_encode(['nim' => '2024003', 'name' => 'Budi Santoso S.Kom', 'major' => 'Teknik Komputer']),
        'expected_status' => 200
    ],
    [
        'desc' => 'PUT Mahasiswa Field Tidak Lengkap',
        'endpoint' => '/mahasiswa.php?id=3',
        'method' => 'PUT',
        'data' => json_encode(['name' => 'Hanya Nama']),
        'expected_status' => 400
    ],
    [
        'desc' => 'PATCH Ubah Sebagian Field (major)',
        'endpoint' => '/mahasiswa.php?id=3',
        'method' => 'PATCH',
        'data' => json_encode(['major' => 'Sistem Informasi']),
        'expected_status' => 200
    ],
    [
        'desc' => 'PATCH Mahasiswa ID Tidak Ditemukan',
        'endpoint' => '/mahasiswa.php?id=999',
        'method' => 'PATCH',
        'data' => json_encode(['major' => 'Sistem Informasi']),
        'expected_status' => 404
    ],
    [
        'desc' => 'DELETE Mahasiswa ID=3 Pertama Kali',
        'endpoint' => '/mahasiswa.php?id=3',
        'method' => 'DELETE',
        'expected_status' => 204
    ],
    [
        'desc' => 'DELETE Mahasiswa ID=3 Kedua Kali (Idempotensi Teruji)',
        'endpoint' => '/mahasiswa.php?id=3',
        'method' => 'DELETE',
        'expected_status' => 404
    ],
    [
        'desc' => 'GET Mahasiswa ID=3 Pasca Dihapus',
        'endpoint' => '/mahasiswa.php?id=3',
        'method' => 'GET',
        'expected_status' => 404
    ],
    [
        'desc' => 'Method OPTIONS Tidak Diizinkan',
        'endpoint' => '/mahasiswa.php',
        'method' => 'OPTIONS',
        'expected_status' => 405,
        'expected_header' => 'Allow: GET, POST, PUT, PATCH, DELETE'
    ],
];

$produkTests = [
    [
        'desc' => 'POST Tambah Produk Lengkap',
        'endpoint' => '/produk.php',
        'method' => 'POST',
        'data' => json_encode(['kode' => 'PRD-004', 'nama' => 'Headset Gaming RGB', 'kategori' => 'Aksesoris', 'harga' => 450000, 'stok' => 20]),
        'expected_status' => 201,
        'expected_header' => 'Location: /produk.php?id=4'
    ],
    [
        'desc' => 'POST Produk Kode Duplikat',
        'endpoint' => '/produk.php',
        'method' => 'POST',
        'data' => json_encode(['kode' => 'PRD-004', 'nama' => 'Headset KW', 'kategori' => 'Aksesoris', 'harga' => 200000, 'stok' => 10]),
        'expected_status' => 409
    ],
    [
        'desc' => 'POST Produk Field Tidak Lengkap',
        'endpoint' => '/produk.php',
        'method' => 'POST',
        'data' => json_encode(['kode' => 'PRD-005', 'nama' => 'Webcam HD']),
        'expected_status' => 400
    ],
    [
        'desc' => 'POST Produk Harga Negatif',
        'endpoint' => '/produk.php',
        'method' => 'POST',
        'data' => json_encode(['kode' => 'PRD-005', 'nama' => 'Webcam HD', 'kategori' => 'Aksesoris', 'harga' => -150000, 'stok' => 5]),
        'expected_status' => 400
    ],
    [
        'desc' => 'POST Produk Body Kosong / Invalid JSON',
        'endpoint' => '/produk.php',
        'method' => 'POST',
        'data' => 'invalid-json',
        'expected_status' => 400
    ],
    [
        'desc' => 'GET Semua Produk (Memuat ID 4)',
        'endpoint' => '/produk.php',
        'method' => 'GET',
        'expected_status' => 200
    ],
    [
        'desc' => 'GET Produk by ID Valid (id=4)',
        'endpoint' => '/produk.php?id=4',
        'method' => 'GET',
        'expected_status' => 200
    ],
    [
        'desc' => 'GET Produk by ID Tidak Ditemukan',
        'endpoint' => '/produk.php?id=999',
        'method' => 'GET',
        'expected_status' => 404
    ],
    [
        'desc' => 'GET Produk by ID Bukan Angka',
        'endpoint' => '/produk.php?id=xyz',
        'method' => 'GET',
        'expected_status' => 400
    ],
    [
        'desc' => 'PUT Ganti Total Data Produk (id=4)',
        'endpoint' => '/produk.php?id=4',
        'method' => 'PUT',
        'data' => json_encode(['kode' => 'PRD-004-PRO', 'nama' => 'Headset Gaming RGB Pro', 'kategori' => 'Audio', 'harga' => 550000, 'stok' => 15]),
        'expected_status' => 200
    ],
    [
        'desc' => 'PUT Produk Kode Bentrok dengan Produk Lain',
        'endpoint' => '/produk.php?id=4',
        'method' => 'PUT',
        'data' => json_encode(['kode' => 'PRD-001', 'nama' => 'Headset Bentrok', 'kategori' => 'Audio', 'harga' => 550000, 'stok' => 15]),
        'expected_status' => 409
    ],
    [
        'desc' => 'PUT Produk Field Tidak Lengkap',
        'endpoint' => '/produk.php?id=4',
        'method' => 'PUT',
        'data' => json_encode(['nama' => 'Headset Saja']),
        'expected_status' => 400
    ],
    [
        'desc' => 'PUT Produk ID Tidak Ditemukan',
        'endpoint' => '/produk.php?id=999',
        'method' => 'PUT',
        'data' => json_encode(['kode' => 'PRD-999', 'nama' => 'Barang Hantu', 'kategori' => 'Gaib', 'harga' => 100000, 'stok' => 1]),
        'expected_status' => 404
    ],
    [
        'desc' => 'PATCH Ubah Harga & Stok Saja (id=4)',
        'endpoint' => '/produk.php?id=4',
        'method' => 'PATCH',
        'data' => json_encode(['harga' => 520000, 'stok' => 12]),
        'expected_status' => 200
    ],
    [
        'desc' => 'PATCH Harga Negatif',
        'endpoint' => '/produk.php?id=4',
        'method' => 'PATCH',
        'data' => json_encode(['harga' => -2000]),
        'expected_status' => 400
    ],
    [
        'desc' => 'PATCH Produk ID Tidak Ditemukan',
        'endpoint' => '/produk.php?id=999',
        'method' => 'PATCH',
        'data' => json_encode(['harga' => 200000]),
        'expected_status' => 404
    ],
    [
        'desc' => 'DELETE Produk ID=4 Pertama Kali',
        'endpoint' => '/produk.php?id=4',
        'method' => 'DELETE',
        'expected_status' => 204
    ],
    [
        'desc' => 'DELETE Produk ID=4 Kedua Kali (Idempotensi)',
        'endpoint' => '/produk.php?id=4',
        'method' => 'DELETE',
        'expected_status' => 404
    ],
    [
        'desc' => 'DELETE Produk Tanpa Parameter ID',
        'endpoint' => '/produk.php',
        'method' => 'DELETE',
        'expected_status' => 400
    ],
    [
        'desc' => 'GET Produk ID=4 Pasca Dihapus',
        'endpoint' => '/produk.php?id=4',
        'method' => 'GET',
        'expected_status' => 404
    ],
    [
        'desc' => 'OPTIONS Method Not Allowed',
        'endpoint' => '/produk.php',
        'method' => 'OPTIONS',
        'expected_status' => 405,
        'expected_header' => 'Allow: GET, POST, PUT, PATCH, DELETE'
    ],
];

$ok1 = runTestSuite("Praktikum Mahasiswa", __DIR__ . "/praktikum-crud", __DIR__ . "/praktikum-crud/students.json", $mahasiswaTests, 8888);
$ok2 = runTestSuite("Tugas Mandiri Resource Produk", __DIR__ . "/tugas-crud", __DIR__ . "/tugas-crud/products.json", $produkTests, 8889);

if ($ok1 && $ok2) {
    echo "\033[32m[SUKSES] SEMUA TEST SUITE LOLOS 100% TANPA KESALAHAN!\033[0m\n";
    exit(0);
} else {
    echo "\033[31m[GAGAL] ADA PENGUJIAN YANG GAGAL!\033[0m\n";
    exit(1);
}
