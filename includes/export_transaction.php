<?php
require_once 'config.php';  // Pastikan koneksi database sudah disertakan
require_once 'auth.php';    // Pastikan autentikasi pengguna sudah ditangani

// Cek apakah pengguna sudah login
if (!is_logged_in()) {
    header('HTTP/1.0 401 Unauthorized');
    exit; // Jika tidak login, kembalikan Unauthorized
}

// Ambil parameter filter dari URL (GET method)
$start_date = $_GET['start_date'] ?? date('Y-m-01');  // Default start date: pertama bulan ini
$end_date = $_GET['end_date'] ?? date('Y-m-d');        // Default end date: hari ini
$search = $_GET['search'] ?? '';                       // Filter pencarian, default kosong

// Query untuk mengambil riwayat transaksi (tanpa nama kasir)
$query = "SELECT t.kode_transaksi, t.created_at, t.total, t.uang_dibayar, t.uang_kembali 
          FROM transaksi t 
          WHERE DATE(t.created_at) BETWEEN :start_date AND :end_date";

if (!empty($search)) {
    // Menambahkan filter pencarian jika ada
    $query .= " AND (t.kode_transaksi LIKE :search)";
}

$query .= " ORDER BY t.created_at DESC";  // Urutkan transaksi berdasarkan tanggal (descending)

$stmt = $db->prepare($query);  // Menyiapkan query SQL
$stmt->bindValue(':start_date', $start_date);  // Mengikat parameter start_date
$stmt->bindValue(':end_date', $end_date);      // Mengikat parameter end_date

if (!empty($search)) {
    // Mengikat parameter pencarian jika ada
    $stmt->bindValue(':search', "%$search%");
}

$stmt->execute();  // Menjalankan query
$transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);  // Mengambil semua hasil transaksi dalam bentuk array asosiatif

// Menetapkan header untuk file CSV yang akan diunduh
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=transaksi_' . date('Ymd') . '.csv');

// Membuka stream output untuk menulis ke file CSV
$output = fopen('php://output', 'w');

// Menulis header CSV tanpa kolom Kasir
fputcsv($output, [
    'No. Transaksi',  // Kolom header untuk CSV
    'Tanggal',
    'Total (Rp)',
    'Dibayar (Rp)',
    'Kembali (Rp)'
]);

// Menulis data transaksi ke CSV
foreach ($transactions as $transaction) {
    // Menulis setiap data transaksi ke dalam file CSV
    fputcsv($output, [
        $transaction['kode_transaksi'],
        $transaction['created_at'],
        $transaction['total'],
        $transaction['uang_dibayar'],
        $transaction['uang_kembali']
    ]);
}

fclose($output);  // Menutup output file CSV
exit;  // Keluar setelah proses eksport CSV selesai
?>
