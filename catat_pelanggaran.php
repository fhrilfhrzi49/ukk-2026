
<?php

require_once __DIR__ . '/config/koneksi.php';
require_once __DIR__ . '/includes/cek_session.php';
require_once __DIR__ . '/config/akses.php';

wajib_role(['guru', 'guru_wali_kelas']);

$namaPengguna = $_SESSION['nama']
    ?? $_SESSION['name']
    ?? $_SESSION['username']
    ?? 'Pengguna';

$pesanSukses = $_SESSION['pesan_sukses'] ?? '';
$pesanError = $_SESSION['pesan_error'] ?? '';

unset($_SESSION['pesan_sukses'], $_SESSION['pesan_error']);

if (!function_exists('e')) {
    function e($nilai): string
    {
        return htmlspecialchars((string) $nilai, ENT_QUOTES, 'UTF-8');
    }
}

$daftarSiswa = [];
$daftarKategori = [];
$daftarJenis = [];

$errorData = '';

$siswa = mysqli_query(
    $koneksi,
    "SELECT id, nis, nama
     FROM t_siswa
     WHERE status_aktif = 1
     ORDER BY nama ASC"
);

if ($siswa) {
    while ($baris = mysqli_fetch_assoc($siswa)) {
        $daftarSiswa[] = $baris;
    }
} else {
    $errorData = 'Gagal mengambil data siswa: ' . mysqli_error($koneksi);
}

$kategori = mysqli_query(
    $koneksi,
    "SELECT id, nama, deskripsi
     FROM t_pelanggaran_kategori
     WHERE status_aktif = 1
     ORDER BY nama ASC"
);

if ($kategori) {
    while ($baris = mysqli_fetch_assoc($kategori)) {
        $daftarKategori[] = $baris;
    }
} else {
    $errorData = 'Gagal mengambil kategori pelanggaran: ' . mysqli_error($koneksi);
}

$jenis = mysqli_query(
    $koneksi,
    "SELECT
        jp.id,
        jp.kategori_id,
        jp.nama_pelanggaran,
        jp.deskripsi,
        jp.poin,
        pk.nama AS nama_kategori
     FROM t_jenis_pelanggaran jp
     INNER JOIN t_pelanggaran_kategori pk
        ON jp.kategori_id = pk.id
     WHERE jp.status_aktif = 1
       AND pk.status_aktif = 1
     ORDER BY pk.nama ASC, jp.nama_pelanggaran ASC"
);

