<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';
if (isLoggedIn()) {
    redirectByRole();
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = trim($_POST['nama'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $konfirmasi = $_POST['konfirmasi'] ?? '';
    $no_hp = trim($_POST['no_hp'] ?? '');
    $alamat = trim($_POST['alamat'] ?? '');

    if ($nama === '' || $email === '' || $password === '') {
        $errors[] = 'Nama, email, dan password wajib diisi.';
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Format email tidak valid.';
    }
    if (strlen($password) < 6) {
        $errors[] = 'Password minimal 6 karakter.';
    }
    if ($password !== $konfirmasi) {
        $errors[] = 'Konfirmasi password tidak cocok.';
    }

    if (empty($errors)) {
        $db = getDB();
        $stmt = $db->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $errors[] = 'Email sudah terdaftar.';
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $db->prepare("INSERT INTO users (nama, email, password, no_hp, alamat, role) VALUES (?, ?, ?, ?, ?, 'user')");
            $stmt->execute([$nama, $email, $hash, $no_hp, $alamat]);
            setFlash('success', 'Pendaftaran berhasil! Silakan masuk.');
            header('Location: /login.php');
            exit;
        }
    }
}

$pageTitle = 'Daftar';
require __DIR__ . '/includes/header.php';
?>

<div class="card" style="max-width:480px;margin:0 auto;">
    <h2 class="mt-0">Daftar sebagai Pengguna</h2>
    <p class="text-muted">Untuk akun pengepul, hubungi admin agar dibuatkan akses.</p>

    <?php foreach ($errors as $err): ?>
        <div class="alert alert-danger"><?= e($err) ?></div>
    <?php endforeach; ?>

    <form method="post">
        <div>
            <label>Nama Lengkap</label>
            <input type="text" name="nama" value="<?= e($_POST['nama'] ?? '') ?>" required>
        </div>
        <div>
            <label>Email</label>
            <input type="email" name="email" value="<?= e($_POST['email'] ?? '') ?>" required>
        </div>
        <div>
            <label>No. HP</label>
            <input type="text" name="no_hp" value="<?= e($_POST['no_hp'] ?? '') ?>">
        </div>
        <div>
            <label>Alamat</label>
            <textarea name="alamat"><?= e($_POST['alamat'] ?? '') ?></textarea>
        </div>
        <div>
            <label>Password</label>
            <input type="password" name="password" required>
        </div>
        <div>
            <label>Konfirmasi Password</label>
            <input type="password" name="konfirmasi" required>
        </div>
        <button type="submit" class="btn">Daftar</button>
    </form>
    <p class="text-muted" style="margin-top:14px;">Sudah punya akun? <a href="/login.php">Masuk di sini</a></p>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
