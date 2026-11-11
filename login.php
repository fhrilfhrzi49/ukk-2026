<?php

if (session_status() === PHP_SESSION_NONE) session_start();

$pesan_error = $_SESSION['pesan_error'] ?? '';
unset($_SESSION['pesan_error']);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login | SI-TIB</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
</head>
<body class="bg-body-tertiary min-vh-100 d-flex align-items-center">

<main class="container py-5">
    <div class="row justify-content-center">
        <div class="col-12 col-sm-10 col-md-7 col-lg-5 col-xl-4">
            <div class="card border-0 shadow rounded-4 overflow-hidden">
                <div class="card-header bg-primary text-white text-center border-0 py-4">
                    <div class="mb-3">
                        <span class="d-inline-flex align-items-center justify-content-center bg-white text-primary rounded-circle" style="width:76px;height:76px;">
                            <i class="bi bi-shield-lock-fill fs-1"></i>
                        </span>
                    </div>
                    <h3 class="fw-bold mb-2">Selamat Datang</h3>
                    <p class="mb-0 text-white-50">SI-TIB — Sistem Informasi Tata Tertib</p>
                </div>

                <div class="card-body p-4">
                    <div class="text-center mb-4">
                        <h5 class="fw-bold mb-2">Login Akun</h5>
                        <p class="text-secondary small mb-0">Masukkan email dan password untuk melanjutkan.</p>
                    </div>

                    <?php if ($pesan_error !== ''): ?>
                        <div class="alert alert-danger d-flex align-items-start gap-2 rounded-3" role="alert">
                            <i class="bi bi-exclamation-triangle-fill fs-5"></i>
                            <div><?= htmlspecialchars((string) $pesan_error, ENT_QUOTES, 'UTF-8') ?></div>
                        </div>
                    <?php endif; ?>

                    <form action="proses_login.php" method="POST" autocomplete="on">
                        <div class="mb-3">
                            <label for="email" class="form-label fw-semibold">
                                <i class="bi bi-envelope me-1 text-primary"></i> Email
                            </label>
                            <input type="email" name="email" id="email" class="form-control form-control-lg rounded-3" placeholder="nama@email.com" autocomplete="username" maxlength="254" required autofocus>
                        </div>

                        <div class="mb-4">
                            <label for="password" class="form-label fw-semibold">
                                <i class="bi bi-key me-1 text-primary"></i> Password
                            </label>
                            <div class="input-group input-group-lg">
                                <input type="password" name="password" id="password" class="form-control rounded-start-3" placeholder="Masukkan password" autocomplete="current-password" required>
                                <button class="btn btn-outline-secondary rounded-end-3" type="button" id="tombolPassword" aria-label="Tampilkan password" aria-pressed="false" title="Tampilkan atau sembunyikan password">
                                    <i class="bi bi-eye" id="ikonPassword"></i>
                                </button>
                            </div>
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary btn-lg rounded-3 fw-semibold">
                                <i class="bi bi-box-arrow-in-right me-2"></i> Login
                            </button>
                        </div>
                    </form>

                    <hr class="my-4">

                    <div class="text-center">
                        <p class="text-secondary small mb-0">
                            <i class="bi bi-shield-check me-1"></i>
                            Akses hanya untuk pengguna terdaftar.
                        </p>
                    </div>
                </div>
            </div>

            <footer class="text-center text-secondary small mt-4">
                &copy; 2026 SI-TIB — Sistem Informasi Tata Tertib
                <div class="mt-1">Sistem Informasi Sekolah</div>
            </footer>
        </div>
    </div>
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
<script>
const inputPassword = document.getElementById('password');
const tombolPassword = document.getElementById('tombolPassword');
const ikonPassword = document.getElementById('ikonPassword');

tombolPassword.addEventListener('click', function () {
    const tampil = inputPassword.type === 'password';
    inputPassword.type = tampil ? 'text' : 'password';
    ikonPassword.className = tampil ? 'bi bi-eye-slash' : 'bi bi-eye';
    tombolPassword.setAttribute('aria-label', tampil ? 'Sembunyikan password' : 'Tampilkan password');
    tombolPassword.setAttribute('aria-pressed', tampil ? 'true' : 'false');
});
</script>

</body>
</html>