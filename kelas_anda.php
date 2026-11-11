
<?php

require_once __DIR__ . '/config/koneksi.php';
require_once __DIR__ . '/config/akses.php';
require_once __DIR__ . '/includes/cek_session.php';

wajib_role(['admin', 'administrator', 'guru', 'guru_wali_kelas', 'wali_kelas']);

if (session_status() === PHP_SESSION_NONE) session_start();

function e($nilai)
{
    return htmlspecialchars((string) ($nilai ?? ''), ENT_QUOTES, 'UTF-8');
}

$userId = (int) ($_SESSION['user_id'] ?? $_SESSION['id_user'] ?? $_SESSION['id'] ?? 0);
$role = function_exists('role_pengguna') ? role_pengguna() : ($_SESSION['role'] ?? '');
$idPenugasan = (int) ($_GET['id'] ?? 0);

if ($idPenugasan <= 0) {
    http_response_code(400);
    exit('ID kelas tidak valid.');
}

$sql = "SELECT
            wk.id AS id_penugasan,
            wk.user_id,
            wk.guru_id,
            wk.tanggal_mulai,
            wk.tanggal_selesai,
            wk.status_aktif AS status_penugasan,
            k.id AS kelas_id,
            k.nama AS nama_kelas,
            k.tingkat,
            k.jurusan,
            ta.id AS tahun_ajaran_id,
            ta.nama AS tahun_ajaran,
            ta.status_aktif AS status_tahun_ajaran,
            COALESCE(NULLIF(g.nama, ''), u.name, '-') AS nama_wali
        FROM t_wali_kelas wk
        INNER JOIN t_kelas k ON k.id = wk.kelas_id
        INNER JOIN t_tahun_ajaran ta ON ta.id = wk.tahun_ajaran_id
        LEFT JOIN t_guru g ON g.id = wk.guru_id
        LEFT JOIN t_users u ON u.id = wk.user_id
        WHERE wk.id = ?
        LIMIT 1";

$stmt = mysqli_prepare($koneksi, $sql);
mysqli_stmt_bind_param($stmt, 'i', $idPenugasan);
mysqli_stmt_execute($stmt);
$kelas = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
mysqli_stmt_close($stmt);

if (!$kelas) {
    http_response_code(404);
    exit('Data kelas tidak ditemukan.');
}

if (!in_array($role, ['admin', 'administrator'], true) && (int) $kelas['user_id'] !== $userId) {
    http_response_code(403);
    exit('Anda tidak memiliki akses ke kelas ini.');
}

$kelasId = (int) $kelas['kelas_id'];
$tahunAjaranId = (int) $kelas['tahun_ajaran_id'];

$sql = "SELECT
            s.id,
            s.nis,
            s.nisn,
            s.nama,
            s.jenis_kelamin,
            s.status_aktif,
            COALESCE((
                SELECT SUM(COALESCE(p.poin, jp.poin, 0))
                FROM t_catatan_pelanggaran cp
                LEFT JOIN t_pelanggaran p ON p.id = cp.pelanggaran_id
                LEFT JOIN t_jenis_pelanggaran jp ON jp.id = cp.jenis_pelanggaran_id
                WHERE cp.siswa_id = s.id
                AND EXISTS (
                    SELECT 1
                    FROM t_tindakan_siswa ts
                    WHERE ts.catatan_pelanggaran_id = cp.id
                    AND UPPER(TRIM(COALESCE(ts.status, 'PENDING'))) = 'SELESAI'
                )
            ), 0) AS total_poin,
            (
                SELECT COUNT(DISTINCT cp.id)
                FROM t_catatan_pelanggaran cp
                WHERE cp.siswa_id = s.id
                AND EXISTS (
                    SELECT 1
                    FROM t_tindakan_siswa ts
                    WHERE ts.catatan_pelanggaran_id = cp.id
                    AND UPPER(TRIM(COALESCE(ts.status, 'PENDING'))) = 'SELESAI'
                )
            ) AS jumlah_pelanggaran
        FROM t_kelas_siswa ks
        INNER JOIN t_siswa s ON s.id = ks.siswa_id
        WHERE ks.kelas_id = ?
        AND ks.tahun_ajaran_id = ?
        AND ks.status_aktif = 1
        AND s.status_aktif = 1
        ORDER BY s.nama ASC";

