<?php

session_start();

require_once "config/koneksi.php";

$error = "";

if (isset($_POST['login'])) {

    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($email == "" || $password == "") {

        $error = "Email dan password wajib diisi.";

    } else {

        $sql = "SELECT * FROM users WHERE email = ? LIMIT 1";

        $stmt = mysqli_prepare($koneksi, $sql);

        if (!$stmt) {

            die("Query gagal: " . mysqli_error($koneksi));

        }

        mysqli_stmt_bind_param(
            $stmt,
            "s",
            $email
        );

        mysqli_stmt_execute($stmt);

        $hasil = mysqli_stmt_get_result($stmt);

        $user = mysqli_fetch_assoc($hasil);

        if (!$user) {

            $error = "Email tidak ditemukan.";

        } elseif ($user['status'] != 1) {

            $error = "Akun tidak aktif.";

        } elseif (!password_verify($password, $user['password'])) {

            $error = "Password salah.";

        } else {

            // Login berhasil

            session_regenerate_id(true);

            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['role'] = $user['role'];

            header("Location: dashboard.php");
            exit;
        }

        mysqli_stmt_close($stmt);
    }
}

?>

<!DOCTYPE html>
<html lang="id">

</body>

</html>

<!DOCTYPE html>
<html lang="id">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Login - Sistem Pelanggaran Siswa</title>

    <link rel="stylesheet"
          href="assets/css/style.css?v=2">

</head>

<body class="login-page">

    <div class="login-card">

        <h1>
            Sistem Pelanggaran Siswa
        </h1>

        <p>
            Silakan login untuk melanjutkan.
        </p>

        <form method="POST">

            <div class="login-form-group">

                <label>
                    Email
                </label>

                <input
                    type="email"
                    name="email"
                    placeholder="Masukkan email"
                    required
                >

            </div>

            <div class="login-form-group">

                <label>
                    Password
                </label>

                <input
                    type="password"
                    name="password"
                    placeholder="Masukkan password"
                    required
                >

            </div>

            <button
                type="submit"
                name="login"
                class="login-button"
            >
                Masuk
            </button>

        </form>

    </div>

</body>

</html>