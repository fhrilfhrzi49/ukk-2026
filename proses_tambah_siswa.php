
<?php

require_once __DIR__ . '/includes/cek_session.php';
require_once __DIR__ . '/config/koneksi.php';


if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: tambah_siswa.php");
    exit;
}


$nis = trim($_POST['nis'] ?? '');
$nisn = trim($_POST['nisn'] ?? '');
$nama = trim($_POST['nama'] ?? '');
$jenis_kelamin = $_POST['jenis_kelamin'] ?? '';
$tanggal_lahir = $_POST['tanggal_lahir'] ?? '';
$alamat = trim($_POST['alamat'] ?? '');
$status_aktif = $_POST['status_aktif'] ?? '';


if (!preg_match('/^[0-9]{1,10}$/', $nis)) {
    exit('Gagal: NIS harus berupa angka dengan panjang 1 sampai 10 digit. <a href="tambah_siswa.php">Kembali</a>');
}


if (!preg_match('/^[0-9]{10}$/', $nisn)) {
    exit('Gagal: NISN wajib terdiri dari tepat 10 digit angka. <a href="tambah_siswa.php">Kembali</a>');
}


if ($nama === '' || mb_strlen($nama) > 150) {
    exit('Gagal: Nama siswa wajib diisi dan maksimal 150 karakter. <a href="tambah_siswa.php">Kembali</a>');
}


if (!in_array($jenis_kelamin, ['L', 'P'], true)) {
    exit('Gagal: Jenis kelamin tidak valid. <a href="tambah_siswa.php">Kembali</a>');
}


$tanggal = DateTime::createFromFormat('!Y-m-d', $tanggal_lahir);
if (!$tanggal || $tanggal->format('Y-m-d') !== $tanggal_lahir) {
    exit('Gagal: Tanggal lahir tidak valid. <a href="tambah_siswa.php">Kembali</a>');
}


if ($alamat === '') {
    exit('Gagal: Alamat wajib diisi. <a href="tambah_siswa.php">Kembali</a>');
}


if (!in_array((string) $status_aktif, ['0', '1'], true)) {
    exit('Gagal: Status aktif tidak valid. <a href="tambah_siswa.php">Kembali</a>');
}


$stmtCek = mysqli_prepare(
    $koneksi,
    "SELECT id FROM t_siswa WHERE nis = ? OR nisn = ? LIMIT 1"
);

if (!$stmtCek) {
    exit('Terjadi kesalahan saat memeriksa data siswa.');
}

mysqli_stmt_bind_param($stmtCek, "ss", $nis, $nisn);
mysqli_stmt_execute($stmtCek);
$hasilCek = mysqli_stmt_get_result($stmtCek);

if (mysqli_num_rows($hasilCek) > 0) {
    mysqli_stmt_close($stmtCek);
    exit('Gagal: NIS atau NISN sudah terdaftar. <a href="tambah_siswa.php">Kembali</a>');
}

mysqli_stmt_close($stmtCek);


$sql = "INSERT INTO t_siswa
        (nis, nisn, nama, jenis_kelamin, tanggal_lahir, alamat, status_aktif)
        VALUES (?, ?, ?, ?, ?, ?, ?)";

$stmt = mysqli_prepare($koneksi, $sql);

if (!$stmt) {
    exit('Terjadi kesalahan saat menyiapkan penyimpanan data siswa.');
}

mysqli_stmt_bind_param(
    $stmt,
    "ssssssi",
    $nis,
    $nisn,
    $nama,
    $jenis_kelamin,
    $tanggal_lahir,
    $alamat,
    $status_aktif
);

if (mysqli_stmt_execute($stmt)) {
    mysqli_stmt_close($stmt);
    header("Location: kelola_siswa.php");
    exit;
}

$kodeError = mysqli_stmt_errno($stmt);
mysqli_stmt_close($stmt);


if ($kodeError === 1062) {
    exit('Gagal: NIS atau NISN sudah terdaftar. <a href="tambah_siswa.php">Kembali</a>');
}

exit('Gagal menambahkan data siswa. Silakan periksa data dan struktur database.');
?>
