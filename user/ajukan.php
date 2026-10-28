<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
requireRole('user');
$user = currentUser();
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $perkiraan = (float)($_POST['perkiraan_liter'] ?? 0);
    $alamat = trim($_POST['alamat_jemput'] ?? '');
    $catatan = trim($_POST['catatan'] ?? '');

    if ($perkiraan <= 0) {
        $errors[] = 'Perkiraan jumlah liter harus lebih dari 0.';
    }
    if ($alamat === '') {
        $errors[] = 'Alamat penjemputan wajib diisi.';
    }

    if (empty($errors)) {
        $stmt = getDB()->prepare(
            "INSERT INTO setoran (user_id, perkiraan_liter, alamat_jemput, catatan) VALUES (?, ?, ?, ?)"
        );
        $stmt->execute([$user['id'], $perkiraan, $alamat, $catatan ?: null]);
        setFlash('success', 'Pengajuan setoran berhasil dikirim. Menunggu pengepul memproses.');
        header('Location: /user/dashboard.php');
        exit;
    }
}

$pageTitle = 'Ajukan Setor';
require __DIR__ . '/../includes/header.php';
?>

<div class="card" style="max-width:520px;">
    <h2 class="mt-0">Ajukan Setoran Minyak Jelantah</h2>
    <p class="text-muted">Isi perkiraan jumlah liter dan alamat penjemputan. Pengepul akan datang untuk mengukur liter aktual dan mengonfirmasi.</p>

    <?php foreach ($errors as $err): ?>
        <div class="alert alert-danger"><?= e($err) ?></div>
    <?php endforeach; ?>

    <form method="post">
        <div>
            <label>Perkiraan Jumlah (liter)</label>
            <input type="number" step="0.1" min="0.1" name="perkiraan_liter" value="<?= e($_POST['perkiraan_liter'] ?? '') ?>" required>
        </div>
        <div>
            <label>Alamat Penjemputan</label>
            <textarea name="alamat_jemput" required><?= e($_POST['alamat_jemput'] ?? $user['alamat']) ?></textarea>
        </div>
        <div>
            <label>Catatan (opsional)</label>
            <textarea name="catatan" placeholder="Contoh: jam berapa bisa dijemput, patokan lokasi, dll."><?= e($_POST['catatan'] ?? '') ?></textarea>
        </div>
        <button type="submit" class="btn">Kirim Pengajuan</button>
    </form>
</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>
