<?php
require '../config/db.php';

// Ambil data riwayat checkout berdasarkan user_id
$query = $conn->prepare("
    SELECT c.id AS checkout_id, c.total_price, c.status, c.created_at, cd.* 
    FROM checkout c
    JOIN checkout_details cd ON c.id = cd.checkout_id
    WHERE c.user_id = ?
    ORDER BY c.created_at DESC
");
$query->bind_param("i", $user_id);
$query->execute();
$result = $query->get_result();

$riwayat = [];
while ($row = $result->fetch_assoc()) {
    $riwayat[] = $row;
}
?>

<h1>Riwayat Checkout</h1>

<?php if (!empty($riwayat)): ?>
    <?php $currentCheckoutId = null; ?>
    <?php foreach ($riwayat as $item): ?>
        <?php if ($currentCheckoutId !== $item['checkout_id']): ?>
            <?php if ($currentCheckoutId !== null): ?>
                </ul>
                <hr>
            <?php endif; ?>
            <div>
                <p>ID Checkout: <?= $item['checkout_id'] ?></p>
                <p>Total Harga: Rp <?= number_format($item['total_price'], 0, ',', '.') ?></p>
                <p>Status: <?= ucfirst($item['status']) ?></p>
                <p>Tanggal: <?= $item['created_at'] ?></p>

                <h3>Detail Produk:</h3>
                <ul>
            <?php $currentCheckoutId = $item['checkout_id']; ?>
        <?php endif; ?>
        <li>
            <?= htmlspecialchars($item['nama_produk']) ?> - 
            Size: <?= $item['size'] ?> - 
            <?= $item['quantity'] ?> x Rp <?= number_format($item['price'], 0, ',', '.') ?> = 
            Rp <?= number_format($item['quantity'] * $item['price'], 0, ',', '.') ?>
        </li>
    <?php endforeach; ?>
    </ul>
<?php else: ?>
    <p>Tidak ada riwayat checkout.</p>
<?php endif; ?>

<script>
    window.snap.pay(data.snapToken, {
    onSuccess: function () {
        alert('Pembayaran berhasil!');
        window.location.href = 'riwayat.php'; // Redirect ke halaman riwayat
    },
    onPending: function () {
        alert('Menunggu pembayaran.');
    },
    onError: function () {
        alert('Pembayaran gagal.');
    }
});
</script>