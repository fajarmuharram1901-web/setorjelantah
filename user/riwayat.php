<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
requireRole('user');
$user = currentUser();

$stmt = getDB()->prepare("SELECT s.*, p.nama AS nama_pengepul FROM setoran s LEFT JOIN users p ON p.id = s.pengepul_id WHERE s.user_id = ? ORDER BY s.tanggal_ajukan DESC");
$stmt->execute([$user['id']]);
$semua = $stmt->fetchAll();

$pageTitle = 'Riwayat Setoran';
require __DIR__ . '/../includes/header.php';
?>

<div class="flex-between">
    <h1 class="mt-0">Riwayat Setoran</h1>
    <a href="/user/ajukan.php" class="btn">+ Ajukan Setor</a>
</div>

<div class="card">
    <?php if (empty($semua)): ?>
        <p class="text-muted">Belum ada riwayat setoran.</p>
    <?php else: ?>
    <table>
        <thead>
            <tr>
                <th>Tanggal Ajukan</th>
                <th>Perkiraan</th>
                <th>Liter Aktual</th>
                <th>Pengepul</th>
                <th>Total Saldo</th>
                <th>Status</th>
                <th>Selesai</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($semua as $s): ?>
            <tr>
                <td><?= date('d M Y H:i', strtotime($s['tanggal_ajukan'])) ?></td>
                <td><?= liter($s['perkiraan_liter']) ?></td>
                <td><?= $s['liter_aktual'] !== null ? liter($s['liter_aktual']) : '-' ?></td>
                <td><?= $s['nama_pengepul'] ? e($s['nama_pengepul']) : '-' ?></td>
                <td><?= $s['total_saldo'] !== null ? rupiah($s['total_saldo']) : '-' ?></td>
                <td><?= statusBadge($s['status']) ?></td>
                <td><?= $s['tanggal_selesai'] ? date('d M Y', strtotime($s['tanggal_selesai'])) : '-' ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <?php endif; ?>
</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>
