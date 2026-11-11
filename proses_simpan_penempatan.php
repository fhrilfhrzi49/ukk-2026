<?php
include 'includes/cek_session.php';
include 'config/koneksi.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: penempatan_siswa.php');
    exit;
}


$siswa_id = filter_input(INPUT_POST, 'siswa_id', FILTER_VALIDATE_INT);
$kelas_id = filter_input(INPUT_POST, 'kelas_id', FILTER_VALIDATE_INT);
$tahun_ajaran_id = filter_input(INPUT_POST, 'tahun_ajaran_id', FILTER_VALIDATE_INT);

$tanggal_mulai = trim($_POST['tanggal_mulai'] ?? '');
$tanggal_selesai = trim($_POST['tanggal_selesai'] ?? '');
$status_aktif = filter_input(INPUT_POST, 'status_aktif', FILTER_VALIDATE_INT);


if (!$siswa_id || !$kelas_id || !$tahun_ajaran_id) {
    echo "<script>alert('Siswa, kelas, dan tahun ajaran wajib dipilih!'); window.location='tambah_penempatan.php';</script>";
    exit;
}


function tanggalValid(string $tanggal): bool
{
    $date = DateTime::createFromFormat('!Y-m-d', $tanggal);

    return $date && $date->format('Y-m-d') === $tanggal;
}

if (
    $tanggal_mulai === '' ||
    $tanggal_selesai === '' ||
    !tanggalValid($tanggal_mulai) ||
    !tanggalValid($tanggal_selesai)
) {
    echo "<script>alert('Tanggal mulai dan tanggal selesai wajib diisi dengan benar!'); window.location='tambah_penempatan.php';</script>";
    exit;
}

if ($tanggal_selesai < $tanggal_mulai) {
    echo "<script>alert('Tanggal selesai tidak boleh lebih awal dari tanggal mulai!'); window.location='tambah_penempatan.php';</script>";
    exit;
}


if ($status_aktif === false || !in_array($status_aktif, [0, 1], true)) {
    echo "<script>alert('Status penempatan tidak valid!'); window.location='tambah_penempatan.php';</script>";
    exit;
}


$stmt = mysqli_prepare($koneksi, "
    SELECT id FROM t_siswa WHERE id = ?
");

mysqli_stmt_bind_param($stmt, "i", $siswa_id);
mysqli_stmt_execute($stmt);

$siswa = mysqli_stmt_get_result($stmt);
$siswaAda = mysqli_num_rows($siswa) > 0;

mysqli_stmt_close($stmt);

if (!$siswaAda) {
    echo "<script>alert('Data siswa tidak ditemukan!'); window.location='tambah_penempatan.php';</script>";
    exit;
}


$stmt = mysqli_prepare($koneksi, "
    SELECT id FROM t_kelas WHERE id = ?
");

mysqli_stmt_bind_param($stmt, "i", $kelas_id);
mysqli_stmt_execute($stmt);

$kelas = mysqli_stmt_get_result($stmt);
$kelasAda = mysqli_num_rows($kelas) > 0;

mysqli_stmt_close($stmt);

if (!$kelasAda) {
    echo "<script>alert('Data kelas tidak ditemukan!'); window.location='tambah_penempatan.php';</script>";
    exit;
}


$stmt = mysqli_prepare($koneksi, "
    SELECT id FROM t_tahun_ajaran WHERE id = ?
");

mysqli_stmt_bind_param($stmt, "i", $tahun_ajaran_id);
mysqli_stmt_execute($stmt);

$tahun = mysqli_stmt_get_result($stmt);
$tahunAda = mysqli_num_rows($tahun) > 0;

mysqli_stmt_close($stmt);

if (!$tahunAda) {
    echo "<script>alert('Data tahun ajaran tidak ditemukan!'); window.location='tambah_penempatan.php';</script>";
    exit;
}


$stmt = mysqli_prepare($koneksi, "
    SELECT id
    FROM t_kelas_siswa
    WHERE siswa_id = ?
      AND tahun_ajaran_id = ?
");

mysqli_stmt_bind_param($stmt, "ii", $siswa_id, $tahun_ajaran_id);
mysqli_stmt_execute($stmt);

$penempatan = mysqli_stmt_get_result($stmt);
$sudahDitempatkan = mysqli_num_rows($penempatan) > 0;

mysqli_stmt_close($stmt);

if ($sudahDitempatkan) {
    echo "<script>alert('Siswa sudah memiliki penempatan pada tahun ajaran tersebut!'); window.location='penempatan_siswa.php';</script>";
    exit;
}


$stmt = mysqli_prepare($koneksi, "
    INSERT INTO t_kelas_siswa (
        siswa_id,
        kelas_id,
        tahun_ajaran_id,
        tanggal_mulai,
        tanggal_selesai,
        status_aktif
    ) VALUES (?, ?, ?, ?, ?, ?)
");

if (!$stmt) {
    die('Gagal menyiapkan query: ' . htmlspecialchars(mysqli_error($koneksi)));
}

mysqli_stmt_bind_param(
    $stmt,
    "iiissi",
    $siswa_id,
    $kelas_id,
    $tahun_ajaran_id,
    $tanggal_mulai,
    $tanggal_selesai,
    $status_aktif
);

if (mysqli_stmt_execute($stmt)) {
    mysqli_stmt_close($stmt);

    echo "<script>
        alert('Penempatan siswa berhasil disimpan beserta tanggal mulai dan tanggal selesai!');
        window.location='penempatan_siswa.php';
    </script>";
    exit;
}

$error = mysqli_stmt_error($stmt);
mysqli_stmt_close($stmt);

echo "<script>
    alert(" . json_encode('Gagal menyimpan penempatan siswa: ' . $error) . ");
    window.location='tambah_penempatan.php';
</script>";
exit;
?>