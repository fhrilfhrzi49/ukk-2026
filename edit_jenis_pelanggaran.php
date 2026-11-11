<?php
require_once __DIR__ . '/config/akses.php';
wajib_role(['admin', 'administrator']);

require_once __DIR__ . '/includes/cek_session.php';
require_once __DIR__ . '/config/koneksi.php';

if (session_status() === PHP_SESSION_NONE) session_start();

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

if (!function_exists('e')) {
    function e($nilai): string
    {
        return htmlspecialchars((string) ($nilai ?? ''), ENT_QUOTES, 'UTF-8');
    }
}

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$id || $id <= 0) {
    $_SESSION['pesan_error'] = 'ID jenis pelanggaran tidak valid.';
    header('Location: kelola_pelanggaran_kategori.php');
    exit;
}

$stmt = mysqli_prepare($koneksi, "SELECT id, kategori_id, nama_pelanggaran, deskripsi, poin, status_aktif FROM t_jenis_pelanggaran WHERE id = ? LIMIT 1");

if (!$stmt) die('Gagal menyiapkan query data.');

mysqli_stmt_bind_param($stmt, 'i', $id);
mysqli_stmt_execute($stmt);
$data = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
mysqli_stmt_close($stmt);

if (!$data) {
    $_SESSION['pesan_error'] = 'Jenis pelanggaran tidak ditemukan.';
    header('Location: kelola_pelanggaran_kategori.php');
    exit;
}

$kategori = mysqli_query(
    $koneksi,
    "SELECT id, nama FROM t_pelanggaran_kategori WHERE status_aktif = 1 OR id = " . (int) $data['kategori_id'] . " ORDER BY nama ASC"
);

if (!$kategori) {
    die('Gagal mengambil data kategori: ' . e(mysqli_error($koneksi)));
}