if ($jenis) {
    while ($baris = mysqli_fetch_assoc($jenis)) {
        $daftarJenis[] = $baris;
    }
} else {
    $errorData = 'Gagal mengambil jenis pelanggaran: ' . mysqli_error($koneksi);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $siswaId = filter_input(INPUT_POST, 'siswa_id', FILTER_VALIDATE_INT);
    $kategoriId = filter_input(INPUT_POST, 'kategori_id', FILTER_VALIDATE_INT);
    $jenisId = filter_input(INPUT_POST, 'jenis_pelanggaran_id', FILTER_VALIDATE_INT);

    $tanggal = trim($_POST['tanggal_pelanggaran'] ?? '');
    $keterangan = trim($_POST['keterangan'] ?? '');

    $tanggalValid = DateTime::createFromFormat('!Y-m-d', $tanggal);

    if (
        !$siswaId ||
        !$kategoriId ||
        !$jenisId ||
        !$tanggalValid ||
        $tanggalValid->format('Y-m-d') !== $tanggal ||
        $tanggal > date('Y-m-d') ||
        $keterangan === '' ||
        mb_strlen($keterangan) > 2000
    ) {
        $_SESSION['pesan_error'] = 'Data belum lengkap atau tanggal tidak valid.';
        header('Location: catat_pelanggaran.php');
        exit;
    }

    $stmt = mysqli_prepare(
        $koneksi,
        "SELECT id
         FROM t_siswa
         WHERE id = ?
           AND status_aktif = 1
         LIMIT 1"
    );

    if (!$stmt) {
        $_SESSION['pesan_error'] = 'Gagal memeriksa data siswa.';
        header('Location: catat_pelanggaran.php');
        exit;
    }

    mysqli_stmt_bind_param($stmt, 'i', $siswaId);
    mysqli_stmt_execute($stmt);

    $hasilSiswa = mysqli_stmt_get_result($stmt);
    $siswaValid = $hasilSiswa && mysqli_num_rows($hasilSiswa) > 0;

    mysqli_stmt_close($stmt);

    if (!$siswaValid) {
        $_SESSION['pesan_error'] = 'Siswa tidak ditemukan atau sudah tidak aktif.';
        header('Location: catat_pelanggaran.php');
        exit;
    }

    $stmt = mysqli_prepare(
        $koneksi,
        "SELECT jp.id
         FROM t_jenis_pelanggaran jp
         INNER JOIN t_pelanggaran_kategori pk
            ON jp.kategori_id = pk.id
         WHERE jp.id = ?
           AND jp.kategori_id = ?
           AND jp.status_aktif = 1
           AND pk.status_aktif = 1
         LIMIT 1"
    );

    if (!$stmt) {
        $_SESSION['pesan_error'] = 'Gagal memeriksa jenis pelanggaran.';
        header('Location: catat_pelanggaran.php');
        exit;
    }

    mysqli_stmt_bind_param($stmt, 'ii', $jenisId, $kategoriId);
    mysqli_stmt_execute($stmt);

    $hasilJenis = mysqli_stmt_get_result($stmt);
    $jenisValid = $hasilJenis && mysqli_num_rows($hasilJenis) > 0;

    mysqli_stmt_close($stmt);

    if (!$jenisValid) {
        $_SESSION['pesan_error'] = 'Jenis pelanggaran tidak sesuai kategori atau sudah tidak aktif.';
        header('Location: catat_pelanggaran.php');
        exit;
    }

    $dicatatOleh = (string) $namaPengguna;

    $stmt = mysqli_prepare(
        $koneksi,
        "INSERT INTO t_catatan_pelanggaran
            (
                siswa_id,
                pelanggaran_id,
                jenis_pelanggaran_id,
                tanggal_pelanggaran,
                keterangan,
                dicatat_oleh
            )
         VALUES (?, NULL, ?, ?, ?, ?)"
    );

    if (!$stmt) {
        $_SESSION['pesan_error'] = 'Gagal menyiapkan penyimpanan catatan pelanggaran: ' . mysqli_error($koneksi);
        header('Location: catat_pelanggaran.php');
        exit;
    }

    mysqli_stmt_bind_param(
        $stmt,
        'iisss',
        $siswaId,
        $jenisId,
        $tanggal,
        $keterangan,
        $dicatatOleh
    );

    if (mysqli_stmt_execute($stmt)) {
        $_SESSION['pesan_sukses'] = 'Catatan pelanggaran berhasil disimpan ke Tindakan.';
    } else {
        $_SESSION['pesan_error'] = 'Catatan pelanggaran gagal disimpan: ' . mysqli_stmt_error($stmt);
    }

    mysqli_stmt_close($stmt);

    header('Location: catat_pelanggaran.php');
    exit;
}

$sqlRiwayat = "
    SELECT
        cp.id,
        cp.tanggal_pelanggaran,
        cp.keterangan,
        cp.dicatat_oleh,
        s.nis,
        s.nama AS nama_siswa,
        COALESCE(jp.nama_pelanggaran, p.nama) AS nama_pelanggaran,
        COALESCE(jp.poin, p.poin, 0) AS poin,
        COALESCE(pk.nama, 'Pelanggaran Lama') AS nama_kategori,
        p.kode
    FROM t_catatan_pelanggaran cp
    LEFT JOIN t_siswa s
        ON cp.siswa_id = s.id
    LEFT JOIN t_jenis_pelanggaran jp
        ON cp.jenis_pelanggaran_id = jp.id
    LEFT JOIN t_pelanggaran_kategori pk
        ON jp.kategori_id = pk.id
    LEFT JOIN t_pelanggaran p
        ON cp.pelanggaran_id = p.id
    ORDER BY cp.id DESC
";

$riwayat = mysqli_query($koneksi, $sqlRiwayat);
$riwayatError = $riwayat ? '' : mysqli_error($koneksi);

?>

