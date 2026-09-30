<?php

session_start();

if (!isset($_SESSION['user_id'])) {
    die("ANDA TIDAK BISA MENGAKSES HALAMAN INI");
}

require_once "config/koneksi.php";
?>

    <!DOCTYPE html>
    <html lang="id">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">

        <title>Tidak Ada Tampilan</title>
    </head>

    <body>

        <div class="content">
                <h2>Tampilan Belum Tersedia</h2>

                <p>
                    HIDUP JOKOW
                </p>

                <a href="dashboard.php" class="btn-dashboard">
                    Kembali ke Dashboard
                </a>

            </div>

        </div>

    </body>
    </html>

    <?php
    exit;

require_once "config/koneksi.php";
?>