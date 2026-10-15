<?php

session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$role =$_SESSION['role'] ?? '';

// Izinkan Guru, Wali Kelas, dan Administrator untuk mengakses halaman ini
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
                    Hanya Guru, Wali Kelas, dan Administrator Yang Dapat Mengakses Halaman Ini.
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

// Deteksi nama tabel tindakan atau alternatifnya yang tersedia di database
$nama_tabel = '';
$cek_tabel = mysqli_query($koneksi, "SHOW TABLES");
while ($t = mysqli_fetch_array($cek_tabel)) {
    $tabel =$t[0];
    if (in_array($tabel, ['tindakan', 'pelanggaran_siswa', 'siswa_pelanggaran'])) {
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

    <title>Tindakan - Sistem Pelanggaran Siswa</title>

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


                <!-- MENU GURU / WALI KELAS -->

                <li class="nav-item mb-2">

                    <a
                        href="catat_pelanggaran.php"
                        class="nav-link text-white"
                    >
                        Catat Pelanggaran
                    </a>

                </li>

                <li class="nav-item mb-2">

                    <a
                        href="tindakan.php"
                        class="nav-link active"
                    >
                        Tindakan
                    </a>

                </li>

                <?php if ($role === 'wali_kelas' or$role === 'admin'): ?>
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
                <?php endif; ?>


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
                Tindakan
            </h2>

            <p class="text-secondary mb-4">
                Kelola tindakan terhadap pelanggaran siswa.
            </p>

            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body">
                    
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="card-title fw-bold m-0">Daftar Tindakan Pelanggaran</h5>
                        <a href="tambah_tindakan.php" class="btn btn-primary btn-sm px-3">
                            + Tambah Tindakan
                        </a>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-striped table-hover align-middle">
                            <thead class="table-dark">
                                <tr>
                                    <th scope="col">No</th>
                                    <th scope="col">Tanggal</th>
                                    <th scope="col">Nama Siswa</th>
                                    <th scope="col">Jenis Pelanggaran</th>
                                    <th scope="col">Tindakan / Sanksi</th>
                                    <th scope="col">Keterangan</th>
                                    <th scope="col" class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $no = 1;
                                $query_tindakan = null;
                                if ($nama_tabel) {
                                    $query_tindakan = mysqli_query($koneksi, "SELECT * FROM `$nama_tabel`");
                                }
                                
                                if ($query_tindakan and mysqli_num_rows($query_tindakan) > 0) {
                                    while ($row_t = mysqli_fetch_assoc($query_tindakan)) {
                                        $id_tindakan      =$row_t['id'] ?? 0;
                                        $tanggal          =$row_t['tanggal'] ?? ($row_t['created_at'] ?? '-');$nama_siswa       = $row_t['nama_siswa'] ?? ($row_t['siswa'] ?? 'Siswa');
                                        $nama_pelanggaran =$row_t['nama_pelanggaran'] ?? ($row_t['pelanggaran'] ?? '-');$tindakan         = $row_t['tindakan'] ?? ($row_t['sanksi'] ?? '-');
                                        $keterangan       =$row_t['keterangan'] ?? '-';
                                ?>
                                        <tr>
                                            <td><?= $no++; ?></td>
                                            <td><?= htmlspecialchars(substr($tanggal, 0, 10)); ?></td>
                                            <td><?= htmlspecialchars($nama_siswa); ?></td>
                                            <td><?= htmlspecialchars($nama_pelanggaran); ?></td>
                                            <td><span class="badge bg-warning text-dark"><?= htmlspecialchars($tindakan); ?></span></td>
                                            <td><?= htmlspecialchars($keterangan); ?></td>
                                            <td class="text-center">
                                                <a href="edit_tindakan.php?id=<?= $id_tindakan; ?>" class="btn btn-warning btn-sm text-white">Edit</a>
                                                <a href="hapus_tindakan.php?id=<?= $id_tindakan; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Yakin ingin menghapus tindakan ini?');">Hapus</a>
                                            </td>
                                        </tr>
                                <?php 
                                    }
                                } else { 
                                ?>
                                    <tr>
                                        <td colspan="7" class="text-center text-secondary py-4">
                                            <?php if (!$nama_tabel): ?>
                                                Tabel untuk tindakan (misal: <code>tindakan</code>) belum ada di database Anda.
                                            <?php else: ?>
                                                Belum ada data tindakan pelanggaran.
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