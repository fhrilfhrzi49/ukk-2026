
<?php
require_once __DIR__ . '/config/akses.php';

wajib_role(['guru', 'wali_kelas', 'guru_wali_kelas']);

require_once __DIR__ . '/includes/cek_session.php';
require_once __DIR__ . '/config/koneksi.php';

$namaPengguna = $_SESSION['nama'] ?? $_SESSION['name'] ?? $_SESSION['username'] ?? 'Pengguna';

if (!function_exists('h')) {
    function h($value): string
    {
        return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('badgeKategori')) {
    function badgeKategori($kategori): string
    {
        return match ($kategori) {
            'Rendah' => 'success',
            'Sedang' => 'warning',
            'Tinggi' => 'danger',
            default => 'secondary'
        };
    }
}

$kataKunci = trim((string) ($_GET['cari'] ?? ''));
$filterPoin = trim((string) ($_GET['kategori_poin'] ?? ''));
$siswaIdFilter = filter_input(INPUT_GET, 'siswa_id', FILTER_VALIDATE_INT) ?: 0;

$kategoriValid = ['', 'Belum Ada Poin', 'Rendah', 'Sedang', 'Tinggi'];

if (!in_array($filterPoin, $kategoriValid, true)) {
    $filterPoin = '';
}

if ($siswaIdFilter < 1) {
    $siswaIdFilter = 0;
}

$daftarSiswa = [];
$daftarRekap = [];
$errorDatabase = '';
$stmt = null;

try {
    $querySiswa = mysqli_query($koneksi, "
        SELECT id, nis, nama
        FROM t_siswa
        WHERE status_aktif = 1
        ORDER BY nama ASC
    ");

    if (!$querySiswa) {
        throw new RuntimeException('Gagal mengambil data siswa.');
    }

    while ($siswa = mysqli_fetch_assoc($querySiswa)) {
        $daftarSiswa[] = $siswa;
    }

    mysqli_free_result($querySiswa);

    $sql = "
        SELECT
            s.id AS siswa_id,
            s.nis,
            s.nama AS nama_siswa,
            COUNT(DISTINCT cp.id) AS jumlah_pelanggaran,
            COALESCE(SUM(COALESCE(jp.poin, p.poin, 0)), 0) AS total_poin,
            MAX(cp.tanggal_pelanggaran) AS pelanggaran_terakhir
        FROM t_siswa s
        LEFT JOIN t_catatan_pelanggaran cp
            ON cp.siswa_id = s.id
            AND EXISTS (
                SELECT 1
                FROM t_tindakan_siswa ts
                WHERE ts.catatan_pelanggaran_id = cp.id
                AND UPPER(TRIM(COALESCE(ts.status, 'PENDING'))) = 'SELESAI'
            )
        LEFT JOIN t_jenis_pelanggaran jp
            ON jp.id = cp.jenis_pelanggaran_id
        LEFT JOIN t_pelanggaran p
            ON p.id = cp.pelanggaran_id
        WHERE s.status_aktif = 1
    ";

    $tipeParameter = '';
    $parameter = [];

    if ($siswaIdFilter > 0) {
        $sql .= " AND s.id = ?";
        $tipeParameter = 'i';
        $parameter[] = $siswaIdFilter;
    } elseif ($kataKunci !== '') {
        $sql .= " AND (s.nama LIKE ? OR s.nis LIKE ?)";
        $polaCari = '%' . $kataKunci . '%';
        $tipeParameter = 'ss';
        $parameter = [$polaCari, $polaCari];
    }

    $sql .= "
        GROUP BY s.id, s.nis, s.nama
        ORDER BY total_poin DESC, s.nama ASC
    ";

    $stmt = mysqli_prepare($koneksi, $sql);

    if (!$stmt) {
        throw new RuntimeException('Gagal menyiapkan query.');
    }

    if ($parameter) {
        mysqli_stmt_bind_param($stmt, $tipeParameter, ...$parameter);
    }

    if (!mysqli_stmt_execute($stmt)) {
        throw new RuntimeException('Gagal menjalankan query.');
    }

    $hasil = mysqli_stmt_get_result($stmt);

    if (!$hasil) {
        throw new RuntimeException('Gagal membaca hasil query.');
    }

    while ($item = mysqli_fetch_assoc($hasil)) {
        $poin = (int) $item['total_poin'];

        $kategori = match (true) {
            $poin === 0 => 'Belum Ada Poin',
            $poin <= 20 => 'Rendah',
            $poin <= 50 => 'Sedang',
            default => 'Tinggi'
        };

        $item['kategori_poin'] = $kategori;

        if ($filterPoin === '' || $filterPoin === $kategori) {
            $daftarRekap[] = $item;
        }
    }

    mysqli_free_result($hasil);
    mysqli_stmt_close($stmt);
    $stmt = null;

} catch (Throwable $e) {
    if ($stmt instanceof mysqli_stmt) {
        mysqli_stmt_close($stmt);
    }

    error_log('Rekap poin siswa: ' . $e->getMessage());
    $errorDatabase = 'Rekap poin gagal dimuat. Periksa struktur tabel database.';
}

$totalSiswa = count($daftarRekap);
$totalPoinSemua = array_sum(array_column($daftarRekap, 'total_poin'));
$totalSiswaBerpoin = count(array_filter($daftarRekap, fn($item) => (int) $item['total_poin'] > 0));
$totalPelanggaran = array_sum(array_column($daftarRekap, 'jumlah_pelanggaran'));
?>

<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Rekap Poin | SI-TIB</title>
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
                        <i class="bi bi-bar-chart-fill text-primary me-2"></i>
                        Rekap Poin SI-TIB
                    </h3>
                    <p class="text-secondary mb-0">Ringkasan poin pelanggaran siswa pada Sistem Informasi Tata Tertib.</p>
                </div>

                <div class="bg-white rounded-4 shadow-sm px-3 py-2">
                    <div class="d-flex align-items-center gap-2">
                        <div class="rounded-circle bg-primary-subtle text-primary p-2">
                            <i class="bi bi-person-circle fs-5"></i>
                        </div>
                        <div>
                            <div class="small text-secondary">Login sebagai</div>
                            <div class="fw-semibold text-dark"><?= h($namaPengguna) ?></div>
                        </div>
                    </div>
                </div>
            </div>

            <?php if ($errorDatabase !== ''): ?>
                <div class="alert alert-warning rounded-3 border-0 shadow-sm">
                    <i class="bi bi-exclamation-triangle me-2"></i><?= h($errorDatabase) ?>
                </div>
            <?php endif; ?>

            <div class="row g-3 mb-4">
                <?php
                $statistik = [
                    ['Jumlah Siswa', $totalSiswa, 'people-fill', 'primary'],
                    ['Akumulasi Poin', $totalPoinSemua, 'award-fill', 'warning'],
                    ['Siswa Berpoin', $totalSiswaBerpoin, 'exclamation-triangle-fill', 'danger'],
                    ['Catatan Pelanggaran', $totalPelanggaran, 'journal-check', 'success']
                ];

                foreach ($statistik as [$judul, $nilai, $ikon, $warna]):
                ?>
                    <div class="col-12 col-sm-6 col-xl-3">
                        <div class="card border-0 shadow-sm rounded-4 h-100">
                            <div class="card-body p-4">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <p class="text-secondary mb-1"><?= $judul ?></p>
                                        <h3 class="fw-bold mb-0"><?= number_format((int) $nilai) ?></h3>
                                    </div>
                                    <div class="bg-<?= $warna ?>-subtle text-<?= $warna ?> rounded-3 p-3">
                                        <i class="bi bi-<?= $ikon ?> fs-3"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-header bg-white border-0 pt-4 px-4">
                    <div class="d-flex align-items-center gap-3">
                        <div class="rounded-3 bg-primary-subtle text-primary p-2">
                            <i class="bi bi-funnel fs-5"></i>
                        </div>
                        <div>
                            <h5 class="fw-bold mb-1">Filter Rekap</h5>
                            <p class="text-secondary small mb-0">Cari siswa berdasarkan nama/NIS atau kelompokkan berdasarkan total poin.</p>
                        </div>
                    </div>
                </div>

                <div class="card-body p-4">
                    <form method="get" action="rekap_poin.php" id="formFilter">
                        <div class="row g-3">
                            <div class="col-12 col-md-6">
                                <label for="cari" class="form-label fw-semibold">Nama Siswa atau NIS</label>
                                <div class="position-relative">
                                    <input type="text" class="form-control" id="cari" name="cari"
                                        value="<?= h($kataKunci) ?>" placeholder="Masukkan nama siswa atau NIS" autocomplete="off">
                                    <div class="list-group position-absolute top-100 start-0 w-100 shadow z-3 d-none" id="hasilSiswa"></div>
                                </div>
                                <input type="hidden" name="siswa_id" id="siswa_id" value="<?= $siswaIdFilter ?: '' ?>">
                                <div id="statusSiswa" class="form-text">Ketik nama atau NIS, lalu pilih siswa dari hasil pencarian.</div>
                            </div>

                            <div class="col-12 col-md-4">
                                <label for="kategori_poin" class="form-label fw-semibold">Kategori Poin</label>
                                <div class="position-relative">
                                    <input type="text" class="form-control" id="kategori_poin" name="kategori_poin"
                                        value="<?= h($filterPoin) ?>" placeholder="Cari kategori poin" autocomplete="off">
                                    <div class="list-group position-absolute top-100 start-0 w-100 shadow z-3 d-none" id="hasilKategori"></div>
                                </div>
                                <div id="statusKategori" class="form-text">Ketik kategori poin, lalu pilih dari hasil pencarian.</div>
                            </div>

                            <div class="col-12 col-md-2 d-flex align-items-end gap-2">
                                <button type="submit" class="btn btn-primary flex-grow-1">
                                    <i class="bi bi-search me-1"></i>Filter
                                </button>
                                <a href="rekap_poin.php" class="btn btn-light border" title="Reset filter">
                                    <i class="bi bi-arrow-counterclockwise"></i>
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-header bg-white border-0 pt-4 px-4">
                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
                        <div class="d-flex align-items-center gap-3">
                            <div class="rounded-3 bg-primary-subtle text-primary p-2">
                                <i class="bi bi-table fs-5"></i>
                            </div>
                            <div>
                                <h5 class="fw-bold mb-1">Data Rekap Poin Siswa</h5>
                                <p class="text-secondary small mb-0">Data diurutkan berdasarkan poin tertinggi.</p>
                            </div>
                        </div>
                        <span class="badge text-bg-primary rounded-pill px-3 py-2"><?= number_format($totalSiswa) ?> Data</span>
                    </div>
                </div>

                <div class="card-body p-4">
                    <div class="table-responsive">
                        <table id="tabelRekap" class="table table-hover align-middle w-100">
                            <thead class="table-light">
                                <tr>
                                    <th>No.</th>
                                    <th>NIS</th>
                                    <th>Nama Siswa</th>
                                    <th>Jumlah Pelanggaran</th>
                                    <th>Total Poin</th>
                                    <th>Kategori</th>
                                    <th>Pelanggaran Terakhir</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($daftarRekap as $item): ?>
                                    <tr>
                                        <td></td>
                                        <td><?= h($item['nis'] ?? '-') ?></td>
                                        <td class="fw-semibold"><?= h($item['nama_siswa'] ?? '-') ?></td>
                                        <td><span class="badge text-bg-light border"><?= (int) $item['jumlah_pelanggaran'] ?> kali</span></td>
                                        <td><span class="badge text-bg-primary"><?= (int) $item['total_poin'] ?> poin</span></td>
                                        <td>
                                            <span class="badge text-bg-<?= badgeKategori($item['kategori_poin']) ?>">
                                                <?= h($item['kategori_poin']) ?>
                                            </span>
                                        </td>
                                        <td><?= h($item['pelanggaran_terakhir'] ?? 'Belum ada') ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <div class="alert alert-info small mt-3 mb-0">
                        <i class="bi bi-info-circle me-1"></i>
                        Kategori poin: 0 = belum ada poin, 1–20 = rendah, 21–50 = sedang, dan di atas 50 = tinggi.
                    </div>
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
document.addEventListener('DOMContentLoaded', function () {
    const daftarSiswa = <?= json_encode(array_map(fn($s) => [
        'id' => (string) $s['id'],
        'nis' => (string) ($s['nis'] ?? ''),
        'nama' => (string) ($s['nama'] ?? '')
    ], $daftarSiswa), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>;

    const daftarKategori = [
        {nama: 'Belum Ada Poin', deskripsi: 'Siswa dengan total 0 poin.'},
        {nama: 'Rendah', deskripsi: 'Total 1 sampai 20 poin.'},
        {nama: 'Sedang', deskripsi: 'Total 21 sampai 50 poin.'},
        {nama: 'Tinggi', deskripsi: 'Total lebih dari 50 poin.'}
    ];

    const inputCari = document.getElementById('cari');
    const inputSiswaId = document.getElementById('siswa_id');
    const hasilSiswa = document.getElementById('hasilSiswa');
    const statusSiswa = document.getElementById('statusSiswa');
    const inputKategori = document.getElementById('kategori_poin');
    const hasilKategori = document.getElementById('hasilKategori');
    const statusKategori = document.getElementById('statusKategori');
    const form = document.getElementById('formFilter');

    function hasilKosong(container, pesan) {
        container.replaceChildren();
        const item = document.createElement('div');
        item.className = 'list-group-item text-secondary';
        item.textContent = pesan;
        container.appendChild(item);
        container.classList.remove('d-none');
    }

    function tampilkanSiswa() {
        const kata = inputCari.value.trim().toLocaleLowerCase('id');
        inputSiswaId.value = '';
        statusSiswa.textContent = 'Pilih siswa dari hasil pencarian.';
        statusSiswa.className = 'form-text';
        hasilSiswa.replaceChildren();

        if (!kata) {
            hasilSiswa.classList.add('d-none');
            return;
        }

        const hasil = daftarSiswa.filter(s =>
            s.nama.toLocaleLowerCase('id').includes(kata) ||
            s.nis.toLocaleLowerCase('id').includes(kata)
        ).slice(0, 100);

        if (!hasil.length) {
            hasilKosong(hasilSiswa, 'Siswa tidak ditemukan.');
            return;
        }

        hasil.forEach(s => {
            const tombol = document.createElement('button');
            tombol.type = 'button';
            tombol.className = 'list-group-item list-group-item-action';

            const nama = document.createElement('span');
            nama.className = 'fw-semibold d-block';
            nama.textContent = s.nama;

            const nis = document.createElement('small');
            nis.className = 'text-secondary';
            nis.textContent = 'NIS: ' + (s.nis || '-');

            tombol.append(nama, nis);
            tombol.addEventListener('click', function () {
                inputSiswaId.value = s.id;
                inputCari.value = s.nama;
                statusSiswa.textContent = 'Siswa terpilih: ' + s.nama + ' (NIS: ' + (s.nis || '-') + ')';
                statusSiswa.className = 'form-text text-success';
                hasilSiswa.classList.add('d-none');
            });

            hasilSiswa.appendChild(tombol);
        });

        hasilSiswa.classList.remove('d-none');
    }

    function tampilkanKategori() {
        const kata = inputKategori.value.trim().toLocaleLowerCase('id');
        hasilKategori.replaceChildren();
        statusKategori.textContent = 'Pilih kategori poin dari hasil pencarian.';
        statusKategori.className = 'form-text';

        if (!kata) {
            hasilKategori.classList.add('d-none');
            return;
        }

        const hasil = daftarKategori.filter(k =>
            k.nama.toLocaleLowerCase('id').includes(kata)
        );

        if (!hasil.length) {
            hasilKosong(hasilKategori, 'Kategori poin tidak ditemukan.');
            return;
        }

        hasil.forEach(k => {
            const tombol = document.createElement('button');
            tombol.type = 'button';
            tombol.className = 'list-group-item list-group-item-action';

            const nama = document.createElement('span');
            nama.className = 'fw-semibold d-block';
            nama.textContent = k.nama;

            const deskripsi = document.createElement('small');
            deskripsi.className = 'text-secondary';
            deskripsi.textContent = k.deskripsi;

            tombol.append(nama, deskripsi);
            tombol.addEventListener('click', function () {
                inputKategori.value = k.nama;
                statusKategori.textContent = 'Kategori poin terpilih: ' + k.nama;
                statusKategori.className = 'form-text text-success';
                hasilKategori.classList.add('d-none');
            });

            hasilKategori.appendChild(tombol);
        });

        hasilKategori.classList.remove('d-none');
    }

    inputCari.addEventListener('input', tampilkanSiswa);
    inputKategori.addEventListener('input', tampilkanKategori);

    document.addEventListener('click', function (event) {
        if (!hasilSiswa.contains(event.target) && event.target !== inputCari) {
            hasilSiswa.classList.add('d-none');
        }

        if (!hasilKategori.contains(event.target) && event.target !== inputKategori) {
            hasilKategori.classList.add('d-none');
        }
    });

    form.addEventListener('submit', function (event) {
        const namaSiswa = inputCari.value.trim();

        if (namaSiswa && !inputSiswaId.value) {
            const siswa = daftarSiswa.find(s =>
                s.nama.toLocaleLowerCase('id') === namaSiswa.toLocaleLowerCase('id') ||
                s.nis.toLocaleLowerCase('id') === namaSiswa.toLocaleLowerCase('id')
            );

            if (!siswa) {
                event.preventDefault();
                statusSiswa.textContent = 'Silakan pilih siswa dari hasil pencarian.';
                statusSiswa.className = 'form-text text-danger';
                inputCari.focus();
                return;
            }

            inputSiswaId.value = siswa.id;
        }

        if (inputKategori.value.trim() && !daftarKategori.some(k => k.nama === inputKategori.value.trim())) {
            event.preventDefault();
            statusKategori.textContent = 'Silakan pilih kategori poin dari hasil pencarian.';
            statusKategori.className = 'form-text text-danger';
            inputKategori.focus();
        }
    });

    if (inputCari.value.trim() && inputSiswaId.value) {
        const siswa = daftarSiswa.find(s => s.id === inputSiswaId.value);

        if (siswa) {
            statusSiswa.textContent = 'Siswa terpilih: ' + siswa.nama + ' (NIS: ' + (siswa.nis || '-') + ')';
            statusSiswa.className = 'form-text text-success';
        }
    }

    if (daftarKategori.some(k => k.nama === inputKategori.value)) {
        statusKategori.textContent = 'Kategori poin terpilih: ' + inputKategori.value;
        statusKategori.className = 'form-text text-success';
    }

    if (window.jQuery && document.getElementById('tabelRekap')) {
        const tabel = $('#tabelRekap').DataTable({
            pageLength: 10,
            lengthMenu: [5, 10, 25, 50, 100],
            order: [],
            scrollX: true,
            language: {
                search: 'Cari:',
                searchPlaceholder: 'Cari data rekap poin...',
                lengthMenu: 'Tampilkan _MENU_ data',
                info: 'Menampilkan _START_ sampai _END_ dari _TOTAL_ data',
                infoEmpty: 'Tidak ada data',
                zeroRecords: 'Data tidak ditemukan',
                emptyTable: 'Belum ada data rekap poin',
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
    }
});
</script>
</body>
</html>