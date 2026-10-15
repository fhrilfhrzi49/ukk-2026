<?php

require_once "config/koneksi.php";

$pesan = "";

if (isset($_POST['daftar'])) {

    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $role = $_POST['role'] ?? 'guru';

    if ($name == "" || $email == "" || $password == "") {

        $pesan = "Semua data wajib diisi.";

    } else {

        $cek = mysqli_prepare(
            $koneksi,
            "SELECT id FROM users WHERE email = ? LIMIT 1"
        );

        mysqli_stmt_bind_param($cek, "s", $email);
        mysqli_stmt_execute($cek);

        $hasil = mysqli_stmt_get_result($cek);

        if (mysqli_num_rows($hasil) > 0) {

            $pesan = "Email sudah terdaftar.";

        } else {

            $hash = password_hash($password, PASSWORD_DEFAULT);

            $stmt = mysqli_prepare(
                $koneksi,
                "INSERT INTO users (name, email, password, role, status)
                 VALUES (?, ?, ?, ?, 1)"
            );

            mysqli_stmt_bind_param(
                $stmt,
                "ssss",
                $name,
                $email,
                $hash,
                $role
            );

            if (mysqli_stmt_execute($stmt)) {

                $pesan = "Akun berhasil dibuat.";

            } else {

                $pesan = "Gagal membuat akun.";
            }

            mysqli_stmt_close($stmt);
        }

        mysqli_stmt_close($cek);
    }
}

?>

<!DOCTYPE html>
<html lang="id">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Daftar Akun - Sistem Pelanggaran Siswa</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

</head>

<body class="bg-light">

<div class="min-vh-100 d-flex justify-content-center align-items-center p-3">

    <div class="card border-0 shadow rounded-4 w-100" style="max-width: 430px;">

        <div class="card-body p-4 p-md-5">

            <h2 class="fw-bold text-center mb-4">
                Daftar Akun
            </h2>

            <?php if ($pesan != ""): ?>

                <div class="alert alert-info">
                    <?= htmlspecialchars($pesan); ?>
                </div>

            <?php endif; ?>

            <form method="POST">

                <div class="mb-3">

                    <label class="form-label">
                        Nama
                    </label>

                    <input
                        type="text"
                        name="name"
                        class="form-control"
                        placeholder="Masukkan nama"
                        required
                    >

                </div>

                <div class="mb-3">

                    <label class="form-label">
                        Email
                    </label>

                    <input
                        type="email"
                        name="email"
                        class="form-control"
                        placeholder="Masukkan email"
                        required
                    >

                </div>

                <div class="mb-3">

                    <label class="form-label">
                        Password
                    </label>

                    <input
                        type="password"
                        name="password"
                        class="form-control"
                        placeholder="Masukkan password"
                        required
                    >

                </div>

                <div class="mb-4">

                    <label class="form-label">
                        Role
                    </label>

                    <select name="role" class="form-select" required>

                        <option value="guru">
                            Guru
                        </option>

                        <option value="wali_kelas">
                            Wali Kelas
                        </option>

                        <option value="admin">
                            Administrator
                        </option>

                    </select>

                </div>

                <button
                    type="submit"
                    name="daftar"
                    class="btn btn-dark w-100"
                >
                    Daftar
                </button>

            </form>

            <div class="text-center mt-3">

                <a href="login.php">
                    Sudah punya akun? Login
                </a>

            </div>

        </div>

    </div>

</div>

</body>

</html>