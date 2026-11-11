
<?php
session_start();
require_once 'config/koneksi.php';


$host = $_SERVER['HTTP_HOST'] ?? '';
if (!in_array($host, ['localhost', 'localhost:80', '127.0.0.1', '127.0.0.1:80'], true)) {
    http_response_code(403);
    exit('Akses ditolak.');
}

$pesan = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password = $_POST['password'] ?? '';
    $konfirmasi = $_POST['konfirmasi'] ?? '';

    if (strlen($password) < 8) {
        $pesan = 'Password minimal 8 karakter.';
    } elseif ($password !== $konfirmasi) {
        $pesan = 'Konfirmasi password tidak sama.';
    } else {
        $email = 'guru001@ukk2026.test';
        $hash = password_hash($password, PASSWORD_DEFAULT);

        $stmt = mysqli_prepare(
            $koneksi,
            "UPDATE t_users SET password = ? WHERE email = ? AND role = 'guru'"
        );

        if (!$stmt) {
            $pesan = 'Gagal menyiapkan pembaruan password.';
        } else {
            mysqli_stmt_bind_param($stmt, 'ss', $hash, $email);
            mysqli_stmt_execute($stmt);

            if (mysqli_stmt_affected_rows($stmt) === 1) {
                $pesan = 'Password berhasil diperbarui. Silakan login kembali.';
            } else {
                $pesan = 'Password belum diperbarui. Periksa akun dan database.';
            }

            mysqli_stmt_close($stmt);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Reset Password Guru | SI-TIB</title>
</head>
<body>
    <h2>Reset Password Guru 001</h2>

    <?php if ($pesan !== ''): ?>
        <p><?= htmlspecialchars($pesan, ENT_QUOTES, 'UTF-8') ?></p>
    <?php endif; ?>

    <form method="post">
        <p>
            <label>Password baru (minimal 8 karakter)</label><br>
            <input type="password" name="password" minlength="8"
                   autocomplete="new-password" required>
        </p>
        <p>
            <label>Ulangi password baru</label><br>
            <input type="password" name="konfirmasi"
                   autocomplete="new-password" required>
        </p>
        <button type="submit">Simpan Password Baru</button>
    </form>
</body>
</html>
