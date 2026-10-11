
<?php
require_once 'config.php';
require_once 'auth.php';
cekLogin();

$totalProduk = $pdo->query(
    "SELECT COUNT(*) FROM products"
)->fetchColumn();

$totalStok = $pdo->query(
    "SELECT COALESCE(SUM(stock), 0) FROM products"
)->fetchColumn();

$nilaiStok = $pdo->query(
    "SELECT COALESCE(SUM(price * stock), 0) FROM products"
)->fetchColumn();

$stokRendah = $pdo->query(
    "SELECT COUNT(*) FROM products
     WHERE stock <= minimum_stock"
)->fetchColumn();

$nama = htmlspecialchars($_SESSION['user']['username']);
$role = htmlspecialchars($_SESSION['user']['role']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Dashboard Galeri Laptop</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<header>
    <h1>Manajemen Stok Galeri Laptop</h1>
    <p>Halo, <?= $nama ?> (<?= $role ?>)</p>
</header>

<nav>
    <a href="dashboard.php">Dashboard</a>
    <a href="products.php">Produk</a>
    <?php if ($_SESSION['user']['role'] === 'admin'): ?>
        <a href="categories.php">Kategori</a>
        <a href="suppliers.php">Supplier</a>
    <?php endif; ?>
    <a href="stock.php">Transaksi Stok</a>
    <a href="logout.php">Keluar</a>
</nav>

<main class="container">
    <section class="welcome">
        <h2>Dashboard Inventory</h2>
        <p>Pantau persediaan Laptop, iPad, dan Aksesoris.</p>
    </section>

    <section class="stats">
        <div class="card">
            <span>Total Produk</span>
            <h2><?= $totalProduk ?></h2>
        </div>
        <div class="card">
            <span>Total Stok</span>
            <h2><?= $totalStok ?> unit</h2>
        </div>
        <div class="card">
            <span>Nilai Persediaan</span>
            <h2>Rp <?= number_format($nilaiStok, 0, ',', '.') ?></h2>
        </div>
        <div class="card">
            <span>Stok Rendah</span>
            <h2><?= $stokRendah ?></h2>
        </div>
    </section>

    <section class="panel">
        <h3>Informasi Sistem</h3>
        <p>Gunakan menu Produk untuk melihat dan mencari barang.</p>
        <p>Gunakan menu Transaksi Stok untuk mencatat barang masuk dan keluar.</p>
        <a class="button" href="products.php">Lihat Produk</a>
    </section>
</main>
</body>
</html>