$errors = [];
$kategori_id = (string) $data['kategori_id'];
$nama_pelanggaran = $data['nama_pelanggaran'];
$deskripsi = $data['deskripsi'];
$poin = (string) $data['poin'];
$status_aktif = (string) $data['status_aktif'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['csrf_token'] ?? '';

    if (!is_string($token) || !hash_equals($_SESSION['csrf_token'], $token)) {
        $errors[] = 'Token keamanan tidak valid. Silakan muat ulang halaman.';
    }

    $kategori_id = (string) (int) ($_POST['kategori_id'] ?? 0);
    $nama_pelanggaran = trim($_POST['nama_pelanggaran'] ?? '');
    $deskripsi = trim($_POST['deskripsi'] ?? '');
    $poin = trim($_POST['poin'] ?? '');
    $status_aktif = $_POST['status_aktif'] ?? '1';

    if ((int) $kategori_id <= 0) {
        $errors[] = 'Kategori pelanggaran wajib dipilih.';
    }

    if ($nama_pelanggaran === '') {
        $errors[] = 'Nama jenis pelanggaran wajib diisi.';
    } elseif (mb_strlen($nama_pelanggaran) > 150) {
        $errors[] = 'Nama pelanggaran maksimal 150 karakter.';
    }

    if ($poin === '' || !ctype_digit($poin)) {
        $errors[] = 'Poin pelanggaran wajib diisi dengan angka bulat.';
    } elseif ((int) $poin < 0 || (int) $poin > 100000) {
        $errors[] = 'Poin pelanggaran harus antara 0 sampai 100000.';
    }

    if (!in_array($status_aktif, ['0', '1'], true)) {
        $errors[] = 'Status pelanggaran tidak valid.';
    }

    if ((int) $kategori_id > 0) {
        $stmtKategori = mysqli_prepare(
            $koneksi,
            "SELECT id FROM t_pelanggaran_kategori WHERE id = ? AND (status_aktif = 1 OR id = ?) LIMIT 1"
        );

        if (!$stmtKategori) {
            $errors[] = 'Gagal memeriksa kategori.';
        } else {
            $kategoriInt = (int) $kategori_id;
            mysqli_stmt_bind_param($stmtKategori, 'ii', $kategoriInt, $id);
            mysqli_stmt_execute($stmtKategori);
            $resultKategori = mysqli_stmt_get_result($stmtKategori);

            if (!$resultKategori || mysqli_num_rows($resultKategori) === 0) {
                $errors[] = 'Kategori tidak ditemukan atau sudah tidak aktif.';
            }

            mysqli_stmt_close($stmtKategori);
        }
    }

    if (empty($errors)) {
        $kategoriInt = (int) $kategori_id;
        $stmtCek = mysqli_prepare(
            $koneksi,
            "SELECT id FROM t_jenis_pelanggaran WHERE kategori_id = ? AND LOWER(nama_pelanggaran) = LOWER(?) AND id <> ? LIMIT 1"
        );

        if (!$stmtCek) {
            $errors[] = 'Gagal memeriksa nama jenis pelanggaran.';
        } else {
            mysqli_stmt_bind_param($stmtCek, 'isi', $kategoriInt, $nama_pelanggaran, $id);
            mysqli_stmt_execute($stmtCek);
            $resultCek = mysqli_stmt_get_result($stmtCek);

            if ($resultCek && mysqli_num_rows($resultCek) > 0) {
                $errors[] = 'Nama jenis pelanggaran sudah digunakan pada kategori tersebut.';
            }

            mysqli_stmt_close($stmtCek);
        }
    }

    if (empty($errors)) {
        $kategoriInt = (int) $kategori_id;
        $poinInt = (int) $poin;
        $statusInt = (int) $status_aktif;

        $stmtUpdate = mysqli_prepare(
            $koneksi,
            "UPDATE t_jenis_pelanggaran SET kategori_id = ?, nama_pelanggaran = ?, deskripsi = ?, poin = ?, status_aktif = ? WHERE id = ?"
        );

        if (!$stmtUpdate) {
            $errors[] = 'Gagal menyiapkan perubahan data.';
        } else {
            mysqli_stmt_bind_param(
                $stmtUpdate,
                'issiii',
                $kategoriInt,
                $nama_pelanggaran,
                $deskripsi,
                $poinInt,
                $statusInt,
                $id
            );

            if (mysqli_stmt_execute($stmtUpdate)) {
                mysqli_stmt_close($stmtUpdate);
                $_SESSION['pesan_sukses'] = 'Jenis pelanggaran berhasil diperbarui.';
                header('Location: kelola_pelanggaran_kategori.php');
                exit;
            }

            $errors[] = 'Gagal memperbarui data: ' . mysqli_stmt_error($stmtUpdate);
            mysqli_stmt_close($stmtUpdate);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Edit Jenis Pelanggaran | SI-TIB</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container-fluid px-0">
    <div class="row g-0">
        <?php include __DIR__ . '/layout/sidebar.php'; ?>

        <main class="col-12 col-md-9 col-lg-10 ms-md-auto p-3 p-lg-4">
            <div class="mb-4">
                <a href="kelola_pelanggaran_kategori.php" class="btn btn-outline-secondary btn-sm mb-3">
                    <i class="bi bi-arrow-left me-1"></i> Kembali
                </a>

                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-3 bg-warning-subtle text-warning p-3">
                        <i class="bi bi-pencil-square fs-4"></i>
                    </div>
                    <div>
                        <h3 class="fw-bold mb-1">Edit Jenis Pelanggaran</h3>
                        <p class="text-secondary mb-0">Perbarui informasi kategori, jenis pelanggaran, dan poin.</p>
                    </div>
                </div>
            </div>

            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger alert-dismissible fade show rounded-3">
                    <div class="fw-bold mb-2">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i>
                        Periksa kembali data berikut:
                    </div>
                    <ul class="mb-0">
                        <?php foreach ($errors as $error): ?>
                            <li><?= e($error); ?></li>
                        <?php endforeach; ?>
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-header bg-white border-0 px-3 px-md-4 pt-4">
                    <h5 class="fw-bold mb-1">
                        <i class="bi bi-clipboard2-check text-warning me-2"></i>
                        Form Edit Jenis Pelanggaran
                    </h5>
                    <p class="text-secondary small mb-0">ID Jenis Pelanggaran: #<?= (int) $id; ?></p>
                </div>

                <div class="card-body p-3 p-md-4">
                    <form method="POST" action="">
                        <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token']); ?>">

                        <div class="row g-3">
                            <div class="col-12">
                                <label for="kategori_id" class="form-label fw-semibold">
                                    Kategori Pelanggaran <span class="text-danger">*</span>
                                </label>
                                <select name="kategori_id" id="kategori_id" class="form-select" required>
                                    <option value="">-- Pilih Kategori Pelanggaran --</option>
                                    <?php while ($rowKategori = mysqli_fetch_assoc($kategori)): ?>
                                        <option value="<?= (int) $rowKategori['id']; ?>" <?= $kategori_id === (string) $rowKategori['id'] ? 'selected' : ''; ?>>
                                            <?= e($rowKategori['nama']); ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>

                            <div class="col-12">
                                <label for="nama_pelanggaran" class="form-label fw-semibold">
                                    Nama Jenis Pelanggaran <span class="text-danger">*</span>
                                </label>
                                <input
                                    type="text"
                                    name="nama_pelanggaran"
                                    id="nama_pelanggaran"
                                    class="form-control"
                                    maxlength="150"
                                    value="<?= e($nama_pelanggaran); ?>"
                                    placeholder="Contoh: Terlambat masuk sekolah"
                                    required
                                >
                            </div>

                            <div class="col-12">
                                <label for="deskripsi" class="form-label fw-semibold">Deskripsi</label>
                                <textarea
                                    name="deskripsi"
                                    id="deskripsi"
                                    class="form-control"
                                    rows="4"
                                    placeholder="Masukkan penjelasan jenis pelanggaran..."
                                ><?= e($deskripsi); ?></textarea>
                            </div>

                            <div class="col-md-6">
                                <label for="poin" class="form-label fw-semibold">
                                    Poin Pelanggaran <span class="text-danger">*</span>
                                </label>
                                <div class="input-group">
                                    <input
                                        type="number"
                                        name="poin"
                                        id="poin"
                                        class="form-control"
                                        min="0"
                                        max="100000"
                                        step="1"
                                        value="<?= e($poin); ?>"
                                        required
                                    >
                                    <span class="input-group-text">Poin</span>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label for="status_aktif" class="form-label fw-semibold">
                                    Status <span class="text-danger">*</span>
                                </label>
                                <select name="status_aktif" id="status_aktif" class="form-select" required>
                                    <option value="1" <?= $status_aktif === '1' ? 'selected' : ''; ?>>Aktif</option>
                                    <option value="0" <?= $status_aktif === '0' ? 'selected' : ''; ?>>Tidak Aktif</option>
                                </select>
                            </div>
                        </div>

                        <hr class="my-4">

                        <div class="d-flex flex-column flex-sm-row justify-content-end gap-2">
                            <a href="kelola_pelanggaran_kategori.php" class="btn btn-light border">
                                <i class="bi bi-x-circle me-1"></i> Batal
                            </a>
                            <button type="submit" class="btn btn-warning">
                                <i class="bi bi-save me-1"></i> Simpan Perubahan
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <footer class="text-center text-secondary small mt-4 pb-3">
                SI-TIB — Sistem Informasi Tata Tertib &copy; 2026
            </footer>
        </main>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>