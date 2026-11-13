<?php
require_once __DIR__ . '/includes/cek_session.php';
require_once __DIR__ . '/config/akses.php';
require_once __DIR__ . '/config/koneksi.php';

wajib_role(['admin', 'guru_wali_kelas', 'wali_kelas']);

$namaPengguna = $_SESSION['nama'] ?? $_SESSION['name'] ?? $_SESSION['username'] ?? 'Pengguna';
$userId = (int) ($_SESSION['id'] ?? 0);
$guruId = (int) ($_SESSION['guru_id'] ?? 0);
$role = strtolower(trim((string) ($_SESSION['role'] ?? '')));

$role = match ($role) {
    'administrator' => 'admin',
    'guru sekaligus wali kelas' => 'guru_wali_kelas',
    'wali kelas' => 'wali_kelas',
    default => $role
};

function h($value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function tanggalValid($tanggal): bool
{
    if ($tanggal === '') return true;
    $hasil = DateTime::createFromFormat('!Y-m-d', $tanggal);
    return $hasil !== false && $hasil->format('Y-m-d') === $tanggal;
}

$tanggalMulai = trim($_GET['tanggal_mulai'] ?? '');
$tanggalSelesai = trim($_GET['tanggal_selesai'] ?? '');
$errorFilter = '';

if (!tanggalValid($tanggalMulai) || !tanggalValid($tanggalSelesai)) {
    $errorFilter = 'Format tanggal tidak valid.';
    $tanggalMulai = $tanggalSelesai = '';
}

if ($tanggalMulai !== '' && $tanggalSelesai !== '' && $tanggalMulai > $tanggalSelesai) {
    $errorFilter = 'Tanggal mulai tidak boleh melebihi tanggal selesai.';
    $tanggalMulai = $tanggalSelesai = '';
}

$sql = "
    SELECT cp.id, cp.tanggal_pelanggaran, cp.keterangan, cp.dicatat_oleh,
           s.nis, s.nama AS nama_siswa,
           COALESCE(NULLIF(j.nama_pelanggaran, ''), NULLIF(p.nama, ''), '-') AS nama_pelanggaran,
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
        ORDER BY (ks2.status_aktif = 1) DESC, ks2.tanggal_mulai DESC, ks2.id DESC
        LIMIT 1
    )
    LEFT JOIN t_kelas k ON k.id = ks.kelas_id
";

$kondisi = [];
$parameter = [];
$tipeParameter = '';

