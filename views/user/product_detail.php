<?php
require '../../config/db.php';
require '../../middleware/auth.php';

if (!isset($_GET['id'])) {
    header("Location: dashboard.php");
    exit();
}

$id = $_GET['id'];

$query = $conn->prepare("SELECT p.nama_produk, p.gambar, p.deskripsi, p.kategori FROM produk p WHERE p.id = ?");
$query->bind_param("i", $id);
$query->execute();
$product = $query->get_result()->fetch_assoc();

$query = $conn->prepare("SELECT size, harga, stok FROM stok_produk WHERE produk_id = ?");
$query->bind_param("i", $id);
$query->execute();
$sizes = $query->get_result()->fetch_all(MYSQLI_ASSOC);

//profil
$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT profile_picture FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$profile_picture = $user ? $user['profile_picture'] : 'default.png';

$stmt = $conn->prepare("SELECT AVG(rating) as avg_rating, COUNT(*) as total_reviews FROM reviews WHERE produk_id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$reviewData = $stmt->get_result()->fetch_assoc();
$avg_rating = round($reviewData['avg_rating'], 1);
$total_reviews = $reviewData['total_reviews'];

// Ambil semua ulasan
$stmt = $conn->prepare("
    SELECT u.username, r.rating, r.review, r.created_at 
    FROM reviews r 
    JOIN users u ON r.user_id = u.id 
    WHERE r.produk_id = ? 
    ORDER BY r.created_at DESC
");
$stmt->bind_param("i", $id);
$stmt->execute();
$reviews = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$total_reviews = count($reviews);

?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <title>Detail Produk</title>
</head>
<body class="bg-gray-100">
<nav class="flex justify-between items-center p-4 border b-2 bg-gray-100 z-40">
    <h1 class="text-black text-xl font-bold">Luxury Vibes</h1>
    </div>
    <div class="flex items-center space-x-4">
        <a href="cart.php"><i class="fa-solid fa-cart-shopping" style="color: #000000;"></i></a>
        <div class="border-l-2 border-gray-400 h-6"></div>
        <a href="dashboard.php" class="text-black hover:underline">Dashboard</a>
        <a href="wishlist.php" class="text-black hover:underline">wishlist</a>
        <img src="../../uploads/<?= htmlspecialchars($profile_picture); ?>" alt="Profile Picture" class="h-12 w-12 rounded-full object-cover border-2 border-black">
        <div class="relative">
            <button id="dropdownBtn" class="text-black focus:outline-none flex items-center space-x-2">
                <span class="text-black"><?php echo $_SESSION['username']; ?></span>
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                </svg>
            </button>
            <div id="dropdownMenu" class="hidden absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg py-2 z-10">
                <a href="../../auth/logout.php" class="block px-4 py-2 text-red-600 hover:bg-gray-200">Logout</a>
            </div>
        </div>
    </div>
</nav>
<div class="container mx-auto p-6">
    <div class="flex justify-between items-center mb-4">
        <a href="javascript:history.back()" class="btn-back bg-black text-white flex gap-2 items-center shadow-lg rounded-lg p-2 hover:bg-gray-800 transition duration-200">
            <i class="fa-solid fa-arrow-left"></i>Kembali
        </a>
    </div>

    <div class="p-6 rounded-lg grid grid-cols-1 md:grid-cols-2 gap-8">
        <div class="flex justify-center">
            <img src="../../uploads/<?= htmlspecialchars($product['gambar']); ?>" 
                 alt="<?= htmlspecialchars($product['nama_produk']); ?>" 
                 class="w-full max-w-md object-cover rounded-lg border border-gray-200 shadow-md transition-transform duration-300 transform hover:scale-105">
        </div>
        <div class="flex flex-col justify-between">
            <div>
                <h2 class="text-3xl font-bold text-gray-900"><?= htmlspecialchars($product['nama_produk']); ?></h2>
                <p class="text-gray-500 text-sm mt-1">Kategori: <?= htmlspecialchars($product['kategori']); ?></p>
               
                
                <form action="../../controllers/add_to_cart.php" method="POST" class="mt-6">
                    <input type="hidden" name="produk_id" value="<?= $id; ?>">
                    <input type="hidden" name="price" id="priceInput" value="<?= $sizes[0]['harga']; ?>">

                    <label class="block text-sm font-medium text-gray-700">Pilih Ukuran</label>
                    <select name="size" id="sizeSelector" class="mt-2 w-full p-2 border rounded-md bg-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500" onchange="updatePrice()">
                        <?php foreach ($sizes as $size): ?>
                            <option value="<?= $size['size']; ?>" data-price="<?= $size['harga']; ?>" data-stock="<?= $size['stok']; ?>">
                                <?= htmlspecialchars($size['size']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>

                    <p class="mt-2 text-gray-600">Stok tersedia: <span id="stockValue" class="font-semibold text-gray-900"><?= $sizes[0]['stok']; ?></span></p>

                    <p class="mt-4 text-xl font-bold text-black">
                        Harga: Rp <span id="price"><?= number_format($sizes[0]['harga'], 2, ',', '.'); ?></span>
                    </p>

                    <div class="mt-4">
                        <label class="block text-sm font-medium text-gray-700">Jumlah Pesanan</label>
                        <div class="flex items-center border rounded-md w-max">
                            <button type="button" onclick="decreaseQuantity()" class="px-3 py-1 bg-gray-300 hover:bg-gray-400 transition duration-200">-</button>
                            <input type="number" id="quantity" name="quantity" value="1" min="1" class="border-none text-center w-12">
                            <button type="button" onclick="increaseQuantity()" class="px-3 py-1 bg-gray-300 hover:bg-gray-400 transition duration-200">+</button>
                        </div>
                        <h3>Deskripsi</h3>
                        <p id="stockMessage" class="mt-2 text-red-500 text-sm hidden">Stok tidak cukup untuk jumlah yang diminta!</p>
                    </div>
                    <p class="text-gray-700 mt-3 text-sm"><?= nl2br(htmlspecialchars($product['deskripsi'])); ?></p>
                    <button type="submit" id="checkoutButton" class="bg-black text-white py-2 rounded-md hover:bg-green-700 transition duration-200 w-full mt-4">
                        Tambahkan ke Keranjang
                    </button>
                </form>
            </div>
        </div>
    </div>
    <div class="ulasan">
    <div class="flex flex-col">
    <h3 class="text-2xl font-bold">Penilaian & Ulasan</h3>
    <div class="flex flex-row items-center gap-5">
    <span class="text-2xl">5/5</span>
    <ul class="flex flex-row">
        <li><i class="fa-solid fa-star fa-xl" style="color: #FFD43B;"></i></li>
        <li><i class="fa-solid fa-star fa-xl" style="color: #FFD43B;"></i></li>
        <li><i class="fa-solid fa-star fa-xl" style="color: #FFD43B;"></i></li>
        <li><i class="fa-solid fa-star fa-xl" style="color: #FFD43B;"></i></li>
        <li><i class="fa-solid fa-star fa-xl" style="color: #FFD43B;"></i></li>
    </ul>
    <span>(<?= $total_reviews ?> ulasan)</span>
    </div>
    </div>
<form action="../../../ecommerce/controllers/review.php" method="POST" class="mt-4">
    <input type="hidden" name="produk_id" value="<?= $id ?>">
    <input type="hidden" name="rating" id="ratingInput" value="0"> <!-- Input Hidden untuk Rating -->

    <label for="rating">Rating:</label>
    <div id="ratingStars" class="flex space-x-1 text-2xl mt-2 text-gray-400 cursor-pointer">
        <i class="fa-solid fa-star" data-value="1"></i>
        <i class="fa-solid fa-star" data-value="2"></i>
        <i class="fa-solid fa-star" data-value="3"></i>
        <i class="fa-solid fa-star" data-value="4"></i>
        <i class="fa-solid fa-star" data-value="5"></i>
    </div>

    <br>
    <label for="review">Ulasan:</label>
    <textarea name="review" required class="w-full p-2 border rounded-md"></textarea>
    <br>
    <button type="submit" class="bg-black text-white py-2 px-4 rounded-md mt-3">Kirim Ulasan</button>
</form>

    <!-- Daftar Ulasan -->
    <div class="mt-6 border p-4 rounded-md shadow-md">
        <h3 class="text-xl font-bold">Ulasan Pengguna</h3>
        <?php if (count($reviews) > 0): ?>
            <?php foreach ($reviews as $review): ?>
                <div class="border-b py-2">
                    <div class="flex flex-row justify-between">
                    <p class="font-semibold"><?= htmlspecialchars($review['username']) ?></p>
                    <p class="text-sm text-gray-500">
                    <?= date("d-m-Y", strtotime($review['created_at'])) ?>
                </p>
                </div>
                    <div>
                        <?php for ($i = 1; $i <= 5; $i++): ?>
                            <span class="<?= $i <= $review['rating'] ? 'text-yellow-500' : 'text-gray-300' ?>">★</span>
                        <?php endfor; ?>
                    </div>
                    <p class="text-gray-600"><?= htmlspecialchars($review['review']) ?></p>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p class="text-gray-500">Belum ada ulasan.</p>
        <?php endif; ?>
    </div>
</div>

    </div>
</div>
<style>
    .container {
        max-width: 1200px;
    }
    .btn-back {
        transition: background-color 0.3s;
    }
    .btn-back:hover {
        background-color: #333;
    }
    .hover\:scale-105:hover {
        transform: scale(1.05);
    }
    .transition-transform {
        transition: transform 0.3s;
    }

    .btn-back{
        border:1px solid;
        padding:10px;
        background:#fffff;
    }
</style>
    
    <script>
        function updatePrice() {
            const selectedOption = document.querySelector('#sizeSelector option:checked');
            const pricePerUnit = parseFloat(selectedOption.dataset.price);
            const stock = parseInt(selectedOption.dataset.stock);
            let quantity = parseInt(document.getElementById('quantity').value);
    
            // Menghitung total harga berdasarkan jumlah pesanan
            const totalPrice = pricePerUnit * quantity;

            document.getElementById('price').innerText = totalPrice.toLocaleString('id-ID', { style: 'currency', currency: 'IDR' });
            document.getElementById('priceInput').value = totalPrice; // Kirim total harga ke form

            document.getElementById('stockValue').innerText = stock;
    
            const stockMessage = document.getElementById('stockMessage');
            const checkoutButton = document.getElementById('checkoutButton');
            if (quantity > stock) {
                stockMessage.classList.remove('hidden');
                checkoutButton.disabled = true;
                checkoutButton.classList.add('bg-gray-400', 'cursor-not-allowed');
            } else {
                stockMessage.classList.add('hidden');
                checkoutButton.disabled = false;
                checkoutButton.classList.remove('bg-gray-400', 'cursor-not-allowed');
            }
        }
    
        function increaseQuantity() {
            const quantityInput = document.getElementById('quantity');
            quantityInput.value = parseInt(quantityInput.value) + 1;
            updatePrice();
        }
    
        function decreaseQuantity() {
            const quantityInput = document.getElementById('quantity');
            if (quantityInput.value > 1) {
                quantityInput.value = parseInt(quantityInput.value) - 1;
                updatePrice();
            }
        }
    
        document.getElementById('quantity').addEventListener('input', function() {
            updatePrice();
        });

        document.addEventListener("DOMContentLoaded", function () {
        const stars = document.querySelectorAll("#ratingStars i");
        const ratingInput = document.getElementById("ratingInput");

        stars.forEach(star => {
            star.addEventListener("click", function () {
                const rating = this.getAttribute("data-value");
                ratingInput.value = rating; // Set nilai rating di input hidden
                
                // Ubah warna bintang yang dipilih dan sebelumnya
                stars.forEach(s => {
                    s.classList.remove("text-yellow-500");
                    s.classList.add("text-gray-400");
                });

                for (let i = 0; i < rating; i++) {
                    stars[i].classList.remove("text-gray-400");
                    stars[i].classList.add("text-yellow-500");
                }
            });
        });
    });
    </script>
</body>
</html>

