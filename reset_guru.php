<?php

require_once "config/koneksi.php";

$email = "guru@gmail.com";
$password = "guru123";

$hash = password_hash($password, PASSWORD_DEFAULT);

$sql = "UPDATE users SET password = ?, status = 1, role = 'guru' WHERE email = ?";

$stmt = mysqli_prepare($koneksi, $sql);

if (!$stmt) {
    die("Query gagal: " . mysqli_error($koneksi));
}

mysqli_stmt_bind_param($stmt, "ss", $hash, $email);

if (mysqli_stmt_execute($stmt)) {

    if (mysqli_stmt_affected_rows($stmt) > 0) {
        echo "Password guru berhasil diubah menjadi: guru123";
    } else {
        echo "Data guru tidak ditemukan.";
    }

} else {
    echo "Gagal mengubah password: " . mysqli_stmt_error($stmt);
}

mysqli_stmt_close($stmt);
?>