
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Stockify - Sistem Kasir & Inventory</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        .hero {
            background: linear-gradient(rgba(0, 0, 0, 0.7), rgba(0, 0, 0, 0.7)), url('assets/img/angkringan-bg.jpg');
            background-size: cover;
            background-position: center;
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="bg-blue-800 text-white p-4">
        <div class="container mx-auto flex justify-between items-center">
            <h1 class="text-2xl font-bold">Stockify</h1>
            <div>
                <a href="login.php" class="px-4 py-2 bg-white text-blue-800 rounded-lg font-medium mr-2 hover:bg-gray-100">
                    <i class="fas fa-sign-in-alt mr-1"></i> Login
                </a>
                <a href="register.php" class="px-4 py-2 bg-transparent border border-white text-white rounded-lg font-medium hover:bg-white hover:text-blue-800">
                    <i class="fas fa-user-plus mr-1"></i> Register
                </a>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero text-white py-20">
        <div class="container mx-auto text-center">
            <h1 class="text-4xl font-bold mb-4">Kelola Bisnis Anda dengan Mudah</h1>
            <p class="text-xl mb-8 max-w-2xl mx-auto">
                Stockify solusi anda mengatur penjualan, stok, dan laporan usaha bisnis anda secara modern.
            </p>
            <div class="space-x-4">
                <a href="register.php" class="px-6 py-3 bg-blue-600 rounded-lg font-medium hover:bg-blue-700">
                    <i class="fas fa-play mr-1"></i> Mulai Sekarang
                </a>
                <a href="#features" class="px-6 py-3 bg-transparent border border-white rounded-lg font-medium hover:bg-white hover:text-blue-800">
                    <i class="fas fa-info-circle mr-1"></i> Fitur Aplikasi
                </a>
            </div>
        </div>
    </section>

    <!-- Fitur Aplikasi -->
    <section id="features" class="py-16 bg-gray-100">
        <div class="container mx-auto px-4">
            <h2 class="text-3xl font-bold text-center mb-12">Fitur Stockify</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <!-- Fitur 1 -->
                <div class="bg-white p-6 rounded-lg shadow-md text-center">
                    <div class="text-blue-600 text-4xl mb-4">
                        <i class="fas fa-cash-register"></i>
                    </div>
                    <h3 class="text-xl font-semibold mb-2">Kasir Modern</h3>
                    <p class="text-gray-600">
                        Pencatatan transaksi cepat dengan tampilan user-friendly dan hitung kembalian otomatis.
                    </p>
                </div>
                
                <!-- Fitur 2 -->
                <div class="bg-white p-6 rounded-lg shadow-md text-center">
                    <div class="text-blue-600 text-4xl mb-4">
                        <i class="fas fa-boxes"></i>
                    </div>
                    <h3 class="text-xl font-semibold mb-2">Manajemen Stok</h3>
                    <p class="text-gray-600">
                        Pantau stok produk real-time dan dapatkan notifikasi jika stok hampir habis.
                    </p>
                </div>
                
                <!-- Fitur 3 -->
                <div class="bg-white p-6 rounded-lg shadow-md text-center">
                    <div class="text-blue-600 text-4xl mb-4">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <h3 class="text-xl font-semibold mb-2">Laporan Penjualan</h3>
                    <p class="text-gray-600">
                        Visualisasi data penjualan harian/mingguan dengan grafik interaktif.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-blue-800 text-white py-8">
        <div class="container mx-auto text-center">
            <p>&copy; <?= date('Y') ?> Stockify. All rights reserved.</p>
            <p class="mt-2">Dibangun dengan <i class="fas fa-heart text-red-400"></i> untuk UMKM</p>
        </div>
    </footer>
</body>
</html>