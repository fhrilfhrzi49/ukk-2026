<?php
include 'includes/cek_session.php';
include 'config/koneksi.php';

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$stmt = mysqli_prepare($koneksi, "SELECT * FROM t_siswa WHERE id = ?");
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$data = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

if (!$data) {
    echo "<script>alert('Data siswa tidak ditemukan!'); window.location='kelola_siswa.php';</script>";
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Siswa | SI-TIB</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-4 py-md-5">
    <div class="row justify-content-center">
        <div class="col-12 col-md-10 col-lg-8">
            <div class="d-flex align-items-center gap-3 mb-4">
                <div class="bg-primary text-white rounded-3 p-3">
                    <i class="bi bi-person-vcard fs-3"></i>
                </div>
                <div>
                    <h1 class="h3 mb-1">Edit Siswa</h1>
                    <p class="text-secondary mb-0">SI-TIB — Sistem Informasi Tata Tertib</p>
                </div>
            </div>

            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-header bg-white border-0 px-4 pt-4">
                    <h2 class="h5 mb-1">Formulir Edit Data Siswa</h2>
                    <p class="text-secondary small mb-0">Ubah informasi siswa, kemudian tekan tombol Update.</p>
                </div>

                <div class="card-body p-4">
                    <form action="proses_edit_siswa.php" method="POST">
                        <input type="hidden" name="id" value="<?= htmlspecialchars($data['id']) ?>">

                        <div class="row g-3">
                            <div class="col-12 col-md-6">
                                <label for="nis" class="form-label">NIS</label>
                                <input type="text" class="form-control" id="nis" name="nis" value="<?= htmlspecialchars($data['nis']) ?>" required>
                            </div>

                            <div class="col-12 col-md-6">
                                <label for="nisn" class="form-label">NISN</label>
                                <input type="text" class="form-control" id="nisn" name="nisn" value="<?= htmlspecialchars($data['nisn']) ?>" required>
                            </div>

                            <div class="col-12">
                                <label for="nama" class="form-label">Nama Siswa</label>
                                <input type="text" class="form-control" id="nama" name="nama" value="<?= htmlspecialchars($data['nama']) ?>" required>
                            </div>

                            <div class="col-12 col-md-6">
                                <label for="jenis_kelamin" class="form-label">Jenis Kelamin</label>
                                <select class="form-select" id="jenis_kelamin" name="jenis_kelamin" required>
                                    <option value="L" <?= $data['jenis_kelamin'] == 'L' ? 'selected' : '' ?>>Laki-laki</option>
                                    <option value="P" <?= $data['jenis_kelamin'] == 'P' ? 'selected' : '' ?>>Perempuan</option>
                                </select>
                            </div>

                            <div class="col-12 col-md-6">
                                <label for="tanggal_lahir" class="form-label">Tanggal Lahir</label>
                                <input type="date" class="form-control" id="tanggal_lahir" name="tanggal_lahir" value="<?= htmlspecialchars($data['tanggal_lahir']) ?>" required>
                            </div>

                            <div class="col-12">
                                <label for="alamat" class="form-label">Alamat</label>
                                <textarea class="form-control" id="alamat" name="alamat" rows="4" required><?= htmlspecialchars($data['alamat']) ?></textarea>
                            </div>

                            <div class="col-12">
                                <label for="status_aktif" class="form-label">Status Aktif</label>
                                <select class="form-select" id="status_aktif" name="status_aktif" required>
                                    <option value="1" <?= $data['status_aktif'] == 1 ? 'selected' : '' ?>>Aktif</option>
                                    <option value="0" <?= $data['status_aktif'] == 0 ? 'selected' : '' ?>>Tidak Aktif</option>
                                </select>
                            </div>
                        </div>

                        <hr class="my-4">
                        <div class="d-flex flex-column flex-sm-row gap-2 justify-content-between">
                            <a href="kelola_siswa.php" class="btn btn-outline-secondary">
                                <i class="bi bi-arrow-left me-1"></i> Kembali
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-circle me-1"></i> Update Data Siswa
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="text-center text-secondary small mt-4">
                &copy; <?= date('Y') ?> SI-TIB — Sistem Informasi Tata Tertib
            </div>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>