
<?php
require_once __DIR__ . '/config/akses.php';

wajib_role([
    'admin',
    'guru',
    'wali_kelas',
    'guru_wali_kelas'
]);

require_once __DIR__ . '/includes/cek_session.php';
require_once __DIR__ . '/config/koneksi.php';

function h($value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
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

        COALESCE(
            NULLIF(j.nama_pelanggaran, ''),
            NULLIF(p.nama, ''),
            '-'
        ) AS nama_pelanggaran,

        p.kode,

        COALESCE(j.poin, p.poin, 0) AS poin,

        COALESCE(k.nama, '-') AS nama_kelas

    FROM t_catatan_pelanggaran cp

    LEFT JOIN t_siswa s
        ON s.id = cp.siswa_id

    LEFT JOIN t_pelanggaran p
        ON p.id = cp.pelanggaran_id

    LEFT JOIN t_jenis_pelanggaran j
        ON j.id = cp.jenis_pelanggaran_id

    LEFT JOIN t_kelas_siswa ks
        ON ks.id = (
            SELECT ks2.id
            FROM t_kelas_siswa ks2
            WHERE ks2.siswa_id = cp.siswa_id
            ORDER BY
                (ks2.status_aktif = 1) DESC,
                ks2.tanggal_mulai DESC,
                ks2.id DESC
            LIMIT 1
        )

    LEFT JOIN t_kelas k
        ON k.id = ks.kelas_id
";

if ($role !== 'admin') {

    $sql .= "
        WHERE EXISTS (
            SELECT 1
            FROM t_wali_kelas wk

            INNER JOIN t_tahun_ajaran ta
                ON ta.id = wk.tahun_ajaran_id

            WHERE wk.user_id = ?
              AND wk.kelas_id = ks.kelas_id
              AND wk.status_aktif = 1
              AND ta.status_aktif = 1

              AND (
                  wk.tanggal_mulai IS NULL
                  OR CURDATE() >= wk.tanggal_mulai
              )

              AND (
                  wk.tanggal_selesai IS NULL
                  OR CURDATE() <= wk.tanggal_selesai
              )
    ";

    if ($role === 'guru_wali_kelas') {
        $sql .= " AND wk.guru_id = ? ";
    } elseif ($role === 'wali_kelas') {
        $sql .= " AND wk.guru_id IS NULL ";
    } else {
        // Guru biasa tidak dapat mencetak laporan wali kelas.
        $sql .= " AND 1 = 0 ";
    }

    $sql .= "
        )
    ";
}

$sql .= "
    ORDER BY
        cp.tanggal_pelanggaran DESC,
        cp.id DESC
";

$stmt = mysqli_prepare($koneksi, $sql);

if (!$stmt) {
    die('Gagal menyiapkan query laporan: ' . h(mysqli_error($koneksi)));
}

if ($role === 'admin') {

    // Admin melihat semua laporan.
    mysqli_stmt_execute($stmt);

} elseif ($role === 'guru_wali_kelas') {

    // Guru wali kelas hanya melihat kelas yang ditugaskan.
    mysqli_stmt_bind_param($stmt, 'ii', $userId, $guruId);
    mysqli_stmt_execute($stmt);

} else {

    // Wali kelas dan guru biasa.
    mysqli_stmt_bind_param($stmt, 'i', $userId);
    mysqli_stmt_execute($stmt);

}

$data = mysqli_stmt_get_result($stmt);

if (!$data) {
    die('Gagal mengambil data laporan: ' . h(mysqli_stmt_error($stmt)));
}

$laporan = [];

while ($row = mysqli_fetch_assoc($data)) {
    $laporan[] = $row;
}

mysqli_stmt_close($stmt);

$total = count($laporan);

$totalPoin = array_sum(
    array_map(
        static fn($row) => (int) ($row['poin'] ?? 0),
        $laporan
    )
);

?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Cetak Laporan | SI-TIB</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css" rel="stylesheet">

    <style>
        body {
            font-family: Arial, sans-serif;
        }

        @media print {

            .no-print {
                display: none !important;
            }

            body {
                background: white !important;
            }

            .container {
                max-width: 100% !important;
                margin: 0 !important;
                padding: 10px !important;
                box-shadow: none !important;
            }

            table {
                font-size: 11px;
            }

            thead {
                display: table-header-group;
            }

            tr {
                page-break-inside: avoid;
            }

            @page {
                size: landscape;
                margin: 12mm;
            }
        }
    </style>
</head>

<body class="bg-light">

<div class="container bg-white p-4 p-md-5 my-4 shadow-sm">

    <div class="text-center mb-4">

        <h4 class="fw-bold mb-1">
            LAPORAN PELANGGARAN SISWA
        </h4>

        <h5 class="fw-semibold mb-1">
            SI-TIB
        </h5>

        <p class="mb-2">
            Sistem Informasi Tata Tertib
        </p>

        <hr class="border border-dark border-2">

    </div>

    <div class="row g-3 mb-3">

        <div class="col-6">
            <strong>Total Laporan:</strong>
            <?= number_format($total); ?> laporan
        </div>

        <div class="col-6 text-end">
            <strong>Total Poin:</strong>
            <?= number_format($totalPoin); ?> poin
        </div>

    </div>

    <div class="table-responsive">

        <table class="table table-bordered table-sm align-middle">

            <thead class="table-light text-center">
                <tr>
                    <th>No</th>
                    <th>Tanggal</th>
                    <th>NIS</th>
                    <th>Nama Siswa</th>
                    <th>Kelas</th>
                    <th>Jenis Pelanggaran</th>
                    <th>Poin</th>
                    <th>Keterangan</th>
                    <th>Dicatat Oleh</th>
                </tr>
            </thead>

            <tbody>

                <?php if ($total > 0): ?>

                    <?php foreach ($laporan as $index => $row): ?>

                        <tr>

                            <td class="text-center">
                                <?= $index + 1; ?>
                            </td>

                            <td class="text-center">
                                <?= !empty($row['tanggal_pelanggaran'])
                                    ? h(date('d-m-Y', strtotime($row['tanggal_pelanggaran'])))
                                    : '-'; ?>
                            </td>

                            <td>
                                <?= h($row['nis'] ?? '-'); ?>
                            </td>

                            <td>
                                <?= h($row['nama_siswa'] ?? '-'); ?>
                            </td>

                            <td>
                                <?= h($row['nama_kelas'] ?? '-'); ?>
                            </td>

                            <td>
                                <?= h(
                                    !empty($row['kode'])
                                        ? $row['kode'] . ' - ' . $row['nama_pelanggaran']
                                        : $row['nama_pelanggaran']
                                ); ?>
                            </td>

                            <td class="text-center">
                                <?= (int) ($row['poin'] ?? 0); ?>
                            </td>

                            <td>
                                <?= h($row['keterangan'] ?? '-'); ?>
                            </td>

                            <td>
                                <?= h($row['dicatat_oleh'] ?? '-'); ?>
                            </td>

                        </tr>

                    <?php endforeach; ?>

                <?php else: ?>

                    <tr>
                        <td colspan="9" class="text-center">
                            Belum ada data laporan pelanggaran.
                        </td>
                    </tr>

                <?php endif; ?>

            </tbody>

        </table>

    </div>

    <div class="row justify-content-end mt-5">

        <div class="col-6 col-md-4 text-center">

            <p>
                Tasikmalaya, <?= date('d-m-Y'); ?>
            </p>

            <p class="mb-5">
                Petugas
            </p>

            <p class="fw-bold text-decoration-underline">
                (................................)
            </p>

        </div>

    </div>

    <div class="no-print d-flex flex-wrap gap-2 justify-content-center mt-4">

        <button onclick="window.print()" class="btn btn-primary">
            <i class="bi bi-printer"></i>
            Cetak Laporan
        </button>

        <a href="laporan.php" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i>
            Kembali
        </a>

    </div>

</div>

</body>
</html>