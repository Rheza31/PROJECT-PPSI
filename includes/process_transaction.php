<?php
header('Content-Type: application/json; charset=utf-8');
require __DIR__ . '/config.php';
require __DIR__ . '/auth.php';

// 1. Hanya cek login (kasir juga boleh)
if (!is_logged_in()) {
    http_response_code(401);
    echo json_encode(['success'=>false,'message'=>'Unauthorized']);
    exit;
}

// 2. Pastikan POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success'=>false,'message'=>'Invalid request method']);
    exit;
}

// 3. Ambil data
$method = $_POST['metode_pembayaran'] ?? '';
$paid   = isset($_POST['uang_dibayar']) ? intval($_POST['uang_dibayar']) : null;
$items  = $_POST['items'] ?? [];

// 4. Validasi dasar
$errors = [];
if (!in_array($method, ['cash','qris'], true)) {
    $errors[] = 'Metode pembayaran tidak valid';
}
if ($method === 'cash' && ($paid === null || $paid < 0)) {
    $errors[] = 'Jumlah uang dibayar tidak valid';
}
if (empty($items)) {
    $errors[] = 'Tidak ada item di keranjang';
}
if ($errors) {
    http_response_code(400);
    echo json_encode(['success'=>false,'message'=>implode(', ',$errors)]);
    exit;
}

try {
    $db->beginTransaction();

    // Generate kode transaksi unik, misal prefix TX + timestamp
    $kode = 'TX'.date('YmdHis');

    // Hitung total dan cek stok
    $total = 0;
    foreach ($items as $it) {
        $qty   = intval($it['qty']);
        $price = intval($it['harga_satuan']);
        $sub   = $qty * $price;
        $total += $sub;

        // Cek stok
        $stmt = $db->prepare("SELECT stok FROM produk WHERE id = ?");
        $stmt->execute([ $it['produk_id'] ]);
        $stok = intval($stmt->fetchColumn());
        if ($qty > $stok) {
            throw new Exception("Stok produk ID {$it['produk_id']} tidak mencukupi");
        }
    }

    // Hitung kembalian
    $change = null;
    if ($method === 'cash') {
        if ($paid < $total) {
            throw new Exception("Uang dibayar kurang dari total");
        }
        $change = $paid - $total;
    }

    // Insert ke tabel transaksi
    $stmt = $db->prepare("
        INSERT INTO transaksi
            (kode_transaksi, total, metode_pembayaran, uang_dibayar, uang_kembali)
        VALUES (?, ?, ?, ?, ?)
    ");
    $stmt->execute([ $kode, $total, $method, $paid, $change ]);
    $transId = $db->lastInsertId();

    // Insert detail transaksi & update stok
    $stmtInsert = $db->prepare("
        INSERT INTO transaksi_detail
            (transaksi_id, produk_id, qty, harga_satuan, subtotal)
        VALUES (?, ?, ?, ?, ?)
    ");
    $stmtUpdate = $db->prepare("
        UPDATE produk SET stok = stok - ? WHERE id = ?
    ");
    foreach ($items as $it) {
        $qty   = intval($it['qty']);
        $price = intval($it['harga_satuan']);
        $sub   = $qty * $price;

        $stmtInsert->execute([
            $transId,
            $it['produk_id'],
            $qty,
            $price,
            $sub
        ]);
        $stmtUpdate->execute([
            $qty,
            $it['produk_id']
        ]);
    }

    $db->commit();
    echo json_encode(['success'=>true,'message'=>'Transaksi berhasil']);
    exit;

} catch (Exception $e) {
    $db->rollBack();
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
    exit;
}
