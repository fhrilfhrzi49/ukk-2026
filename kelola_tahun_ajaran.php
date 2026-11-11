<?php
require_once __DIR__ . '/config/akses.php';
wajib_role(['admin', 'administrator']);

require_once __DIR__ . '/includes/cek_session.php';
require_once __DIR__ . '/config/koneksi.php';

$sql = "SELECT * FROM t_tahun_ajaran ORDER BY tanggal_mulai DESC";
$hasil = mysqli_query($koneksi, $sql);
$error_query = !$hasil;

$pesan = $_GET['pesan'] ?? '';

$daftarPesan = [
    'tahun_aktif' => [
        'type' => 'danger',
        'text' => 'Tidak dapat mengaktifkan tahun ajaran baru karena masih ada tahun ajaran lain yang aktif. Nonaktifkan tahun ajaran sebelumnya terlebih dahulu.'
    ],
    'berhasil_tambah' => [
        'type' => 'success',
        'text' => 'Tahun ajaran berhasil ditambahkan.'
    ],
    'berhasil_edit' => [
        'type' => 'success',
        'text' => 'Tahun ajaran berhasil diperbarui.'
    ],
    'gagal' => [
        'type' => 'danger',
        'text' => 'Proses tidak berhasil. Periksa kembali data yang dimasukkan.'
    ]
];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Tahun Ajaran | SI-TIB</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/2.3.4/css/dataTables.bootstrap5.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container-fluid">
    <div class="row">
        <?php include __DIR__ . '/layout/sidebar.php'; ?>

        <main class="col-12 col-md-9 col-lg-10 ms-auto p-3 p-lg-4">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
                <div>
                    <h2 class="fw-bold mb-1">
                        <i class="bi bi-calendar-range-fill text-primary"></i>
                        Kelola Tahun Ajaran
                    </h2>
                    <p class="text-secondary mb-0">Kelola data tahun ajaran dalam sistem pelanggaran siswa.</p>
                </div>

                <div>
                    <a href="tambah_tahun_ajaran.php" class="btn btn-primary">
                        <i class="bi bi-plus-circle-fill"></i>
                        Tambah Tahun Ajaran
                    </a>
                </div>
            </div>

            <?php if (isset($daftarPesan[$pesan])): ?>
                <div class="alert alert-<?= htmlspecialchars($daftarPesan[$pesan]['type'], ENT_QUOTES, 'UTF-8'); ?> alert-dismissible fade show rounded-3 shadow-sm border-0 mb-4" role="alert">
                    <div class="d-flex align-items-start gap-3">
                        <div class="fs-3">
                            <?php if ($pesan === 'tahun_aktif'): ?>
                                <i class="bi bi-exclamation-octagon-fill"></i>
                            <?php elseif ($pesan === 'gagal'): ?>
                                <i class="bi bi-x-circle-fill"></i>
                            <?php else: ?>
                                <i class="bi bi-check-circle-fill"></i>
                            <?php endif; ?>
                        </div>

                        <div class="flex-grow-1 pe-3">
                            <?php if ($pesan === 'tahun_aktif'): ?>
                                <h5 class="fw-bold mb-2">Gagal Mengaktifkan Tahun Ajaran!</h5>
                                <p class="mb-0 fw-semibold"><?= htmlspecialchars($daftarPesan[$pesan]['text'], ENT_QUOTES, 'UTF-8'); ?></p>
                            <?php elseif ($pesan === 'berhasil_tambah' || $pesan === 'berhasil_edit'): ?>
                                <h6 class="fw-bold mb-1">Berhasil!</h6>
                                <p class="mb-0"><?= htmlspecialchars($daftarPesan[$pesan]['text'], ENT_QUOTES, 'UTF-8'); ?></p>
                            <?php else: ?>
                                <h6 class="fw-bold mb-1">Proses Gagal!</h6>
                                <p class="mb-0"><?= htmlspecialchars($daftarPesan[$pesan]['text'], ENT_QUOTES, 'UTF-8'); ?></p>
                            <?php endif; ?>
                        </div>
                    </div>

                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button>
                </div>
            <?php endif; ?>

            <?php if ($error_query): ?>
                <div class="alert alert-danger rounded-3">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>
                    Gagal mengambil data tahun ajaran.
                    Periksa tabel <code>t_tahun_ajaran</code> dan koneksi database.
                </div>
            <?php endif; ?>

            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-header bg-white border-0 rounded-top-4 py-3">
                    <h5 class="fw-semibold mb-0">
                        <i class="bi bi-table text-primary"></i>
                        Daftar Tahun Ajaran
                    </h5>
                </div>

                <div class="card-body">
                    <div class="table-responsive">
                        <table id="tabelTahunAjaran" class="table table-striped table-hover table-bordered align-middle w-100">
                            <thead class="table-dark">
                                <tr>
                                    <th>No</th>
                                    <th>Tahun Ajaran</th>
                                    <th>Tanggal Mulai</th>
                                    <th>Tanggal Selesai</th>
                                    <th>Status Aktif</th>
                                    <th>Dibuat</th>
                                    <th>Diubah</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($hasil && mysqli_num_rows($hasil) > 0): ?>
                                    <?php while ($row = mysqli_fetch_assoc($hasil)): ?>
                                        <tr>
                                            <td></td>
                                            <td><?= htmlspecialchars((string)($row['nama'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></td>
                                            <td><?= htmlspecialchars((string)($row['tanggal_mulai'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></td>
                                            <td><?= htmlspecialchars((string)($row['tanggal_selesai'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></td>
                                            <td>
                                                <?php if ((int)($row['status_aktif'] ?? 0) === 1): ?>
                                                    <span class="badge text-bg-success">
                                                        <i class="bi bi-check-circle-fill"></i>
                                                        Aktif
                                                    </span>
                                                <?php else: ?>
                                                    <span class="badge text-bg-secondary">
                                                        <i class="bi bi-x-circle-fill"></i>
                                                        Tidak Aktif
                                                    </span>
                                                <?php endif; ?>
                                            </td>
                                            <td><?= htmlspecialchars((string)($row['created_at'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></td>
                                            <td><?= htmlspecialchars((string)($row['updated_at'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></td>
                                            <td>
                                                <div class="d-flex flex-wrap gap-1">
                                                    <a href="edit_tahun_ajaran.php?id=<?= rawurlencode((string)($row['id'] ?? '')); ?>" class="btn btn-sm btn-warning">
                                                        <i class="bi bi-pencil-square"></i>
                                                        Edit
                                                    </a>
                                                    <a href="hapus_tahun_ajaran.php?id=<?= rawurlencode((string)($row['id'] ?? '')); ?>" class="btn btn-sm btn-danger" onclick="return confirm('Yakin ingin menghapus data tahun ajaran ini?');">
                                                        <i class="bi bi-trash"></i>
                                                        Hapus
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <footer class="text-center text-secondary small mt-4">
                Sistem Pelanggaran Siswa &copy; 2026
            </footer>
        </main>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.datatables.net/2.3.4/js/dataTables.min.js"></script>
<script src="https://cdn.datatables.net/2.3.4/js/dataTables.bootstrap5.min.js"></script>
<script>
$(document).ready(function () {
    const tabel = $('#tabelTahunAjaran').DataTable({
        pageLength: 10,
        lengthMenu: [5, 10, 25, 50, 100],
        order: [],
        columnDefs: [
            {
                targets: 0,
                orderable: false,
                searchable: false
            },
            {
                targets: 7,
                orderable: false,
                searchable: false
            }
        ],
        language: {
            search: "Cari:",
            lengthMenu: "Tampilkan _MENU_ data",
            info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
            infoEmpty: "Tidak ada data tahun ajaran",
            infoFiltered: "(difilter dari _MAX_ total data)",
            zeroRecords: "Data tahun ajaran tidak ditemukan",
            emptyTable: "Belum ada data tahun ajaran",
            paginate: {
                first: "Pertama",
                last: "Terakhir",
                next: "Berikutnya",
                previous: "Sebelumnya"
            }
        }
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

    const url = new URL(window.location.href);

    if (url.searchParams.has('pesan')) {
        url.searchParams.delete('pesan');

        const query = url.searchParams.toString();
        const alamatBaru = url.pathname + (query ? '?' + query : '') + url.hash;

        window.history.replaceState({}, document.title, alamatBaru);
    }
});
</script>
</body>
</html>