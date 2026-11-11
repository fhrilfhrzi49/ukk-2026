<?php
require_once __DIR__ . '/config/akses.php';

if (session_status() === PHP_SESSION_NONE) session_start();

require_once __DIR__ . '/includes/cek_session.php';
require_once __DIR__ . '/config/koneksi.php';

wajib_role(['guru', 'guru_wali_kelas', 'guru sekaligus wali kelas']);

$namaPengguna = trim($_SESSION['nama'] ?? $_SESSION['name'] ?? $_SESSION['username'] ?? 'Pengguna');
$role = strtolower(trim($_SESSION['role'] ?? ''));

$role = [
    'administrator' => 'admin',
    'wali kelas' => 'wali_kelas',
    'guru sekaligus wali kelas' => 'guru_wali_kelas'
][$role] ?? $role;

if (!in_array($role, ['guru', 'guru_wali_kelas'], true)) {
    http_response_code(403);
    exit('Akses ditolak. Halaman ini hanya untuk Guru dan Guru Wali Kelas.');
}

$pesan = $_SESSION['pesan_tindakan'] ?? '';
$jenisPesan = $_SESSION['jenis_pesan_tindakan'] ?? 'success';
unset($_SESSION['pesan_tindakan'], $_SESSION['jenis_pesan_tindakan']);

function h($nilai): string
{
    return htmlspecialchars((string) ($nilai ?? ''), ENT_QUOTES, 'UTF-8');
}

function badgeStatus($status): string
{
    return [
        'PROSES' => 'warning',
        'SELESAI' => 'primary'
    ][$status] ?? 'secondary';
}

$daftarStatus = ['PENDING', 'PROSES', 'SELESAI'];

$daftarTindakan = [
    'Teguran Lisan',
    'Teguran Tertulis',
    'Peringatan 1 (SP 1)',
    'Peringatan 2 (SP 2)',
    'Peringatan 3 (SP 3)',
    'Pemanggilan Orang Tua/Wali',
    'Pembinaan oleh Guru BK',
    'Konseling',
    'Perjanjian Tertulis',
    'Skorsing',
    'Pembinaan Khusus'
];

