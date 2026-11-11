<?php
include 'includes/cek_session.php';
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Tahun Ajaran | SI-TIB</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container py-4">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
            <div>
                <h2 class="fw-bold mb-1"><i class="bi bi-calendar-plus-fill text-primary"></i> Tambah Tahun Ajaran</h2>
                <p class="text-secondary mb-0">Tambahkan data tahun ajaran baru ke dalam sistem.</p>
            </div>
            <a href="kelola_tahun_ajaran.php" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Kembali
            </a>
        </div>

        <div class="row justify-content-center">
            <div class="col-12 col-lg-8 col-xl-7">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white py-3">
                        <h5 class="fw-semibold mb-0"><i class="bi bi-pencil-square"></i> Formulir Tahun Ajaran</h5>
                    </div>

                    <div class="card-body p-4">
                        <form action="proses_tambah_tahun_ajaran.php" method="POST">
                            <div class="mb-3">
                                <label for="nama" class="form-label fw-semibold">Nama Tahun Ajaran</label>
                                <input type="text" class="form-control" id="nama" name="nama" placeholder="Contoh: 2026/2027" required>
                                <div class="form-text">Masukkan nama tahun ajaran yang akan digunakan.</div>
                            </div>

                            <div class="mb-3">
                                <label for="tanggal_mulai" class="form-label fw-semibold">Tanggal Mulai</label>
                                <input type="date" class="form-control" id="tanggal_mulai" name="tanggal_mulai" required>
                            </div>

                            <div class="mb-3">
                                <label for="tanggal_selesai" class="form-label fw-semibold">Tanggal Selesai</label>
                                <input type="date" class="form-control" id="tanggal_selesai" name="tanggal_selesai" required>
                            </div>

                            <div class="mb-4">
                                <label for="status_aktif" class="form-label fw-semibold">Status Aktif</label>
                                <select class="form-select" id="status_aktif" name="status_aktif" required>
                                    <option value="1">Aktif</option>
                                    <option value="0">Tidak Aktif</option>
                                </select>
                                <div class="form-text">Pilih status tahun ajaran.</div>
                            </div>

                            <div class="d-flex flex-column flex-sm-row gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-save-fill"></i> Simpan Tahun Ajaran
                                </button>
                                <a href="kelola_tahun_ajaran.php" class="btn btn-light border">
                                    <i class="bi bi-x-circle"></i> Batal
                                </a>
                            </div>
                        </form>
                    </div>
                </div>

                <footer class="text-center text-secondary small mt-4">
                    Sistem Pelanggaran Siswa &copy; 2026
                </footer>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.querySelector('form').addEventListener('submit', function (event) {
            const mulai = document.getElementById('tanggal_mulai').value;
            const selesai = document.getElementById('tanggal_selesai').value;

            if (mulai && selesai && selesai < mulai) {
                event.preventDefault();
                alert('Tanggal selesai tidak boleh lebih awal dari tanggal mulai.');
            }
        });
    </script>
</body>
</html>