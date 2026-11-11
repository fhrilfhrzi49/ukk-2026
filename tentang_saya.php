<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/config/koneksi.php';
require_once __DIR__ . '/includes/cek_session.php';

function e($teks): string
{
    return htmlspecialchars((string) ($teks ?? ''), ENT_QUOTES, 'UTF-8');
}

$queryProfil = mysqli_query($koneksi, "SELECT * FROM t_tentang_saya WHERE id = 1 LIMIT 1");

if (!$queryProfil) {
    die('Gagal mengambil data profil: ' . e(mysqli_error($koneksi)));
}

$profil = mysqli_fetch_assoc($queryProfil);

if (!$profil) {
    die('Data Tentang Saya belum tersedia. Silakan jalankan SQL pembuatan tabel dan data awal.');
}

$nama = $profil['nama'];
$kelas = $profil['kelas'];
$sekolah = $profil['sekolah'];
$jurusan = $profil['jurusan'];
$hobi = $profil['hobi'];
$cita_cita = $profil['cita_cita'];
$deskripsi = $profil['deskripsi'];
$foto = $profil['foto'] ?: 'assets/img/profil.jpg';

$role = strtolower(trim($_SESSION['role'] ?? ''));
$role = str_replace([' ', '-'], '_', $role);
$isAdmin = in_array($role, ['admin', 'administrator'], true);

$pesanSukses = $_SESSION['pesan_sukses'] ?? '';
unset($_SESSION['pesan_sukses']);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Tentang Saya | SI-TIB</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
</head>

<body class="bg-light">
<div class="container-fluid px-0">
    <div class="row g-0">
        <?php include __DIR__ . '/layout/sidebar.php'; ?>

        <main class="col-12 col-md-9 col-lg-10 ms-md-auto p-3 p-lg-4">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-3 bg-primary-subtle text-primary p-3">
                        <i class="bi bi-person-circle fs-4"></i>
                    </div>
                    <div>
                        <h2 class="fw-bold mb-1">Tentang Saya</h2>
                        <p class="text-secondary mb-0">Profil pengembang SI-TIB — Sistem Informasi Tata Tertib.</p>
                    </div>
                </div>

                <?php if ($isAdmin): ?>
                    <a href="konfirmasi_password_tentang_saya.php" class="btn btn-primary rounded-3">
                        <i class="bi bi-pencil-square me-1"></i> Edit Tentang Saya
                    </a>
                <?php endif; ?>
            </div>

            <?php if ($pesanSukses !== ''): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="bi bi-check-circle me-2"></i>
                    <?= e($pesanSukses); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button>
                </div>
            <?php endif; ?>

            <div class="row g-4">
                <div class="col-12 col-lg-4">
                    <div class="card border-0 shadow-sm rounded-4 h-100">
                        <div class="card-body text-center p-4 p-md-5">
                            <div class="mb-3">
                                <img
                                    src="<?= e($foto); ?>"
                                    alt="Foto profil <?= e($nama); ?>"
                                    class="rounded-circle shadow-sm border border-4 border-white img-fluid"
                                    style="width: 140px; height: 140px; object-fit: cover;"
                                    onerror="this.classList.add('d-none'); document.getElementById('fotoFallback').classList.remove('d-none');"
                                >

                                <div
                                    id="fotoFallback"
                                    class="d-none rounded-circle bg-primary text-white mx-auto shadow-sm align-items-center justify-content-center"
                                    style="width: 140px; height: 140px; font-size: 5rem;"
                                >
                                    <i class="bi bi-person-fill"></i>
                                </div>
                            </div>

                            <h4 class="fw-bold mb-2"><?= e($nama); ?></h4>

                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-3 py-2">
                                <?= e($kelas); ?>
                            </span>

                            <hr class="my-4">

                            <div class="text-start">
                                <div class="text-secondary small mb-1">
                                    <i class="bi bi-mortarboard me-2"></i>Jurusan
                                </div>
                                <div class="fw-semibold"><?= e($jurusan); ?></div>

                                <div class="text-secondary small mt-3 mb-1">
                                    <i class="bi bi-building me-2"></i>Sekolah
                                </div>
                                <div class="fw-semibold"><?= e($sekolah); ?></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-lg-8">
                    <div class="card border-0 shadow-sm rounded-4 h-100">
                        <div class="card-header bg-white border-0 px-4 pt-4">
                            <h5 class="fw-bold mb-1">
                                <i class="bi bi-card-text text-primary me-2"></i>Informasi Diri
                            </h5>
                            <p class="text-secondary small mb-0">Informasi pribadi, pendidikan, dan minat.</p>
                        </div>

                        <div class="card-body p-3 p-md-4">
                            <div class="row g-3">
                                <div class="col-12">
                                    <div class="p-3 rounded-3 bg-light border h-100">
                                        <div class="d-flex align-items-start gap-3">
                                            <i class="bi bi-person-badge text-primary fs-4"></i>
                                            <div>
                                                <div class="text-secondary small">Nama Lengkap</div>
                                                <div class="fw-semibold"><?= e($nama); ?></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-12 col-md-6">
                                    <div class="p-3 rounded-3 bg-light border h-100">
                                        <div class="d-flex align-items-start gap-3">
                                            <i class="bi bi-mortarboard text-primary fs-4"></i>
                                            <div>
                                                <div class="text-secondary small">Kelas</div>
                                                <div class="fw-semibold"><?= e($kelas); ?></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-12 col-md-6">
                                    <div class="p-3 rounded-3 bg-light border h-100">
                                        <div class="d-flex align-items-start gap-3">
                                            <i class="bi bi-book text-primary fs-4"></i>
                                            <div>
                                                <div class="text-secondary small">Jurusan</div>
                                                <div class="fw-semibold"><?= e($jurusan); ?></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-12">
                                    <div class="p-3 rounded-3 bg-light border h-100">
                                        <div class="d-flex align-items-start gap-3">
                                            <i class="bi bi-building text-primary fs-4"></i>
                                            <div>
                                                <div class="text-secondary small">Sekolah</div>
                                                <div class="fw-semibold"><?= e($sekolah); ?></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-12 col-md-6">
                                    <div class="p-3 rounded-3 bg-light border h-100">
                                        <div class="d-flex align-items-start gap-3">
                                            <i class="bi bi-controller text-primary fs-4"></i>
                                            <div>
                                                <div class="text-secondary small">Hobi</div>
                                                <div class="fw-semibold"><?= e($hobi); ?></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-12 col-md-6">
                                    <div class="p-3 rounded-3 bg-light border h-100">
                                        <div class="d-flex align-items-start gap-3">
                                            <i class="bi bi-stars text-primary fs-4"></i>
                                            <div>
                                                <div class="text-secondary small">Cita-cita</div>
                                                <div class="fw-semibold"><?= e($cita_cita); ?></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-12">
                    <div class="card border-0 shadow-sm rounded-4">
                        <div class="card-body p-4 p-md-5">
                            <h5 class="fw-bold mb-3">
                                <i class="bi bi-chat-square-text text-primary me-2"></i>Deskripsi Diri
                            </h5>
                            <p class="text-secondary lh-lg mb-0">
                                <?= nl2br(e($deskripsi)); ?>
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <footer class="text-center text-secondary small mt-4 pb-2">
                &copy; <?= date('Y'); ?> SI-TIB — Sistem Informasi Tata Tertib
            </footer>
        </main>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>