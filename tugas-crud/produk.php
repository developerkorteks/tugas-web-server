<?php
/**
 * REST API CRUD Resource Mandiri: Produk
 * Mata Kuliah: Teknologi Web Service - Pertemuan 4
 * Full CRUD: GET (All, One), POST, PUT (Full Update), PATCH (Partial Update), DELETE
 */

header("Content-Type: application/json; charset=UTF-8");
require_once "data_produk.php";

$products = loadProducts();

/**
 * Operasi CREATE (POST)
 */
function tambahProduk(&$products) {
    $rawInput = file_get_contents("php://input");
    $input = json_decode($rawInput, true);

    if (!is_array($input)) {
        http_response_code(400);
        echo json_encode(["error" => "Format JSON body tidak valid atau kosong"]);
        return;
    }

    $requiredFields = ['kode', 'nama', 'kategori', 'harga', 'stok'];
    $missingFields = [];
    foreach ($requiredFields as $field) {
        if (!isset($input[$field]) || trim((string)$input[$field]) === '') {
            $missingFields[] = $field;
        }
    }

    if (!empty($missingFields)) {
        http_response_code(400);
        echo json_encode([
            "error" => "Field berikut wajib diisi: " . implode(", ", $missingFields)
        ]);
        return;
    }

    if (!is_numeric($input['harga']) || $input['harga'] < 0) {
        http_response_code(400);
        echo json_encode(["error" => "Field 'harga' harus berupa angka dan tidak boleh bernilai negatif"]);
        return;
    }

    if (!is_numeric($input['stok']) || $input['stok'] < 0) {
        http_response_code(400);
        echo json_encode(["error" => "Field 'stok' harus berupa angka dan tidak boleh bernilai negatif"]);
        return;
    }

    // Cek duplikasi kode produk
    foreach ($products as $p) {
        if (strcasecmp($p['kode'], trim((string)$input['kode'])) === 0) {
            http_response_code(409);
            echo json_encode(["error" => "Produk dengan kode '{$input['kode']}' sudah ada"]);
            return;
        }
    }

    $existingIds = array_column($products, 'id');
    $newId = !empty($existingIds) ? max($existingIds) + 1 : 1;

    $newProduct = [
        "id" => $newId,
        "kode" => trim((string)$input['kode']),
        "nama" => trim((string)$input['nama']),
        "kategori" => trim((string)$input['kategori']),
        "harga" => (float)$input['harga'],
        "stok" => (int)$input['stok']
    ];

    $products[] = $newProduct;
    saveProducts($products);

    http_response_code(201);
    header("Location: /produk.php?id=$newId");
    echo json_encode($newProduct);
}

/**
 * Operasi READ - Ambil Semua Data (GET)
 */
function ambilSemuaProduk($products) {
    http_response_code(200);
    echo json_encode(array_values($products));
}

/**
 * Operasi READ - Ambil Satu Data berdasarkan ID (GET)
 */
function ambilSatuProduk($products, $id) {
    if (!is_numeric($id)) {
        http_response_code(400);
        echo json_encode(["error" => "Parameter ID harus berupa bilangan bulat valid"]);
        return;
    }

    foreach ($products as $p) {
        if ($p["id"] == $id) {
            http_response_code(200);
            echo json_encode($p);
            return;
        }
    }

    http_response_code(404);
    echo json_encode(["error" => "Produk dengan ID $id tidak ditemukan"]);
}

/**
 * Operasi UPDATE TOTAL (PUT)
 * Mengganti seluruh data resource produk
 */
function gantiProduk(&$products, $id) {
    if ($id === null || !is_numeric($id)) {
        http_response_code(400);
        echo json_encode(["error" => "Parameter query 'id' wajib disertakan dan berupa bilangan bulat valid"]);
        return;
    }

    $rawInput = file_get_contents("php://input");
    $input = json_decode($rawInput, true);

    if (!is_array($input)) {
        http_response_code(400);
        echo json_encode(["error" => "Format JSON body tidak valid atau kosong"]);
        return;
    }

    // Cari produk target terlebih dahulu
    $targetIndex = null;
    foreach ($products as $i => $p) {
        if ($p["id"] == $id) {
            $targetIndex = $i;
            break;
        }
    }

    if ($targetIndex === null) {
        http_response_code(404);
        echo json_encode(["error" => "Produk dengan ID $id tidak ditemukan"]);
        return;
    }

    $requiredFields = ['kode', 'nama', 'kategori', 'harga', 'stok'];
    $missingFields = [];
    foreach ($requiredFields as $field) {
        if (!isset($input[$field]) || trim((string)$input[$field]) === '') {
            $missingFields[] = $field;
        }
    }

    if (!empty($missingFields)) {
        http_response_code(400);
        echo json_encode([
            "error" => "Untuk operasi PUT, seluruh field wajib diisi: " . implode(", ", $missingFields)
        ]);
        return;
    }

    if (!is_numeric($input['harga']) || $input['harga'] < 0) {
        http_response_code(400);
        echo json_encode(["error" => "Field 'harga' harus berupa angka dan tidak boleh bernilai negatif"]);
        return;
    }

    if (!is_numeric($input['stok']) || $input['stok'] < 0) {
        http_response_code(400);
        echo json_encode(["error" => "Field 'stok' harus berupa angka dan tidak boleh bernilai negatif"]);
        return;
    }

    // Cek duplikasi kode produk dengan produk lain
    foreach ($products as $i => $p) {
        if ($i !== $targetIndex && strcasecmp($p['kode'], trim((string)$input['kode'])) === 0) {
            http_response_code(409);
            echo json_encode(["error" => "Kode produk '{$input['kode']}' sudah digunakan oleh produk lain"]);
            return;
        }
    }

    $updatedProduct = [
        "id" => (int)$id,
        "kode" => trim((string)$input['kode']),
        "nama" => trim((string)$input['nama']),
        "kategori" => trim((string)$input['kategori']),
        "harga" => (float)$input['harga'],
        "stok" => (int)$input['stok']
    ];

    $products[$targetIndex] = $updatedProduct;
    saveProducts($products);

    http_response_code(200);
    echo json_encode($updatedProduct);
}

