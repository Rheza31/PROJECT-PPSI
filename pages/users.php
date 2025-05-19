<?php
// pages/users.php
// index.php sudah include config.php & auth.php

// 1) Pastikan sudah login dan admin
if (!is_logged_in() || !is_admin()) {
    redirect('../login.php');
}

// 2) Ambil data user
$users = $db
    ->query("SELECT id, username, nama_lengkap, role FROM users ORDER BY created_at DESC")
    ->fetchAll(PDO::FETCH_ASSOC);
?>

<!-- Load SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<!-- Tambah User Modal (hidden by default) -->
<div id="addUserModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center hidden">
  <div class="bg-white p-6 rounded-lg shadow-lg w-full max-w-md">
    <h4 class="text-xl font-semibold mb-4">Tambah User Baru</h4>
    <form id="addUserForm">
      <div class="mb-4">
        <label class="block text-gray-700 mb-2">Username</label>
        <input type="text" name="username" required
               class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
      </div>
      <div class="mb-4">
        <label class="block text-gray-700 mb-2">Nama Lengkap</label>
        <input type="text" name="nama_lengkap" required
               class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
      </div>
      <div class="mb-6">
        <label class="block text-gray-700 mb-2">Password</label>
        <input type="password" name="password" required
               class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
      </div>
      <div class="flex justify-end space-x-2">
        <button type="button" id="cancelAdd" class="px-4 py-2 bg-gray-300 rounded hover:bg-gray-400">Batal</button>
        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">Simpan</button>
      </div>
    </form>
  </div>
</div>

<div class="bg-white p-6 rounded-lg shadow mb-6">
  <div class="flex justify-between items-center mb-4">
    <h3 class="text-lg font-semibold">Manajemen User</h3>
    <!-- tombol Tambah User -->
    <button id="openAddUserBtn" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded flex items-center">
      <i class="fas fa-user-plus mr-2"></i> Tambah Kasir
    </button>
  </div>

  <table class="min-w-full bg-white">
    <thead class="bg-gray-50">
      <tr>
        <th class="py-2 px-4 border-b text-center">#</th>
        <th class="py-2 px-4 border-b">Username</th>
        <th class="py-2 px-4 border-b">Nama Lengkap</th>
        <th class="py-2 px-4 border-b text-center">Role</th>
        <th class="py-2 px-4 border-b text-center">Aksi</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($users as $i => $u): ?>
      <tr data-id="<?= $u['id'] ?>">
        <td class="py-2 px-4 border-b text-center"><?= $i+1 ?></td>
        <td class="py-2 px-4 border-b"><?= htmlspecialchars($u['username']) ?></td>
        <td class="py-2 px-4 border-b"><?= htmlspecialchars($u['nama_lengkap']) ?></td>
        <td class="py-2 px-4 border-b text-center">
          <select class="role-select border rounded px-2 py-1">
            <option value="kasir" <?= $u['role']==='kasir' ? 'selected':'' ?>>Kasir</option>
            <option value="admin" <?= $u['role']==='admin' ? 'selected':'' ?>>Admin</option>
          </select>
        </td>
        <td class="py-2 px-4 border-b text-center space-x-2">
          <button class="update-btn bg-green-600 hover:bg-green-700 text-white px-3 py-1 rounded">Update</button>
          <button class="delete-btn bg-red-600 hover:bg-red-700 text-white px-3 py-1 rounded">Delete</button>
        </td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>

<script>
// util: POST JSON dan throw kalau error
async function postJSON(url, data) {
  const resp = await fetch(url, {
    method: 'POST',
    headers: {'Content-Type':'application/json'},
    body: JSON.stringify(data)
  });
  const payload = await resp.json();
  if (!resp.ok) throw payload;
  return payload;
}

// Update role
document.querySelectorAll('.update-btn').forEach(btn => {
  btn.addEventListener('click', async () => {
    const row = btn.closest('tr');
    const id  = row.dataset.id;
    const role = row.querySelector('.role-select').value;
    try {
      const res = await postJSON('includes/update_user.php', {id, role});
      Swal.fire('Berhasil', res.message, 'success');
    } catch(err) {
      Swal.fire('Gagal', err.message||'Terjadi kesalahan', 'error');
    }
  });
});

// Delete user
document.querySelectorAll('.delete-btn').forEach(btn => {
  btn.addEventListener('click', () => {
    const row = btn.closest('tr');
    const id  = row.dataset.id;
    const name = row.querySelector('td:nth-child(2)').textContent;
    Swal.fire({
      title: `Hapus user "${name}"?`,
      text: "Data user akan dihapus permanen!",
      icon: 'warning',
      showCancelButton: true,
      confirmButtonText: 'Ya, Hapus',
      cancelButtonText: 'Batal'
    }).then(async (r) => {
      if (!r.isConfirmed) return;
      try {
        const res = await postJSON('includes/delete_user.php', {id});
        Swal.fire('Dihapus', res.message, 'success')
          .then(() => row.remove());
      } catch(err) {
        Swal.fire('Gagal', err.message||'Terjadi kesalahan', 'error');
      }
    });
  });
});

// Tambah User: buka & tutup modal
const openBtn   = document.getElementById('openAddUserBtn');
const modal     = document.getElementById('addUserModal');
const cancelBtn = document.getElementById('cancelAdd');

openBtn.addEventListener('click', () => modal.classList.remove('hidden'));
cancelBtn.addEventListener('click', () => modal.classList.add('hidden'));

// Submit form tambah user
document.getElementById('addUserForm').addEventListener('submit', async e => {
  e.preventDefault();
  const f = e.target;
  try {
    const res = await postJSON('includes/add_user.php', {
      username:     f.username.value.trim(),
      nama_lengkap: f.nama_lengkap.value.trim(),
      password:     f.password.value
    });
    Swal.fire('Berhasil', res.message, 'success')
      .then(() => location.reload());
  } catch(err) {
    Swal.fire('Gagal', err.message||'Terjadi kesalahan', 'error');
  }
});
</script>