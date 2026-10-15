<?php

session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$role =$_SESSION['role'] ?? '';

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
                <p class="text-secondary mb-4">Hanya Administrator yang dapat mengakses halaman ini.</p>
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

$id_guru =$_GET['id'] ?? 0;
if (!$id_guru) {
    header("Location: guru.php");
    exit;
}

$pesan_error = "";

// Cek kolom apa saja yang ada di tabel guru secara otomatis
$kolom_tersedia = [];
$result_kolom = mysqli_query($koneksi, "SHOW COLUMNS FROM guru");
while ($kolom = mysqli_fetch_assoc($result_kolom)) {
    $kolom_tersedia[] =$kolom['Field'];
}

// Cari kolom nama yang aktif digunakan di tabel
$kolom_nama_pilihan = 'nama';
foreach (['nama', 'nama_guru', 'nama_lengkap', 'name', 'fullname'] as $k) {
    if (in_array($k,$kolom_tersedia)) {
        $kolom_nama_pilihan =$k;
        break;
    }
}

// Ambil data guru berdasarkan ID
$query_cek = mysqli_query($koneksi, "SELECT * FROM guru WHERE id = '$id_guru'");
$guru = mysqli_fetch_assoc($query_cek);

if (!$guru) {
    header("Location: guru.php?pesan=tidak_ditemukan");
    exit;
}

// Ambil nilai data yang ada di database secara fleksibel
$nama_saat_ini = $guru[$kolom_nama_pilihan] ?? '';
$nip_saat_ini  =$guru['nip'] ?? '';
$email_saat_ini =$guru['email'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama  = trim($_POST['nama'] ?? '');
    $nip   = trim($_POST['nip'] ?? '');
    $email = trim($_POST['email'] ?? '');

    if (empty($nama)) {$pesan_error = "Nama Guru wajib diisi!";
    } else {
        $ada_nip = in_array('nip',$kolom_tersedia);
        $ada_email = in_array('email',$kolom_tersedia);

        if ($ada_nip && $ada_email) {$sql = "UPDATE guru SET `$kolom_nama_pilihan` = ?, nip = ?, email = ? WHERE id = ?";
            $stmt = mysqli_prepare($koneksi,$sql);
            if ($stmt) {
                mysqli_stmt_bind_param($stmt, "sssi", $nama,$nip, $email,$id_guru);
            }
        } else if ($ada_nip) {$sql = "UPDATE guru SET `$kolom_nama_pilihan` = ?, nip = ? WHERE id = ?";
            $stmt = mysqli_prepare($koneksi,$sql);
            if ($stmt) {
                mysqli_stmt_bind_param($stmt, "ssi", $nama, $nip,$id_guru);
            }
        } else {
            $sql = "UPDATE guru SET `$kolom_nama_pilihan` = ? WHERE id = ?";
            $stmt = mysqli_prepare($koneksi,$sql);
            if ($stmt) {
                mysqli_stmt_bind_param($stmt, "si", $nama,$id_guru);
            }
        }

        if (isset($stmt) &&$stmt) {
            if (mysqli_stmt_execute($stmt)) {
                header("Location: guru.php?pesan=sukses_edit");
                exit;
            } else {
                $pesan_error = "Gagal memperbarui data guru: " . mysqli_error($koneksi);
            }
            mysqli_stmt_close($stmt);
        } else {
            $pesan_error = "Gagal menyiapkan query database: " . mysqli_error($koneksi);
        }
    }
}

?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Data Guru - Sistem Pelanggaran Siswa</title>
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
                    <a href="guru.php" class="nav-link active">Data Guru</a>
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
            <h2 class="mb-2">Edit Data Guru</h2>
            <p class="text-secondary mb-4">Perbarui informasi data guru.</p>

            <div class="card border-0 shadow-sm rounded-4" style="max-width: 600px;">
                <div class="card-body p-4">

                    <?php if (!empty($pesan_error)): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <?= htmlspecialchars($pesan_error); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>

                    <form action="" method="POST">
                        <div class="mb-3">
                            <label for="nama" class="form-label fw-bold">Nama Lengkap Guru</label>
                            <input type="text" class="form-control" id="nama" name="nama" value="<?= htmlspecialchars($nama_saat_ini); ?>" placeholder="Masukkan nama guru" required>
                        </div>

                        <div class="mb-3">
                            <label for="nip" class="form-label fw-bold">NIP</label>
                            <input type="text" class="form-control" id="nip" name="nip" value="<?= htmlspecialchars($nip_saat_ini); ?>" placeholder="Masukkan Nomor Induk Pegawai">
                        </div>

                        <div class="mb-4">
                            <label for="email" class="form-label fw-bold">Email</label>
                            <input type="email" class="form-control" id="email" name="email" value="<?= htmlspecialchars($email_saat_ini); ?>" placeholder="Masukkan email aktif guru">
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="guru.php" class="btn btn-secondary px-4">Kembali</a>
                            <button type="submit" class="btn btn-primary px-4">Simpan Perubahan</button>
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