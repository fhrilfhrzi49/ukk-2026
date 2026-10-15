<?php

session_start();

require_once "config/koneksi.php";

$error = "";
$success = "";

/* =========================
   LOGIN
========================= */

if (isset($_POST['login'])) {

    $email = trim($_POST['email'] ?? '');
    $password =$_POST['password'] ?? '';

    if ($email == "" or $password == "") {

        $error = "Email dan password wajib diisi.";

    } else {

        $sql = "SELECT * FROM users WHERE email = ? LIMIT 1";

        $stmt = mysqli_prepare($koneksi,$sql);

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

        } elseif (!password_verify($password,$user['password'])) {

            $error = "Password salah.";

        } else {

            session_regenerate_id(true);

            $_SESSION['user_id'] =$user['id'];
            $_SESSION['user_name'] =$user['name'];
            $_SESSION['user_email'] =$user['email'];
            
            $role_final =$user['role'];

            // Jika role guru, cek apakah terdaftar sebagai wali kelas
            if ($role_final === 'guru') {
                $q_guru = mysqli_query($koneksi, "SELECT * FROM guru WHERE email = '$email' LIMIT 1");
                if ($q_guru and mysqli_num_rows($q_guru) > 0) {$d_guru = mysqli_fetch_assoc($q_guru);$id_guru = 0;
                    if (isset($d_guru['id'])) {
                        $id_guru =$d_guru['id'];
                    } elseif (isset($d_guru['id_guru'])) {
                        $id_guru =$d_guru['id_guru'];
                    }

                    if ($id_guru > 0) {
                        $res_col = mysqli_query($koneksi, "SHOW COLUMNS FROM wali_kelas");
                        $kolom_wk = [];
                        if ($res_col) {
                            while ($col = mysqli_fetch_assoc($res_col)) {
                                $kolom_wk[] =$col['Field'];
                            }
                        }

                        $kolom_relasi = '';
                        if (in_array('guru_id', $kolom_wk)) {$kolom_relasi = 'guru_id';
                        } elseif (in_array('id_guru', $kolom_wk)) {$kolom_relasi = 'id_guru';
                        }

                        if ($kolom_relasi !== '') {
                            $q_wk = mysqli_query($koneksi, "SELECT * FROM wali_kelas WHERE `$kolom_relasi` = '$id_guru' LIMIT 1");
                            if ($q_wk and mysqli_num_rows($q_wk) > 0) {$role_final = 'wali_kelas';
                            }
                        }
                    }
                }
            }

            $_SESSION['role'] =$role_final;

            header("Location: dashboard.php");
            exit;
        }

        mysqli_stmt_close($stmt);
    }
}


/* =========================
   DAFTAR AKUN
========================= */

