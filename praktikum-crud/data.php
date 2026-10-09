<?php
/**
 * Data In-Memory & Storage Persistence untuk Mahasiswa
 * Mata Kuliah Teknologi Web Service - Pertemuan 4
 */

$defaultStudents = [
    ["id" => 1, "nim" => "2024001", "name" => "Andi Saputra", "major" => "Informatika"],
    ["id" => 2, "nim" => "2024002", "name" => "Sari Wulandari", "major" => "Sistem Informasi"],
];

$storageFile = __DIR__ . '/students.json';

function loadStudents(): array {
    global $storageFile, $defaultStudents;
    if (!file_exists($storageFile)) {
        file_put_contents($storageFile, json_encode($defaultStudents, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        return $defaultStudents;
    }
    $content = file_get_contents($storageFile);
    $data = json_decode($content, true);
    if (!is_array($data)) {
        file_put_contents($storageFile, json_encode($defaultStudents, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        return $defaultStudents;
    }
    return $data;
}

function saveStudents(array $students): void {
    global $storageFile;
    file_put_contents($storageFile, json_encode(array_values($students), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}

// Inisialisasi awal untuk variabel $students agar kompatibel dengan kode modul
$students = loadStudents();
