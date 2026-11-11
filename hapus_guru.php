<?php
require_once __DIR__ . '/includes/cek_session.php';
require_once __DIR__ . '/config/koneksi.php';

function tampilkanPesan($judul, $pesan, $url, $jenis = 'danger')
{
    $e = fn($value) => htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
    ?>
    <!DOCTYPE html>
    <html lang="id">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title><?= $e($judul) ?> | SI-TIB</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    </head>
    <body class="bg-light">
        <div class="container min-vh-100 d-flex align-items-center justify-content-center py-4">
            <div class="card border-0 shadow rounded-4 w-100" style="max-width: 540px;">
                <div class="card-body p-4 p-md-5 text-center">
                    <div class="rounded-circle bg-<?= $e($jenis) ?>-subtle text-<?= $e($jenis) ?> d-inline-flex align-items-center justify-content-center mb-4" style="width: 85px; height: 85px;">
                        <i class="bi bi-exclamation-circle-fill fs-1"></i>
                    </div>
                    <h4 class="fw-bold mb-2"><?= $e($judul) ?></h4>
                    <p class="text-secondary mb-4"><?= $e($pesan) ?></p>
                    <div class="d-grid gap-2">
                        <a href="<?= $e($url) ?>" class="btn btn-primary btn-lg rounded-3">
                            <i class="bi bi-arrow-left-circle me-2"></i>Kembali
                        </a>
                        <a href="kelola_guru.php" class="btn btn-outline-secondary rounded-3">
                            <i class="bi bi-people me-2"></i>Kelola Data Guru
                        </a>
                    </div>
                    <hr class="my-4">
                    <div class="small text-secondary">
                        <strong>SI-TIB</strong> — Sistem Informasi Tata Tertib
                    </div>
                </div>
            </div>
        </div>
    </body>
    </html>
    <?php
    exit;
}

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$id || $id < 1) {
    tampilkanPesan('ID Tidak Valid', 'ID guru yang akan dihapus tidak valid.', 'kelola_guru.php', 'warning');
}

$stmt = mysqli_prepare($koneksi, "SELECT id, nama, user_id FROM t_guru WHERE id = ? LIMIT 1");

if (!$stmt) {
    tampilkanPesan('Kesalahan Database', 'Pemeriksaan data guru gagal dilakukan.', 'kelola_guru.php', 'danger');
}

mysqli_stmt_bind_param($stmt, 'i', $id);
mysqli_stmt_execute($stmt);
$data = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
mysqli_stmt_close($stmt);

if (!$data) {
    tampilkanPesan('Guru Tidak Ditemukan', 'Data guru yang akan dihapus tidak ditemukan.', 'kelola_guru.php', 'warning');
}

$userId = (int) $data['user_id'];
$namaGuru = $data['nama'];

mysqli_begin_transaction($koneksi);

try {
    $stmt = mysqli_prepare($koneksi, "DELETE FROM t_guru WHERE id = ?");

    if (!$stmt) {
        throw new Exception('Gagal menyiapkan penghapusan data guru.');
    }

    mysqli_stmt_bind_param($stmt, 'i', $id);

    if (!mysqli_stmt_execute($stmt)) {
        throw new Exception('Data guru gagal dihapus.');
    }

    mysqli_stmt_close($stmt);

    $stmt = mysqli_prepare($koneksi, "SELECT id FROM t_guru WHERE user_id = ? LIMIT 1");
    mysqli_stmt_bind_param($stmt, 'i', $userId);
    mysqli_stmt_execute($stmt);
    $akunMasihDigunakan = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    mysqli_stmt_close($stmt);

    if (!$akunMasihDigunakan && $userId > 0) {
        $stmt = mysqli_prepare($koneksi, "DELETE FROM t_users WHERE id = ?");

        if (!$stmt) {
            throw new Exception('Gagal menyiapkan penghapusan akun pengguna.');
        }

        mysqli_stmt_bind_param($stmt, 'i', $userId);

        if (!mysqli_stmt_execute($stmt)) {
            throw new Exception('Akun pengguna gagal dihapus.');
        }

        mysqli_stmt_close($stmt);
    }

    $idUserAktif = (int) ($_SESSION['id_user'] ?? $_SESSION['user_id'] ?? 0);
    $aktivitas = 'Hapus guru: ' . $namaGuru;
    $cekTabelLog = mysqli_query($koneksi, "SHOW TABLES LIKE 'tbl_log'");

    if ($cekTabelLog && mysqli_num_rows($cekTabelLog) > 0 && $idUserAktif > 0) {
        $stmt = mysqli_prepare(
            $koneksi,
            "INSERT INTO tbl_log (id_user, aktivitas, waktu) VALUES (?, ?, NOW())"
        );

        if ($stmt) {
            mysqli_stmt_bind_param($stmt, 'is', $idUserAktif, $aktivitas);

            if (!mysqli_stmt_execute($stmt)) {
                throw new Exception('Pencatatan aktivitas gagal.');
            }

            mysqli_stmt_close($stmt);
        }
    }

    mysqli_commit($koneksi);
    header('Location: kelola_guru.php?pesan=berhasil_hapus');
    exit;
} catch (Throwable $e) {
    mysqli_rollback($koneksi);
    error_log('Hapus Guru Error: ' . $e->getMessage());

    tampilkanPesan('Gagal Menghapus Guru', $e->getMessage(), 'kelola_guru.php', 'danger');
}
?>