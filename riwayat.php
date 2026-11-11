
<?php
require_once __DIR__ . '/includes/cek_session.php';
require_once __DIR__ . '/config/koneksi.php';
require_once __DIR__ . '/config/akses.php';

wajib_role(['guru', 'wali_kelas', 'guru_wali_kelas']);

$namaPengguna = trim((string) ($_SESSION['nama'] ?? $_SESSION['name'] ?? $_SESSION['username'] ?? 'Pengguna'));
if ($namaPengguna === '') $namaPengguna = 'Pengguna';

if (!function_exists('h')) {
    function h($value): string {
        return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
    }
}

function tanggalValid($tanggal): bool {
    if ($tanggal === '') return true;
    $hasil = DateTime::createFromFormat('!Y-m-d', $tanggal);
    return $hasil !== false && $hasil->format('Y-m-d') === $tanggal;
}

$tanggalMulai = trim((string) ($_GET['mulai'] ?? ''));
$tanggalSelesai = trim((string) ($_GET['selesai'] ?? ''));
$kataKunci = trim((string) ($_GET['cari'] ?? ''));

$riwayat = [];
$errorDatabase = '';

try {
    if (!tanggalValid($tanggalMulai) || !tanggalValid($tanggalSelesai)) {
        throw new InvalidArgumentException('Format tanggal filter tidak valid.');
    }

    if ($tanggalMulai !== '' && $tanggalSelesai !== '' && $tanggalMulai > $tanggalSelesai) {
        throw new InvalidArgumentException('Tanggal mulai tidak boleh melebihi tanggal selesai.');
    }

    $sql = "
        SELECT
            ts.id AS tindakan_id,
            ts.jenis_tindakan,
            ts.keterangan AS keterangan_tindakan,
            ts.tanggal_tindakan,
            ts.dicatat_oleh,
            ts.status,
            cp.id AS catatan_id,
            cp.tanggal_pelanggaran,
            s.nis,
            s.nama AS nama_siswa,
            p.kode,
            COALESCE(jp.nama_pelanggaran, p.nama, 'Pelanggaran tidak ditemukan') AS nama_pelanggaran,
            COALESCE(jp.poin, p.poin, 0) AS poin
        FROM t_tindakan_siswa ts
        INNER JOIN t_catatan_pelanggaran cp ON cp.id = ts.catatan_pelanggaran_id
        LEFT JOIN t_siswa s ON s.id = cp.siswa_id
        LEFT JOIN t_jenis_pelanggaran jp ON jp.id = cp.jenis_pelanggaran_id
        LEFT JOIN t_pelanggaran p ON p.id = cp.pelanggaran_id
        WHERE UPPER(TRIM(ts.status)) = 'SELESAI'
        AND ts.dicatat_oleh = ?
    ";

    $tipe = 's';
    $parameter = [$namaPengguna];

    if ($kataKunci !== '') {
        $sql .= " AND (s.nama LIKE ? OR s.nis LIKE ? OR ts.jenis_tindakan LIKE ?)";
        $pola = '%' . $kataKunci . '%';
        $tipe .= 'sss';
        $parameter[] = $pola;
        $parameter[] = $pola;
        $parameter[] = $pola;
    }

    if ($tanggalMulai !== '') {
        $sql .= " AND ts.tanggal_tindakan >= ?";
        $tipe .= 's';
        $parameter[] = $tanggalMulai;
    }

    if ($tanggalSelesai !== '') {
        $sql .= " AND ts.tanggal_tindakan <= ?";
        $tipe .= 's';
        $parameter[] = $tanggalSelesai;
    }

    $sql .= " ORDER BY ts.tanggal_tindakan DESC, ts.id DESC";

    $stmt = mysqli_prepare($koneksi, $sql);
    if (!$stmt) throw new RuntimeException('Gagal menyiapkan query riwayat.');

    mysqli_stmt_bind_param($stmt, $tipe, ...$parameter);

    if (!mysqli_stmt_execute($stmt)) {
        throw new RuntimeException('Gagal mengambil riwayat tindakan.');
    }

    $hasil = mysqli_stmt_get_result($stmt);
    if (!$hasil) throw new RuntimeException('Gagal membaca hasil riwayat.');

    while ($item = mysqli_fetch_assoc($hasil)) $riwayat[] = $item;

    mysqli_free_result($hasil);
    mysqli_stmt_close($stmt);

} catch (InvalidArgumentException $e) {
    $errorDatabase = $e->getMessage();
} catch (Throwable $e) {
    error_log('Riwayat SI-TIB: ' . $e->getMessage());
    $errorDatabase = 'Data riwayat gagal dimuat. Periksa struktur tabel database.';
}

