<?php

require_once __DIR__ . '/includes/cek_session.php';
require_once __DIR__ . '/config/koneksi.php';

function gagal($pesan)
{
    ?>
    <!DOCTYPE html>
    <html lang="id">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Tambah Guru | SI-TIB</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    </head>
    <body class="bg-light">
        <div class="container min-vh-100 d-flex align-items-center justify-content-center py-4">
            <div class="card border-0 shadow rounded-4 w-100" style="max-width: 540px;">
                <div class="card-body p-4 p-md-5 text-center">
                    <div class="rounded-circle bg-danger-subtle text-danger d-inline-flex align-items-center justify-content-center mb-4" style="width: 85px; height: 85px;">
                        <i class="bi bi-x-circle-fill fs-1"></i>
                    </div>
                    <h4 class="fw-bold text-dark mb-2">Gagal Menambahkan Guru</h4>
                    <p class="text-secondary mb-4">Data guru belum dapat disimpan. Silakan periksa keterangan berikut.</p>
                    <div class="alert alert-danger border-0 rounded-3 text-start" role="alert">
                        <div class="d-flex align-items-start gap-2">
                            <i class="bi bi-exclamation-triangle-fill fs-5"></i>
                            <div>
                                <strong>Terjadi Kesalahan</strong>
                                <div class="mt-1"><?= htmlspecialchars($pesan, ENT_QUOTES, 'UTF-8') ?></div>
                            </div>
                        </div>
                    </div>
                    <div class="d-grid gap-2 mt-4">
                        <a href="tambah_guru.php" class="btn btn-primary btn-lg rounded-3">
                            <i class="bi bi-arrow-left-circle me-2"></i>Kembali ke Form Tambah Guru
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

$nip = trim($_POST['nip'] ?? '');
$nama = trim($_POST['nama'] ?? '');
$email = trim($_POST['email'] ?? '');
$status_aktif = $_POST['status_aktif'] ?? '1';
$role = trim($_POST['role'] ?? 'guru');
$password = $_POST['password'] ?? '';
$konfirmasi_password = $_POST['konfirmasi_password'] ?? '';

if ($nip === '' || $nama === '' || $email === '') gagal('NIP, nama, dan email wajib diisi.');
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) gagal('Format email tidak valid.');
if (!in_array((string) $status_aktif, ['0', '1'], true)) gagal('Status aktif tidak valid.');
if (!in_array($role, ['guru', 'guru_wali_kelas', 'wali_kelas'], true)) gagal('Role pengguna tidak valid.');
if ($password === '') gagal('Password akun guru wajib diisi.');
if (strlen($password) < 8) gagal('Password minimal 8 karakter.');
if ($password !== $konfirmasi_password) gagal('Konfirmasi password tidak cocok.');

$passwordHash = password_hash($password, PASSWORD_DEFAULT);
if ($passwordHash === false) gagal('Password gagal diproses.');

mysqli_begin_transaction($koneksi);

try {
    $stmt = mysqli_prepare($koneksi, "SELECT id FROM t_guru WHERE nip = ? LIMIT 1");
    if (!$stmt) throw new Exception('Pemeriksaan NIP gagal dilakukan.');

    mysqli_stmt_bind_param($stmt, 's', $nip);
    mysqli_stmt_execute($stmt);
    $cekNip = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    mysqli_stmt_close($stmt);

    if ($cekNip) {
        throw new Exception('NIP sudah digunakan oleh guru lain. Silakan gunakan NIP yang berbeda.');
    }

    $stmt = mysqli_prepare($koneksi, "SELECT id FROM t_users WHERE email = ? LIMIT 1");
    if (!$stmt) throw new Exception('Pemeriksaan email gagal dilakukan.');

    mysqli_stmt_bind_param($stmt, 's', $email);
    mysqli_stmt_execute($stmt);
    $cekEmail = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    mysqli_stmt_close($stmt);

    if ($cekEmail) throw new Exception('Email sudah digunakan oleh akun pengguna lain.');

    $stmt = mysqli_prepare(
        $koneksi,
        "INSERT INTO t_users (name, email, password, role, created_at, updated_at)
         VALUES (?, ?, ?, ?, NOW(), NOW())"
    );

    if (!$stmt) throw new Exception('Gagal menyiapkan pembuatan akun.');

    mysqli_stmt_bind_param($stmt, 'ssss', $nama, $email, $passwordHash, $role);

    if (!mysqli_stmt_execute($stmt)) throw new Exception('Gagal membuat akun pengguna.');

    $userId = mysqli_insert_id($koneksi);
    mysqli_stmt_close($stmt);

    if (!$userId) throw new Exception('ID akun pengguna gagal diperoleh.');

    $statusInt = (int) $status_aktif;

    $stmt = mysqli_prepare(
        $koneksi,
        "INSERT INTO t_guru (nip, nama, email, status_aktif, user_id, created_at, updated_at)
         VALUES (?, ?, ?, ?, ?, NOW(), NOW())"
    );

    if (!$stmt) throw new Exception('Gagal menyiapkan penyimpanan data guru.');

    mysqli_stmt_bind_param($stmt, 'sssii', $nip, $nama, $email, $statusInt, $userId);

    if (!mysqli_stmt_execute($stmt)) throw new Exception('Gagal menyimpan data guru.');

    mysqli_stmt_close($stmt);
    mysqli_commit($koneksi);

    header('Location: kelola_guru.php?pesan=berhasil_tambah');
    exit;

} catch (Throwable $e) {
    mysqli_rollback($koneksi);
    error_log('Tambah Guru Error: ' . $e->getMessage());
    gagal($e->getMessage());
}