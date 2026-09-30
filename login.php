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

<head>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Login - Sistem Pelanggaran Siswa</title>

    <link rel="stylesheet"
          href="assets/css/style.css?v=2">

    <link rel="stylesheet"
          href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
</head>

<body class="login-page">
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js" integrity="sha384-I7E8VVD/ismYTF4hNIPjVp/Zjvgyol6VFvRkX/vR+Vc4jQkC+hVqc2pM8ODewa9r" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.min.js" integrity="sha384-G/EV+4j2dNv+tEPo3++6LCgdCROaejBqfUeNjuKAiuXbjrxilcCdDz6ZAVfHWe1Y" crossorigin="anonymous"></script>
    <div class="card shadow border rounded">
        <div class="card-body">

            <h1>Sistem Pelanggaran Siswa</h1>

            <p>Silakan login untuk melanjutkan.</p>

            <?php if ($error != ""): ?>
                <p><?= htmlspecialchars($error); ?></p>
            <?php endif; ?>

            <form method="POST">

                <div class="login-form-group">

                    <label for="email">Email</label>

                    <input
                        type="email"
                        name="email"
                        id="email"
                        placeholder="Masukkan email"
                        required
                    >

                </div>

                <div class="login-form-group">

                    <label for="password">Password</label>

                    <input
                        type="password"
                        name="password"
                        id="password"
                        placeholder="Masukkan password"
                        autocomplete="new-password"
                        required
                    >

                </div>

                <button type="submit" name="login">
                    Login
                </button>

            </form>

        </div>
    </div>

</body>

</html>