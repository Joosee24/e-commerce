<?php
session_start();
require '../config/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$produk_id = $_GET['produk_id'] ?? null;
$size = $_GET['size'] ?? null;

// Validate the input data
if (!$produk_id || !$size) {
    echo "Data tidak valid!";
    exit();
}

// Query to delete the item from the cart
$deleteQuery = $conn->prepare("DELETE FROM cart WHERE user_id = ? AND produk_id = ? AND size = ?");
$deleteQuery->bind_param("iis", $user_id, $produk_id, $size);

if ($deleteQuery->execute()) {
    header('Location: ../views/user/cart.php');  // Redirect back to cart after successful deletion
    exit();
} else {
    echo "Gagal menghapus item!";
    exit();
}
?>
