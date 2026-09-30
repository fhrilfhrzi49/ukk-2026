<?php

session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    ?>

    <!DOCTYPE html>
    <html lang="id">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">

        <title>Akses Ditolak</title>

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