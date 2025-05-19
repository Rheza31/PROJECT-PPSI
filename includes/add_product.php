<?php
header('Content-Type: application/json; charset=utf-8');
require __DIR__ . '/config.php';
require __DIR__ . '/auth.php';

// 1. Cek autentikasi
if (!is_logged_in() || !is_admin()) {
    http_response_code(403);
    echo json_encode([
        'success' => false,
        'message' => 'Unauthorized',
        'error'   => ''
    ]);
    exit;
}

// 2. Cek metode request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request method',
        'error'   => ''
    ]);
    exit;
}

// 3. Ambil & sanitasi input
$kode_produk = trim($_POST['kode_produk'] ?? '');
$nama_produk = trim($_POST['nama_produk'] ?? '');
$kategori    = trim($_POST['kategori'] ?? '');
$harga       = isset($_POST['harga']) ? intval($_POST['harga']) : null;
$stok        = isset($_POST['stok'])  ? intval($_POST['stok'])  : null;
$gambar      = trim($_POST['gambar'] ?? '') ?: null;

// 4. Validasi input
$errors = [];
if ($kode_produk === '') $errors[] = 'Kode produk wajib diisi';
if ($nama_produk === '') $errors[] = 'Nama produk wajib diisi';
if (!in_array($kategori, ['makanan', 'minuman', 'snack'], true)) {
    $errors[] = 'Kategori tidak valid';
}
if (!is_numeric($harga) || $harga <= 0) $errors[] = 'Harga harus angka lebih besar dari 0';
if (!is_numeric($stok) || $stok < 0) $errors[] = 'Stok harus angka 0 atau lebih';

// Handle file upload (gambar)
if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] === UPLOAD_ERR_OK) {
    $gambar_tmp = $_FILES['gambar']['tmp_name'];
    $gambar_name = basename($_FILES['gambar']['name']);
    $gambar_target = 'uploads/' . $gambar_name;

    // Ensure it's a valid image
    if (getimagesize($gambar_tmp) !== false) {
        // Ensure the upload folder exists and is writable
        if (!is_dir('uploads')) {
            mkdir('uploads', 0777, true);
        }
        move_uploaded_file($gambar_tmp, $gambar_target);
        $gambar = $gambar_target;
    } else {
        $errors[] = 'File bukan gambar yang valid';
    }
}

if ($errors) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => implode(', ', $errors),
        'error'   => ''
    ]);
    exit;
}

try {
    // 5. Simpan ke database
    $db->beginTransaction();

    // Cek duplikat kode_produk
    $stmt = $db->prepare("SELECT id FROM produk WHERE kode_produk = ?");
    $stmt->execute([$kode_produk]);
    if ($stmt->fetchColumn()) {
        throw new Exception('Kode produk sudah digunakan');
    }

    // Insert produk baru
    $stmt = $db->prepare("
        INSERT INTO produk
            (kode_produk, nama_produk, kategori, harga, stok, gambar)
        VALUES (?, ?, ?, ?, ?, ?)
    ");
    $stmt->execute([
        $kode_produk,
        $nama_produk,
        $kategori,
        $harga,
        $stok,
        $gambar
    ]);

    $db->commit();

    echo json_encode([
        'success' => true,
        'message' => 'Produk berhasil ditambahkan',
        'error'   => ''
    ]);
} catch (PDOException $e) {
    $db->rollBack();
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Database error',
        'error'   => $e->getMessage()
    ]);
} catch (Exception $e) {
    $db->rollBack();
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
        'error'   => ''
    ]);
}