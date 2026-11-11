<?php

require_once __DIR__ . '/config/akses.php';
wajib_role(['admin']);

require_once __DIR__ . '/includes/cek_session.php';
require_once __DIR__ . '/config/koneksi.php';

function tampilkanError(string $pesan): void
{
    ?>
    <!DOCTYPE html>
    <html lang="id">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Tambah Wali Kelas | SI-TIB</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    </head>
    <body class="bg-light">
        <div class="container min-vh-100 d-flex align-items-center justify-content-center">
            <div class="card border-0 shadow-sm rounded-4 w-100" style="max-width:520px">
                <div class="card-body p-4">
                    <div class="text-center mb-3">
                        <i class="bi bi-exclamation-circle-fill text-danger display-4"></i>
                        <h4 class="fw-bold mt-3">Gagal Menyimpan Data</h4>
                    </div>
                    <div class="alert alert-danger">
                        <?= htmlspecialchars($pesan, ENT_QUOTES, 'UTF-8') ?>
                    </div>
                    <a href="tambah_wali_kelas.php" class="btn btn-primary">
                        <i class="bi bi-arrow-left me-1"></i>Kembali
                    </a>
                </div>
            </div>
        </div>
    </body>
    </html>
    <?php
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: tambah_wali_kelas.php');
    exit;
}

$guruId = filter_input(INPUT_POST, 'guru_id', FILTER_VALIDATE_INT);
$kelasId = filter_input(INPUT_POST, 'kelas_id', FILTER_VALIDATE_INT);
$tahunId = filter_input(INPUT_POST, 'tahun_ajaran_id', FILTER_VALIDATE_INT);
$statusAktif = filter_input(INPUT_POST, 'status_aktif', FILTER_VALIDATE_INT);
$tanggalMulai = trim($_POST['tanggal_mulai'] ?? '');
$tanggalSelesai = trim($_POST['tanggal_selesai'] ?? '');

foreach ([
    [$guruId, 'Silakan pilih guru.'],
    [$kelasId, 'Silakan pilih kelas.'],
    [$tahunId, 'Silakan pilih tahun ajaran.']
] as [$id, $pesan]) {
    if (!$id || $id <= 0) {
        tampilkanError($pesan);
    }
}

if (!in_array($statusAktif, [0, 1], true)) {
    tampilkanError('Status penugasan tidak valid.');
}

foreach ([
    [$tanggalMulai, 'Tanggal mulai tidak valid.'],
    [$tanggalSelesai, 'Tanggal selesai tidak valid.']
] as [$tanggal, $pesan]) {
    $valid = DateTime::createFromFormat('!Y-m-d', $tanggal);

    if (!$valid || $valid->format('Y-m-d') !== $tanggal) {
        tampilkanError($pesan);
    }
}

if ($tanggalSelesai < $tanggalMulai) {
    tampilkanError('Tanggal selesai tidak boleh sebelum tanggal mulai.');
}

try {
    mysqli_begin_transaction($koneksi);

    $stmt = mysqli_prepare($koneksi, "
        SELECT
            (SELECT COUNT(*) FROM t_kelas WHERE id = ? AND status_aktif = 1) AS kelas_valid,
            (SELECT COUNT(*) FROM t_tahun_ajaran WHERE id = ?) AS tahun_valid
    ");
    mysqli_stmt_bind_param($stmt, 'ii', $kelasId, $tahunId);
    mysqli_stmt_execute($stmt);
    $master = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    mysqli_stmt_close($stmt);

    if (!$master || (int) $master['kelas_valid'] !== 1 || (int) $master['tahun_valid'] !== 1) {
        throw new Exception('Kelas atau tahun ajaran tidak ditemukan atau tidak aktif.');
    }

    $stmt = mysqli_prepare($koneksi, "
        SELECT g.id, g.user_id, g.nama, u.role
        FROM t_guru g
        INNER JOIN t_users u ON u.id = g.user_id
        WHERE g.id = ? AND g.status_aktif = 1
        LIMIT 1
    ");
    mysqli_stmt_bind_param($stmt, 'i', $guruId);
    mysqli_stmt_execute($stmt);
    $guru = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    mysqli_stmt_close($stmt);

    if (!$guru) {
        throw new Exception('Data guru tidak ditemukan atau akun belum terhubung.');
    }

    $userIdSimpan = (int) $guru['user_id'];
    $guruIdSimpan = (int) $guru['id'];

    if ($statusAktif === 1) {
        $stmt = mysqli_prepare($koneksi, "
            SELECT id FROM t_wali_kelas
            WHERE kelas_id = ? AND tahun_ajaran_id = ? AND status_aktif = 1
            LIMIT 1
        ");
        mysqli_stmt_bind_param($stmt, 'ii', $kelasId, $tahunId);
        mysqli_stmt_execute($stmt);
        $adaPenugasan = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
        mysqli_stmt_close($stmt);

        if ($adaPenugasan) {
            throw new Exception('Kelas tersebut sudah memiliki wali kelas aktif pada tahun ajaran ini.');
        }

        $stmt = mysqli_prepare($koneksi, "
            SELECT id FROM t_wali_kelas
            WHERE guru_id = ? AND status_aktif = 1
            LIMIT 1
        ");
        mysqli_stmt_bind_param($stmt, 'i', $guruIdSimpan);
        mysqli_stmt_execute($stmt);
        $guruSudahDitugaskan = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
        mysqli_stmt_close($stmt);

        if ($guruSudahDitugaskan) {
            throw new Exception('Guru tersebut sudah menjadi wali kelas aktif.');
        }
    }

    $stmt = mysqli_prepare($koneksi, "
        INSERT INTO t_wali_kelas (
            tahun_ajaran_id, kelas_id, guru_id, user_id,
            tanggal_mulai, tanggal_selesai, status_aktif
        ) VALUES (?, ?, ?, ?, ?, ?, ?)
    ");
    mysqli_stmt_bind_param(
        $stmt,
        'iiiissi',
        $tahunId,
        $kelasId,
        $guruIdSimpan,
        $userIdSimpan,
        $tanggalMulai,
        $tanggalSelesai,
        $statusAktif
    );

    if (!mysqli_stmt_execute($stmt)) {
        throw new Exception('Gagal menyimpan penugasan: ' . mysqli_stmt_error($stmt));
    }

    mysqli_stmt_close($stmt);

    $roleBaru = 'guru_wali_kelas';
    $stmt = mysqli_prepare($koneksi, "
        UPDATE t_users SET role = ?, updated_at = NOW()
        WHERE id = ?
    ");
    mysqli_stmt_bind_param($stmt, 'si', $roleBaru, $userIdSimpan);

    if (!mysqli_stmt_execute($stmt)) {
        throw new Exception('Gagal memperbarui role guru: ' . mysqli_stmt_error($stmt));
    }

    mysqli_stmt_close($stmt);
    mysqli_commit($koneksi);

    header('Location: kelola_wali_kelas.php?pesan=berhasil_tambah');
    exit;

} catch (Throwable $e) {
    mysqli_rollback($koneksi);
    error_log('Tambah wali kelas: ' . $e->getMessage());
    tampilkanError($e->getMessage());
}