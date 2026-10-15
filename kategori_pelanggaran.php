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

            <p class="text-secondary">
                Kelola kategori pelanggaran siswa.
            </p>

        </main>

    </div>

</div>

</body>

</html>