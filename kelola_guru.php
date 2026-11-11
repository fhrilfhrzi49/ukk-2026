<?php
require_once __DIR__ . '/config/akses.php';
wajib_role(['admin', 'administrator']);

require_once __DIR__ . '/includes/cek_session.php';
require_once __DIR__ . '/config/koneksi.php';

$hasil = mysqli_query($koneksi, "SELECT id, nip, nama, email, status_aktif FROM t_guru ORDER BY nama ASC");
$errorDatabase = !$hasil;
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Kelola Guru | SI-TIB</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/2.3.4/css/dataTables.bootstrap5.min.css" rel="stylesheet">
</head>

<body class="bg-light">
<div class="container-fluid">
    <div class="row">
        <?php include __DIR__ . '/layout/sidebar.php'; ?>

        <main class="col-md-9 col-lg-10 ms-md-auto p-3 p-lg-4">

            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
                <div>
                    <h2 class="fw-bold mb-1">
                        <i class="bi bi-person-badge-fill text-primary"></i>
                        Kelola Guru
                    </h2>
                    <p class="text-secondary mb-0">Kelola data guru dalam sistem pelanggaran siswa.</p>
                </div>

                <a href="tambah_guru.php" class="btn btn-primary">
                    <i class="bi bi-person-plus-fill me-1"></i>
                    Tambah Guru
                </a>
            </div>

            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-header bg-white border-0 p-3">
                    <h5 class="fw-semibold mb-0">
                        <i class="bi bi-table text-primary me-2"></i>
                        Daftar Guru
                    </h5>
                </div>

                <div class="card-body">
                    <?php if ($errorDatabase): ?>
                        <div class="alert alert-danger mb-0">
                            <i class="bi bi-exclamation-triangle-fill me-2"></i>
                            Data guru gagal dimuat. Periksa koneksi database dan tabel <code>t_guru</code>.
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table id="tabelGuru" class="table table-striped table-hover table-bordered align-middle w-100">
                                <thead class="table-dark">
                                    <tr>
                                        <th scope="col" class="text-center">No.</th>
                                        <th scope="col" class="text-start text-nowrap">NIP</th>
                                        <th scope="col">Nama Guru</th>
                                        <th scope="col">Email</th>
                                        <th scope="col">Status Aktif</th>
                                        <th scope="col">Aksi</th>
                                    </tr>
                                </thead>

                                <tbody>
                                    <?php while ($row = mysqli_fetch_assoc($hasil)): ?>
                                        <tr>
                                            <td class="text-center"></td>
                                            <td class="text-start text-nowrap px-3">
                                                <span class="d-inline-block">
                                                    <?= htmlspecialchars((string) ($row['nip'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>
                                                </span>
                                            </td>
                                            <td class="fw-semibold">
                                                <?= htmlspecialchars((string) ($row['nama'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>
                                            </td>
                                            <td>
                                                <?= htmlspecialchars((string) ($row['email'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>
                                            </td>
                                            <td>
                                                <?php if ((int) ($row['status_aktif'] ?? 0) === 1): ?>
                                                    <span class="badge text-bg-success">
                                                        <i class="bi bi-check-circle me-1"></i>
                                                        Aktif
                                                    </span>
                                                <?php else: ?>
                                                    <span class="badge text-bg-secondary">Tidak Aktif</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <div class="d-flex flex-wrap gap-1">
                                                    <a href="edit_guru.php?id=<?= rawurlencode((string) $row['id']); ?>" class="btn btn-sm btn-warning text-nowrap">
                                                        <i class="bi bi-pencil-square"></i>
                                                        Edit
                                                    </a>

                                                    <a href="hapus_guru.php?id=<?= rawurlencode((string) $row['id']); ?>"
                                                       class="btn btn-sm btn-danger text-nowrap"
                                                       onclick="return confirm('Yakin ingin menghapus data guru ini?');">
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

                        <?php mysqli_free_result($hasil); ?>
                    <?php endif; ?>
                </div>
            </div>

            <footer class="text-center text-secondary small mt-4 pb-3">
                © 2026 SI-TIB — Sistem Informasi Tata Tertib
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
    const selector = '#tabelGuru';

    if (!$(selector).length || !$.fn.DataTable) {
        console.error('Tabel guru atau library DataTables tidak tersedia.');
        return;
    }

    if ($.fn.DataTable.isDataTable(selector)) return;

    $(selector).addClass('d-none');

    $(selector).DataTable({
        paging: true,
        pagingType: 'full_numbers',
        pageLength: 10,
        lengthMenu: [5, 10, 25, 50, 100],
        searching: true,
        ordering: true,
        info: true,
        autoWidth: false,
        order: [],
        columnDefs: [
            {
                targets: 0,
                className: 'text-center',
                width: '55px',
                orderable: false,
                searchable: false
            },
            {
                targets: 1,
                className: 'text-start text-nowrap px-3',
                width: '190px'
            },
            {
                targets: 5,
                orderable: false,
                searchable: false
            }
        ],
        language: {
            emptyTable: 'Belum ada data guru',
            zeroRecords: 'Data guru tidak ditemukan',
            search: 'Cari:',
            searchPlaceholder: 'Cari nama, NIP, atau email...',
            lengthMenu: 'Tampilkan _MENU_ data',
            info: 'Menampilkan _START_ sampai _END_ dari _TOTAL_ data',
            infoEmpty: 'Menampilkan 0 sampai 0 dari 0 data',
            infoFiltered: '(difilter dari _MAX_ total data)',
            paginate: {
                first: 'Pertama',
                last: 'Terakhir',
                next: 'Berikutnya',
                previous: 'Sebelumnya'
            }
        },
        drawCallback: function () {
            const api = this.api();
            const info = api.page.info();

            api.column(0, {
                page: 'current',
                search: 'applied',
                order: 'applied'
            }).nodes().each(function (cell, index) {
                cell.textContent = info.start + index + 1;
            });
        },
        initComplete: function () {
            $(selector).removeClass('d-none');
            this.api().columns.adjust();
        }
    });
});
</script>
</body>
</html>