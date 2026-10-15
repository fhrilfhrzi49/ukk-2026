<?php

session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$role =$_SESSION['role'] ?? '';

// Hanya Admin yang diizinkan menambah data siswa (sesuai Use Case)
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

$error = "";
$success = "";

// Cek struktur kolom pada tabel siswa secara otomatis
$kolom_siswa = [];
$res_cols = mysqli_query($koneksi, "SHOW COLUMNS FROM siswa");
if ($res_cols) {
    while ($c = mysqli_fetch_assoc($res_cols)) {
        $kolom_siswa[] =$c['Field'];
    }
}

// Tentukan nama kolom nama siswa yang tersedia di database
$kolom_nama = 'nama';
if (in_array('nama_lengkap', $kolom_siswa)) {$kolom_nama = 'nama_lengkap';
} elseif (in_array('nama_siswa', $kolom_siswa)) {$kolom_nama = 'nama_siswa';
}

// Tentukan nama kolom jenis kelamin yang tersedia di database
$kolom_jk = 'jenis_kelamin';
if (in_array('jk', $kolom_siswa)) {$kolom_jk = 'jk';
} elseif (in_array('gender', $kolom_siswa)) {$kolom_gender = 'gender';
}

// Tentukan nama kolom kelas yang tersedia di database
$kolom_kelas = 'kelas_id';
if (in_array('id_kelas', $kolom_siswa)) {$kolom_kelas = 'id_kelas';
}

// Proses ketika form disubmit
if (isset($_POST['simpan'])) {
    $nis           = trim($_POST['nis'] ?? '');
    $nama_input    = trim($_POST['nama_input'] ?? '');
    $jk_input      =$_POST['jk_input'] ?? '';
    $kelas_id      =$_POST['kelas_id'] ?? '';
    $alamat        = trim($_POST['alamat'] ?? '');
    $no_hp         = trim($_POST['no_hp'] ?? '');

    if ($nis == "" or $nama_input == "" or $jk_input == "" or $kelas_id == "") {
        $error = "NIS, Nama, Jenis Kelamin, dan Kelas wajib diisi!";
    } else {
        // Susun query dinamis berdasarkan kolom yang ada di database
        $fields = ["nis", "`$kolom_nama`", "`$kolom_jk`", "`$kolom_kelas`"];
        $values = ["?", "?", "?", "?"];
        $types  = "ssss";
        $params = [$nis,$nama_input, $jk_input,$kelas_id];

        if (in_array('alamat', $kolom_siswa)) {$fields[] = "alamat";
            $values[] = "?";
            $types   .= "s";
            $params[] =$alamat;
        }

        if (in_array('no_hp', $kolom_siswa) || in_array('telepon', $kolom_siswa)) {$kolom_hp = in_array('no_hp', $kolom_siswa) ? 'no_hp' : 'telepon';$fields[] = "`$kolom_hp`";
            $values[] = "?";
            $types   .= "s";
            $params[] =$no_hp;
        }

        $sql = "INSERT INTO siswa (" . implode(", ", $fields) . ") VALUES (" . implode(", ", $values) . ")";
        $stmt = mysqli_prepare($koneksi,$sql);

        if ($stmt) {
            mysqli_stmt_bind_param($stmt, $types, ...$params);
            if (mysqli_stmt_execute($stmt)) {$success = "Data siswa berhasil ditambahkan.";
                echo "<script>
                    setTimeout(function() {
                        window.location.href = 'siswa.php';
                    }, 1500);
                </script>";
            } else {
                $error = "Gagal menyimpan data: " . mysqli_error($koneksi);
            }
            mysqli_stmt_close($stmt);
        } else {
            $error = "Query error: " . mysqli_error($koneksi);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Siswa - Sistem Pelanggaran Siswa</title>
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
                    <a href="siswa.php" class="nav-link active">Data Siswa</a>
                </li>
                <li class="nav-item mb-2">
                    <a href="guru.php" class="nav-link text-white">Data Guru</a>
                </li>
                <li class="nav-item mb-2">
                    <a href="kelas.php" class="nav-link text-white">Kelas</a>
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
                    <a href="riwayat.php" class="nav-link text-white">Riwayat</a>
                </li>
                <li class="nav-item mb-2">
                    <a href="rekap_poin.php" class="nav-link text-white">Rekap Poin</a>
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
            <h2 class="mb-2">Tambah Data Siswa</h2>
            <p class="text-secondary mb-4">Formulir penambahan data siswa baru.</p>

            <div class="card border-0 shadow-sm rounded-4" style="max-width: 700px;">
                <div class="card-body p-4">

                    <?php if ($error !== ""): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <?= htmlspecialchars($error); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>

                    <?php if ($success !== ""): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <?= htmlspecialchars($success); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>

                    <form method="POST" autocomplete="off">
                        
                        <div class="mb-3">
                            <label for="nis" class="form-label fw-bold">NIS (Nomor Induk Siswa)</label>
                            <input type="text" class="form-control" id="nis" name="nis" placeholder="Masukkan NIS" required>
                        </div>

                        <div class="mb-3">
                            <label for="nama_input" class="form-label fw-bold">Nama Lengkap</label>
                            <input type="text" class="form-control" id="nama_input" name="nama_input" placeholder="Masukkan nama lengkap siswa" required>
                        </div>

                        <div class="mb-3">
                            <label for="jk_input" class="form-label fw-bold">Jenis Kelamin</label>
                            <select class="form-select" id="jk_input" name="jk_input" required>
                                <option value="" selected disabled>-- Pilih Jenis Kelamin --</option>
                                <option value="L">Laki-laki (L)</option>
                                <option value="P">Perempuan (P)</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="kelas_id" class="form-label fw-bold">Kelas</label>
                            <select class="form-select" id="kelas_id" name="kelas_id" required>
                                <option value="" selected disabled>-- Pilih Kelas --</option>
                                <?php
                                $q_kelas = mysqli_query($koneksi, "SELECT * FROM kelas");