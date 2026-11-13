<?php
require_once __DIR__ . '/config/akses.php';
wajib_role(['admin', 'administrator']);

require_once __DIR__ . '/includes/cek_session.php';
require_once __DIR__ . '/config/koneksi.php';

$query = mysqli_query($koneksi, "
    SELECT
        ks.id,
        s.nis,
        s.nama AS nama_siswa,
        ta.nama AS tahun_ajaran,
        k.nama AS nama_kelas,
        k.tingkat,
        k.jurusan,
        ks.tanggal_mulai,
        ks.tanggal_selesai,
        ks.status_aktif
    FROM t_kelas_siswa ks
    JOIN t_siswa s ON ks.siswa_id = s.id
    JOIN t_kelas k ON ks.kelas_id = k.id
    JOIN t_tahun_ajaran ta ON ks.tahun_ajaran_id = ta.id
    ORDER BY ks.id DESC
");

$hasil = mysqli_query($koneksi, "SELECT * FROM t_siswa ORDER BY nama ASC");
?>


<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Siswa | SI-TIB</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/2.3.4/css/dataTables.bootstrap5.min.css" rel="stylesheet">

    <style>
        .page-content {
            min-width: 0;
            box-sizing: border-box;
        }

        @media (min-width: 768px) {
            .page-content {
                margin-left: 25%;
                width: 75%;
                max-width: 75%;
            }
        }

        @media (min-width: 992px) {
            .page-content {
                margin-left: 16.666667%;
                width: 83.333333%;
                max-width: 83.333333%;
            }
        }

        @media (max-width: 767.98px) {
            .page-content {
                width: 100%;
                max-width: 100%;
            }
        }

        .table-responsive {
            min-width: 0;
        }
    </style>
</head>

<body class="bg-light">
<div class="container-fluid">
    <div class="row">

        <?php include __DIR__ . '/layout/sidebar.php'; ?>

        <main class="page-content col-12 p-3 p-lg-4">

            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
                <div>
                    <h2 class="fw-bold mb-1">
                        <i class="bi bi-people-fill text-primary"></i>
                        Kelola Siswa
                    </h2>
                    <p class="text-secondary mb-0">Kelola dan pantau data siswa dalam sistem.</p>
                </div>

                <a href="tambah_siswa.php" class="btn btn-primary">
                    <i class="bi bi-person-plus-fill me-1"></i>
                    Tambah Siswa
                </a>
            </div>

            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-header bg-white border-0 py-3">
                    <h5 class="fw-semibold mb-0">
                        <i class="bi bi-table text-primary me-2"></i>
                        Daftar Siswa
                    </h5>
                </div>

                <div class="card-body">
                    <?php if (!$hasil): ?>
                        <div class="alert alert-danger mb-0">
                            <i class="bi bi-exclamation-triangle-fill me-2"></i>
                            Gagal mengambil data siswa.
                            Periksa koneksi database dan tabel <code>t_siswa</code>.
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table id="tabelSiswa" class="table table-striped table-hover table-bordered align-middle w-100">
                                <thead class="table-dark">
                                    <tr>
                                        <th>No.</th>
                                        <th>NIS</th>
                                        <th>NISN</th>
                                        <th>Nama</th>
                                        <th>Jenis Kelamin</th>
                                        <th>Tanggal Lahir</th>
                                        <th>Alamat</th>
                                        <th>Status Aktif</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>

                                <tbody>
                                    <?php while ($row = mysqli_fetch_assoc($hasil)): ?>
                                        <tr>
                                            <td></td>
                                            <td><?= htmlspecialchars((string) ($row['nis'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></td>
                                            <td><?= htmlspecialchars((string) ($row['nisn'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></td>
                                            <td class="fw-semibold"><?= htmlspecialchars((string) ($row['nama'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></td>
                                            <td><?= htmlspecialchars((string) ($row['jenis_kelamin'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></td>
                                            <td><?= htmlspecialchars((string) ($row['tanggal_lahir'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></td>
                                            <td><?= htmlspecialchars((string) ($row['alamat'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></td>

                                            <td>
                                                <?php if ((int) ($row['status_aktif'] ?? 0) === 1): ?>
                                                    <span class="badge text-bg-success">
                                                        <i class="bi bi-check-circle me-1"></i>Aktif
                                                    </span>
                                                <?php else: ?>
                                                    <span class="badge text-bg-secondary">Tidak Aktif</span>
                                                <?php endif; ?>
                                            </td>

                                            <td>
                                                <div class="d-flex flex-wrap gap-1">
                                                    <a href="edit_siswa.php?id=<?= rawurlencode((string) ($row['id'] ?? '')); ?>" class="btn btn-sm btn-warning">
                                                        <i class="bi bi-pencil-square"></i> Edit
                                                    </a>

                                                    <a href="hapus_siswa.php?id=<?= rawurlencode((string) ($row['id'] ?? '')); ?>"
                                                       class="btn btn-sm btn-danger"
                                                       onclick="return confirm('Apakah Anda yakin ingin menghapus data siswa ini?');">
                                                        <i class="bi bi-trash"></i> Hapus
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
    const selector = '#tabelSiswa';

    if (!$(selector).length || !$.fn.DataTable) return;

    $(selector).DataTable({
        pageLength: 10,
        lengthMenu: [5, 10, 25, 50, 100],
        order: [],
        autoWidth: false,
        language: {
            search: "Cari:",
            lengthMenu: "Tampilkan _MENU_ data",
            info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
            infoEmpty: "Tidak ada data siswa",
            infoFiltered: "(difilter dari _MAX_ total data)",
            zeroRecords: "Data siswa tidak ditemukan",
            emptyTable: "Belum ada data siswa",
            paginate: {
                first: "Pertama",
                last: "Terakhir",
                next: "Berikutnya",
                previous: "Sebelumnya"
            }
        },
        columnDefs: [
            {
                targets: [0, 8],
                orderable: false,
                searchable: false
            }
        ],
        drawCallback: function () {
            const api = this.api();
            const info = api.page.info();

            api.column(0, {
                page: 'current',
                search: 'applied',
                order: 'applied'
            }).nodes().each(function (cell, i) {
                cell.textContent = info.start + i + 1;
            });
        }
    });
});
</script>
</body>
</html>