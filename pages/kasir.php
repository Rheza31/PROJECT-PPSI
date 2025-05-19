<?php
// pages/kasir.php
// index.php sudah include config.php & auth.php

// Pastikan user sudah login
if (!is_logged_in()) {
    redirect('../login.php');
}

// Ambil semua produk dengan stok > 0
$produk = $db
    ->query("SELECT id, kode_produk, nama_produk, harga, stok FROM produk WHERE stok > 0 ORDER BY nama_produk")
    ->fetchAll(PDO::FETCH_ASSOC);
?>

<!-- Load SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
  <!-- Daftar Produk -->
  <div class="lg:col-span-2 bg-white p-6 rounded-lg shadow overflow-auto" style="max-height:700px">
    <h3 class="text-lg font-semibold mb-4">Daftar Produk</h3>
    <table class="min-w-full">
      <thead class="bg-gray-50">
        <tr>
          <th class="py-2 px-4 text-left">Kode</th>
          <th class="py-2 px-4 text-left">Nama</th>
          <th class="py-2 px-4 text-right">Harga</th>
          <th class="py-2 px-4 text-center">Stok</th>
          <th class="py-2 px-4 text-center">Aksi</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($produk as $p): ?>
        <tr>
          <td class="py-2 px-4"><?= htmlspecialchars($p['kode_produk']) ?></td>
          <td class="py-2 px-4"><?= htmlspecialchars($p['nama_produk']) ?></td>
          <td class="py-2 px-4 text-right">Rp <?= number_format($p['harga'],0,',','.') ?></td>
          <td class="py-2 px-4 text-center"><?= $p['stok'] ?></td>
          <td class="py-2 px-4 text-center">
            <button
              class="add-to-cart bg-green-600 hover:bg-green-700 text-white px-3 py-1 rounded"
              data-id="<?= $p['id'] ?>"
              data-name="<?= htmlspecialchars($p['nama_produk']) ?>"
              data-price="<?= $p['harga'] ?>"
              data-stock="<?= $p['stok'] ?>">
              Tambah
            </button>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>

  <!-- Keranjang & Pembayaran -->
  <div class="bg-white p-6 rounded-lg shadow flex flex-col" style="max-height:700px">
    <h3 class="text-lg font-semibold mb-4">Keranjang</h3>
    <div class="flex-1 overflow-auto mb-4">
      <table class="min-w-full">
        <thead class="bg-gray-50">
          <tr>
            <th class="py-2 px-4">Produk</th>
            <th class="py-2 px-4 text-center">Qty</th>
            <th class="py-2 px-4 text-right">Subtotal</th>
            <th class="py-2 px-4 text-center">Hapus</th>
          </tr>
        </thead>
        <tbody id="cart-body"></tbody>
        <tfoot>
          <tr>
            <td colspan="2" class="py-2 px-4 text-right font-bold">Total:</td>
            <td id="cart-total" class="py-2 px-4 text-right font-bold">Rp 0</td>
            <td></td>
          </tr>
        </tfoot>
      </table>
    </div>

    <!-- Metode Pembayaran -->
    <p class="mb-1 font-medium">Metode Pembayaran</p>
    <div class="flex space-x-4 mb-4">
      <label class="flex-1 cursor-pointer border rounded-lg p-3 text-center payment-option active" data-method="cash">
        <input type="radio" name="payment-method" value="cash" class="hidden" checked>
        <i class="fas fa-money-bill-wave text-2xl text-green-600 mb-2"></i>
        <div>Cash</div>
      </label>
      <label class="flex-1 cursor-pointer border rounded-lg p-3 text-center payment-option" data-method="qris">
        <input type="radio" name="payment-method" value="qris" class="hidden">
        <i class="fas fa-qrcode text-2xl text-blue-600 mb-2"></i>
        <div>QRIS</div>
      </label>
    </div>

    <!-- Field Cash -->
    <div id="cash-fields" class="mb-4">
      <label class="block mb-1">Uang Dibayar (Rp)</label>
      <input type="number" id="paid-amount" class="w-full border rounded px-3 py-2" min="0" value="0">
      <p class="mt-2">Kembalian: <strong id="change">Rp 0</strong></p>
    </div>

    <!-- Field QRIS -->
    <div id="qris-fields" class="mb-4 hidden">
      <p class="mb-2">Scan QRIS berikut untuk membayar:</p>
      <div class="w-full h-48 flex items-center justify-center bg-gray-100 text-gray-500">
        QRIS Placeholder
      </div>
    </div>

    <button
      id="process-btn"
      class="mt-auto bg-blue-600 hover:bg-blue-700 text-white py-2 rounded disabled:opacity-50"
      disabled>
      Proses Transaksi
    </button>
  </div>
