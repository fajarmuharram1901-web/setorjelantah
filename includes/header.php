<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/functions.php';
$flash = getFlash();
$user = currentUser();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($pageTitle) ? e($pageTitle) . ' - ' : '' ?>SetorJelantah</title>
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>
<header class="navbar">
    <div class="navbar-inner">
        <a href="/index.php" class="brand">🛢️ SetorJelantah</a>
        <nav>
            <?php if ($user): ?>
                <span class="nav-user"><?= e($user['nama']) ?> <small>(<?= e($user['role']) ?>)</small></span>
                <?php if ($user['role'] === 'user'): ?>
                    <a href="/user/dashboard.php">Dashboard</a>
                    <a href="/user/ajukan.php">Ajukan Setor</a>
                <?php elseif ($user['role'] === 'pengepul'): ?>
                    <a href="/pengepul/dashboard.php">Dashboard</a>
                <?php elseif ($user['role'] === 'admin'): ?>
                    <a href="/admin/dashboard.php">Dashboard</a>
                    <a href="/admin/kelola_user.php">Kelola Pengguna</a>
                    <a href="/admin/harga.php">Harga</a>
                <?php endif; ?>
                <a href="/logout.php">Keluar</a>
            <?php else: ?>
                <a href="/login.php">Masuk</a>
                <a href="/register.php">Daftar</a>
            <?php endif; ?>
        </nav>
    </div>
</header>
<main class="container">
    <?php if ($flash): ?>
        <div class="alert alert-<?= e($flash['type']) ?>"><?= e($flash['message']) ?></div>
    <?php endif; ?>