try {
    $cek = mysqli_query($koneksi, "SHOW COLUMNS FROM t_tindakan_siswa LIKE 'status'");

    if (!$cek) throw new Exception(mysqli_error($koneksi));

    if (!mysqli_num_rows($cek)) {
        if (!mysqli_query($koneksi, "ALTER TABLE t_tindakan_siswa ADD COLUMN status VARCHAR(20) NOT NULL DEFAULT 'PENDING'")) {
            throw new Exception(mysqli_error($koneksi));
        }
    }

    mysqli_free_result($cek);

    $sinkron = mysqli_prepare(
        $koneksi,
        "INSERT INTO t_tindakan_siswa
        (catatan_pelanggaran_id, jenis_tindakan, keterangan, tanggal_tindakan, dicatat_oleh, status)
        SELECT
            cp.id,
            '',
            '',
            COALESCE(cp.tanggal_pelanggaran, CURDATE()),
            cp.dicatat_oleh,
            'PENDING'
        FROM t_catatan_pelanggaran cp
        WHERE cp.dicatat_oleh = ?
        AND NOT EXISTS (
            SELECT 1
            FROM t_tindakan_siswa ts
            WHERE ts.catatan_pelanggaran_id = cp.id
        )"
    );

    if (!$sinkron) throw new Exception(mysqli_error($koneksi));

    mysqli_stmt_bind_param($sinkron, 's', $namaPengguna);

    if (!mysqli_stmt_execute($sinkron)) {
        throw new Exception(mysqli_stmt_error($sinkron));
    }

    mysqli_stmt_close($sinkron);
} catch (Throwable $e) {
    error_log($e->getMessage());
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $aksi = trim($_POST['aksi'] ?? '');

    if ($aksi === 'ubah_status') {
        $id = filter_input(INPUT_POST, 'tindakan_id', FILTER_VALIDATE_INT);
        $status = strtoupper(trim($_POST['status'] ?? ''));
        $jenisTindakan = trim($_POST['jenis_tindakan'] ?? '');
        $keteranganTindakan = trim($_POST['keterangan_tindakan'] ?? '');

        if (
            !$id ||
            !in_array($status, $daftarStatus, true) ||
            !in_array($jenisTindakan, $daftarTindakan, true) ||
            mb_strlen($keteranganTindakan) > 5000
        ) {
            $_SESSION['pesan_tindakan'] = 'Periksa kembali data perubahan tindakan.';
            $_SESSION['jenis_pesan_tindakan'] = 'danger';
        } else {
            try {
                $stmt = mysqli_prepare(
                    $koneksi,
                    "UPDATE t_tindakan_siswa
                     SET status = ?, jenis_tindakan = ?, keterangan = ?
                     WHERE id = ? AND dicatat_oleh = ?
                     LIMIT 1"
                );

                if (!$stmt) throw new Exception(mysqli_error($koneksi));

                mysqli_stmt_bind_param(
                    $stmt,
                    'sssis',
                    $status,
                    $jenisTindakan,
                    $keteranganTindakan,
                    $id,
                    $namaPengguna
                );

                if (!mysqli_stmt_execute($stmt)) {
                    throw new Exception(mysqli_stmt_error($stmt));
                }

                $berhasil = mysqli_stmt_affected_rows($stmt);
                mysqli_stmt_close($stmt);

                if ($berhasil === 0) {
                    $cek = mysqli_prepare(
                        $koneksi,
                        "SELECT id FROM t_tindakan_siswa
                         WHERE id = ? AND dicatat_oleh = ? LIMIT 1"
                    );

                    if (!$cek) throw new Exception(mysqli_error($koneksi));

                    mysqli_stmt_bind_param($cek, 'is', $id, $namaPengguna);
                    mysqli_stmt_execute($cek);
                    $hasil = mysqli_stmt_get_result($cek);
                    $milikSendiri = $hasil && mysqli_num_rows($hasil) > 0;
                    mysqli_stmt_close($cek);

                    if (!$milikSendiri) {
                        throw new RuntimeException('Tindakan tidak ditemukan atau bukan milik Anda.');
                    }
                }

                $_SESSION['pesan_tindakan'] = $status === 'SELESAI'
                    ? 'Tindakan berhasil diselesaikan dan otomatis dipindahkan ke Riwayat.'
                    : 'Perubahan tindakan berhasil disimpan.';

                $_SESSION['jenis_pesan_tindakan'] = 'success';
            } catch (Throwable $e) {
                error_log($e->getMessage());

                $_SESSION['pesan_tindakan'] = $e instanceof RuntimeException
                    ? $e->getMessage()
                    : 'Gagal menyimpan perubahan tindakan.';

                $_SESSION['jenis_pesan_tindakan'] = 'danger';
            }
        }

        header('Location: tindakan.php');
        exit;
    }
}

$riwayat = [];
$errorDatabase = '';
$totalPending = 0;
$totalProses = 0;
$totalSelesai = 0;

