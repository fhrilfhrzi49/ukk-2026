<?php
require_once __DIR__ . '/includes/cek_session.php';
require_once __DIR__ . '/config/koneksi.php';

function tampilkanError($judul, $pesan, $urlKembali)
{
    $e = fn($v) => htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');
    ?>
    <!DOCTYPE html>
    <html lang="id">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title><?= $e($judul) ?> | SI-TIB</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    </head>
    <body class="bg-light">
        <div class="container min-vh-100 d-flex align-items-center justify-content-center py-4">
            <div class="card border-0 shadow rounded-4 w-100" style="max-width:540px;">
                <div class="card-body p-4 p-md-5 text-center">
                    <div class="rounded-circle bg-warning-subtle text-warning-emphasis d-inline-flex align-items-center justify-content-center mb-3" style="width:80px;height:80px;">
                        <i class="bi bi-exclamation-triangle-fill fs-1"></i>
                    </div>
                    <h4 class="fw-bold mb-2"><?= $e($judul) ?></h4>
                    <p class="text-secondary mb-4">Data guru belum dapat diproses. Silakan periksa keterangan berikut.</p>
                    <div class="alert alert-warning border-0 rounded-3 text-start" role="alert">
                        <i class="bi bi-info-circle-fill me-2"></i><?= $e($pesan) ?>
                    </div>
                    <div class="d-grid gap-2 mt-4">
                        <a href="<?= $e($urlKembali) ?>" class="btn btn-primary btn-lg rounded-3">
                            <i class="bi bi-arrow-left-circle me-2"></i>Kembali
                        </a>
                        <a href="kelola_guru.php" class="btn btn-outline-secondary rounded-3">
                            <i class="bi bi-people me-2"></i>Kelola Data Guru
                        </a>
                    </div>
                    <hr class="my-4">
                    <div class="small text-secondary"><strong>SI-TIB</strong> — Sistem Informasi Tata Tertib</div>
                </div>
            </div>
        </div>
    </body>
    </html>
    <?php
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: kelola_guru.php');
    exit;
}

$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
$nip = trim($_POST['nip'] ?? '');
$nama = trim($_POST['nama'] ?? '');
$email = trim($_POST['email'] ?? '');
$status_aktif = $_POST['status_aktif'] ?? '';
$user_id = trim($_POST['user_id'] ?? '');
$password_baru = $_POST['password_baru'] ?? '';
$konfirmasi_password = $_POST['konfirmasi_password'] ?? '';

if (!$id || $id < 1) tampilkanError('ID Guru Tidak Valid', 'ID guru tidak valid.', 'kelola_guru.php');

if ($nip === '' || $nama === '' || $email === '') {
    tampilkanError('Data Belum Lengkap', 'NIP, nama, dan email wajib diisi.', 'edit_guru.php?id=' . $id);
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    tampilkanError('Format Email Tidak Valid', 'Masukkan alamat email dengan format yang benar.', 'edit_guru.php?id=' . $id);
}

if (!in_array((string) $status_aktif, ['0', '1'], true)) {
    tampilkanError('Status Aktif Tidak Valid', 'Pilih status aktif yang tersedia.', 'edit_guru.php?id=' . $id);
}

if ($password_baru !== '' || $konfirmasi_password !== '') {
    if ($password_baru === '' || $konfirmasi_password === '') {
        tampilkanError('Password Belum Lengkap', 'Password baru dan konfirmasi password wajib diisi.', 'edit_guru.php?id=' . $id);
    }

    if ($password_baru !== $konfirmasi_password) {
        tampilkanError('Konfirmasi Password Tidak Sesuai', 'Password baru dan konfirmasi password harus sama.', 'edit_guru.php?id=' . $id);
    }

    if ($user_id === '') {
        tampilkanError('Akun Pengguna Tidak Terhubung', 'Isi User ID terlebih dahulu untuk mengubah password akun guru.', 'edit_guru.php?id=' . $id);
    }
}

$stmt = mysqli_prepare($koneksi, "SELECT id FROM t_guru WHERE id = ?");

if (!$stmt) {
    tampilkanError('Kesalahan Database', 'Pemeriksaan data guru gagal dilakukan.', 'kelola_guru.php');
}

mysqli_stmt_bind_param($stmt, 'i', $id);
mysqli_stmt_execute($stmt);
$guru = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
mysqli_stmt_close($stmt);

