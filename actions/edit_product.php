<?php
require '../middleware/auth.php';
require '../config/db.php';
if (!isAdmin()) {
    header("Location: ../user/dashboard.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = intval($_POST['id']);
    $nama_produk = $_POST['nama_produk'];
    $kategori = $_POST['kategori'];
    $deskripsi = $_POST['deskripsi'];

    // Proses Upload Gambar jika ada
    if (!empty($_FILES['gambar']['name'])) {
        $target_dir = "../../uploads/";
        $gambar_name = basename($_FILES["gambar"]["name"]);
        $target_file = $target_dir . $gambar_name;
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

        $allowed_types = ["jpg", "jpeg", "png", "gif"];
        if (!in_array($imageFileType, $allowed_types)) {
            die("Format gambar hanya JPG, JPEG, PNG & GIF.");
        }

        if (move_uploaded_file($_FILES["gambar"]["tmp_name"], $target_file)) {
            // Update produk dengan gambar baru
            $stmt = $conn->prepare("UPDATE produk SET nama_produk = ?, kategori = ?, deskripsi = ?, gambar = ? WHERE id = ?");
            $stmt->bind_param("ssssi", $nama_produk, $kategori, $deskripsi, $gambar_name, $id);
        } else {
            die("Gagal mengupload gambar.");
        }
    } else {
        // Update produk tanpa mengganti gambar
        $stmt = $conn->prepare("UPDATE produk SET nama_produk = ?, kategori = ?, deskripsi = ? WHERE id = ?");
        $stmt->bind_param("sssi", $nama_produk, $kategori, $deskripsi, $id);
    }

    if ($stmt->execute()) {
        // Hapus stok lama
        $conn->query("DELETE FROM stok_produk WHERE produk_id = $id");
        
        // Tambahkan stok baru
        $sizes = isset($_POST['stok']) ? $_POST['stok'] : [];
        $harga_per_size = isset($_POST['harga']) ? $_POST['harga'] : [];

        foreach ($sizes as $size => $stok) {
            $harga = $harga_per_size[$size] ?? 0;
            if (!empty($stok) && !empty($harga)) {
                $stmt_stok = $conn->prepare("INSERT INTO stok_produk (produk_id, size, stok, harga) VALUES (?, ?, ?, ?)");
                $stmt_stok->bind_param("isii", $id, $size, $stok, $harga);
                $stmt_stok->execute();
            }
        }
        
       
        $success = true;
    } else {
        echo "Error: " . $conn->error;
    }
}
?>

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
        <?php if ($success): ?>
            <script>
                alert("Produk berhasil diperbarui!");
                window.location.href = "../../ecommerce/views/admin/edit_product.php?id=<?php echo $id; ?>";
            </script>
        <?php endif; ?>
    </div>
</body>
</html>

