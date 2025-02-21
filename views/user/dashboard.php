<?php
require '../../config/db.php';
require '../../middleware/auth.php';

// Proteksi hanya untuk user
if (!isUser  ()) {
    header("Location: ../admin/dashboard.php");
    exit();
}

// Ambil kategori yang tersedia
date_default_timezone_set('Asia/Jakarta');
$kategori_result = $conn->query("SELECT DISTINCT kategori FROM produk");
$kategori_list = [];
while ($row = $kategori_result->fetch_assoc()) {
    $kategori_list[] = $row['kategori'];
}

// Ambil kategori yang dipilih dari URL (jika ada)
$kategori_terpilih = isset($_GET['kategori']) ? $_GET['kategori'] : '';

// Query produk dengan filter kategori jika dipilih
$query_produk = "SELECT p.id, p.nama_produk, p.gambar, MIN(s.harga) as harga, p.kategori
                 FROM produk p 
                 LEFT JOIN stok_produk s ON p.id = s.produk_id";

if (!empty($kategori_terpilih)) {
    $query_produk .= " WHERE p.kategori = ?";
}

$query_produk .= " GROUP BY p.id ORDER BY p.created_at DESC LIMIT 20";
$stmt = $conn->prepare($query_produk);

if (!empty($kategori_terpilih)) {
    $stmt->bind_param("s", $kategori_terpilih);
}

$stmt->execute();
$result = $stmt->get_result();

// Ambil semua rating dan ulasan untuk ditampilkan
$ratingQuery = $conn->query("SELECT users.username, ratings.rating, ratings.review, ratings.created_at FROM ratings JOIN users ON ratings.user_id = users.id ORDER BY ratings.created_at DESC");
$ratings = $ratingQuery->fetch_all(MYSQLI_ASSOC);


?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <title>Beranda - Toko Baju</title>
</head>
<body class="bg-gray-100">
<nav class="flex justify-between items-center p-4 border b-2 bg-gray-100 z-40">
    <h1 class="text-black text-xl font-bold">Luxury Vibes</h1>
    <div class="flex-grow flex justify-center">
        <div class="dropdown relative flex">
            <div x-data="{ open: false }" class="relative">
                <button @click="open = !open" class="categori px-4 py-2 border rounded-md bg-white shadow">
                    Pilih Kategori
                </button>
                <ul x-show="open" @click.away="open = false" class="absolute left-0 mt-2 w-48 bg-white border rounded-md shadow-lg">
                    <li>
                        <a href="?" class="block px-4 py-2 text-gray-700 hover:bg-gray-200">All Kategori</a>
                    </li>
                    <?php foreach ($kategori_list as $kategori): ?>
                        <li>
                            <a href="?kategori=<?= urlencode($kategori); ?>" class="block px-4 py-2 text-gray-700 hover:bg-gray-200">
                                <?= htmlspecialchars($kategori); ?>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
                <input type="search" id="site-search" name="q" placeholder="Silakan cari di sini..." class="w-96 border-2 rounded-lg p-1 ml-4" />
            </div>
        </div>
    </div>
    <div class="flex items-center space-x-4">
        <a href="cart.php"><i class="fa-solid fa-cart-shopping" style="color: #000000;"></i></a>
        <div class="border-l-2 border-gray-400 h-6"></div>
        <a href="dashboard.php" class="text-black hover:underline">Dashboard</a>
        <a href="wishlist.php" class="text-black hover:underline">wishlist</a>
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

