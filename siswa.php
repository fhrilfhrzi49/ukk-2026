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
    <div class="min-vh-100 d-flex justify-content-center align-items-center">
        <div class="card border-0 shadow rounded-4 text-center" style="width: 400px;">
            <div class="card-body p-5">
                <h2 class="fw-bold mb-3">
                    Halaman Ini Tidak Dapat Dibuka
                </h2>
                <p class="text-secondary mb-4">
                    Hanya Administrator Yang Dapat Mengakses Halaman Ini.
                </p>
                <a href="dashboard.php" class="btn btn-dark px-4">
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

?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Siswa - Sistem Pelanggaran Siswa</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
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
                    <a href="dashboard.php" class="nav-link text-white">
                        Dashboard
                    </a>
                </li>

                <!-- ADMIN -->
                <?php if ($role === 'admin') { ?>
                    <li class="nav-item mb-2">
                        <a href="tahun_ajaran.php" class="nav-link text-white">
                            Tahun Ajaran
                        </a>
                    </li>
                    <li class="nav-item mb-2">
                        <a href="siswa.php" class="nav-link active">
                            Data Siswa
                        </a>
                    </li>
                    <li class="nav-item mb-2">
                        <a href="guru.php" class="nav-link text-white">
                            Data Guru
                        </a>
                    </li>
                    <li class="nav-item mb-2">
                        <a href="kelas.php" class="nav-link text-white">
                            Kelas
                        </a>
                    </li>
                    <li class="nav-item mb-2">
                        <a href="wali_kelas.php" class="nav-link text-white">
                            Wali Kelas
                        </a>
                    </li>
                    <li class="nav-item mb-2">
                        <a href="penempatan_siswa.php" class="nav-link text-white">
                            Penempatan Siswa
                        </a>
                    </li>
                    <li class="nav-item mb-2">
                        <a href="kategori_pelanggaran.php" class="nav-link text-white">
                            Kategori Pelanggaran
                        </a>
                    </li>
                    <li class="nav-item mb-2">
                        <a href="jenis_pelanggaran.php" class="nav-link text-white">
                            Jenis Pelanggaran
                        </a>
                    </li>
                    <li class="nav-item mb-2">
                        <a href="laporan.php" class="nav-link text-white">
                            Laporan
                        </a>
                    </li>
                <?php } ?>

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
                
                 <!-- COPYRIGHT -->
                <footer class="mt-4 pt-3 border-top text-center text-secondary">
                    <small>
                        © 2026 Sistem Pelanggaran Siswa — Muhamad Fahril Fahrurozi
                    </small>
                </footer>
            </ul>
        </div>

        <!-- KONTEN SISWA -->
        <main class="col-md-9 col-lg-10 p-4">
            <h2 class="mb-2">
                Data Siswa
            </h2>
            <p class="text-secondary mb-4">
                Kelola data siswa.
            </p>

            <!-- TOMBOL TAMBAH & KONTEN TABEL -->
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body">
                    
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="card-title fw-bold m-0">Daftar Siswa</h5>
                        <a href="tambah_siswa.php" class="btn btn-primary btn-sm px-3">
                            + Tambah Siswa
                        </a>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-striped table-hover align-middle">
                            <thead class="table-dark">
                                <tr>
                                    <th scope="col">No</th>
                                    <th scope="col">NISN / NIS</th>
                                    <th scope="col">Nama Lengkap</th>
                                    <th scope="col">Jenis Kelamin</th>
                                    <th scope="col">Alamat</th>
                                    <th scope="col" class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $no = 1;
                                // Menggunakan ORDER BY 1 atau aman tanpa spesifik kolom nama_lengkap agar tidak error
                                $query = mysqli_query($koneksi, "SELECT * FROM siswa");
                                
                                if ($query && mysqli_num_rows($query) > 0) {
                                    while ($row = mysqli_fetch_assoc($query)) {
                                        // Menyesuaikan dengan kolom yang biasanya ada di tabel siswa
                                        $nis    = $row['nisn'] ?? ($row['nis'] ?? '-');
                                        $nama   = $row['nama_lengkap'] ?? ($row['nama'] ?? '-');
                                        $jk     = $row['jenis_kelamin'] ?? ($row['jk'] ?? '-');
                                        $alamat = $row['alamat'] ?? '-';
                                        $id_s   = $row['id_siswa'] ?? ($row['id'] ?? 0);
                                ?>
                                        <tr>
                                            <td><?= $no++; ?></td>
                                            <td><?= htmlspecialchars($nis); ?></td>
                                            <td><?= htmlspecialchars($nama); ?></td>
                                            <td><?= htmlspecialchars($jk); ?></td>
                                            <td><?= htmlspecialchars($alamat); ?></td>
                                            <td class="text-center">
                                                <a href="edit_siswa.php?id=<?= $id_s; ?>" class="btn btn-warning btn-sm text-white">
                                                    Edit
                                                </a>
                                                <a href="hapus_siswa.php?id=<?= $id_s; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Yakin ingin menghapus data siswa ini?');">
                                                    Hapus
                                                </a>
                                            </td>
                                        </tr>
                                <?php 
                                    }
                                } else { 
                                ?>
                                    <tr>
                                        <td colspan="6" class="text-center text-secondary py-4">Belum ada data siswa atau tabel kosong.</td>
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

</body>

</html>