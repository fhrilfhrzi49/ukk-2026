<?php
require_once __DIR__ . '/config/akses.php';
wajib_role(['admin', 'administrator']);

require_once __DIR__ . '/includes/cek_session.php';
require_once __DIR__ . '/config/koneksi.php';

$sql = "SELECT * FROM t_kelas ORDER BY nama ASC";
$hasil = mysqli_query($koneksi, $sql);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Kelas | SI-TIB</title>
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
                        <i class="bi bi-building text-primary"></i>
                        Kelola Kelas
                    </h2>
                    <p class="text-secondary mb-0">Kelola data kelas dalam sistem pelanggaran siswa.</p>
                </div>

                <a href="tambah_kelas.php" class="btn btn-primary">
                    <i class="bi bi-plus-circle me-1"></i>
                    Tambah Kelas
                </a>
            </div>

            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-header bg-white border-0 py-3">
                    <h5 class="fw-semibold mb-0">
                        <i class="bi bi-table text-primary me-2"></i>
                        Daftar Kelas
                    </h5>
                </div>

                <div class="card-body">
                    <?php if (!$hasil): ?>
                        <div class="alert alert-danger mb-0">
                            <i class="bi bi-exclamation-triangle-fill me-2"></i>
                            Gagal mengambil data kelas.
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table id="tabelKelas" class="table table-striped table-hover table-bordered align-middle w-100">
                                <thead class="table-dark">
                                    <tr>
                                        <th>No.</th>
                                        <th>Nama Kelas</th>
                                        <th>Tingkat</th>
                                        <th>Jurusan</th>
                                        <th>Status Aktif</th>
                                        <th>Dibuat</th>
                                        <th>Diubah</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($row = mysqli_fetch_assoc($hasil)): ?>
                                        <tr>
                                            <td></td>
                                            <td class="fw-semibold"><?= htmlspecialchars((string)($row['nama'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></td>
                                            <td><?= htmlspecialchars((string)($row['tingkat'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></td>
                                            <td><?= htmlspecialchars((string)($row['jurusan'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></td>
                                            <td>
                                                <?php if ((int)($row['status_aktif'] ?? 0) === 1): ?>
                                                    <span class="badge text-bg-success">
                                                        <i class="bi bi-check-circle me-1"></i>
                                                        Aktif
                                                    </span>
                                                <?php else: ?>
                                                    <span class="badge text-bg-secondary">Tidak Aktif</span>
                                                <?php endif; ?>
                                            </td>
                                            <td><?= htmlspecialchars((string)($row['created_at'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></td>
                                            <td><?= htmlspecialchars((string)($row['updated_at'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></td>
                                            <td>
                                                <div class="d-flex flex-wrap gap-1">
                                                    <a href="edit_kelas.php?id=<?= urlencode((string)($row['id'] ?? '')); ?>" class="btn btn-sm btn-warning">
                                                        <i class="bi bi-pencil-square"></i>
                                                        Edit
                                                    </a>
                                                    <a href="hapus_kelas.php?id=<?= urlencode((string)($row['id'] ?? '')); ?>" class="btn btn-sm btn-danger" onclick="return confirm('Yakin ingin menghapus data kelas ini?');">
                                                        <i class="bi bi-trash"></i>
                                                        Hapus
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
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
    const tabel = $('#tabelKelas').DataTable({
        pageLength: 10,
        lengthMenu: [5, 10, 25, 50, 100],
        order: [],
        columnDefs: [
            {
                targets: [0, 7],
                orderable: false,
                searchable: false
            }
        ],
        language: {
            search: "Cari:",
            lengthMenu: "Tampilkan _MENU_ data",
            info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
            infoEmpty: "Tidak ada data kelas",
            infoFiltered: "(difilter dari _MAX_ total data)",
            zeroRecords: "Data kelas tidak ditemukan",
            emptyTable: "Belum ada data kelas",
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
            cell.innerHTML = info.start + i + 1;
        });
    }

    tabel.on('draw.dt', perbaruiNomor);
    perbaruiNomor();
});
</script>
</body>
</html>