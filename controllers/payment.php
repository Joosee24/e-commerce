<?php
session_start();
require '../config/db.php'; // Pastikan file ini menginisialisasi $conn
require '../midtrans-php/Midtrans.php';

use Midtrans\Snap;
use Midtrans\Config;
use Midtrans\Transaction;

// Konfigurasi Midtrans
Config::$serverKey = 'SB-Mid-server-52biJRwxf53J52PaZzqO32wU'; // Ganti dengan server key Anda
Config::$isProduction = false;

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'POST') {
    // ======= [1] PEMBUATAN TRANSAKSI MIDTRANS =======
    $inputData = json_decode(file_get_contents('php://input'), true);

    if (!$inputData || !isset($inputData['total_harga']) || !isset($inputData['cart']) || !isset($inputData['nama']) || !isset($inputData['alamat'])) {
        echo json_encode(['error' => 'Invalid cart data']);
        exit();
    }

    $totalHarga = $inputData['total_harga'];
    $keranjang = $inputData['cart'];
    $nama = $inputData['nama'];
    $alamat = $inputData['alamat'];
    $order_id = 'ORDER-' . time();

    // Detail transaksi
    $transactionDetails = [
        'order_id' => $order_id,
        'gross_amount' => $totalHarga,
        'customer_details' => [
            'first_name' => $nama,
            'address' => $alamat,
        ],
    ];

    // Detail item
    $itemDetails = [];
    foreach ($keranjang as $item) {
        $itemDetails[] = [
            'id' => $item['produk_id'],
            'price' => $item['harga'],
            'quantity' => $item['quantity'],
            'name' => $item['nama'],
            'size' => $item['size']
        ];
    }

    // Simpan data cart ke session
    $_SESSION['cart'] = $keranjang;
    $_SESSION['order_id'] = $order_id;

    // Buat transaksi Midtrans
    $transaction = [
        'transaction_details' => $transactionDetails,
        'item_details' => $itemDetails,
    ];

    try {
        $snapToken = Snap::getSnapToken($transaction);
        echo json_encode(['snapToken' => $snapToken, 'order_id' => $order_id]);
    } catch (Exception $e) {
        error_log("Midtrans Error: " . $e->getMessage());
        echo json_encode(['error' => 'Failed to create Midtrans token']);
    }
} elseif ($method === 'POST' && isset($_POST['order_id'])) {
    // ======= [2] CEK STATUS TRANSAKSI MIDTRANS =======
    $order_id = $_POST['order_id'];

    try {
        $status = Transaction::status($order_id);

        if ($status->transaction_status === 'settlement' || $status->transaction_status === 'capture') {
            // Simpan ke tabel checkout
            $stmt = $conn->prepare("INSERT INTO checkout (user_id, total_price, status) VALUES (?, ?, ?)");
            $checkoutStatus = 'processed';
            $stmt->bind_param("ids", $_SESSION['user_id'], $status->gross_amount, $checkoutStatus);
            if (!$stmt->execute()) {
                error_log("Execute failed for checkout: " . $stmt->error);
                echo json_encode(['success' => false, 'message' => 'Failed to save checkout']);
                exit();
            }
            $checkoutId = $stmt->insert_id;

            // Simpan checkout_items dari session cart
            foreach ($_SESSION['cart'] as $item) {
                $stmt = $conn->prepare("INSERT INTO checkout_items (checkout_id, produk_id, size, quantity, price) VALUES (?, ?, ?, ?, ?)");
                $stmt->bind_param("iisid", $checkoutId, $item['produk_id'], $item['size'], $item['quantity'], $item['harga']);
                if (!$stmt->execute()) {
                    error_log("Execute failed for checkout_items: " . $stmt->error);
                }
            }

            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Payment not successful']);
        }
    } catch (Exception $e) {
        error_log("Midtrans Status Error: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Failed to check Midtrans status']);
    }
} else {
    // ======= [3] CALLBACK DARI MIDTRANS =======
    // Implementasi callback jika diperlukan
}