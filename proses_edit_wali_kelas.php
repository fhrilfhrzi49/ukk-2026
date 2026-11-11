<?php

require_once __DIR__ . '/includes/cek_session.php';
require_once __DIR__ . '/config/koneksi.php';

$id = (int) ($_POST['id'] ?? 0);
$tahun_ajaran_id = (int) ($_POST['tahun_ajaran_id'] ?? 0);
$kelas_id = (int) ($_POST['kelas_id'] ?? 0);
$guru_id = (int) ($_POST['guru_id'] ?? 0);
$user_id = (int) ($_POST['user_id'] ?? 0);
$tanggal_mulai = $_POST['tanggal_mulai'] ?? '';
$tanggal_selesai = $_POST['tanggal_selesai'] ?? '';
$status_aktif = (int) ($_POST['status_aktif'] ?? 0);

if ($id <= 0 || $tahun_ajaran_id <= 0 || $kelas_id <= 0 || $tanggal_mulai === '' || $tanggal_selesai === '') {
    exit('Data wali kelas tidak lengkap.');
}

if ($tanggal_selesai < $tanggal_mulai) {
    exit('Tanggal selesai tidak boleh lebih awal dari tanggal mulai.');
}

if ($guru_id > 0 && $user_id <= 0) {
    $stmt = mysqli_prepare($koneksi, "SELECT user_id FROM t_guru WHERE id = ? LIMIT 1");
    mysqli_stmt_bind_param($stmt, 'i', $guru_id);
    mysqli_stmt_execute($stmt);
    $dataGuru = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    mysqli_stmt_close($stmt);

    if (!$dataGuru || empty($dataGuru['user_id'])) {
        exit('Akun guru belum terhubung dengan data pengguna.');
    }

    $user_id = (int) $dataGuru['user_id'];
}

if ($user_id <= 0) exit('Akun wali kelas harus dipilih.');

if ($status_aktif === 1) {
    $stmt = mysqli_prepare(
        $koneksi,
        "SELECT id FROM t_wali_kelas
         WHERE tahun_ajaran_id = ? AND status_aktif = 1 AND id <> ?
         AND (user_id = ? OR (? > 0 AND guru_id = ?))
         LIMIT 1"
    );

    mysqli_stmt_bind_param($stmt, 'iiiii', $tahun_ajaran_id, $id, $user_id, $guru_id, $guru_id);
    mysqli_stmt_execute($stmt);
    $adaWali = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    mysqli_stmt_close($stmt);

    if ($adaWali) {
        exit('Wali kelas tersebut sudah memegang kelas lain pada tahun ajaran ini.');
    }

    $stmt = mysqli_prepare(
        $koneksi,
        "SELECT id FROM t_wali_kelas
         WHERE tahun_ajaran_id = ? AND kelas_id = ? AND status_aktif = 1 AND id <> ?
         LIMIT 1"
    );

    mysqli_stmt_bind_param($stmt, 'iii', $tahun_ajaran_id, $kelas_id, $id);
    mysqli_stmt_execute($stmt);
    $adaKelas = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    mysqli_stmt_close($stmt);

    if ($adaKelas) {
        exit('Kelas tersebut sudah memiliki wali kelas pada tahun ajaran ini.');
    }
}

$stmt = mysqli_prepare(
    $koneksi,
    "UPDATE t_wali_kelas SET
        tahun_ajaran_id = ?,
        kelas_id = ?,
        guru_id = NULLIF(?, 0),
        user_id = ?,
        tanggal_mulai = ?,
        tanggal_selesai = ?,
        status_aktif = ?,
        updated_at = CURRENT_TIMESTAMP
     WHERE id = ?"
);

mysqli_stmt_bind_param(
    $stmt,
    'iiiissii',
    $tahun_ajaran_id,
    $kelas_id,
    $guru_id,
    $user_id,
    $tanggal_mulai,
    $tanggal_selesai,
    $status_aktif,
    $id
);

if (mysqli_stmt_execute($stmt)) {
    mysqli_stmt_close($stmt);
    header('Location: kelola_wali_kelas.php?pesan=berhasil_edit');
    exit;
}

echo 'Gagal mengubah data wali kelas: ' . mysqli_stmt_error($stmt);
mysqli_stmt_close($stmt);