$stmt = mysqli_prepare($koneksi, $sql);
mysqli_stmt_bind_param($stmt, 'ii', $kelasId, $tahunAjaranId);
mysqli_stmt_execute($stmt);
$resultSiswa = mysqli_stmt_get_result($stmt);

$siswaList = [];
$totalPoinKelas = $totalPelanggaranKelas = $siswaPernahMelanggar = 0;

while ($row = mysqli_fetch_assoc($resultSiswa)) {
    $siswaList[] = $row;
    $totalPoinKelas += (int) $row['total_poin'];
    $totalPelanggaranKelas += (int) $row['jumlah_pelanggaran'];

    if ((int) $row['jumlah_pelanggaran'] > 0) {
        $siswaPernahMelanggar++;
    }
}

mysqli_stmt_close($stmt);

$sql = "SELECT
            cp.id,
            cp.tanggal_pelanggaran,
            cp.keterangan,
            s.nis,
            s.nama AS nama_siswa,
            COALESCE(p.nama, jp.nama_pelanggaran, '-') AS nama_pelanggaran,
            COALESCE(p.poin, jp.poin, 0) AS poin,
            COALESCE(
                NULLIF((SELECT u.name FROM t_users u WHERE u.id = cp.dicatat_oleh LIMIT 1), ''),
                NULLIF((SELECT g.nama FROM t_guru g WHERE g.id = cp.dicatat_oleh LIMIT 1), ''),
                NULLIF(CAST(cp.dicatat_oleh AS CHAR), ''),
                '-'
            ) AS nama_pencatat
        FROM t_catatan_pelanggaran cp
        INNER JOIN t_siswa s ON s.id = cp.siswa_id
        INNER JOIN t_kelas_siswa ks ON ks.siswa_id = s.id
            AND ks.kelas_id = ?
            AND ks.tahun_ajaran_id = ?
            AND ks.status_aktif = 1
        LEFT JOIN t_pelanggaran p ON p.id = cp.pelanggaran_id
        LEFT JOIN t_jenis_pelanggaran jp ON jp.id = cp.jenis_pelanggaran_id
        WHERE EXISTS (
            SELECT 1
            FROM t_tindakan_siswa ts
            WHERE ts.catatan_pelanggaran_id = cp.id
            AND UPPER(TRIM(COALESCE(ts.status, 'PENDING'))) = 'SELESAI'
        )
        ORDER BY cp.tanggal_pelanggaran DESC, cp.id DESC";

$stmt = mysqli_prepare($koneksi, $sql);
mysqli_stmt_bind_param($stmt, 'ii', $kelasId, $tahunAjaranId);
mysqli_stmt_execute($stmt);
$resultRiwayat = mysqli_stmt_get_result($stmt);

$riwayatList = [];

while ($row = mysqli_fetch_assoc($resultRiwayat)) {
    $riwayatList[] = $row;
}

mysqli_stmt_close($stmt);

