<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';
if (isLoggedIn()) {
    redirectByRole();
}

$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    $stmt = getDB()->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['nama'] = $user['nama'];
        redirectByRole();
    } else {
        $error = 'Email atau password salah.';
    }
}

$pageTitle = 'Masuk';
require __DIR__ . '/includes/header.php';
?>

<div class="card" style="max-width:420px;margin:0 auto;">
    <h2 class="mt-0">Masuk</h2>

    <?php if ($error): ?>
        <div class="alert alert-danger"><?= e($error) ?></div>
    <?php endif; ?>

    <form method="post">
        <div>
            <label>Email</label>
            <input type="email" name="email" required>
        </div>
        <div>
            <label>Password</label>
            <input type="password" name="password" required>
        </div>
        <button type="submit" class="btn">Masuk</button>
    </form>
    <p class="text-muted" style="margin-top:14px;">Belum punya akun? <a href="/register.php">Daftar di sini</a></p>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
