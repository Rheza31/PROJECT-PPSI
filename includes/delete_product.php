<?php
// includes/delete_product.php

// Matikan output buffering lama dan error reporting ke layar
if (ob_get_level()) ob_end_clean();
ob_start();

// Matikan tampilkan error ke browser, agar tidak mengganggu JSON
ini_set('display_errors', 0);
error_reporting(0);

// Header JSON wajib di paling atas sebelum output apapun
header('Content-Type: application/json');

// Include config dan auth
require_once 'config.php';
require_once 'auth.php';

// Inisialisasi response
$response = [];

// Cek autentikasi dan role
if (!is_logged_in() || !is_admin()) {
    ob_clean();
    echo json_encode(['success'=>false,'message'=>'Akses ditolak']);
    exit;
}

// Validasi input
$id = $_POST['id'] ?? '';
if (!is_numeric($id)) {
    ob_clean();
    echo json_encode(['success'=>false,'message'=>'ID produk tidak valid']);
    exit;
}

// Jalankan delete
try {
    $stmt = $db->prepare("DELETE FROM produk WHERE id = ?");
    $exec = $stmt->execute([$id]);

    ob_clean();

    if ($exec) {
        echo json_encode(['success'=>true,'message'=>'Produk berhasil dihapus']);
    } else {
        echo json_encode(['success'=>false,'message'=>'Gagal menghapus produk']);
    }
} catch (Exception $e) {
    ob_clean();
    echo json_encode(['success'=>false,'message'=>'Terjadi kesalahan: ' . $e->getMessage()]);
}

exit;
