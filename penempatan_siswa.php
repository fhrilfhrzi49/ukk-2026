<?php

session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$role =$_SESSION['role'] ?? '';

// Validasi ketat: Hanya Administrator yang dapat mengakses halaman ini
if ($role !== 'admin') {
    ?>
    <!DOCTYPE html>
    <html lang="id">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Akses Ditolak</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    </head>
    <body class="bg-light">
    <div class="min-vh-100 d-flex justify-content-center align-items-center p-3">
        <div class="card border-0 shadow rounded-4 text-center w-100" style="max-width: 400px;">
            <div class="card-body p-5">
                <h2 class="fw-bold mb-3">Halaman Ini Tidak Dapat Dibuka</h2>
                <p class="text-secondary mb-4">Hanya Administrator Yang Dapat Mengakses Halaman Ini.</p>
                <a href="dashboard.php" class="btn btn-dark px-4">Kembali ke Dashboard</a>
            </div>
        </div>
    </div>
    </body>
    </html>
    <?php
    exit;
}

require_once "config/koneksi.php";

// Deteksi nama tabel penempatan siswa yang tersedia di database
$nama_tabel = '';
$cek_tabel = mysqli_query($koneksi, "SHOW TABLES");
while ($t = mysqli_fetch_array($cek_tabel)) {
    $tabel =$t[0];
    if (in_array($tabel, ['kelas_siswa', 'penempatan_siswa', 'siswa_kelas'])) {
        $nama_tabel =$tabel;
        break;
    }
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Penempatan Siswa - Sistem Pelanggaran Siswa</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>

<div class="container-fluid">
    <div class="row">

        <!-- SIDEBAR -->
        <div class="col-md-3 col-lg-2 bg-dark min-vh-100 p-3">
            <h4 class="text-white mb-4">My Website</h4>

            <ul class="nav nav-pills flex-column">
                <li class="nav-item mb-2">
                    <a href="dashboard.php" class="nav-link text-white">Dashboard</a>
                </li>
                <li class="nav-item mb-2">
                    <a href="tahun_ajaran.php" class="nav-link text-white">Tahun Ajaran</a>
                </li>
                <li class="nav-item mb-2">
                    <a href="siswa.php" class="nav-link text-white">Data Siswa</a>
                </li>
                <li class="nav-item mb-2">
                    <a href="guru.php" class="nav-link text-white">Data Guru</a>
                </li>
                <li class="nav-item mb-2">
                    <a href="kelas.php" class="nav-link text-white">Kelas</a>
                </li>
                <li class="nav-item mb-2">
                    <a href="wali_kelas.php" class="nav-link text-white">Wali Kelas</a>
                </li>
                <li class="nav-item mb-2">
                    <a href="penempatan_siswa.php" class="nav-link active">Penempatan Siswa</a>
                </li>
                <li class="nav-item mb-2">
                    <a href="kategori_pelanggaran.php" class="nav-link text-white">Kategori Pelanggaran</a>
                </li>
                <li class="nav-item mb-2">
                    <a href="jenis_pelanggaran.php" class="nav-link text-white">Jenis Pelanggaran</a>
                </li>
                <li class="nav-item mb-2">
                    <a href="laporan.php" class="nav-link text-white">Laporan</a>
                </li>
                <li class="nav-item mb-2">
                    <a
                        href="riwayat.php"
                        class="nav-link text-white"
                    >
                        Riwayat
                    </a>
                </li>

                <li class="nav-item mb-2">
                    <a
                        href="rekap_poin.php"
                        class="nav-link text-white"
                    >
                        Rekap Poin
                    </a>
                </li>
                <li class="nav-item mb-2">
                    <a href="about.php" class="nav-link text-white">About Me</a>
                </li>

                <hr class="text-secondary">

                <li class="nav-item">
                    <p>
                        <a href="logout.php" class="text-white" onclick="return confirm('Yakin ingin logout?');">Logout</a>
                    </p>
                </li>

                <footer class="mt-4 pt-3 border-top text-center text-secondary">
                    <small>© 2026 Sistem Pelanggaran Siswa — Muhamad Fahril Fahrurozi</small>
                </footer>
            </ul>
        </div>

        <!-- KONTEN UTAMA -->
        <main class="col-md-9 col-lg-10 p-4">
            <h2 class="mb-2">Penempatan Siswa</h2>
            <p class="text-secondary mb-4">Kelola data penempatan kelas siswa berdasarkan tahun ajaran.</p>

            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body">
                    
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="card-title fw-bold m-0">Daftar Penempatan Siswa</h5>
                        <a href="tambah_penempatan_siswa.php" class="btn btn-primary btn-sm px-3">
                            + Tambah Penempatan Siswa
                        </a>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-striped table-hover align-middle">
                            <thead class="table-dark">
                                <tr>
                                    <th scope="col">No</th>
                                    <th scope="col">Tahun Ajaran</th>
                                    <th scope="col">Kelas</th>
                                    <th scope="col">Nama Siswa (NIS)</th>
                                    <th scope="col">Tanggal Mulai</th>
                                    <th scope="col">Tanggal Selesai</th>
                                    <th scope="col" class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $no = 1;
                                $query = null;
                                if ($nama_tabel) {
                                    $query = mysqli_query($koneksi, "SELECT * FROM `$nama_tabel`");
                                }
                                
                                if ($query && mysqli_num_rows($query) > 0) {
                                    while ($row = mysqli_fetch_assoc($query)) {
                                        $id_penempatan   =$row['id'] ?? 0;
                                        $tahun_ajaran_id =$row['tahun_ajaran_id'] ?? 0;
                                        $kelas_id        =$row['kelas_id'] ?? 0;
                                        $siswa_id        =$row['siswa_id'] ?? 0;
                                        $tanggal_mulai   =$row['tanggal_mulai'] ?? '-';
                                        $tanggal_selesai =$row['tanggal_selesai'] ?? '-';

                                        // Cari nama tahun ajaran
                                        $ta_nama = '-';
                                        if ($tahun_ajaran_id) {
                                            $q_ta = mysqli_query($koneksi, "SELECT * FROM tahun_ajaran WHERE id = '$tahun_ajaran_id'");
                                            if ($q_ta &&$d_ta = mysqli_fetch_assoc($q_ta)) {$ta_nama = $d_ta['tahun_ajaran'] ?? ($d_ta['nama'] ?? '-');
                                            }
                                        }

                                        // Cari nama kelas
                                        $kelas_nama = '-';
                                        if ($kelas_id) {
                                            $q_k = mysqli_query($koneksi, "SELECT * FROM kelas WHERE id = '$kelas_id'");
                                            if ($q_k && $d_k = mysqli_fetch_assoc($q_k)) {
                                                $kelas_nama = ($d_k['nama_kelas'] ?? ($d_k['nama'] ?? '-')) . ' (' . ($d_k['tingkat'] ?? '') . ')';
                                            }
                                        }

                                        // Cari nama siswa
                                        $siswa_nama = '-';
                                        if ($siswa_id) {
                                            $q_s = mysqli_query($koneksi, "SELECT * FROM siswa WHERE id = '$siswa_id'");
                                            if ($q_s && $d_s = mysqli_fetch_assoc($q_s)) {
                                                $siswa_nama = ($d_s['nama'] ?? ($d_s['name'] ?? '-')) . ' (NIS: ' . ($d_s['nis'] ?? '-') . ')';
                                            }
                                        }
                                ?>
                                        <tr>
                                            <td><?= $no++; ?></td>
                                            <td><?= htmlspecialchars($ta_nama); ?></td>
                                            <td><?= htmlspecialchars($kelas_nama); ?></td>
                                            <td><?= htmlspecialchars($siswa_nama); ?></td>
                                            <td><?= htmlspecialchars($tanggal_mulai); ?></td>
                                            <td><?= htmlspecialchars($tanggal_selesai); ?></td>
                                            <td class="text-center">
                                                <a href="edit_penempatan_siswa.php?id=<?= $id_penempatan; ?>" class="btn btn-warning btn-sm text-white">
                                                    Edit
                                                </a>
                                                <a href="hapus_penempatan_siswa.php?id=<?= $id_penempatan; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Yakin ingin menghapus data penempatan siswa ini?');">
                                                    Hapus
                                                </a>
                                            </td>
                                        </tr>
                                <?php 
                                    }
                                } else { 
                                ?>
                                    <tr>
                                        <td colspan="7" class="text-center text-secondary py-4">
                                            <?php if (!$nama_tabel): ?>
                                                Tabel untuk penempatan siswa (misal: <code>kelas_siswa</code> atau <code>penempatan_siswa</code>) belum dibuat di database Anda. Silakan buat tabel tersebut terlebih dahulu.
                                            <?php else: ?>
                                                Belum ada data penempatan siswa atau tabel kosong.
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>

                </div>
            </div>

        </main>

    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>