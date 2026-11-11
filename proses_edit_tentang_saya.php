<?php

if (session_status() === PHP_SESSION_NONE) session_start();

require_once __DIR__ . '/config/koneksi.php';
require_once __DIR__ . '/config/akses.php';

wajib_role(['admin']);

function gagal(string $pesan): void
{
    $_SESSION['pesan_error'] = $pesan;
    header('Location: edit_tentang_saya.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: edit_tentang_saya.php');
    exit;
}

$csrf = $_POST['csrf_token'] ?? '';

if (empty($_SESSION['csrf_tentang_saya']) || !hash_equals($_SESSION['csrf_tentang_saya'], $csrf)) {
    gagal('Permintaan tidak valid. Silakan coba kembali.');
}

$nama = trim($_POST['nama'] ?? '');
$kelas = trim($_POST['kelas'] ?? '');
$sekolah = trim($_POST['sekolah'] ?? '');
$jurusan = trim($_POST['jurusan'] ?? '');
$hobi = trim($_POST['hobi'] ?? '');
$citaCita = trim($_POST['cita_cita'] ?? '');
$deskripsi = trim($_POST['deskripsi'] ?? '');

if (in_array('', [$nama, $kelas, $sekolah, $jurusan, $hobi, $citaCita, $deskripsi], true)) {
    gagal('Semua informasi wajib diisi.');
}

$stmt = mysqli_prepare($koneksi, "SELECT foto FROM t_tentang_saya WHERE id = 1 LIMIT 1");

if (!$stmt) gagal('Gagal memeriksa data profil.');

mysqli_stmt_execute($stmt);
$profil = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
mysqli_stmt_close($stmt);

if (!$profil) gagal('Data profil tidak ditemukan.');

$foto = $profil['foto'];

if (isset($_FILES['foto']) && $_FILES['foto']['error'] !== UPLOAD_ERR_NO_FILE) {
    if ($_FILES['foto']['error'] !== UPLOAD_ERR_OK) gagal('Upload foto gagal. Silakan coba kembali.');
    if ($_FILES['foto']['size'] > 2 * 1024 * 1024) gagal('Ukuran foto maksimal 2 MB.');

    $infoFoto = getimagesize($_FILES['foto']['tmp_name']);

    if ($infoFoto === false) gagal('File yang diunggah bukan gambar yang valid.');

    $ekstensiDiizinkan = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp'
    ];

    $mime = $infoFoto['mime'];

    if (!isset($ekstensiDiizinkan[$mime])) {
        gagal('Format foto harus JPG, PNG, atau WEBP.');
    }

    $folderFoto = __DIR__ . '/assets/img';

    if (!is_dir($folderFoto) && !mkdir($folderFoto, 0755, true)) {
        gagal('Folder penyimpanan foto tidak dapat dibuat.');
    }

    if (!is_writable($folderFoto)) {
        gagal('Folder foto tidak memiliki izin untuk menyimpan file.');
    }

    $namaFile = 'profil_' . bin2hex(random_bytes(12)) . '.' . $ekstensiDiizinkan[$mime];
    $lokasiFoto = $folderFoto . '/' . $namaFile;

    if (!move_uploaded_file($_FILES['foto']['tmp_name'], $lokasiFoto)) {
        gagal('Foto gagal disimpan.');
    }

    $foto = 'assets/img/' . $namaFile;
}

$stmt = mysqli_prepare(
    $koneksi,
    "UPDATE t_tentang_saya SET nama = ?, kelas = ?, sekolah = ?, jurusan = ?, hobi = ?, cita_cita = ?, deskripsi = ?, foto = ? WHERE id = 1"
);

if (!$stmt) gagal('Gagal menyiapkan penyimpanan data.');

mysqli_stmt_bind_param($stmt, 'ssssssss', $nama, $kelas, $sekolah, $jurusan, $hobi, $citaCita, $deskripsi, $foto);

if (!mysqli_stmt_execute($stmt)) {
    mysqli_stmt_close($stmt);
    gagal('Data Tentang Saya gagal diperbarui.');
}

mysqli_stmt_close($stmt);

unset($_SESSION['csrf_tentang_saya']);

$_SESSION['pesan_sukses'] = 'Data Tentang Saya berhasil diperbarui.';

header('Location: tentang_saya.php');
exit;