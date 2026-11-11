<?php
include 'includes/cek_session.php';
include 'config/koneksi.php';

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$stmt = mysqli_prepare($koneksi, "SELECT * FROM t_kelas WHERE id = ?");
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$data = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
mysqli_stmt_close($stmt);

if (!$data) {
    echo "<script>alert('Data kelas tidak ditemukan!'); window.location='kelola_kelas.php';</script>";
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Kelas | SI-TIB</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container py-4 py-md-5">
        <div class="row justify-content-center">
            <div class="col-12 col-md-10 col-lg-8">
                <div class="mb-4">
                    <a href="kelola_kelas.php" class="btn btn-outline-secondary btn-sm mb-3">
                        <i class="bi bi-arrow-left"></i> Kembali ke Kelola Kelas
                    </a>
                    <h2 class="fw-bold mb-1"><i class="bi bi-pencil-square text-primary"></i> Edit Kelas</h2>
                    <p class="text-secondary mb-0">SI-TIB — Sistem Informasi Tata Tertib</p>
                </div>

                <div class="card border-0 shadow-sm rounded-3">
                    <div class="card-header bg-white py-3">
                        <h5 class="fw-semibold mb-0"><i class="bi bi-card-list"></i> Formulir Edit Data Kelas</h5>
                    </div>
                    <div class="card-body p-3 p-md-4">
                        <form action="proses_edit_kelas.php" method="POST">
                            <input type="hidden" name="id" value="<?= htmlspecialchars((string) $data['id'], ENT_QUOTES, 'UTF-8') ?>">

                            <div class="row g-3">
                                <div class="col-12">
                                    <label for="nama" class="form-label fw-semibold">Nama Kelas</label>
                                    <input type="text" class="form-control" id="nama" name="nama" value="<?= htmlspecialchars((string) $data['nama'], ENT_QUOTES, 'UTF-8') ?>" placeholder="Masukkan nama kelas" required>
                                </div>

                                <div class="col-12 col-md-6">
                                    <label for="tingkat" class="form-label fw-semibold">Tingkat</label>
                                    <input type="text" class="form-control" id="tingkat" name="tingkat" value="<?= htmlspecialchars((string) $data['tingkat'], ENT_QUOTES, 'UTF-8') ?>" placeholder="Contoh: X, XI, XII" required>
                                </div>

                                <div class="col-12 col-md-6">
                                    <label for="jurusan" class="form-label fw-semibold">Jurusan</label>
                                    <input type="text" class="form-control" id="jurusan" name="jurusan" value="<?= htmlspecialchars((string) $data['jurusan'], ENT_QUOTES, 'UTF-8') ?>" placeholder="Masukkan jurusan" required>
                                </div>

                                <div class="col-12">
                                    <label for="status_aktif" class="form-label fw-semibold">Status Aktif</label>
                                    <select class="form-select" id="status_aktif" name="status_aktif" required>
                                        <option value="1" <?= $data['status_aktif'] == 1 ? 'selected' : '' ?>>Aktif</option>
                                        <option value="0" <?= $data['status_aktif'] == 0 ? 'selected' : '' ?>>Tidak Aktif</option>
                                    </select>
                                </div>
                            </div>

                            <hr class="my-4">

                            <div class="d-flex flex-column flex-sm-row gap-2 justify-content-end">
                                <a href="kelola_kelas.php" class="btn btn-outline-secondary">
                                    <i class="bi bi-x-circle"></i> Batal
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-save"></i> Update Data Kelas
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <footer class="text-center text-secondary small mt-4">
                    &copy; <?= date('Y') ?> SI-TIB — Sistem Informasi Tata Tertib
                </footer>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>