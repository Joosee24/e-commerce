<?php
session_start();
require '../config/db.php';

// Pastikan user sudah login
if (!isset($_SESSION['user_id'])) {
    die("Silakan login untuk memberikan rating.");
}

$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $rating = (int) $_POST['rating'];
    $review = htmlspecialchars(trim($_POST['review'])); // Mencegah XSS

    if (empty($rating) || empty($review)) {
        echo "<script>alert('Rating dan ulasan tidak boleh kosong!'); window.location.href='dashboard.php';</script>";
        exit;
    }

    // Cek apakah user sudah memberikan rating sebelumnya
    $stmt = $conn->prepare("SELECT id FROM ratings WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $stmt = $conn->prepare("UPDATE ratings SET rating = ?, review = ? WHERE user_id = ?");
        $stmt->bind_param("isi", $rating, $review, $user_id);
        $stmt->execute();
        echo "<script>alert('Rating dan ulasan Anda telah diperbarui!'); window.location.href='../views/user/dashboard.php';</script>";
    } else {
        $stmt = $conn->prepare("INSERT INTO ratings (user_id, rating, review) VALUES (?, ?, ?)");
        $stmt->bind_param("iis", $user_id, $rating, $review);
        $stmt->execute();
        echo "<script>alert('Terima kasih atas rating dan ulasan Anda!'); window.location.href='../views/user/dashboard.php';</script>";
    }

    $stmt->close();
}
?>
