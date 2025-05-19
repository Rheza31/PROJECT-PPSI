<?php
// pages/dashboard.php
// Pastikan index.php sudah include config & auth

if (!is_logged_in()) {
    redirect('../login.php');
}

// 1) Statistik kartu
$total_produk     = $db->query("SELECT COUNT(*) FROM produk")->fetchColumn();
$total_transaksi  = $db->query("SELECT COUNT(*) FROM transaksi")->fetchColumn();
$total_pendapatan = $db->query("SELECT COALESCE(SUM(total),0) FROM transaksi")->fetchColumn();
$stok_habis       = $db->query("SELECT COUNT(*) FROM produk WHERE stok <= 0")->fetchColumn();

// 2) Data Line chart (7 hari terakhir)
$chart_data = [];
for ($i = 6; $i >= 0; $i--) {
    $d = date('Y-m-d', strtotime("-{$i} days"));
    $stmt = $db->prepare("SELECT COALESCE(SUM(total),0) FROM transaksi WHERE DATE(created_at)=?");
    $stmt->execute([$d]);
    $chart_data[] = ['date'=>$d,'total'=>(int)$stmt->fetchColumn()];
}

// 3) Data Pie chart (produk terlaris)
$pie_data = $db->query("
    SELECT p.nama_produk, SUM(td.qty) AS total_qty
    FROM transaksi_detail td
    JOIN produk p ON td.produk_id = p.id
    GROUP BY td.produk_id
")->fetchAll(PDO::FETCH_ASSOC);

// 4) 5 transaksi terakhir
$recent = $db->query("
    SELECT kode_transaksi, created_at, total, metode_pembayaran
    FROM transaksi
    ORDER BY created_at DESC
    LIMIT 5
")->fetchAll(PDO::FETCH_ASSOC);
?>

<!-- Statistik Cards -->
<div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
  <!-- Total Produk -->
  <div class="bg-white p-6 rounded-lg shadow flex items-center">
    <div class="p-3 bg-blue-100 text-blue-600 rounded-full mr-4">
      <i class="fas fa-box text-xl"></i>
    </div>
    <div>
      <p class="text-gray-500">Total Produk</p>
      <h3 class="text-2xl font-bold"><?= $total_produk ?></h3>
    </div>
  </div>
  <!-- Total Transaksi -->
  <div class="bg-white p-6 rounded-lg shadow flex items-center">
    <div class="p-3 bg-green-100 text-green-600 rounded-full mr-4">
      <i class="fas fa-receipt text-xl"></i>
    </div>
    <div>
      <p class="text-gray-500">Total Transaksi</p>
      <h3 class="text-2xl font-bold"><?= $total_transaksi ?></h3>
    </div>
  </div>
  <!-- Total Pendapatan -->
  <div class="bg-white p-6 rounded-lg shadow flex items-center">
    <div class="p-3 bg-purple-100 text-purple-600 rounded-full mr-4">
      <i class="fas fa-money-bill-wave text-xl"></i>
    </div>
    <div>
      <p class="text-gray-500">Total Pendapatan</p>
      <h3 class="text-2xl font-bold">Rp <?= number_format($total_pendapatan,0,',','.') ?></h3>
    </div>
  </div>
  <!-- Stok Habis -->
  <div class="bg-white p-6 rounded-lg shadow flex items-center">
    <div class="p-3 bg-red-100 text-red-600 rounded-full mr-4">
      <i class="fas fa-exclamation-triangle text-xl"></i>
    </div>
    <div>
      <p class="text-gray-500">Produk Stok Habis</p>
      <h3 class="text-2xl font-bold"><?= $stok_habis ?></h3>
    </div>
  </div>
</div>

<!-- Charts Side by Side -->
<div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
  <!-- Line Chart 7 Hari Terakhir -->
  <div class="bg-white p-6 rounded-lg shadow flex flex-col">
    <h3 class="text-lg font-semibold mb-4">Penjualan 7 Hari Terakhir</h3>
    <div class="flex-1" style="position: relative; height: 350px;">
      <canvas id="salesChart"></canvas>
    </div>
  </div>
  <!-- Pie Chart Produk Terlaris -->
  <div class="bg-white p-6 rounded-lg shadow flex flex-col">
    <h3 class="text-lg font-semibold mb-4">Produk Terlaris</h3>
    <div class="flex-1" style="position: relative; height: 350px;">
      <canvas id="pieChart"></canvas>
    </div>
  </div>
</div>

<!-- Tabel Transaksi Terakhir -->
<div class="bg-white p-6 rounded-lg shadow">
  <h3 class="text-lg font-semibold mb-4">Transaksi Terakhir</h3>
  <div class="overflow-x-auto">
    <table class="min-w-full bg-white">
      <thead>
        <tr>
          <th class="py-2 px-4 border-b">No. Transaksi</th>
          <th class="py-2 px-4 border-b">Tanggal</th>
          <th class="py-2 px-4 border-b">Total</th>
          <th class="py-2 px-4 border-b">Metode Bayar</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($recent as $r): ?>
        <tr>
          <td class="py-2 px-4 border-b text-center"><?= htmlspecialchars($r['kode_transaksi']) ?></td>
          <td class="py-2 px-4 border-b text-center"><?= date('d/m/Y H:i', strtotime($r['created_at'])) ?></td>
          <td class="py-2 px-4 border-b text-center">Rp <?= number_format($r['total'],0,',','.') ?></td>
          <td class="py-2 px-4 border-b text-center"><?= htmlspecialchars($r['metode_pembayaran']) ?></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<script>
// 1) Line Chart
(function(){
  const ctx = document.getElementById('salesChart').getContext('2d');
  new Chart(ctx, {
    type: 'line',
    data: {
      labels: <?= json_encode(array_column($chart_data,'date')) ?>,
      datasets: [{
        label: 'Pendapatan',
        data: <?= json_encode(array_column($chart_data,'total')) ?>,
        tension: 0.4,
        borderWidth: 2,
        pointRadius: 3,
        fill: false
      }]
    },
    options: {
      maintainAspectRatio: false,
      scales: { y: { beginAtZero: true } }
    }
  });
})();

// 2) Pie Chart
(function(){
  const ctxPie = document.getElementById('pieChart').getContext('2d');
  new Chart(ctxPie, {
    type: 'pie',
    data: {
      labels: <?= json_encode(array_column($pie_data,'nama_produk')) ?>,
      datasets: [{
        data: <?= json_encode(array_column($pie_data,'total_qty')) ?>,
        backgroundColor: [
          '#FF6384','#36A2EB','#FFCE56','#4BC0C0',
          '#9966FF','#FF9F40','#C9CBCF','#8ED081','#F67019'
        ],
        hoverOffset: 8
      }]
    },
    options: {
      maintainAspectRatio: false,
      responsive: true
    }
  });
})();
</script>
