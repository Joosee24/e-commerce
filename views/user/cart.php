<?php
session_start();
require '../../config/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$cart = [];

$query = $conn->prepare("SELECT cart.*, produk.nama_produk, produk.gambar FROM cart 
                         JOIN produk ON cart.produk_id = produk.id 
                         WHERE cart.user_id = ?");
$query->bind_param("i", $user_id);
$query->execute();
$result = $query->get_result();

while ($row = $result->fetch_assoc()) {
    $cart[] = $row;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <title>Keranjang Belanja</title>
    <script src="https://app.sandbox.midtrans.com/snap/snap.js" data-client-key="SB-Mid-client-zQ9K1NDc50anbBiB"></script>
</head>
<body class="bg-gray-100">

<nav class="flex justify-between items-center p-4 border b-2 bg-gray-100 z-40">
    <h1 class="text-black text-xl font-bold">Luxury Vibes</h1>
    </div>
    <div class="flex items-center space-x-4">
        <a href="cart.php"><i class="fa-solid fa-cart-shopping" style="color: #000000;"></i></a>
        <div class="border-l-2 border-gray-400 h-6"></div>
        <a href="dashboard.php" class="text-black hover:underline">Dashboard</a>
        <a href="whistlist.php" class="text-black hover:underline">whistlist</a>
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
    <h2 class="text-3xl font-bold text-gray-800 mb-6 text-center">Keranjang Belanja</h2>

    <?php if (empty($cart)): ?>
        <p class="text-center text-gray-600">Keranjang belanja Anda kosong.</p>
    <?php else: ?>
        <div class="bg-white shadow-lg rounded-lg p-6">
            <table class="w-full border-collapse">
                <thead>
                    <tr class="bg-gray-200 text-gray-700">
                        <th class="p-3 text-center">Pilih</th>
                        <th class="p-3 text-left">Gambar</th>
                        <th class="p-3 text-left">Produk</th>
                        <th class="p-3 text-left">Size</th>
                        <th class="p-3 text-center">Quantity</th>
                        <th class="p-3 text-center">Harga</th>
                        <th class="p-3 text-center">Total</th>
                        <th class="p-3 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($cart as $index => $item): ?>
                        <tr class="border-b">
                            <td class="p-3 text-center">
                                <input type="checkbox" class="cart-checkbox" 
                                       data-produk-id="<?php echo $item['produk_id']; ?>" 
                                       data-nama="<?php echo htmlspecialchars($item['nama_produk']); ?>" 
                                       data-quantity="<?php echo $item['quantity']; ?>" 
                                       data-harga="<?php echo $item['price']; ?>" 
                                       data-size="<?php echo $item['size']; ?>" 
                                       data-gambar="<?php echo $item['gambar']; ?>">
                            </td>
                            <td class="p-3">
                                <img src="../../uploads/<?php echo htmlspecialchars($item['gambar']); ?>" 
                                     alt="<?php echo htmlspecialchars($item['nama_produk']); ?>" 
                                     class="w-20 h-20 object-cover rounded">
                            </td>
                            <td class="p-3"><?php echo htmlspecialchars($item['nama_produk']); ?></td>
                            <td class="p-3"><?php echo htmlspecialchars($item['size']); ?></td>
                            <td class="p-3 text-center"><?php echo htmlspecialchars($item['quantity']); ?></td>
                            <td class="p-3 text-center">Rp <?php echo number_format($item['price'], 2, ',', '.'); ?></td>
                            <td class="p-3 text-center">Rp <?php echo number_format($item['quantity'] * $item['price'], 2, ',', '.'); ?></td>
                            <td class="p-3 text-center">
                                <a href="../../controllers/hapus_cart.php?produk_id=<?php echo $item['produk_id']; ?>&size=<?php echo $item['size']; ?>" 
                                   onclick="return confirm('Yakin ingin menghapus?')" 
                                   class="px-4 py-2 bg-red-500 text-white rounded hover:bg-red-700 transition">
                                    Hapus
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <div class="mt-6 text-right">
                <h3 class="text-xl font-semibold">Total: Rp <span id="total-harga">0,00</span></h3>
            </div>

            <div class="flex justify-between mt-6">
                <a href="dashboard.php" class="p-4 bg-gray-500 text-white rounded hover:bg-gray-700 transition">Kembali Belanja</a>
                <button id="pay-button" class="px-6 py-3 bg-blue-500 text-white rounded-lg text-lg hover:bg-blue-700 transition">
                    Lanjut ke Checkout
                </button>
            </div>
        </div>
    <?php endif; ?>
</div>

<!-- Modal untuk input nama dan alamat -->
<div id="checkout-modal" class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 hidden">
    <div class="bg-white rounded-lg p-6 w-96">
        <h2 class="text-xl font-bold mb-4">Masukkan Detail Pembayaran</h2>
        <label for="nama" class="block mb-2">Nama:</label>
        <input type="text" id="nama" class="border border-gray-300 rounded w-full p-2 mb-4" placeholder="Masukkan nama Anda" required>
        
        <label for="alamat" class="block mb-2">Alamat:</label>
        <textarea id="alamat" class="border border-gray-300 rounded w-full p-2 mb-4" placeholder="Masukkan alamat Anda" required></textarea>
        
        <div class="flex justify-end">
            <button id="submit-checkout" class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-700">Checkout</button>
            <button id="close-modal" class="ml-2 px-4 py-2 bg-gray-300 text-black rounded hover:bg-gray-400">Batal</button>
        </div>
    </div>
</div>

<script>
document.querySelectorAll(".cart-checkbox").forEach(checkbox => {
    checkbox.addEventListener("change", updateTotal);
});

function updateTotal() {
    let total = 0;
    document.querySelectorAll(".cart-checkbox:checked").forEach(checkbox => {
        total += parseFloat(checkbox.dataset.harga) * parseInt(checkbox.dataset.quantity);
    });
    document.getElementById("total-harga").innerText = new Intl.NumberFormat('id-ID', { 
        style: 'currency', currency: 'IDR' 
    }).format(total);
}

document.getElementById("pay-button").addEventListener("click", function (event) {
    event.preventDefault();

    let selectedItems = [];
    document.querySelectorAll(".cart-checkbox:checked").forEach(checkbox => {
        selectedItems.push({
            produk_id: checkbox.dataset.produkId,
            nama: checkbox.dataset.nama,
            quantity: checkbox.dataset.quantity,
            harga: checkbox.dataset.harga,
            size: checkbox.dataset.size,
            gambar: checkbox.dataset.gambar
        });
    });

    if (selectedItems.length === 0) {
        alert("Pilih setidaknya satu produk untuk checkout.");
        return;
    }

    // Tampilkan modal untuk input nama dan alamat
    document.getElementById("checkout-modal").classList.remove("hidden");
});

document.getElementById("close-modal").addEventListener("click", function () {
    document.getElementById("checkout-modal").classList.add("hidden");
});

document.getElementById("submit-checkout").addEventListener("click", async function () {
    const nama = document.getElementById("nama").value;
    const alamat = document.getElementById("alamat").value;

    if (!nama || !alamat) {
        alert("Nama dan alamat harus diisi.");
        return;
    }

    let selectedItems = [];
    document.querySelectorAll(".cart-checkbox:checked").forEach(checkbox => {
        selectedItems.push({
            produk_id: checkbox.dataset.produkId,
            nama: checkbox.dataset.nama,
            quantity: checkbox.dataset.quantity,
            harga: checkbox.dataset.harga,
            size: checkbox.dataset.size,
            gambar: checkbox.dataset.gambar
        });
    });

    // Hitung total harga
    let totalHarga = 0;
    selectedItems.forEach(item => {
        totalHarga += parseFloat(item.harga) * parseInt(item.quantity);
    });

    try {
        const response = await fetch("../../controllers/payment.php", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({ cart: selectedItems, total_harga: totalHarga, nama: nama, alamat: alamat }),
        });

        const json = await response.json();
        if (json.snapToken) {
            window.snap.pay(json.snapToken);
        }
    } catch (error) {
        alert("Terjadi kesalahan saat memproses checkout.");
    }

    // Tutup modal setelah checkout
    document.getElementById("checkout-modal").classList.add("hidden");
});

document.getElementById("dropdownBtn").addEventListener("click", function () {
    let menu = document.getElementById("dropdownMenu");
    menu.classList.toggle("hidden");
});

// Menutup dropdown jika klik di luar
window.addEventListener("click", function (event) {
    let dropdownBtn = document.getElementById("dropdownBtn");
    let dropdownMenu = document.getElementById("dropdownMenu");

    if (!dropdownBtn.contains(event.target) && !dropdownMenu.contains(event.target)) {
        dropdownMenu.classList.add("hidden");
    }
});
</script>

</body>
</html>