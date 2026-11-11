<?php

require_once __DIR__ . '/config/akses.php';
wajib_role(['admin', 'administrator']);

require_once __DIR__ . '/includes/cek_session.php';
require_once __DIR__ . '/config/koneksi.php';

if (session_status() === PHP_SESSION_NONE) session_start();

function kembali(string $pesan, string $jenis = 'error'): void
{
    $_SESSION[$jenis === 'success' ? 'pesan_sukses' : 'pesan_error'] = $pesan;
    header('Location: kelola_pelanggaran_kategori.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') kembali('Permintaan tidak valid.');

$token = $_POST['csrf_token'] ?? '';

if (empty($_SESSION['csrf_token']) || !is_string($token) || !hash_equals($_SESSION['csrf_token'], $token)) {
    kembali('Token keamanan tidak valid. Silakan coba kembali.');
}

$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);

if (!$id || $id <= 0) kembali('ID jenis pelanggaran tidak valid.');

try {
    mysqli_begin_transaction($koneksi);

    $stmt = mysqli_prepare($koneksi, "SELECT id, nama_pelanggaran FROM t_jenis_pelanggaran WHERE id = ? LIMIT 1 FOR UPDATE");
    if (!$stmt) throw new Exception('Gagal memeriksa data jenis pelanggaran.');

    mysqli_stmt_bind_param($stmt, 'i', $id);
    mysqli_stmt_execute($stmt);
    $data = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    mysqli_stmt_close($stmt);

    if (!$data) throw new Exception('Jenis pelanggaran tidak ditemukan.');

    $cekKolom = mysqli_query($koneksi, "SHOW COLUMNS FROM t_catatan_pelanggaran LIKE 'jenis_pelanggaran_id'");
    if (!$cekKolom) throw new Exception('Gagal memeriksa struktur catatan pelanggaran.');

    $kolomTersedia = mysqli_num_rows($cekKolom) > 0;
    mysqli_free_result($cekKolom);

    $jumlahPenggunaan = 0;

    if ($kolomTersedia) {
        $stmt = mysqli_prepare($koneksi, "SELECT COUNT(*) AS total FROM t_catatan_pelanggaran WHERE jenis_pelanggaran_id = ?");
        if (!$stmt) throw new Exception('Gagal memeriksa riwayat pelanggaran.');

        mysqli_stmt_bind_param($stmt, 'i', $id);
        mysqli_stmt_execute($stmt);
        $hasil = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
        $jumlahPenggunaan = (int) ($hasil['total'] ?? 0);
        mysqli_stmt_close($stmt);
    }

    if ($jumlahPenggunaan > 0) {
        $stmt = mysqli_prepare($koneksi, "UPDATE t_jenis_pelanggaran SET status_aktif = 0 WHERE id = ?");
        if (!$stmt) throw new Exception('Gagal menonaktifkan jenis pelanggaran.');

        mysqli_stmt_bind_param($stmt, 'i', $id);
        if (!mysqli_stmt_execute($stmt)) throw new Exception('Jenis pelanggaran gagal dinonaktifkan.');
        mysqli_stmt_close($stmt);

        mysqli_commit($koneksi);
        kembali('Jenis pelanggaran "' . $data['nama_pelanggaran'] . '" sudah digunakan dalam ' . $jumlahPenggunaan . ' catatan. Data tidak dihapus, tetapi berhasil dinonaktifkan.', 'success');
    }

    $stmt = mysqli_prepare($koneksi, "DELETE FROM t_jenis_pelanggaran WHERE id = ?");
    if (!$stmt) throw new Exception('Gagal menyiapkan penghapusan data.');

    mysqli_stmt_bind_param($stmt, 'i', $id);
    if (!mysqli_stmt_execute($stmt)) {
        throw new Exception('Data tidak dapat dihapus. Kemungkinan masih digunakan oleh tabel lain.');
    }

    $terhapus = mysqli_stmt_affected_rows($stmt);
    mysqli_stmt_close($stmt);

    if ($terhapus < 1) throw new Exception('Jenis pelanggaran gagal dihapus.');

    mysqli_commit($koneksi);
    kembali('Jenis pelanggaran "' . $data['nama_pelanggaran'] . '" berhasil dihapus.', 'success');

} catch (Throwable $e) {
    mysqli_rollback($koneksi);
    kembali('Proses gagal: ' . $e->getMessage());
}