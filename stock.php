
<?php
require_once 'config.php';
require_once 'auth.php';
cekLogin();

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $produkId = (int) ($_POST['produk_id'] ?? 0);
    $jenis = $_POST['jenis'] ?? '';
    $jumlah = filter_var(
        $_POST['jumlah'] ?? null,
        FILTER_VALIDATE_INT
    );
    $catatan = trim($_POST['catatan'] ?? '');

    if (
        $produkId < 1 ||
        !in_array($jenis, ['IN', 'OUT'], true) ||
        $jumlah === false ||
        $jumlah < 1
    ) {
        $error = 'Pilih produk dan masukkan jumlah minimal 1.';
    } else {
        try {
            $pdo->beginTransaction();

            $stmt = $pdo->prepare(
                'SELECT stock FROM products WHERE id = ? FOR UPDATE'
            );
            $stmt->execute([$produkId]);
            $produk = $stmt->fetch();

            if (!$produk) {
                throw new Exception('Produk tidak ditemukan.');
            }

            if ($jenis === 'OUT' && $jumlah > $produk['stock']) {
                throw new Exception(
                    'Barang keluar melebihi stok yang tersedia.'
                );
            }

            if ($jenis === 'IN') {
                $sql = 'UPDATE products SET stock = stock + ? WHERE id = ?';
            } else {
                $sql = 'UPDATE products SET stock = stock - ? WHERE id = ?';
            }

            $stmt = $pdo->prepare($sql);
            $stmt->execute([$jumlah, $produkId]);

            $stmt = $pdo->prepare(
                'INSERT INTO stock_movements
                 (product_id, user_id, type, quantity, note)
                 VALUES (?, ?, ?, ?, ?)'
            );
            $stmt->execute([
                $produkId,
                $_SESSION['user']['id'],
                $jenis,
                $jumlah,
                $catatan
            ]);

            $pdo->commit();
            $success = 'Transaksi stok berhasil disimpan.';
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            $error = $e->getMessage();
        }
    }
}

$products = $pdo->query(
    'SELECT id, sku, name, stock FROM products ORDER BY name'
)->fetchAll();

$history = $pdo->query(
    'SELECT m.*, p.name AS nama_produk, p.sku, u.username
     FROM stock_movements m
     JOIN products p ON p.id = m.product_id
     JOIN users u ON u.id = m.user_id
     ORDER BY m.id DESC
     LIMIT 30'
)->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Transaksi Stok</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<header><h1>Manajemen Stok Galeri Laptop</h1></header>
<nav>
    <a href="dashboard.php">Dashboard</a>
    <a href="products.php">Produk</a>
    <a href="stock.php">Transaksi Stok</a>
    <a href="logout.php">Keluar</a>
</nav>

<main class="container">
    <h2>Barang Masuk dan Keluar</h2>
    <p class="small">
        Histori transaksi mencatat produk, jumlah, pengguna, dan waktu transaksi.
    </p>

    <?php if ($error): ?>
        <p class="error"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>

    <?php if ($success): ?>
        <p class="success"><?= htmlspecialchars($success) ?></p>
    <?php endif; ?>

    <section class="panel">
        <form method="POST">
            <label>Produk</label>
            <select name="produk_id" required>
                <option value="">Pilih produk</option>
                <?php foreach ($products as $p): ?>
                    <option value="<?= $p['id'] ?>">
                        <?= htmlspecialchars($p['sku']) ?> -
                        <?= htmlspecialchars($p['name']) ?>
                        (Stok: <?= $p['stock'] ?>)
                    </option>
                <?php endforeach; ?>
            </select>

            <label>Jenis Transaksi</label>
            <select name="jenis" required>
                <option value="IN">Barang Masuk</option>
                <option value="OUT">Barang Keluar</option>
            </select>

            <label>Jumlah</label>
            <input type="number" name="jumlah" min="1" required>

            <label>Keterangan</label>
            <input type="text" name="catatan" maxlength="255">

            <button type="submit">Simpan Transaksi</button>
        </form>
    </section>

    <section class="panel table-wrap">
        <h3>Histori Transaksi</h3>
        <table>
            <tr>
                <th>Waktu</th>
                <th>SKU</th>
                <th>Produk</th>
                <th>Jenis</th>
                <th>Jumlah</th>
                <th>Pengguna</th>
                <th>Keterangan</th>
            </tr>
            <?php foreach ($history as $h): ?>
            <tr>
                <td><?= htmlspecialchars($h['created_at']) ?></td>
                <td><?= htmlspecialchars($h['sku']) ?></td>
                <td><?= htmlspecialchars($h['nama_produk']) ?></td>
                <td><?= $h['type'] === 'IN' ? 'Masuk' : 'Keluar' ?></td>
                <td><?= (int)$h['quantity'] ?></td>
                <td><?= htmlspecialchars($h['username']) ?></td>
                <td><?= htmlspecialchars($h['note'] ?? '') ?></td>
            </tr>
            <?php endforeach; ?>
        </table>
    </section>
</main>
</body>
</html>
