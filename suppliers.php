
<?php
require_once 'config.php';
require_once 'auth.php';
cekAdmin();

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = trim($_POST['nama'] ?? '');
    $telepon = trim($_POST['telepon'] ?? '');
    $alamat = trim($_POST['alamat'] ?? '');

    if ($nama === '') {
        $error = 'Nama supplier wajib diisi.';
    } else {
        $stmt = $pdo->prepare(
            'INSERT INTO suppliers (name, phone, address)
             VALUES (?, ?, ?)'
        );
        $stmt->execute([$nama, $telepon, $alamat]);

        header('Location: suppliers.php');
        exit;
    }
}

$suppliers = $pdo->query(
    'SELECT * FROM suppliers ORDER BY name'
)->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Supplier</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<header><h1>Manajemen Stok Galeri Laptop</h1></header>
<nav>
    <a href="dashboard.php">Dashboard</a>
    <a href="products.php">Produk</a>
    <a href="categories.php">Kategori</a>
    <a href="suppliers.php">Supplier</a>
    <a href="logout.php">Keluar</a>
</nav>

<main class="container">
    <h2>Data Supplier</h2>

    <?php if ($error): ?>
        <p class="error"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>

    <section class="panel">
        <form method="POST">
            <label>Nama Supplier</label>
            <input name="nama" required>

            <label>Nomor Telepon</label>
            <input name="telepon">

            <label>Alamat</label>
            <input name="alamat">

            <button type="submit">Tambah Supplier</button>
        </form>
    </section>

    <section class="panel table-wrap">
        <table>
            <tr>
                <th>Nama Supplier</th>
                <th>Telepon</th>
                <th>Alamat</th>
            </tr>
            <?php foreach ($suppliers as $s): ?>
            <tr>
                <td><?= htmlspecialchars($s['name']) ?></td>
                <td><?= htmlspecialchars($s['phone'] ?? '') ?></td>
                <td><?= htmlspecialchars($s['address'] ?? '') ?></td>
            </tr>
            <?php endforeach; ?>
        </table>
    </section>
</main>
</body>
</html>
