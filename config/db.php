<?php
$host = "localhost";  // Server database (default: localhost)
$user = "root";       // Username MySQL (default: root di Laragon)
$pass = "";           // Password MySQL (kosong di Laragon)
$dbname = "ecommerce_db"; // Nama database yang sudah dibuat

// Buat koneksi
$conn = new mysqli($host, $user, $pass, $dbname);

// Periksa koneksi
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}
?>
