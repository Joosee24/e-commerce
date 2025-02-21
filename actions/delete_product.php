<?php
include '../config/db.php';

if (isset($_GET['id'])) {
    $produk_id = $_GET['id'];

    // Ambil nama file gambar sebelum menghapus produk
    $sql_gambar = "SELECT gambar FROM produk WHERE id = '$produk_id'";
    $result_gambar = $conn->query($sql_gambar);
    
    if ($result_gambar->num_rows > 0) {
        $row = $result_gambar->fetch_assoc();
        $gambar_path = "../uploads/" . $row['gambar'];
        
        // Hapus file gambar jika ada
        if (file_exists($gambar_path)) {
            unlink($gambar_path);
        }
    }

    // Hapus item terkait di tabel cart terlebih dahulu
    $sql_delete_cart = "DELETE FROM cart WHERE produk_id = '$produk_id'";
    $conn->query($sql_delete_cart);

    // Hapus stok produk terlebih dahulu (karena ada foreign key dependency)
    $sql_delete_stok = "DELETE FROM stok_produk WHERE produk_id = '$produk_id'";
    $conn->query($sql_delete_stok);

    // Hapus produk dari tabel produk
    $sql_delete_produk = "DELETE FROM produk WHERE id = '$produk_id'";
    if ($conn->query($sql_delete_produk) === TRUE) {
        echo "Produk berhasil dihapus! <a href='../views/admin/dashboard.php'>Kembali</a>";
    } else {
        echo "Error: " . $conn->error;
    }
} else {
    echo "ID produk tidak ditemukan.";
}
?>
