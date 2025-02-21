<?php
include '../config/db.php';

if (isset($_POST['submit'])) {
    $nama_produk = $_POST['nama_produk'];
    $kategori    = $_POST['kategori'];
    $deskripsi   = $_POST['deskripsi'];

    // Proses Upload Gambar
    $target_dir = "../uploads/";
    $gambar_name = basename($_FILES["gambar"]["name"]);
    $target_file = $target_dir . $gambar_name;
    $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

    $allowed_types = array("jpg", "jpeg", "png", "gif");
    if (!in_array($imageFileType, $allowed_types)) {
        die("Format gambar hanya JPG, JPEG, PNG & GIF.");
    }

    if (move_uploaded_file($_FILES["gambar"]["tmp_name"], $target_file)) {
        // Simpan ke Database produk
        $sql_produk = "INSERT INTO produk (nama_produk, kategori, deskripsi, gambar)
                       VALUES ('$nama_produk', '$kategori', '$deskripsi', '$gambar_name')";

        if ($conn->query($sql_produk) === TRUE) {
            $produk_id = $conn->insert_id; // Ambil ID produk yang baru saja ditambahkan

            // Menyimpan stok berdasarkan ukuran
            $sizes = isset($_POST['size']) ? $_POST['size'] : [];
            $harga_per_size = isset($_POST['harga']) ? $_POST['harga'] : [];
            $stok_per_size = isset($_POST['stok']) ? $_POST['stok'] : [];

            foreach ($sizes as $size) {
                // Pastikan harga dan stok diisi jika ukuran dipilih
                if (!empty($harga_per_size[$size]) && !empty($stok_per_size[$size])) {
                    $harga = $harga_per_size[$size];
                    $stok = $stok_per_size[$size];

                    $sql_stok = "INSERT INTO stok_produk (produk_id, size, stok, harga)
                                  VALUES ('$produk_id', '$size', '$stok', '$harga')";
                    $conn->query($sql_stok);
                } else {
                    // Jika harga atau stok tidak diisi, tampilkan pesan kesalahan
                    echo "Harga dan stok harus diisi untuk ukuran $size.<br>";
                }
            }

            // Tampilkan alert sukses
            echo "<script>alert('Produk berhasil ditambahkan!'); window.location.href='../../ecommerce/views/admin/add_product.php';</script>";
        } else {
            echo "Error: " . $sql_produk . "<br>" . $conn->error;
        }
    } else {
        echo "Gagal mengupload gambar.";
    }
}
?>