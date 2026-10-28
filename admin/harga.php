<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
requireRole('admin');
$db = getDB();
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $harga = (float)($_POST['harga_per_liter'] ?? 0);
    if ($harga <= 0) {
        $errors[] = 'Harga harus lebih dari 0.';
    } else {
        $stmt = $db->prepare("INSERT INTO harga (harga_per_liter, updated_by) VALUES (?, ?)");
        $stmt->execute([$harga, $_SESSION['user_id']]);
        setFlash('success', 'Harga per liter berhasil diperbarui.');
        header('Location: /admin/harga.php');
        exit;
    }
}

$riwayatHarga = $db->query(
    "SELECT h.*, u.nama AS nama_admin FROM harga h LEFT JOIN users u ON u.id = h.updated_by ORDER BY h.id DESC LIMIT 10"
)->fetchAll();

$pageTitle = 'Atur Harga';
require __DIR__ . '/../includes/header.php';
?>

<h1 class="mt-0">Atur Harga per Liter</h1>

<?php foreach ($errors as $err): ?>
    <div class="alert alert-danger"><?= e($err) ?></div>
<?php endforeach; ?>

<div class="grid grid-2">
    <div class="card">
        <h3 class="mt-0">Harga Saat Ini: <?= rupiah(hargaAktif()) ?></h3>
        <form method="post">
            <div>
                <label>Harga Baru per Liter (Rp)</label>
                <input type="number" step="100" min="1" name="harga_per_liter" required>
            </div>
            <button type="submit" class="btn">Perbarui Harga</button>
        </form>
        <p class="text-muted" style="margin-top:10px;">Perubahan harga hanya berlaku untuk setoran yang dikonfirmasi pengepul setelah harga ini disimpan.</p>
    </div>

    <div class="card">
        <h3 class="mt-0">Riwayat Perubahan Harga</h3>
        <table>
            <thead><tr><th>Harga</th><th>Diubah oleh</th><th>Tanggal</th></tr></thead>
            <tbody>
            <?php foreach ($riwayatHarga as $h): ?>
                <tr>
                    <td><?= rupiah($h['harga_per_liter']) ?></td>
                    <td><?= $h['nama_admin'] ? e($h['nama_admin']) : '-' ?></td>
                    <td><?= date('d M Y H:i', strtotime($h['updated_at'])) ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>
