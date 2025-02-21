<?php
session_start();
require '../config/db.php';

header('Content-Type: application/json');

// Tampilkan error untuk debugging (hapus di production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Cek apakah pengguna sudah login
if (!isset($_SESSION['user_id'])) {
    echo json_encode(["status" => "error", "message" => "Silakan login terlebih dahulu."]);
    exit();
}

$user_id = $_SESSION['user_id'];

// Debugging: Cek data yang dikirim dari JavaScript
if (!isset($_POST['produk_id']) || empty($_POST['produk_id'])) {
    echo json_encode([
        "status" => "error",
        "message" => "produk_id tidak dikirim atau kosong.",
        "debug" => $_POST
    ]);
    exit();
}

$produk_id = (int) $_POST['produk_id'];

// Debugging: Pastikan produk_id tidak 0 atau negatif
if ($produk_id <= 0) {
    echo json_encode(["status" => "error", "message" => "Produk tidak valid.", "produk_id" => $produk_id]);
    exit();
}

// Cek apakah produk ada di database
$stmt = $conn->prepare("SELECT id FROM produk WHERE id = ?");
$stmt->bind_param("i", $produk_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(["status" => "error", "message" => "Produk tidak ditemukan di database!"]);
    exit();
}

// Cek apakah produk sudah ada di wishlist
$stmt = $conn->prepare("SELECT id FROM wishlist WHERE user_id = ? AND produk_id = ?");
$stmt->bind_param("ii", $user_id, $produk_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    // Hapus dari wishlist jika sudah ada
    $stmt = $conn->prepare("DELETE FROM wishlist WHERE user_id = ? AND produk_id = ?");
    $stmt->bind_param("ii", $user_id, $produk_id);
    if ($stmt->execute()) {
        echo json_encode(["status" => "removed", "message" => "Produk dihapus dari wishlist."]);
    } else {
        echo json_encode(["status" => "error", "message" => "Gagal menghapus produk dari wishlist."]);
    }
} else {
    // Tambahkan ke wishlist jika belum ada
    $stmt = $conn->prepare("INSERT INTO wishlist (user_id, produk_id) VALUES (?, ?)");
    $stmt->bind_param("ii", $user_id, $produk_id);
    if ($stmt->execute()) {
        echo json_encode(["status" => "success", "message" => "Produk ditambahkan ke wishlist!"]);
    } else {
        echo json_encode(["status" => "error", "message" => "Gagal menambahkan produk ke wishlist."]);
    }
}

// Tutup koneksi
$stmt->close();
$conn->close();
?>