try {
    $sql = "
        SELECT
            ts.id,
            ts.jenis_tindakan,
            ts.keterangan AS keterangan_tindakan,
            ts.tanggal_tindakan,
            ts.dicatat_oleh,
            ts.status,
            cp.keterangan AS keterangan_pelanggaran,
            s.nama AS nama_siswa,
            s.nis,
            COALESCE(jp.nama_pelanggaran, p.nama) AS nama_pelanggaran,
            COALESCE(jp.poin, p.poin, 0) AS poin
        FROM t_tindakan_siswa ts
        INNER JOIN t_catatan_pelanggaran cp ON cp.id = ts.catatan_pelanggaran_id
        LEFT JOIN t_siswa s ON s.id = cp.siswa_id
        LEFT JOIN t_jenis_pelanggaran jp ON jp.id = cp.jenis_pelanggaran_id
        LEFT JOIN t_pelanggaran p ON p.id = cp.pelanggaran_id
        WHERE ts.dicatat_oleh = ?
        AND UPPER(TRIM(COALESCE(ts.status, 'PENDING'))) IN ('PENDING', 'PROSES')
        ORDER BY ts.tanggal_tindakan DESC, ts.id DESC
    ";

    $stmt = mysqli_prepare($koneksi, $sql);

    if (!$stmt) throw new Exception(mysqli_error($koneksi));

    mysqli_stmt_bind_param($stmt, 's', $namaPengguna);
    mysqli_stmt_execute($stmt);
    $hasil = mysqli_stmt_get_result($stmt);

    while ($item = mysqli_fetch_assoc($hasil)) {
        $riwayat[] = $item;

        if (strtoupper(trim($item['status'] ?? 'PENDING')) === 'PROSES') {
            $totalProses++;
        } else {
            $totalPending++;
        }
    }

    mysqli_stmt_close($stmt);

    $stmt = mysqli_prepare(
        $koneksi,
        "SELECT COUNT(*) AS total
         FROM t_tindakan_siswa
         WHERE dicatat_oleh = ?
         AND UPPER(TRIM(COALESCE(status, 'PENDING'))) = 'SELESAI'"
    );

    if (!$stmt) throw new Exception(mysqli_error($koneksi));

    mysqli_stmt_bind_param($stmt, 's', $namaPengguna);
    mysqli_stmt_execute($stmt);
    $hasil = mysqli_stmt_get_result($stmt);
    $totalSelesai = (int) (mysqli_fetch_assoc($hasil)['total'] ?? 0);
    mysqli_stmt_close($stmt);
} catch (Throwable $e) {
    error_log($e->getMessage());
    $errorDatabase = 'Data belum dapat ditampilkan. Periksa struktur tabel dan kolom database.';
}
?>

