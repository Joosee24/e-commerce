<?php
session_start();
require '../config/db.php';

// Pastikan user sudah login
if (!isset($_SESSION['user_id'])) {
    die("Silakan login terlebih dahulu.");
}

$user_id = $_SESSION['user_id'];
$produk_id = $_GET['produk_id'] ?? 0;

if (!$produk_id) {
    die("Produk tidak valid.");
}

// Hapus produk dari wishlist
$stmt = $conn->prepare("DELETE FROM wishlist WHERE user_id = ? AND produk_id = ?");
$stmt->bind_param("ii", $user_id, $produk_id);
$stmt->execute();

echo "<script>alert('Produk dihapus dari wishlist!'); window.location.href='../../ecommerce/views/user/wishlist.php';</script>";

$stmt->close();
$conn->close();
?>
