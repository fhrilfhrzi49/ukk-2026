
<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$roleAsli = strtolower(trim((string) ($_SESSION['role'] ?? 'pengguna')));
$role = str_replace([' ', '-'], '_', $roleAsli);
$halamanAktif = basename($_SERVER['PHP_SELF'] ?? '');
$isWaliKelasSession = !empty($_SESSION['is_wali_kelas']);

$menuLaporan = ['laporan.php', 'bi-file-earmark-bar-graph-fill', 'Laporan'];

$menuAdmin = [
    ['dashboard.php', 'bi-speedometer2', 'Dashboard'],
    ['kelola_siswa.php', 'bi-people-fill', 'Data Siswa'],
    ['kelola_guru.php', 'bi-person-badge-fill', 'Data Guru'],
    ['kelola_kelas.php', 'bi-building', 'Data Kelas'],
    ['kelola_tahun_ajaran.php', 'bi-calendar3', 'Data Tahun Ajaran'],
    ['kelola_wali_kelas.php', 'bi-person-workspace', 'Data Wali Kelas'],
    ['penempatan_siswa.php', 'bi-person-lines-fill', 'Penempatan Siswa'],
    ['kelola_pelanggaran_kategori.php', 'bi-exclamation-triangle-fill', 'Kategori Pelanggaran'],
    $menuLaporan,
    ['tentang_saya.php', 'bi-info-circle-fill', 'Tentang Saya']
];

$menuGuru = [
    ['dashboard.php', 'bi-speedometer2', 'Dashboard'],
    ['catat_pelanggaran.php', 'bi-pencil-square', 'Catat Pelanggaran'],
    ['tindakan.php', 'bi-clipboard-check', 'Tindakan'],
    ['riwayat.php', 'bi-clock-history', 'Riwayat'],
    ['rekap_poin.php', 'bi-bar-chart-fill', 'Rekap Poin']
];

$menuGuruWaliKelas = array_merge($menuGuru, [$menuLaporan]);

$menuWaliKelas = [
    ['dashboard.php', 'bi-speedometer2', 'Dashboard'],
    ['riwayat.php', 'bi-clock-history', 'Riwayat Pelanggaran'],
    ['rekap_poin.php', 'bi-bar-chart-fill', 'Rekap Poin'],
    $menuLaporan
];

$aliasRole = [
    'administrator' => 'admin',
    'wali_kelas' => 'wali_kelas',
    'guru_wali_kelas' => 'guru_wali_kelas',
    'guru_sekaligus_wali_kelas' => 'guru_wali_kelas'
];

$role = $aliasRole[$role] ?? $role;

if ($role === 'admin') {
    $daftarMenu = $menuAdmin;
    $namaRole = 'Administrator';
} elseif ($role === 'wali_kelas') {
    $daftarMenu = $menuWaliKelas;
    $namaRole = 'Wali Kelas';
} elseif ($role === 'guru_wali_kelas' || ($role === 'guru' && $isWaliKelasSession)) {
    $daftarMenu = $menuGuruWaliKelas;
    $namaRole = 'Guru & Wali Kelas';
} elseif ($role === 'guru') {
    $daftarMenu = $menuGuru;
    $namaRole = 'Guru';
} else {
    $daftarMenu = [];
    $namaRole = 'Pengguna';
}

$namaPengguna = '';

foreach (['nama', 'name', 'nama_lengkap', 'full_name', 'username'] as $kunciNama) {
    if (!empty($_SESSION[$kunciNama]) && is_string($_SESSION[$kunciNama])) {
        $namaPengguna = trim($_SESSION[$kunciNama]);
        break;
    }
}

$namaPengguna = $namaPengguna ?: $namaRole;
$badgeRole = in_array($namaRole, ['Administrator', 'Wali Kelas']) ? 'bg-primary' : 'bg-secondary';

if ($namaRole === 'Guru') {
    $badgeRole = 'bg-success';
} elseif ($namaRole === 'Guru & Wali Kelas') {
    $badgeRole = 'bg-info text-dark';
}

function eSidebar($teks)
{
    return htmlspecialchars((string) $teks, ENT_QUOTES, 'UTF-8');
}
?>

<style>
.sidebar-hover .nav-link {
    transition: background-color 0.2s ease, color 0.2s ease;
}

.sidebar-hover .nav-link:not(.active):not(.text-danger):hover {
    background-color: rgba(13, 110, 253, 0.3) !important;
    color: #ffffff !important;
}

.sidebar-hover .nav-link:not(.active):hover i {
    color: #ffffff !important;
}

.sidebar-hover .nav-link.text-danger:hover {
    background-color: rgba(220, 53, 69, 0.3) !important;
    color: #ff6b6b !important;
}

.sidebar-hover .nav-link.text-danger:hover i {
    color: #ff6b6b !important;
}
</style>