<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Tindakan | SI-TIB</title>
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
                    <h3 class="fw-bold text-dark mb-1">Tindakan SI-TIB</h3>
                    <p class="text-secondary mb-0">Kelola tindak lanjut dan pantau status tindakan pelanggaran siswa.</p>
                </div>

                <div class="d-flex align-items-center gap-2 bg-white rounded-3 shadow-sm px-3 py-2">
                    <div class="bg-primary bg-opacity-10 text-primary rounded-circle p-2">
                        <i class="bi bi-person-fill"></i>
                    </div>
                    <div>
                        <div class="small text-secondary">Login sebagai</div>
                        <div class="fw-semibold text-dark"><?= h($namaPengguna) ?></div>
                    </div>
                </div>
            </div>

            <?php if ($pesan !== ''): ?>
                <div class="alert alert-<?= h($jenisPesan) ?> alert-dismissible fade show border-0 shadow-sm rounded-3" role="alert">
                    <?= h($pesan) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button>
                </div>
            <?php endif; ?>

            <?php if ($errorDatabase !== ''): ?>
                <div class="alert alert-warning border-0 shadow-sm rounded-3">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    <?= h($errorDatabase) ?>
                </div>
            <?php endif; ?>

            <div class="row g-3 mb-4">
                <?php
                $statistik = [
                    ['Pending', $totalPending, 'hourglass-split', 'secondary'],
                    ['Dalam Proses', $totalProses, 'arrow-repeat', 'warning'],
                    ['Selesai', $totalSelesai, 'check-circle', 'primary']
                ];
                foreach ($statistik as [$judul, $jumlah, $ikon, $warna]):
                ?>
                    <div class="col-12 col-sm-6 col-xl-4">
                        <div class="card border-0 shadow-sm rounded-4 h-100">
                            <div class="card-body d-flex align-items-center gap-3 p-4">
                                <div class="bg-<?= $warna ?> bg-opacity-10 text-<?= $warna ?> rounded-3 p-3">
                                    <i class="bi bi-<?= $ikon ?> fs-4"></i>
                                </div>
                                <div>
                                    <div class="text-secondary small"><?= h($judul) ?></div>
                                    <div class="fs-4 fw-bold"><?= $jumlah ?></div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-header bg-white border-0 px-4 pt-4">
                    <div class="d-flex align-items-center gap-3">
                        <div class="bg-info bg-opacity-10 text-info rounded-3 p-3">
                            <i class="bi bi-clock-history fs-5"></i>
                        </div>
                        <div>
                            <h5 class="fw-bold mb-1">Status Tindakan</h5>
                            <p class="text-secondary small mb-0">
                                Hanya tindakan dari catatan pelanggaran yang Anda buat sendiri yang ditampilkan.
                                Tindakan SELESAI otomatis masuk ke Riwayat.
                            </p>
                        </div>
                    </div>
                </div>

                <div class="card-body p-4">
                    <div class="table-responsive">
                        <table id="tabelTindakan" class="table table-hover align-middle w-100">
                            <thead class="table-light">
                                <tr>
                                    <th>No.</th>
                                    <th>Tanggal</th>
                                    <th>NIS</th>
                                    <th>Nama Siswa</th>
                                    <th>Pelanggaran</th>
                                    <th>Poin</th>
                                    <th>Tindakan</th>
                                    <th>Keterangan</th>
                                    <th>Dicatat Oleh</th>
                                    <th>Status</th>
                                </tr>
                            </thead>

                            <tbody>
                                <?php foreach ($riwayat as $r): ?>
                                    <?php
                                    $statusSaatIni = strtoupper(trim($r['status'] ?? 'PENDING'));
                                    $jenisSaatIni = trim($r['jenis_tindakan'] ?? '');

                                    if ($jenisSaatIni === 'Belum ditentukan') {
                                        $jenisSaatIni = '';
                                    }
                                    ?>
                                    <tr>
                                        <td></td>
                                        <td><?= h($r['tanggal_tindakan'] ?? '-') ?></td>
                                        <td><?= h($r['nis'] ?? '-') ?></td>
                                        <td class="fw-semibold"><?= h($r['nama_siswa'] ?? '-') ?></td>
                                        <td><?= h($r['nama_pelanggaran'] ?? '-') ?></td>
                                        <td>
                                            <span class="badge text-bg-warning"><?= (int) ($r['poin'] ?? 0) ?> poin</span>
                                        </td>
                                        <td>
                                            <span class="badge text-bg-primary"><?= h($jenisSaatIni !== '' ? $jenisSaatIni : '-') ?></span>
                                        </td>
                                        <td><?= nl2br(h($r['keterangan_pelanggaran'] ?? '-')) ?></td>
                                        <td><?= h($r['dicatat_oleh'] ?? '-') ?></td>
                                        <td>
                                            <div class="d-flex flex-column gap-2">
                                                <span class="badge text-bg-<?= h(badgeStatus($statusSaatIni)) ?>">
                                                    <?= h($statusSaatIni) ?>
                                                </span>
                                                <button
                                                    type="button"
                                                    class="btn btn-primary btn-sm"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#modalEditTindakan"
                                                    data-id="<?= (int) $r['id'] ?>"
                                                    data-status="<?= h($statusSaatIni) ?>"
                                                    data-jenis="<?= h($jenisSaatIni) ?>"
                                                    data-keterangan="<?= h($r['keterangan_tindakan'] ?? '') ?>">
                                                    <i class="bi bi-pencil-square me-1"></i> EDIT
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <footer class="text-center text-secondary small py-4">
                &copy; <?= date('Y') ?> SI-TIB — Sistem Informasi Tata Tertib
            </footer>
        </main>
    </div>
</div>

