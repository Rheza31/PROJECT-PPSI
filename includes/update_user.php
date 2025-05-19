<?php
// includes/update_user.php
header('Content-Type: application/json; charset=utf-8');
require __DIR__ . '/config.php';
require __DIR__ . '/auth.php';

// Hanya admin
if (!is_logged_in() || !is_admin()) {
    http_response_code(403);
    echo json_encode(['success'=>false,'message'=>'Unauthorized']);
    exit;
}

// Pastikan JSON POST
$input = json_decode(file_get_contents('php://input'), true);
$id   = $input['id']   ?? null;
$role = $input['role'] ?? '';

// Validasi
if (!$id || !in_array($role, ['admin','kasir'], true)) {
    http_response_code(400);
    echo json_encode(['success'=>false,'message'=>'Data tidak valid']);
    exit;
}

try {
    $stmt = $db->prepare("UPDATE users SET role = ? WHERE id = ?");
    $stmt->execute([$role, $id]);

    echo json_encode([
        'success' => true,
        'message' => 'Role user berhasil diperbarui'
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
