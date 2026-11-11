<?php
if (session_status() === PHP_SESSION_NONE) session_start();

require_once __DIR__ . '/includes/cek_session.php';
require_once __DIR__ . '/config/akses.php';
require_once __DIR__ . '/config/koneksi.php';

wajib_role(['admin']);

$role = str_replace([' ', '-'], '_', strtolower(trim($_SESSION['role'] ?? '')));

if (!in_array($role, ['admin', 'administrator'], true)) {
    http_response_code(403);
    exit('Akses ditolak.');
}

if (empty($_SESSION['csrf_konfirmasi_tentang_saya'])) {
    $_SESSION['csrf_konfirmasi_tentang_saya'] = bin2hex(random_bytes(32));
}

function e($value): string {
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

$pesanError = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrf = $_POST['csrf_token'] ?? '';
    $password = $_POST['password'] ?? '';

    if (!is_string($csrf) || !hash_equals($_SESSION['csrf_konfirmasi_tentang_saya'], $csrf)) {
        $pesanError = 'Permintaan tidak valid. Silakan coba kembali.';
    } elseif ($password === '') {
        $pesanError = 'Password Admin wajib diisi.';
    } else {
        $userId = $_SESSION['user_id'] ?? $_SESSION['id'] ?? 0;
        $stmt = mysqli_prepare($koneksi, "SELECT password FROM t_users WHERE id = ? LIMIT 1");

        if (!$stmt) {
            $pesanError = 'Gagal memproses konfirmasi password.';
        } else {
            mysqli_stmt_bind_param($stmt, 'i', $userId);
            mysqli_stmt_execute($stmt);
            $akun = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
            mysqli_stmt_close($stmt);

            if ($akun && password_verify($password, $akun['password'])) {
                session_regenerate_id(true);
                $_SESSION['konfirmasi_edit_tentang_saya'] = time();
                unset($_SESSION['csrf_konfirmasi_tentang_saya']);
                header('Location: edit_tentang_saya.php');
                exit;
            }

            $pesanError = 'Password Admin salah. Silakan periksa kembali.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Konfirmasi Password | SI-TIB</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container min-vh-100 d-flex align-items-center justify-content-center py-4">
    <div class="card border-0 shadow rounded-4 w-100" style="max-width: 430px;">
        <div class="card-body p-4 p-md-5">

            <div class="text-center mb-4">
                <div class="bg-primary-subtle text-primary rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 70px; height: 70px;">
                    <i class="bi bi-shield-lock fs-1"></i>
                </div>
                <h4 class="fw-bold mb-2">Konfirmasi Password</h4>
                <p class="text-secondary mb-0">Masukkan password Admin untuk melanjutkan edit Tentang Saya.</p>
            </div>

            <?php if ($pesanError !== ''): ?>
                <div class="alert alert-danger" role="alert">
                    <i class="bi bi-exclamation-circle me-2"></i><?= e($pesanError) ?>
                </div>
            <?php endif; ?>

            <form method="POST">
                <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_konfirmasi_tentang_saya']) ?>">

                <div class="mb-3">
                    <label for="password" class="form-label fw-semibold">Password Admin</label>
                    <div class="input-group">
                        <input type="password" name="password" id="password" class="form-control" placeholder="Masukkan password Admin" autocomplete="current-password" required autofocus>
                        <button class="btn btn-outline-secondary" type="button" id="togglePassword" aria-label="Tampilkan password">
                            <i class="bi bi-eye"></i>
                        </button>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary w-100 py-2">
                    <i class="bi bi-shield-check me-2"></i>Konfirmasi dan Lanjutkan
                </button>

                <a href="tentang_saya.php" class="btn btn-light border w-100 mt-2 py-2">
                    <i class="bi bi-arrow-left me-2"></i>Kembali
                </a>
            </form>

        </div>
    </div>
</div>

<script>
document.getElementById('togglePassword').addEventListener('click', function () {
    const password = document.getElementById('password');
    const icon = this.querySelector('i');
    password.type = password.type === 'password' ? 'text' : 'password';
    icon.classList.toggle('bi-eye');
    icon.classList.toggle('bi-eye-slash');
});
</script>

</body>
</html>