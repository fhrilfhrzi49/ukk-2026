<?php
include 'includes/cek_session.php';
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Tambah Kategori Pelanggaran | SI-TIB</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container py-4 px-3 px-md-4">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h3 class="fw-bold mb-1">
                <i class="bi bi-plus-circle text-primary me-2"></i>Tambah Kategori Pelanggaran
            </h3>
            <p class="text-secondary mb-0">Tambahkan kategori untuk pengelompokan pelanggaran siswa.</p>
        </div>
        <a href="kelola_pelanggaran_kategori.php" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i>Kembali
        </a>
    </div>

    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-header bg-white border-0 px-4 pt-4">
            <h5 class="fw-bold mb-1">
                <i class="bi bi-clipboard2-check text-primary me-2"></i>Formulir Kategori Pelanggaran
            </h5>
            <p class="text-secondary small mb-0">Isi informasi kategori pada kolom berikut.</p>
        </div>

        <div class="card-body p-4">
            <form action="proses_tambah_pelanggaran_kategori.php" method="POST">
                <div class="mb-3">
                    <label for="nama" class="form-label fw-semibold">
                        Nama Kategori <span class="text-danger">*</span>
                    </label>
                    <input type="text" class="form-control" id="nama" name="nama" placeholder="Contoh: Pelanggaran Ringan" maxlength="100" required>
                    <div class="form-text">Maksimal 100 karakter.</div>
                </div>

                <div class="mb-3">
                    <label for="deskripsi" class="form-label fw-semibold">Deskripsi</label>
                    <textarea class="form-control" id="deskripsi" name="deskripsi" rows="4" placeholder="Masukkan deskripsi kategori pelanggaran"></textarea>
                </div>

                <div class="mb-4">
                    <label for="status_aktif" class="form-label fw-semibold">
                        Status Aktif <span class="text-danger">*</span>
                    </label>
                    <select class="form-select" id="status_aktif" name="status_aktif" required>
                        <option value="1" selected>Aktif</option>
                        <option value="0">Tidak Aktif</option>
                    </select>
                    <div class="form-text">Pilih apakah kategori langsung digunakan atau dinonaktifkan.</div>
                </div>

                <hr class="my-4">

                <div class="d-flex flex-wrap gap-2">
                    <button type="submit" class="btn btn-primary px-4">
                        <i class="bi bi-save me-1"></i>Simpan
                    </button>
                    <button type="reset" class="btn btn-light border px-4">
                        <i class="bi bi-arrow-counterclockwise me-1"></i>Reset
                    </button>
                    <a href="kelola_pelanggaran_kategori.php" class="btn btn-outline-secondary px-4">Batal</a>
                </div>
            </form>
        </div>
    </div>

    <div class="text-center text-secondary small mt-4">
        Sistem Informasi Pelanggaran Siswa
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>