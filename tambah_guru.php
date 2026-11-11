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
    <title>Tambah Guru | SI-TIB</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-4 py-md-5">
    <div class="mb-4">
        <a href="kelola_guru.php" class="btn btn-outline-secondary btn-sm mb-3">
            <i class="bi bi-arrow-left"></i> Kembali ke Kelola Guru
        </a>
        <h2 class="fw-bold mb-1"><i class="bi bi-person-plus-fill text-primary"></i> Tambah Guru</h2>
        <p class="text-secondary mb-0">Isi data guru sekaligus buat akun untuk login ke sistem.</p>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3">
            <h5 class="fw-semibold mb-0"><i class="bi bi-card-list"></i> Formulir Data Guru</h5>
        </div>

        <div class="card-body p-3 p-md-4">
            <div class="alert alert-info small" role="alert">
                <i class="bi bi-info-circle-fill me-1"></i>
                Admin membuat password akun guru di halaman ini. Guru nantinya login menggunakan email dan password tersebut.
            </div>

            <form action="proses_tambah_guru.php" method="POST" autocomplete="off">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label for="nip" class="form-label fw-semibold">NIP <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="nip" name="nip" placeholder="Masukkan NIP guru" maxlength="50" required>
                    </div>

                    <div class="col-md-6">
                        <label for="nama" class="form-label fw-semibold">Nama Guru <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="nama" name="nama" placeholder="Masukkan nama lengkap guru" maxlength="150" required>
                    </div>

                    <div class="col-md-6">
                        <label for="email" class="form-label fw-semibold">Email Login <span class="text-danger">*</span></label>
                        <input type="email" class="form-control" id="email" name="email" placeholder="contoh@email.com" maxlength="150" autocomplete="off" required>
                        <div class="form-text">Email ini akan digunakan guru untuk login.</div>
                    </div>

                    <div class="col-md-6">
                        <label for="status_aktif" class="form-label fw-semibold">Status Aktif <span class="text-danger">*</span></label>
                        <select class="form-select" id="status_aktif" name="status_aktif" required>
                            <option value="1" selected>Aktif</option>
                            <option value="0">Tidak Aktif</option>
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label for="password" class="form-label fw-semibold">Password <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="password" class="form-control" id="password" name="password" placeholder="Buat password minimal 8 karakter" minlength="8" autocomplete="new-password" required>
                            <button class="btn btn-outline-secondary" type="button" id="togglePassword" aria-label="Tampilkan password" title="Tampilkan atau sembunyikan password">
                                <i class="bi bi-eye" id="iconPassword"></i>
                            </button>
                        </div>
                        <div class="form-text">Gunakan password yang kuat dan tidak mudah ditebak.</div>
                    </div>

                    <div class="col-md-6">
                        <label for="konfirmasi_password" class="form-label fw-semibold">Konfirmasi Password <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="password" class="form-control" id="konfirmasi_password" name="konfirmasi_password" placeholder="Masukkan ulang password" minlength="8" autocomplete="new-password" required>
                            <button class="btn btn-outline-secondary" type="button" id="toggleKonfirmasi" aria-label="Tampilkan konfirmasi password" title="Tampilkan atau sembunyikan password">
                                <i class="bi bi-eye" id="iconKonfirmasi"></i>
                            </button>
                        </div>
                        <div id="pesanPassword" class="form-text">Password dan konfirmasi harus sama.</div>
                    </div>

                    <div class="col-md-6">
                        <label for="user_id" class="form-label fw-semibold">User ID</label>
                        <input type="number" class="form-control" id="user_id" name="user_id" placeholder="Kosongkan untuk akun baru" min="1">
                        <div class="form-text">Kosongkan jika akun pengguna akan dibuat otomatis. Isi hanya jika menghubungkan guru ke akun yang sudah ada.</div>
                    </div>
                </div>

                <hr class="my-4">
                <div class="d-flex flex-column flex-sm-row gap-2 justify-content-end">
                    <a href="kelola_guru.php" class="btn btn-outline-secondary">
                        <i class="bi bi-x-circle"></i> Batal
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-person-check-fill"></i> Simpan Data Guru & Akun Login
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
<script>
function aturTogglePassword(tombolId, inputId, iconId) {
    const tombol = document.getElementById(tombolId);
    const input = document.getElementById(inputId);
    const icon = document.getElementById(iconId);

    tombol.addEventListener('click', function () {
        const tampil = input.type === 'password';
        input.type = tampil ? 'text' : 'password';
        icon.className = tampil ? 'bi bi-eye-slash' : 'bi bi-eye';
        tombol.setAttribute('aria-label', tampil ? 'Sembunyikan password' : 'Tampilkan password');
    });
}

aturTogglePassword('togglePassword', 'password', 'iconPassword');
aturTogglePassword('toggleKonfirmasi', 'konfirmasi_password', 'iconKonfirmasi');

const form = document.querySelector('form');
const password = document.getElementById('password');
const konfirmasi = document.getElementById('konfirmasi_password');
const pesanPassword = document.getElementById('pesanPassword');

function cekKecocokanPassword() {
    if (konfirmasi.value === '') {
        konfirmasi.setCustomValidity('');
        pesanPassword.textContent = 'Password dan konfirmasi harus sama.';
        pesanPassword.className = 'form-text';
    } else if (password.value !== konfirmasi.value) {
        konfirmasi.setCustomValidity('Konfirmasi password tidak sama.');
        pesanPassword.textContent = 'Password tidak sama.';
        pesanPassword.className = 'form-text text-danger';
    } else {
        konfirmasi.setCustomValidity('');
        pesanPassword.textContent = 'Password sudah cocok.';
        pesanPassword.className = 'form-text text-success';
    }
}

password.addEventListener('input', cekKecocokanPassword);
konfirmasi.addEventListener('input', cekKecocokanPassword);

form.addEventListener('submit', function (event) {
    cekKecocokanPassword();

    if (!form.checkValidity()) {
        event.preventDefault();
        form.reportValidity();
    }
});
</script>
</body>
</html>
