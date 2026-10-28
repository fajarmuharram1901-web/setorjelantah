<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';
if (isLoggedIn()) {
    redirectByRole();
}
$pageTitle = 'Beranda';
require __DIR__ . '/includes/header.php';
$harga = hargaAktif();
?>

<div class="hero">
    <h1>Setor Minyak Jelantah, Dapat Saldo</h1>
    <p>SetorJelantah menghubungkan rumah tangga dengan pengepul untuk mengumpulkan minyak jelantah bekas.
    Ajukan setoran, pengepul akan datang mengecek dan mengonfirmasi, lalu saldo Anda otomatis bertambah.</p>
    <p><strong>Harga saat ini: <?= rupiah($harga) ?> / liter</strong></p>
    <a href="/register.php" class="btn">Daftar Sekarang</a>
    <a href="/login.php" class="btn btn-secondary">Masuk</a>
</div>

<div class="grid grid-3">
    <div class="card">
        <h3>1. Ajukan Setoran</h3>
        <p class="text-muted">Masukkan perkiraan jumlah liter minyak jelantah dan alamat penjemputan.</p>
    </div>
    <div class="card">
        <h3>2. Pengepul Verifikasi</h3>
        <p class="text-muted">Pengepul datang, mengukur liter aktual, dan mengonfirmasi langsung tanpa alat otomatis.</p>
    </div>
    <div class="card">
        <h3>3. Saldo Bertambah</h3>
        <p class="text-muted">Saldo dihitung otomatis dari liter aktual dikali harga per liter yang berlaku.</p>
    </div>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