<aside class="position-fixed top-0 start-0 bottom-0 vh-100 overflow-y-auto bg-dark text-white p-2 d-none d-md-flex flex-column shadow sidebar-hover"
       style="width: 225px; z-index: 1030;">

    <div class="d-flex align-items-center gap-2 mb-3 pb-3 border-bottom border-secondary">
        <div class="bg-primary rounded-3 p-2 flex-shrink-0">
            <i class="bi bi-shield-check text-white fs-4"></i>
        </div>
        <div>
            <h6 class="text-white fw-bold mb-1">SI-TIB</h6>
            <small class="text-white-50" style="font-size: 10px;">Sistem Informasi Tata Tertib</small>
        </div>
    </div>

    <div class="bg-secondary bg-opacity-25 rounded-3 p-2 mb-3">
        <div class="d-flex align-items-center gap-2">
            <i class="bi bi-person-circle text-info fs-4 flex-shrink-0"></i>
            <div class="text-truncate">
                <div class="text-white fw-semibold text-truncate small" title="<?= eSidebar($namaPengguna); ?>">
                    <?= eSidebar($namaPengguna); ?>
                </div>
                <span class="badge <?= eSidebar($badgeRole); ?>" style="font-size: 10px;">
                    <?= eSidebar($namaRole); ?>
                </span>
            </div>
        </div>
    </div>

    <div class="text-uppercase text-white-50 fw-bold mb-2" style="font-size: 10px;">
        Menu <?= eSidebar($namaRole); ?>
    </div>

    <ul class="nav nav-pills flex-column gap-1">
        <?php foreach ($daftarMenu as $item): ?>
            <?php
            [$url, $ikon, $label] = $item;
            $aktif = $halamanAktif === $url;
            ?>
            <li class="nav-item">
                <a href="<?= eSidebar($url); ?>"
                   class="nav-link d-flex align-items-center gap-2 rounded-3 py-2 px-2 small <?= $aktif ? 'active shadow-sm' : 'text-white-50'; ?>"
                   <?= $aktif ? 'aria-current="page"' : ''; ?>>
                    <i class="bi <?= eSidebar($ikon); ?> fs-6 flex-shrink-0"></i>
                    <span><?= eSidebar($label); ?></span>
                </a>
            </li>
        <?php endforeach; ?>
    </ul>

    <div class="flex-grow-1"></div>
    <hr class="border-secondary my-2">

    <ul class="nav nav-pills flex-column">
        <li class="nav-item">
            <a href="#" class="nav-link d-flex align-items-center gap-2 rounded-3 text-danger py-2 px-2 small"
               data-bs-toggle="modal" data-bs-target="#modalLogout">
                <i class="bi bi-box-arrow-right fs-6 flex-shrink-0"></i>
                <span>Logout</span>
            </a>
        </li>
    </ul>

    <div class="text-center text-white-50 mt-3 pb-1" style="font-size: 9px;">
        &copy; 2026 SI-TIB<br>
        Sistem Informasi Tata Tertib
    </div>
</aside>

<div class="d-md-none bg-dark text-white p-3 shadow sidebar-hover">
    <div class="d-flex align-items-center justify-content-between">
        <div class="d-flex align-items-center gap-2">
            <i class="bi bi-shield-check text-primary fs-3"></i>
            <div>
                <h5 class="mb-0 fw-bold">SI-TIB</h5>
                <small class="text-white-50">Sistem Informasi Tata Tertib</small>
            </div>
        </div>
        <button class="btn btn-outline-light" type="button" data-bs-toggle="collapse"
                data-bs-target="#sidebarMobile" aria-expanded="false" aria-controls="sidebarMobile">
            <i class="bi bi-list fs-4"></i>
        </button>
    </div>

    <div class="collapse mt-3" id="sidebarMobile">
        <div class="bg-secondary bg-opacity-25 rounded-3 p-3 mb-3">
            <div class="d-flex align-items-center gap-2">
                <i class="bi bi-person-circle text-info fs-2"></i>
                <div class="text-truncate">
                    <div class="fw-semibold text-truncate"><?= eSidebar($namaPengguna); ?></div>
                    <span class="badge <?= eSidebar($badgeRole); ?>"><?= eSidebar($namaRole); ?></span>
                </div>
            </div>
        </div>

        <div class="text-uppercase text-white-50 small fw-bold mb-2">
            Menu <?= eSidebar($namaRole); ?>
        </div>

        <ul class="nav nav-pills flex-column gap-2">
            <?php foreach ($daftarMenu as $item): ?>
                <?php
                [$url, $ikon, $label] = $item;
                $aktif = $halamanAktif === $url;
                ?>
                <li class="nav-item">
                    <a href="<?= eSidebar($url); ?>"
                       class="nav-link d-flex align-items-center gap-3 <?= $aktif ? 'active' : 'text-white-50'; ?>"
                       <?= $aktif ? 'aria-current="page"' : ''; ?>>
                        <i class="bi <?= eSidebar($ikon); ?> fs-5"></i>
                        <?= eSidebar($label); ?>
                    </a>
                </li>
            <?php endforeach; ?>

            <li class="nav-item mt-2">
                <a href="#" class="nav-link text-danger" data-bs-toggle="modal" data-bs-target="#modalLogout">
                    <i class="bi bi-box-arrow-right me-2"></i>
                    Logout
                </a>
            </li>
        </ul>

        <div class="text-center text-white-50 small mt-4">
            &copy; 2026 SI-TIB — Sistem Informasi Tata Tertib
        </div>
    </div>
</div>

<div class="modal fade" id="modalLogout" tabindex="-1" aria-labelledby="judulModalLogout" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 rounded-4 shadow">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold" id="judulModalLogout">
                    <i class="bi bi-box-arrow-right text-danger me-2"></i>
                    Konfirmasi Logout
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>

            <div class="modal-body py-4">
                <p class="mb-2">Apakah Anda yakin ingin keluar dari akun SI-TIB?</p>
                <div class="alert alert-warning mb-0">
                    <i class="bi bi-info-circle me-2"></i>
                    Anda harus login kembali untuk mengakses sistem.
                </div>
            </div>

            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-light border" data-bs-dismiss="modal">
                    Batal
                </button>
                <a href="logout.php" class="btn btn-danger">
                    <i class="bi bi-box-arrow-right me-1"></i>
                    Ya, Logout
                </a>
            </div>
        </div>
    </div>
</div>