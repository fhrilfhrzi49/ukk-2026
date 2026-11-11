
<?php


require_once __DIR__ . '/includes/cek_session.php';
require_once __DIR__ . '/config/koneksi.php';

function tampilkanPesan($judul, $pesan, $tipe = 'danger')
{
    ?>
    <!DOCTYPE html>
    <html lang="id">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title><?= htmlspecialchars($judul, ENT_QUOTES, 'UTF-8') ?> | SI-TIB</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    </head>
    <body class="bg-light">
        <div class="container min-vh-100 d-flex align-items-center justify-content-center">
            <div class="card border-0 shadow-sm rounded-4 w-100" style="max-width: 500px;">
                <div class="card-body p-4 text-center">
                    <i class="bi bi-exclamation-circle-fill text-<?= $tipe ?> display-4"></i>
                    <h4 class="fw-bold mt-3"><?= htmlspecialchars($judul) ?></h4>
                    <p class="text-secondary"><?= htmlspecialchars($pesan) ?></p>
                    <a href="kelola_wali_kelas.php" class="btn btn-primary">
                        <i class="bi bi-arrow-left me-1"></i> Kembali
                    </a>
                </div>
            </div>
        </div>
    </body>
    </html>
    <?php
    exit;
}

$id = (int) ($_GET['id'] ?? 0);

if ($id <= 0) {
    tampilkanPesan('ID Tidak Valid', 'ID wali kelas tidak ditemukan.');
}

try {
    mysqli_begin_transaction($koneksi);

    
    $sql = "SELECT
                w.id,
                w.kelas_id,
                w.guru_id,
                w.tahun_ajaran_id,
                k.nama AS nama_kelas,
                g.nama AS nama_guru
            FROM t_wali_kelas w
            LEFT JOIN t_kelas k ON w.kelas_id = k.id
            LEFT JOIN t_guru g ON w.guru_id = g.id
            WHERE w.id = ?
            FOR UPDATE";

    $stmt = mysqli_prepare($koneksi, $sql);
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);

    $data = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

    if (!$data) {
        mysqli_rollback($koneksi);
        tampilkanPesan('Data Tidak Ditemukan', 'Data wali kelas tidak ditemukan.');
    }

    
    $hapus = mysqli_prepare(
        $koneksi,
        "DELETE FROM t_wali_kelas WHERE id = ?"
    );

    mysqli_stmt_bind_param($hapus, "i", $id);
    mysqli_stmt_execute($hapus);

    if (mysqli_stmt_affected_rows($hapus) !== 1) {
        throw new Exception('Data wali kelas gagal dihapus.');
    }

    
    $cekLog = mysqli_query(
        $koneksi,
        "SHOW TABLES LIKE 'tbl_log'"
    );

    if ($cekLog && mysqli_num_rows($cekLog) > 0) {
        $id_user = (int) ($_SESSION['id_user'] ?? $_SESSION['user_id'] ?? 0);

        if ($id_user > 0) {
            $namaKelas = $data['nama_kelas'] ?? 'Tidak diketahui';
            $namaGuru = $data['nama_guru'] ?? 'Tidak diketahui';

            $aktivitas = "Menghapus wali kelas {$namaGuru} untuk kelas {$namaKelas}";
            $waktu = date('Y-m-d H:i:s');

            $log = mysqli_prepare(
                $koneksi,
                "INSERT INTO tbl_log (id_user, aktivitas, waktu)
                 VALUES (?, ?, ?)"
            );

            mysqli_stmt_bind_param(
                $log,
                "iss",
                $id_user,
                $aktivitas,
                $waktu
            );

            mysqli_stmt_execute($log);
        }
    }

    mysqli_commit($koneksi);

    header('Location: kelola_wali_kelas.php?pesan=berhasil_hapus');
    exit;

} catch (Throwable $e) {
    mysqli_rollback($koneksi);

    error_log('Hapus wali kelas: ' . $e->getMessage());

    tampilkanPesan(
        'Gagal Menghapus',
        'Data wali kelas tidak dapat dihapus. Periksa kembali relasi database.'
    );
}
?>