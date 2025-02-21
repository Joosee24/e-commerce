<?php
session_start();
require '../config/db.php'; // Koneksi ke database
require '../midtrans-php/Midtrans.php'; // Konfigurasi Midtrans API

use Midtrans\Config;
use Midtrans\Transaction;

// Konfigurasi Midtrans
Config::$serverKey = 'SB-Mid-server-52biJRwxf53J52PaZzqO32wU';
Config::$isProduction = false; // Ubah ke true jika di produksi

// Validasi order_id
if (!isset($_GET['order_id']) || empty($_GET['order_id'])) {
    error_log("ERROR: order_id tidak ditemukan");
    echo json_encode(['error' => 'Order ID tidak valid']);
    exit();
}

$order_id = $_GET['order_id'];

// Cek session user_id
if (!isset($_SESSION['user_id'])) {
    error_log("ERROR: User ID tidak ditemukan dalam session");
    echo json_encode(['error' => 'User tidak terautentikasi']);
    exit();
}

$userId = $_SESSION['user_id'];

// Cek koneksi database
if ($conn->connect_error) {
    error_log("Koneksi database gagal: " . $conn->connect_error);
    exit();
}

// Ambil status pembayaran dari Midtrans
$status = Transaction::status($order_id);
error_log("Midtrans Response: " . print_r($status, true));

// Cek apakah pembayaran sukses
if ($status->transaction_status == 'settlement') {
    $totalHarga = $status->gross_amount; 
    $statusCheckout = 'pending'; 

    // Mulai transaksi database
    $conn->begin_transaction();
    try {
        // Simpan ke tabel checkout
        $stmt = $conn->prepare("INSERT INTO checkout (user_id, total_price, status) VALUES (?, ?, ?)");
        if (!$stmt) {
            throw new Exception("Prepare failed: " . $conn->error);
        }
        $stmt->bind_param("ids", $userId, $totalHarga, $statusCheckout);
        if (!$stmt->execute()) {
            throw new Exception("Execute failed: " . $stmt->error);
        }
        $checkoutId = $stmt->insert_id;
        error_log("Checkout berhasil dengan ID: " . $checkoutId);

        // Simpan ke tabel checkout_items
        foreach ($status->item_details as $item) {
            $stmt = $conn->prepare("INSERT INTO checkout_items (checkout_id, produk_id, quantity, price) VALUES (?, ?, ?, ?)");
            if (!$stmt) {
                throw new Exception("Prepare failed for checkout_items: " . $conn->error);
            }
            $stmt->bind_param("iiid", $checkoutId, $item->id, $item->quantity, $item->price);
            if (!$stmt->execute()) {
                throw new Exception("Execute failed for checkout_items: " . $stmt->error);
            }
        }

        // Commit transaksi
        $conn->commit();
        echo json_encode(['success' => true]);
    } catch (Exception $e) {
        // Rollback jika ada error
        $conn->rollback();
        error_log("ERROR: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Database error']);
    }
} else {
    error_log("Payment status: " . $status->transaction_status);
    echo json_encode(['success' => false, 'message' => 'Payment not successful']);
}
?>
