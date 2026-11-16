
<?php
require_once __DIR__ . '/config/akses.php';

wajib_role(['guru', 'wali_kelas', 'guru_wali_kelas']);

require_once __DIR__ . '/includes/cek_session.php';
require_once __DIR__ . '/config/koneksi.php';

function h($value): string
{
    return htmlspecialchars((string) ($value ?? ''), ENT_QUOTES, 'UTF-8');
}

$siswaId = filter_input(INPUT_GET, 'siswa_id', FILTER_VALIDATE_INT);

if (!$siswaId || $siswaId < 1) {
    exit('ID siswa tidak valid. Silakan kembali ke rekap poin dan pilih siswa.');
}

$role = strtolower(trim((string) ($_SESSION['role'] ?? '')));
$role = str_replace([' ', '-'], '_', $role);

$namaPengguna = $_SESSION['nama'] ?? $_SESSION['name'] ?? $_SESSION['username'] ?? 'Pengguna';
$userId = (int) ($_SESSION['id'] ?? $_SESSION['user_id'] ?? 0);
$guruId = (int) ($_SESSION['guru_id'] ?? 0);

$jabatan = match ($role) {
    'guru' => 'Guru',
    'wali_kelas' => 'Wali Kelas',
    'guru_wali_kelas' => 'Guru / Wali Kelas',
    default => 'Petugas'
};

$namaPenandatangan = $namaPengguna;
$nipPenandatangan = '-';

