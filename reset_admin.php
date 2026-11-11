<?php
session_start();
require_once "config/koneksi.php";


$kunci_reset = "ResetAdmin2026-Fahril";

$pesan = "";
$jenis_pesan = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $kunci = $_POST["kunci"] ?? "";
    $email = trim($_POST["email"] ?? "");
    $password_baru = $_POST["password_baru"] ?? "";
    $konfirmasi = $_POST["konfirmasi"] ?? "";

    if (!hash_equals($kunci_reset, $kunci)) {
        $pesan = "Kunci reset salah.";
        $jenis_pesan = "danger";

    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $pesan = "Format email tidak valid.";
        $jenis_pesan = "danger";

    } elseif (strlen($password_baru) < 8) {
        $pesan = "Password minimal 8 karakter.";
        $jenis_pesan = "danger";

    } elseif ($password_baru !== $konfirmasi) {
        $pesan = "Konfirmasi password tidak sama.";
        $jenis_pesan = "danger";

    } else {
        
        $cek = mysqli_prepare(
            $koneksi,
            "SELECT id FROM t_users
             WHERE email = ? AND role = 'admin'
             LIMIT 1"
        );

        if (!$cek) {
            $pesan = "Gagal menyiapkan pemeriksaan akun.";
            $jenis_pesan = "danger";
        } else {
            mysqli_stmt_bind_param($cek, "s", $email);
            mysqli_stmt_execute($cek);
            $hasil = mysqli_stmt_get_result($cek);
            $admin = mysqli_fetch_assoc($hasil);
            mysqli_stmt_close($cek);

            if (!$admin) {
                $pesan = "Akun admin dengan email tersebut tidak ditemukan.";
                $jenis_pesan = "danger";
            } else {
                
                $hash = password_hash(
                    $password_baru,
                    PASSWORD_DEFAULT
                );

                
                $update = mysqli_prepare(
                    $koneksi,
                    "UPDATE t_users
                     SET password = ?
                     WHERE id = ? AND role = 'admin'"
                );

                if (!$update) {
                    $pesan = "Gagal menyiapkan proses reset password.";
                    $jenis_pesan = "danger";
                } else {
                    $id_admin = (int) $admin["id"];

                    mysqli_stmt_bind_param(
                        $update,
                        "si",
                        $hash,
                        $id_admin
                    );

                    if (mysqli_stmt_execute($update)) {
                        $pesan = "Password admin berhasil direset. Silakan login menggunakan password baru.";
                        $jenis_pesan = "success";
                    } else {
                        $pesan = "Gagal mereset password admin.";
                        $jenis_pesan = "danger";
                    }

                    mysqli_stmt_close($update);
                }
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Reset Admin | SI-TIB</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >
</head>

<body class="bg-light">

<div class="container min-vh-100 d-flex align-items-center justify-content-center py-4">
    <div class="card border-0 shadow rounded-4 w-100" style="max-width: 480px;">
        <div class="card-body p-4 p-md-5">

            <h3 class="fw-bold mb-2">Reset Password Admin</h3>

            <p class="text-secondary mb-4">
                Sistem Pelanggaran Siswa — UKK 2026
            </p>

            <?php if ($pesan !== ""): ?>
                <div class="alert alert-<?= htmlspecialchars($jenis_pesan) ?>" role="alert">
                    <?= htmlspecialchars($pesan) ?>
                </div>
            <?php endif; ?>

            <form method="POST">
                <div class="mb-3">
                    <label for="kunci" class="form-label">Kunci Reset</label>
                    <input
                        type="password"
                        name="kunci"
                        id="kunci"
                        class="form-control"
                        autocomplete="off"
                        required
                    >
                </div>

                <div class="mb-3">
                    <label for="email" class="form-label">Email Admin</label>
                    <input
                        type="email"
                        name="email"
                        id="email"
                        class="form-control"
                        value="admin@gmail.com"
                        required
                    >
                </div>

                <div class="mb-3">
                    <label for="password_baru" class="form-label">Password Baru</label>
                    <input
                        type="password"
                        name="password_baru"
                        id="password_baru"
                        class="form-control"
                        minlength="8"
                        autocomplete="new-password"
                        required
                    >
                </div>

                <div class="mb-4">
                    <label for="konfirmasi" class="form-label">
                        Konfirmasi Password
                    </label>
                    <input
                        type="password"
                        name="konfirmasi"
                        id="konfirmasi"
                        class="form-control"
                        minlength="8"
                        autocomplete="new-password"
                        required
                    >
                </div>

                <button type="submit" class="btn btn-dark w-100">
                    Reset Password Admin
                </button>

                <a href="login.php" class="btn btn-outline-secondary w-100 mt-2">
                    Kembali ke Login
                </a>
            </form>

        </div>
    </div>
</div>

</body>
</html>