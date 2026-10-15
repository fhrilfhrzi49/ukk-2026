<?php

session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

require_once "config/koneksi.php";

$role =$_SESSION['role'] ?? '';
$user_id =$_SESSION['user_id'];

$is_wali_kelas = false;

/* CEK GURU SEBAGAI WALI KELAS */
if ($role === 'guru') {$sql_wali = "
        SELECT wk.id
        FROM wali_kelas wk
        INNER JOIN guru g ON g.id = wk.id_guru
        INNER JOIN users u ON u.email = g.email
        WHERE u.id = ?
        LIMIT 1
    ";

    $stmt_wali = mysqli_prepare($koneksi,$sql_wali);
    if ($stmt_wali) {
        mysqli_stmt_bind_param($stmt_wali, "i", $user_id);
        mysqli_stmt_execute($stmt_wali);
        $hasil_wali = mysqli_stmt_get_result($stmt_wali);

        if (mysqli_num_rows($hasil_wali) > 0) {$is_wali_kelas = true;
        }
        mysqli_stmt_close($stmt_wali);
    }
}

// Validasi hak akses halaman tambah laporan
if (
    $role !== 'admin' &&
    !($role === 'guru' && $is_wali_kelas) &&$role !== 'wali_kelas'
) {
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
                <p class="text-secondary mb-4">Anda tidak memiliki akses untuk menambah laporan.</p>
                <a href="dashboard.php" class="btn btn-dark px-4">Kembali ke Dashboard</a>
            </div>
        </div>
    </div>
    </body>
    </html>
    <?php
    exit;
}

// Deteksi nama tabel jenis pelanggaran yang tersedia
$tabel_pelanggaran = '';
$cek_tabel = mysqli_query($koneksi, "SHOW TABLES");
while ($t = mysqli_fetch_array($cek_tabel)) {
    $tabel =$t[0];
    if (in_array($tabel, ['pelanggaran', 'jenis_pelanggaran'])) {
        $tabel_pelanggaran =$tabel;
        break;
    }
}

$pesan_error = "";

// Proses penyimpanan data laporan pelanggaran baru
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $siswa_id       = trim($_POST['siswa_id'] ?? '');
    $pelanggaran_id = trim($_POST['pelanggaran_id'] ?? '');
    $tanggal        = trim($_POST['tanggal'] ?? date('Y-m-d'));
    $keterangan     = trim($_POST['keterangan'] ?? '');

    if (empty($siswa_id) || empty($pelanggaran_id)) {$pesan_error = "Siswa dan Jenis Pelanggaran wajib dipilih!";
    } else {
        // Ambil data siswa untuk mendapatkan nama siswa
        $q_s = mysqli_query($koneksi, "SELECT * FROM siswa WHERE id = '$siswa_id'");
        $d_s = mysqli_fetch_assoc($q_s);$nama_siswa = $d_s['nama'] ?? ($d_s['name'] ?? 'Siswa');

        // Ambil data jenis pelanggaran untuk mendapatkan poin & nama pelanggaran
        $d_p = [];
        if ($tabel_pelanggaran) {
            $q_p = mysqli_query($koneksi, "SELECT * FROM `$tabel_pelanggaran` WHERE id = '$pelanggaran_id'");
            if ($q_p) {
                $d_p = mysqli_fetch_assoc($q_p);
            }
        }
        $nama_pelanggaran = $d_p['nama'] ?? ($d_p['nama_pelanggaran'] ?? 'Pelanggaran');
        $poin =$d_p['poin'] ?? 0;

        // Coba insert ke tabel pelanggaran_siswa
        $stmt = mysqli_prepare($koneksi, "INSERT INTO pelanggaran_siswa (siswa_id, pelanggaran_id, tanggal, keterangan, nama_siswa, nama_pelanggaran, poin) VALUES (?, ?, ?, ?, ?, ?, ?)");
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "iissssi", $siswa_id, $pelanggaran_id,$tanggal, $keterangan,$nama_siswa, $nama_pelanggaran,$poin);
            if (mysqli_stmt_execute($stmt)) {
                header("Location: laporan.php?pesan=sukses_tambah");
                exit;
            } else {
                // Fallback jika struktur kolom tabel berbeda
                $stmt_alt = mysqli_prepare($koneksi, "INSERT INTO siswa_pelanggaran (siswa_id, tanggal, keterangan, poin) VALUES (?, ?, ?, ?)");
                if ($stmt_alt) {
                    mysqli_stmt_bind_param($stmt_alt, "issi", $siswa_id,$tanggal, $keterangan,$poin);
                    if (mysqli_stmt_execute($stmt_alt)) {
                        header("Location: laporan.php?pesan=sukses_tambah");
                        exit;
                    }
                }
                $pesan_error = "Gagal menyimpan laporan ke database: " . mysqli_error($koneksi);
            }
            mysqli_stmt_close($stmt);
        } else {
            $pesan_error = "Terjadi kesalahan pada query database: " . mysqli_error($koneksi);
        }
    }
}