<main class="p-6">
    <section class="iklan relative w-full overflow-hidden">
        <div class="slider flex transition-transform duration-500 ease-in-out">
            <div class="slide">
                <img src="../../img/newbrand.jpg" alt="models" class="w-full h-96 object-cover">
            </div>
            <div class="slide">
                <img src="../../img/outfit.jpg" alt="models" class="w-full h-96 object-cover">
            </div>
        </div>
    </section>

    <ul class="flex gap-5 mt-5">
    <li>
        <a href="?kategori=" class="border border-gray-400 rounded-lg p-2 <?= empty($kategori_terpilih) ? 'bg-gray-300' : '' ?>">
            All Kategori
        </a>
    </li>
    <?php foreach ($kategori_list as $kategori) : ?>
        <li>
            <a href="?kategori=<?= urlencode($kategori) ?>" 
               class="border border-gray-400 rounded-lg p-2 <?= ($kategori_terpilih === $kategori) ? 'bg-gray-300' : '' ?>">
                <?= htmlspecialchars($kategori) ?>
            </a>
        </li>
    <?php endforeach; ?>
    </ul>
    <div class="container mt-6">
        <h2 class="text-2xl font-bold mb-4">Daftar Produk</h2>
        <div class="grid grid-cols-5 gap-10">
            <?php while ($row = $result->fetch_assoc()): ?>
                <div class="product">
                    <div>
                        <img src="../../uploads/<?= $row['gambar']; ?>" 
                             alt="<?= htmlspecialchars($row['nama_produk']); ?>" 
                             class="img-produk w-full object-cover rounded-t-lg">
                    </div>
                    <p class="kategori text-gray-500 text-sm px-4 py-1 rounded-bl-xl"><?= htmlspecialchars($row['kategori']); ?></p>
                    <div>
                        <div class="flex flex-row justify-between">
                            <h3 class="text-lg font-semibold text-gray-500"><?= htmlspecialchars($row['nama_produk']); ?></h3>
                            <button class="wishlist-btn" data-produk-id="<?= intval($row['id']); ?>">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-6 h-6">
                                    <path d="M19.84 4.61a5.5 5.5 0 0 0-7.78 0L12 4.67l-.06-.06a5.5 5.5 0 0 0-7.78 7.78l7.78 7.78 7.78-7.78a5.5 5.5 0 0 0 0-7.78z"/>
                                </svg>
                            </button>
                        </div>
                        <p class="text-black font-bold text-lg mt-2">
                            Rp <?= number_format($row['harga'], 2, ',', '.'); ?>
                        </p>
                        <a href="product_detail.php?id=<?= $row['id']; ?>"
                           class="btn-view mt-4 block text-black text-center py-2 border border-black rounded-md">
                            View Order
                        </a>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
        <p id="no-results" class="text-red-500 mt-4 hidden">Item tidak ditemukan</p>
    </div>

    <div class="border b-2 bg-gray-100 mt-10"></div>
  <section class="mt-10">
    <h3 class="text-2xl font-bold">rating web</h3>
    <div class="max-w-2xl mt-4 bg-white p-6 shadow-2xl rounded-md">
        <h2 class="text-lg font-semibold mb-4">Beri Rating & Ulasan</h2>

        <form action="../../controllers/rate.php" method="POST" class="space-y-4">
            <div class="flex space-x-1" id="star-rating">
                <?php for ($i = 1; $i <= 5; $i++): ?>
                    <label class="cursor-pointer">
                        <input type="radio" name="rating" value="<?= $i ?>" class="hidden" required>
                        <span class="text-3xl text-gray-400 star">★</span>
                    </label>
                <?php endfor; ?>
            </div>

            <textarea name="review" class="w-full border p-2 rounded-md" placeholder="Tulis ulasan Anda di sini..." required></textarea>

            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">Kirim</button>
        </form>
    </div>

    <div class="max-w-full mt-10 bg-white p-6 shadow-2xl rounded-md">
        <h2 class="text-lg font-semibold mb-4">Ulasan Pengguna</h2>
        
        <?php if (!empty($ratings)): ?>
            <?php foreach ($ratings as $r): ?>
                <div class="border-b py-3">
                    <p class="font-semibold"><?= htmlspecialchars($r['username'] ?? '', ENT_QUOTES, 'UTF-8') ?></p>
                    <p>
                        <?php for ($i = 1; $i <= 5; $i++): ?>
                            <span class="text-xl <?= $i <= $r['rating'] ? 'text-yellow-500' : 'text-gray-300' ?>">★</span>
                        <?php endfor; ?>
                    </p>
                    <p class="text-gray-700"><?= htmlspecialchars($r['review'] ?? '', ENT_QUOTES, 'UTF-8') ?></p>
                    <small class="text-gray-500"><?= date('d M Y, H:i', strtotime($r['created_at'])) ?></small>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p class="text-gray-500">Belum ada ulasan.</p>
        <?php endif; ?>
    </div>
    </section>
</main>
<footer class="border-t-2 p-6">
    <section class="flex flex-row">
        <div class="flex flex-row gap-20 ">
            <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3964.927610825674!2d106.97310707505089!3d-6.403327993587334!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x2e699445f0d1c541%3A0x3c8a27a75eb76093!2sSMK%20Metland%20School!5e0!3m2!1sid!2sid!4v1739931919381!5m2!1sid!2sid" width="400" height="300" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
            <div class="flex flex-col">
                <h3 class="text-black text-xl font-bold">Luxury Vibes</h3>
                <ul>
                    <li>tentang kami</li>
                    <li>karir</li>
                    <li>blog</li>
                </ul>
            </div>
            <div class="flex flex-col">
                <h3 class="text-black text-xl font-bold">Methode</h3>
                <ul>
                    <li>gopay</li>
                    <li>virtual account</li>
                    <li>shoopePay</li>
                </ul>
            </div>
            <div class="flex flex-col">
                <h3 class="text-black text-xl font-bold">Ikuti Kami</h3>
                <ul>
                    <li><i class="fa-brands fa-github"></i>github</li>
                    <li><i class="fa-brands fa-instagram"></i>instagram</li>
                    <li><i class="fa-brands fa-facebook"></i>facebook</li>
                </ul>
            </div>
            <div class="flex flex-col">
                <h3 class="text-black text-xl font-bold">Produk</h3>
                <ul>
                    <li>baju</li>
                    <li>jaket</li>
                    <li>hoodie</li>
                </ul>
            </div>
            <div class="flex flex-col">
                <h3 class="text-4xl font-bold">Luxury Vibes</h3>
                <span>@by jose</span>
            </div>
        </div>
    </section>
