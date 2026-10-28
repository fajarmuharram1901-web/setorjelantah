<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
requireRole('user');
$user = currentUser();
$db = getDB();

$stmt = $db->prepare("SELECT * FROM setoran WHERE user_id = ? ORDER BY tanggal_ajukan DESC LIMIT 5");
$stmt->execute([$user['id']]);
$setoranTerbaru = $stmt->fetchAll();

$stmt = $db->prepare("SELECT COUNT(*) c FROM setoran WHERE user_id = ? AND status = 'menunggu'");
$stmt->execute([$user['id']]);
$menunggu = $stmt->fetch()['c'];

$stmt = $db->prepare("SELECT COALESCE(SUM(liter_aktual),0) total FROM setoran WHERE user_id = ? AND status = 'selesai'");
$stmt->execute([$user['id']]);
$totalLiter = $stmt->fetch()['total'];

$pageTitle = 'Dashboard';
require __DIR__ . '/../includes/header.php';
?>

<div class="flex-between">
    <h1 class="mt-0">Halo, <?= e($user['nama']) ?> 👋</h1>
    <a href="/user/ajukan.php" class="btn">+ Ajukan Setor</a>
</div>

<div class="grid grid-3">
    <div class="stat-box">
        <div class="value"><?= rupiah($user['saldo']) ?></div>
        <div class="label">Saldo Anda</div>
    </div>
    <div class="stat-box">
        <div class="value"><?= liter($totalLiter) ?></div>
        <div class="label">Total Minyak Tersetor</div>
    </div>
    <div class="stat-box">
        <div class="value"><?= (int)$menunggu ?></div>
        <div class="label">Pengajuan Menunggu</div>
    </div>
</div>

<div class="card">
    <div class="flex-between">
        <h3 class="mt-0">Setoran Terbaru</h3>
        <a href="/user/riwayat.php">Lihat semua &rarr;</a>
    </div>
    <?php if (empty($setoranTerbaru)): ?>
        <p class="text-muted">Belum ada pengajuan setoran. <a href="/user/ajukan.php">Ajukan sekarang</a>.</p>
    <?php else: ?>
    <table>
        <thead>
            <tr><th>Tanggal</th><th>Perkiraan</th><th>Liter Aktual</th><th>Total</th><th>Status</th></tr>
        </thead>
        <tbody>
        <?php foreach ($setoranTerbaru as $s): ?>
            <tr>
                <td><?= date('d M Y', strtotime($s['tanggal_ajukan'])) ?></td>
                <td><?= liter($s['perkiraan_liter']) ?></td>
                <td><?= $s['liter_aktual'] !== null ? liter($s['liter_aktual']) : '-' ?></td>
                <td><?= $s['total_saldo'] !== null ? rupiah($s['total_saldo']) : '-' ?></td>
                <td><?= statusBadge($s['status']) ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <?php endif; ?>
</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>
