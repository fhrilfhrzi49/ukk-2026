<?php

require_once __DIR__ . '/config/akses.php';
wajib_role(['admin', 'administrator']);

require_once __DIR__ . '/includes/cek_session.php';
require_once __DIR__ . '/config/koneksi.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: kelola_tahun_ajaran.php');
    exit;
}

$nama = trim($_POST['nama'] ?? '');
$tanggal_mulai = trim($_POST['tanggal_mulai'] ?? '');
$tanggal_selesai = trim($_POST['tanggal_selesai'] ?? '');
$status_aktif = isset($_POST['status_aktif']) ? (int)$_POST['status_aktif'] : -1;

function tanggalValid($tanggal)
{
    $date = DateTime::createFromFormat('Y-m-d', $tanggal);
    return $date && $date->format('Y-m-d') === $tanggal;
}

if (
    $nama === '' ||
    !tanggalValid($tanggal_mulai) ||
    !tanggalValid($tanggal_selesai) ||
    $tanggal_selesai < $tanggal_mulai ||
    !in_array($status_aktif, [0, 1], true)
) {
    header('Location: tambah_tahun_ajaran.php?pesan=gagal');
    exit;
}

try {
    mysqli_begin_transaction($koneksi);

    
    $lock = mysqli_query(
        $koneksi,
        "SELECT id, status_aktif FROM t_tahun_ajaran FOR UPDATE"
    );

    if (!$lock) {
        throw new Exception('Gagal mengunci data tahun ajaran.');
    }

    
    if ($status_aktif === 1) {
        $cekAktif = mysqli_query(
            $koneksi,
            "SELECT id FROM t_tahun_ajaran WHERE status_aktif = 1 LIMIT 1"
        );

        if (!$cekAktif) {
            throw new Exception('Gagal memeriksa tahun ajaran aktif.');
        }

        if (mysqli_num_rows($cekAktif) > 0) {
            mysqli_rollback($koneksi);
            header('Location: kelola_tahun_ajaran.php?pesan=tahun_aktif');
            exit;
        }
    }

    $stmt = mysqli_prepare(
        $koneksi,
        "INSERT INTO t_tahun_ajaran
        (nama, tanggal_mulai, tanggal_selesai, status_aktif)
        VALUES (?, ?, ?, ?)"
    );

    if (!$stmt) {
        throw new Exception('Gagal menyiapkan query.');
    }

    mysqli_stmt_bind_param(
        $stmt,
        "sssi",
        $nama,
        $tanggal_mulai,
        $tanggal_selesai,
        $status_aktif
    );

    if (!mysqli_stmt_execute($stmt)) {
        throw new Exception('Gagal menyimpan tahun ajaran.');
    }

    mysqli_stmt_close($stmt);

    mysqli_commit($koneksi);

    header('Location: kelola_tahun_ajaran.php?pesan=berhasil_tambah');
    exit;

} catch (Throwable $e) {
    mysqli_rollback($koneksi);
    error_log($e->getMessage());

    header('Location: kelola_tahun_ajaran.php?pesan=gagal');
    exit;
}