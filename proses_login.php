<?php

if (session_status() === PHP_SESSION_NONE) session_start();

require_once __DIR__ . '/config/koneksi.php';

function gagal_login(string $pesan): void
{
    $_SESSION['pesan_error'] = $pesan;
    header('Location: login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: login.php');
    exit;
}

$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';

if ($email === '' || $password === '') {
    gagal_login('Email dan password wajib diisi.');
}

$stmt = mysqli_prepare($koneksi, "SELECT id, name, email, password, role FROM t_users WHERE email = ? LIMIT 1");

if (!$stmt) {
    error_log('Login prepare error: ' . mysqli_error($koneksi));
    gagal_login('Terjadi kesalahan sistem. Silakan coba lagi.');
}

mysqli_stmt_bind_param($stmt, 's', $email);
mysqli_stmt_execute($stmt);
$data = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
mysqli_stmt_close($stmt);

if (!$data || !password_verify($password, $data['password'])) {
    gagal_login('Email atau password salah.');
}

$roleDatabase = strtolower(trim((string) $data['role']));
$roleDatabase = str_replace([' ', '-'], '_', $roleDatabase);

$aliasRole = [
    'administrator' => 'admin',
    'guru_sebagai_wali_kelas' => 'guru_wali_kelas',
    'guru_sekaligus_wali_kelas' => 'guru_wali_kelas',
    'wali_kelas_saja' => 'wali_kelas'
];

$roleDatabase = $aliasRole[$roleDatabase] ?? $roleDatabase;

if (!in_array($roleDatabase, ['admin', 'guru', 'guru_wali_kelas', 'wali_kelas'], true)) {
    gagal_login('Role akun tidak dikenali. Hubungi administrator.');
}

$userId = (int) $data['id'];
$namaTampilan = trim((string) $data['name']);
$idGuru = null;
$isWaliKelas = false;
$idPenugasanWaliKelas = null;
$idKelasWali = null;

if (in_array($roleDatabase, ['guru', 'guru_wali_kelas'], true)) {
    $stmtGuru = mysqli_prepare($koneksi, "
        SELECT id, nama, status_aktif
        FROM t_guru
        WHERE user_id = ?
        LIMIT 1
    ");

    if (!$stmtGuru) {
        error_log('Login guru prepare error: ' . mysqli_error($koneksi));
        gagal_login('Terjadi kesalahan saat memeriksa data guru.');
    }

    mysqli_stmt_bind_param($stmtGuru, 'i', $userId);
    mysqli_stmt_execute($stmtGuru);
    $guru = mysqli_fetch_assoc(mysqli_stmt_get_result($stmtGuru));
    mysqli_stmt_close($stmtGuru);

    if (!$guru) gagal_login('Akun belum terhubung dengan data guru.');
    if ((int) $guru['status_aktif'] !== 1) {
        gagal_login('Akun guru tidak aktif. Hubungi administrator.');
    }

    $idGuru = (int) $guru['id'];

    if (trim((string) $guru['nama']) !== '') {
        $namaTampilan = trim((string) $guru['nama']);
    }

    $sqlWali = "
        SELECT wk.id AS penugasan_id, wk.kelas_id
        FROM t_wali_kelas wk
        INNER JOIN t_tahun_ajaran ta ON ta.id = wk.tahun_ajaran_id
        WHERE wk.user_id = ?
          AND wk.status_aktif = 1
          AND ta.status_aktif = 1
          AND (wk.tanggal_mulai IS NULL OR CURDATE() >= wk.tanggal_mulai)
          AND (wk.tanggal_selesai IS NULL OR CURDATE() <= wk.tanggal_selesai)
        ORDER BY wk.id DESC
        LIMIT 1
    ";

    $stmtWali = mysqli_prepare($koneksi, $sqlWali);

    if (!$stmtWali) {
        error_log('Login wali kelas prepare error: ' . mysqli_error($koneksi));
        gagal_login('Terjadi kesalahan saat memeriksa penugasan wali kelas.');
    }

    mysqli_stmt_bind_param($stmtWali, 'i', $userId);
    mysqli_stmt_execute($stmtWali);
    $wali = mysqli_fetch_assoc(mysqli_stmt_get_result($stmtWali));
    mysqli_stmt_close($stmtWali);

    if ($wali) {
        $isWaliKelas = true;
        $idPenugasanWaliKelas = (int) $wali['penugasan_id'];
        $idKelasWali = (int) $wali['kelas_id'];
    }

    if ($roleDatabase === 'guru_wali_kelas' && !$isWaliKelas) {
        gagal_login('Penugasan Wali Kelas tidak ditemukan atau tidak aktif.');
    }
}

if ($roleDatabase === 'wali_kelas') {
    $sqlWali = "
        SELECT wk.id AS penugasan_id, wk.kelas_id
        FROM t_wali_kelas wk
        INNER JOIN t_tahun_ajaran ta ON ta.id = wk.tahun_ajaran_id
        WHERE wk.user_id = ?
          AND wk.guru_id IS NULL
          AND wk.status_aktif = 1
          AND ta.status_aktif = 1
          AND (wk.tanggal_mulai IS NULL OR CURDATE() >= wk.tanggal_mulai)
          AND (wk.tanggal_selesai IS NULL OR CURDATE() <= wk.tanggal_selesai)
        ORDER BY wk.id DESC
        LIMIT 1
    ";

    $stmtWali = mysqli_prepare($koneksi, $sqlWali);

    if (!$stmtWali) {
        error_log('Login wali kelas saja prepare error: ' . mysqli_error($koneksi));
        gagal_login('Terjadi kesalahan saat memeriksa penugasan Wali Kelas.');
    }

    mysqli_stmt_bind_param($stmtWali, 'i', $userId);
    mysqli_stmt_execute($stmtWali);
    $wali = mysqli_fetch_assoc(mysqli_stmt_get_result($stmtWali));
    mysqli_stmt_close($stmtWali);

    if (!$wali) {
        gagal_login('Akun Wali Kelas belum memiliki penugasan aktif.');
    }

    $isWaliKelas = true;
    $idPenugasanWaliKelas = (int) $wali['penugasan_id'];
    $idKelasWali = (int) $wali['kelas_id'];
}

$roleSession = $roleDatabase;

if ($roleDatabase === 'guru' && $isWaliKelas) {
    $roleSession = 'guru_wali_kelas';
}

session_regenerate_id(true);

$_SESSION['login'] = true;
$_SESSION['id'] = $userId;
$_SESSION['user_id'] = $userId;
$_SESSION['name'] = $namaTampilan;
$_SESSION['nama'] = $namaTampilan;
$_SESSION['email'] = $data['email'];
$_SESSION['role'] = $roleSession;

if ($idGuru !== null) {
    $_SESSION['guru_id'] = $idGuru;
} else {
    unset($_SESSION['guru_id']);
}

$_SESSION['is_wali_kelas'] = $isWaliKelas;
$_SESSION['wali_kelas_id'] = $idPenugasanWaliKelas;
$_SESSION['kelas_wali_id'] = $idKelasWali;

unset($_SESSION['pesan_error']);

header('Location: dashboard.php');
exit;