if ($role !== 'admin') {
    if ($userId <= 0) {
        http_response_code(403);
        exit('ID akun tidak ditemukan. Silakan login kembali.');
    }

    $aksesKelas = "
        EXISTS (
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

    $tipeParameter .= 'i';
    $parameter[] = $userId;

    if ($role === 'guru_wali_kelas') {
        if ($guruId <= 0) {
            http_response_code(403);
            exit('Data guru tidak ditemukan. Silakan login kembali.');
        }

        $aksesKelas .= " AND wk.guru_id = ?";
        $tipeParameter .= 'i';
        $parameter[] = $guruId;
    } elseif ($role === 'wali_kelas') {
        $aksesKelas .= " AND wk.guru_id IS NULL";
    }

    $kondisi[] = $aksesKelas . ")";
}

if ($tanggalMulai !== '') {
    $kondisi[] = "cp.tanggal_pelanggaran >= ?";
    $tipeParameter .= 's';
    $parameter[] = $tanggalMulai;
}

if ($tanggalSelesai !== '') {
    $kondisi[] = "cp.tanggal_pelanggaran <= ?";
    $tipeParameter .= 's';
    $parameter[] = $tanggalSelesai;
}

if ($kondisi) {
    $sql .= " WHERE " . implode(' AND ', $kondisi);
}

$sql .= " ORDER BY cp.tanggal_pelanggaran DESC, cp.id DESC";

$stmt = mysqli_prepare($koneksi, $sql);

if (!$stmt) {
    die('Gagal menyiapkan laporan: ' . h(mysqli_error($koneksi)));
}

if ($parameter) {
    mysqli_stmt_bind_param($stmt, $tipeParameter, ...$parameter);
}

if (!mysqli_stmt_execute($stmt)) {
    die('Gagal mengambil data laporan: ' . h(mysqli_stmt_error($stmt)));
}

$data = mysqli_stmt_get_result($stmt);

if (!$data) {
    die('Gagal membaca data laporan.');
}

$laporan = [];

while ($row = mysqli_fetch_assoc($data)) {
    $laporan[] = $row;
}

mysqli_stmt_close($stmt);

$totalLaporan = count($laporan);
$totalPoin = array_sum(array_map(fn($row) => (int) ($row['poin'] ?? 0), $laporan));
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan | SI-TIB</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/2.3.4/css/dataTables.bootstrap5.min.css" rel="stylesheet">
</head>

<body class="bg-light">
<div class="container-fluid">
    <div class="row">
        <?php include __DIR__ . '/layout/sidebar.php'; ?>

        <main class="col-12 col-md-9 col-lg-10 ms-md-auto px-3 px-lg-4 py-4">
            <div class="d-flex justify-content-end mb-4">
                <div class="bg-white rounded-4 shadow-sm px-3 py-2">
                    <div class="d-flex align-items-center gap-2">
                        <div class="rounded-circle bg-primary-subtle text-primary p-2">
                            <i class="bi bi-person-circle fs-5"></i>
                        </div>
                        <div>
                            <div class="small text-secondary">Login sebagai,</div>
                            <div class="fw-semibold text-dark"><?= h($namaPengguna) ?></div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mb-4">
                <h3 class="fw-bold mb-1">
                    <i class="bi bi-file-earmark-text text-primary me-2"></i>
                    Laporan Pelanggaran Siswa
                </h3>
                <p class="text-secondary mb-0">
                    <?= $role === 'admin'
                        ? 'Daftar seluruh laporan pelanggaran siswa.'
                        : 'Daftar laporan pelanggaran siswa dari kelas yang menjadi tanggung jawab Anda.' ?>
                </p>
            </div>

            <?php if ($errorFilter !== ''): ?>
                <div class="alert alert-danger border-0 shadow-sm rounded-3">
                    <i class="bi bi-exclamation-circle me-2"></i><?= h($errorFilter) ?>
                </div>
            <?php endif; ?>

            <div class="row g-3 mb-4">
                <?php foreach ([
                    ['Total Laporan', $totalLaporan, 'file-earmark-text', 'primary'],
                    ['Total Poin', $totalPoin, 'award', 'warning']
                ] as [$judul, $nilai, $ikon, $warna]): ?>
                    <div class="col-12 col-sm-6 col-lg-4">
                        <div class="card border-0 shadow-sm rounded-4">
                            <div class="card-body p-4">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <p class="text-secondary mb-1"><?= $judul ?></p>
                                        <h3 class="fw-bold mb-0"><?= number_format((int) $nilai) ?></h3>
                                    </div>
                                    <div class="bg-<?= $warna ?> bg-opacity-10 text-<?= $warna ?> rounded-3 p-3">
                                        <i class="bi bi-<?= $ikon ?> fs-3"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-header bg-white border-0 p-3 p-md-4 rounded-top-4">
                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
                        <div>
                            <h5 class="fw-bold mb-1">Data Laporan</h5>
                            <small class="text-secondary">Informasi pelanggaran yang telah dicatat.</small>
                        </div>
                        <a href="cetak_laporan.php" class="btn btn-primary">
                            <i class="bi bi-printer me-1"></i>Cetak Laporan
                        </a>
                    </div>
                </div>

                <div class="card-body p-3 p-md-4">
                    <form method="GET" action="laporan.php" class="bg-light rounded-3 p-3 mb-4">
                        <div class="row g-3 align-items-end">
                            <div class="col-12 col-md-4">
                                <label for="tanggal_mulai" class="form-label fw-semibold">Tanggal Awal</label>
                                <input type="date" class="form-control" id="tanggal_mulai" name="tanggal_mulai" value="<?= h($tanggalMulai) ?>">
                            </div>

                            <div class="col-12 col-md-4">
                                <label for="tanggal_selesai" class="form-label fw-semibold">Tanggal Akhir</label>
                                <input type="date" class="form-control" id="tanggal_selesai" name="tanggal_selesai" value="<?= h($tanggalSelesai) ?>">
                            </div>

                            <div class="col-12 col-md-4">
                                <div class="d-flex flex-wrap gap-2">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="bi bi-funnel me-1"></i>Tampilkan
                                    </button>
                                    <a href="laporan.php" class="btn btn-light border">
                                        <i class="bi bi-arrow-counterclockwise me-1"></i>Reset
                                    </a>
                                </div>
                            </div>
                        </div>
                    </form>

                    <?php if ($tanggalMulai !== '' || $tanggalSelesai !== ''): ?>
                        <div class="alert alert-info border-0 rounded-3">
                            <i class="bi bi-calendar-range me-2"></i>
                            Menampilkan laporan
                            <?php if ($tanggalMulai !== ''): ?>
                                dari <strong><?= h(date('d-m-Y', strtotime($tanggalMulai))) ?></strong>
                            <?php endif; ?>
                            <?php if ($tanggalSelesai !== ''): ?>
                                sampai <strong><?= h(date('d-m-Y', strtotime($tanggalSelesai))) ?></strong>
                            <?php endif; ?>.
                        </div>
                    <?php endif; ?>

                    <div class="table-responsive">
                        <table id="tabelLaporan" class="table table-hover table-bordered align-middle w-100">
                            <thead class="table-dark">
                                <tr>
                                    <th class="text-center">No</th>
                                    <th>Tanggal</th>
                                    <th>NIS</th>
                                    <th>Nama Siswa</th>
                                    <th>Kelas</th>
                                    <th>Jenis Pelanggaran</th>
                                    <th class="text-center">Poin</th>
                                    <th>Keterangan</th>
                                    <th>Dicatat Oleh</th>

                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($laporan as $row): ?>
                                    <tr>
                                        <td class="text-center"></td>
                                        <td>
                                            <?= !empty($row['tanggal_pelanggaran'])
                                                ? h(date('d-m-Y', strtotime($row['tanggal_pelanggaran'])))
                                                : '-' ?>
                                        </td>
                                        <td><?= h($row['nis'] ?? '-') ?></td>
                                        <td class="fw-semibold"><?= h($row['nama_siswa'] ?? 'Siswa tidak ditemukan') ?></td>
                                        <td><?= h($row['nama_kelas'] ?? '-') ?></td>
                                        <td>
                                            <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-3 py-2">
                                                <?= h(!empty($row['kode'])
                                                    ? $row['kode'] . ' - ' . $row['nama_pelanggaran']
                                                    : $row['nama_pelanggaran']) ?>
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge text-bg-warning"><?= (int) ($row['poin'] ?? 0) ?> poin</span>
                                        </td>
                                        <td><?= h($row['keterangan'] ?? '-') ?></td>
                                        <td><?= h($row['dicatat_oleh'] ?? '-') ?></td>
                                        <td class="text-center">
                                            <a href="?id=<?= (int) $row['id'] ?>" class="" title="">
                                                
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <div class="text-secondary small mt-3">
                        Total data laporan: <strong><?= number_format($totalLaporan) ?> laporan</strong>
                    </div>
                </div>
            </div>

            <footer class="text-center text-secondary small py-4">
                &copy; 2026 SI-TIB — Sistem Informasi Tata Tertib
            </footer>
        </main>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.datatables.net/2.3.4/js/dataTables.min.js"></script>
<script src="https://cdn.datatables.net/2.3.4/js/dataTables.bootstrap5.min.js"></script>

<script>
$(function () {
    const tabel = $('#tabelLaporan').DataTable({
        pageLength: 10,
        lengthMenu: [5, 10, 25, 50, 100],
        order: [],
        scrollX: true,
        language: {
            search: 'Cari:',
            searchPlaceholder: 'Cari laporan...',
            lengthMenu: 'Tampilkan _MENU_ data',
            info: 'Menampilkan _START_ sampai _END_ dari _TOTAL_ data',
            infoEmpty: 'Tidak ada data',
            zeroRecords: 'Data tidak ditemukan',
            emptyTable: 'Belum ada data laporan pelanggaran',
            paginate: {
                first: 'Pertama',
                last: 'Terakhir',
                next: 'Berikutnya',
                previous: 'Sebelumnya'
            },
            infoFiltered: '(disaring dari _MAX_ data)'
        },
        columnDefs: [{
            targets: [0, 9],
            orderable: false,
            searchable: false
        }]
    });

    function perbaruiNomor() {
        const info = tabel.page.info();

        tabel.column(0, {
            page: 'current',
            search: 'applied',
            order: 'applied'
        }).nodes().each(function (cell, i) {
            cell.innerHTML = info.start + i + 1;
        });
    }

    tabel.on('draw.dt', perbaruiNomor);
    perbaruiNomor();
});
</script>
</body>
</html>