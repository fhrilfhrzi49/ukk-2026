<?php

session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$role =$_SESSION['role'] ?? '';

if ($role !== 'guru' and $role !== 'wali_kelas' and$role !== 'admin') {
    ?>
    <!DOCTYPE html>
    <html lang="id">

    <head>

        <meta charset="UTF-8">

        <meta name="viewport" content="width=device-width, initial-scale=1.0">

        <title>Akses Ditolak</title>

        <link
            href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css"
            rel="stylesheet"
        >

    </head>

    <body class="bg-light">

    <div class="min-vh-100 d-flex justify-content-center align-items-center p-3">

        <div class="card border-0 shadow rounded-4 text-center w-100" style="max-width: 400px;">

            <div class="card-body p-5">

                <h2 class="fw-bold mb-3">
                    Halaman Ini Tidak Dapat Dibuka
                </h2>

                <p class="text-secondary mb-4">
                    Halaman ini hanya dapat diakses oleh Guru dan Wali Kelas.
                </p>

                <a
                    href="dashboard.php"
                    class="btn btn-dark px-4"
                >
                    Kembali ke Dashboard
                </a>

            </div>

        </div>

    </div>

    </body>

    </html>
    <?php
    exit;
}

require_once "config/koneksi.php";

// Deteksi nama tabel riwayat atau alternatifnya yang tersedia di database
$nama_tabel = '';
$cek_tabel = mysqli_query($koneksi, "SHOW TABLES");
while ($t = mysqli_fetch_array($cek_tabel)) {
    $tabel =$t[0];
    if (in_array($tabel, ['riwayat', 'pelanggaran_siswa', 'siswa_pelanggaran'])) {
        $nama_tabel =$tabel;
        break;
    }
}

?>

<!DOCTYPE html>
<html lang="id">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Riwayat - Sistem Pelanggaran Siswa</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

</head>

<body>

<div class="container-fluid">

    <div class="row">

        <!-- SIDEBAR -->
        <div class="col-md-3 col-lg-2 bg-dark min-vh-100 p-3">

            <h4 class="text-white mb-4">
                My Website
            </h4>

            <ul class="nav nav-pills flex-column">

                <!-- DASHBOARD -->
                <li class="nav-item mb-2">
                        <a href="dashboard.php" class="nav-link text-white">Dashboard</a>
                    </li>

                <!-- ADMIN MENU -->
                <?php if ($role === 'admin') { ?>

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

                <?php } ?>


                <!-- GURU & WALI KELAS (Akses Bersama/Turunan) -->
                <?php if ($role === 'guru' || $role === 'wali_kelas') { ?>

                    <li class="nav-item mb-2">
                        <a href="catat_pelanggaran.php" class="nav-link text-white">Catat Pelanggaran</a>
                    </li>

                    <li class="nav-item mb-2">
                        <a href="tindakan.php" class="nav-link text-white">Tindakan</a>
                    </li>

                <?php } ?>


                <!-- WALI KELAS & ADMIN (Menu Khusus Wali Kelas) -->
                <?php if ($role === 'wali_kelas' || $role === 'admin') { ?>

                    <li class="nav-item mb-2">
                        <a href="laporan.php" class="nav-link text-white">Laporan</a>
                    </li>

                    <li class="nav-item mb-2">

                    <a href="riwayat.php" class="nav-link active">

                        Riwayat
                    </a>

                    </li>

                    <li class="nav-item mb-2">
                        <a href="rekap_poin.php" class="nav-link text-white">Rekap Poin</a>
                    </li>

                <?php } ?>


                <!-- ABOUT -->
                <li class="nav-item mb-2">

                    <a href="about.php" class="nav-link text-white">

                        About Me

                    </a>

                </li>


                <hr class="text-secondary">


                <!-- LOGOUT -->
                <li class="nav-item">

                    <p>

                        <a href="logout.php"
                           class="text-white"
                           onclick="return confirm('Yakin ingin logout?');">

                            Logout

                        </a>

                    </p>

                </li>

                <footer class="mt-4 pt-3 border-top text-center text-secondary">
                    <small>
                        © 2026 Sistem Pelanggaran Siswa — Muhamad Fahril Fahrurozi
                    </small>
                </footer>

            </ul>

        </div>

        </div>


        <!-- KONTEN -->
        <main class="col-md-9 col-lg-10 p-4">

            <h2 class="mb-2">
                Riwayat
            </h2>

            <p class="text-secondary mb-4">
                Melihat riwayat pelanggaran siswa.
            </p>

            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body">
                    
                    <h5 class="card-title fw-bold mb-3">Daftar Riwayat Pelanggaran Siswa</h5>

                    <div class="table-responsive">
                        <table class="table table-striped table-hover align-middle">
                            <thead class="table-dark">
                                <tr>
                                    <th scope="col">No</th>
                                    <th scope="col">Tanggal</th>
                                    <th scope="col">Nama Siswa</th>
                                    <th scope="col">Jenis Pelanggaran</th>
                                    <th scope="col">Poin</th>
                                    <th scope="col">Keterangan</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $no = 1;
                                $query_riwayat = null;
                                if ($nama_tabel) {
                                    $query_riwayat = mysqli_query($koneksi, "SELECT * FROM `$nama_tabel`");
                                }
                                
                                if ($query_riwayat && mysqli_num_rows($query_riwayat) > 0) {
                                    while ($row_r = mysqli_fetch_assoc($query_riwayat)) {$tanggal          = $row_r['tanggal'] ?? ($row_r['created_at'] ?? '-');
                                        $nama_siswa       =$row_r['nama_siswa'] ?? ($row_r['siswa'] ?? 'Siswa');$nama_pelanggaran = $row_r['nama_pelanggaran'] ?? ($row_r['pelanggaran'] ?? '-');
                                        $poin             =$row_r['poin'] ?? 0;
                                        $keterangan       =$row_r['keterangan'] ?? '-';
                                ?>
                                        <tr>
                                            <td><?= $no++; ?></td>
                                            <td><?= htmlspecialchars(substr($tanggal, 0, 10)); ?></td>
                                            <td><?= htmlspecialchars($nama_siswa); ?></td>
                                            <td><?= htmlspecialchars($nama_pelanggaran); ?></td>
                                            <td><span class="badge bg-danger"><?= htmlspecialchars($poin); ?> Poin</span></td>
                                            <td><?= htmlspecialchars($keterangan); ?></td>
                                        </tr>
                                <?php 
                                    }
                                } else { 
                                ?>
                                    <tr>
                                        <td colspan="6" class="text-center text-secondary py-4">
                                            <?php if (!$nama_tabel): ?>
                                                Tabel untuk riwayat (misal: <code>riwayat</code> atau <code>pelanggaran_siswa</code>) belum ada di database Anda.
                                            <?php else: ?>
                                                Belum ada data riwayat pelanggaran.
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>

                </div>
            </div>

        </main>

    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>