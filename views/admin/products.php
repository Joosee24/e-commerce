<?php
require '../../middleware/auth.php';
require '../../config/db.php';
if (!isAdmin()) {
    header("Location: ../user/dashboard.php");
    exit();
}

// Konfigurasi Pagination
$limit = 4; // Jumlah produk per halaman
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Ambil total produk
$total_products = $conn->query("SELECT COUNT(DISTINCT p.id) AS total FROM produk p LEFT JOIN stok_produk s ON p.id = s.produk_id")->fetch_assoc()['total'];
$total_pages = ceil($total_products / $limit);

// Ambil data produk dengan pagination dan stok
$result = $conn->query("
    SELECT p.id, p.nama_produk, p.gambar, p.deskripsi, GROUP_CONCAT(s.size ORDER BY s.size ASC) AS sizes, 
           GROUP_CONCAT(s.stok ORDER BY s.size ASC) AS stok, GROUP_CONCAT(s.harga ORDER BY s.size ASC) AS harga
    FROM produk p
    LEFT JOIN stok_produk s ON p.id = s.produk_id
    GROUP BY p.id
    LIMIT $limit OFFSET $offset
");


?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <title>Manajemen Produk</title>
</head>
<body class="bg-gray-100">

<!-- Navbar -->
<nav class="flex justify-between items-center p-4 shadow-md">
    <h1 class=" text-xl font-bold">Logo</h1>
    <div class="flex items-center space-x-4">
        <a href="dashboard.php" class=" hover:underline">Dashboard</a>
        <a href="products.php" class=" hover:underline">Manajemen Produk</a>
        <a href="add_product.php" class=" hover:underline">Tambah Produk</a>

        <!-- Dropdown -->
        <div class="relative">
            <button id="dropdownBtn" class=" flex items-center space-x-2">
                <span><?php echo $_SESSION['username']; ?></span>
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                </svg>
            </button>

            <!-- Menu Dropdown -->
            <div id="dropdownMenu" class="hidden absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg py-2 z-10">
                <a href="../../auth/logout.php" class="block px-4 py-2 text-red-600 hover:bg-gray-200">Logout</a>
            </div>
        </div>
    </div>
</nav>

<!-- Container -->
<div class="max-w-5xl mx-auto mt-6 p-6 bg-white rounded-lg shadow-md">
    <h2 class="text-2xl font-semibold mb-4">Daftar Produk</h2>

    <!-- Pencarian -->
    <div class="mb-4">
        <form action="" method="GET">
            <input type="text" name="search" placeholder="Cari produk..." class="border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 p-2 w-full" />
            <button type="submit" class="mt-2 px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">Cari</button>
        </form>
    </div>

    <!-- Tabel Produk -->
    <div class="overflow-x-auto">
        <table class="w-full border-collapse border border-gray-300">
            <thead class="bg-gray-200">
                <tr>
                    <th class="border p-3">Gambar</th>
                    <th class="border p-3">Nama</th>
                    <th class="border p-3">Kategori</th>
                    <th class="border p-3">Size</th>
                    <th class="border p-3">Harga</th>
                    <th class="border p-3">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()): ?>
                <tr class="text-center">
                    <td class="border p-2">
                        <img src="../../uploads/<?php echo $row['gambar']; ?>" class="w-16 h-16 object-cover rounded">
                    </td>
                    <td class="border p-2"><?php echo $row['nama_produk']; ?></td>
                    <td class="border p-2"><?php echo $row['deskripsi']; ?></td>
                    <td class="border p-2">
                        <?php 
                        // Menampilkan ukuran produk
                        $sizes = !empty($row['sizes']) ? explode(",", $row['sizes']) : ['Tidak tersedia'];
                        echo implode(", ", $sizes); // Menampilkan ukuran sebagai string
                        ?>
                    </td>
                    <td class="border p-2 font-semibold text-gray-700">
                        <?php 
                        // Menampilkan harga berdasarkan ukuran
                        $harga = !empty($row['harga']) ? explode(",", $row['harga']) : [];  // Cek jika harga tidak kosong
                        $sizes = !empty($row['sizes']) ? explode(",", $row['sizes']) : [];  // Cek jika sizes tidak kosong
                        
                        $price_display = [];
                        foreach ($sizes as $index => $size) {
                            // Pastikan harga valid sebelum ditampilkan
                            $price = isset($harga[$index]) ? floatval($harga[$index]) : 0;  // Convert ke float jika valid, atau 0 jika tidak
                            if ($price > 0) {
                                $price_display[] = $size . ': Rp ' . number_format($price, 2, ',', '.');  // Menampilkan harga per ukuran
                            } else {
                                $price_display[] = $size . ': Harga tidak tersedia'; // Tampilkan jika harga tidak ada
                            }
                        }

                        echo implode('<br>', $price_display);  // Menampilkan harga dengan pemisah baris baru
                        ?>
                    </td>
                    <td class="border p-2 space-x-2">
                        <a href="edit_product.php?id=<?php echo $row['id']; ?>" 
                           class="px-3 py-1 bg-blue-600 text-white rounded hover:bg-blue-700">
                            Edit
                        </a>
                        <a href="../../actions/delete_product.php?id=<?php echo $row['id']; ?>" 
                           onclick="return confirm('Yakin ingin menghapus?')" 
                           class="px-3 py-1 bg-red-600 text-white rounded hover:bg-red-700">
                            Hapus
                        </a>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <div class="flex justify-center space-x-2 mt-4">
        <?php if ($page > 1): ?>
            <a href="?page=<?php echo $page - 1; ?>" 
               class="px-4 py-2 bg-gray-300 text-gray-800 rounded hover:bg-gray-400">
                ← Sebelumnya
            </a>
        <?php endif; ?>

        <span class="px-4 py-2 bg-blue-500 text-white rounded">
            Halaman <?php echo $page; ?> dari <?php echo $total_pages; ?>
        </span>

        <?php if ($page < $total_pages): ?>
            <a href="?page=<?php echo $page + 1; ?>" 
               class="px-4 py-2 bg-gray-300 text-gray-800 rounded hover:bg-gray-400">
                Berikutnya →
            </a>
        <?php endif; ?>
    </div>
</div>

<!-- JavaScript Dropdown -->
<script>
    const dropdownBtn = document.getElementById("dropdownBtn");
    const dropdownMenu = document.getElementById("dropdownMenu");

    dropdownBtn.addEventListener("click", () => {
        dropdownMenu.classList.toggle("hidden");
    });

    document.addEventListener("click", (event) => {
        if (!dropdownBtn.contains(event.target) && !dropdownMenu.contains(event.target)) {
            dropdownMenu.classList.add("hidden");
        }
    });
</script>

</body>
</html>
