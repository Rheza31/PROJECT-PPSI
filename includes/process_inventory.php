<?php
require 'db.php'; // koneksi database

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Ambil data dari form
    $nama_produk = $_POST['nama_produk'];
    $stok = $_POST['stok'];
    $harga = $_POST['harga'];
    $mode = $_POST['mode']; // add atau update
    $id = $_POST['id'] ?? null; // id produk untuk update, jika ada

    if ($mode === 'add') {
        // Proses tambah produk
        $stmt = $conn->prepare("INSERT INTO produk (nama_produk, stok, harga) VALUES (?, ?, ?)");
        $stmt->bind_param("sii", $nama_produk, $stok, $harga);
        $stmt->execute();
        $response = ['success' => true, 'message' => 'Produk berhasil ditambahkan!'];
    } elseif ($mode === 'update' && $id) {
        // Proses update produk
        $stmt = $conn->prepare("UPDATE produk SET nama_produk = ?, stok = ?, harga = ? WHERE id = ?");
        $stmt->bind_param("siii", $nama_produk, $stok, $harga, $id);
        $stmt->execute();
        $response = ['success' => true, 'message' => 'Produk berhasil diperbarui!'];
    } else {
        // Jika tidak ada mode yang valid
        $response = ['success' => false, 'message' => 'Mode tidak valid atau ID produk tidak ditemukan!'];
    }

    // Kirim response JSON
    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}
