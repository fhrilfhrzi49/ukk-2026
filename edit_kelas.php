<?php

session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$role = $_SESSION['role'] ?? '';

if ($role !== 'admin') {
    ?>
    <!DOCTYPE html>
    <html lang="id">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Akses Ditolak</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    </head>
    <body class="bg-light">
    <div class="min-vh-100 d-flex justify-content-center align-items-center p-3">
        <div class="card border-0 shadow rounded-4 text-center w-100" style="max-width: 400px;">
            <div class="card-body p-5">
                <h2 class="fw-bold mb-3">Halaman Ini Tidak Dapat Dibuka</h2>
                <p class="text-secondary mb-4">Hanya Administrator Yang Dapat Mengakses Halaman Ini.</p>
                <a href="dashboard.php" class="btn btn-dark px-4">Kembali ke Dashboard</a>
            </div>
        </div>
    </div>
    </body>
    </html>
    <?php
    exit;
}

require_once "config/koneksi.php";

$pesan_error = "";

// Ambil ID dari URL
$id = $_GET['id'] ?? '';
if (empty($id)) {
    header("Location: kelas.php");
    exit;
}

// Ambil data kelas berdasarkan ID
$stmt_get = mysqli_prepare($koneksi, "SELECT * FROM kelas WHERE id = ?");
mysqli_stmt_bind_param($stmt_get, "i", $id);
mysqli_stmt_execute($stmt_get);
$result = mysqli_stmt_get_result($stmt_get);
$kelas = mysqli_fetch_assoc($result);

if (!$kelas) {
    header("Location: kelas.php");
    exit;
}

// Proses ketika form disubmit untuk update data
if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama_kelas = trim($_POST['nama_kelas'] ?? '');
    $tingkat    = trim($_POST['tingkat'] ?? '');

    if (empty($nama_kelas) || empty($tingkat)) {
        $pesan_error = "Nama kelas dan tingkat wajib diisi!";
    } else {
        $stmt_update = mysqli_prepare($koneksi, "UPDATE kelas SET nama_kelas = ?, tingkat = ? WHERE id = ?");
        if ($stmt_update) {
            mysqli_stmt_bind_param($stmt_update, "ssi", $nama_kelas, $tingkat, $id);
            if (mysqli_stmt_execute($stmt_update)) {
                header("Location: kelas.php?pesan=sukses_edit");
                exit;
            } else {
                $pesan_error = "Gagal memperbarui data: " . mysqli_error($koneksi);
            }
            mysqli_stmt_close($stmt_update);
        } else {
            $pesan_error = "Terjadi kesalahan pada query: " . mysqli_error($koneksi);
        }
    }
}

?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Kelas - Sistem Pelanggaran Siswa</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>

<div class="container-fluid">
    <div class="row">

        <!-- SIDEBAR -->
        <div class="col-md-3 col-lg-2 bg-dark min-vh-100 p-3">
            <h4 class="text-white mb-4">My Website</h4>

            <ul class="nav nav-pills flex-column">
                <li class="nav-item mb-2">
                    <a href="dashboard.php" class="nav-link text-white">Dashboard</a>
                </li>
                <li class="nav-item mb-2">
                    <a href="tahun_ajaran.php" class="nav-link text-white">Tahun Ajaran</a>
                </li>
                <li class="nav-item mb-2">
                    <a href="siswa.php" class="nav-link text-white">Data Siswa</a>
                </li>
                <li class="nav-item mb-2">
                    <a href="guru.php" class="nav-link text-white">Data Guru</a>
                </li>
                <li class="nav-item mb-2">
                    <a href="kelas.php" class="nav-link active">Kelas</a>
                </li>
                <li class="nav-item mb-2">
                    <a href="wali_kelas.php" class="nav-link text-white">Wali Kelas</a>
                </li>
                <li class="nav-item mb-2">
                    <a href="penempatan_siswa.php" class="nav-link text-white">Penempatan Siswa</a>
                </li>
                <li class="nav-item mb-2">
                    <a href="kategori_pelanggaran.php" class="nav-link text-white">Kategori Pelanggaran</a>
                </li>
                <li class="nav-item mb-2">
                    <a href="jenis_pelanggaran.php" class="nav-link text-white">Jenis Pelanggaran</a>
                </li>
                <li class="nav-item mb-2">
                    <a href="laporan.php" class="nav-link text-white">Laporan</a>
                </li>
                <li class="nav-item mb-2">
                    <a href="about.php" class="nav-link text-white">About Me</a>
                </li>

                <hr class="text-secondary">

                <li class="nav-item">
                    <p>
                        <a href="logout.php" class="text-white" onclick="return confirm('Yakin ingin logout?');">Logout</a>
                    </p>
                </li>

                <footer class="mt-4 pt-3 border-top text-center text-secondary">
                    <small>© 2026 Sistem Pelanggaran Siswa — Muhamad Fahril Fahrurozi</small>
                </footer>
            </ul>
        </div>

        <!-- KONTEN UTAMA -->
        <main class="col-md-9 col-lg-10 p-4">
            <h2 class="mb-2">Edit Data Kelas</h2>
            <p class="text-secondary mb-4">Formulir perubahan data kelas.</p>

            <div class="card border-0 shadow-sm rounded-4" style="max-width: 700px;">
                <div class="card-body p-4">

                    <?php if (!empty($pesan_error)): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <?= htmlspecialchars($pesan_error); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>

                    <form action="" method="POST">
                        <div class="mb-3">
                            <label for="nama_kelas" class="form-label fw-bold">Nama Kelas</label>
                            <input type="text" class="form-control" id="nama_kelas" name="nama_kelas" value="<?= htmlspecialchars($kelas['nama_kelas'] ?? ($kelas['nama'] ?? '')); ?>" required>
                        </div>

                        <div class="mb-4">
                            <label for="tingkat" class="form-label fw-bold">Tingkat</label>
                            <input type="text" class="form-control" id="tingkat" name="tingkat" value="<?= htmlspecialchars($kelas['tingkat'] ?? ''); ?>" required>
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="kelas.php" class="btn btn-secondary px-4">Kembali</a>
                            <button type="submit" class="btn btn-primary px-4">Update Kelas</button>
                        </div>
                    </form>

                </div>
            </div>

        </main>

    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>