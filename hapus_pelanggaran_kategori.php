<?php
require_once __DIR__ . '/config/akses.php';
wajib_role(['admin']);

require_once __DIR__ . '/includes/cek_session.php';
require_once __DIR__ . '/config/koneksi.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function kembali(string $pesan): void
{
    $_SESSION['pesan_error'] = $pesan;
    header('Location: kelola_pelanggaran_kategori.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    kembali('Permintaan tidak valid!');
}

$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
$csrf_token = $_POST['csrf_token'] ?? '';

if (!$id || $id <= 0) {
    kembali('ID kategori tidak valid!');
}

if (
    empty($_SESSION['csrf_token']) ||
    !is_string($csrf_token) ||
    !hash_equals($_SESSION['csrf_token'], $csrf_token)
) {
    kembali('Token keamanan tidak valid. Silakan muat ulang halaman!');
}

try {
    mysqli_begin_transaction($koneksi);

    $stmt = mysqli_prepare($koneksi, "
        SELECT id, nama
        FROM t_pelanggaran_kategori
        WHERE id = ?
        FOR UPDATE
    ");

    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);

    $result = mysqli_stmt_get_result($stmt);
    $kategori = mysqli_fetch_assoc($result);

    mysqli_stmt_close($stmt);

    if (!$kategori) {
        mysqli_rollback($koneksi);
        kembali('Kategori pelanggaran tidak ditemukan!');
    }

    $stmt = mysqli_prepare($koneksi, "
        SELECT COUNT(*) AS total
        FROM t_jenis_pelanggaran
        WHERE kategori_id = ?
    ");

    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);

    $result = mysqli_stmt_get_result($stmt);
    $data = mysqli_fetch_assoc($result);

    mysqli_stmt_close($stmt);

    $total = (int) $data['total'];

    if ($total > 0) {
        $stmt = mysqli_prepare($koneksi, "
            UPDATE t_pelanggaran_kategori
            SET status_aktif = 0
            WHERE id = ?
        ");

        mysqli_stmt_bind_param($stmt, "i", $id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);

        mysqli_commit($koneksi);

        $_SESSION['pesan_error'] =
            "Kategori '{$kategori['nama']}' masih digunakan oleh $total jenis pelanggaran. Kategori berhasil dinonaktifkan.";

        header('Location: kelola_pelanggaran_kategori.php');
        exit;
    }

    $stmt = mysqli_prepare($koneksi, "
        DELETE FROM t_pelanggaran_kategori
        WHERE id = ?
    ");

    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    mysqli_commit($koneksi);

    $_SESSION['pesan_sukses'] = 'Kategori pelanggaran berhasil dihapus!';

    header('Location: kelola_pelanggaran_kategori.php');
    exit;

} catch (Throwable $e) {
    if (mysqli_errno($koneksi) !== 0) {
        mysqli_rollback($koneksi);
    }

    kembali('Gagal memproses kategori pelanggaran. Periksa relasi database!');
}
?>