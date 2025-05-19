<?php
// pages/history.php
// (index.php sudah include config.php & auth.php)

// Pastikan sudah login
if (!is_logged_in()) {
    redirect('../login.php');
}

// Ambil semua transaksi
$stmt = $db->query("
    SELECT 
      id, kode_transaksi, created_at, total, metode_pembayaran,
      uang_dibayar, uang_kembali
    FROM transaksi
    ORDER BY created_at DESC
");
$transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Untuk setiap transaksi, ambil detailnya
$details = [];
if ($transactions) {
    $ids = array_column($transactions, 'id');
    // prepare satu statement untuk performa
    $in  = implode(',', array_fill(0, count($ids), '?'));
    $sql = "SELECT td.transaksi_id, p.nama_produk, td.qty, td.harga_satuan, td.subtotal
            FROM transaksi_detail td
            JOIN produk p ON td.produk_id = p.id
            WHERE td.transaksi_id IN ($in)
            ORDER BY td.id";
    $stmt2 = $db->prepare($sql);
    $stmt2->execute($ids);
    foreach ($stmt2->fetchAll(PDO::FETCH_ASSOC) as $row) {
        $details[$row['transaksi_id']][] = $row;
    }
}
?>

<div class="bg-white p-6 rounded-lg shadow overflow-auto">
  <h3 class="text-lg font-semibold mb-4">Riwayat Transaksi</h3>
  <table class="min-w-full">
    <thead class="bg-gray-50">
      <tr>
        <th class="py-2 px-4 border-b text-center">#</th>
        <th class="py-2 px-4 border-b">Kode</th>
        <th class="py-2 px-4 border-b">Tanggal</th>
        <th class="py-2 px-4 border-b text-right">Total</th>
        <th class="py-2 px-4 border-b">Metode</th>
        <th class="py-2 px-4 border-b text-right">Dibayar</th>
        <th class="py-2 px-4 border-b text-right">Kembalian</th>
        <th class="py-2 px-4 border-b text-center">Detail</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($transactions as $i => $tx): ?>
      <tr class="group">
        <td class="py-2 px-4 border-b text-center"><?= $i+1 ?></td>
        <td class="py-2 px-4 border-b"><?= htmlspecialchars($tx['kode_transaksi']) ?></td>
        <td class="py-2 px-4 border-b"><?= date('d/m/Y H:i', strtotime($tx['created_at'])) ?></td>
        <td class="py-2 px-4 border-b text-right">Rp <?= number_format($tx['total'],0,',','.') ?></td>
        <td class="py-2 px-4 border-b"><?= htmlspecialchars($tx['metode_pembayaran']) ?></td>
        <td class="py-2 px-4 border-b text-right">
          <?= $tx['uang_dibayar']!==null ? 'Rp '.number_format($tx['uang_dibayar'],0,',','.') : '-' ?>
        </td>
        <td class="py-2 px-4 border-b text-right">
          <?= $tx['uang_kembali']!==null ? 'Rp '.number_format($tx['uang_kembali'],0,',','.') : '-' ?>
        </td>
        <td class="py-2 px-4 border-b text-center">
          <button 
            class="toggle-detail text-blue-600 hover:underline focus:outline-none" 
            data-id="<?= $tx['id'] ?>">
            Lihat
          </button>
        </td>
      </tr>
      <tr id="details-<?= $tx['id'] ?>" class="hidden bg-gray-50">
        <td colspan="8" class="py-2 px-4">
          <table class="w-full">
            <thead>
              <tr>
                <th class="py-1 px-2 border-b">Produk</th>
                <th class="py-1 px-2 border-b text-center">Qty</th>
                <th class="py-1 px-2 border-b text-right">Harga</th>
                <th class="py-1 px-2 border-b text-right">Subtotal</th>
              </tr>
            </thead>
            <tbody>
              <?php if (!empty($details[$tx['id']])): ?>
                <?php foreach ($details[$tx['id']] as $d): ?>
                <tr>
                  <td class="py-1 px-2"><?= htmlspecialchars($d['nama_produk']) ?></td>
                  <td class="py-1 px-2 text-center"><?= $d['qty'] ?></td>
                  <td class="py-1 px-2 text-right">Rp <?= number_format($d['harga_satuan'],0,',','.') ?></td>
                  <td class="py-1 px-2 text-right">Rp <?= number_format($d['subtotal'],0,',','.') ?></td>
                </tr>
                <?php endforeach; ?>
              <?php else: ?>
                <tr><td colspan="4" class="py-1 px-2 text-center italic">Tidak ada detail</td></tr>
              <?php endif; ?>
            </tbody>
          </table>
        </td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>

<script>
// Toggle baris detail
document.querySelectorAll('.toggle-detail').forEach(btn => {
  btn.addEventListener('click', () => {
    const id = btn.dataset.id;
    const row = document.getElementById('details-' + id);
    row.classList.toggle('hidden');
  });
});
</script>
