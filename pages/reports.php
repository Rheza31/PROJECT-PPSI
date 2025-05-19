<?php
// pages/reports.php
// index.php sudah include config.php & auth.php

// Hanya user terautentikasi yang boleh akses
if (!is_logged_in()) {
    redirect('../login.php');
}

// 1. Ambil filter tanggal dari query string, default bulan ini
$start = $_GET['start_date'] ?? date('Y-m-01');
$end   = $_GET['end_date']   ?? date('Y-m-d');

// 2. Ambil transaksi sesuai rentang tanggal
$stmt = $db->prepare("
    SELECT 
      kode_transaksi, DATE(created_at) AS tanggal, created_at,
      total, metode_pembayaran,
      uang_dibayar, uang_kembali
    FROM transaksi
    WHERE DATE(created_at) BETWEEN ? AND ?
    ORDER BY created_at DESC
");
$stmt->execute([$start, $end]);
$transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!-- Reports Page -->
<div class="bg-white p-6 rounded-lg shadow mb-6">
  <h3 class="text-lg font-semibold mb-4">Laporan Transaksi</h3>
  <form method="get" class="grid grid-cols-1 md:grid-cols-3 gap-4 items-end">
    <div>
      <label class="block mb-1">Tanggal Mulai</label>
      <input type="date" name="start_date" value="<?= htmlspecialchars($start) ?>"
             class="w-full border rounded px-3 py-2">
    </div>
    <div>
      <label class="block mb-1">Tanggal Selesai</label>
      <input type="date" name="end_date" value="<?= htmlspecialchars($end) ?>"
             class="w-full border rounded px-3 py-2">
    </div>
    <div class="flex space-x-2">
      <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded">
        Filter
      </button>
      <a href="includes/export_transaction.php?start_date=<?= urlencode($start) ?>&end_date=<?= urlencode($end) ?>"
         class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded">
        Export CSV
      </a>
    </div>
  </form>
</div>

<div class="bg-white p-6 rounded-lg shadow overflow-auto">
  <table class="min-w-full">
    <thead class="bg-gray-50">
      <tr>
        <th class="py-2 px-4 border-b">#</th>
        <th class="py-2 px-4 border-b">Kode Transaksi</th>
        <th class="py-2 px-4 border-b">Tanggal & Waktu</th>
        <th class="py-2 px-4 border-b text-right">Total</th>
        <th class="py-2 px-4 border-b">Metode Bayar</th>
        <th class="py-2 px-4 border-b text-right">Dibayar</th>
        <th class="py-2 px-4 border-b text-right">Kembalian</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($transactions as $i => $tx): ?>
      <tr>
        <td class="py-2 px-4 border-b text-center"><?= $i + 1 ?></td>
        <td class="py-2 px-4 border-b"><?= htmlspecialchars($tx['kode_transaksi']) ?></td>
        <td class="py-2 px-4 border-b">
          <?= date('d/m/Y H:i', strtotime($tx['created_at'])) ?>
        </td>
        <td class="py-2 px-4 border-b text-right">Rp <?= number_format($tx['total'],0,',','.') ?></td>
        <td class="py-2 px-4 border-b"><?= htmlspecialchars($tx['metode_pembayaran']) ?></td>
        <td class="py-2 px-4 border-b text-right">
          <?= $tx['uang_dibayar']!==null 
              ? 'Rp '.number_format($tx['uang_dibayar'],0,',','.') 
              : '-' 
          ?>
        </td>
        <td class="py-2 px-4 border-b text-right">
          <?= $tx['uang_kembali']!==null 
              ? 'Rp '.number_format($tx['uang_kembali'],0,',','.') 
              : '-' 
          ?>
        </td>
      </tr>
      <?php endforeach; ?>
      <?php if (empty($transactions)): ?>
      <tr>
        <td colspan="7" class="py-4 text-center italic text-gray-500">
          Tidak ada transaksi pada rentang tanggal ini.
        </td>
      </tr>
      <?php endif; ?>
    </tbody>
  </table>
</div>