</div>

<script>
// State keranjang
let cart = [];

// Format rupiah
function formatRupiah(num) {
  return 'Rp ' + num.toLocaleString('id-ID');
}

// Render keranjang
function renderCart() {
  const tbody = document.getElementById('cart-body');
  tbody.innerHTML = '';
  let total = 0;

  cart.forEach((item, idx) => {
    const sub = item.qty * item.price;
    total += sub;
    const tr = document.createElement('tr');
    tr.innerHTML = `
      <td class="py-2 px-4">${item.name}</td>
      <td class="py-2 px-4 text-center">
        <input type="number" min="1" max="${item.stock}" value="${item.qty}"
               data-idx="${idx}" class="w-16 border text-center qty-input">
      </td>
      <td class="py-2 px-4 text-right">${formatRupiah(sub)}</td>
      <td class="py-2 px-4 text-center">
        <button data-idx="${idx}" class="text-red-600 remove-btn">&times;</button>
      </td>
    `;
    tbody.appendChild(tr);
  });

  document.getElementById('cart-total').textContent = formatRupiah(total);
  document.getElementById('process-btn').disabled = cart.length === 0;
  updateChange();

  document.querySelectorAll('.qty-input').forEach(inp => {
    inp.addEventListener('change', e => {
      const idx = e.target.dataset.idx;
      let v = parseInt(e.target.value);
      if (v < 1) v = 1;
      if (v > cart[idx].stock) v = cart[idx].stock;
      cart[idx].qty = v;
      renderCart();
    });
  });
  document.querySelectorAll('.remove-btn').forEach(btn => {
    btn.addEventListener('click', e => {
      cart.splice(e.target.dataset.idx, 1);
      renderCart();
    });
  });
}

// Tambah ke keranjang
document.querySelectorAll('.add-to-cart').forEach(btn => {
  btn.addEventListener('click', () => {
    const id    = +btn.dataset.id;
    const name  = btn.dataset.name;
    const price = +btn.dataset.price;
    const stock = +btn.dataset.stock;
    const exist = cart.find(i => i.id === id);
    if (exist) {
      if (exist.qty < stock) exist.qty++;
    } else {
      cart.push({ id, name, price, stock, qty: 1 });
    }
    renderCart();
  });
});

// Pilih metode bayar via ikon
document.querySelectorAll('.payment-option').forEach(lbl => {
  lbl.addEventListener('click', () => {
    document.querySelectorAll('.payment-option').forEach(l => l.classList.remove('border-blue-600','ring-2','ring-blue-300'));
    lbl.classList.add('border-blue-600','ring-2','ring-blue-300');
    lbl.querySelector('input').checked = true;
    const method = lbl.dataset.method;
    document.getElementById('cash-fields').classList.toggle('hidden', method !== 'cash');
    document.getElementById('qris-fields').classList.toggle('hidden', method !== 'qris');
    updateChange();
  });
});

// Update kembalian
function updateChange() {
  const total = parseInt(document.getElementById('cart-total').textContent.replace(/[^0-9]/g,'')) || 0;
  const paid  = parseInt(document.getElementById('paid-amount').value) || 0;
  const change= paid - total;
  document.getElementById('change').textContent = formatRupiah(change > 0 ? change : 0);
}
document.getElementById('paid-amount').addEventListener('input', updateChange);

// Proses transaksi
document.getElementById('process-btn').addEventListener('click', () => {
  const method = document.querySelector('input[name="payment-method"]:checked').value;
  const form   = new FormData();
  form.append('action', 'process_transaction');
  form.append('metode_pembayaran', method);
  if (method === 'cash') {
    form.append('uang_dibayar', document.getElementById('paid-amount').value || 0);
  }

  cart.forEach((item, i) => {
    form.append(`items[${i}][produk_id]`, item.id);
    form.append(`items[${i}][qty]`, item.qty);
    form.append(`items[${i}][harga_satuan]`, item.price);
  });

  fetch('includes/process_transaction.php', { method: 'POST', body: form })
    .then(res => res.json())
    .then(json => {
      if (json.success) {
        Swal.fire('Sukses','Transaksi berhasil','success').then(() => location.reload());
      } else {
        Swal.fire('Gagal', json.message,'error');
      }
    })
    .catch(() => {
      Swal.fire('Error','Terjadi kesalahan','error');
    });
});
</script>
