<?php
require_once __DIR__ . '/config/akses.php';
wajib_role(['admin', 'administrator']);

require_once __DIR__ . '/includes/cek_session.php';
require_once __DIR__ . '/config/koneksi.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

if (!function_exists('e')) {
    function e($nilai): string
    {
        return htmlspecialchars((string) ($nilai ?? ''), ENT_QUOTES, 'UTF-8');
    }
}

$sqlKategori = "
    SELECT k.id, k.nama, k.deskripsi, k.status_aktif, k.created_at, k.updated_at,
           COUNT(j.id) AS jumlah_jenis
    FROM t_pelanggaran_kategori k
    LEFT JOIN t_jenis_pelanggaran j ON j.kategori_id = k.id
    GROUP BY k.id, k.nama, k.deskripsi, k.status_aktif, k.created_at, k.updated_at
    ORDER BY k.id DESC
";

$hasilKategori = mysqli_query($koneksi, $sqlKategori);

$sqlJenis = "
    SELECT j.id, j.kategori_id, j.nama_pelanggaran, j.deskripsi, j.poin,
           j.status_aktif, j.created_at, k.nama AS nama_kategori
    FROM t_jenis_pelanggaran j
    INNER JOIN t_pelanggaran_kategori k ON k.id = j.kategori_id
    ORDER BY k.nama ASC, j.nama_pelanggaran ASC
";

$hasilJenis = mysqli_query($koneksi, $sqlJenis);

$totalKategori = 0;
$totalJenis = 0;
$kategoriAktif = 0;
$jenisAktif = 0;
$dataKategoriRingkasan = [];
$dataJenisRingkasan = [];

if ($hasilKategori) {
    while ($row = mysqli_fetch_assoc($hasilKategori)) {
        $dataKategoriRingkasan[] = $row;
        $totalKategori++;

        if ((int) $row['status_aktif'] === 1) {
            $kategoriAktif++;
        }
    }
    mysqli_data_seek($hasilKategori, 0);
}

