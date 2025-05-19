<?php
require_once 'config.php';
require_once 'auth.php';

if (!is_logged_in()) {
    header('HTTP/1.0 401 Unauthorized');
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

if (isset($_GET['id'])) {
    // Ambil data transaksi
    $stmt = $db->prepare("SELECT t.*, u.nama_lengkap 
                        FROM transaksi t 
                        JOIN users u ON t.user_id = u.id 
                        WHERE t.id = ?");
    $stmt->execute([$_GET['id']]);
    $transaction = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($transaction) {
        // Ambil detail transaksi
        $stmt = $db->prepare("SELECT dt.*, p.nama_produk 
                            FROM detail_transaksi dt 
                            JOIN produk p ON dt.produk_id = p.id 
                            WHERE dt.transaksi_id = ?");
        $stmt->execute([$_GET['id']]);
        $details = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode([
            'success' => true,
            'transaction' => $transaction,
            'details' => $details
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Transaction not found']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'ID parameter is required']);
}
?>