$jumlahSiswa = count($siswaList);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Detail Kelas <?= e($kelas['nama_kelas']) ?> | SI-TIB</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container-fluid px-4 py-4">

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h3 class="fw-bold mb-1">Detail Kelas</h3>
            <p class="text-secondary mb-0">Informasi siswa dan riwayat pelanggaran kelas.</p>
        </div>
        <a href="dashboard.php" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i> Kembali ke Dashboard
        </a>
    </div>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-4">
            <div class="d-flex flex-wrap justify-content-between gap-3">
                <div>
                    <span class="badge text-bg-primary mb-2">Informasi Kelas</span>
                    <h4 class="fw-bold mb-1"><?= e($kelas['nama_kelas']) ?></h4>
                    <div class="text-secondary">
                        <?= e($kelas['tingkat']) ?>
                        <?= $kelas['jurusan'] ? ' - ' . e($kelas['jurusan']) : '' ?>
                    </div>
                </div>
                <div class="text-md-end">
                    <div class="text-secondary small">Tahun Ajaran</div>
                    <h5 class="fw-bold"><?= e($kelas['tahun_ajaran']) ?></h5>
                </div>
            </div>
            <hr>
            <div class="row g-3">
                <div class="col-md-4">
                    <div class="text-secondary small">Wali Kelas</div>
                    <div class="fw-semibold"><?= e($kelas['nama_wali']) ?></div>
                </div>
                <div class="col-md-4">
                    <div class="text-secondary small">Tanggal Mulai</div>
                    <div class="fw-semibold"><?= e(date('d-m-Y', strtotime($kelas['tanggal_mulai']))) ?></div>
                </div>
                <div class="col-md-4">
                    <div class="text-secondary small">Tanggal Selesai</div>
                    <div class="fw-semibold"><?= $kelas['tanggal_selesai'] ? e(date('d-m-Y', strtotime($kelas['tanggal_selesai']))) : '-' ?></div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <?php
        $statistik = [
            ['Siswa Aktif', $jumlahSiswa, 'bi-people', 'primary'],
            ['Siswa Pernah Melanggar', $siswaPernahMelanggar, 'bi-person-exclamation', 'warning'],
            ['Jumlah Kejadian', $totalPelanggaranKelas, 'bi-clipboard-data', 'info'],
            ['Total Poin', $totalPoinKelas, 'bi-star', 'danger']
        ];

        foreach ($statistik as [$label, $jumlah, $ikon, $warna]):
        ?>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <div class="text-secondary small"><?= $label ?></div>
                                <h3 class="fw-bold mb-0"><?= $jumlah ?></h3>
                            </div>
                            <i class="bi <?= $ikon ?> fs-2 text-<?= $warna ?>"></i>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white py-3">
            <h5 class="fw-bold mb-0"><i class="bi bi-people me-2"></i>Daftar Siswa</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th width="55">No.</th>
                            <th>NIS</th>
                            <th>NISN</th>
                            <th>Nama Siswa</th>
                            <th>Jenis Kelamin</th>
                            <th>Jumlah Pelanggaran</th>
                            <th>Total Poin</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($siswaList)): ?>
                            <tr>
                                <td colspan="7" class="text-center text-secondary py-4">Belum ada siswa aktif di kelas ini.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($siswaList as $index => $siswa): ?>
                                <tr>
                                    <td><?= $index + 1 ?></td>
                                    <td><?= e($siswa['nis']) ?></td>
                                    <td><?= e($siswa['nisn']) ?></td>
                                    <td class="fw-semibold"><?= e($siswa['nama']) ?></td>
                                    <td><?= e($siswa['jenis_kelamin']) ?></td>
                                    <td><span class="badge text-bg-info"><?= (int) $siswa['jumlah_pelanggaran'] ?> kali</span></td>
                                    <td><span class="badge text-bg-danger"><?= (int) $siswa['total_poin'] ?> poin</span></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3">
            <h5 class="fw-bold mb-0"><i class="bi bi-clock-history me-2"></i>Riwayat Pelanggaran Kelas</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th width="55">No.</th>
                            <th>Tanggal</th>
                            <th>NIS</th>
                            <th>Nama Siswa</th>
                            <th>Jenis Pelanggaran</th>
                            <th>Poin</th>
                            <th>Keterangan</th>
                            <th>Dicatat Oleh</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($riwayatList)): ?>
                            <tr>
                                <td colspan="8" class="text-center text-secondary py-4">Belum ada riwayat pelanggaran untuk kelas ini.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($riwayatList as $index => $riwayat): ?>
                                <tr>
                                    <td><?= $index + 1 ?></td>
                                    <td><?= e(date('d-m-Y', strtotime($riwayat['tanggal_pelanggaran']))) ?></td>
                                    <td><?= e($riwayat['nis']) ?></td>
                                    <td><?= e($riwayat['nama_siswa']) ?></td>
                                    <td><?= e($riwayat['nama_pelanggaran']) ?></td>
                                    <td><span class="badge text-bg-danger"><?= (int) $riwayat['poin'] ?> poin</span></td>
                                    <td><?= e($riwayat['keterangan'] ?: '-') ?></td>
                                    <td><?= e($riwayat['nama_pencatat']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="text-center text-secondary small mt-4">
        © 2026 SI-TIB — Sistem Informasi Tata Tertib
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>