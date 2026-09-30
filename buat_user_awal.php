<?php

require_once "config/koneksi.php";

$nama = "Administrator";
$email = "admin@gmail.com";
$password_asli = "admin123";
$role = "admin";
$status = 1;

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
        <meta name='viewport' content='width=device-width, initial-scale=1.0'>
        <title>User Sudah Ada</title>
    </head>

    <body>

        <h2>Admin Sudah Ada</h2>

        <p>
            Akun dengan email
            <strong>$email</strong>
            sudah terdaftar.
        </p>

        <p>
            Tidak membuat akun admin baru.
        </p>

        <a href='login.php'>
            Ke Halaman Login
        </a>

    </body>

    </html>
    ";

    mysqli_stmt_close($cek);
    exit;
}

mysqli_stmt_close($cek);

$password = password_hash(
    $password_asli,
    PASSWORD_DEFAULT
);

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

if (mysqli_stmt_execute($stmt)) {

?>

<!DOCTYPE html>
<html lang="id">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>User Admin Berhasil Dibuat</title>

</head>

<body>

<h2>
    User Admin Berhasil Dibuat
</h2>

<p>
    Akun Administrator berhasil dibuat
    dengan password terenkripsi.
</p>

<h3>Data Akun</h3>

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

<hr>

<h3>PENTING!</h3>

<p>
    Hapus file
    <strong>buat_user_awal.php</strong>
    setelah proses ini selesai.
</p>

<p>
    <a href="login.php">
        Ke Halaman Login
    </a>
</p>

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