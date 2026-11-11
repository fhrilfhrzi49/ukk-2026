<?php

require_once __DIR__ . '/config/akses.php';
wajib_role(['admin', 'administrator']);

require_once __DIR__ . '/includes/cek_session.php';
require_once __DIR__ . '/config/koneksi.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: kelola_tahun_ajaran.php');
    exit;
}

function tanggalValidEdit($tanggal)
{
    $date = DateTime::createFromFormat('Y-m-d', $tanggal);
    return $date && $date->format('Y-m-d') === $tanggal;
}

$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
$nama = trim($_POST['nama'] ?? '');
$tanggal_mulai = trim($_POST['tanggal_mulai'] ?? '');
$tanggal_selesai = trim($_POST['tanggal_selesai'] ?? '');
$status_aktif = isset($_POST['status_aktif']) ? (int) $_POST['status_aktif'] : -1;

if (!$id || $nama === '' || !tanggalValidEdit($tanggal_mulai) || !tanggalValidEdit($tanggal_selesai) || $tanggal_selesai < $tanggal_mulai || !in_array($status_aktif, [0, 1], true)) {
    header('Location: kelola_tahun_ajaran.php?pesan=gagal');
    exit;
}

try {
    mysqli_begin_transaction($koneksi);

    $lock = mysqli_query($koneksi, "SELECT id, status_aktif FROM t_tahun_ajaran FOR UPDATE");
    if (!$lock) throw new Exception('Gagal mengunci data tahun ajaran.');

    $stmt = mysqli_prepare($koneksi, "SELECT id FROM t_tahun_ajaran WHERE id = ? LIMIT 1");
    mysqli_stmt_bind_param($stmt, 'i', $id);
    mysqli_stmt_execute($stmt);
    $dataAda = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    mysqli_stmt_close($stmt);

    if (!$dataAda) throw new Exception('Data tahun ajaran tidak ditemukan.');

    if ($status_aktif === 1) {
        $stmt = mysqli_prepare($koneksi, "SELECT id FROM t_tahun_ajaran WHERE status_aktif = 1 AND id != ? LIMIT 1");
        mysqli_stmt_bind_param($stmt, 'i', $id);
        mysqli_stmt_execute($stmt);
        $adaAktifLain = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
        mysqli_stmt_close($stmt);

        if ($adaAktifLain) {
            mysqli_rollback($koneksi);
            header('Location: kelola_tahun_ajaran.php?pesan=tahun_aktif');
            exit;
        }
    }

    $stmt = mysqli_prepare(
        $koneksi,
        "UPDATE t_tahun_ajaran SET nama = ?, tanggal_mulai = ?, tanggal_selesai = ?, status_aktif = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?"
    );

    if (!$stmt) throw new Exception('Gagal menyiapkan query.');

    mysqli_stmt_bind_param($stmt, 'sssii', $nama, $tanggal_mulai, $tanggal_selesai, $status_aktif, $id);

    if (!mysqli_stmt_execute($stmt)) throw new Exception('Gagal memperbarui tahun ajaran.');

    mysqli_stmt_close($stmt);
    mysqli_commit($koneksi);

    header('Location: kelola_tahun_ajaran.php?pesan=berhasil_edit');
    exit;

} catch (Throwable $e) {
    mysqli_rollback($koneksi);
    error_log($e->getMessage());
    header('Location: kelola_tahun_ajaran.php?pesan=gagal');
    exit;
}