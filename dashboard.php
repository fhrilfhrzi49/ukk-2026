<?php
require_once __DIR__ . '/config/koneksi.php';
require_once __DIR__ . '/config/akses.php';
require_once __DIR__ . '/includes/cek_session.php';

wajib_role([
    'admin', 'administrator', 'guru', 'wali_kelas',
    'wali kelas', 'guru_wali_kelas', 'guru sekaligus wali kelas'
]);

$role_login = strtolower(trim((string) ($_SESSION['role'] ?? 'pengguna')));
$nama_login = $_SESSION['nama'] ?? $_SESSION['name'] ?? $_SESSION['username'] ?? 'Pengguna';

$alias_role = [
    'administrator' => 'admin',
    'wali kelas' => 'wali_kelas',
    'guru sekaligus wali kelas' => 'guru_wali_kelas'
];

$role_login = $alias_role[$role_login] ?? $role_login;
$is_wali_kelas_session = !empty($_SESSION['is_wali_kelas']);

$is_admin = $role_login === 'admin';
$is_guru = $role_login === 'guru' && !$is_wali_kelas_session;
$is_wali_kelas = $role_login === 'wali_kelas' || $is_wali_kelas_session;
$is_guru_wali = $role_login === 'guru_wali_kelas' || ($role_login === 'guru' && $is_wali_kelas_session);

if ($is_admin) {
    $role_label = 'Administrator';
} elseif ($role_login === 'wali_kelas') {
    $role_label = 'Wali Kelas';
} elseif ($role_login === 'guru_wali_kelas' || $is_guru_wali) {
    $role_label = 'Guru sekaligus Wali Kelas';
} elseif ($role_login === 'guru') {
    $role_label = 'Guru';
} else {
    $role_label = 'Pengguna';
}

$boleh_catat = $is_admin || $is_guru || $is_guru_wali;
$boleh_tindakan = $is_admin || $is_guru || $is_wali_kelas || $is_guru_wali;
$boleh_laporan = $is_admin || $is_wali_kelas || $is_guru_wali;
$boleh_riwayat = $is_admin || $is_wali_kelas || $is_guru_wali;
$boleh_rekap = $is_admin || $is_wali_kelas || $is_guru_wali;

$userIdLogin = (int) ($_SESSION['user_id'] ?? $_SESSION['id_user'] ?? $_SESSION['id'] ?? 0);
$daftarKelasWali = [];

