<?php
session_start();
require '../config/db.php'; // Koneksi ke database

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    // Validasi input tidak boleh kosong
    if (empty($username) || empty($email) || empty($password)) {
        $_SESSION['error'] = "Semua kolom harus diisi!";
        header("Location: ../views/register.php");
        exit();
    }

    // Cek apakah username atau email sudah digunakan
    $stmt = $conn->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
    $stmt->bind_param("ss", $username, $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $_SESSION['error'] = "Username atau email sudah digunakan!";
        header("Location: ../views/register.php");
        exit();
    }

    // Hash password sebelum disimpan ke database
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    $role = 'user'; // Role default adalah user

    // Simpan user ke database
    $stmt = $conn->prepare("INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $username, $email, $hashed_password, $role);

    if ($stmt->execute()) {
        $_SESSION['success'] = "Registrasi berhasil! Silakan login.";
        header("Location: ../views/login.php");
        exit();
    } else {
        $_SESSION['error'] = "Terjadi kesalahan saat registrasi!";
        header("Location: ../views/register.php");
        exit();
    }
}
?>