</footer>
</body>
</html>

<style>
nav {
    top: 0;
    position: sticky;
}

.categori {
    background-color: #04AA6D;
    color: white;
    padding: 10px;
    font-size: 16px;
    border: none;
    cursor: pointer;
}

.categori:hover, .kategori:focus {
    background-color: #3e8e41;
}

#myInput {
    box-sizing: border-box;
    background-image: url('searchicon.png');
    background-position: 14px 12px;
    background-repeat: no-repeat;
    font-size: 16px;
    padding: 14px 20px 12px 45px;
    border: none;
    border-bottom: 1px solid #ddd;
}

#myInput:focus {
    outline: 3px solid #ddd;
}



.kategori {
    background: linear-gradient(135deg, #388e3c, #66bb6a); 
    color: #ffffff;
}

.btn-view:hover {
    background: linear-gradient(135deg, #388e3c, #66bb6a); 
    color: #ffffff;
    border: none;
    transition: 0.2s;
}

.slider {
    display: flex;
    transition: transform 0.5s ease-in-out;
}

.slide {
    min-width: 100%;
    transition: opacity 1s ease-in-out;
}

.hidden {
    opacity: 0;
    pointer-events: none; 
}

.visible {
    opacity: 1;
}

.img-produk{
    transition:0.3s;
}

.img-produk:hover{
    filter: brightness(.9);
}
</style>

<script>
//dropdown users
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

    // Fitur search produk
    const searchInput = document.getElementById("site-search");
    const products = document.querySelectorAll(".product");
    const noResultsMessage = document.getElementById("no-results");

    searchInput.addEventListener("keyup", function () {
        const filter = searchInput.value.toLowerCase();
        let hasResults = false;

        products.forEach(product => {
            const productName = product.querySelector("h3").textContent.toLowerCase();

            if (productName.includes(filter)) {
                product.style.display = "";
                hasResults = true;
            } else {
                product.style.display = "none";
            }
        });

        if (hasResults) {
            noResultsMessage.classList.add("hidden");
        } else {
            noResultsMessage.classList.remove("hidden");
        }
    });

    // Fitur slider gambar
    const slides = document.querySelectorAll(".slide");
    const slider = document.querySelector(".slider");
    let index = 0;

    function showSlide() {
        slider.style.transform = `translateX(-${index * 100}%)`;
        index = (index + 1) % slides.length;
    }

    showSlide();
    setInterval(showSlide, 3000);
});

//whistlist conection
document.querySelectorAll(".wishlist-btn").forEach((button) => {
    button.addEventListener("click", function () {
        const produkId = this.getAttribute("data-produk-id");
        
        console.log("Produk ID yang dikirim:", produkId); // 🔍 Cek apakah ID benar

        fetch("../../controllers/add_to_whistlist.php", {
            method: "POST",
            headers: { "Content-Type": "application/x-www-form-urlencoded" },
            body: new URLSearchParams({ produk_id: produkId }),
        })
        .then(response => response.json())
        .then(data => {
            console.log("Response dari server:", data); // 🔍 Debug respons dari server
            alert(data.message);
        })
        .catch(error => console.error("Error:", error));
    });
});




//untuk rating 
document.addEventListener("DOMContentLoaded", function () {
            const stars = document.querySelectorAll("#star-rating .star");
            const inputs = document.querySelectorAll("input[name='rating']");

            stars.forEach((star, index) => {
                star.addEventListener("click", function () {
                    inputs[index].checked = true;
                    updateStars(index);
                });

                star.addEventListener("mouseover", function () {
                    highlightStars(index);
                });

                star.addEventListener("mouseleave", function () {
                    resetStars();
                });
            });

            function updateStars(selectedIndex) {
                stars.forEach((star, index) => {
                    star.classList.toggle("text-yellow-500", index <= selectedIndex);
                    star.classList.toggle("text-gray-400", index > selectedIndex);
                });
            }

            function highlightStars(hoverIndex) {
                stars.forEach((star, index) => {
                    star.classList.toggle("text-yellow-400", index <= hoverIndex);
                    star.classList.toggle("text-gray-300", index > hoverIndex);
                });
            }

            function resetStars() {
                let selectedIndex = [...inputs].findIndex(input => input.checked);
                updateStars(selectedIndex);
            }
        });
</script>