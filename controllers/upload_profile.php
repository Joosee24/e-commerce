<?php
session_start();
require '../config/db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_FILES["profile_picture"])) {
  $user_id = $_SESSION['user_id']; // Pastikan user sudah login

  $target_dir = "../uploads";
  $file_name = basename($_FILES["profile_picture"]["name"]);
  $target_file = $target_dir . time() . "_" . $file_name;
  $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

  // Validasi file gambar
  $allowed_types = ["jpg", "jpeg", "png", "gif"];
  if (!in_array($imageFileType, $allowed_types)) {
      die("Format file tidak diizinkan.");
  }

  // Pindahkan file yang diupload
  if (move_uploaded_file($_FILES["profile_picture"]["tmp_name"], $target_file)) {
      // Simpan URL gambar ke database
      $stmt = $conn->prepare("UPDATE users SET profile_picture = ? WHERE id = ?");
      $stmt->execute([$target_file, $user_id]);

      echo "Foto profil berhasil diupload.";
  } else {
      echo "Gagal mengupload file.";
  }
}
?>