if ($guruId > 0) {
    $stmt = mysqli_prepare($koneksi, "
        SELECT nama, nip
        FROM t_guru
        WHERE id = ?
        LIMIT 1
    ");

    if ($stmt) {
        mysqli_stmt_bind_param($stmt, 'i', $guruId);
        mysqli_stmt_execute($stmt);
        $guru = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
        mysqli_stmt_close($stmt);

        if ($guru) {
            $namaPenandatangan = $guru['nama'] ?: $namaPengguna;
            $nipPenandatangan = $guru['nip'] ?: '-';
        }
    }
} elseif ($userId > 0) {
    $stmt = mysqli_prepare($koneksi, "
        SELECT nama, nip
        FROM t_guru
        WHERE user_id = ?
        LIMIT 1
    ");

    if ($stmt) {
        mysqli_stmt_bind_param($stmt, 'i', $userId);
        mysqli_stmt_execute($stmt);
        $guru = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
        mysqli_stmt_close($stmt);

        if ($guru) {
            $namaPenandatangan = $guru['nama'] ?: $namaPengguna;
            $nipPenandatangan = $guru['nip'] ?: '-';
        }
    }
}

$sql = "
    SELECT
        s.id,
        s.nis,
        s.nisn,
        s.nama,
        s.jenis_kelamin,
        s.alamat,
        COALESCE(k.nama, '-') AS nama_kelas,
        COALESCE(ta.nama, '-') AS tahun_ajaran
    FROM t_siswa s
    LEFT JOIN t_kelas_siswa ks
        ON ks.siswa_id = s.id
        AND ks.status_aktif = 1
    LEFT JOIN t_kelas k ON k.id = ks.kelas_id
    LEFT JOIN t_tahun_ajaran ta ON ta.id = ks.tahun_ajaran_id
    WHERE s.id = ?
    LIMIT 1
";

$stmt = mysqli_prepare($koneksi, $sql);
mysqli_stmt_bind_param($stmt, 'i', $siswaId);
mysqli_stmt_execute($stmt);
$siswa = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
mysqli_stmt_close($stmt);

if (!$siswa) {
    exit('Data siswa tidak ditemukan.');
}

$sqlPoin = "
    SELECT COALESCE(SUM(COALESCE(jp.poin, p.poin, 0)), 0) AS total_poin
    FROM t_catatan_pelanggaran cp
    INNER JOIN t_tindakan_siswa ts
        ON ts.catatan_pelanggaran_id = cp.id
        AND UPPER(TRIM(ts.status)) = 'SELESAI'
    LEFT JOIN t_jenis_pelanggaran jp ON jp.id = cp.jenis_pelanggaran_id
    LEFT JOIN t_pelanggaran p ON p.id = cp.pelanggaran_id
    WHERE cp.siswa_id = ?
";

$stmt = mysqli_prepare($koneksi, $sqlPoin);
mysqli_stmt_bind_param($stmt, 'i', $siswaId);
mysqli_stmt_execute($stmt);
$dataPoin = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
mysqli_stmt_close($stmt);

$totalPoin = (int) ($dataPoin['total_poin'] ?? 0);

if ($totalPoin <= 50) {
    exit('Surat peringatan hanya dapat dicetak untuk siswa berstatus merah dengan total poin lebih dari 50.');
}

$sqlPelanggaran = "
    SELECT
        cp.tanggal_pelanggaran,
        COALESCE(jp.nama_pelanggaran, p.nama, '-') AS nama_pelanggaran,
        COALESCE(jp.poin, p.poin, 0) AS poin,
        cp.keterangan
    FROM t_catatan_pelanggaran cp
    INNER JOIN t_tindakan_siswa ts
        ON ts.catatan_pelanggaran_id = cp.id
        AND UPPER(TRIM(ts.status)) = 'SELESAI'
    LEFT JOIN t_jenis_pelanggaran jp ON jp.id = cp.jenis_pelanggaran_id
    LEFT JOIN t_pelanggaran p ON p.id = cp.pelanggaran_id
    WHERE cp.siswa_id = ?
    ORDER BY cp.tanggal_pelanggaran ASC, cp.id ASC
";

$stmt = mysqli_prepare($koneksi, $sqlPelanggaran);
mysqli_stmt_bind_param($stmt, 'i', $siswaId);
mysqli_stmt_execute($stmt);
$resultPelanggaran = mysqli_stmt_get_result($stmt);

$pelanggaran = [];

while ($row = mysqli_fetch_assoc($resultPelanggaran)) {
    $pelanggaran[] = $row;
}

mysqli_stmt_close($stmt);

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

$tanggalSurat = date('d') . ' ' . $bulanIndonesia[(int) date('n')] . ' ' . date('Y');
$nomorSurat = '001/SI-TIB/' . date('Y');
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Surat Peringatan - <?= h($siswa['nama']) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">

    <style>
        @media print {
            .no-print {
                display: none !important;
            }

            body {
                background: white !important;
            }
        }
    </style>
</head>
<body class="bg-light">

<div class="container py-3">

    <div class="bg-white border rounded p-4 p-md-5">
        <div class="row align-items-center text-center">
            <div class="col-3 col-md-2">
                <img src="assets/img/logo-smkmtsm.png" alt="Logo Sekolah" class="img-fluid">
            </div>
            <div class="col-9 col-md-10">
                <h6 class="fw-bold mb-1">PEMERINTAH PROVINSI JAWA BARAT</h6>
                <h6 class="fw-bold mb-1">DINAS PENDIDIKAN</h6>
                <h4 class="fw-bold mb-1">SMK MUHAMMADIYAH TASIKMALAYA</h4>
                <p class="mb-0 small">Jl. Rumah Sakit No. 29, Tasikmalaya</p>
                <p class="mb-0 small">Email: smkmuhammadiyahtasikmalaya@gmail.com</p>
            </div>
        </div>

        <hr class="border border-dark border-2 opacity-100">

        <h5 class="text-center fw-bold text-decoration-underline mt-4 mb-1">
            SURAT PERINGATAN
        </h5>
        <p class="text-center mb-4">Nomor: <?= h($nomorSurat) ?></p>

        <p>
            Yang bertanda tangan di bawah ini, pihak sekolah SMK Muhammadiyah
            Tasikmalaya, memberikan surat peringatan kepada siswa berikut:
        </p>

        <table class="table table-borderless table-sm w-auto">
            <tr>
                <td>Nama</td>
                <td>:</td>
                <td><?= h($siswa['nama']) ?></td>
            </tr>
            <tr>
                <td>NIS</td>
                <td>:</td>
                <td><?= h($siswa['nis']) ?></td>
            </tr>
            <tr>
                <td>NISN</td>
                <td>:</td>
                <td><?= h($siswa['nisn']) ?></td>
            </tr>
            <tr>
                <td>Jenis Kelamin</td>
                <td>:</td>
                <td><?= h($siswa['jenis_kelamin']) ?></td>
            </tr>
            <tr>
                <td>Kelas</td>
                <td>:</td>
                <td><?= h($siswa['nama_kelas']) ?></td>
            </tr>
            <tr>
                <td>Tahun Ajaran</td>
                <td>:</td>
                <td><?= h($siswa['tahun_ajaran']) ?></td>
            </tr>
            <tr>
                <td>Total Poin</td>
                <td>:</td>
                <td><strong><?= $totalPoin ?> Poin</strong></td>
            </tr>
        </table>

        <p>
            Berdasarkan catatan tata tertib sekolah, siswa tersebut telah
            memperoleh total <strong><?= $totalPoin ?> poin pelanggaran</strong>.
            Dengan ini pihak sekolah memberikan surat peringatan agar siswa
            segera memperbaiki sikap dan mematuhi seluruh peraturan yang berlaku
            di lingkungan sekolah.
        </p>

        <p>Adapun catatan pelanggaran siswa adalah sebagai berikut:</p>

        <div class="table-responsive">
            <table class="table table-bordered table-sm align-middle">
                <thead class="table-light text-center">
                    <tr>
                        <th>No</th>
                        <th>Tanggal</th>
                        <th>Jenis Pelanggaran</th>
                        <th>Poin</th>
                        <th>Keterangan</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($pelanggaran) > 0): ?>
                        <?php foreach ($pelanggaran as $index => $item): ?>
                            <tr>
                                <td class="text-center"><?= $index + 1 ?></td>
                                <td><?= h(date('d-m-Y', strtotime($item['tanggal_pelanggaran']))) ?></td>
                                <td><?= h($item['nama_pelanggaran']) ?></td>
                                <td class="text-center"><?= (int) $item['poin'] ?></td>
                                <td><?= h($item['keterangan'] ?: '-') ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="text-center">Tidak ada data pelanggaran.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <p>
            Siswa diharapkan tidak mengulangi pelanggaran tersebut. Apabila
            tidak terdapat perubahan sikap atau kembali melakukan pelanggaran,
            pihak sekolah dapat memberikan tindakan lanjutan sesuai dengan
            peraturan tata tertib sekolah.
        </p>

        <p>
            Demikian surat peringatan ini dibuat agar dapat diperhatikan dan
            dilaksanakan sebagaimana mestinya.
        </p>

        <div class="row justify-content-end mt-5">
            <div class="col-6 col-md-5 text-center">
                <p>Tasikmalaya, <?= h($tanggalSurat) ?></p>
                <p>Mengetahui,</p>
                <p class="fw-bold"><?= h($jabatan) ?></p>
                <br><br><br>
                <p class="fw-bold text-decoration-underline mb-0">
                    <?= h($namaPenandatangan) ?>
                </p>
                <p class="mb-0">NIP. <?= h($nipPenandatangan) ?></p>
            </div>
        </div>
    </div>

    <div class="no-print d-flex gap-2 justify-content-center mt-4">
        <a href="rekap_poin.php" class="btn btn-secondary">
            <i class="bi bi-arrow-left me-1"></i> Kembali
        </a>
        <button onclick="window.print()" class="btn btn-primary">
            <i class="bi bi-printer me-1"></i> Cetak Surat
        </button>
    </div>

</div>

</body>
</html>
