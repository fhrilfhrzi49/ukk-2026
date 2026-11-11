<?php

require_once __DIR__ . '/includes/cek_session.php';
require_once __DIR__ . '/config/koneksi.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: penempatan_siswa.php');
    exit;
}

function kembali($pesan)
{
    echo "<script>alert(" . json_encode($pesan) . ");window.history.back();</script>";
    exit;
}

$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
$siswa_id = filter_input(INPUT_POST, 'siswa_id', FILTER_VALIDATE_INT);
$kelas_id = filter_input(INPUT_POST, 'kelas_id', FILTER_VALIDATE_INT);
$tahun_ajaran_id = filter_input(INPUT_POST, 'tahun_ajaran_id', FILTER_VALIDATE_INT);
$tanggal_mulai = $_POST['tanggal_mulai'] ?? '';
$tanggal_selesai = $_POST['tanggal_selesai'] ?? '';
$status_aktif = isset($_POST['status_aktif']) ? (int) $_POST['status_aktif'] : -1;

if (!$id || !$siswa_id || !$kelas_id || !$tahun_ajaran_id) kembali('Data penempatan belum lengkap!');
if (!in_array($status_aktif, [0, 1], true)) kembali('Status penempatan tidak valid!');

if ($tanggal_mulai !== '' && !DateTime::createFromFormat('Y-m-d', $tanggal_mulai)) {
    kembali('Format tanggal mulai tidak valid!');
}

if ($tanggal_selesai !== '' && !DateTime::createFromFormat('Y-m-d', $tanggal_selesai)) {
    kembali('Format tanggal selesai tidak valid!');
}

if ($tanggal_mulai !== '' && $tanggal_selesai !== '' && $tanggal_selesai < $tanggal_mulai) {
    kembali('Tanggal selesai tidak boleh lebih awal dari tanggal mulai!');
}

$stmt = mysqli_prepare($koneksi, "SELECT id FROM t_kelas_siswa WHERE id = ?");
mysqli_stmt_bind_param($stmt, 'i', $id);
mysqli_stmt_execute($stmt);
$penempatanAda = mysqli_num_rows(mysqli_stmt_get_result($stmt)) > 0;
mysqli_stmt_close($stmt);

if (!$penempatanAda) kembali('Data penempatan tidak ditemukan!');

foreach ([['t_siswa', $siswa_id], ['t_kelas', $kelas_id], ['t_tahun_ajaran', $tahun_ajaran_id]] as [$tabel, $nilai]) {
    $stmt = mysqli_prepare($koneksi, "SELECT id FROM $tabel WHERE id = ?");
    mysqli_stmt_bind_param($stmt, 'i', $nilai);
    mysqli_stmt_execute($stmt);
    $ada = mysqli_num_rows(mysqli_stmt_get_result($stmt)) > 0;
    mysqli_stmt_close($stmt);

    if (!$ada) kembali('Data referensi penempatan tidak ditemukan!');
}

$stmt = mysqli_prepare($koneksi, "SELECT id FROM t_kelas_siswa WHERE siswa_id = ? AND tahun_ajaran_id = ? AND id != ?");
mysqli_stmt_bind_param($stmt, 'iii', $siswa_id, $tahun_ajaran_id, $id);
mysqli_stmt_execute($stmt);
$sudahAda = mysqli_num_rows(mysqli_stmt_get_result($stmt)) > 0;
mysqli_stmt_close($stmt);

if ($sudahAda) kembali('Siswa sudah memiliki penempatan pada tahun ajaran tersebut!');

$tanggal_mulai = $tanggal_mulai === '' ? null : $tanggal_mulai;
$tanggal_selesai = $tanggal_selesai === '' ? null : $tanggal_selesai;

$stmt = mysqli_prepare(
    $koneksi,
    "UPDATE t_kelas_siswa SET siswa_id = ?, kelas_id = ?, tahun_ajaran_id = ?, tanggal_mulai = ?, tanggal_selesai = ?, status_aktif = ? WHERE id = ?"
);

if (!$stmt) kembali('Gagal menyiapkan query: ' . mysqli_error($koneksi));

mysqli_stmt_bind_param($stmt, 'iiissii', $siswa_id, $kelas_id, $tahun_ajaran_id, $tanggal_mulai, $tanggal_selesai, $status_aktif, $id);

if (mysqli_stmt_execute($stmt)) {
    mysqli_stmt_close($stmt);
    echo "<script>alert('Data penempatan siswa berhasil diperbarui!');window.location='penempatan_siswa.php';</script>";
    exit;
}

$error = mysqli_stmt_error($stmt);
mysqli_stmt_close($stmt);

kembali('Gagal memperbarui data penempatan: ' . $error);
?>