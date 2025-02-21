<?php
session_start(); // Mulai sesi
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <title>Tambah Produk</title>
</head>
<body class="bg-gray-100">

<nav class="flex justify-between items-center  border b-2 bg-gray-100  p-4 shadow-md">
        <h1 class=" text-xl font-bold">Logo</h1>
        <div class="flex items-center space-x-4">
            <a href="dashboard.php" class=" hover:underline">Dashboard</a>
            <a href="products.php" class=" hover:underline">Manajemen Produk</a>
            <a href="add_product.php" class=" hover:underline">Tambah Produk</a>
            
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

<div class="max-w-4xl mx-auto mt-8 p-6 bg-white rounded-lg shadow-md">
    <h2 class="text-2xl font-semibold mb-4">Tambah Produk</h2>
    <form action="../../actions/add_product.php" method="POST" enctype="multipart/form-data">
        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700">Nama Produk:</label>
            <input type="text" name="nama_produk" required class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 p-2" placeholder="Masukkan nama produk">
        </div>

        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700">Kategori:</label>
            <select name="kategori" required class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 p-2">
                <option value="Baju">Baju</option>
                <option value="Jacket">Jacket</option>
                <option value="Hoodie">Hoodie</option>
            </select>
        </div>

        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700">Ukuran, Harga, dan Stok:</label>
            <div class="border border-gray-300 rounded-md overflow-hidden">
                <div class="max-h-40 overflow-y-auto p-2">
                    <div class="flex items-center mb-2">
                        <input type="checkbox" name="size[]" value="S" class="mr-2">
                        <label class="mr-2">S -</label>
                        <input type="number" name="harga[S]" placeholder="Harga S" class="border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 p-1 w-24 text-right" min="0">
                        <input type="number" name="stok[S]" placeholder="Stok S" class="border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 p-1 w-24 ml-2" min="0">
                    </div>
                    <div class="flex items-center mb-2">
                        <input type="checkbox" name="size[]" value="M" class="mr-2">
                        <label class="mr-2">M -</label>
                        <input type="number" name="harga[M]" placeholder="Harga M" class="border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 p-1 w-24" min="0">
                        <input type="number" name="stok[M]" placeholder="Stok M" class="border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 p-1 w-24 ml-2" min="0">
                    </div>
                    <div class="flex items-center mb-2">
                        <input type="checkbox" name="size[]" value="L" class="mr-2">
                        <label class="mr-2">L -</label>
                        <input type="number" name="harga[L]" placeholder="Harga L" class="border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 p-1 w-24" min="0">
                        <input type="number" name="stok[L]" placeholder="Stok L" class="border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 p-1 w-24 ml-2" min="0">
                    </div>
                    <div class="flex items-center mb-2">
                        <input type="checkbox" name="size[]" value="XL" class="mr-2">
                        <label class="mr-2">XL -</label>
                        <input type="number" name="harga[XL]" placeholder="Harga XL" class="border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 p-1 w-24" min="0">
                        <input type="number" name="stok[XL]" placeholder="Stok XL" class="border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 p-1 w-24 ml-2" min="0">
                    </div>
                    <div class="flex items-center mb-2">
                        <input type="checkbox" name="size[]" value="XXL" class="mr-2">
                        <label class="mr-2">XXL -</label>
                        <input type="number" name="harga[XXL]" placeholder="Harga XXL" class="border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 p-1 w-24" min="0">
                        <input type="number" name="stok[XXL]" placeholder="Stok XXL" class="border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 p-1 w-24 ml-2" min="0">
                    </div>
                </div>
            </div>
        </div>

        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700">Deskripsi:</label>
            <textarea name="deskripsi" required class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 p-2" placeholder="Masukkan deskripsi produk"></textarea>
        </div>

        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700">Gambar Produk:</label>
            <input type="file" name="gambar" accept="image/*" required class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 p-2">
        </div>

        <button type="submit" name="submit" class="w-full bg-blue-600 text-white font-semibold py-2 rounded-md hover:bg-blue-700">Tambah Produk</button>
    </form>
</div>

</body>
</html>

