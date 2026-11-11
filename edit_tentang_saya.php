<?php
if (session_status() === PHP_SESSION_NONE) session_start();

require_once __DIR__ . '/config/koneksi.php';
require_once __DIR__ . '/config/akses.php';

if (empty($_SESSION['login']) || $_SESSION['login'] !== true) {
    header('Location: /ukk-2026/login.php');
    exit;
}

$role = str_replace([' ', '-'], '_', strtolower(trim($_SESSION['role'] ?? '')));

if (!in_array($role, ['admin', 'administrator'], true)) {
    http_response_code(403);
    exit('403 - Akses ditolak. Hanya admin yang dapat mengubah data Tentang Saya.');
}

if (
    empty($_SESSION['konfirmasi_edit_tentang_saya']) ||
    !is_numeric($_SESSION['konfirmasi_edit_tentang_saya']) ||
    time() - (int) $_SESSION['konfirmasi_edit_tentang_saya'] > 600
) {
    unset($_SESSION['konfirmasi_edit_tentang_saya']);
    header('Location: konfirmasi_password_tentang_saya.php');
    exit;
}

function e($teks): string
{
    return htmlspecialchars((string) ($teks ?? ''), ENT_QUOTES, 'UTF-8');
}

if (empty($_SESSION['csrf_tentang_saya'])) {
    $_SESSION['csrf_tentang_saya'] = bin2hex(random_bytes(32));
}

$queryProfil = mysqli_query($koneksi, "SELECT * FROM t_tentang_saya WHERE id = 1 LIMIT 1");

if (!$queryProfil) {
    die('Gagal mengambil data profil: ' . e(mysqli_error($koneksi)));
}

$profil = mysqli_fetch_assoc($queryProfil);

if (!$profil) {
    die('Data Tentang Saya tidak ditemukan. Silakan periksa data awal tabel t_tentang_saya.');
}

$pesanError = $_SESSION['pesan_error'] ?? '';
unset($_SESSION['pesan_error']);

$foto = $profil['foto'] ?: 'assets/img/profil.jpg';
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Edit Tentang Saya | SI-TIB</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container-fluid px-0">
    <div class="row g-0">
        <?php include __DIR__ . '/layout/sidebar.php'; ?>

        <main class="col-12 col-md-9 col-lg-10 ms-md-auto p-3 p-lg-4">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
                <div>
                    <h2 class="fw-bold mb-1">
                        <i class="bi bi-pencil-square text-primary me-2"></i>
                        Edit Tentang Saya
                    </h2>
                    <p class="text-secondary mb-0">Perbarui informasi profil pengembang SI-TIB.</p>
                </div>
                <a href="tentang_saya.php" class="btn btn-outline-secondary rounded-3">
                    <i class="bi bi-arrow-left me-1"></i> Kembali
                </a>
            </div>

            <?php if ($pesanError !== ''): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="bi bi-exclamation-circle me-2"></i>
                    <?= e($pesanError); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button>
                </div>
            <?php endif; ?>

            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-header bg-white border-0 p-4">
                    <h5 class="fw-bold mb-1">
                        <i class="bi bi-person-lines-fill text-primary me-2"></i>
                        Informasi Profil
                    </h5>
                    <p class="text-secondary small mb-0">Lengkapi informasi profil di bawah ini.</p>
                </div>

                <div class="card-body p-4">
                    <form action="proses_edit_tentang_saya.php" method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_tentang_saya']); ?>">

                        <div class="row g-3">
                            <div class="col-12 text-center mb-3">
                                <img
                                    src="<?= e($foto); ?>"
                                    alt="Foto profil"
                                    id="previewFoto"
                                    class="rounded-circle border shadow-sm mb-3"
                                    width="150"
                                    height="150"
                                    style="object-fit: cover;"
                                    onerror="this.src='assets/img/profil.jpg';"
                                >

                                <div class="col-md-6 mx-auto text-start">
                                    <label for="foto" class="form-label fw-semibold">Foto Profil</label>
                                    <input type="file" class="form-control" id="foto" name="foto" accept="image/jpeg,image/png,image/webp">
                                    <div class="form-text">Format JPG, PNG, atau WEBP. Maksimal 2 MB.</div>
                                </div>
                            </div>

                            <?php
                            $fields = [
                                ['nama', 'Nama Lengkap', 150],
                                ['kelas', 'Kelas', 100],
                                ['sekolah', 'Nama Sekolah', 150],
                                ['jurusan', 'Jurusan', 150],
                                ['hobi', 'Hobi', 255],
                                ['cita_cita', 'Cita-cita', 255]
                            ];
                            ?>

                            <?php foreach ($fields as [$field, $label, $maxlength]): ?>
                                <div class="col-md-6">
                                    <label for="<?= e($field); ?>" class="form-label fw-semibold"><?= e($label); ?></label>
                                    <input
                                        type="text"
                                        class="form-control"
                                        id="<?= e($field); ?>"
                                        name="<?= e($field); ?>"
                                        value="<?= e($profil[$field]); ?>"
                                        maxlength="<?= $maxlength; ?>"
                                        required
                                    >
                                </div>
                            <?php endforeach; ?>

                            <div class="col-12">
                                <label for="deskripsi" class="form-label fw-semibold">Deskripsi Diri</label>
                                <textarea class="form-control" id="deskripsi" name="deskripsi" rows="5" required><?= e($profil['deskripsi']); ?></textarea>
                            </div>
                        </div>

                        <hr class="my-4">

                        <div class="d-flex flex-wrap justify-content-end gap-2">
                            <a href="tentang_saya.php" class="btn btn-light border rounded-3">Batal</a>
                            <button type="submit" class="btn btn-primary rounded-3">
                                <i class="bi bi-save me-1"></i> Simpan Perubahan
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <footer class="text-center text-secondary small mt-4">
                &copy; <?= date('Y'); ?> SI-TIB — Sistem Informasi Tata Tertib
            </footer>
        </main>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.getElementById('foto').addEventListener('change', function(event) {
    const file = event.target.files[0];
    if (file) document.getElementById('previewFoto').src = URL.createObjectURL(file);
});
</script>
</body>
</html>