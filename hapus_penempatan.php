<?php
require_once __DIR__ . '/config/akses.php';
wajib_role(['admin']);

require_once __DIR__ . '/includes/cek_session.php';
require_once __DIR__ . '/config/koneksi.php';

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$id || $id <= 0) {
    echo "<script>
        alert('ID penempatan tidak valid!');
        window.location.href = 'penempatan_siswa.php';
    </script>";
    exit;
}

$stmt = mysqli_prepare($koneksi, "SELECT id FROM t_kelas_siswa WHERE id = ?");
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result) === 0) {
    mysqli_stmt_close($stmt);

    echo "<script>
        alert('Data penempatan tidak ditemukan!');
        window.location.href = 'penempatan_siswa.php';
    </script>";
    exit;
}

mysqli_stmt_close($stmt);

$stmt = mysqli_prepare($koneksi, "DELETE FROM t_kelas_siswa WHERE id = ?");
mysqli_stmt_bind_param($stmt, "i", $id);

if (mysqli_stmt_execute($stmt)) {
    mysqli_stmt_close($stmt);

    echo "<script>
        alert('Data penempatan siswa berhasil dihapus!');
        window.location.href = 'penempatan_siswa.php';
    </script>";
    exit;
}

mysqli_stmt_close($stmt);

echo "<script>
    alert('Gagal menghapus data penempatan siswa!');
    window.location.href = 'penempatan_siswa.php';
</script>";
exit;
?>