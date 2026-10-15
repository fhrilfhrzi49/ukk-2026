<?php

session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

require_once "config/koneksi.php";

?>

<!DOCTYPE html>
<html lang="id">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Cetak Laporan - Sistem Pelanggaran Siswa</title>

    <!-- Bootstrap -->
    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

</head>

<body class="bg-light">

<div class="container py-5">

    <div class="card border-0 shadow rounded-4">

        <div class="card-body p-4 p-md-5">

            <!-- JUDUL -->
            <div class="text-center mb-4">

                <h2 class="fw-bold mb-2">
                    LAPORAN PELANGGARAN SISWA
                </h2>

                <p class="text-secondary mb-0">
                    SISTEM PELANGGARAN SISWA
                </p>

            </div>


            <!-- TABEL -->
            <div class="table-responsive">

                <table class="table table-bordered table-striped align-middle">

                    <thead class="table-dark">

                        <tr>

                            <th class="text-center">
                                No
                            </th>

                            <th>
                                Nama Siswa
                            </th>

                            <th>
                                Pelanggaran
                            </th>

                            <th class="text-center">
                                Poin
                            </th>

                            <th class="text-center">
                                Tanggal
                            </th>

                        </tr>

                    </thead>

                    <tbody>

                        <tr>

                            <td class="text-center">
                                1
                            </td>

                            <td>
                                -
                            </td>

                            <td>
                                -
                            </td>

                            <td class="text-center">
                                -
                            </td>

                            <td class="text-center">
                                -
                            </td>

                        </tr>

                    </tbody>

                </table>

            </div>


            <!-- TANDA TANGAN -->
            <div class="text-end mt-5">

                <p>
                    Tasikmalaya, ....................
                </p>

                <br>
                <br>
                <br>

                <p class="fw-bold mb-0">
                    Administrator
                </p>

            </div>


            <!-- BUTTON -->
            <div class="text-center mt-4">

                <button
                    type="button"
                    onclick="window.print()"
                    class="btn btn-dark px-4 me-2"
                >
                    Cetak Laporan
                </button>

                <a
                    href="dashboard.php"
                    class="btn btn-outline-secondary px-4"
                >
                    Kembali ke Dashboard
                </a>

            </div>


            <!-- COPYRIGHT -->
            <footer class="mt-5 pt-3 border-top text-center text-secondary">

                <small>
                    © 2026 Sistem Pelanggaran Siswa — Muhamad Fahril Fahrurozi
                </small>

            </footer>

        </div>

    </div>

</div>


<!-- Bootstrap JS -->
<script
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js">
</script>

</body>

</html>