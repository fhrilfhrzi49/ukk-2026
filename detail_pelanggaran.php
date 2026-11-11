<?php
require_once __DIR__ . '/config/akses.php';

wajib_role(['admin', 'guru', 'wali_kelas', 'guru_wali_kelas']);

require_once __DIR__ . '/includes/cek_session.php';
require_once __DIR__ . '/config/koneksi.php';

function h($value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$id || $id < 1) {
    header('Location: laporan.php');
    exit;
}

$role = $_SESSION['role'] ?? '';
$userId = (int) ($_SESSION['id'] ?? $_SESSION['user_id'] ?? 0);
$guruId = (int) ($_SESSION['guru_id'] ?? 0);

$sql = "
    SELECT
        cp.id,
        cp.tanggal_pelanggaran,
        cp.keterangan,
        cp.dicatat_oleh,
        s.nis,
        s.nama AS nama_siswa,
        COALESCE(NULLIF(j.nama_pelanggaran, ''), NULLIF(p.nama, ''), '-') AS nama_pelanggaran,
        p.kode,
        COALESCE(j.poin, p.poin, 0) AS poin,
        COALESCE(NULLIF(kj.nama, ''), NULLIF(kp.nama, ''), '-') AS nama_kategori,
        COALESCE(k.nama, '-') AS nama_kelas
    FROM t_catatan_pelanggaran cp
    LEFT JOIN t_siswa s ON s.id = cp.siswa_id
    LEFT JOIN t_pelanggaran p ON p.id = cp.pelanggaran_id
    LEFT JOIN t_pelanggaran_kategori kp ON kp.id = p.pelanggaran_kategori_id
    LEFT JOIN t_jenis_pelanggaran j ON j.id = cp.jenis_pelanggaran_id
    LEFT JOIN t_pelanggaran_kategori kj ON kj.id = j.kategori_id
    LEFT JOIN t_kelas_siswa ks ON ks.id = (
        SELECT ks2.id
        FROM t_kelas_siswa ks2
        WHERE ks2.siswa_id = cp.siswa_id
        ORDER BY (ks2.status_aktif = 1) DESC, ks2.tanggal_mulai DESC, ks2.id DESC
        LIMIT 1
    )
    LEFT JOIN t_kelas k ON k.id = ks.kelas_id
    WHERE cp.id = ?
";

