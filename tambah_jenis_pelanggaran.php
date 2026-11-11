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
    function e($nilai): string {
        return htmlspecialchars((string) ($nilai ?? ''), ENT_QUOTES, 'UTF-8');
    }
}

$kategori = mysqli_query($koneksi, "SELECT id, nama FROM t_pelanggaran_kategori WHERE status_aktif = 1 ORDER BY nama ASC");

$errors = [];
$kategori_id = '';
$nama_pelanggaran = '';
$deskripsi = '';
$poin = '';
$status_aktif = '1';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
        $errors[] = 'Token keamanan tidak valid. Silakan muat ulang halaman.';
    }

    $kategori_id = (int) ($_POST['kategori_id'] ?? 0);
    $nama_pelanggaran = trim($_POST['nama_pelanggaran'] ?? '');
    $deskripsi = trim($_POST['deskripsi'] ?? '');
    $poin = trim($_POST['poin'] ?? '');
    $status_aktif = $_POST['status_aktif'] ?? '1';

    if ($kategori_id <= 0) $errors[] = 'Kategori pelanggaran wajib dipilih.';

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

    if ($kategori_id > 0) {
        $stmt = mysqli_prepare($koneksi, "SELECT id FROM t_pelanggaran_kategori WHERE id = ? AND status_aktif = 1 LIMIT 1");
        mysqli_stmt_bind_param($stmt, 'i', $kategori_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        if (!$result || mysqli_num_rows($result) === 0) {
            $errors[] = 'Kategori tidak ditemukan atau sudah tidak aktif.';
        }

        mysqli_stmt_close($stmt);
    }

    if (empty($errors)) {
        $stmt = mysqli_prepare($koneksi, "SELECT id FROM t_jenis_pelanggaran WHERE kategori_id = ? AND LOWER(nama_pelanggaran) = LOWER(?) LIMIT 1");
        mysqli_stmt_bind_param($stmt, 'is', $kategori_id, $nama_pelanggaran);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        if ($result && mysqli_num_rows($result) > 0) {
            $errors[] = 'Jenis pelanggaran tersebut sudah tersedia pada kategori yang dipilih.';
        }

        mysqli_stmt_close($stmt);
    }

    if (empty($errors)) {
        $poinInt = (int) $poin;
        $statusInt = (int) $status_aktif;

        $stmt = mysqli_prepare($koneksi, "INSERT INTO t_jenis_pelanggaran (kategori_id, nama_pelanggaran, deskripsi, poin, status_aktif) VALUES (?, ?, ?, ?, ?)");

        if (!$stmt) {
            $errors[] = 'Gagal menyiapkan penyimpanan data.';
        } else {
            mysqli_stmt_bind_param($stmt, 'issii', $kategori_id, $nama_pelanggaran, $deskripsi, $poinInt, $statusInt);

            if (mysqli_stmt_execute($stmt)) {
                mysqli_stmt_close($stmt);
                $_SESSION['pesan_sukses'] = 'Jenis pelanggaran berhasil ditambahkan.';
                header('Location: kelola_pelanggaran_kategori.php');
                exit;
            }

            $errors[] = 'Gagal menyimpan data: ' . mysqli_stmt_error($stmt);
            mysqli_stmt_close($stmt);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Tambah Jenis Pelanggaran | SI-TIB</title>
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
                    <i class="bi bi-arrow-left me-1"></i>Kembali
                </a>

                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-3 bg-success-subtle text-success p-3">
                        <i class="bi bi-plus-circle fs-4"></i>
                    </div>
                    <div>
                        <h3 class="fw-bold mb-1">Tambah Jenis Pelanggaran</h3>
                        <p class="text-secondary mb-0">Tambahkan jenis pelanggaran beserta poinnya.</p>
                    </div>
                </div>
            </div>

            <div class="alert alert-info border-0 rounded-3 mb-4">
                <i class="bi bi-info-circle-fill me-2"></i>
                Pilih kategori terlebih dahulu, kemudian isi nama pelanggaran dan jumlah poin.
                Data ini akan digunakan pada halaman Catat Pelanggaran.
            </div>

            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger alert-dismissible fade show rounded-3">
                    <div class="fw-bold mb-2">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i>
                        Data belum dapat disimpan.
                    </div>
                    <ul class="mb-0">
                        <?php foreach ($errors as $error): ?>
                            <li><?= e($error) ?></li>
                        <?php endforeach; ?>
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-header bg-white border-0 px-3 px-md-4 pt-4">
                    <h5 class="fw-bold mb-1">
                        <i class="bi bi-clipboard2-plus text-success me-2"></i>Form Jenis Pelanggaran
                    </h5>
                    <p class="text-secondary small mb-0">Lengkapi informasi jenis pelanggaran berikut.</p>
                </div>

                <div class="card-body p-3 p-md-4">
                    <?php if (!$kategori): ?>
                        <div class="alert alert-danger mb-0">
                            Gagal mengambil kategori: <?= e(mysqli_error($koneksi)) ?>
                        </div>

                    <?php elseif (mysqli_num_rows($kategori) === 0): ?>
                        <div class="text-center py-4">
                            <i class="bi bi-folder-x display-4 text-secondary"></i>
                            <h5 class="fw-bold mt-3">Belum Ada Kategori Aktif</h5>
                            <p class="text-secondary">
                                Buat kategori pelanggaran terlebih dahulu sebelum menambahkan jenis pelanggaran.
                            </p>
                            <a href="tambah_pelanggaran_kategori.php" class="btn btn-primary">
                                <i class="bi bi-plus-circle me-1"></i>Tambah Kategori
                            </a>
                        </div>

                    <?php else: ?>
                        <form method="POST" action="">
                            <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token']) ?>">

                            <div class="row g-3">
                                <div class="col-12">
                                    <label for="kategori_id" class="form-label fw-semibold">
                                        Kategori Pelanggaran <span class="text-danger">*</span>
                                    </label>
                                    <select name="kategori_id" id="kategori_id" class="form-select" required>
                                        <option value="">-- Pilih Kategori Pelanggaran --</option>
                                        <?php while ($rowKategori = mysqli_fetch_assoc($kategori)): ?>
                                            <option value="<?= (int) $rowKategori['id'] ?>" <?= (string) $kategori_id === (string) $rowKategori['id'] ? 'selected' : '' ?>>
                                                <?= e($rowKategori['nama']) ?>
                                            </option>
                                        <?php endwhile; ?>
                                    </select>
                                    <div class="form-text">Pilih kelompok kategori yang sesuai dengan pelanggaran.</div>
                                </div>

                                <div class="col-12">
                                    <label for="nama_pelanggaran" class="form-label fw-semibold">
                                        Nama Jenis Pelanggaran <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" name="nama_pelanggaran" id="nama_pelanggaran" class="form-control" maxlength="150" placeholder="Contoh: Terlambat masuk sekolah" value="<?= e($nama_pelanggaran) ?>" required>
                                    <div class="form-text">Masukkan tindakan pelanggaran secara spesifik.</div>
                                </div>

                                <div class="col-12">
                                    <label for="deskripsi" class="form-label fw-semibold">Deskripsi</label>
                                    <textarea name="deskripsi" id="deskripsi" class="form-control" rows="4" placeholder="Masukkan penjelasan jenis pelanggaran..."><?= e($deskripsi) ?></textarea>
                                </div>

                                <div class="col-md-6">
                                    <label for="poin" class="form-label fw-semibold">
                                        Poin Pelanggaran <span class="text-danger">*</span>
                                    </label>
                                    <div class="input-group">
                                        <input type="number" name="poin" id="poin" class="form-control" min="0" max="100000" step="1" placeholder="Contoh: 10" value="<?= e($poin) ?>" required>
                                        <span class="input-group-text">Poin</span>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <label for="status_aktif" class="form-label fw-semibold">
                                        Status <span class="text-danger">*</span>
                                    </label>
                                    <select name="status_aktif" id="status_aktif" class="form-select" required>
                                        <option value="1" <?= $status_aktif === '1' ? 'selected' : '' ?>>Aktif</option>
                                        <option value="0" <?= $status_aktif === '0' ? 'selected' : '' ?>>Tidak Aktif</option>
                                    </select>
                                </div>
                            </div>

                            <hr class="my-4">

                            <div class="d-flex flex-column flex-sm-row justify-content-end gap-2">
                                <a href="kelola_pelanggaran_kategori.php" class="btn btn-light border">
                                    <i class="bi bi-x-circle me-1"></i>Batal
                                </a>
                                <button type="submit" class="btn btn-success">
                                    <i class="bi bi-save me-1"></i>Simpan Jenis Pelanggaran
                                </button>
                            </div>
                        </form>
                    <?php endif; ?>
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