<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Catat Pelanggaran | SI-TIB</title>

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
                    <h3 class="fw-bold text-dark mb-1">Catat Pelanggaran</h3>
                    <p class="text-secondary mb-0">
                        Kelola pencatatan dan pantau riwayat pelanggaran siswa.
                    </p>
                </div>

                <div class="d-flex align-items-center gap-2 bg-white rounded-3 shadow-sm px-3 py-2">
                    <div class="bg-primary bg-opacity-10 text-primary rounded-circle p-2">
                        <i class="bi bi-person-fill"></i>
                    </div>
                    <div>
                        <div class="small text-secondary">Login sebagai</div>
                        <div class="fw-semibold text-dark"><?= e($namaPengguna) ?></div>
                    </div>
                </div>
            </div>

            <?php if ($pesanSukses !== ''): ?>
                <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm rounded-3">
                    <i class="bi bi-check-circle-fill me-2"></i>
                    <?= e($pesanSukses) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <?php if ($pesanError !== ''): ?>
                <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm rounded-3">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>
                    <?= e($pesanError) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <?php if ($errorData !== ''): ?>
                <div class="alert alert-danger border-0 shadow-sm rounded-3">
                    <?= e($errorData) ?>
                </div>
            <?php endif; ?>

            <div class="row g-3 mb-4">

                <div class="col-12 col-sm-6 col-xl-4">
                    <div class="card border-0 shadow-sm rounded-4 h-100">
                        <div class="card-body d-flex align-items-center gap-3 p-4">
                            <div class="bg-primary bg-opacity-10 text-primary rounded-3 p-3">
                                <i class="bi bi-people-fill fs-4"></i>
                            </div>
                            <div>
                                <div class="text-secondary small">Siswa Aktif</div>
                                <div class="fs-4 fw-bold"><?= count($daftarSiswa) ?></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-sm-6 col-xl-4">
                    <div class="card border-0 shadow-sm rounded-4 h-100">
                        <div class="card-body d-flex align-items-center gap-3 p-4">
                            <div class="bg-success bg-opacity-10 text-success rounded-3 p-3">
                                <i class="bi bi-tags-fill fs-4"></i>
                            </div>
                            <div>
                                <div class="text-secondary small">Kategori Aktif</div>
                                <div class="fs-4 fw-bold"><?= count($daftarKategori) ?></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-sm-6 col-xl-4">
                    <div class="card border-0 shadow-sm rounded-4 h-100">
                        <div class="card-body d-flex align-items-center gap-3 p-4">
                            <div class="bg-warning bg-opacity-10 text-warning rounded-3 p-3">
                                <i class="bi bi-clipboard-check-fill fs-4"></i>
                            </div>
                            <div>
                                <div class="text-secondary small">Jenis Pelanggaran</div>
                                <div class="fs-4 fw-bold"><?= count($daftarJenis) ?></div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            <?php if (empty($daftarSiswa)): ?>
                <div class="alert alert-warning border-0 shadow-sm rounded-3">
                    Belum ada siswa aktif yang dapat dipilih.
                </div>
            <?php endif; ?>

            <?php if (empty($daftarKategori)): ?>
                <div class="alert alert-warning border-0 shadow-sm rounded-3">
                    Belum ada kategori pelanggaran aktif. Silakan hubungi admin.
                </div>
            <?php endif; ?>

            <div class="card border-0 shadow-sm rounded-4 mb-4">

                <div class="card-header bg-white border-0 px-4 pt-4">
                    <div class="d-flex align-items-center gap-3">
                        <div class="bg-primary bg-opacity-10 text-primary rounded-3 p-3">
                            <i class="bi bi-pencil-square fs-5"></i>
                        </div>
                        <div>
                            <h5 class="fw-bold mb-1">Formulir Pelanggaran</h5>
                            <p class="text-secondary small mb-0">
                                Masukkan informasi kejadian pelanggaran siswa.
                            </p>
                        </div>
                    </div>
                </div>

                <div class="card-body p-4">

                    <form method="post" action="catat_pelanggaran.php" id="formPelanggaran">

                        <div class="row g-4">

                            <div class="col-12 col-md-6">

                                <label for="cari_siswa" class="form-label fw-semibold">
                                    Nama Siswa atau NIS <span class="text-danger">*</span>
                                </label>

                                <div class="position-relative">

                                    <input
                                        type="text"
                                        id="cari_siswa"
                                        class="form-control"
                                        placeholder="Masukkan nama siswa atau NIS"
                                        autocomplete="off"
                                        required
                                    >
                                    
                                    <div
                                        class="list-group position-absolute top-100 start-0 w-100 shadow d-none overflow-auto"
                                        id="hasilSiswa"
                                        style="z-index: 1050; max-height: 260px;"
                                    ></div>

                                </div>

                                <input type="hidden" name="siswa_id" id="siswa_id">

                                <div id="statusSiswa" class="form-text">
                                    Pilih siswa dari hasil pencarian.
                                </div>

                            </div>

                            <div class="col-12 col-md-6">

                                <label for="cari_kategori" class="form-label fw-semibold">
                                    Kategori Pelanggaran <span class="text-danger">*</span>
                                </label>

                                <div class="position-relative">

                                    <input
                                        type="text"
                                        id="cari_kategori"
                                        class="form-control"
                                        placeholder="Masukkan nama kategori pelanggaran"
                                        autocomplete="off"
                                        required
                                    >

                                    <div
                                        class="list-group position-absolute top-100 start-0 w-100 shadow d-none overflow-auto"
                                        id="hasilKategori"
                                        style="z-index: 1050; max-height: 260px;"
                                    ></div>

                                </div>

                                <input type="hidden" name="kategori_id" id="kategori_id">

                                <div id="statusKategori" class="form-text">
                                    Pilih kategori dari hasil pencarian.
                                </div>

                            </div>

                            <div class="col-12 col-md-6">

                                <label for="cari_jenis_pelanggaran" class="form-label fw-semibold">
                                    Jenis Pelanggaran <span class="text-danger">*</span>
                                </label>

                                <div class="position-relative">

                                    <input
                                        type="text"
                                        id="cari_jenis_pelanggaran"
                                        class="form-control"
                                        placeholder="Masukkan nama pelanggaran"
                                        autocomplete="off"
                                        disabled
                                    >

                                    <div
                                        class="list-group position-absolute top-100 start-0 w-100 shadow d-none overflow-auto"
                                        id="hasilJenisPelanggaran"
                                        style="z-index: 1050; max-height: 260px;"
                                    ></div>

                                </div>

                                <input
                                    type="hidden"
                                    name="jenis_pelanggaran_id"
                                    id="jenis_pelanggaran_id"
                                >

                                <div id="statusPelanggaran" class="form-text">
                                    Pilih kategori terlebih dahulu.
                                </div>

                            </div>

                            <div class="col-12 col-md-6">

                                <label for="tampilan_poin" class="form-label fw-semibold">
                                    Poin Pelanggaran
                                </label>

                                <input
                                    type="text"
                                    id="tampilan_poin"
                                    class="form-control bg-light"
                                    value="0 poin"
                                    readonly
                                >

                            </div>

                            <div class="col-12 col-md-6">

                                <label for="tanggal_pelanggaran" class="form-label fw-semibold">
                                    Tanggal Pelanggaran <span class="text-danger">*</span>
                                </label>

                                <input
                                    type="date"
                                    name="tanggal_pelanggaran"
                                    id="tanggal_pelanggaran"
                                    class="form-control"
                                    value="<?= e(date('Y-m-d')) ?>"
                                    max="<?= e(date('Y-m-d')) ?>"
                                    required
                                >

                            </div>

                            <div class="col-12">

                                <label for="keterangan" class="form-label fw-semibold">
                                    Keterangan <span class="text-danger">*</span>
                                </label>

                                <textarea
                                    name="keterangan"
                                    id="keterangan"
                                    class="form-control"
                                    rows="4"
                                    maxlength="2000"
                                    placeholder="Jelaskan kejadian pelanggaran secara jelas..."
                                    required
                                ></textarea>

                            </div>

                        </div>

                        <hr class="my-4">

                        <div class="d-flex flex-wrap gap-2">

                            <button
                                type="submit"
                                class="btn btn-primary px-4"
                                <?= empty($daftarSiswa) || empty($daftarKategori) ? 'disabled' : '' ?>
                            >
                                <i class="bi bi-save me-2"></i>
                                Simpan Pelanggaran
                            </button>

                            <button type="reset" class="btn btn-light border px-4">
                                <i class="bi bi-arrow-counterclockwise me-2"></i>
                                Reset
                            </button>

                        </div>

                    </form>

                </div>
            </div>

            <div class="card border-0 shadow-sm rounded-4">
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

    const daftarSiswa = <?= json_encode(
        array_map(function ($s) {
            return [
                'id' => (string) $s['id'],
                'nis' => (string) ($s['nis'] ?? ''),
                'nama' => (string) ($s['nama'] ?? '')
            ];
        }, $daftarSiswa),
        JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP
    ) ?>;

    const daftarKategori = <?= json_encode(
        array_map(function ($k) {
            return [
                'id' => (string) $k['id'],
                'nama' => (string) $k['nama'],
                'deskripsi' => (string) ($k['deskripsi'] ?? '')
            ];
        }, $daftarKategori),
        JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP
    ) ?>;

    const daftarJenis = <?= json_encode(
        array_map(function ($p) {
            return [
                'id' => (string) $p['id'],
                'kategori_id' => (string) $p['kategori_id'],
                'nama' => (string) $p['nama_pelanggaran'],
                'poin' => (int) $p['poin'],
                'deskripsi' => (string) ($p['deskripsi'] ?? '')
            ];
        }, $daftarJenis),
        JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP
    ) ?>;

    const form = document.getElementById('formPelanggaran');

    const inputCariSiswa = document.getElementById('cari_siswa');
    const inputSiswaId = document.getElementById('siswa_id');
    const hasilSiswa = document.getElementById('hasilSiswa');
    const statusSiswa = document.getElementById('statusSiswa');

    const inputCariKategori = document.getElementById('cari_kategori');
    const kategoriSelect = document.getElementById('kategori_id');
    const hasilKategori = document.getElementById('hasilKategori');
    const statusKategori = document.getElementById('statusKategori');

    const inputCariJenis = document.getElementById('cari_jenis_pelanggaran');
    const inputJenisId = document.getElementById('jenis_pelanggaran_id');
    const hasilJenis = document.getElementById('hasilJenisPelanggaran');
    const statusPelanggaran = document.getElementById('statusPelanggaran');
    const tampilanPoin = document.getElementById('tampilan_poin');

    function buatHasilKosong(container, pesan) {
        container.replaceChildren();

        const item = document.createElement('div');
        item.className = 'list-group-item text-secondary';
        item.textContent = pesan;

        container.appendChild(item);
        container.classList.remove('d-none');
    }

    function tampilkanSiswa() {
        const kataKunci = inputCariSiswa.value.trim().toLocaleLowerCase('id');

        inputSiswaId.value = '';
        statusSiswa.textContent = 'Pilih siswa dari hasil pencarian.';
        statusSiswa.className = 'form-text';
        hasilSiswa.replaceChildren();

        if (!kataKunci) {
            hasilSiswa.classList.add('d-none');
            return;
        }

        const hasil = daftarSiswa.filter(function (s) {
            return s.nama.toLocaleLowerCase('id').includes(kataKunci) ||
                s.nis.toLocaleLowerCase('id').includes(kataKunci);
        }).slice(0, 100);

        if (hasil.length === 0) {
            buatHasilKosong(hasilSiswa, 'Siswa tidak ditemukan.');
            return;
        }

        hasil.forEach(function (s) {
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
                inputCariSiswa.value = s.nama;

                statusSiswa.textContent =
                    'Siswa terpilih: ' + s.nama + ' (NIS: ' + (s.nis || '-') + ')';

                statusSiswa.className = 'form-text text-success';
                hasilSiswa.classList.add('d-none');
            });

            hasilSiswa.appendChild(tombol);
        });

        hasilSiswa.classList.remove('d-none');
    }

    function tampilkanKategori() {
        const kataKunci = inputCariKategori.value.trim().toLocaleLowerCase('id');

        kategoriSelect.value = '';
        inputJenisId.value = '';
        inputCariJenis.value = '';
        inputCariJenis.disabled = true;
        tampilanPoin.value = '0 poin';

        hasilKategori.replaceChildren();
        hasilJenis.classList.add('d-none');

        statusKategori.textContent = 'Pilih kategori dari hasil pencarian.';
        statusKategori.className = 'form-text';

        statusPelanggaran.textContent = 'Pilih kategori terlebih dahulu.';
        statusPelanggaran.className = 'form-text';

        if (!kataKunci) {
            hasilKategori.classList.add('d-none');
            return;
        }

        const hasil = daftarKategori.filter(function (k) {
            return k.nama.toLocaleLowerCase('id').includes(kataKunci) ||
                k.deskripsi.toLocaleLowerCase('id').includes(kataKunci);
        }).slice(0, 100);

        if (hasil.length === 0) {
            buatHasilKosong(hasilKategori, 'Kategori pelanggaran tidak ditemukan.');
            return;
        }

        hasil.forEach(function (k) {
            const tombol = document.createElement('button');
            tombol.type = 'button';
            tombol.className = 'list-group-item list-group-item-action';

            const nama = document.createElement('span');
            nama.className = 'fw-semibold d-block';
            nama.textContent = k.nama;

            const deskripsi = document.createElement('small');
            deskripsi.className = 'text-secondary d-block';
            deskripsi.textContent = k.deskripsi || 'Kategori pelanggaran';

            tombol.append(nama, deskripsi);

            tombol.addEventListener('click', function () {
                kategoriSelect.value = k.id;
                inputCariKategori.value = k.nama;

                statusKategori.textContent = 'Kategori terpilih: ' + k.nama;
                statusKategori.className = 'form-text text-success';

                hasilKategori.classList.add('d-none');

                inputCariJenis.value = '';
                inputJenisId.value = '';
                tampilanPoin.value = '0 poin';

                tampilkanJenisPelanggaran();
            });

            hasilKategori.appendChild(tombol);
        });

        hasilKategori.classList.remove('d-none');
    }

    function tampilkanJenisPelanggaran() {
        const kategoriId = kategoriSelect.value;
        const kataKunci = inputCariJenis.value.trim().toLocaleLowerCase('id');

        inputJenisId.value = '';
        tampilanPoin.value = '0 poin';
        hasilJenis.replaceChildren();

        if (!kategoriId) {
            inputCariJenis.value = '';
            inputCariJenis.disabled = true;
            hasilJenis.classList.add('d-none');
            statusPelanggaran.textContent = 'Pilih kategori terlebih dahulu.';
            statusPelanggaran.className = 'form-text';
            return;
        }

        inputCariJenis.disabled = false;

        const jenisKategori = daftarJenis.filter(function (p) {
            return p.kategori_id === kategoriId;
        });

        if (jenisKategori.length === 0) {
            hasilJenis.classList.add('d-none');
            statusPelanggaran.textContent =
                'Belum ada jenis pelanggaran aktif pada kategori ini.';
            statusPelanggaran.className = 'form-text text-danger';
            return;
        }

        if (!kataKunci) {
            hasilJenis.classList.add('d-none');
            statusPelanggaran.textContent =
                jenisKategori.length + ' jenis pelanggaran tersedia. Ketik nama pelanggaran untuk mencari.';
            statusPelanggaran.className = 'form-text';
            return;
        }

        const hasil = jenisKategori.filter(function (p) {
            return p.nama.toLocaleLowerCase('id').includes(kataKunci) ||
                p.deskripsi.toLocaleLowerCase('id').includes(kataKunci);
        }).slice(0, 100);

        if (hasil.length === 0) {
            buatHasilKosong(hasilJenis, 'Jenis pelanggaran tidak ditemukan.');
            statusPelanggaran.textContent = 'Coba gunakan kata kunci lain.';
            statusPelanggaran.className = 'form-text text-danger';
            return;
        }

        hasil.forEach(function (p) {
            const tombol = document.createElement('button');
            tombol.type = 'button';
            tombol.className = 'list-group-item list-group-item-action';

            const nama = document.createElement('span');
            nama.className = 'fw-semibold d-block';
            nama.textContent = p.nama;

            const detail = document.createElement('small');
            detail.className = 'text-secondary';
            detail.textContent = p.poin + ' poin';

            tombol.append(nama, detail);

            tombol.addEventListener('click', function () {
                inputJenisId.value = p.id;
                inputCariJenis.value = p.nama;
                tampilanPoin.value = p.poin + ' poin';

                statusPelanggaran.textContent =
                    'Jenis pelanggaran dipilih. Poin: ' + p.poin + '.';

                statusPelanggaran.className = 'form-text text-success';
                hasilJenis.classList.add('d-none');
            });

            hasilJenis.appendChild(tombol);
        });

        hasilJenis.classList.remove('d-none');
        statusPelanggaran.textContent = hasil.length + ' hasil ditemukan.';
        statusPelanggaran.className = 'form-text text-success';
    }

    inputCariSiswa.addEventListener('input', tampilkanSiswa);

    inputCariKategori.addEventListener('input', tampilkanKategori);

    inputCariJenis.addEventListener('input', tampilkanJenisPelanggaran);

    document.addEventListener('click', function (event) {
        if (!hasilSiswa.contains(event.target) && event.target !== inputCariSiswa) {
            hasilSiswa.classList.add('d-none');
        }

        if (!hasilKategori.contains(event.target) && event.target !== inputCariKategori) {
            hasilKategori.classList.add('d-none');
        }

        if (!hasilJenis.contains(event.target) && event.target !== inputCariJenis) {
            hasilJenis.classList.add('d-none');
        }
    });

    form.addEventListener('submit', function (event) {

        if (!inputSiswaId.value) {
            event.preventDefault();
            statusSiswa.textContent = 'Silakan cari dan pilih siswa terlebih dahulu.';
            statusSiswa.className = 'form-text text-danger';
            inputCariSiswa.focus();
            return;
        }

        if (!kategoriSelect.value) {
            event.preventDefault();
            statusKategori.textContent = 'Silakan cari dan pilih kategori pelanggaran terlebih dahulu.';
            statusKategori.className = 'form-text text-danger';
            inputCariKategori.focus();
            return;
        }

        if (!inputJenisId.value) {
            event.preventDefault();
            statusPelanggaran.textContent =
                'Silakan cari dan pilih jenis pelanggaran terlebih dahulu.';
            statusPelanggaran.className = 'form-text text-danger';
            inputCariJenis.focus();
        }

    });

    form.addEventListener('reset', function () {
        setTimeout(function () {

            inputSiswaId.value = '';
            inputJenisId.value = '';
            kategoriSelect.value = '';

            inputCariKategori.value = '';
            hasilKategori.classList.add('d-none');

            statusKategori.textContent =
                'Pilih kategori dari hasil pencarian.';
            statusKategori.className = 'form-text';

            statusSiswa.textContent =
                'Pilih siswa dari hasil pencarian.';
            statusSiswa.className = 'form-text';

            hasilSiswa.classList.add('d-none');
            hasilJenis.classList.add('d-none');

            inputCariJenis.value = '';
            inputCariJenis.disabled = true;

            tampilanPoin.value = '0 poin';

            statusPelanggaran.textContent = 'Pilih kategori terlebih dahulu.';
            statusPelanggaran.className = 'form-text';

        }, 0);
    });

    if (window.jQuery && document.getElementById('tabelPelanggaran')) {

        const tabel = $('#tabelPelanggaran').DataTable({
            pageLength: 10,
            lengthMenu: [5, 10, 25, 50, 100],
            order: [],
            columnDefs: [
                {
                    targets: 0,
                    orderable: false,
                    searchable: false
                }
            ],
            language: {
                search: 'Cari:',
                searchPlaceholder: 'Cari riwayat pelanggaran...',
                lengthMenu: 'Tampilkan _MENU_ data',
                info: 'Menampilkan _START_ sampai _END_ dari _TOTAL_ data',
                infoEmpty: 'Tidak ada data',
                infoFiltered: '(difilter dari _MAX_ total data)',
                zeroRecords: 'Data tidak ditemukan',
                emptyTable: 'Belum ada catatan pelanggaran',
                paginate: {
                    first: 'Pertama',
                    last: 'Terakhir',
                    next: 'Berikutnya',
                    previous: 'Sebelumnya'
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
    }

});
</script>

</body>
</html>