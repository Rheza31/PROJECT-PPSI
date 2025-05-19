<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/auth.php';

if (!is_logged_in() || !is_admin()) {
    echo json_encode(['success' => false, 'message' => 'Akses ditolak']);
    exit;
}

// Pastikan ID produk valid
if (isset($_POST['id']) && !empty($_POST['id'])) {
    $id = $_POST['id'];
    $kode_produk = trim($_POST['kode_produk'] ?? '');
    $nama_produk = trim($_POST['nama_produk'] ?? '');
    $kategori = trim($_POST['kategori'] ?? '');
    $harga = isset($_POST['harga']) ? intval($_POST['harga']) : 0;
    $stok = isset($_POST['stok']) ? intval($_POST['stok']) : 0;
    $gambar = trim($_POST['gambar'] ?? '');

    // Validasi input dasar
    if (empty($kode_produk) || empty($nama_produk) || empty($kategori) || $harga <= 0) {
        echo json_encode(['success' => false, 'message' => 'Data tidak lengkap atau tidak valid']);
        exit;
    }

    try {
        // Cek apakah kode produk sudah digunakan oleh produk lain
        $check = $db->prepare("SELECT id FROM produk WHERE kode_produk = ? AND id != ?");
        $check->execute([$kode_produk, $id]);
        if ($check->fetchColumn()) {
            echo json_encode(['success' => false, 'message' => 'Kode produk sudah digunakan oleh produk lain']);
            exit;
        }

        // Query untuk memperbarui produk
        $query = "UPDATE produk SET 
                    kode_produk = :kode_produk, 
                    nama_produk = :nama_produk, 
                    kategori = :kategori, 
                    harga = :harga, 
                    stok = :stok, 
                    gambar = :gambar, 
                    updated_at = CURRENT_TIMESTAMP 
                  WHERE id = :id";
        
        $stmt = $db->prepare($query);
        
        // Bind parameter
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->bindParam(':kode_produk', $kode_produk, PDO::PARAM_STR);
        $stmt->bindParam(':nama_produk', $nama_produk, PDO::PARAM_STR);
        $stmt->bindParam(':kategori', $kategori, PDO::PARAM_STR);
        $stmt->bindParam(':harga', $harga, PDO::PARAM_INT);
        $stmt->bindParam(':stok', $stok, PDO::PARAM_INT);
        $stmt->bindParam(':gambar', $gambar, PDO::PARAM_STR);

        // Eksekusi query dan cek apakah berhasil
        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Produk berhasil diperbarui']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Gagal memperbarui produk']);
        }
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'ID produk tidak valid']);
}