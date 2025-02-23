<?php
session_start();
require '../config/db.php';
require '../midtrans-php/Midtrans.php';

use Midtrans\Snap;
use Midtrans\Config;
use Midtrans\Transaction;

// Konfigurasi Midtrans
Config::$serverKey = 'SB-Mid-server-52biJRwxf53J52PaZzqO32wU'; // Ganti dengan server key Anda
Config::$isProduction = false;
Config::$isSanitized = true;
Config::$is3ds = true;

// Buat file log untuk debugging
$log_file = 'debug_log.txt';
file_put_contents($log_file, "=== DEBUGGING CHECKOUT ===\n", FILE_APPEND);

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'POST' && empty($_POST['order_id'])) {
    // ======= [1] PROSES TRANSAKSI MIDTRANS =======
    $inputData = json_decode(file_get_contents('php://input'), true);
    
    file_put_contents($log_file, "Input Data:\n" . print_r($inputData, true) . "\n", FILE_APPEND);

    if (!$inputData || !isset($inputData['total_harga'], $inputData['cart'], $inputData['nama'], $inputData['alamat'])) {
        echo json_encode(['error' => 'Invalid request data']);
        exit();
    }

    $user_id = $_SESSION['user_id'] ?? null;
    if (!$user_id) {
        echo json_encode(['error' => 'User not authenticated']);
        exit();
    }

    $totalHarga = $inputData['total_harga'];
    $keranjang = $inputData['cart'];
    $nama = $inputData['nama'];
    $alamat = $inputData['alamat'];
    $order_id = 'ORDER-' . time();

    // Debugging transaksi
    file_put_contents($log_file, "Order ID: $order_id\nTotal Harga: $totalHarga\n", FILE_APPEND);

    // Detail transaksi
    $transactionDetails = [
        'order_id' => $order_id,
        'gross_amount' => $totalHarga
    ];

    // Detail item
    $itemDetails = array_map(function($item) {
        return [
            'id' => $item['produk_id'],
            'price' => $item['harga'],
            'quantity' => $item['quantity'],
            'name' => $item['nama']
        ];
    }, $keranjang);

    // Simpan data cart ke session
    $_SESSION['cart'] = $keranjang;
    $_SESSION['order_id'] = $order_id;

    // Buat transaksi Midtrans
    $transaction = [
        'transaction_details' => $transactionDetails,
        'item_details' => $itemDetails
    ];

    try {
        $snapToken = Snap::getSnapToken($transaction);
        echo json_encode(['snapToken' => $snapToken, 'order_id' => $order_id]);
    } catch (Exception $e) {
        file_put_contents($log_file, "Midtrans Error: " . $e->getMessage() . "\n", FILE_APPEND);
        echo json_encode(['error' => 'Failed to create Midtrans token']);
    }
} elseif ($method === 'POST' && isset($_POST['order_id'])) {
    // ======= [2] CEK STATUS TRANSAKSI MIDTRANS =======
    $order_id = $_POST['order_id'];

    try {
        $status = Transaction::status($order_id);
        file_put_contents($log_file, "Midtrans Status:\n" . print_r($status, true) . "\n", FILE_APPEND);

        $transaction_status = $status->transaction_status;

        if ($transaction_status === 'settlement' || $transaction_status === 'capture') {
            saveToDatabase($order_id, 'processed');
        } elseif ($transaction_status === 'pending') {
            saveToDatabase($order_id, 'pending');
        } else {
            saveToDatabase($order_id, 'failed');
        }

        echo json_encode(['success' => true, 'status' => $transaction_status]);
    } catch (Exception $e) {
        file_put_contents($log_file, "Midtrans Status Error: " . $e->getMessage() . "\n", FILE_APPEND);
        echo json_encode(['success' => false, 'message' => 'Failed to check Midtrans status']);
    }
} elseif ($method === 'POST') {
    // ======= [3] CALLBACK DARI MIDTRANS =======
    $callbackData = json_decode(file_get_contents('php://input'), true);
    
    file_put_contents($log_file, "Midtrans Callback Masuk!\n" . print_r($callbackData, true) . "\n", FILE_APPEND);

    if (!isset($callbackData['order_id'], $callbackData['transaction_status'])) {
        echo json_encode(['success' => false, 'message' => 'Invalid callback data']);
        return;
    }

    $order_id = $callbackData['order_id'];
    $transaction_status = $callbackData['transaction_status'];

    if ($transaction_status === 'settlement' || $transaction_status === 'capture') {
        saveToDatabase($order_id, 'processed');
    } elseif ($transaction_status === 'pending') {
        saveToDatabase($order_id, 'pending');
    } elseif ($transaction_status === 'expire' || $transaction_status === 'cancel') {
        saveToDatabase($order_id, 'failed');
    }

    echo json_encode(['success' => true]);
}

// ======= [4] FUNGSI MENYIMPAN DATA KE DATABASE =======
function saveToDatabase($order_id, $status) {
    global $conn, $log_file;

    $user_id = $_SESSION['user_id'] ?? null;
    if (!$user_id) {
        file_put_contents($log_file, "ERROR: User ID tidak ditemukan dalam session!\n", FILE_APPEND);
        return;
    }

    $cart = $_SESSION['cart'] ?? [];
    if (empty($cart)) {
        file_put_contents($log_file, "ERROR: Cart tidak ditemukan dalam session!\n", FILE_APPEND);
        return;
    }

    // Hitung total harga dari cart
    $total_price = array_sum(array_map(fn($item) => $item['harga'] * $item['quantity'], $cart));

    // Simpan ke tabel checkout
    $stmt = $conn->prepare("INSERT INTO checkout (order_id, user_id, total_price, status) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("sids", $order_id, $user_id, $total_price, $status);
    if (!$stmt->execute()) {
        file_put_contents($log_file, "ERROR: Gagal menyimpan checkout - " . $stmt->error . "\n", FILE_APPEND);
        return;
    }
    $checkoutId = $stmt->insert_id;

    // Simpan checkout_items
    $stmt = $conn->prepare("INSERT INTO checkout_items (checkout_id, produk_id, size, quantity, price) VALUES (?, ?, ?, ?, ?)");
    foreach ($cart as $item) {
        $stmt->bind_param("iisid", $checkoutId, $item['produk_id'], $item['size'], $item['quantity'], $item['harga']);
        if (!$stmt->execute()) {
            file_put_contents($log_file, "ERROR: Gagal menyimpan checkout_items - " . $stmt->error . "\n", FILE_APPEND);
        }
    }

    file_put_contents($log_file, "Data Berhasil Disimpan ke Database!\n", FILE_APPEND);
}
?>