$totalData = count($riwayat);
$totalPoin = array_sum(array_column($riwayat, 'poin'));
?>

<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Riwayat | SI-TIB</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/2.3.4/css/dataTables.bootstrap5.min.css" rel="stylesheet">
</head>

<body class="bg-light">
<div class="container-fluid">
    <div class="row">
        <?php include __DIR__ . '/layout/sidebar.php'; ?>

        <main class="col-12 col-md-9 col-lg-10 ms-md-auto px-3 px-lg-4 py-4">

            <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
                <div>
                    <h3 class="fw-bold text-dark mb-1">
                        <i class="bi bi-clock-history text-primary me-2"></i>
                        Riwayat SI-TIB
                    </h3>
                    <p class="text-secondary mb-0">
                        Riwayat tindakan pelanggaran siswa yang sudah selesai ditindaklanjuti.
                    </p>
                </div>

                <div class="bg-white rounded-4 shadow-sm px-3 py-2">
                    <div class="d-flex align-items-center gap-2">
                        <div class="rounded-circle bg-primary-subtle text-primary p-2">
                            <i class="bi bi-person-circle fs-5"></i>
                        </div>
                        <div>
                            <div class="small text-secondary">Login Sebagai</div>
                            <div class="fw-semibold text-dark"><?= h($namaPengguna) ?></div>
                        </div>
                    </div>
                </div>
            </div>

            <?php if ($errorDatabase !== ''): ?>
                <div class="alert alert-warning rounded-3 border-0 shadow-sm">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>
                    <?= h($errorDatabase) ?>
                </div>
            <?php endif; ?>

            <div class="row g-3 mb-4">
                <div class="col-12 col-md-6">
                    <div class="card border-0 shadow-sm rounded-4 h-100">
                        <div class="card-body p-4">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <div class="text-secondary small mb-2">Jumlah Tindakan Selesai</div>
                                    <h3 class="fw-bold text-dark mb-0"><?= number_format($totalData, 0, ',', '.') ?></h3>
                                    <div class="text-secondary small mt-1">Total tindakan selesai milik akun Anda</div>
                                </div>
                                <div class="rounded-4 bg-success-subtle text-success p-3">
                                    <i class="bi bi-check-circle fs-2"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-md-6">
                    <div class="card border-0 shadow-sm rounded-4 h-100">
                        <div class="card-body p-4">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <div class="text-secondary small mb-2">Total Poin Pelanggaran</div>
                                    <h3 class="fw-bold text-dark mb-0"><?= number_format($totalPoin, 0, ',', '.') ?></h3>
                                    <div class="text-secondary small mt-1">Akumulasi poin dari tindakan selesai</div>
                                </div>
                                <div class="rounded-4 bg-warning-subtle text-warning-emphasis p-3">
                                    <i class="bi bi-award fs-2"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-header bg-white border-0 px-4 pt-4">
                    <div class="d-flex align-items-center gap-3">
                        <div class="rounded-3 bg-primary-subtle text-primary p-2">
                            <i class="bi bi-funnel fs-5"></i>
                        </div>
                        <div>
                            <h5 class="fw-bold mb-1">Filter Riwayat</h5>
                            <p class="text-secondary small mb-0">
                                Cari berdasarkan rentang tanggal tindakan.
                            </p>
                        </div>
                    </div>
                </div>

                <div class="card-body p-4">
                    <form method="get" action="riwayat.php">
                        <div class="row g-3">
                            <div class="col-lg-3 col-md-6">
                                <label for="mulai" class="form-label fw-semibold">Tanggal Mulai</label>
                                <input type="date" name="mulai" id="mulai" class="form-control" value="<?= h($tanggalMulai) ?>">
                            </div>

                            <div class="col-lg-3 col-md-6">
                                <label for="selesai" class="form-label fw-semibold">Tanggal Selesai</label>
                                <input type="date" name="selesai" id="selesai" class="form-control" value="<?= h($tanggalSelesai) ?>">
                            </div>

                            <div class="col-lg-2 d-flex align-items-end gap-2">
                                <button type="submit" class="btn btn-primary flex-grow-1">
                                    <i class="bi bi-search me-1"></i>
                                    Filter
                                </button>
                                <a href="riwayat.php" class="btn btn-light border" title="Reset filter">
                                    <i class="bi bi-arrow-counterclockwise"></i>
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-header bg-white border-0 px-4 pt-4">
                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
                        <div class="d-flex align-items-center gap-3">
                            <div class="rounded-3 bg-success-subtle text-success p-2">
                                <i class="bi bi-list-check fs-5"></i>
                            </div>
                            <div>
                                <h5 class="fw-bold mb-1">Daftar Riwayat Tindakan</h5>
                                <p class="text-secondary small mb-0">Hanya tindakan dengan status SELESAI.</p>
                            </div>
                        </div>
                        <span class="badge text-bg-success rounded-pill px-3 py-2">
                            <?= $totalData ?> Data Selesai
                        </span>
                    </div>
                </div>

                <div class="card-body p-4">
                    <div class="table-responsive">
                        <table id="tabelRiwayat" class="table table-hover align-middle w-100">
                            <thead class="table-light">
                                <tr>
                                    <th>No.</th>
                                    <th>Tanggal Tindakan</th>
                                    <th>NIS</th>
                                    <th>Nama Siswa</th>
                                    <th>Pelanggaran</th>
                                    <th>Poin</th>
                                    <th>Jenis Tindakan</th>
                                    <th>Keterangan Tindakan</th>
                                    <th>Dicatat Oleh</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($riwayat as $r): ?>
                                    <tr>
                                        <td></td>
                                        <td><?= h($r['tanggal_tindakan'] ?? '-') ?></td>
                                        <td><?= h($r['nis'] ?? '-') ?></td>
                                        <td class="fw-semibold"><?= h($r['nama_siswa'] ?? 'Siswa tidak ditemukan') ?></td>
                                        <td><?= h($r['nama_pelanggaran'] ?? '-') ?></td>
                                        <td>
                                            <span class="badge text-bg-warning rounded-pill px-3 py-2">
                                                <?= (int) ($r['poin'] ?? 0) ?> poin
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge text-bg-primary">
                                                <?= h($r['jenis_tindakan'] ?: 'Belum ditentukan') ?>
                                            </span>
                                        </td>
                                        <td><?= nl2br(h($r['keterangan_tindakan'] ?? '-')) ?></td>
                                        <td><?= h($r['dicatat_oleh'] ?? '-') ?></td>
                                        <td>
                                            <span class="badge text-bg-success rounded-pill px-3 py-2">
                                                <i class="bi bi-check-circle me-1"></i>
                                                SELESAI
                                            </span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <?php if ($totalData === 0 && $errorDatabase === ''): ?>
                        <div class="text-center py-4">
                            <div class="text-secondary mb-2">
                                <i class="bi bi-inbox display-5"></i>
                            </div>
                            <h6 class="fw-bold">Belum Ada Riwayat Tindakan Selesai</h6>
                            <p class="text-secondary small mb-0">
                                Tindakan akan muncul di halaman ini setelah statusnya diubah menjadi SELESAI.
                            </p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <footer class="text-center text-secondary small py-4">
                &copy; <?= date('Y') ?> SI-TIB — Sistem Informasi Tata Tertib
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
    const tabel = $('#tabelRiwayat').DataTable({
        pageLength: 10,
        lengthMenu: [5, 10, 25, 50, 100],
        order: [],
        scrollX: true,
        language: {
            search: 'Cari:',
            searchPlaceholder: 'Cari riwayat tindakan...',
            lengthMenu: 'Tampilkan _MENU_ data',
            info: 'Menampilkan _START_ sampai _END_ dari _TOTAL_ data',
            infoEmpty: 'Tidak ada data',
            zeroRecords: 'Data tidak ditemukan',
            emptyTable: 'Belum ada riwayat tindakan selesai',
            paginate: {
                first: 'Pertama',
                last: 'Terakhir',
                next: 'Berikutnya',
                previous: 'Sebelumnya'
            },
            infoFiltered: '(disaring dari _MAX_ data)'
        },
        columnDefs: [{
            targets: 0,
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
            cell.textContent = info.start + i + 1;
        });
    }

    tabel.on('draw.dt', perbaruiNomor);
    perbaruiNomor();
});
</script>
</body>
</html>