if (isset($_POST['daftar'])) {

    $email_daftar = trim($_POST['email_daftar'] ?? '');
    $password_daftar =$_POST['password_daftar'] ?? '';

    if ($email_daftar == "" or $password_daftar == "") {

        $error = "Email dan password wajib diisi.";

    } else {

        $cek = mysqli_prepare($koneksi,
            "SELECT id FROM users WHERE email = ? LIMIT 1"
        );

        if (!$cek) {
            die("Query gagal: " . mysqli_error($koneksi));
        }

        mysqli_stmt_bind_param(
            $cek,
            "s",
            $email_daftar
        );

        mysqli_stmt_execute($cek);

        $hasil_cek = mysqli_stmt_get_result($cek);

        if (mysqli_num_rows($hasil_cek) > 0) {

            $error = "Email sudah terdaftar.";

        } else {

            $password_hash = password_hash($password_daftar,
                PASSWORD_DEFAULT
            );

            $name =$email_daftar;
            $role = "guru";

            $stmt = mysqli_prepare($koneksi,
                "INSERT INTO users
                (name, email, password, role, status)
                VALUES (?, ?, ?, ?, 1)"
            );

            if (!$stmt) {
                die("Query gagal: " . mysqli_error($koneksi));
            }

            mysqli_stmt_bind_param(
                $stmt,
                "ssss",
                $name,$email_daftar,
                $password_hash,$role
            );

            if (mysqli_stmt_execute($stmt)) {

                $success = "Akun berhasil dibuat. Silakan login.";

            } else {

                $error = "Gagal membuat akun: " . mysqli_error($koneksi);

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

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Login - Sistem Pelanggaran Siswa</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css"
        rel="stylesheet"
        integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB"
        crossorigin="anonymous"
    >

</head>

<body class="bg-light">

<div class="min-vh-100 d-flex justify-content-center align-items-center p-3">

    <div
        class="card border-0 shadow rounded-4"
        style="width: 100%; max-width: 430px;"
    >

        <div class="card-body p-4 p-md-5">

            <!-- =========================
                HALAMAN LOGIN
            ========================= -->

            <div id="halamanLogin">

                <div class="text-center mb-4">

                    <h2 class="fw-bold">
                        Sistem Pelanggaran Siswa
                    </h2>

                    <p class="text-secondary mb-0">
                        Silakan login untuk melanjutkan.
                    </p>

                </div>


                <?php if ($error != "" and !isset($_POST['daftar'])): ?>

                    <div class="alert alert-danger">
                        <?= htmlspecialchars($error); ?>
                    </div>

                <?php endif; ?>


                <?php if ($success != ""): ?>

                    <div class="alert alert-success">
                        <?= htmlspecialchars($success); ?>
                    </div>

                <?php endif; ?>


                <form method="POST" autocomplete="off">

                    <div class="mb-3">

                        <label
                            for="email"
                            class="form-label"
                        >
                            Email
                        </label>

                        <input
                            type="email"
                            name="email"
                            id="email"
                            class="form-control form-control-lg"
                            placeholder="Masukkan email"
                            autocomplete="off"
                            required
                        >

                    </div>


                    <div class="mb-4">

                        <label
                            for="password"
                            class="form-label"
                        >
                            Password
                        </label>

                        <div class="input-group">

                            <input
                                type="password"
                                name="password"
                                id="password"
                                class="form-control form-control-lg"
                                placeholder="Masukkan password"
                                autocomplete="new-password"
                                value=""
                                readonly
                                required
                            >

                            <button
                                type="button"
                                class="btn btn-outline-secondary"
                                id="togglePassword"
                                title="Lihat password"
                            >
                                👁
                            </button>

                        </div>

                    </div>


                    <button
                        type="submit"
                        name="login"
                        class="btn btn-dark btn-lg w-100"
                    >
                        Login
                    </button>

                </form>


                <div class="text-center mt-3">

                    <button
                        type="button"
                        class="btn btn-link text-decoration-none"
                        id="btnDaftar"
                    >
                        Daftar Akun
                    </button>

                </div>

            </div>


            <!-- =========================
                HALAMAN DAFTAR
            ========================= -->

            <div
                id="halamanDaftar"
                class="d-none"
            >

                <div class="text-center mb-4">

                    <h2 class="fw-bold">
                        Daftar Akun
                    </h2>

                    <p class="text-secondary mb-0">
                        Silakan isi data untuk membuat akun.
                    </p>

                </div>


                <?php if ($error != "" and isset($_POST['daftar'])): ?>

                    <div class="alert alert-danger">
                        <?= htmlspecialchars($error); ?>
                    </div>

                <?php endif; ?>


                <form method="POST" autocomplete="off">

                    <div class="mb-3">

                        <label
                            for="email_daftar"
                            class="form-label"
                        >
                            Email
                        </label>

                        <input
                            type="email"
                            name="email_daftar"
                            id="email_daftar"
                            class="form-control"
                            placeholder="Masukkan email"
                            autocomplete="off"
                            required
                        >

                    </div>


                    <div class="mb-4">

                        <label
                            for="password_daftar"
                            class="form-label"
                        >
                            Password
                        </label>

                        <div class="input-group">

                            <input
                                type="password"
                                name="password_daftar"
                                id="password_daftar"
                                class="form-control"
                                placeholder="Masukkan password"
                                autocomplete="new-password"
                                value=""
                                readonly
                                required
                            >

                            <button
                                type="button"
                                class="btn btn-outline-secondary"
                                id="togglePasswordDaftar"
                            >
                                👁
                            </button>

                        </div>

                    </div>


                    <button
                        type="submit"
                        name="daftar"
                        class="btn btn-dark btn-lg w-100"
                    >
                        Daftar
                    </button>

                </form>


                <div class="text-center mt-3">

                    <button
                        type="button"
                        class="btn btn-link text-decoration-none"
                        id="btnLogin"
                    >
                        Sudah punya akun? Login
                    </button>

                </div>

            </div>

        </div>


        <footer class="mt-4 pt-3 border-top text-center text-secondary">

            <small>
                © 2026 Sistem Pelanggaran Siswa — Muhamad Fahril Fahrurozi
            </small>

        </footer>

    </div>

</div>


<script
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"
    integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYJrW7qNfN8Q2P3s3P4mX0n5B0n"
    crossorigin="anonymous">
</script>


<script>

/* =========================
   PINDAH LOGIN / DAFTAR
========================= */

const halamanLogin = document.getElementById("halamanLogin");
const halamanDaftar = document.getElementById("halamanDaftar");

const btnDaftar = document.getElementById("btnDaftar");
const btnLogin = document.getElementById("btnLogin");

btnDaftar.addEventListener("click", function () {

    halamanLogin.classList.add("d-none");

    halamanDaftar.classList.remove("d-none");

});


btnLogin.addEventListener("click", function () {

    halamanDaftar.classList.add("d-none");

    halamanLogin.classList.remove("d-none");

});


/* =========================
   LIHAT PASSWORD LOGIN
========================= */

const password = document.getElementById("password");
const togglePassword = document.getElementById("togglePassword");

password.value = "";

password.addEventListener("focus", function () {

    password.removeAttribute("readonly");

});

togglePassword.addEventListener("click", function () {

    password.removeAttribute("readonly");

    if (password.type === "password") {

        password.type = "text";

        togglePassword.innerHTML = "🙈";

    } else {

        password.type = "password";

        togglePassword.innerHTML = "👁";

    }

});


/* =========================
   LIHAT PASSWORD DAFTAR
========================= */

const passwordDaftar =
    document.getElementById("password_daftar");

const togglePasswordDaftar =
    document.getElementById("togglePasswordDaftar");

passwordDaftar.value = "";

passwordDaftar.addEventListener("focus", function () {

    passwordDaftar.removeAttribute("readonly");

});

togglePasswordDaftar.addEventListener("click", function () {

    passwordDaftar.removeAttribute("readonly");

    if (passwordDaftar.type === "password") {

        passwordDaftar.type = "text";

        togglePasswordDaftar.innerHTML = "🙈";

    } else {

        passwordDaftar.type = "password";

        togglePasswordDaftar.innerHTML = "👁";

    }

});

</script>

</body>

</html>