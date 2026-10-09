<?php
/**
 * Data Awal & Storage Persistence untuk Resource Produk
 * Mata Kuliah: Teknologi Web Service - Pertemuan 4
 */

$defaultProducts = [
    [
        "id" => 1,
        "kode" => "PRD-001",
        "nama" => "Laptop Asus Vivobook 14",
        "kategori" => "Elektronik",
        "harga" => 8500000,
        "stok" => 12
    ],
    [
        "id" => 2,
        "kode" => "PRD-002",
        "nama" => "Mouse Wireless Logitech M330",
        "kategori" => "Aksesoris",
        "harga" => 225000,
        "stok" => 35
    ],
    [
        "id" => 3,
        "kode" => "PRD-003",
        "nama" => "Keyboard Mechanical Keychron K2",
        "kategori" => "Aksesoris",
        "harga" => 1350000,
        "stok" => 8
    ]
];

$storageFile = __DIR__ . '/products.json';

function loadProducts(): array {
    global $storageFile, $defaultProducts;
    if (!file_exists($storageFile)) {
        file_put_contents($storageFile, json_encode($defaultProducts, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        return $defaultProducts;
    }
    $content = file_get_contents($storageFile);
    $data = json_decode($content, true);
    if (!is_array($data)) {
        file_put_contents($storageFile, json_encode($defaultProducts, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        return $defaultProducts;
    }
    return $data;
}

function saveProducts(array $products): void {
    global $storageFile;
    file_put_contents($storageFile, json_encode(array_values($products), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}

$products = loadProducts();
