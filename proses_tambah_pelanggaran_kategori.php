
<?php


if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/includes/cek_session.php';
require_once __DIR__ . '/config/koneksi.php';


if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: tambah_pelanggaran_kategori.php');
    exit;
}


if (!$koneksi) {
    die('Koneksi database gagal. Periksa config/koneksi.php.');
}


$nama = trim($_POST['nama'] ?? '');
$deskripsi = trim($_POST['deskripsi'] ?? '');
$status_aktif = $_POST['status_aktif'] ?? '';


if ($nama === '' || mb_strlen($nama) > 100) {
    echo "<script>
        alert('Nama kategori wajib diisi dan maksimal 100 karakter.');
        window.location.href = 'tambah_pelanggaran_kategori.php';
    </script>";
    exit;
}

if (!in_array($status_aktif, ['0', '1'], true)) {
    echo "<script>
        alert('Status kategori tidak valid.');
        window.location.href = 'tambah_pelanggaran_kategori.php';
    </script>";
    exit;
}


$sql = "INSERT INTO t_pelanggaran_kategori
        (nama, deskripsi, status_aktif)
        VALUES (?, ?, ?)";

$stmt = mysqli_prepare($koneksi, $sql);

if (!$stmt) {
    die('Gagal menyiapkan penyimpanan: ' .
        htmlspecialchars(mysqli_error($koneksi), ENT_QUOTES, 'UTF-8'));
}

$status = (int) $status_aktif;

mysqli_stmt_bind_param(
    $stmt,
    'ssi',
    $nama,
    $deskripsi,
    $status
);

if (mysqli_stmt_execute($stmt)) {
    mysqli_stmt_close($stmt);

    
    echo "<script>
        alert('Kategori pelanggaran berhasil disimpan.');
        window.location.href = 'kelola_pelanggaran_kategori.php';
    </script>";
    exit;
}

$error = mysqli_stmt_error($stmt);
mysqli_stmt_close($stmt);

echo 'Kategori gagal disimpan: ' .
    htmlspecialchars($error, ENT_QUOTES, 'UTF-8');
