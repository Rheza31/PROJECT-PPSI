<?php
header('Content-Type: application/json');
require_once __DIR__ . '/config.php';  // $db (PDO)
require_once __DIR__ . '/auth.php';    // is_logged_in(), is_admin()

// Cek autentikasi & role
if (!is_logged_in() || !is_admin()) {
    http_response_code(403);
    echo json_encode(['message'=>'Akses ditolak']);
    exit;
}

// Ambil JSON body
$data = json_decode(file_get_contents('php://input'), true);
$username     = trim($data['username'] ?? '');
$nama_lengkap = trim($data['nama_lengkap'] ?? '');
$password     = $data['password'] ?? '';

// Validasi
if ($username === '' || $nama_lengkap === '' || $password === '') {
    http_response_code(400);
    echo json_encode(['message'=>'Semua field wajib diisi']);
    exit;
}

// Cek unik username
$stmt = $db->prepare('SELECT COUNT(*) FROM users WHERE username = ?');
$stmt->execute([$username]);
if ($stmt->fetchColumn() > 0) {
    http_response_code(400);
    echo json_encode(['message'=>'Username sudah digunakan']);
    exit;
}

// Simpan user baru dengan role 'kasir'
$hash = password_hash($password, PASSWORD_DEFAULT);
$stmt = $db->prepare('INSERT INTO users (username, password, nama_lengkap, role, created_at) VALUES (?, ?, ?, ?, NOW())');
$stmt->execute([$username, $hash, $nama_lengkap, 'kasir']);

echo json_encode(['message'=>'User baru berhasil ditambahkan']);
