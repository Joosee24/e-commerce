<?php
session_start(); // Memulai sesi
session_destroy(); // Menghancurkan sesi

// Pastikan tidak ada output sebelum header
header("Location: ../views/login.php"); // Arahkan ke login.php
exit(); // Menghentikan eksekusi script
?>