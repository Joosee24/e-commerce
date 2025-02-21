<?php
require '../../middleware/auth.php';
require '../../config/db.php';
if (!isAdmin()) {
    header("Location: ../user/dashboard.php");
    exit();
}

// Ambil data produk berdasarkan ID
if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $stmt = $conn->prepare("SELECT * FROM produk WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $produk = $result->fetch_assoc();

    // Ambil stok produk
    $stmt = $conn->prepare("SELECT * FROM stok_produk WHERE produk_id = ? ORDER BY size ASC");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stok_result = $stmt->get_result();
    $stok_data = [];
    while ($row = $stok_result->fetch_assoc()) {
        $stok_data[$row['size']] = [
            'stok' => $row['stok'],
            'harga' => $row['harga']
        ];
    }
} else {
    header("Location: products.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <title>Edit Produk</title>
</head>
<body class="bg-gray-100">
<div class="max-w-4xl mx-auto mt-6 p-6 bg-white rounded-lg shadow-md">
    <h2 class="text-2xl font-semibold mb-4">Edit Produk</h2>
    <form action="../../actions/edit_product.php" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="id" value="<?php echo $produk['id']; ?>">

        <label class="block">Nama Produk</label>
        <input type="text" name="nama_produk" value="<?php echo $produk['nama_produk']; ?>" required class="border p-2 w-full mb-3">

        <label class="block">Kategori</label>
        <input type="text" name="kategori" value="<?php echo $produk['kategori']; ?>" required class="border p-2 w-full mb-3">

        <label class="block">Deskripsi</label>
        <textarea name="deskripsi" required class="border p-2 w-full mb-3"><?php echo $produk['deskripsi']; ?></textarea>

        <label class="block">Gambar Produk</label>
        <img src="../../uploads/<?php echo $produk['gambar']; ?>" class="w-32 h-32 object-cover mb-2">
        <input type="file" name="gambar" class="border p-2 w-full mb-3">
        
        <label class="block">Stok & Harga per Ukuran</label>
        <?php foreach (["S", "M", "L", "XL", "XXL"] as $size): ?>
            <div class="flex space-x-2 mb-2">
                <span class="w-12 font-semibold"><?php echo $size; ?>:</span>
                <input type="number" name="stok[<?php echo $size; ?>]" placeholder="Stok" value="<?php echo $stok_data[$size]['stok'] ?? ''; ?>" class="border p-2 w-24">
                <input type="number" name="harga[<?php echo $size; ?>]" placeholder="Harga" value="<?php echo $stok_data[$size]['harga'] ?? ''; ?>" class="border p-2 w-32">
            </div>
        <?php endforeach; ?>

        <button type="submit" class="bg-blue-600 text-white px-4 py-2 mt-4">Simpan Perubahan</button>
    </form>
</div>
</body>
</html>
