
<?php
require_once 'config.php';
require_once 'auth.php';
cekLogin();

$cari = trim($_GET['cari'] ?? '');
$kategori = (int) ($_GET['kategori'] ?? 0);
$urut = $_GET['urut'] ?? 'name';
$arah = strtoupper($_GET['arah'] ?? 'ASC');

$kolomUrut = [
    'name' => 'p.name',
    'price' => 'p.price',
    'stock' => 'p.stock'
];

if (!isset($kolomUrut[$urut])) {
    $urut = 'name';
}
if (!in_array($arah, ['ASC', 'DESC'], true)) {
    $arah = 'ASC';
}

$syarat = [];
$parameter = [];

if ($cari !== '') {
    $syarat[] = '(p.name LIKE :cari OR p.sku LIKE :cari)';
    $parameter['cari'] = "%$cari%";
}
if ($kategori > 0) {
    $syarat[] = 'p.category_id = :kategori';
    $parameter['kategori'] = $kategori;
}

$where = $syarat ? 'WHERE ' . implode(' AND ', $syarat) : '';

$hitung = $pdo->prepare(
    "SELECT COUNT(*) FROM products p $where"
);
$hitung->execute($parameter);
$total = (int) $hitung->fetchColumn();

$batas = 5;
$jumlahHalaman = max(1, (int) ceil($total / $batas));
$halaman = min(
    max(1, (int) ($_GET['halaman'] ?? 1)),
    $jumlahHalaman
);
$mulai = ($halaman - 1) * $batas;

$sql = "SELECT p.*, c.name AS kategori_nama
        FROM products p
        JOIN categories c ON c.id = p.category_id
        $where
        ORDER BY {$kolomUrut[$urut]} $arah
        LIMIT :batas OFFSET :mulai";

$stmt = $pdo->prepare($sql);

foreach ($parameter as $key => $value) {
    $stmt->bindValue(":$key", $value);
}
$stmt->bindValue(':batas', $batas, PDO::PARAM_INT);
$stmt->bindValue(':mulai', $mulai, PDO::PARAM_INT);
$stmt->execute();

$products = $stmt->fetchAll();
$categories = $pdo->query(
    "SELECT * FROM categories ORDER BY name"
)->fetchAll();

function aman($nilai) {
    return htmlspecialchars((string) $nilai, ENT_QUOTES, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Data Produk</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<header><h1>Manajemen Stok Galeri Laptop</h1></header>
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
    <h2>Daftar Produk Elektronik</h2>

    <section class="panel">
        <form method="GET" class="toolbar">
            <input type="text" name="cari"
                   placeholder="Cari nama atau SKU..."
                   value="<?= aman($cari) ?>">

            <select name="kategori">
                <option value="0">Semua kategori</option>
                <?php foreach ($categories as $c): ?>
                    <option value="<?= $c['id'] ?>"
                        <?= $kategori === (int)$c['id'] ? 'selected' : '' ?>>
                        <?= aman($c['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <select name="urut">
                <option value="name" <?= $urut==='name'?'selected':'' ?>>Nama</option>
                <option value="price" <?= $urut==='price'?'selected':'' ?>>Harga</option>
                <option value="stock" <?= $urut==='stock'?'selected':'' ?>>Stok</option>
            </select>

            <select name="arah">
                <option value="ASC" <?= $arah==='ASC'?'selected':'' ?>>Naik</option>
                <option value="DESC" <?= $arah==='DESC'?'selected':'' ?>>Turun</option>
            </select>

            <button type="submit">Terapkan</button>

            <?php if ($_SESSION['user']['role'] === 'admin'): ?>
                <a class="button" href="product_form.php">Tambah Produk</a>
            <?php endif; ?>
        </form>

        <div class="table-wrap">
            <table>
                <tr>
                    <th>SKU</th><th>Nama Produk</th><th>Kategori</th>
                    <th>Harga</th><th>Stok</th><th>Status</th><th>Aksi</th>
                </tr>
                <?php foreach ($products as $p): ?>
                <tr>
                    <td><?= aman($p['sku']) ?></td>
                    <td><?= aman($p['name']) ?></td>
                    <td><?= aman($p['kategori_nama']) ?></td>
                    <td>Rp <?= number_format($p['price'], 0, ',', '.') ?></td>
                    <td><?= (int)$p['stock'] ?></td>
                    <td>
                        <span class="<?= $p['stock'] <= $p['minimum_stock'] ? 'low' : 'available' ?>">
                            <?= $p['stock'] <= $p['minimum_stock'] ? 'Stok rendah' : 'Tersedia' ?>
                        </span>
                    </td>
                    <td>
                        <?php if ($_SESSION['user']['role'] === 'admin'): ?>
                            <a class="button"
                               href="product_form.php?id=<?= $p['id'] ?>">Edit</a>
                            <a class="button danger"
                               href="product_form.php?hapus=<?= $p['id'] ?>"
                               onclick="return confirm('Hapus produk ini?')">Hapus</a>
                        <?php else: ?>
                            -
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>

                <?php if (!$products): ?>
                    <tr><td colspan="7">Produk tidak ditemukan.</td></tr>
                <?php endif; ?>
            </table>
        </div>

        <div class="pagination">
            <?php for ($i = 1; $i <= $jumlahHalaman; $i++): ?>
                <a class="<?= $i === $halaman ? 'active' : '' ?>"
                   href="?halaman=<?= $i ?>&cari=<?= urlencode($cari) ?>&kategori=<?= $kategori ?>&urut=<?= $urut ?>&arah=<?= $arah ?>">
                    <?= $i ?>
                </a>
            <?php endfor; ?>
        </div>
        <p class="small">Total hasil: <?= $total ?> produk.</p>
    </section>
</main>
</body>
</html>