if ($hasilJenis) {
    while ($row = mysqli_fetch_assoc($hasilJenis)) {
        $dataJenisRingkasan[] = $row;
        $totalJenis++;

        if ((int) $row['status_aktif'] === 1) {
            $jenisAktif++;
        }
    }
    mysqli_data_seek($hasilJenis, 0);
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Kategori Pelanggaran | SI-TIB</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/2.3.4/css/dataTables.bootstrap5.min.css" rel="stylesheet">
</head>

<body class="bg-light">
<div class="container-fluid px-0">
    <div class="row g-0">
        <?php include __DIR__ . '/layout/sidebar.php'; ?>

        <main class="col-12 col-md-9 col-lg-10 ms-md-auto p-3 p-lg-4">
            <div class="d-flex flex-column flex-xl-row justify-content-between align-items-xl-center gap-3 mb-4">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-3 bg-primary-subtle text-primary p-3">
                        <i class="bi bi-clipboard2-check fs-4"></i>
                    </div>
                    <div>
                        <h3 class="fw-bold mb-1">Master Pelanggaran</h3>
                        <p class="text-secondary mb-0">Kelola kategori, jenis pelanggaran, dan poin pelanggaran siswa.</p>
                    </div>
                </div>

                <div class="d-flex flex-wrap gap-2">
                    <a href="tambah_pelanggaran_kategori.php" class="btn btn-primary">
                        <i class="bi bi-plus-circle me-1"></i>Tambah Kategori
                    </a>
                    <a href="tambah_jenis_pelanggaran.php" class="btn btn-success">
                        <i class="bi bi-plus-circle me-1"></i>Tambah Jenis Pelanggaran
                    </a>
                </div>
            </div>

            <?php if (!empty($_SESSION['pesan_sukses'])): ?>
                <div class="alert alert-success alert-dismissible fade show rounded-3">
                    <i class="bi bi-check-circle-fill me-2"></i>
                    <?= e($_SESSION['pesan_sukses']) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php unset($_SESSION['pesan_sukses']); ?>
            <?php endif; ?>

            <?php if (!empty($_SESSION['pesan_error'])): ?>
                <div class="alert alert-danger alert-dismissible fade show rounded-3">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>
                    <?= e($_SESSION['pesan_error']) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php unset($_SESSION['pesan_error']); ?>
            <?php endif; ?>

            <div class="row g-3 mb-4">
                <div class="col-6 col-xl-3">
                    <div class="card border-0 shadow-sm rounded-4 h-100">
                        <div class="card-body">
                            <div class="text-secondary small mb-2">Total Kategori</div>
                            <div class="d-flex align-items-center justify-content-between">
                                <h3 class="fw-bold mb-0"><?= $totalKategori ?></h3>
                                <i class="bi bi-collection text-primary fs-3"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-6 col-xl-3">
                    <div class="card border-0 shadow-sm rounded-4 h-100">
                        <div class="card-body">
                            <div class="text-secondary small mb-2">Kategori Aktif</div>
                            <div class="d-flex align-items-center justify-content-between">
                                <h3 class="fw-bold mb-0"><?= $kategoriAktif ?></h3>
                                <i class="bi bi-check-circle text-success fs-3"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-6 col-xl-3">
                    <div class="card border-0 shadow-sm rounded-4 h-100">
                        <div class="card-body">
                            <div class="text-secondary small mb-2">Total Jenis Pelanggaran</div>
                            <div class="d-flex align-items-center justify-content-between">
                                <h3 class="fw-bold mb-0"><?= $totalJenis ?></h3>
                                <i class="bi bi-list-check text-warning fs-3"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-6 col-xl-3">
                    <div class="card border-0 shadow-sm rounded-4 h-100">
                        <div class="card-body">
                            <div class="text-secondary small mb-2">Jenis Aktif</div>
                            <div class="d-flex align-items-center justify-content-between">
                                <h3 class="fw-bold mb-0"><?= $jenisAktif ?></h3>
                                <i class="bi bi-shield-check text-success fs-3"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-header bg-white border-0 px-3 px-md-4 pt-4">
                    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2">
                        <div>
                            <h5 class="fw-bold mb-1">
                                <i class="bi bi-collection text-primary me-2"></i>Kategori Pelanggaran
                            </h5>
                            <p class="text-secondary small mb-0">Kelompok utama untuk mengelompokkan jenis pelanggaran.</p>
                        </div>
                        <a href="tambah_pelanggaran_kategori.php" class="btn btn-sm btn-outline-primary">
                            <i class="bi bi-plus-lg me-1"></i>Tambah Kategori
                        </a>
                    </div>
                </div>

                <div class="card-body p-3 p-md-4">
                    <?php if (!$hasilKategori): ?>
                        <div class="alert alert-danger mb-0">
                            Gagal mengambil data kategori: <?= e(mysqli_error($koneksi)) ?>
                        </div>
                    <?php elseif ($totalKategori === 0): ?>
                        <div class="text-center py-5">
                            <i class="bi bi-folder-x text-secondary display-4"></i>
                            <h6 class="fw-bold mt-3">Belum Ada Kategori</h6>
                            <p class="text-secondary small">Silakan tambahkan kategori pelanggaran terlebih dahulu.</p>
                            <a href="tambah_pelanggaran_kategori.php" class="btn btn-primary">
                                <i class="bi bi-plus-circle me-1"></i>Tambah Kategori
                            </a>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table id="tabelKategori" class="table table-hover table-striped table-bordered align-middle w-100">
                                <thead class="table-primary">
                                    <tr>
                                        <th class="text-center">No.</th>
                                        <th>Nama Kategori</th>
                                        <th>Deskripsi</th>
                                        <th class="text-center">Jumlah Jenis</th>
                                        <th class="text-center">Status</th>
                                        <th class="text-center">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($dataKategoriRingkasan as $row): ?>
                                        <tr>
                                            <td class="text-center"></td>
                                            <td class="fw-semibold"><?= e($row['nama']) ?></td>
                                            <td><?= nl2br(e($row['deskripsi'])) ?></td>
                                            <td class="text-center">
                                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle">
                                                    <?= (int) $row['jumlah_jenis'] ?> Jenis
                                                </span>
                                            </td>
                                            <td class="text-center">
                                                <?php if ((int) $row['status_aktif'] === 1): ?>
                                                    <span class="badge bg-success-subtle text-success border border-success-subtle">Aktif</span>
                                                <?php else: ?>
                                                    <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle">Tidak Aktif</span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-center text-nowrap">
                                                <div class="d-flex flex-wrap justify-content-center gap-2">
                                                    <a href="edit_pelanggaran_kategori.php?id=<?= (int) $row['id'] ?>" class="btn btn-sm btn-warning">
                                                        <i class="bi bi-pencil-square me-1"></i>Edit
                                                    </a>
                                                    <form action="hapus_pelanggaran_kategori.php" method="POST" class="d-inline" onsubmit="return confirm('Yakin ingin menghapus kategori ini?')">
                                                        <input type="hidden" name="id" value="<?= (int) $row['id'] ?>">
                                                        <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token']) ?>">
                                                        <button type="submit" class="btn btn-sm btn-danger">
                                                            <i class="bi bi-trash me-1"></i>Hapus
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-header bg-white border-0 px-3 px-md-4 pt-4">
                    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2">
                        <div>
                            <h5 class="fw-bold mb-1">
                                <i class="bi bi-list-check text-success me-2"></i>Jenis Pelanggaran
                            </h5>
                            <p class="text-secondary small mb-0">Daftar tindakan pelanggaran beserta kategori dan poinnya.</p>
                        </div>
                        <a href="tambah_jenis_pelanggaran.php" class="btn btn-sm btn-success">
                            <i class="bi bi-plus-lg me-1"></i>Tambah Jenis Pelanggaran
                        </a>
                    </div>
                </div>

                <div class="card-body p-3 p-md-4">
                    <?php if (!$hasilJenis): ?>
                        <div class="alert alert-danger mb-0">
                            Gagal mengambil data jenis pelanggaran: <?= e(mysqli_error($koneksi)) ?>
                        </div>
                    <?php elseif ($totalJenis === 0): ?>
                        <div class="text-center py-5">
                            <i class="bi bi-clipboard-x text-secondary display-4"></i>
                            <h6 class="fw-bold mt-3">Belum Ada Jenis Pelanggaran</h6>
                            <p class="text-secondary small">Tambahkan jenis pelanggaran ke kategori yang sudah tersedia.</p>
                            <a href="tambah_jenis_pelanggaran.php" class="btn btn-success">
                                <i class="bi bi-plus-circle me-1"></i>Tambah Jenis Pelanggaran
                            </a>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table id="tabelJenis" class="table table-hover table-striped table-bordered align-middle w-100">
                                <thead class="table-success">
                                    <tr>
                                        <th class="text-center">No.</th>
                                        <th>Kategori</th>
                                        <th>Nama Pelanggaran</th>
                                        <th>Deskripsi</th>
                                        <th class="text-center">Poin</th>
                                        <th class="text-center">Status</th>
                                        <th class="text-center">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($dataJenisRingkasan as $row): ?>
                                        <tr>
                                            <td class="text-center"></td>
                                            <td>
                                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle">
                                                    <?= e($row['nama_kategori']) ?>
                                                </span>
                                            </td>
                                            <td class="fw-semibold"><?= e($row['nama_pelanggaran']) ?></td>
                                            <td><?= nl2br(e($row['deskripsi'])) ?></td>
                                            <td class="text-center">
                                                <span class="badge bg-danger-subtle text-danger border border-danger-subtle">
                                                    <?= (int) $row['poin'] ?> Poin
                                                </span>
                                            </td>
                                            <td class="text-center">
                                                <?php if ((int) $row['status_aktif'] === 1): ?>
                                                    <span class="badge bg-success-subtle text-success border border-success-subtle">Aktif</span>
                                                <?php else: ?>
                                                    <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle">Tidak Aktif</span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-center text-nowrap">
                                                <div class="d-flex flex-wrap justify-content-center gap-2">
                                                    <a href="edit_jenis_pelanggaran.php?id=<?= (int) $row['id'] ?>" class="btn btn-sm btn-warning">
                                                        <i class="bi bi-pencil-square me-1"></i>Edit
                                                    </a>
                                                    <form action="hapus_jenis_pelanggaran.php" method="POST" class="d-inline" onsubmit="return confirm('Yakin ingin menghapus jenis pelanggaran ini?')">
                                                        <input type="hidden" name="id" value="<?= (int) $row['id'] ?>">
                                                        <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token']) ?>">
                                                        <button type="submit" class="btn btn-sm btn-danger">
                                                            <i class="bi bi-trash me-1"></i>Hapus
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <footer class="text-center text-secondary small mt-4 pb-3">
                SI-TIB — Sistem Informasi Tata Tertib &copy; 2026
            </footer>
        </main>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/2.3.4/js/dataTables.min.js"></script>
<script src="https://cdn.datatables.net/2.3.4/js/dataTables.bootstrap5.min.js"></script>

<script>
$(document).ready(function () {
    function aktifkanTabel(selector, placeholder) {
        const elemen = $(selector);

        if (!elemen.length) return;

        const tabel = elemen.DataTable({
            language: {
                search: 'Cari:',
                searchPlaceholder: placeholder,
                lengthMenu: 'Tampilkan _MENU_ data',
                info: 'Menampilkan _START_ sampai _END_ dari _TOTAL_ data',
                infoEmpty: 'Tidak ada data untuk ditampilkan',
                infoFiltered: '(difilter dari _MAX_ total data)',
                zeroRecords: 'Data yang dicari tidak ditemukan',
                emptyTable: 'Belum ada data',
                paginate: {
                    first: 'Pertama',
                    last: 'Terakhir',
                    next: 'Berikutnya',
                    previous: 'Sebelumnya'
                }
            },
            pageLength: 10,
            lengthMenu: [5, 10, 25, 50, 100],
            order: [],
            columnDefs: [
                {
                    targets: [0],
                    orderable: false,
                    searchable: false,
                    className: 'text-center'
                },
                {
                    targets: [-1],
                    orderable: false,
                    searchable: false
                }
            ]
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
    }

    aktifkanTabel('#tabelKategori', 'Cari kategori...');
    aktifkanTabel('#tabelJenis', 'Cari jenis pelanggaran...');
});
</script>
</body>
</html>