<?php
session_start();
require '../config/db.php';

// Cek jika user sudah login
if (!isset($_SESSION['user_id'])) {
    echo json_encode(["success" => false, "message" => "Anda harus login terlebih dahulu"]);
    exit();
}

$user_id = $_SESSION['user_id'];
$produk_id = $_POST['produk_id'] ?? null;
$size = $_POST['size'] ?? null;
$quantity = isset($_POST['quantity']) ? (int) $_POST['quantity'] : 1;
$price = isset($_POST['price']) ? (float) $_POST['price'] : 0;

// Validasi data awal
if (!$produk_id || !$size || $quantity <= 0 || $price <= 0) {
    echo json_encode(["success" => false, "message" => "Data tidak valid"]);
    exit();
}

// Ambil data produk dari database berdasarkan produk_id
$selectProductQuery = $conn->prepare("SELECT nama_produk, gambar FROM produk WHERE id = ?");
$selectProductQuery->bind_param("i", $produk_id);
$selectProductQuery->execute();
$productResult = $selectProductQuery->get_result();

// Jika produk ditemukan, ambil nama dan gambar
if ($productResult->num_rows > 0) {
    $product = $productResult->fetch_assoc();
    $name = $product['nama_produk']; // Ambil nama produk dari database
    $image = $product['gambar']; // Ambil gambar produk dari database
} else {
    echo json_encode(["success" => false, "message" => "Produk tidak ditemukan"]);
    exit();
}

// Cek apakah produk sudah ada di keranjang user
$checkQuery = $conn->prepare("SELECT quantity FROM cart WHERE user_id = ? AND produk_id = ? AND size = ?");
$checkQuery->bind_param("iis", $user_id, $produk_id, $size);
$checkQuery->execute();
$result = $checkQuery->get_result();

if ($result->num_rows > 0) {
    // Jika produk sudah ada di keranjang, update quantity
    $row = $result->fetch_assoc();
    $newQuantity = $row['quantity'] + $quantity;

    $updateQuery = $conn->prepare("UPDATE cart SET quantity = ?, image = ?, name = ? WHERE user_id = ? AND produk_id = ? AND size = ?");
    $updateQuery->bind_param("isssis", $newQuantity, $image, $name, $user_id, $produk_id, $size);
    if ($updateQuery->execute()) {
        echo json_encode(["success" => true, "message" => "Keranjang diperbarui!"]);
    } else {
        echo json_encode(["success" => false, "message" => "Gagal memperbarui keranjang", "error" => $updateQuery->error]);
    }
} else {
    // Jika belum ada, tambahkan produk baru ke keranjang
    $insertQuery = $conn->prepare("INSERT INTO cart (user_id, produk_id, size, quantity, price, image, name) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $insertQuery->bind_param("iisidss", $user_id, $produk_id, $size, $quantity, $price, $image, $name);
    if ($insertQuery->execute()) {
        echo json_encode(["success" => true, "message" => "Produk ditambahkan ke keranjang!"]);
    } else {
        echo json_encode(["success" => false, "message" => "Gagal menambahkan produk ke keranjang", "error" => $insertQuery->error]);
    }
}
?>
