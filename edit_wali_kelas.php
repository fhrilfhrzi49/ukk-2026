<?php
include 'includes/cek_session.php';
include 'config/koneksi.php';

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$stmt = mysqli_prepare($koneksi, "SELECT * FROM t_wali_kelas WHERE id = ?");
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$data = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
mysqli_stmt_close($stmt);

if (!$data) {
    echo "<script>alert('Data wali kelas tidak ditemukan!'); window.location='kelola_wali_kelas.php';</script>";
    exit;
}

$guru = mysqli_query($koneksi, "SELECT id, nip, nama FROM t_guru ORDER BY nama ASC");
$kelas = mysqli_query($koneksi, "SELECT id, nama, tingkat, jurusan FROM t_kelas ORDER BY tingkat ASC, nama ASC");
$tahunAjaran = mysqli_query($koneksi, "SELECT id, nama FROM t_tahun_ajaran ORDER BY nama DESC");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Wali Kelas | SI-TIB</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container py-4 py-md-5">
        <div class="row justify-content-center">
            <div class="col-12 col-md-10 col-lg-8">
                <div class="mb-4">
                    <a href="kelola_wali_kelas.php" class="btn btn-outline-secondary btn-sm mb-3">
                        <i class="bi bi-arrow-left"></i> Kembali ke Kelola Wali Kelas
                    </a>
                    <h2 class="fw-bold mb-1"><i class="bi bi-person-gear text-primary"></i> Edit Wali Kelas</h2>
                    <p class="text-secondary mb-0">SI-TIB — Sistem Informasi Tata Tertib</p>
                </div>

                <div class="card border-0 shadow-sm rounded-3">
                    <div class="card-header bg-white py-3">
                        <h5 class="fw-semibold mb-0"><i class="bi bi-card-list"></i> Formulir Edit Data Wali Kelas</h5>
                    </div>

                    <div class="card-body p-3 p-md-4">
                        <form action="proses_edit_wali_kelas.php" method="POST">
                            <input type="hidden" name="id" value="<?= htmlspecialchars((string) $data['id'], ENT_QUOTES, 'UTF-8') ?>">

                            <div class="row g-3">
                                <div class="col-12">
                                    <label for="guru_id" class="form-label fw-semibold">Guru</label>
                                    <select class="form-select" id="guru_id" name="guru_id" required>
                                        <option value="">Pilih Guru</option>
                                        <?php while ($row = mysqli_fetch_assoc($guru)): ?>
                                            <option value="<?= (int) $row['id'] ?>" <?= (int) $data['guru_id'] === (int) $row['id'] ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($row['nama'], ENT_QUOTES, 'UTF-8') ?>
                                                (<?= htmlspecialchars($row['nip'], ENT_QUOTES, 'UTF-8') ?>)
                                            </option>
                                        <?php endwhile; ?>
                                    </select>
                                </div>

                                <div class="col-12">
                                    <label for="kelas_id" class="form-label fw-semibold">Kelas</label>
                                    <select class="form-select" id="kelas_id" name="kelas_id" required>
                                        <option value="">Pilih Kelas</option>
                                        <?php while ($row = mysqli_fetch_assoc($kelas)): ?>
                                            <option value="<?= (int) $row['id'] ?>" <?= (int) $data['kelas_id'] === (int) $row['id'] ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($row['nama'], ENT_QUOTES, 'UTF-8') ?>
                                                - <?= htmlspecialchars($row['tingkat'], ENT_QUOTES, 'UTF-8') ?>
                                                <?= !empty($row['jurusan']) ? ' ' . htmlspecialchars($row['jurusan'], ENT_QUOTES, 'UTF-8') : '' ?>
                                            </option>
                                        <?php endwhile; ?>
                                    </select>
                                </div>

                                <div class="col-12">
                                    <label for="tahun_ajaran_id" class="form-label fw-semibold">Tahun Ajaran</label>
                                    <select class="form-select" id="tahun_ajaran_id" name="tahun_ajaran_id" required>
                                        <option value="">Pilih Tahun Ajaran</option>
                                        <?php while ($row = mysqli_fetch_assoc($tahunAjaran)): ?>
                                            <option value="<?= (int) $row['id'] ?>" <?= (int) $data['tahun_ajaran_id'] === (int) $row['id'] ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($row['nama'], ENT_QUOTES, 'UTF-8') ?>
                                            </option>
                                        <?php endwhile; ?>
                                    </select>
                                </div>

                                <div class="col-12 col-md-6">
                                    <label for="tanggal_mulai" class="form-label fw-semibold">Tanggal Mulai</label>
                                    <input type="date" class="form-control" id="tanggal_mulai" name="tanggal_mulai" value="<?= htmlspecialchars((string) ($data['tanggal_mulai'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" required>
                                </div>

                                <div class="col-12 col-md-6">
                                    <label for="tanggal_selesai" class="form-label fw-semibold">Tanggal Selesai</label>
                                    <input type="date" class="form-control" id="tanggal_selesai" name="tanggal_selesai" value="<?= htmlspecialchars((string) ($data['tanggal_selesai'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                                </div>

                                <div class="col-12 col-md-6">
                                    <label for="status_aktif" class="form-label fw-semibold">Status Aktif</label>
                                    <select class="form-select" id="status_aktif" name="status_aktif" required>
                                        <option value="1" <?= $data['status_aktif'] == 1 ? 'selected' : '' ?>>Aktif</option>
                                        <option value="0" <?= $data['status_aktif'] == 0 ? 'selected' : '' ?>>Tidak Aktif</option>
                                    </select>
                                </div>
                            </div>

                            <hr class="my-4">

                            <div class="d-flex flex-column flex-sm-row gap-2 justify-content-end">
                                <a href="kelola_wali_kelas.php" class="btn btn-outline-secondary">
                                    <i class="bi bi-x-circle"></i> Batal
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-save"></i> Update Data Wali Kelas
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