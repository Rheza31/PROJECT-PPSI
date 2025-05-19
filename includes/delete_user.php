<?php
// includes/delete_user.php
header('Content-Type: application/json; charset=utf-8');
require __DIR__ . '/config.php';
require __DIR__ . '/auth.php';

// Hanya admin
if (!is_logged_in() || !is_admin()) {
    http_response_code(403);
    echo json_encode(['success'=>false,'message'=>'Unauthorized']);
    exit;
}

// Pastikan JSON
$input = json_decode(file_get_contents('php://input'), true);
$id    = $input['id'] ?? null;

// Validasi
if (!$id) {
    http_response_code(400);
    echo json_encode(['success'=>false,'message'=>'ID user tidak valid']);
    exit;
}
// Jangan izinkan admin menghapus dirinya sendiri
if ($id == $_SESSION['user_id']) {
    http_response_code(400);
    echo json_encode(['success'=>false,'message'=>'Tidak bisa menghapus user sendiri']);
    exit;
}

try {
    $stmt = $db->prepare("DELETE FROM users WHERE id = ?");
    $stmt->execute([$id]);
    echo json_encode(['success'=>true,'message'=>'User berhasil dihapus']);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success'=>false,'message'=>'Database error: '.$e->getMessage()]);
}
