<?php
require '../config/db.php'; // Koneksi database
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $produk_id = $_POST['produk_id'];
    $user_id = $_SESSION['user_id']; // Pastikan user login
    $rating = $_POST['rating'];
    $review = $_POST['review'];

    // Debugging: Cek apakah data benar-benar dikirim
    echo "Produk ID: " . $produk_id . "<br>";
    echo "User ID: " . $user_id . "<br>";
    echo "Rating: " . $rating . "<br>";
    echo "Review: " . $review . "<br>";

    // Cek apakah produk ID ada di database sebelum insert
    $check = $conn->prepare("SELECT id FROM produk WHERE id = ?");
    $check->bind_param("i", $produk_id);
    $check->execute();
    $result = $check->get_result();

    if ($result->num_rows == 0) {
        die("Error: Produk dengan ID tersebut tidak ditemukan!");
    }

    // Jika valid, lanjutkan insert
    $stmt = $conn->prepare("INSERT INTO reviews (produk_id, user_id, rating, review) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("iiis", $produk_id, $user_id, $rating, $review);

    if ($stmt->execute()) {
        echo "Ulasan berhasil dikirim!";
    } else {
        echo "Gagal menyimpan ulasan.";
    }
}
?>