if (!$guru) {
    tampilkanError('Data Guru Tidak Ditemukan', 'Data guru yang akan diedit tidak ditemukan.', 'kelola_guru.php');
}

$userIdInt = null;

if ($user_id !== '') {
    if (!ctype_digit($user_id) || (int) $user_id < 1) {
        tampilkanError('User ID Tidak Valid', 'User ID harus berupa angka positif.', 'edit_guru.php?id=' . $id);
    }

    $userIdInt = (int) $user_id;
    $stmt = mysqli_prepare($koneksi, "SELECT id FROM t_users WHERE id = ?");

    if (!$stmt) {
        tampilkanError('Kesalahan Database', 'Pemeriksaan akun pengguna gagal dilakukan.', 'edit_guru.php?id=' . $id);
    }

    mysqli_stmt_bind_param($stmt, 'i', $userIdInt);
    mysqli_stmt_execute($stmt);
    $user = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    mysqli_stmt_close($stmt);

    if (!$user) {
        tampilkanError(
            'User ID Tidak Ditemukan',
            "User ID {$userIdInt} tidak ditemukan di tabel t_users. Masukkan ID pengguna yang tersedia atau kosongkan User ID jika guru tidak perlu ditautkan ke akun.",
            'edit_guru.php?id=' . $id
        );
    }
}

mysqli_begin_transaction($koneksi);

if ($userIdInt === null) {
    $stmt = mysqli_prepare($koneksi, "UPDATE t_guru SET nip = ?, nama = ?, email = ?, status_aktif = ?, user_id = NULL WHERE id = ?");

    if ($stmt) {
        mysqli_stmt_bind_param($stmt, 'sssii', $nip, $nama, $email, $status_aktif, $id);
    }
} else {
    $stmt = mysqli_prepare($koneksi, "UPDATE t_guru SET nip = ?, nama = ?, email = ?, status_aktif = ?, user_id = ? WHERE id = ?");

    if ($stmt) {
        mysqli_stmt_bind_param($stmt, 'sssiii', $nip, $nama, $email, $status_aktif, $userIdInt, $id);
    }
}

if (!$stmt) {
    mysqli_rollback($koneksi);
    tampilkanError('Kesalahan Database', 'Pernyataan untuk memperbarui data guru gagal disiapkan.', 'edit_guru.php?id=' . $id);
}

if (!mysqli_stmt_execute($stmt)) {
    $kodeError = mysqli_stmt_errno($stmt);
    mysqli_stmt_close($stmt);
    mysqli_rollback($koneksi);

    if ($kodeError === 1062) {
        tampilkanError('Data Sudah Digunakan', 'NIP, email, atau data unik lainnya sudah digunakan oleh data lain.', 'edit_guru.php?id=' . $id);
    }

    if ($kodeError === 1452) {
        tampilkanError('Akun Pengguna Tidak Valid', 'User ID tidak tersedia atau tidak dapat ditautkan ke data guru.', 'edit_guru.php?id=' . $id);
    }

    tampilkanError('Gagal Memperbarui Data', 'Data guru gagal diperbarui. Periksa kembali data yang dimasukkan.', 'edit_guru.php?id=' . $id);
}

mysqli_stmt_close($stmt);

if ($password_baru !== '') {
    $password_hash = password_hash($password_baru, PASSWORD_DEFAULT);
    $stmt = mysqli_prepare($koneksi, "UPDATE t_users SET password = ? WHERE id = ?");

    if (!$stmt) {
        mysqli_rollback($koneksi);
        tampilkanError('Kesalahan Database', 'Pernyataan untuk mengubah password gagal disiapkan.', 'edit_guru.php?id=' . $id);
    }

    mysqli_stmt_bind_param($stmt, 'si', $password_hash, $userIdInt);

    if (!mysqli_stmt_execute($stmt) || mysqli_stmt_affected_rows($stmt) < 0) {
        mysqli_stmt_close($stmt);
        mysqli_rollback($koneksi);
        tampilkanError('Gagal Mengubah Password', 'Password akun guru gagal diperbarui.', 'edit_guru.php?id=' . $id);
    }

    mysqli_stmt_close($stmt);
}

if (!mysqli_commit($koneksi)) {
    mysqli_rollback($koneksi);
    tampilkanError('Gagal Menyimpan Data', 'Perubahan data guru gagal disimpan.', 'edit_guru.php?id=' . $id);
}

header('Location: kelola_guru.php');
exit;
?>