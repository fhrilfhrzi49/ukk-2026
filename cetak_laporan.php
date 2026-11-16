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

$role = strtolower(trim($_SESSION['role'] ?? ''));
$userId = (int) ($_SESSION['id'] ?? $_SESSION['user_id'] ?? 0);
$guruId = (int) ($_SESSION['guru_id'] ?? 0);
$nama_login = $_SESSION['nama'] ?? $_SESSION['name'] ?? $_SESSION['username'] ?? 'Pengguna';

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
    LEFT JOIN t_siswa s ON s.id = cp.siswa_id
    LEFT JOIN t_pelanggaran p ON p.id = cp.pelanggaran_id
    LEFT JOIN t_jenis_pelanggaran j ON j.id = cp.jenis_pelanggaran_id
    LEFT JOIN t_kelas_siswa ks ON ks.id = (
        SELECT ks2.id
        FROM t_kelas_siswa ks2
        WHERE ks2.siswa_id = cp.siswa_id
        ORDER BY
            (ks2.status_aktif = 1) DESC,
            ks2.tanggal_mulai DESC,
            ks2.id DESC
        LIMIT 1
    )
    LEFT JOIN t_kelas k ON k.id = ks.kelas_id
";

if ($role !== 'admin') {
    $sql .= "
        WHERE EXISTS (
            SELECT 1
            FROM t_wali_kelas wk
            INNER JOIN t_tahun_ajaran ta ON ta.id = wk.tahun_ajaran_id
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
        $sql .= " AND 1 = 0 ";
    }

    $sql .= " ) ";
}

$sql .= " ORDER BY cp.tanggal_pelanggaran DESC, cp.id DESC ";

$stmt = mysqli_prepare($koneksi, $sql);

if (!$stmt) {
    die('Gagal menyiapkan query laporan: ' . h(mysqli_error($koneksi)));
}

