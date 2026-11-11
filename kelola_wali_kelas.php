<?php
require_once __DIR__ . '/config/akses.php';
wajib_role(['admin']);

require_once __DIR__ . '/includes/cek_session.php';
require_once __DIR__ . '/config/koneksi.php';

$sql = "
    SELECT
        w.id,
        w.user_id,
        w.guru_id,
        w.tahun_ajaran_id,
        w.kelas_id,
        w.tanggal_mulai,
        w.tanggal_selesai,
        w.status_aktif,
        g.nip,
        CASE
            WHEN w.guru_id IS NOT NULL THEN COALESCE(NULLIF(g.nama, ''), u.name)
            ELSE u.name
        END AS nama_wali,
        u.role,
        k.nama AS nama_kelas,
        k.tingkat,
        k.jurusan,
        ta.nama AS nama_tahun_ajaran
    FROM t_wali_kelas w
    LEFT JOIN t_guru g ON w.guru_id = g.id
    LEFT JOIN t_users u ON w.user_id = u.id
    LEFT JOIN t_kelas k ON w.kelas_id = k.id
    LEFT JOIN t_tahun_ajaran ta ON w.tahun_ajaran_id = ta.id
    ORDER BY w.id DESC
";

$hasil = mysqli_query($koneksi, $sql);
$errorDatabase = !$hasil;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Kelola Wali Kelas | SI-TIB</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/2.3.4/css/dataTables.bootstrap5.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container-fluid">
    <div class="row">
        <?php include __DIR__ . '/layout/sidebar.php'; ?>

        <main class="col-12 col-md-9 col-lg-10 ms-md-auto p-3 p-lg-4">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
                <div>
                    <h2 class="fw-bold mb-1">
                        <i class="bi bi-person-workspace text-primary"></i>
                        Kelola Wali Kelas
                    </h2>
                    <p class="text-secondary mb-0">Kelola penugasan guru sebagai wali kelas dan wali kelas.</p>
                </div>

                <a href="tambah_wali_kelas.php" class="btn btn-primary">
                    <i class="bi bi-person-plus-fill me-1"></i>
                    Tambah Wali Kelas
                </a>
            </div>

            <?php if (isset($_GET['pesan']) && $_GET['pesan'] === 'berhasil_tambah'): ?>
                <div class="alert alert-success alert-dismissible fade show">
                    <i class="bi bi-check-circle-fill me-2"></i>
                    Data wali kelas berhasil ditambahkan.
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php elseif (isset($_GET['pesan']) && $_GET['pesan'] === 'berhasil_hapus'): ?>
                <div class="alert alert-success alert-dismissible fade show">
                    <i class="bi bi-check-circle-fill me-2"></i>
                    Data wali kelas berhasil dihapus.
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-header bg-white border-0 p-3">
                    <h5 class="fw-semibold mb-0">
                        <i class="bi bi-table text-primary me-2"></i>
                        Daftar Wali Kelas
                    </h5>
                </div>

                <div class="card-body">
                    <?php if ($errorDatabase): ?>
                        <div class="alert alert-danger mb-0">
                            <i class="bi bi-exclamation-triangle-fill me-2"></i>
                            Data wali kelas gagal dimuat.
                            <?= htmlspecialchars(mysqli_error($koneksi), ENT_QUOTES, 'UTF-8'); ?>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table id="tabelWaliKelas" class="table table-striped table-hover table-bordered align-middle w-100">
                                <thead class="table-dark">
                                    <tr>
                                        <th>No.</th>
                                        <th>Nama Wali Kelas</th>
                                        <th>NIP</th>
                                        <th>Jenis Akun</th>
                                        <th>Kelas</th>
                                        <th>Tahun Ajaran</th>
                                        <th>Tanggal Mulai</th>
                                        <th>Tanggal Selesai</th>
                                        <th>Status</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($row = mysqli_fetch_assoc($hasil)): ?>
                                        <tr>
                                            <td></td>
                                            <td class="fw-semibold">
                                                <?= htmlspecialchars((string)($row['nama_wali'] ?? '-'), ENT_QUOTES, 'UTF-8'); ?>
                                            </td>
                                            <td>
                                                <?php if (!empty($row['guru_id'])): ?>
                                                    <?= htmlspecialchars((string)($row['nip'] ?? '-'), ENT_QUOTES, 'UTF-8'); ?>
                                                <?php else: ?>
                                                    -
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if (!empty($row['guru_id'])): ?>
                                                    <span class="badge text-bg-primary">Guru sebagai Wali Kelas</span>
                                                <?php else: ?>
                                                    <span class="badge text-bg-info">Wali Kelas</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <div class="fw-semibold">
                                                    <?= htmlspecialchars((string)($row['nama_kelas'] ?? '-'), ENT_QUOTES, 'UTF-8'); ?>
                                                </div>
                                                <small class="text-secondary">
                                                    <?= htmlspecialchars(trim((string)($row['tingkat'] ?? '') . (!empty($row['jurusan']) ? ' - ' . $row['jurusan'] : '')), ENT_QUOTES, 'UTF-8'); ?>
                                                </small>
                                            </td>
                                            <td>
                                                <?= htmlspecialchars((string)($row['nama_tahun_ajaran'] ?? '-'), ENT_QUOTES, 'UTF-8'); ?>
                                            </td>
                                            <td>
                                                <?= htmlspecialchars((string)$row['tanggal_mulai'], ENT_QUOTES, 'UTF-8'); ?>
                                            </td>
                                            <td>
                                                <?= htmlspecialchars((string)$row['tanggal_selesai'], ENT_QUOTES, 'UTF-8'); ?>
                                            </td>
                                            <td>
                                                <?php if ((int)$row['status_aktif'] === 1): ?>
                                                    <span class="badge text-bg-success">Aktif</span>
                                                <?php else: ?>
                                                    <span class="badge text-bg-secondary">Tidak Aktif</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <div class="d-flex flex-wrap gap-1">
                                                    <a href="edit_wali_kelas.php?id=<?= (int)$row['id']; ?>" class="btn btn-sm btn-warning">
                                                        <i class="bi bi-pencil-square"></i> Edit
                                                    </a>
                                                    <a href="hapus_wali_kelas.php?id=<?= (int)$row['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Yakin ingin menghapus penugasan wali kelas ini?');">
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

            <footer class="text-center text-secondary small mt-4 pb-3">
                &copy; <?= date('Y'); ?> SI-TIB — Sistem Informasi Tata Tertib
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
    $('#tabelWaliKelas').DataTable({
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
                targets: [0, 9],
                orderable: false,
                searchable: false
            }
        ],
        language: {
            emptyTable: 'Belum ada data wali kelas',
            zeroRecords: 'Data wali kelas tidak ditemukan',
            search: 'Cari:',
            searchPlaceholder: 'Cari nama, NIP, kelas, atau tahun ajaran...',
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
        }
    });
});
</script>
</body>
</html>