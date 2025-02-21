<?php
require '../../middleware/auth.php';
require '../../config/db.php'; // Pastikan Anda menghubungkan ke database

// Proteksi hanya untuk admin
if (!isAdmin()) {
    header("Location: ../user/dashboard.php");
    exit();
}

// Pagination
$limit = 6; // Jumlah data per halaman
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1; // Halaman saat ini
$offset = ($page - 1) * $limit; // Offset untuk query

// Ambil total pengguna untuk menghitung total halaman
$totalQuery = $conn->query("SELECT COUNT(*) as total FROM users");
$totalRow = $totalQuery->fetch_assoc();
$totalUsers = $totalRow['total'];
$totalPages = ceil($totalUsers / $limit); // Total halaman

// Ambil data pengguna dari tabel users dengan pagination
$users = [];
$query = $conn->prepare("SELECT * FROM users LIMIT ? OFFSET ?");
$query->bind_param("ii", $limit, $offset);
$query->execute();
$result = $query->get_result();

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $users[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <title>Dashboard Admin</title>
</head>
<body class="bg-gray-100">
    <nav class="flex justify-between items-center border b-2 bg-gray-100 p-4 shadow-md">
        <h1 class="text-xl font-bold">Logo</h1>
        <div class="flex items-center space-x-4">
            <a href="dashboard.php" class="hover:underline">Dashboard</a>
            <a href="products.php" class="hover:underline">Manajemen Produk</a>
            <a href="add_product.php" class="hover:underline">Tambah Produk</a>
            
            <!-- Dropdown -->
            <div x-data="{ open: false }" class="relative">
                <button @click="open = !open" class="focus:outline-none flex items-center space-x-2">
                    <span><?php echo $_SESSION['username']; ?></span>
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </button>

                <!-- Menu Dropdown -->
                <div x-show="open" @click.away="open = false" 
                     class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg py-2 z-10">
                    <a href="../../auth/logout.php" class="block px-4 py-2 text-red-600 hover:bg-gray-200">Logout</a>
                </div>
            </div>
        </div>
    </nav>

    <div class="max-w-4xl mx-auto mt-10 bg-white p-6 shadow rounded-md">
        <h3 class="text-lg font-semibold mt-6">Daftar Pengguna</h3>
        <table class="min-w-full mt-4 border-collapse border border-gray-300">
            <thead>
                <tr class="bg-gray-200 text-gray-700">
                    <th class="p-3 border border-gray-300">ID</th>
                    <th class="p-3 border border-gray-300">Username</th>
                    <th class="p-3 border border-gray-300">Email</th>
                    <th class="p-3 border border-gray-300">Role</th>
                    <th class="p-3 border border-gray-300">Tanggal Dibuat</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $user): ?>
                    <tr class="border-b">
                        <td class="p-3 border border-gray-300 text-center"><?php echo htmlspecialchars($user['id']); ?></td>
                        <td class="p-3 border border-gray-300"><?php echo htmlspecialchars($user['username']); ?></td>
                        <td class="p-3 border border-gray-300"><?php echo htmlspecialchars($user['email']); ?></td>
                        <td class="p-3 border border-gray-300"><?php echo htmlspecialchars($user['role']); ?></td>
                        <td class="p-3 border border-gray-300"><?php echo htmlspecialchars($user['created_at']); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <!-- Pagination -->
        <div class="mt-4">
            <nav class="flex justify-between">
                <div>
                    <?php if ($page > 1): ?>
                        <a href="?page=<?php echo $page - 1; ?>" class="px-4 py-2 bg-gray-300 text-black rounded hover:bg-gray-400">Sebelumnya</a>
                    <?php endif; ?>
                </div>
                <div>
                    <?php if ($page < $totalPages): ?>
                        <a href="?page=<?php echo $page + 1; ?>" class="px-4 py-2 bg-gray-300 text-black rounded hover:bg-gray-400">Selanjutnya</a>
                    <?php endif; ?>
                </div>
            </nav>
        </div>
    </div>
</body>
</html>