// Ambil data siswa untuk dropdown
$query_siswa = mysqli_query($koneksi, "SELECT * FROM siswa");

// Ambil data jenis pelanggaran untuk dropdown secara aman
$query_pelanggaran = null;
if ($tabel_pelanggaran) {
    $query_pelanggaran = mysqli_query($koneksi, "SELECT * FROM `$tabel_pelanggaran`");
}

?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Laporan Pelanggaran - Sistem Pelanggaran Siswa</title>
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
                    <a href="laporan.php" class="nav-link active">Laporan</a>
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
            <h2 class="mb-2">Tambah Laporan Pelanggaran</h2>
            <p class="text-secondary mb-4">Formulir pencatatan pelanggaran siswa baru.</p>

            <div class="card border-0 shadow-sm rounded-4" style="max-width: 700px;">
                <div class="card-body p-4">

                    <?php if (!empty($pesan_error)): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <?= htmlspecialchars($pesan_error); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>

                    <form action="" method="POST">
                        <div class="mb-3">
                            <label for="siswa_id" class="form-label fw-bold">Pilih Siswa</label>
                            <select class="form-select" id="siswa_id" name="siswa_id" required>
                                <option value="" selected disabled>-- Pilih Siswa --</option>
                                <?php if ($query_siswa && mysqli_num_rows($query_siswa) > 0): ?>
                                    <?php while ($s = mysqli_fetch_assoc($query_siswa)): ?>
                                        <option value="<?= $s['id']; ?>">
                                            <?= htmlspecialchars(($s['nama'] ?? ($s['name'] ?? '')) . ' (NIS: ' . ($s['nis'] ?? '-') . ')'); ?>
                                        </option>
                                    <?php endwhile; ?>
                                <?php endif; ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="pelanggaran_id" class="form-label fw-bold">Jenis Pelanggaran</label>
                            <select class="form-select" id="pelanggaran_id" name="pelanggaran_id" required>
                                <option value="" selected disabled>-- Pilih Jenis Pelanggaran --</option>
                                <?php if ($query_pelanggaran && mysqli_num_rows($query_pelanggaran) > 0): ?>
                                    <?php while ($p = mysqli_fetch_assoc($query_pelanggaran)): ?>
                                        <option value="<?= $p['id']; ?>">
                                            <?= htmlspecialchars(($p['nama'] ?? ($p['nama_pelanggaran'] ?? '')) . ' (' . ($p['poin'] ?? 0) . ' Poin)'); ?>
                                        </option>
                                    <?php endwhile; ?>
                                <?php endif; ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="tanggal" class="form-label fw-bold">Tanggal Kejadian</label>
                            <input type="date" class="form-control" id="tanggal" name="tanggal" value="<?= date('Y-m-d'); ?>" required>
                        </div>

                        <div class="mb-4">
                            <label for="keterangan" class="form-label fw-bold">Keterangan / Tindakan</label>
                            <textarea class="form-control" id="keterangan" name="keterangan" rows="3" placeholder="Masukkan keterangan tambahan atau catatan tindakan"></textarea>
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="laporan.php" class="btn btn-secondary px-4">Kembali</a>
                            <button type="submit" class="btn btn-primary px-4">Simpan Laporan</button>
                        </div>
                    </form>

                </div>
            </div>

        </main>

    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>