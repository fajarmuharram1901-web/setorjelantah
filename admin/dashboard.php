<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
requireRole('admin');
$db = getDB();

$totalUser = $db->query("SELECT COUNT(*) c FROM users WHERE role = 'user'")->fetch()['c'];
$totalPengepul = $db->query("SELECT COUNT(*) c FROM users WHERE role = 'pengepul'")->fetch()['c'];
$totalLiter = $db->query("SELECT COALESCE(SUM(liter_aktual),0) t FROM setoran WHERE status = 'selesai'")->fetch()['t'];
$totalDisalurkan = $db->query("SELECT COALESCE(SUM(total_saldo),0) t FROM setoran WHERE status = 'selesai'")->fetch()['t'];
$menunggu = $db->query("SELECT COUNT(*) c FROM setoran WHERE status = 'menunggu'")->fetch()['c'];

$terbaru = $db->query(
    "SELECT s.*, u.nama AS nama_user, p.nama AS nama_pengepul FROM setoran s
     JOIN users u ON u.id = s.user_id
     LEFT JOIN users p ON p.id = s.pengepul_id
     ORDER BY s.tanggal_ajukan DESC LIMIT 10"
)->fetchAll();

$pageTitle = 'Dashboard Admin';
require __DIR__ . '/../includes/header.php';
?>

<h1 class="mt-0">Dashboard Admin</h1>

<div class="grid grid-3">
    <div class="stat-box"><div class="value"><?= (int)$totalUser ?></div><div class="label">Pengguna Terdaftar</div></div>
    <div class="stat-box"><div class="value"><?= (int)$totalPengepul ?></div><div class="label">Pengepul Aktif</div></div>
    <div class="stat-box"><div class="value"><?= (int)$menunggu ?></div><div class="label">Pengajuan Menunggu</div></div>
    <div class="stat-box"><div class="value"><?= liter($totalLiter) ?></div><div class="label">Total Minyak Terkumpul</div></div>
    <div class="stat-box"><div class="value"><?= rupiah($totalDisalurkan) ?></div><div class="label">Total Saldo Disalurkan</div></div>
    <div class="stat-box"><div class="value"><?= rupiah(hargaAktif()) ?></div><div class="label">Harga / Liter Saat Ini</div></div>
</div>

<div class="card">
    <h3 class="mt-0">Setoran Terbaru</h3>
    <table>
        <thead><tr><th>Tanggal</th><th>Warga</th><th>Pengepul</th><th>Liter</th><th>Total</th><th>Status</th></tr></thead>
        <tbody>
        <?php foreach ($terbaru as $s): ?>
            <tr>
                <td><?= date('d M Y', strtotime($s['tanggal_ajukan'])) ?></td>
                <td><?= e($s['nama_user']) ?></td>
                <td><?= $s['nama_pengepul'] ? e($s['nama_pengepul']) : '-' ?></td>
                <td><?= $s['liter_aktual'] !== null ? liter($s['liter_aktual']) : liter($s['perkiraan_liter']) . ' (perkiraan)' ?></td>
                <td><?= $s['total_saldo'] !== null ? rupiah($s['total_saldo']) : '-' ?></td>
                <td><?= statusBadge($s['status']) ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>
