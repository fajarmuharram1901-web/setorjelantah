<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
requireRole('pengepul');
$pengepul = currentUser();
$db = getDB();

// Pengajuan yang masih menunggu (siapa saja pengepul bisa ambil)
$menunggu = $db->query(
    "SELECT s.*, u.nama AS nama_user, u.no_hp FROM setoran s
     JOIN users u ON u.id = s.user_id
     WHERE s.status = 'menunggu'
     ORDER BY s.tanggal_ajukan ASC"
)->fetchAll();

// Yang sedang diproses oleh pengepul ini
$stmt = $db->prepare(
    "SELECT s.*, u.nama AS nama_user, u.no_hp FROM setoran s
     JOIN users u ON u.id = s.user_id
     WHERE s.status = 'diproses' AND s.pengepul_id = ?
     ORDER BY s.tanggal_ajukan ASC"
);
$stmt->execute([$pengepul['id']]);
$diproses = $stmt->fetchAll();

$pageTitle = 'Dashboard Pengepul';
require __DIR__ . '/../includes/header.php';
?>

<h1 class="mt-0">Dashboard Pengepul</h1>

<div class="card">
    <h3 class="mt-0">Pengajuan Menunggu (<?= count($menunggu) ?>)</h3>
    <?php if (empty($menunggu)): ?>
        <p class="text-muted">Tidak ada pengajuan baru saat ini.</p>
    <?php else: ?>
    <table>
        <thead><tr><th>Tanggal</th><th>Warga</th><th>Perkiraan</th><th>Alamat</th><th>Aksi</th></tr></thead>
        <tbody>
        <?php foreach ($menunggu as $s): ?>
            <tr>
                <td><?= date('d M Y', strtotime($s['tanggal_ajukan'])) ?></td>
                <td><?= e($s['nama_user']) ?><br><small class="text-muted"><?= e($s['no_hp']) ?></small></td>
                <td><?= liter($s['perkiraan_liter']) ?></td>
                <td><?= e($s['alamat_jemput']) ?></td>
                <td>
                    <form method="post" action="/pengepul/verifikasi.php" style="max-width:none;">
                        <input type="hidden" name="setoran_id" value="<?= $s['id'] ?>">
                        <input type="hidden" name="aksi" value="ambil">
                        <button type="submit" class="btn btn-sm">Ambil Tugas</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <?php endif; ?>
</div>

<div class="card">
    <h3 class="mt-0">Sedang Diproses Anda (<?= count($diproses) ?>)</h3>
    <?php if (empty($diproses)): ?>
        <p class="text-muted">Tidak ada tugas yang sedang diproses.</p>
    <?php else: ?>
    <table>
        <thead><tr><th>Tanggal</th><th>Warga</th><th>Perkiraan</th><th>Alamat</th><th>Aksi</th></tr></thead>
        <tbody>
        <?php foreach ($diproses as $s): ?>
            <tr>
                <td><?= date('d M Y', strtotime($s['tanggal_ajukan'])) ?></td>
                <td><?= e($s['nama_user']) ?><br><small class="text-muted"><?= e($s['no_hp']) ?></small></td>
                <td><?= liter($s['perkiraan_liter']) ?></td>
                <td><?= e($s['alamat_jemput']) ?></td>
                <td><a href="/pengepul/verifikasi.php?id=<?= $s['id'] ?>" class="btn btn-sm">Konfirmasi</a></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <?php endif; ?>
</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>