if ($role === 'admin') {
    mysqli_stmt_execute($stmt);
} elseif ($role === 'guru_wali_kelas') {
    mysqli_stmt_bind_param($stmt, 'ii', $userId, $guruId);
    mysqli_stmt_execute($stmt);
} else {
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

$bulanIndonesia = [
    1 => 'Januari',
    2 => 'Februari',
    3 => 'Maret',
    4 => 'April',
    5 => 'Mei',
    6 => 'Juni',
    7 => 'Juli',
    8 => 'Agustus',
    9 => 'September',
    10 => 'Oktober',
    11 => 'November',
    12 => 'Desember'
];

$tanggalCetak = date('d') . ' ' . $bulanIndonesia[(int) date('n')] . ' ' . date('Y');
$tahunAjaran = '-';

$queryTahun = mysqli_query(
    $koneksi,
    "SELECT nama FROM t_tahun_ajaran WHERE status_aktif = 1 ORDER BY id DESC LIMIT 1"
);

if ($queryTahun && mysqli_num_rows($queryTahun) > 0) {
    $tahunAjaran = mysqli_fetch_assoc($queryTahun)['nama'];
}

$nomorDokumen = 'SI-TIB/LAP/' . date('Ymd') . '/' . str_pad((string) $total, 3, '0', STR_PAD_LEFT);

$namaPenandatangan = $nama_login;
$nipPenandatangan = '-';

$jabatan = match ($role) {
    'guru' => 'Guru',
    'wali_kelas' => 'Wali Kelas',
    'guru_wali_kelas' => 'Guru / Wali Kelas',
    'admin' => 'Administrator',
    default => 'Petugas'
};

if ($guruId > 0) {
    $stmtGuru = mysqli_prepare(
        $koneksi,
        "SELECT nama, nip FROM t_guru WHERE id = ? LIMIT 1"
    );

    if ($stmtGuru) {
        mysqli_stmt_bind_param($stmtGuru, 'i', $guruId);
        mysqli_stmt_execute($stmtGuru);
        $resultGuru = mysqli_stmt_get_result($stmtGuru);

        if ($resultGuru && $guru = mysqli_fetch_assoc($resultGuru)) {
            $namaPenandatangan = $guru['nama'] ?: $nama_login;
            $nipPenandatangan = $guru['nip'] ?: '-';
        }

        mysqli_stmt_close($stmtGuru);
    }
} elseif ($userId > 0) {
    $stmtGuru = mysqli_prepare(
        $koneksi,
        "SELECT nama, nip FROM t_guru WHERE user_id = ? LIMIT 1"
    );

    if ($stmtGuru) {
        mysqli_stmt_bind_param($stmtGuru, 'i', $userId);
        mysqli_stmt_execute($stmtGuru);
        $resultGuru = mysqli_stmt_get_result($stmtGuru);

        if ($resultGuru && $guru = mysqli_fetch_assoc($resultGuru)) {
            $namaPenandatangan = $guru['nama'] ?: $nama_login;
            $nipPenandatangan = $guru['nip'] ?: '-';
        }

        mysqli_stmt_close($stmtGuru);
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Cetak Laporan Pelanggaran | SI-TIB</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css" rel="stylesheet">

    <style>
        body {
            font-family: "Times New Roman", serif;
            color: #000;
            font-size: 12pt;
        }

        .dokumen {
            max-width: 1100px;
            margin: 30px auto;
            padding: 45px 55px;
            background: #fff;
            box-shadow: 0 0 10px rgba(0, 0, 0, .12);
        }

        .logo-sekolah {
            width: 90px;
            height: 90px;
            object-fit: contain;
        }

        .kop-surat h4,
        .kop-surat h5,
        .kop-surat p {
            margin: 0;
        }

        .garis-kop {
            border: 0;
            border-top: 3px solid #000;
            border-bottom: 1px solid #000;
            height: 5px;
            margin: 12px 0 22px;
        }

        .judul-dokumen {
            text-align: center;
            margin-bottom: 22px;
        }

        .judul-dokumen h5 {
            font-weight: bold;
            text-decoration: underline;
            margin-bottom: 5px;
        }

        .identitas-dokumen {
            width: 100%;
            margin-bottom: 18px;
        }

        .identitas-dokumen td {
            padding: 2px 5px;
            vertical-align: top;
        }

        .ringkasan {
            display: flex;
            gap: 25px;
            margin-bottom: 15px;
            font-weight: bold;
        }

        .tabel-laporan {
            width: 100%;
            border-collapse: collapse;
            font-size: 9pt;
        }

        .tabel-laporan th,
        .tabel-laporan td {
            border: 1px solid #000;
            padding: 6px 5px;
            vertical-align: middle;
        }

        .tabel-laporan th {
            text-align: center;
            font-weight: bold;
            background: #eee;
        }

        .tengah {
            text-align: center;
        }

        .ttd {
            margin-top: 45px;
            page-break-inside: avoid;
        }

        .nama-ttd {
            font-weight: bold;
            text-decoration: underline;
        }

        .footer-dokumen {
            margin-top: 25px;
            padding-top: 8px;
            border-top: 1px solid #000;
            text-align: center;
            font-size: 9pt;
        }

        @page {
            size: A4 landscape;
            margin: 12mm;
        }

        @media print {
            body {
                background: #fff !important;
                font-size: 11pt;
            }

            .dokumen {
                max-width: none;
                margin: 0;
                padding: 0;
                box-shadow: none;
            }

            .no-print {
                display: none !important;
            }

            .tabel-laporan {
                font-size: 9pt;
            }

            .tabel-laporan thead {
                display: table-header-group;
            }

            .tabel-laporan tr {
                page-break-inside: avoid;
            }

            .ringkasan,
            .identitas-dokumen,
            .judul-dokumen {
                page-break-inside: avoid;
            }
        }
    </style>
</head>

<body class="bg-light">

<div class="dokumen">

    <div class="row align-items-center text-center kop-surat">
        <div class="col-2">
            <img src="assets/img/logo-smkmtsm.png" class="logo-sekolah" alt="Logo SMK Muhammadiyah Tasikmalaya">
        </div>

        <div class="col-10">
            <h5 class="fw-bold">PIMPINAN DAERAH MUHAMMADIYAH</h5>
            <h5 class="fw-bold">MAJELIS PENDIDIKAN DASAR MENENGAH</h5>
            <h4 class="fw-bold">SMK MUHAMMADIYAH TASIKMALAYA</h4>
            <p>Jl. Rumah Sakit No. 29, Empangsari, Kec. Tawang, Kota Tasikmalaya, Jawa Barat 46113</p>
            <p>Website: https://smkmtsm.sch.id | Email: info@smkmtsm.sch.id</p>
            <p>Telepon: (0265) 327380</p>
        </div>
    </div>

    <hr class="garis-kop">

    <div class="judul-dokumen">
        <h5>LAPORAN DATA PELANGGARAN TATA TERTIB SISWA</h5>
        <div>TAHUN AJARAN <?= h($tahunAjaran); ?></div>
    </div>

    <table class="identitas-dokumen">
        <tr>
            <td width="125">Nomor Dokumen</td>
            <td width="10">:</td>
            <td><?= h($nomorDokumen); ?></td>
            <td width="125">Tanggal Cetak</td>
            <td width="10">:</td>
            <td><?= h($tanggalCetak); ?></td>
        </tr>
        <tr>
            <td>Dicetak Oleh</td>
            <td>:</td>
            <td><?= h($nama_login); ?></td>
            <td>Jenis Dokumen</td>
            <td>:</td>
            <td>Laporan Pelanggaran Siswa</td>
        </tr>
    </table>

    <div class="ringkasan">
        <div>Jumlah Laporan: <?= number_format($total); ?> Data</div>
        <div>Total Poin: <?= number_format($totalPoin); ?> Poin</div>
    </div>

    <table class="tabel-laporan">
        <thead>
            <tr>
                <th width="35">No.</th>
                <th width="85">Tanggal</th>
                <th width="75">NIS</th>
                <th width="130">Nama Siswa</th>
                <th width="85">Kelas</th>
                <th>Jenis Pelanggaran</th>
                <th width="50">Poin</th>
                <th>Keterangan</th>
                <th width="110">Dicatat Oleh</th>
            </tr>
        </thead>

        <tbody>
            <?php if ($total > 0): ?>
                <?php foreach ($laporan as $index => $row): ?>
                    <tr>
                        <td class="tengah"><?= $index + 1; ?></td>
                        <td class="tengah">
                            <?= !empty($row['tanggal_pelanggaran'])
                                ? h(date('d-m-Y', strtotime($row['tanggal_pelanggaran'])))
                                : '-'; ?>
                        </td>
                        <td><?= h($row['nis'] ?? '-'); ?></td>
                        <td><?= h($row['nama_siswa'] ?? '-'); ?></td>
                        <td class="tengah"><?= h($row['nama_kelas'] ?? '-'); ?></td>
                        <td>
                            <?= h(
                                !empty($row['kode'])
                                    ? $row['kode'] . ' - ' . $row['nama_pelanggaran']
                                    : $row['nama_pelanggaran']
                            ); ?>
                        </td>
                        <td class="tengah"><?= (int) ($row['poin'] ?? 0); ?></td>
                        <td><?= h($row['keterangan'] ?? '-'); ?></td>
                        <td><?= h($row['dicatat_oleh'] ?? '-'); ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="9" class="tengah">
                        Belum terdapat data laporan pelanggaran siswa.
                    </td>
                </tr>
            <?php endif; ?>
        </tbody>

        <tfoot>
            <tr>
                <th colspan="6" class="text-end">Total Keseluruhan Poin</th>
                <th class="tengah"><?= number_format($totalPoin); ?></th>
                <th colspan="2"></th>
            </tr>
        </tfoot>
    </table>

    <div class="row justify-content-end mt-5 ttd">
        <div class="col-6 col-md-5 text-center">
            <p>Tasikmalaya, <?= h($tanggalCetak); ?></p>
            <p>Mengetahui,</p>
            <p class="fw-bold"><?= h($jabatan); ?></p>
            <br><br><br>
            <p class="fw-bold text-decoration-underline mb-0">
                <?= h($namaPenandatangan); ?>
            </p>
            <p class="mb-0">NIP. <?= h($nipPenandatangan); ?></p>
        </div>
    </div>

    <div class="footer-dokumen">
        Dokumen ini dihasilkan melalui SI-TIB — Sistem Informasi Tata Tertib.
        <br>
        © <?= date('Y'); ?> SI-TIB
    </div>

    <div class="no-print d-flex flex-wrap gap-2 justify-content-center mt-4">
        <button onclick="window.print()" class="btn btn-primary">
            <i class="bi bi-printer me-1"></i>
            Cetak Laporan
        </button>

        <a href="laporan.php" class="btn btn-secondary">
            <i class="bi bi-arrow-left me-1"></i>
            Kembali
        </a>
    </div>

</div>

</body>
</html>