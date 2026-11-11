<?php
require_once __DIR__ . '/config/koneksi.php';
require_once __DIR__ . '/includes/cek_session.php';
require_once __DIR__ . '/config/akses.php';

wajib_role(['admin', 'administrator']);

if (session_status() === PHP_SESSION_NONE) session_start();

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

function e($value): string {
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$id || $id < 1) die('ID kategori tidak valid.');

$stmt = $koneksi->prepare("SELECT id, nama, deskripsi, status_aktif FROM t_pelanggaran_kategori WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$kategori = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$kategori) die('Data kategori pelanggaran tidak ditemukan.');

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['csrf_token'] ?? '';

    if (!hash_equals($_SESSION['csrf_token'], $token)) {
        $errors[] = 'Token keamanan tidak valid. Silakan muat ulang halaman.';
    } else {
        $nama = trim($_POST['nama'] ?? '');
        $deskripsi = trim($_POST['deskripsi'] ?? '');
        $status_aktif = isset($_POST['status_aktif']) ? 1 : 0;

        if ($nama === '') {
            $errors[] = 'Nama kategori wajib diisi.';
        } elseif (strlen($nama) > 100) {
            $errors[] = 'Nama kategori maksimal 100 karakter.';
        }

        if ($deskripsi === '') $errors[] = 'Deskripsi kategori wajib diisi.';

        if (empty($errors)) {
            $update = $koneksi->prepare("
                UPDATE t_pelanggaran_kategori
                SET nama = ?, deskripsi = ?, status_aktif = ?, updated_at = CURRENT_TIMESTAMP
                WHERE id = ?
            ");

            $update->bind_param("ssii", $nama, $deskripsi, $status_aktif, $id);

            if ($update->execute()) {
                $update->close();
                header("Location: kelola_pelanggaran_kategori.php?success=1");
                exit;
            }

            $errors[] = 'Gagal memperbarui kategori.';
            $update->close();
        }

        $kategori['nama'] = $nama;
        $kategori['deskripsi'] = $deskripsi;
        $kategori['status_aktif'] = $status_aktif;
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Kategori Pelanggaran | SI-TIB</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<?php require_once __DIR__ . '/layout/sidebar.php'; ?>

<main class="container-fluid py-4 px-3 px-md-4">
    <div class="row">
        <div class="col-12 col-md-9 offset-md-3 col-lg-10 offset-lg-2">

            <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
                <div>
                    <h4 class="fw-bold mb-1">
                        <i class="bi bi-pencil-square me-2 text-primary"></i>Edit Kategori Pelanggaran
                    </h4>
                    <p class="text-secondary mb-0">Master Data / Kategori Pelanggaran / Edit</p>
                </div>
                <a href="kelola_pelanggaran_kategori.php" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left me-1"></i>Kembali
                </a>
            </div>

            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <strong>
                        <i class="bi bi-exclamation-triangle-fill me-2"></i>Terjadi Kesalahan
                    </strong>
                    <ul class="mb-0 mt-2">
                        <?php foreach ($errors as $error): ?>
                            <li><?= e($error) ?></li>
                        <?php endforeach; ?>
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom py-3">
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi bi-card-list text-primary fs-5"></i>
                        <div>
                            <h6 class="fw-bold mb-0">Form Edit Kategori</h6>
                            <small class="text-secondary">Ubah informasi kategori pelanggaran siswa.</small>
                        </div>
                    </div>
                </div>

                <div class="card-body p-4">
                    <form method="POST">
                        <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token']) ?>">

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold">ID Kategori</label>
                                <input type="text" class="form-control bg-light" value="<?= e($kategori['id']) ?>" readonly>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold">
                                    Nama Kategori <span class="text-danger">*</span>
                                </label>
                                <input type="text" name="nama" class="form-control" maxlength="100" value="<?= e($kategori['nama']) ?>" placeholder="Masukkan nama kategori" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">
                                Deskripsi <span class="text-danger">*</span>
                            </label>
                            <textarea name="deskripsi" class="form-control" rows="4" placeholder="Masukkan deskripsi kategori" required><?= e($kategori['deskripsi']) ?></textarea>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-semibold">Status Kategori</label>
                            <div class="form-check form-switch">
                                <input type="checkbox" class="form-check-input" role="switch" name="status_aktif" id="status_aktif" value="1" <?= (int) $kategori['status_aktif'] === 1 ? 'checked' : '' ?>>
                                <label class="form-check-label" for="status_aktif">Aktif</label>
                            </div>
                            <small class="text-secondary">Aktifkan atau nonaktifkan kategori pelanggaran.</small>
                        </div>

                        <hr>

                        <div class="d-flex justify-content-end flex-wrap gap-2">
                            <a href="kelola_pelanggaran_kategori.php" class="btn btn-light border">
                                <i class="bi bi-x-circle me-1"></i>Batal
                            </a>
                            <button type="reset" class="btn btn-outline-secondary">
                                <i class="bi bi-arrow-counterclockwise me-1"></i>Reset
                            </button>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save me-1"></i>Simpan Perubahan
                            </button>
                        </div>
                    </form>
                </div>
            </div>

        </div>
    </div>
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>