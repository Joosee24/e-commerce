<?php
session_start();
require '../config/db.php'; // Hubungkan ke database

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    // Ambil data user dari database
    $stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    // Debugging: Cek apakah user ditemukan
    if (!$user) {
        die("User tidak ditemukan!");
    }

    // Debugging: Cek password hash di database
    var_dump($user['password']); // Lihat hash password yang tersimpan
    var_dump(password_verify($password, $user['password'])); // Apakah cocok?

    // Cek apakah user ditemukan dan password cocok
    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'];

        // Redirect berdasarkan role
        if ($user['role'] == 'admin') {
            header("Location: ../views/admin/dashboard.php");
        } else {
            header("Location: ../views/user/dashboard.php");
        }
        exit();
    } else {
        $_SESSION['error'] = "Username atau password salah!";
        header("Location: ../views/login.php");
        exit();
    }
}
?>
