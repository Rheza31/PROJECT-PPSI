<?php
// index.php
require_once 'includes/config.php';
require_once 'includes/auth.php';

// Pastikan user sudah login
if (!is_logged_in()) {
    redirect('login.php');
}

// Daftar halaman yang diizinkan
$allowed = ['dashboard', 'kasir', 'inventory', 'history', 'reports', 'users'];
$page    = $_GET['page'] ?? 'dashboard';

// Validasi halaman
if (!in_array($page, $allowed)) {
    $page = 'dashboard';
}
// Jika non-admin mencoba akses 'users', kembalikan ke dashboard
if ($page === 'users' && !is_admin()) {
    $page = 'dashboard';
}

// Judul per halaman
$titles = [
    'dashboard' => 'Dashboard',
    'kasir'     => 'Kasir',
    'inventory' => 'Inventory',
    'history'   => 'Riwayat Transaksi',
    'reports'   => 'Reports',
    'users'     => 'Manajemen User'
];

// Ikon per halaman (FontAwesome)
$icons = [
    'dashboard' => 'tachometer-alt',
    'kasir'     => 'cash-register',
    'inventory' => 'boxes',
    'history'   => 'history',
    'reports'   => 'chart-line',
    'users'     => 'users'
];
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1.0">
  <title>Stockify – <?= htmlspecialchars($titles[$page]) ?></title>
  <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="bg-gray-100 flex h-screen">
  <!-- Sidebar -->
  <aside class="w-64 bg-blue-800 text-white flex-shrink-0">
    <div class="p-4 text-center border-b border-blue-700">
      <h1 class="text-2xl font-bold">Stockify</h1>
    </div>
    <nav class="p-4 space-y-2">
      <?php foreach ($allowed as $p): ?>
        <?php if ($p === 'users' && !is_admin()) continue; ?>
        <a href="?page=<?= $p ?>"
           class="flex items-center py-2 px-4 rounded hover:bg-blue-700 <?= $page === $p ? 'bg-blue-700' : '' ?>">
          <i class="fas fa-<?= $icons[$p] ?> mr-2"></i>
          <?= htmlspecialchars($titles[$p]) ?>
        </a>
      <?php endforeach; ?>
      <a href="logout.php"
         class="flex items-center py-2 px-4 mt-4 rounded hover:bg-blue-700 text-red-400">
        <i class="fas fa-sign-out-alt mr-2"></i> Logout
      </a>
    </nav>
  </aside>

  <!-- Main Content -->
  <div class="flex-1 flex flex-col overflow-hidden">
    <!-- Header -->
    <header class="bg-white shadow-sm p-4 flex justify-between items-center">
      <h2 class="text-xl font-semibold"><?= htmlspecialchars($titles[$page]) ?></h2>
      <div class="flex items-center">
        <span class="mr-2"><?= htmlspecialchars($_SESSION['nama_lengkap']) ?></span>
        <div class="w-8 h-8 bg-blue-600 rounded-full flex items-center justify-center text-white">
          <?= strtoupper(substr($_SESSION['nama_lengkap'], 0, 1)) ?>
        </div>
      </div>
    </header>

    <!-- Page Include -->
    <main class="p-6 overflow-auto">
      <?php include __DIR__ . "/pages/{$page}.php"; ?>
    </main>
  </div>
</body>
</html>
