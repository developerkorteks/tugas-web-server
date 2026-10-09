<?php
header("Content-Type: application/json; charset=UTF-8");
require_once "data.php";

$students = loadStudents();

function tambahData(&$students) {
    $raw = file_get_contents("php://input");
    $input = json_decode($raw, true);

    if (!is_array($input)) {
        http_response_code(400);
        echo json_encode(["error" => "Body JSON tidak valid atau kosong"]);
        return;
    }

    if (!isset($input['nim']) || !isset($input['name']) || !isset($input['major']) ||
        trim((string)$input['nim']) === '' || trim((string)$input['name']) === '' || trim((string)$input['major']) === '') {
        http_response_code(400);
        echo json_encode(["error" => "Field nim, name, dan major wajib diisi"]);
        return;
    }

    // Cek duplikasi NIM
    foreach ($students as $s) {
        if ($s['nim'] === (string)$input['nim']) {
            http_response_code(409);
            echo json_encode(["error" => "Mahasiswa dengan NIM {$input['nim']} sudah terdaftar"]);
            return;
        }
    }

    $existingIds = array_column($students, 'id');
    $newId = !empty($existingIds) ? max($existingIds) + 1 : 1;

    $newStudent = [
        "id" => $newId,
        "nim" => (string)$input['nim'],
        "name" => (string)$input['name'],
        "major" => (string)$input['major'],
    ];

    $students[] = $newStudent;
    saveStudents($students);

    http_response_code(201);
    header("Location: /mahasiswa.php?id=$newId");
    echo json_encode($newStudent);
}

function ambilSemuaData($students) {
    http_response_code(200);
    echo json_encode(array_values($students));
}

function ambilSatuData($students, $id) {
    if (!is_numeric($id)) {
        http_response_code(400);
        echo json_encode(["error" => "Parameter id harus berupa angka"]);
        return;
    }

    foreach ($students as $s) {
        if ($s["id"] == $id) {
            http_response_code(200);
            echo json_encode($s);
            return;
        }
    }

    http_response_code(404);
    echo json_encode(["error" => "Mahasiswa tidak ditemukan"]);
}

function gantiData(&$students, $id) {
    if ($id === null || !is_numeric($id)) {
        http_response_code(400);
        echo json_encode(["error" => "Parameter id harus berupa angka valid"]);
        return;
    }

    $raw = file_get_contents("php://input");
    $input = json_decode($raw, true);

    if (!is_array($input)) {
        http_response_code(400);
        echo json_encode(["error" => "Body JSON tidak valid atau kosong"]);
        return;
    }

    if (!isset($input['nim']) || !isset($input['name']) || !isset($input['major']) ||
        trim((string)$input['nim']) === '' || trim((string)$input['name']) === '' || trim((string)$input['major']) === '') {
        http_response_code(400);
        echo json_encode(["error" => "Untuk operasi PUT, seluruh field (nim, name, major) wajib diisi"]);
        return;
    }

    foreach ($students as $i => $s) {
        if ($s["id"] == $id) {
            $updated = [
                "id" => (int)$id,
                "nim" => (string)$input['nim'],
                "name" => (string)$input['name'],
                "major" => (string)$input['major']
            ];
            $students[$i] = $updated;
            saveStudents($students);

            http_response_code(200);
            echo json_encode($updated);
            return;
        }
    }

    http_response_code(404);
    echo json_encode(["error" => "Mahasiswa tidak ditemukan"]);
}

function ubahData(&$students, $id) {
    if ($id === null || !is_numeric($id)) {
        http_response_code(400);
        echo json_encode(["error" => "Parameter id harus berupa angka valid"]);
        return;
    }

    $raw = file_get_contents("php://input");
    $input = json_decode($raw, true);

    if (!is_array($input) || empty($input)) {
        http_response_code(400);
        echo json_encode(["error" => "Body JSON untuk PATCH tidak boleh kosong"]);
        return;
    }

    // ID tidak boleh diubah lewat body
    unset($input['id']);

    foreach ($students as $i => $s) {
        if ($s["id"] == $id) {
            $students[$i] = array_merge($s, $input);
            saveStudents($students);

            http_response_code(200);
            echo json_encode($students[$i]);
            return;
        }
    }

    http_response_code(404);
    echo json_encode(["error" => "Mahasiswa tidak ditemukan"]);
}

function hapusData(&$students, $id) {
    if ($id === null || !is_numeric($id)) {
        http_response_code(400);
        echo json_encode(["error" => "Parameter id harus berupa angka valid"]);
        return;
    }

    foreach ($students as $i => $s) {
        if ($s["id"] == $id) {
            unset($students[$i]);
            saveStudents($students);

            // Sesuai modul lembar observasi nomor 7 (204 No Content)
            http_response_code(204);
            return;
        }
    }

    http_response_code(404);
    echo json_encode(["error" => "Mahasiswa tidak ditemukan"]);
}

$method = $_SERVER['REQUEST_METHOD'];
$id = $_GET['id'] ?? null;

match ($method) {
    'GET' => $id !== null ? ambilSatuData($students, $id) : ambilSemuaData($students),
    'POST' => tambahData($students),
    'PUT' => gantiData($students, $id),
    'PATCH' => ubahData($students, $id),
    'DELETE' => hapusData($students, $id),
    default => (function() use ($method) {
        header("Allow: GET, POST, PUT, PATCH, DELETE");
        http_response_code(405);
        echo json_encode(["error" => "Metode HTTP $method tidak diizinkan"]);
    })(),
};
