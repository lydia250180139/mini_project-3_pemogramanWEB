
<?php
require_once 'config.php';
session_start();

if (isset($_SESSION['user'])) {
    header('Location: dashboard.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    $stmt = $pdo->prepare(
        "SELECT * FROM users WHERE username = :username"
    );
    $stmt->execute(['username' => $username]);
    $user = $stmt->fetch();

    if (
        $user &&
        hash('sha256', $password) === $user['password']
    ) {
        session_regenerate_id(true);

        $_SESSION['user'] = [
            'id' => $user['id'],
            'username' => $user['username'],
            'role' => $user['role']
        ];

        header('Location: dashboard.php');
        exit;
    } else {
        $error = 'Username atau password salah.';
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login Galeri Laptop</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="login-box">
    <h1>Galeri Laptop</h1>
    <p>Manajemen Stok Produk Elektronik</p>

    <?php if ($error): ?>
        <p class="error"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>

    <form method="POST">
        <label>Username</label>
        <input type="text" name="username" required>

        <label>Password</label>
        <input type="password" name="password" required>

        <button type="submit">Login</button>
    </form>

    <p class="small">Admin: admin / admin123</p>
    <p class="small">Staff: staff / staff123</p>
</div>
</body>
</html>
