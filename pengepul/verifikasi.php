<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
requireRole('pengepul');
$pengepul = currentUser();
$db = getDB();
$errors = [];

// Aksi: ambil tugas dari daftar menunggu
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['aksi'] ?? '') === 'ambil') {
    $id = (int)($_POST['setoran_id'] ?? 0);
    $stmt = $db->prepare("UPDATE setoran SET status = 'diproses', pengepul_id = ? WHERE id = ? AND status = 'menunggu'");
    $stmt->execute([$pengepul['id'], $id]);
    setFlash('success', 'Tugas berhasil diambil. Silakan konfirmasi setelah pengecekan di lokasi.');
    header('Location: /pengepul/verifikasi.php?id=' . $id);
    exit;
}

// Aksi: konfirmasi liter aktual & selesaikan
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['aksi'] ?? '') === 'konfirmasi') {
    $id = (int)($_POST['setoran_id'] ?? 0);
    $literAktual = (float)($_POST['liter_aktual'] ?? 0);

    if ($literAktual <= 0) {
        $errors[] = 'Liter aktual harus lebih dari 0.';
    }

    if (empty($errors)) {
        $stmt = $db->prepare("SELECT * FROM setoran WHERE id = ? AND pengepul_id = ? AND status = 'diproses'");
        $stmt->execute([$id, $pengepul['id']]);
        $setoran = $stmt->fetch();

        if (!$setoran) {
            $errors[] = 'Data setoran tidak ditemukan atau sudah diproses pengepul lain.';
        } else {
            $harga = hargaAktif();
            $total = $literAktual * $harga;

            $db->beginTransaction();
            try {
                $stmt = $db->prepare(
                    "UPDATE setoran SET liter_aktual = ?, harga_per_liter_saat_itu = ?, total_saldo = ?,
                     status = 'selesai', tanggal_selesai = NOW() WHERE id = ?"
                );
                $stmt->execute([$literAktual, $harga, $total, $id]);

                $stmt = $db->prepare("UPDATE users SET saldo = saldo + ? WHERE id = ?");
                $stmt->execute([$total, $setoran['user_id']]);

                $db->commit();
                setFlash('success', 'Setoran dikonfirmasi. Saldo warga bertambah ' . rupiah($total) . '.');
                header('Location: /pengepul/dashboard.php');
                exit;
            } catch (Exception $e) {
                $db->rollBack();
                $errors[] = 'Terjadi kesalahan saat menyimpan data.';
            }
        }
    }
}

$id = (int)($_GET['id'] ?? $_POST['setoran_id'] ?? 0);
$stmt = $db->prepare(
    "SELECT s.*, u.nama AS nama_user, u.no_hp FROM setoran s
     JOIN users u ON u.id = s.user_id
     WHERE s.id = ? AND s.pengepul_id = ? AND s.status = 'diproses'"
);
$stmt->execute([$id, $pengepul['id']]);
$setoran = $stmt->fetch();

$pageTitle = 'Konfirmasi Setoran';
require __DIR__ . '/../includes/header.php';
?>

<h1 class="mt-0">Konfirmasi Setoran</h1>

<?php foreach ($errors as $err): ?>
    <div class="alert alert-danger"><?= e($err) ?></div>
<?php endforeach; ?>

<?php if (!$setoran): ?>
    <div class="card">
        <p class="text-muted">Data setoran tidak ditemukan, sudah selesai, atau bukan tugas Anda.</p>
        <a href="/pengepul/dashboard.php" class="btn btn-sm">Kembali ke Dashboard</a>
    </div>
<?php else: ?>
    <div class="card" style="max-width:520px;">
        <p><strong>Warga:</strong> <?= e($setoran['nama_user']) ?> (<?= e($setoran['no_hp']) ?>)</p>
        <p><strong>Alamat:</strong> <?= e($setoran['alamat_jemput']) ?></p>
        <p><strong>Perkiraan Warga:</strong> <?= liter($setoran['perkiraan_liter']) ?></p>
        <?php if ($setoran['catatan']): ?><p><strong>Catatan:</strong> <?= e($setoran['catatan']) ?></p><?php endif; ?>
        <p class="text-muted">Harga berlaku saat ini: <?= rupiah(hargaAktif()) ?> / liter</p>

        <form method="post">
            <input type="hidden" name="aksi" value="konfirmasi">
            <input type="hidden" name="setoran_id" value="<?= $setoran['id'] ?>">
            <div>
                <label>Liter Aktual (hasil ukur di lokasi)</label>
                <input type="number" step="0.1" min="0.1" name="liter_aktual" required>
            </div>
            <button type="submit" class="btn">Konfirmasi & Selesaikan</button>
        </form>
    </div>
<?php endif; ?>

<?php require __DIR__ . '/../includes/footer.php'; ?>
