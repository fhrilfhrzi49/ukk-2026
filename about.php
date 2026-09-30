<?php

session_start();

?>

<!DOCTYPE html>
<html lang="id">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>About Me</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

</head>

<body class="bg-light">

<div class="container min-vh-100 d-flex justify-content-center align-items-center py-5">

    <div class="card shadow border-0 rounded-4 w-100" style="max-width: 800px;">

        <div class="card-body p-4 p-md-5 text-center">

            <img
                src="assets/img/profile.jpg"
                class="rounded-circle object-fit-cover mb-4"
                width="130"
                height="130"
                alt="Foto Profil"
            >

            <h1 class="fw-bold">
                Muhamad Fahril Fahrurozi
            </h1>

            <div class="text-secondary mb-4">
                Pelajar • Rekayasa Perangkat Lunak
            </div>

            <div class="text-start mb-4">

                <h2 class="h4 fw-bold">
                    Data Diri
                </h2>

                <p>
                    <strong>Nama:</strong>
                    Muhamad Fahril Fahrurozi
                </p>

                <p>
                    <strong>Kelas:</strong>
                    XII RPL 2
                </p>

                <p>
                    <strong>Sekolah:</strong>
                    SMK Muhammadiyah Tasikmalaya
                </p>

                <p>
                    <strong>Keahlian:</strong>
                    PHP, MySQL, HTML, CSS
                </p>

                <p>
                    <strong>Alamat:</strong>
                    Sambong Mangkubumi, Kota Tasikmalaya
                </p>

            </div>

            <div class="text-start mb-4">

                <h2 class="h4 fw-bold">
                    Tentang Saya
                </h2>

                <p class="text-secondary lh-lg">
                    Halo, saya Muhamad Fahril Fahrurozi.
                    Saya merupakan pelajar yang memiliki ketertarikan
                    dalam bidang pemrograman, website, database,
                    dan pengembangan aplikasi.
                </p>

            </div>


            <a href="dashboard.php" class="btn btn-dark px-4 py-2">
                Kembali ke Dashboard
            </a>

        </div>

    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>