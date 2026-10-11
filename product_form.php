
<?php
require_once 'config.php';
require_once 'auth.php';
cekAdmin();

$id = (int) ($_GET['id'] ?? 0);
$error = '';

if (isset($_GET['hapus'])) {
    try {
        $stmt = $pdo->prepare('DELETE FROM products WHERE id = ?');
        $stmt->execute([(int) $_GET['hapus']]);
        header('Location: products.php');
        exit;
    } catch (PDOException $e) {
        $error = 'Produk masih memiliki riwayat atau relasi data.';
    }
}

$data = [
    'category_id' => '',
    'sku' => '',
    'name' => '',
    'price' => '',
    'stock' => 0,
    'minimum_stock' => 2,
    'description' => ''
];

if ($id > 0) {
    $stmt = $pdo->prepare('SELECT * FROM products WHERE id = ?');
    $stmt->execute([$id]);
    $hasil = $stmt->fetch();

    if (!$hasil) {
        exit('Produk tidak ditemukan.');
    }
    $data = $hasil;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'category_id' => (int) ($_POST['category_id'] ?? 0),
        'sku' => trim($_POST['sku'] ?? ''),
        'name' => trim($_POST['name'] ?? ''),
        'price' => $_POST['price'] ?? '',
        'stock' => $_POST['stock'] ?? '',
        'minimum_stock' => $_POST['minimum_stock'] ?? '',
        'description' => trim($_POST['description'] ?? '')
    ];

    if ($data['sku'] === '' || $data['name'] === '') {
        $error = 'SKU dan nama produk wajib diisi.';
    } elseif ($data['category_id'] < 1) {
        $error = 'Pilih kategori produk.';
    } elseif (
        !is_numeric($data['price']) ||
        $data['price'] < 0
    ) {
        $error = 'Harga harus berupa angka nol atau lebih.';
    } elseif (
        filter_var($data['stock'], FILTER_VALIDATE_INT) === false ||
        $data['stock'] < 0
    ) {
        $error = 'Stok harus bilangan bulat nol atau lebih.';
    } elseif (
        filter_var($data['minimum_stock'], FILTER_VALIDATE_INT) === false ||
        $data['minimum_stock'] < 0
    ) {
        $error = 'Minimum stok harus bilangan bulat nol atau lebih.';
    } else {
        try {
            if ($id > 0) {
                $sql = "UPDATE products SET
                    category_id=?, sku=?, name=?, price=?,
                    stock=?, minimum_stock=?, description=?
                    WHERE id=?";
                $values = [
                    $data['category_id'], $data['sku'], $data['name'],
                    $data['price'], $data['stock'],
                    $data['minimum_stock'], $data['description'], $id
                ];
            } else {
                $sql = "INSERT INTO products
                    (category_id, sku, name, price, stock,
                     minimum_stock, description)
                    VALUES (?, ?, ?, ?, ?, ?, ?)";
                $values = [
                    $data['category_id'], $data['sku'], $data['name'],
                    $data['price'], $data['stock'],
                    $data['minimum_stock'], $data['description']
                ];
            }

            $stmt = $pdo->prepare($sql);
            $stmt->execute($values);

            header('Location: products.php');
            exit;
        } catch (PDOException $e) {
            $error = 'SKU sudah digunakan atau data tidak valid.';
        }
    }
}

$categories = $pdo->query(
    'SELECT * FROM categories ORDER BY name'
)->fetchAll();

function amanForm($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Form Produk</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<header><h1>Manajemen Stok Galeri Laptop</h1></header>
<nav>
    <a href="dashboard.php">Dashboard</a>
    <a href="products.php">Produk</a>
    <a href="categories.php">Kategori</a>
    <a href="logout.php">Keluar</a>
</nav>

<main class="container">
    <section class="panel">
        <h2><?= $id > 0 ? 'Edit Produk' : 'Tambah Produk' ?></h2>

        <?php if ($error): ?>
            <p class="error"><?= amanForm($error) ?></p>
        <?php endif; ?>

        <form method="POST">
            <label>SKU / Kode Produk</label>
            <input name="sku" maxlength="40"
                   value="<?= amanForm($data['sku']) ?>" required>

            <label>Nama Produk</label>
            <input name="name" maxlength="150"
                   value="<?= amanForm($data['name']) ?>" required>

            <label>Kategori</label>
            <select name="category_id" required>
                <option value="">Pilih kategori</option>
                <?php foreach ($categories as $c): ?>
                    <option value="<?= $c['id'] ?>"
                        <?= (int)$data['category_id'] === (int)$c['id'] ? 'selected' : '' ?>>
                        <?= amanForm($c['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <label>Harga (Rp)</label>
            <input type="number" name="price" min="0" step="0.01"
                   value="<?= amanForm($data['price']) ?>" required>

            <label>Stok</label>
            <input type="number" name="stock" min="0"
                   value="<?= amanForm($data['stock']) ?>" required>

            <label>Minimum Stok</label>
            <input type="number" name="minimum_stock" min="0"
                   value="<?= amanForm($data['minimum_stock']) ?>" required>

            <label>Deskripsi</label>
            <textarea name="description" rows="3"><?= amanForm($data['description']) ?></textarea>

            <button type="submit">Simpan Produk</button>
            <a class="button secondary" href="products.php">Kembali</a>
        </form>
    </section>
</main>
</body>
</html>