if ($userIdLogin > 0) {
    $stmt = mysqli_prepare($koneksi, "SELECT
        wk.id AS id_penugasan,
        wk.tahun_ajaran_id,
        wk.kelas_id,
        k.nama AS nama_kelas,
        k.tingkat,
        k.jurusan,
        ta.nama AS tahun_ajaran,
        wk.tanggal_mulai,
        wk.tanggal_selesai
        FROM t_wali_kelas wk
        INNER JOIN t_kelas k ON k.id = wk.kelas_id
        INNER JOIN t_tahun_ajaran ta ON ta.id = wk.tahun_ajaran_id
        WHERE wk.user_id = ? AND wk.status_aktif = 1
        ORDER BY ta.tanggal_mulai DESC, k.nama ASC");

    if ($stmt) {
        mysqli_stmt_bind_param($stmt, 'i', $userIdLogin);
        mysqli_stmt_execute($stmt);
        $hasilKelasWali = mysqli_stmt_get_result($stmt);

        while ($row = mysqli_fetch_assoc($hasilKelasWali)) {
            $daftarKelasWali[] = $row;
        }

        mysqli_stmt_close($stmt);
    }
}

$jumlahKelasWali = count($daftarKelasWali);

if ($jumlahKelasWali > 0 && $role_login === 'guru') {
    $role_label = 'Guru sekaligus Wali Kelas';
}

if ($jumlahKelasWali > 0) {
    $boleh_rekap = true;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - SI-TIB | Sistem Informasi Tata Tertib</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
</head>

<body class="bg-light">
<div class="container-fluid">
    <div class="row">
        <?php include __DIR__ . '/layout/sidebar.php'; ?>

        <main class="col-12 col-md-9 col-lg-10 ms-md-auto p-3 p-lg-4">

            <div class="bg-white rounded-4 shadow-sm border p-4 mb-4">
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                    <div>
                        <span class="badge text-bg-primary rounded-pill px-3 py-2 mb-3">
                            <i class="bi bi-grid-1x2-fill me-1"></i>
                            SISTEM INFORMASI SEKOLAH
                        </span>
                        <h2 class="fw-bold mb-2">Dashboard</h2>
                        <p class="text-secondary mb-0">Selamat datang di SI-TIB — Sistem Informasi Tata Tertib.</p>
                    </div>

                    <div class="d-flex align-items-center gap-3 border rounded-3 p-3">
                        <div class="rounded-3 bg-primary-subtle text-primary p-3">
                            <i class="bi bi-calendar3 fs-4"></i>
                        </div>
                        <div>
                            <div class="small text-secondary">Tanggal Hari Ini</div>
                            <div class="fw-semibold"><?= date('d-m-Y'); ?></div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-body p-3 p-md-4">
                    <div class="d-flex flex-column flex-sm-row align-items-sm-center gap-3">
                        <div class="rounded-circle bg-primary-subtle text-primary p-3 align-self-start">
                            <i class="bi bi-person-circle fs-3"></i>
                        </div>

                        <div class="flex-grow-1">
                            <div class="text-secondary small mb-1">Anda sedang login sebagai</div>
                            <h5 class="fw-bold mb-1">
                                <?= htmlspecialchars((string) $nama_login, ENT_QUOTES, 'UTF-8'); ?>
                            </h5>
                            <span class="badge text-bg-primary rounded-pill">
                                <i class="bi bi-shield-check me-1"></i>
                                <?= htmlspecialchars($role_label, ENT_QUOTES, 'UTF-8'); ?>
                            </span>
                        </div>

                        <div class="text-secondary small">
                            <i class="bi bi-check-circle-fill text-success me-1"></i>
                            Sesi login aktif
                        </div>
                    </div>
                </div>
            </div>

            <?php if ($jumlahKelasWali > 0): ?>
                <div class="card border-0 shadow-sm rounded-4 mb-4">
                    <div class="card-body p-3 p-md-4">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div>
                                <h6 class="fw-bold mb-1">
                                    <i class="bi bi-building text-primary me-2"></i>
                                    Daftar Kelas Anda
                                </h6>
                                <small class="text-secondary">Daftar kelas tanggung jawab Anda.</small>
                            </div>
                            <span class="badge text-bg-primary rounded-pill px-3 py-2">
                                <?= $jumlahKelasWali; ?> Kelas
                            </span>
                        </div>

                        <div class="row g-3">
                            <?php foreach ($daftarKelasWali as $kelasWali): ?>
                                <div class="col-12 col-md-6 col-xl-4">
                                    <div class="border rounded-3 p-3 h-100">
                                        <div class="d-flex align-items-center gap-3 mb-3">
                                            <div class="rounded-3 bg-primary-subtle text-primary p-2">
                                                <i class="bi bi-mortarboard-fill fs-4"></i>
                                            </div>
                                            <div class="flex-grow-1">
                                                <div class="fw-semibold">
                                                    <?= htmlspecialchars($kelasWali['nama_kelas'], ENT_QUOTES, 'UTF-8'); ?>
                                                </div>
                                                <small class="text-secondary">
                                                    Tahun Ajaran <?= htmlspecialchars($kelasWali['tahun_ajaran'], ENT_QUOTES, 'UTF-8'); ?>
                                                </small>
                                            </div>
                                        </div>

                                        <div class="small text-secondary mb-3">
                                            <?= htmlspecialchars($kelasWali['tingkat'] ?? '', ENT_QUOTES, 'UTF-8'); ?>
                                            <?= !empty($kelasWali['jurusan']) ? ' - ' . htmlspecialchars($kelasWali['jurusan'], ENT_QUOTES, 'UTF-8') : ''; ?>
                                        </div>

                                        <a href="kelas_anda.php?id=<?= (int) $kelasWali['id_penugasan']; ?>" class="btn btn-outline-primary btn-sm w-100">
                                            <i class="bi bi-eye me-1"></i>
                                            Lihat Kelas
                                        </a>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <h5 class="fw-bold mb-1">Ringkasan Data</h5>
                    <p class="text-secondary small mb-0">Akses fitur berdasarkan role akun</p>
                </div>
                <span class="badge bg-white text-secondary border rounded-pill px-3 py-2">
                    <i class="bi bi-bar-chart-line me-1"></i>
                    Menu
                </span>
            </div>

            <div class="row g-3 g-lg-4 mb-4">

                <?php if ($is_admin): ?>
                    <?php
                    $menuAdmin = [
                        ['Data Siswa', 'Kelola data siswa dalam sistem.', 'kelola_siswa.php', 'bi-people-fill', 'primary', 'Kelola Siswa'],
                        ['Data Guru', 'Kelola data guru dan akun guru.', 'kelola_guru.php', 'bi-person-workspace', 'success', 'Kelola Guru'],
                        ['Data Kelas', 'Kelola data kelas dalam sistem.', 'kelola_kelas.php', 'bi-building', 'warning', 'Kelola Kelas']
                    ];
                    foreach ($menuAdmin as [$judul, $deskripsi, $link, $ikon, $warna, $tombol]):
                    ?>
                        <div class="col-12 col-sm-6 col-xl-4">
                            <div class="card border-0 shadow-sm rounded-4 h-100">
                                <div class="card-body p-4">
                                    <div class="rounded-3 bg-<?= $warna; ?>-subtle text-<?= $warna; ?> p-3 d-inline-block mb-3">
                                        <i class="bi <?= $ikon; ?> fs-3"></i>
                                    </div>
                                    <h5 class="fw-bold"><?= $judul; ?></h5>
                                    <p class="text-secondary"><?= $deskripsi; ?></p>
                                    <a href="<?= $link; ?>" class="btn btn-outline-<?= $warna; ?> w-100">
                                        <?= $tombol; ?> <i class="bi bi-arrow-right ms-2"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>

                <?php else: ?>
                    <?php
                    $menu = [
                        [$boleh_catat, 'Catat Pelanggaran', 'Catat pelanggaran siswa sesuai ketentuan sekolah.', 'catat_pelanggaran.php', 'bi-clipboard2-check-fill', 'danger', 'Catat Pelanggaran', 'bi-plus-circle'],
                        [$boleh_tindakan, 'Tindakan', 'Buka fitur tindakan terkait pelanggaran siswa.', 'tindakan.php', 'bi-clipboard2-pulse', 'warning', 'Buka Tindakan', 'bi-arrow-right-circle'],
                        [$boleh_laporan, 'Laporan', 'Akses laporan pelanggaran siswa.', 'laporan.php', 'bi-file-earmark-bar-graph', 'primary', 'Lihat Laporan', 'bi-file-earmark-text'],
                        [$boleh_riwayat, 'Riwayat Pelanggaran', 'Lihat riwayat pencatatan pelanggaran siswa.', 'riwayat.php', 'bi-clock-history', 'info', 'Lihat Riwayat', 'bi-list-ul'],
                        [$boleh_rekap, 'Rekap Poin', 'Lihat rekap poin pelanggaran siswa.', 'rekap_poin.php', 'bi-graph-up-arrow', 'success', 'Lihat Rekap Poin', 'bi-bar-chart']
                    ];

                    foreach ($menu as [$boleh, $judul, $deskripsi, $link, $ikon, $warna, $tombol, $ikonTombol]):
                        if (!$boleh) {
                            continue;
                        }
                    ?>
                        <div class="col-12 col-md-6">
                            <div class="card border-0 shadow-sm rounded-4 h-100">
                                <div class="card-body p-4">
                                    <div class="rounded-3 bg-<?= $warna; ?>-subtle text-<?= $warna; ?>-emphasis p-3 d-inline-block mb-3">
                                        <i class="bi <?= $ikon; ?> fs-3"></i>
                                    </div>
                                    <h5 class="fw-bold"><?= $judul; ?></h5>
                                    <p class="text-secondary"><?= $deskripsi; ?></p>
                                    <a href="<?= $link; ?>" class="btn btn-outline-<?= $warna; ?> w-100">
                                        <i class="bi <?= $ikonTombol; ?> me-2"></i>
                                        <?= $tombol; ?>
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>

                    <?php if (!$boleh_catat && !$boleh_tindakan && !$boleh_laporan && !$boleh_riwayat && !$boleh_rekap): ?>
                        <div class="col-12">
                            <div class="alert alert-warning rounded-4">
                                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                                Role akun belum dikenali. Hubungi administrator untuk memeriksa hak akses.
                            </div>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>

            </div>

            <div class="row g-3 g-lg-4">
                <div class="col-12 col-lg-7">
                    <div class="card border-0 shadow-sm rounded-4 h-100">
                        <div class="card-body p-4">
                            <div class="d-flex align-items-center gap-3 mb-3">
                                <div class="rounded-3 bg-info-subtle text-info-emphasis p-3">
                                    <i class="bi bi-info-circle-fill fs-4"></i>
                                </div>
                                <div>
                                    <h5 class="fw-bold mb-1">Informasi Sistem</h5>
                                    <p class="text-secondary small mb-0">SI-TIB — Sistem Informasi Tata Tertib</p>
                                </div>
                            </div>

                            <p class="text-secondary">
                                Dashboard SI-TIB — Sistem Informasi Tata Tertib UKK 2026.
                                Menu yang tersedia disesuaikan dengan role akun yang sedang login.
                            </p>

                            <div class="alert alert-info mb-0">
                                <i class="bi bi-lightbulb me-2"></i>
                                Gunakan menu navigasi untuk membuka fitur yang tersedia bagi akun Anda.
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-lg-5">
                    <div class="card border-0 shadow-sm rounded-4 h-100">
                        <div class="card-body p-4">
                            <h5 class="fw-bold mb-1">Akses Cepat</h5>
                            <p class="text-secondary small mb-3">Pilih fitur yang ingin dibuka.</p>

                            <div class="d-grid gap-2">
                                <?php
                                $aksesCepat = [];

                                if ($is_admin) {
                                    $aksesCepat = [
                                        ['kelola_siswa.php', 'bi-people-fill', 'primary', 'Kelola Data Siswa'],
                                        ['kelola_guru.php', 'bi-person-workspace', 'success', 'Kelola Data Guru'],
                                        ['kelola_kelas.php', 'bi-building', 'warning', 'Kelola Data Kelas']
                                    ];
                                } else {
                                    $aksesCepat = [
                                        [$boleh_catat ? 'catat_pelanggaran.php' : '', 'bi-clipboard2-check', 'primary', 'Catat Pelanggaran', $boleh_catat],
                                        [$boleh_tindakan ? 'tindakan.php' : '', 'bi-clipboard2-pulse', 'warning', 'Tindakan', $boleh_tindakan],
                                        [$boleh_laporan ? 'laporan.php' : '', 'bi-file-earmark-text', 'primary', 'Laporan', $boleh_laporan],
                                        [$boleh_riwayat ? 'riwayat.php' : '', 'bi-clock-history', 'success', 'Riwayat Pelanggaran', $boleh_riwayat],
                                        [$boleh_rekap ? 'rekap_poin.php' : '', 'bi-graph-up', 'info', 'Rekap Poin', $boleh_rekap]
                                    ];
                                }

                                foreach ($aksesCepat as $item):
                                    if (isset($item[4]) && !$item[4]) {
                                        continue;
                                    }
                                ?>
                                    <a href="<?= $item[0]; ?>" class="btn btn-light border rounded-3 text-start p-3">
                                        <i class="bi <?= $item[1]; ?> text-<?= $item[2]; ?> me-2"></i>
                                        <?= $item[3]; ?>
                                        <i class="bi bi-chevron-right float-end mt-1"></i>
                                    </a>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <footer class="text-center text-secondary small mt-4 mt-lg-5 pt-3 pb-2 border-top">
                &copy; 2026 SI-TIB — Sistem Informasi Tata Tertib
            </footer>

        </main>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>