if ($role !== 'admin') {
    $sql .= "
        AND EXISTS (
            SELECT 1
            FROM t_wali_kelas wk
            INNER JOIN t_tahun_ajaran ta ON ta.id = wk.tahun_ajaran_id
            WHERE wk.user_id = ?
              AND wk.kelas_id = ks.kelas_id
              AND wk.status_aktif = 1
              AND ta.status_aktif = 1
              AND (wk.tanggal_mulai IS NULL OR CURDATE() >= wk.tanggal_mulai)
              AND (wk.tanggal_selesai IS NULL OR CURDATE() <= wk.tanggal_selesai)
    ";

    if ($role === 'guru_wali_kelas') {
        $sql .= " AND wk.guru_id = ? ";
    } elseif ($role === 'wali_kelas') {
        $sql .= " AND wk.guru_id IS NULL ";
    } else {
        $sql .= " AND 1 = 0 ";
    }

    $sql .= ")";
}

$stmt = mysqli_prepare($koneksi, $sql . " LIMIT 1 ");

if (!$stmt) {
    die('Gagal menyiapkan query: ' . h(mysqli_error($koneksi)));
}

if ($role === 'admin') {
    mysqli_stmt_bind_param($stmt, 'i', $id);
} elseif ($role === 'guru_wali_kelas') {
    mysqli_stmt_bind_param($stmt, 'iii', $id, $userId, $guruId);
} else {
    mysqli_stmt_bind_param($stmt, 'ii', $id, $userId);
}

mysqli_stmt_execute($stmt);
$data = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
mysqli_stmt_close($stmt);

if (!$data) {
    header('Location: laporan.php');
    exit;
}

$tanggal = !empty($data['tanggal_pelanggaran'])
    ? date('d F Y', strtotime($data['tanggal_pelanggaran']))
    : '-';
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Pelanggaran | SI-TIB</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container-fluid">
    <div class="row">
        <?php include __DIR__ . '/layout/sidebar.php'; ?>

        <main class="col-12 col-md-9 col-lg-10 ms-md-auto px-3 px-lg-4 py-4">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
                <div>
                    <h3 class="fw-bold mb-1">
                        <i class="bi bi-file-earmark-text text-primary me-2"></i>
                        Detail Pelanggaran Siswa
                    </h3>
                    <p class="text-secondary mb-0">Informasi lengkap catatan pelanggaran siswa.</p>
                </div>
                <a href="laporan.php" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left me-1"></i> Kembali
                </a>
            </div>

            <div class="alert alert-info border-0 shadow-sm rounded-3 mb-4">
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-info-circle-fill fs-5"></i>
                    <div>
                        <strong>Informasi Laporan</strong>
                        <div class="small">Detail catatan pelanggaran dengan ID #<?= (int) $data['id']; ?>.</div>
                    </div>
                </div>
            </div>

            <div class="row g-4">
                <div class="col-12 col-lg-6">
                    <div class="card border-0 shadow-sm rounded-4 h-100">
                        <div class="card-header bg-white border-0 p-4">
                            <h5 class="fw-bold mb-0">
                                <i class="bi bi-person-vcard text-primary me-2"></i>
                                Identitas Siswa
                            </h5>
                        </div>
                        <div class="card-body px-4 pt-0">
                            <?php foreach ([
                                ['NIS', $data['nis'] ?? '-'],
                                ['Nama Siswa', $data['nama_siswa'] ?? 'Siswa tidak ditemukan'],
                                ['Kelas', $data['nama_kelas'] ?? '-']
                            ] as $i => [$label, $value]): ?>
                                <div class="mb-3">
                                    <label class="text-secondary small mb-1"><?= h($label); ?></label>
                                    <div class="fw-semibold"><?= h($value); ?></div>
                                </div>
                                <?php if ($i < 2): ?><hr><?php endif; ?>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-lg-6">
                    <div class="card border-0 shadow-sm rounded-4 h-100">
                        <div class="card-header bg-white border-0 p-4">
                            <h5 class="fw-bold mb-0">
                                <i class="bi bi-exclamation-triangle text-danger me-2"></i>
                                Informasi Pelanggaran
                            </h5>
                        </div>
                        <div class="card-body px-4 pt-0">
                            <div class="mb-3">
                                <label class="text-secondary small mb-1">Kategori Pelanggaran</label>
                                <div class="fw-semibold"><?= h($data['nama_kategori'] ?? '-'); ?></div>
                            </div>
                            <hr>
                            <div class="mb-3">
                                <label class="text-secondary small mb-1">Jenis Pelanggaran</label>
                                <div class="fw-semibold">
                                    <?php if (!empty($data['kode'])): ?>
                                        <?= h($data['kode']); ?> -
                                    <?php endif; ?>
                                    <?= h($data['nama_pelanggaran'] ?? '-'); ?>
                                </div>
                            </div>
                            <hr>
                            <div class="mb-3">
                                <label class="text-secondary small mb-1">Jumlah Poin</label>
                                <div>
                                    <span class="badge text-bg-warning fs-6 px-3 py-2">
                                        <?= (int) ($data['poin'] ?? 0); ?> Poin
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-12">
                    <div class="card border-0 shadow-sm rounded-4">
                        <div class="card-header bg-white border-0 p-4">
                            <h5 class="fw-bold mb-0">
                                <i class="bi bi-clipboard-check text-success me-2"></i>
                                Detail Catatan Pelanggaran
                            </h5>
                        </div>
                        <div class="card-body px-4 pt-0">
                            <div class="row g-4">
                                <div class="col-12 col-md-6">
                                    <label class="text-secondary small mb-1">Tanggal Pelanggaran</label>
                                    <div class="fw-semibold">
                                        <i class="bi bi-calendar-event text-primary me-2"></i>
                                        <?= h($tanggal); ?>
                                    </div>
                                </div>
                                <div class="col-12 col-md-6">
                                    <label class="text-secondary small mb-1">Dicatat Oleh</label>
                                    <div class="fw-semibold">
                                        <i class="bi bi-person-badge text-primary me-2"></i>
                                        <?= h($data['dicatat_oleh'] ?? '-'); ?>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <hr>
                                    <label class="text-secondary small mb-2">Keterangan Pelanggaran</label>
                                    <div class="bg-light rounded-3 p-3">
                                        <?= nl2br(h($data['keterangan'] ?? '-')); ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <footer class="text-center text-secondary small py-4">
                &copy; 2026 SI-TIB — Sistem Informasi Tata Tertib
            </footer>
        </main>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>