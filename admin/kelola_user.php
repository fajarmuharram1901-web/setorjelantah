<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
requireRole('admin');
$db = getDB();
$errors = [];

// Tambah akun pengepul baru
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['aksi'] ?? '') === 'tambah_pengepul') {
    $nama = trim($_POST['nama'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $no_hp = trim($_POST['no_hp'] ?? '');

    if ($nama === '' || $email === '' || strlen($password) < 6) {
        $errors[] = 'Nama, email wajib diisi dan password minimal 6 karakter.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Format email tidak valid.';
    } else {
        $stmt = $db->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $errors[] = 'Email sudah terdaftar.';
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $db->prepare("INSERT INTO users (nama, email, password, no_hp, role) VALUES (?, ?, ?, ?, 'pengepul')");
            $stmt->execute([$nama, $email, $hash, $no_hp]);
            setFlash('success', 'Akun pengepul berhasil dibuat.');
            header('Location: /admin/kelola_user.php');
            exit;
        }
    }
}

// Nonaktifkan/hapus akun (kecuali admin lain)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['aksi'] ?? '') === 'hapus') {
    $id = (int)($_POST['user_id'] ?? 0);
    $stmt = $db->prepare("DELETE FROM users WHERE id = ? AND role != 'admin'");
    $stmt->execute([$id]);
    setFlash('success', 'Akun berhasil dihapus.');
    header('Location: /admin/kelola_user.php');
    exit;
}

$pengepulList = $db->query("SELECT * FROM users WHERE role = 'pengepul' ORDER BY nama")->fetchAll();
$userList = $db->query("SELECT * FROM users WHERE role = 'user' ORDER BY nama")->fetchAll();

$pageTitle = 'Kelola Pengguna';
require __DIR__ . '/../includes/header.php';
?>

<h1 class="mt-0">Kelola Pengguna</h1>

<?php foreach ($errors as $err): ?>
    <div class="alert alert-danger"><?= e($err) ?></div>
<?php endforeach; ?>

<div class="grid grid-2">
    <div class="card">
        <h3 class="mt-0">Tambah Akun Pengepul</h3>
        <form method="post">
            <input type="hidden" name="aksi" value="tambah_pengepul">
            <div><label>Nama</label><input type="text" name="nama" required></div>
            <div><label>Email</label><input type="email" name="email" required></div>
            <div><label>No. HP</label><input type="text" name="no_hp"></div>
            <div><label>Password</label><input type="password" name="password" required></div>
            <button type="submit" class="btn">Buat Akun Pengepul</button>
        </form>
    </div>

    <div class="card">
        <h3 class="mt-0">Daftar Pengepul (<?= count($pengepulList) ?>)</h3>
        <?php if (empty($pengepulList)): ?>
            <p class="text-muted">Belum ada akun pengepul.</p>
        <?php else: ?>
        <table>
            <thead><tr><th>Nama</th><th>Email</th><th>Aksi</th></tr></thead>
            <tbody>
            <?php foreach ($pengepulList as $p): ?>
                <tr>
                    <td><?= e($p['nama']) ?></td>
                    <td><?= e($p['email']) ?></td>
                    <td>
                        <form method="post" style="max-width:none;" onsubmit="return confirm('Hapus akun ini?');">
                            <input type="hidden" name="aksi" value="hapus">
                            <input type="hidden" name="user_id" value="<?= $p['id'] ?>">
                            <button type="submit" class="btn btn-sm btn-danger">Hapus</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
    </div>
</div>

<div class="card">
    <h3 class="mt-0">Daftar Pengguna / Rumah Tangga (<?= count($userList) ?>)</h3>
    <?php if (empty($userList)): ?>
        <p class="text-muted">Belum ada pengguna terdaftar.</p>
    <?php else: ?>
    <table>
        <thead><tr><th>Nama</th><th>Email</th><th>No. HP</th><th>Saldo</th><th>Aksi</th></tr></thead>
        <tbody>
        <?php foreach ($userList as $u): ?>
            <tr>
                <td><?= e($u['nama']) ?></td>
                <td><?= e($u['email']) ?></td>
                <td><?= e($u['no_hp']) ?></td>
                <td><?= rupiah($u['saldo']) ?></td>
                <td>
                    <form method="post" style="max-width:none;" onsubmit="return confirm('Hapus akun ini?');">
                        <input type="hidden" name="aksi" value="hapus">
                        <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                        <button type="submit" class="btn btn-sm btn-danger">Hapus</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <?php endif; ?>
</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>
