
<?php
require_once 'config.php';
require_once 'auth.php';
cekAdmin();

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = trim($_POST['nama'] ?? '');

    if ($nama === '') {
        $error = 'Nama kategori wajib diisi.';
    } else {
        try {
            $stmt = $pdo->prepare(
                "INSERT INTO categories (name) VALUES (?)"
            );
            $stmt->execute([$nama]);
            header('Location: categories.php');
            exit;
        } catch (PDOException $e) {
            $error = 'Kategori sudah ada atau tidak dapat ditambahkan.';
        }
    }
}

if (isset($_GET['hapus'])) {
    try {
        $stmt = $pdo->prepare(
            "DELETE FROM categories WHERE id = ?"
        );
        $stmt->execute([(int) $_GET['hapus']]);
        header('Location: categories.php');
        exit;
    } catch (PDOException $e) {
        $error = 'Kategori masih digunakan oleh produk.';
    }
}

$categories = $pdo->query(
    "SELECT * FROM categories ORDER BY name"
)->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Kategori Produk</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<header><h1>Manajemen Stok Galeri Laptop</h1></header>
<nav>
    <a href="dashboard.php">Dashboard</a>
    <a href="products.php">Produk</a>
    <a href="categories.php">Kategori</a>
    <a href="suppliers.php">Supplier</a>
    <a href="stock.php">Transaksi Stok</a>
    <a href="logout.php">Keluar</a>
</nav>

<main class="container">
    <h2>Kelola Kategori</h2>

    <?php if ($error): ?>
        <p class="error"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>

    <section class="panel">
        <form method="POST">
            <label>Nama Kategori</label>
            <input type="text" name="nama" required>
            <button type="submit">Tambah Kategori</button>
        </form>
    </section>

    <section class="panel table-wrap">
        <table>
            <tr><th>ID</th><th>Nama Kategori</th><th>Aksi</th></tr>
            <?php foreach ($categories as $c): ?>
            <tr>
                <td><?= $c['id'] ?></td>
                <td><?= htmlspecialchars($c['name']) ?></td>
                <td>
                    <a class="button danger"
                       href="?hapus=<?= $c['id'] ?>"
                       onclick="return confirm('Hapus kategori ini?')">
                        Hapus
                    </a>
                </td>
            </tr>
            <?php endforeach; ?>
        </table>
    </section>
</main>
</body>
</html>
