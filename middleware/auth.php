<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    $_SESSION['error'] = "Anda harus login terlebih dahulu!";
    header("Location: ../views/login.php");
    exit();
}

// Middleware khusus admin
function isAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

// Middleware khusus user
function isUser() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'user';
}
?>
