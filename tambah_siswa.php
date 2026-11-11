<?php
require_once __DIR__ . '/config/akses.php';
wajib_role(['admin', 'administrator']);
require_once __DIR__ . '/includes/cek_session.php';
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Siswa | SI-TIB</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-4 py-md-5">
    <div class="mb-4">
        <a href="kelola_siswa.php" class="btn btn-outline-secondary btn-sm mb-3">
            <i class="bi bi-arrow-left"></i> Kembali ke Kelola Siswa
        </a>
        <h2 class="fw-bold mb-1"><i class="bi bi-person-plus-fill text-primary"></i> Tambah Siswa</h2>
        <p class="text-secondary mb-0">SI-TIB — Sistem Informasi Tata Tertib. Silakan isi formulir berikut untuk menambahkan data siswa.</p>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3">
            <h5 class="fw-semibold mb-0"><i class="bi bi-card-list"></i> Formulir Data Siswa</h5>
        </div>
        <div class="card-body p-3 p-md-4">
            <form action="proses_tambah_siswa.php" method="POST" id="formTambahSiswa">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label for="nis" class="form-label fw-semibold">NIS <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="nis" name="nis" placeholder="Masukkan 10 digit angka" inputmode="numeric" pattern="[0-9]{1,10}" maxlength="10" title="NIS hanya boleh berisi angka, maksimal 10 digit." required>
                        <div class="form-text">NIS hanya boleh berisi angka, maksimal 10 digit.</div>
                        <div class="invalid-feedback">NIS wajib berupa angka dengan panjang 1 sampai 10 digit.</div>
                    </div>

                    <div class="col-md-6">
                        <label for="nisn" class="form-label fw-semibold">NISN <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="nisn" name="nisn" placeholder="Masukkan 10 digit angka" inputmode="numeric" pattern="[0-9]{10}" minlength="10" maxlength="10" title="NISN harus terdiri dari tepat 10 digit angka." aria-describedby="bantuanNisn" required>
                        <div class="form-text" id="bantuanNisn">NISN harus tepat 10 digit angka.</div>
                        <div class="invalid-feedback">NISN wajib terdiri dari tepat 10 digit angka tanpa spasi atau huruf.</div>
                    </div>

                    <div class="col-12">
                        <label for="nama" class="form-label fw-semibold">Nama Siswa <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="nama" name="nama" placeholder="Masukkan nama lengkap siswa" maxlength="150" required>
                    </div>

                    <div class="col-md-6">
                        <label for="jenis_kelamin" class="form-label fw-semibold">Jenis Kelamin <span class="text-danger">*</span></label>
                        <select class="form-select" id="jenis_kelamin" name="jenis_kelamin" required>
                            <option value="">Pilih Jenis Kelamin</option>
                            <option value="L">Laki-laki</option>
                            <option value="P">Perempuan</option>
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label for="tanggal_lahir" class="form-label fw-semibold">Tanggal Lahir <span class="text-danger">*</span></label>
                        <input type="date" class="form-control" id="tanggal_lahir" name="tanggal_lahir" required>
                    </div>

                    <div class="col-12">
                        <label for="alamat" class="form-label fw-semibold">Alamat <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="alamat" name="alamat" rows="4" placeholder="Masukkan alamat lengkap siswa" required></textarea>
                    </div>

                    <div class="col-md-6">
                        <label for="status_aktif" class="form-label fw-semibold">Status Aktif <span class="text-danger">*</span></label>
                        <select class="form-select" id="status_aktif" name="status_aktif" required>
                            <option value="1" selected>Aktif</option>
                            <option value="0">Tidak Aktif</option>
                        </select>
                    </div>
                </div>

                <hr class="my-4">
                <div class="d-flex flex-column flex-sm-row gap-2 justify-content-end">
                    <a href="kelola_siswa.php" class="btn btn-outline-secondary"><i class="bi bi-x-circle"></i> Batal</a>
                    <button type="submit" class="btn btn-primary"><i class="bi bi-save"></i> Simpan Data Siswa</button>
                </div>
            </form>
        </div>
    </div>

    <footer class="text-center text-secondary small mt-4">
        &copy; <?= date('Y') ?> SI-TIB — Sistem Informasi Tata Tertib
    </footer>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
<script>
const inputNis = document.getElementById('nis');
const inputNisn = document.getElementById('nisn');
const formSiswa = document.getElementById('formTambahSiswa');

[inputNis, inputNisn].forEach(input => {
    input.addEventListener('input', function () {
        this.value = this.value.replace(/[^0-9]/g, '').slice(0, 10);
        this.setCustomValidity('');
    });
});

formSiswa.addEventListener('submit', function (event) {
    if (!/^[0-9]{1,10}$/.test(inputNis.value)) {
        event.preventDefault();
        inputNis.setCustomValidity('NIS harus berupa angka dengan panjang maksimal 10 digit.');
        inputNis.reportValidity();
        inputNis.focus();
        return;
    }

    if (!/^[0-9]{10}$/.test(inputNisn.value)) {
        event.preventDefault();
        inputNisn.setCustomValidity('NISN harus terdiri dari tepat 10 digit angka.');
        inputNisn.reportValidity();
        inputNisn.focus();
    }
});
</script>
</body>
</html>