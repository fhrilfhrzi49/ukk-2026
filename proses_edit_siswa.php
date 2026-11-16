
<?php
require_once __DIR__ . '/config/akses.php';
wajib_role(['admin', 'administrator']);

require_once __DIR__ . '/includes/cek_session.php';
require_once __DIR__ . '/config/koneksi.php';

$id = (int) ($_POST['id'] ?? 0);
$nis = trim($_POST['nis'] ?? '');
$nisn = trim($_POST['nisn'] ?? '');
$nama = trim($_POST['nama'] ?? '');
$jenis_kelamin = $_POST['jenis_kelamin'] ?? '';
$tanggal_lahir = $_POST['tanggal_lahir'] ?? '';
$alamat = trim($_POST['alamat'] ?? '');
$status_aktif = isset($_POST['status_aktif']) ? (int) $_POST['status_aktif'] : -1;

if (
    $id <= 0 ||
    $nis === '' ||
    $nisn === '' ||
    $nama === '' ||
    !in_array($jenis_kelamin, ['L', 'P'], true) ||
    $tanggal_lahir === '' ||
    $alamat === '' ||
    !in_array($status_aktif, [0, 1], true)
) {
    echo "<script>alert('Data siswa tidak valid!'); window.location='kelola_siswa.php';</script>";
    exit;
}

mysqli_begin_transaction($koneksi);

try {
    $stmt = mysqli_prepare($koneksi, "
        UPDATE t_siswa SET
            nis = ?,
            nisn = ?,
            nama = ?,
            jenis_kelamin = ?,
            tanggal_lahir = ?,
            alamat = ?,
            status_aktif = ?,
            updated_at = CURRENT_TIMESTAMP
        WHERE id = ?
    ");

    mysqli_stmt_bind_param(
        $stmt,
        "ssssssii",
        $nis,
        $nisn,
        $nama,
        $jenis_kelamin,
        $tanggal_lahir,
        $alamat,
        $status_aktif,
        $id
    );

    if (!mysqli_stmt_execute($stmt)) {
        throw new Exception(mysqli_stmt_error($stmt));
    }

    mysqli_stmt_close($stmt);

    if ($status_aktif === 0) {
        $stmt = mysqli_prepare($koneksi, "
            UPDATE t_kelas_siswa
            SET status_aktif = 0
            WHERE siswa_id = ?
        ");

        mysqli_stmt_bind_param($stmt, "i", $id);

        if (!mysqli_stmt_execute($stmt)) {
            throw new Exception(mysqli_stmt_error($stmt));
        }

        mysqli_stmt_close($stmt);
    }

    mysqli_commit($koneksi);

    header("Location: kelola_siswa.php");
    exit;

} catch (Throwable $e) {
    mysqli_rollback($koneksi);

    echo "<script>
        alert('Gagal mengubah data siswa: " . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8') . "');
        window.location='kelola_siswa.php';
    </script>";
    exit;
}