<div class="modal fade" id="modalEditTindakan" tabindex="-1" aria-labelledby="judulEditTindakan" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 rounded-4 shadow">
            <form method="post" action="tindakan.php" id="formEditTindakan">
                <div class="modal-header border-0 px-4 pt-4">
                    <h5 class="modal-title fw-bold" id="judulEditTindakan">
                        <i class="bi bi-pencil-square text-primary me-2"></i>
                        Edit Status Tindakan
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>

                <div class="modal-body px-4">
                    <input type="hidden" name="aksi" value="ubah_status">
                    <input type="hidden" name="tindakan_id" id="editTindakanId">

                    <div class="mb-3">
                        <label for="editStatus" class="form-label fw-semibold">Status</label>
                        <select class="form-select" name="status" id="editStatus" required>
                            <option value="PENDING">PENDING</option>
                            <option value="PROSES">PROSES</option>
                            <option value="SELESAI">SELESAI</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="cariJenisTindakan" class="form-label fw-semibold">Jenis Tindakan</label>
                        <div class="dropdown">
                            <input
                                type="text"
                                class="form-control"
                                id="cariJenisTindakan"
                                placeholder="Cari atau pilih jenis tindakan..."
                                autocomplete="off"
                                data-bs-toggle="dropdown"
                                aria-expanded="false"
                                required>

                            <input
                                type="hidden"
                                name="jenis_tindakan"
                                id="editJenisTindakan"
                                required>

                            <div
                                class="dropdown-menu w-100 p-2 shadow overflow-auto"
                                id="daftarPilihanTindakan"
                                style="max-height: 220px;">
                                <?php foreach ($daftarTindakan as $tindakan): ?>
                                    <button
                                        type="button"
                                        class="dropdown-item rounded pilihan-tindakan"
                                        data-value="<?= h($tindakan) ?>">
                                        <?= h($tindakan) ?>
                                    </button>
                                <?php endforeach; ?>
                                <div class="dropdown-item-text text-secondary small d-none" id="tindakanTidakDitemukan">
                                    Jenis tindakan tidak ditemukan.
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="editKeterangan" class="form-label fw-semibold">Keterangan Tindakan</label>
                        <textarea
                            class="form-control"
                            name="keterangan_tindakan"
                            id="editKeterangan"
                            rows="4"
                            maxlength="5000"
                            placeholder="Masukkan keterangan tindakan..."></textarea>
                    </div>
                </div>

                <div class="modal-footer border-0 px-4 pb-4">
                    <button type="button" class="btn btn-light border px-4" data-bs-dismiss="modal">
                        Batal
                    </button>
                    <button type="submit" class="btn btn-primary px-4">
                        <i class="bi bi-save me-1"></i>
                        Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="modalKonfirmasiSelesai" tabindex="-1" aria-labelledby="judulKonfirmasiSelesai" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content border-0 rounded-4 shadow">
            <div class="modal-body text-center p-4">
                <div class="text-primary mb-3">
                    <i class="bi bi-check-circle-fill display-3"></i>
                </div>
                <h5 class="fw-bold mb-2" id="judulKonfirmasiSelesai">Selesaikan Tindakan?</h5>
                <p class="text-secondary small mb-4">
                    Tindakan ini akan ditandai SELESAI dan otomatis dipindahkan ke halaman Riwayat setelah disimpan.
                </p>
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-light border w-50" id="batalSelesai">
                        Batal
                    </button>
                    <button type="button" class="btn btn-primary w-50" id="konfirmasiSelesai">
                        Ya, Selesaikan
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.datatables.net/2.3.4/js/dataTables.min.js"></script>
<script src="https://cdn.datatables.net/2.3.4/js/dataTables.bootstrap5.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const modalEdit = document.getElementById('modalEditTindakan');
    const modalKonfirmasiElement = document.getElementById('modalKonfirmasiSelesai');
    const modalKonfirmasi = bootstrap.Modal.getOrCreateInstance(modalKonfirmasiElement);
    const inputCari = document.getElementById('cariJenisTindakan');
    const inputJenis = document.getElementById('editJenisTindakan');
    const editStatus = document.getElementById('editStatus');
    const pilihanTindakan = document.querySelectorAll('.pilihan-tindakan');
    const tidakDitemukan = document.getElementById('tindakanTidakDitemukan');
    const dropdownTindakan = bootstrap.Dropdown.getOrCreateInstance(inputCari);
    const formEdit = document.getElementById('formEditTindakan');
    const tombolBatalSelesai = document.getElementById('batalSelesai');
    const tombolKonfirmasiSelesai = document.getElementById('konfirmasiSelesai');

    let statusSebelumnya = 'PENDING';
    let menungguKonfirmasi = false;

    function pilihanTersedia(nilai) {
        return Array.from(pilihanTindakan).some(function (pilihan) {
            return pilihan.dataset.value === nilai;
        });
    }

    function filterTindakan() {
        const kataKunci = inputCari.value.toLowerCase().trim();
        let jumlahTampil = 0;

        pilihanTindakan.forEach(function (pilihan) {
            const cocok = pilihan.dataset.value.toLowerCase().includes(kataKunci);
            pilihan.classList.toggle('d-none', !cocok);

            if (cocok) jumlahTampil++;
        });

        tidakDitemukan.classList.toggle('d-none', jumlahTampil > 0);
        dropdownTindakan.show();
    }

    inputCari.addEventListener('input', function () {
        inputJenis.value = '';
        filterTindakan();
    });

    inputCari.addEventListener('focus', function () {
        filterTindakan();
    });

    pilihanTindakan.forEach(function (pilihan) {
        pilihan.addEventListener('click', function () {
            inputCari.value = this.dataset.value;
            inputJenis.value = this.dataset.value;
            dropdownTindakan.hide();
        });
    });

    modalEdit.addEventListener('show.bs.modal', function (event) {
        const tombol = event.relatedTarget;
        let jenisSaatIni = tombol.dataset.jenis || '';

        if (jenisSaatIni === 'Belum ditentukan') {
            jenisSaatIni = '';
        }

        statusSebelumnya = tombol.dataset.status || 'PENDING';
        menungguKonfirmasi = false;

        document.getElementById('editTindakanId').value = tombol.dataset.id;
        editStatus.value = statusSebelumnya;
        document.getElementById('editKeterangan').value = tombol.dataset.keterangan;

        inputCari.value = jenisSaatIni;
        inputJenis.value = pilihanTersedia(jenisSaatIni) ? jenisSaatIni : '';
    });

    editStatus.addEventListener('change', function () {
        if (this.value === 'SELESAI' && statusSebelumnya !== 'SELESAI') {
            menungguKonfirmasi = true;
            modalKonfirmasi.show();
        } else {
            statusSebelumnya = this.value;
        }
    });

    tombolBatalSelesai.addEventListener('click', function () {
        editStatus.value = statusSebelumnya;
        menungguKonfirmasi = false;
        modalKonfirmasi.hide();
    });

    tombolKonfirmasiSelesai.addEventListener('click', function () {
        if (!menungguKonfirmasi) return;

        statusSebelumnya = 'SELESAI';
        editStatus.value = 'SELESAI';
        menungguKonfirmasi = false;
        modalKonfirmasi.hide();
    });

    modalKonfirmasiElement.addEventListener('hidden.bs.modal', function () {
        if (menungguKonfirmasi) {
            editStatus.value = statusSebelumnya;
            menungguKonfirmasi = false;
        }
    });

    formEdit.addEventListener('submit', function (event) {
        if (!inputJenis.value || !pilihanTersedia(inputJenis.value)) {
            event.preventDefault();
            inputCari.focus();
            filterTindakan();
            return;
        }

        if (editStatus.value === 'SELESAI' && statusSebelumnya !== 'SELESAI') {
            event.preventDefault();
            menungguKonfirmasi = true;
            modalKonfirmasi.show();
        }
    });
});

$(function () {
    const tabel = $('#tabelTindakan').DataTable({
        pageLength: 10,
        lengthMenu: [5, 10, 25, 50, 100],
        order: [],
        scrollX: true,
        language: {
            search: 'Cari:',
            searchPlaceholder: 'Cari tindakan...',
            lengthMenu: 'Tampilkan _MENU_ data',
            info: 'Menampilkan _START_ sampai _END_ dari _TOTAL_ data',
            infoEmpty: 'Tidak ada data',
            infoFiltered: '(difilter dari _MAX_ total data)',
            zeroRecords: 'Data tidak ditemukan',
            emptyTable: 'Belum ada tindakan yang perlu ditindaklanjuti',
            paginate: {
                first: 'Pertama',
                last: 'Terakhir',
                next: 'Berikutnya',
                previous: 'Sebelumnya'
            }
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