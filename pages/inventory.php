<?php
// pages/inventory.php
// index.php sudah include config.php & auth.php

// 1) Pastikan sudah login
if (!is_logged_in()) {
    redirect('../login.php');
}

// 2) Cek role: hanya admin boleh akses
if (!is_admin()) {
    echo '<div class="bg-white p-6 rounded-lg shadow">';
    echo '  <p class="text-center text-red-600 font-semibold">Maaf Kamu Tidak Punya Akses</p>';
    echo '</div>';
    return;
}

// 3) Jika admin, ambil data produk
$products = $db->query("SELECT * FROM produk ORDER BY created_at DESC")
               ->fetchAll(PDO::FETCH_ASSOC);
?>

<!-- Load SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<div class="flex justify-between items-center mb-4">
  <h3 class="text-lg font-semibold">Inventory</h3>
  <button id="add-btn" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded">
    Tambah Produk
  </button>
</div>

<div class="overflow-auto bg-white rounded-lg shadow">
  <table class="min-w-full">
    <thead class="bg-gray-50">
      <tr>
        <th class="py-2 px-4 border-b">Kode</th>
        <th class="py-2 px-4 border-b">Nama</th>
        <th class="py-2 px-4 border-b">Kategori</th>
        <th class="py-2 px-4 border-b text-right">Harga</th>
        <th class="py-2 px-4 border-b text-center">Stok</th>
        <th class="py-2 px-4 border-b text-center">Aksi</th>
      </tr>
    </thead>
    <tbody id="prod-body">
      <?php foreach ($products as $p): ?>
      <tr>
        <td class="py-2 px-4 border-b"><?= htmlspecialchars($p['kode_produk']) ?></td>
        <td class="py-2 px-4 border-b"><?= htmlspecialchars($p['nama_produk']) ?></td>
        <td class="py-2 px-4 border-b"><?= htmlspecialchars($p['kategori']) ?></td>
        <td class="py-2 px-4 border-b text-right">Rp <?= number_format($p['harga'],0,',','.') ?></td>
        <td class="py-2 px-4 border-b text-center"><?= $p['stok'] ?></td>
        <td class="py-2 px-4 border-b text-center space-x-2">
          <button 
            class="edit-btn bg-yellow-500 hover:bg-yellow-600 text-white px-2 py-1 rounded"
            data-id="<?= $p['id'] ?>"
            data-kode="<?= htmlspecialchars($p['kode_produk']) ?>"
            data-nama="<?= htmlspecialchars($p['nama_produk']) ?>"
            data-kategori="<?= $p['kategori'] ?>"
            data-harga="<?= $p['harga'] ?>"
            data-stok="<?= $p['stok'] ?>"
            data-gambar="<?= htmlspecialchars($p['gambar']) ?>"
          >Edit</button>
          <button 
            class="delete-btn bg-red-600 hover:bg-red-700 text-white px-2 py-1 rounded"
            data-id="<?= $p['id'] ?>"
          >Hapus</button>
        </td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>

