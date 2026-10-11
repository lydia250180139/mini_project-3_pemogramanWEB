
<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function cekLogin()
{
    if (!isset($_SESSION['user'])) {
        header('Location: login.php');
        exit;
    }
}

function cekAdmin()
{
    cekLogin();

    if ($_SESSION['user']['role'] !== 'admin') {
        http_response_code(403);
        exit('Akses ditolak! Halaman ini hanya untuk admin.');
    }
}
?>
