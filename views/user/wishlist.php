<?php
session_start();
require '../../config/db.php';

if (!isset($_SESSION['user_id'])) {
  die("Silakan login terlebih dahulu.");
}

$user_id = $_SESSION['user_id'];

// Ambil daftar wishlist user
$stmt = $conn->prepare("SELECT produk.id, produk.nama_produk, produk.kategori, produk.gambar 
                      FROM wishlist 
                      JOIN produk ON wishlist.produk_id = produk.id 
                      WHERE wishlist.user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

//profil
$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT profile_picture FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$profile_picture = $user ? $user['profile_picture'] : 'default.png';

?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <script src="https://cdn.tailwindcss.com"></script>
  <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
  <title>whistlist</title>
</head>
<body>
<nav class="flex justify-between items-center p-4 border b-2 bg-gray-100 z-40">
    <h1 class="text-black text-xl font-bold">Luxury Vibes</h1>
    </div>
    <div class="flex items-center space-x-4">
        <a href="cart.php"><i class="fa-solid fa-cart-shopping" style="color: #000000;"></i></a>
        <div class="border-l-2 border-gray-400 h-6"></div>
        <a href="dashboard.php" class="text-black hover:underline">Dashboard</a>
        <a href="wishlist.php" class="text-black hover:underline">wishlist</a>
        <?php if (!empty($profile_picture)) : ?>
            <img src="../../uploads/<?= htmlspecialchars($profile_picture); ?>" 
                alt="Profile Picture" 
                class="h-12 w-12 rounded-full object-cover border-2 border-black">
        <?php endif; ?>
        <div class="relative">
            <button id="dropdownBtn" class="text-black focus:outline-none flex items-center space-x-2">
                <span class="text-black"><?php echo $_SESSION['username']; ?></span>
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                </svg>
            </button>
            <div id="dropdownMenu" class="hidden absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg py-2 z-10">
            <a href="#" id="openModal" class="block px-4 py-2 text-red-600 hover:bg-gray-200">profile</a>
                <a href="../../auth/logout.php" class="block px-4 py-2 text-red-600 hover:bg-gray-200">Logout</a>
            </div>
        </div>
    </div>
</nav>
  <main class="m-6">
  <h1 class="text-xl font-bold mb-4">Wishlist Saya</h1>

<div class="grid grid-cols-5 gap-7">
    <?php while ($row = $result->fetch_assoc()): ?>
        <div class="">
            <img src="../../uploads/<?= $row['gambar'] ?>" alt="<?= htmlspecialchars($row['nama_produk']) ?>" class="img-produk w-full h-64 object-cover rounded">
            <p class="kategori text-gray-700  rounded-bl-xl pl-2"><?= htmlspecialchars($row['kategori']) ?></p>
            <h2 class="font-semibold m-2"><?= htmlspecialchars($row['nama_produk']) ?></h2>
            <div class="flex flex-col gap-3  justify-between">
            <button 
            class="text-white bg-black border border-white rounded-md p-2"
            onclick="window.location.href='../user/product_detail.php?produk_id=<?= $row['id'] ?>'">view</button>
            <a href="../../controllers/remove_from_wishlist.php?produk_id=<?= $row['id'] ?>" class="text-white bg-red-600 p-2 text-sm ml-auto"><i class="fa-solid fa-trash" style="color: #ffffff;"></i>Hapus</a>
            </div>
        </div>
    <?php endwhile; ?>
</div>
    </main>
</body>
</html>

<style>
.kategori {
    background: linear-gradient(135deg, #388e3c, #66bb6a); 
    color: #ffffff;
}

.img-produk{
    transition:0.3s;
}

.img-produk:hover{
    filter: brightness(.9);
}
</style>

<script>
  document.addEventListener("DOMContentLoaded", function () {
    const dropdownBtn = document.getElementById("dropdownBtn");
    const dropdownMenu = document.getElementById("dropdownMenu");

    dropdownBtn.addEventListener("click", function () {
        dropdownMenu.classList.toggle("hidden");
    });

    document.addEventListener("click", function (event) {
        if (!dropdownBtn.contains(event.target) && !dropdownMenu.contains(event.target)) {
            dropdownMenu.classList.add("hidden");
        }
    });
  });
</script>