<!-- Modal Tambah/Edit Produk -->
<div id="product-modal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center hidden">
  <div class="bg-white p-6 rounded-lg w-full max-w-md">
    <h3 id="modal-title" class="text-xl font-semibold mb-4">Tambah Produk</h3>
    <form id="product-form" class="space-y-4">
      <input type="hidden" name="id" id="product-id">
      <div>
        <label class="block mb-1">Kode Produk</label>
        <input type="text" id="kode_produk" name="kode_produk" class="w-full border rounded px-3 py-2" required>
      </div>
      <div>
        <label class="block mb-1">Nama Produk</label>
        <input type="text" id="nama_produk" name="nama_produk" class="w-full border rounded px-3 py-2" required>
      </div>
      <div>
        <label class="block mb-1">Kategori</label>
        <select id="kategori" name="kategori" class="w-full border rounded px-3 py-2" required>
          <option value="makanan">Makanan</option>
          <option value="minuman">Minuman</option>
          <option value="snack">Snack</option>
        </select>
      </div>
      <div>
        <label class="block mb-1">Harga (Rp)</label>
        <input type="number" id="harga" name="harga" class="w-full border rounded px-3 py-2" min="1" required>
      </div>
      <div>
        <label class="block mb-1">Stok</label>
        <input type="number" id="stok" name="stok" class="w-full border rounded px-3 py-2" min="0" required>
      </div>
      <div>
        <label class="block mb-1">URL Gambar</label>
        <input type="text" id="gambar" name="gambar" class="w-full border rounded px-3 py-2">
      </div>
      <div class="flex justify-end space-x-2">
        <button type="button" id="modal-cancel" class="px-4 py-2 rounded border">Batal</button>
        <button type="submit" id="modal-save" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded">Simpan</button>
      </div>
    </form>
  </div>
</div>

<script>
// Helper untuk POST + JSON
async function postForm(url, fd) {
  const res = await fetch(url, { method:'POST', body:fd });
  const data = await res.json();
  if (!res.ok) throw data;
  return data;
}

const modal     = document.getElementById('product-modal');
const form      = document.getElementById('product-form');
const btnAdd    = document.getElementById('add-btn');
const btnCancel = document.getElementById('modal-cancel');
const prodBody  = document.getElementById('prod-body');

// Buka modal Tambah
btnAdd.addEventListener('click', () => {
  form.reset();
  document.getElementById('product-id').value = '';
  document.getElementById('modal-title').textContent = 'Tambah Produk';
  modal.classList.remove('hidden');
});

// Tutup modal
btnCancel.addEventListener('click', () => modal.classList.add('hidden'));
modal.addEventListener('click', e => { if(e.target===modal) modal.classList.add('hidden'); });

// Submit Tambah/Edit
form.addEventListener('submit', async e => {
  e.preventDefault();
  const id  = document.getElementById('product-id').value;
  const url = id ? 'includes/update_product.php' : 'includes/add_product.php';
  const fd  = new FormData(form);
  try {
    const res = await postForm(url, fd);
    Swal.fire('Sukses', res.message, 'success').then(()=>location.reload());
  } catch(err) {
    Swal.fire('Gagal', err.message||'Terjadi kesalahan','error');
  }
});

// Edit button
prodBody.querySelectorAll('.edit-btn').forEach(btn=>{
  btn.addEventListener('click',()=>{
    document.getElementById('product-id').value       = btn.dataset.id;
    document.getElementById('kode_produk').value      = btn.dataset.kode;
    document.getElementById('nama_produk').value      = btn.dataset.nama;
    document.getElementById('kategori').value         = btn.dataset.kategori;
    document.getElementById('harga').value            = btn.dataset.harga;
    document.getElementById('stok').value             = btn.dataset.stok;
    document.getElementById('gambar').value           = btn.dataset.gambar;
    document.getElementById('modal-title').textContent= 'Edit Produk';
    modal.classList.remove('hidden');
  });
});

// Delete button — **path diperbaiki** ke ../includes/delete_product.php
prodBody.querySelectorAll('.delete-btn').forEach(btn=>{
  btn.addEventListener('click',()=>{
    Swal.fire({
      title:'Hapus produk?',
      text:'Data akan hilang permanen',
      icon:'warning',
      showCancelButton:true,
      confirmButtonText:'Ya, Hapus',
      cancelButtonText:'Batal'
    }).then(async r=>{
      if(!r.isConfirmed) return;
      try {
        const fd = new FormData();
        fd.append('id',btn.dataset.id);
        const res = await postForm('includes/delete_product.php', fd);
        Swal.fire('Dihapus',res.message,'success').then(()=>location.reload());
      } catch(err) {
        Swal.fire('Gagal', err.message||'Terjadi kesalahan','error');
      }
    });
  });
});
</script>
