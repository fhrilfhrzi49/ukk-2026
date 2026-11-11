<?php
include 'includes/cek_session.php';
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Kelas | SI-TIB</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-4 py-md-5">
    <div class="mb-4">
        <a href="kelola_kelas.php" class="btn btn-outline-secondary btn-sm mb-3">
            <i class="bi bi-arrow-left"></i> Kembali ke Kelola Kelas
        </a>
        <h2 class="fw-bold mb-1"><i class="bi bi-building-add text-primary"></i> Tambah Kelas</h2>
        <p class="text-secondary mb-0">Silakan isi formulir berikut untuk menambahkan data kelas.</p>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3">
            <h5 class="fw-semibold mb-0"><i class="bi bi-card-list"></i> Formulir Data Kelas</h5>
        </div>

        <div class="card-body p-3 p-md-4">
            <form action="proses_tambah_kelas.php" method="POST">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label for="nama" class="form-label fw-semibold">Nama Kelas <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="nama" name="nama" placeholder="Contoh: XII RPL 2" required>
                    </div>

                    <div class="col-md-6">
                        <label for="tingkat" class="form-label fw-semibold">Tingkat <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="tingkat" name="tingkat" placeholder="Contoh: XII" required>
                    </div>

                    <div class="col-md-6">
                        <label for="jurusan" class="form-label fw-semibold">Jurusan <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="jurusan" name="jurusan" placeholder="Contoh: Rekayasa Perangkat Lunak" required>
                    </div>

                    <div class="col-md-6">
                        <label for="status_aktif" class="form-label fw-semibold">Status Aktif <span class="text-danger">*</span></label>
                        <select class="form-select" id="status_aktif" name="status_aktif" required>
                            <option value="1">Aktif</option>
                            <option value="0">Tidak Aktif</option>
                        </select>
                    </div>
                </div>

                <hr class="my-4">
                <div class="d-flex flex-column flex-sm-row gap-2 justify-content-end">
                    <a href="kelola_kelas.php" class="btn btn-outline-secondary">
                        <i class="bi bi-x-circle"></i> Batal
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save"></i> Simpan Data Kelas
                    </button>
                </div>
            </form>
        </div>
    </div>

    <footer class="text-center text-secondary small mt-4">
        Sistem Pelanggaran Siswa &copy; 2026
    </footer>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>