<?php
require_once __DIR__ . '/config/akses.php';
wajib_role(['admin']);

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

if (!$query) {
    die('Gagal mengambil data penempatan: ' . htmlspecialchars(mysqli_error($koneksi)));
}

function tanggalPenempatan($tanggal)
{
    if (empty($tanggal) || $tanggal === '0000-00-00') {
        return '-';
    }

    return date('d-m-Y', strtotime($tanggal));
}

$total = mysqli_num_rows($query);
$aktif = 0;

while ($row = mysqli_fetch_assoc($query)) {
    if ((int)$row['status_aktif'] === 1) {
        $aktif++;
    }
}

$tidakAktif = $total - $aktif;
mysqli_data_seek($query, 0);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Penempatan Siswa | SI-TIB</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/2.3.4/css/dataTables.bootstrap5.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<?php include __DIR__ . '/layout/sidebar.php'; ?>

<main class="container-fluid">
    <div class="row">
        <div class="col-12 col-md-9 col-lg-10 offset-md-3 offset-lg-2 p-3 p-lg-4">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
                <div>
                    <h3 class="fw-bold mb-1">
                        <i class="bi bi-person-lines-fill text-primary me-2"></i>
                        Data Penempatan Siswa
                    </h3>
                    <p class="text-secondary mb-0">Daftar penempatan siswa yang tercatat dalam sistem.</p>
                </div>

                <a href="tambah_penempatan.php" class="btn btn-primary">
                    <i class="bi bi-plus-circle me-1"></i>
                    Tambah Penempatan
                </a>
            </div>

            <div class="row g-3 mb-4">
                <div class="col-12 col-sm-6 col-lg-4">
                    <div class="card border-0 shadow-sm rounded-3">
                        <div class="card-body">
                            <div class="d-flex align-items-center justify-content-between">
                                <div>
                                    <p class="text-secondary mb-1">Total Penempatan</p>
                                    <h3 class="fw-bold mb-0"><?= $total; ?></h3>
                                </div>
                                <div class="bg-primary bg-opacity-10 text-primary rounded-3 p-3">
                                    <i class="bi bi-people-fill fs-3"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-sm-6 col-lg-4">
                    <div class="card border-0 shadow-sm rounded-3">
                        <div class="card-body">
                            <div class="d-flex align-items-center justify-content-between">
                                <div>
                                    <p class="text-secondary mb-1">Penempatan Aktif</p>
                                    <h3 class="fw-bold mb-0"><?= $aktif; ?></h3>
                                </div>
                                <div class="bg-success bg-opacity-10 text-success rounded-3 p-3">
                                    <i class="bi bi-check-circle-fill fs-3"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-sm-6 col-lg-4">
                    <div class="card border-0 shadow-sm rounded-3">
                        <div class="card-body">
                            <div class="d-flex align-items-center justify-content-between">
                                <div>
                                    <p class="text-secondary mb-1">Penempatan Tidak Aktif</p>
                                    <h3 class="fw-bold mb-0"><?= $tidakAktif; ?></h3>
                                </div>
                                <div class="bg-danger bg-opacity-10 text-danger rounded-3 p-3">
                                    <i class="bi bi-x-circle-fill fs-3"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-header bg-white py-3">
                    <h5 class="fw-semibold mb-0">
                        <i class="bi bi-table me-2"></i>
                        Daftar Penempatan Siswa
                    </h5>
                </div>

                <div class="card-body">
                    <div class="table-responsive">
                        <table id="tabelPenempatan" class="table table-bordered table-striped table-hover align-middle w-100">
                            <thead class="table-light text-center">
                                <tr>
                                    <th>No.</th>
                                    <th>NIS</th>
                                    <th>Nama Siswa</th>
                                    <th>Tahun Ajaran</th>
                                    <th>Kelas</th>
                                    <th>Tingkat</th>
                                    <th>Jurusan</th>
                                    <th>Tanggal Mulai</th>
                                    <th>Tanggal Selesai</th>
                                    <th>Status</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $no = 1;
                                while ($row = mysqli_fetch_assoc($query)):
                                ?>
                                    <tr>
                                        <td class="text-center"><?= $no++; ?></td>
                                        <td><?= htmlspecialchars($row['nis'] ?? '', ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td class="fw-semibold"><?= htmlspecialchars($row['nama_siswa'] ?? '', ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td><?= htmlspecialchars($row['tahun_ajaran'] ?? '', ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td><?= htmlspecialchars($row['nama_kelas'] ?? '', ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td><?= htmlspecialchars($row['tingkat'] ?? '', ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td><?= htmlspecialchars($row['jurusan'] ?? '', ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td class="text-center"><?= tanggalPenempatan($row['tanggal_mulai']); ?></td>
                                        <td class="text-center"><?= tanggalPenempatan($row['tanggal_selesai']); ?></td>
                                        <td class="text-center">
                                            <?php if ((int)$row['status_aktif'] === 1): ?>
                                                <span class="badge bg-success">Aktif</span>
                                            <?php else: ?>
                                                <span class="badge bg-danger">Tidak Aktif</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-center">
                                            <a href="edit_penempatan.php?id=<?= (int)$row['id']; ?>" class="btn btn-warning btn-sm" title="Edit Penempatan">
                                                <i class="bi bi-pencil-square"></i>
                                            </a>
                                            <a href="hapus_penempatan.php?id=<?= (int)$row['id']; ?>" class="btn btn-danger btn-sm" title="Hapus Penempatan" onclick="return confirm('Apakah Anda yakin ingin menghapus penempatan siswa ini?')">
                                                <i class="bi bi-trash"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <footer class="text-center text-secondary small mt-4">
                &copy; <?= date('Y'); ?> SI-TIB — Sistem Informasi Tata Tertib
            </footer>
        </div>
    </div>
</main>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.datatables.net/2.3.4/js/dataTables.min.js"></script>
<script src="https://cdn.datatables.net/2.3.4/js/dataTables.bootstrap5.min.js"></script>
<script>
$(document).ready(function () {
    $('#tabelPenempatan').DataTable({
        pageLength: 10,
        lengthMenu: [5, 10, 25, 50, 100],
        ordering: true,
        responsive: false,
        language: {
            lengthMenu: 'Tampilkan _MENU_ data',
            search: 'Cari:',
            zeroRecords: 'Data penempatan tidak ditemukan',
            info: 'Menampilkan _START_ sampai _END_ dari _TOTAL_ data',
            infoEmpty: 'Tidak ada data tersedia',
            infoFiltered: '(difilter dari _MAX_ total data)',
            paginate: {
                first: 'Pertama',
                last: 'Terakhir',
                next: 'Berikutnya',
                previous: 'Sebelumnya'
            }
        }
    });
});
</script>
</body>
</html>