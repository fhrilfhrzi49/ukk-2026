<?php

// =========================================================
// buat_user_awal.php
// Membuat akun Admin pertama
// Jalankan SATU KALI saja
// Setelah berhasil, HAPUS file ini
// =========================================================

require_once "config/koneksi.php";

// =========================================================
// DATA USER ADMIN AWAL
// =========================================================

$nama = "Administrator";
$email = "admin@gmail.com";
$password_asli = "admin123";
$role = "admin";
$status = 1;

// =========================================================
// CEK APAKAH ADMIN SUDAH ADA
// =========================================================

$cek = mysqli_prepare(
    $koneksi,
    "SELECT id FROM users WHERE email = ? LIMIT 1"
);

mysqli_stmt_bind_param(
    $cek,
    "s",
    $email
);

mysqli_stmt_execute($cek);

$hasil_cek = mysqli_stmt_get_result($cek);

if (mysqli_num_rows($hasil_cek) > 0) {

    echo "
    <!DOCTYPE html>
    <html lang='id'>
    <head>
        <meta charset='UTF-8'>
        <meta name='viewport'
              content='width=device-width, initial-scale=1.0'>
        <title>User Sudah Ada</title>
        <style>
            body {
                font-family: Arial, sans-serif;
                background: #f5f5f5;
                padding: 30px;
            }

            .box {
                max-width: 500px;
                margin: 50px auto;
                background: white;
                padding: 30px;
                border-radius: 15px;
                box-shadow: 0 5px 20px rgba(0,0,0,.08);
                text-align: center;
            }

            h2 {
                color: #e67e22;
            }

            .btn {
                display: inline-block;
                margin-top: 15px;
                padding: 10px 18px;
                background: #e91e63;
                color: white;
                text-decoration: none;
                border-radius: 8px;
            }
        </style>
    </head>

    <body>

        <div class='box'>

            <h2>Admin Sudah Ada</h2>

            <p>
                Akun dengan email
                <strong>$email</strong>
                sudah terdaftar.
            </p>

            <p>
                Tidak membuat akun admin baru.
            </p>

            <a href='login.php' class='btn'>
                Ke Halaman Login
            </a>

        </div>

    </body>
    </html>
    ";

    mysqli_stmt_close($cek);
    exit;
}

mysqli_stmt_close($cek);


// =========================================================
// ENKRIPSI PASSWORD
// =========================================================

$password = password_hash(
    $password_asli,
    PASSWORD_DEFAULT
);


// =========================================================
// INSERT USER ADMIN
// =========================================================

$sql = "
    INSERT INTO users
    (
        name,
        email,
        password,
        role,
        status
    )
    VALUES
    (
        ?,
        ?,
        ?,
        ?,
        ?
    )
";

$stmt = mysqli_prepare($koneksi, $sql);

if (!$stmt) {

    die(
        "Query gagal dibuat: "
        . htmlspecialchars(mysqli_error($koneksi))
    );
}

mysqli_stmt_bind_param(
    $stmt,
    "ssssi",
    $nama,
    $email,
    $password,
    $role,
    $status
);


// =========================================================
// EKSEKUSI
// =========================================================

if (mysqli_stmt_execute($stmt)) {

?>

<!DOCTYPE html>
<html lang="id">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>User Admin Berhasil Dibuat</title>

    <style>

        body {
            font-family: Arial, sans-serif;
            background: #f5f5f5;
            padding: 30px;
        }

        .box {
            max-width: 550px;
            margin: 50px auto;
            background: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0,0,0,.08);
        }

        h2 {
            color: #2e7d32;
            margin-top: 0;
        }

        .data {
            background: #f8f8f8;
            padding: 15px;
            border-radius: 10px;
            margin: 20px 0;
        }

        .data p {
            margin: 8px 0;
        }

        .warning {
            background: #fff3cd;
            color: #856404;
            padding: 15px;
            border-radius: 10px;
            margin-top: 20px;
        }

        .btn {
            display: inline-block;
            margin-top: 15px;
            padding: 10px 18px;
            background: #e91e63;
            color: white;
            text-decoration: none;
            border-radius: 8px;
        }

    </style>

</head>

<body>

<div class="box">

    <h2>
        ✓ User Admin Berhasil Dibuat
    </h2>

    <p>
        Akun Administrator berhasil dibuat
        dengan password terenkripsi.
    </p>

    <div class="data">

        <p>
            <strong>Nama:</strong>
            <?= htmlspecialchars($nama); ?>
        </p>

        <p>
            <strong>Email:</strong>
            <?= htmlspecialchars($email); ?>
        </p>

        <p>
            <strong>Password:</strong>
            <?= htmlspecialchars($password_asli); ?>
        </p>

        <p>
            <strong>Role:</strong>
            <?= htmlspecialchars($role); ?>
        </p>

    </div>

    <div class="warning">

        <strong>PENTING!</strong>

        <br><br>

        Hapus file
        <strong>buat_user_awal.php</strong>
        setelah proses ini selesai.

    </div>

    <a href="login.php" class="btn">
        Ke Halaman Login
    </a>

</div>

</body>

</html>

<?php

} else {

    echo "
    <h2>Gagal membuat user admin.</h2>
    <p>
        Error:
        " . htmlspecialchars(mysqli_error($koneksi)) . "
    </p>
    ";

}

mysqli_stmt_close($stmt);

?>