/**
 * Operasi UPDATE SEBAGIAN (PATCH)
 * Mengubah sebagian field produk yang dikirimkan
 */
function ubahProduk(&$products, $id) {
    if ($id === null || !is_numeric($id)) {
        http_response_code(400);
        echo json_encode(["error" => "Parameter query 'id' wajib disertakan dan berupa bilangan bulat valid"]);
        return;
    }

    $rawInput = file_get_contents("php://input");
    $input = json_decode($rawInput, true);

    if (!is_array($input) || empty($input)) {
        http_response_code(400);
        echo json_encode(["error" => "Body JSON untuk update tidak boleh kosong"]);
        return;
    }

    // Cari produk target
    $targetIndex = null;
    foreach ($products as $i => $p) {
        if ($p["id"] == $id) {
            $targetIndex = $i;
            break;
        }
    }

    if ($targetIndex === null) {
        http_response_code(404);
        echo json_encode(["error" => "Produk dengan ID $id tidak ditemukan"]);
        return;
    }

    if (isset($input['harga']) && (!is_numeric($input['harga']) || $input['harga'] < 0)) {
        http_response_code(400);
        echo json_encode(["error" => "Nilai 'harga' harus berupa angka dan tidak boleh bernilai negatif"]);
        return;
    }

    if (isset($input['stok']) && (!is_numeric($input['stok']) || $input['stok'] < 0)) {
        http_response_code(400);
        echo json_encode(["error" => "Nilai 'stok' harus berupa angka dan tidak boleh bernilai negatif"]);
        return;
    }

    if (isset($input['kode'])) {
        $newKode = trim((string)$input['kode']);
        if ($newKode === '') {
            http_response_code(400);
            echo json_encode(["error" => "Kode produk tidak boleh kosong"]);
            return;
        }
        foreach ($products as $i => $p) {
            if ($i !== $targetIndex && strcasecmp($p['kode'], $newKode) === 0) {
                http_response_code(409);
                echo json_encode(["error" => "Kode produk '{$newKode}' sudah digunakan oleh produk lain"]);
                return;
            }
        }
        $input['kode'] = $newKode;
    }

    // ID tidak boleh dimodifikasi lewat body
    unset($input['id']);

    $merged = array_merge($products[$targetIndex], $input);
    if (isset($merged['harga'])) $merged['harga'] = (float)$merged['harga'];
    if (isset($merged['stok'])) $merged['stok'] = (int)$merged['stok'];

    $products[$targetIndex] = $merged;
    saveProducts($products);

    http_response_code(200);
    echo json_encode($merged);
}

/**
 * Operasi DELETE (DELETE)
 */
function hapusProduk(&$products, $id) {
    if ($id === null || !is_numeric($id)) {
        http_response_code(400);
        echo json_encode(["error" => "Parameter query 'id' wajib disertakan dan berupa bilangan bulat valid"]);
        return;
    }

    foreach ($products as $i => $p) {
        if ($p["id"] == $id) {
            unset($products[$i]);
            saveProducts($products);
            http_response_code(204);
            return;
        }
    }

    http_response_code(404);
    echo json_encode(["error" => "Produk dengan ID $id tidak ditemukan"]);
}

// Router menggunakan match expression (PHP 8.x)
$method = $_SERVER['REQUEST_METHOD'];
$id = $_GET['id'] ?? null;

match ($method) {
    'GET' => $id !== null ? ambilSatuProduk($products, $id) : ambilSemuaProduk($products),
    'POST' => tambahProduk($products),
    'PUT' => gantiProduk($products, $id),
    'PATCH' => ubahProduk($products, $id),
    'DELETE' => hapusProduk($products, $id),
    default => (function() use ($method) {
        header("Allow: GET, POST, PUT, PATCH, DELETE");
        http_response_code(405);
        echo json_encode(["error" => "Metode HTTP $method tidak diizinkan pada resource ini"]);
    })(),
};
