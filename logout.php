<?php

session_start();

session_unset();
session_destroy();

?>

<!DOCTYPE html>
<html lang="id">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Logout</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

</head>

<body class="bg-light">

<div class="min-vh-100 d-flex justify-content-center align-items-center">

    <div class="card border-0 shadow rounded-4 text-center" style="width: 400px;">

        <div class="card-body p-5">

            <h2 class="fw-bold mb-3">
                Logout Berhasil
            </h2>

            <p class="text-secondary mb-4">
                Anda telah berhasil keluar dari sistem.
            </p>

            <a href="login.php" class="btn btn-dark px-4">
                Kembali ke Login
            </a>

        </div>

        <footer class="mt-4 pt-3 border-top text-center text-secondary">
        <small>
            © 2026 Sistem Pelanggaran Siswa — Muhamad Fahril Fahrurozi
        </small>
    </footer>

    </div>

</div>

</body>

</html>