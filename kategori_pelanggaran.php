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
                    Hanya Administrator Yang Dapat Mengakses Halaman Ini.
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

// Deteksi nama tabel kategori pelanggaran yang tersedia di database
$nama_tabel = '';
$cek_tabel = mysqli_query($koneksi, "SHOW TABLES");
while ($t = mysqli_fetch_array($cek_tabel)) {
    $tabel =$t[0];
    if (in_array($tabel, ['pelanggaran_kategori', 'kategori_pelanggaran', 'kategori'])) {
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

    <title>Kategori Pelanggaran - Sistem Pelanggaran Siswa</title>

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

                    <a
                        href="dashboard.php"
                        class="nav-link text-white"
                    >
                        Dashboard
                    </a>

                </li>


                <!-- ADMIN -->

                <li class="nav-item mb-2">

                    <a
                        href="tahun_ajaran.php"
                        class="nav-link text-white"
                    >
                        Tahun Ajaran
                    </a>

                </li>

                <li class="nav-item mb-2">

                    <a
                        href="siswa.php"
                        class="nav-link text-white"
                    >
                        Data Siswa
                    </a>

                </li>

                <li class="nav-item mb-2">

                    <a
                        href="guru.php"
                        class="nav-link text-white"
                    >
                        Data Guru
                    </a>

                </li>

                <li class="nav-item mb-2">

                    <a
                        href="kelas.php"
                        class="nav-link text-white"
                    >
                        Kelas
                    </a>

                </li>

                <li class="nav-item mb-2">

                    <a
                        href="wali_kelas.php"
                        class="nav-link text-white"
                    >
                        Wali Kelas
                    </a>

                </li>

                <li class="nav-item mb-2">

                    <a
                        href="penempatan_siswa.php"
                        class="nav-link text-white"
                    >
                        Penempatan Siswa
                    </a>

                </li>

                <li class="nav-item mb-2">

                    <a
                        href="kategori_pelanggaran.php"
                        class="nav-link active"
                    >
                        Kategori Pelanggaran
                    </a>

                </li>

                <li class="nav-item mb-2">

                    <a
                        href="jenis_pelanggaran.php"
                        class="nav-link text-white"
                    >
                        Jenis Pelanggaran
                    </a>

                </li>

                <li class="nav-item mb-2">

                    <a
                        href="laporan.php"
                        class="nav-link text-white"
                    >
                        Laporan
                    </a>

                </li>

                <li class="nav-item mb-2">
                    <a
                        href="riwayat.php"
                        class="nav-link text-white"
                    >
                        Riwayat
                    </a>
                </li>

                <li class="nav-item mb-2">
                    <a
                        href="rekap_poin.php"
                        class="nav-link text-white"
                    >
                        Rekap Poin
                    </a>
                </li>


                <!-- ABOUT ME -->
                <li class="nav-item mb-2">

                    <a
                        href="about.php"
                        class="nav-link text-white"
                    >
                        About Me
                    </a>

                </li>


                <hr class="text-secondary">


                <!-- LOGOUT -->
                <li class="nav-item">

                    <p>

                        <a
                            href="logout.php"
                            class="text-white"
                            onclick="return confirm('Yakin ingin logout?');"
                        >
                            Logout
                        </a>

                    </p>

                </li>


                <!-- COPYRIGHT -->
                <footer class="mt-4 pt-3 border-top text-center text-secondary">

                    <small>
                        © 2026 Sistem Pelanggaran Siswa — Muhamad Fahril Fahrurozi
                    </small>

                </footer>

            </ul>

        </div>


        <!-- KONTEN -->
        <main class="col-md-9 col-lg-10 p-4">

            <h2 class="mb-2">
                Kategori Pelanggaran
            </h2>

            <p class="text-secondary mb-4">
                Kelola kategori pelanggaran siswa.
            </p>

            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body">
                    
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="card-title fw-bold m-0">Daftar Kategori Pelanggaran</h5>
                        <a href="tambah_kategori_pelanggaran.php" class="btn btn-primary btn-sm px-3">
                            + Tambah Kategori
                        </a>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-striped table-hover align-middle">
                            <thead class="table-dark">
                                <tr>
                                    <th scope="col">No</th>
                                    <th scope="col">Nama Kategori</th>
                                    <th scope="col">Deskripsi</th>
                                    <th scope="col">Status Aktif</th>
                                    <th scope="col" class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $no = 1;
                                $query = null;
                                if ($nama_tabel) {
                                    $query = mysqli_query($koneksi, "SELECT * FROM `$nama_tabel`");
                                }
                                
                                if ($query && mysqli_num_rows($query) > 0) {
                                    while ($row = mysqli_fetch_assoc($query)) {
                                        $id_kat       =$row['id'] ?? 0;
                                        $nama_kat     =$row['nama'] ?? ($row['nama_kategori'] ?? '-');$deskripsi    = $row['deskripsi'] ?? '-';$status_aktif = $row['status_aktif'] ?? 'Aktif';$badge_class = 'bg-secondary';
                                        if ($status_aktif == 'Aktif') {$badge_class = 'bg-success';
                                        } else if ($status_aktif == '1') {$badge_class = 'bg-success';
                                        } else if ($status_aktif == 1) {$badge_class = 'bg-success';
                                        }
                                ?>
                                        <tr>
                                            <td><?= $no++; ?></td>
                                            <td><?= htmlspecialchars($nama_kat); ?></td>
                                            <td><?= htmlspecialchars($deskripsi); ?></td>
                                            <td>
                                                <span class="badge <?= $badge_class; ?>">
                                                    <?= htmlspecialchars($status_aktif); ?>
                                                </span>
                                            </td>
                                            <td class="text-center">
                                                <a href="edit_kategori_pelanggaran.php?id=<?= $id_kat; ?>" class="btn btn-warning btn-sm text-white">
                                                    Edit
                                                </a>
                                                <a href="hapus_kategori_pelanggaran.php?id=<?= $id_kat; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Yakin ingin menghapus kategori pelanggaran ini?');">
                                                    Hapus
                                                </a>
                                            </td>
                                        </tr>
                                <?php 
                                    }
                                } else { 
                                ?>
                                    <tr>
                                        <td colspan="5" class="text-center text-secondary py-4">
                                            <?php if (!$nama_tabel): ?>
                                                Tabel untuk kategori pelanggaran (misal: <code>pelanggaran_kategori</code>) belum ada di database Anda.
                                            <?php else: ?>
                                                Belum ada data kategori pelanggaran atau tabel kosong.
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