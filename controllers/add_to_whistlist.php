<?php
session_start();
require '../config/db.php';

// Cek apakah pengguna sudah login
if (!isset($_SESSION['user_id'])) {
    echo json_encode(["status" => "error", "message" => "Silakan login terlebih dahulu."]);
    exit();
}

$user_id = $_SESSION['user_id'];
$produk_id = isset($_GET['produk_id']) ? (int)$_GET['produk_id'] : 0;

// Validasi ID produk
if ($produk_id <= 0) {
    echo json_encode(["status" => "error", "message" => "Produk tidak valid."]);
    exit();
}

// Cek apakah produk ada di tabel produk
$stmt = $conn->prepare("SELECT id FROM produk WHERE id = ?");
$stmt->bind_param("i", $produk_id);
if (!$stmt->execute()) {
    echo json_encode(["status" => "error", "message" => "Error executing query: " . $stmt->error]);
    exit();
}
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(["status" => "error", "message" => "Produk tidak ditemukan!"]);
    exit();
}

// Cek apakah produk sudah ada di wishlist
$stmt = $conn->prepare("SELECT id FROM wishlist WHERE user_id = ? AND produk_id = ?");
$stmt->bind_param("ii", $user_id, $produk_id);
if (!$stmt->execute()) {
    echo json_encode(["status" => "error", "message" => "Error executing query: " . $stmt->error]);
    exit();
}
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    echo json_encode(["status" => "error", "message" => "Produk sudah ada di wishlist!"]);
} else {
    // Tambahkan ke wishlist
    $stmt = $conn->prepare("INSERT INTO wishlist (user_id, produk_id) VALUES (?, ?)");
    $stmt->bind_param("ii", $user_id, $produk_id);
    if (!$stmt->execute()) {
        echo json_encode(["status" => "error", "message" => "Error executing query: " . $stmt->error]);
        exit();
    }
    echo json_encode(["status" => "success", "message" => "Produk berhasil ditambahkan ke wishlist!"]);
}

// Tutup statement dan koneksi
$stmt->close();
$conn->close();
?>