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

$pesan_error = "";

// Cek kolom apa saja yang benar-benar ada di tabel wali_kelas
$kolom_tersedia = [];
$res_col = mysqli_query($koneksi, "SHOW COLUMNS FROM wali_kelas");
if ($res_col) {
    while ($row_col = mysqli_fetch_assoc($res_col)) {
        $kolom_tersedia[] =$row_col['Field'];
    }
}

// Proses ketika form disubmit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tahun_ajaran_id = trim($_POST['tahun_ajaran_id'] ?? '');
    $kelas_id        = trim($_POST['kelas_id'] ?? '');
    $guru_id         = trim($_POST['guru_id'] ?? '');
    $tanggal_mulai   = trim($_POST['tanggal_mulai'] ?? '');
    $tanggal_selesai = trim($_POST['tanggal_selesai'] ?? '');
    $status_aktif    = trim($_POST['status_aktif'] ?? 'Aktif');

    if ($tahun_ajaran_id == '') {$pesan_error = "Tahun Ajaran wajib dipilih!";
    } elseif ($kelas_id == '') {$pesan_error = "Kelas wajib dipilih!";
    } elseif ($guru_id == '') {$pesan_error = "Guru (Wali Kelas) wajib dipilih!";
    } else {
        $kolom_k = in_array('id_kelas',$kolom_tersedia) ? 'id_kelas' : 'kelas_id';
        $kolom_g = in_array('id_guru',$kolom_tersedia) ? 'id_guru' : 'guru_id';
        $kolom_ta = in_array('id_tahun_ajaran',$kolom_tersedia) ? 'id_tahun_ajaran' : 'tahun_ajaran_id';

        $fields = ["`$kolom_ta`", "`$kolom_k`", "`$kolom_g`"];
        $values = ["?", "?", "?"];
        $types  = "iii";
        $params = [$tahun_ajaran_id, $kelas_id,$guru_id];

        if (in_array('tanggal_mulai', $kolom_tersedia) and $tanggal_mulai !== '') {$fields[] = "`tanggal_mulai`"; $values[] = "?"; $types .= "s"; $params[] =$tanggal_mulai;
        } elseif (in_array('tgl_mulai', $kolom_tersedia) and $tanggal_mulai !== '') {$fields[] = "`tgl_mulai`"; $values[] = "?"; $types .= "s"; $params[] =$tanggal_mulai;
        }

        if (in_array('tanggal_selesai', $kolom_tersedia) and $tanggal_selesai !== '') {$fields[] = "`tanggal_selesai`"; $values[] = "?"; $types .= "s"; $params[] =$tanggal_selesai;
        } elseif (in_array('tgl_selesai', $kolom_tersedia) and $tanggal_selesai !== '') {$fields[] = "`tgl_selesai`"; $values[] = "?"; $types .= "s"; $params[] =$tanggal_selesai;
        }

        if (in_array('status_aktif', $kolom_tersedia)) {$fields[] = "`status_aktif`"; $values[] = "?"; $types .= "s"; $params[] =$status_aktif;
        } elseif (in_array('status', $kolom_tersedia)) {$fields[] = "`status`"; $values[] = "?"; $types .= "s"; $params[] =$status_aktif;
        }

        $sql = "INSERT INTO wali_kelas (" . implode(", ", $fields) . ") VALUES (" . implode(", ", $values) . ")";
        $stmt = mysqli_prepare($koneksi,$sql);
        
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, $types, ...$params);
            if (mysqli_stmt_execute($stmt)) {
                header("Location: wali_kelas.php?pesan=sukses_tambah");
                exit;
            } else {
                $pesan_error = "Gagal menyimpan data ke database: " . mysqli_error($koneksi);
            }
            mysqli_stmt_close($stmt);
        } else {
            $pesan_error = "Terjadi kesalahan pada query: " . mysqli_error($koneksi);
        }
    }
}

// Ambil data untuk dropdown Tahun Ajaran
$query_ta = mysqli_query($koneksi, "SELECT * FROM tahun_ajaran");

// Ambil data untuk dropdown Kelas
$query_kelas = mysqli_query($koneksi, "SELECT * FROM kelas");

// Ambil data untuk dropdown Guru
$query_guru = mysqli_query($koneksi, "SELECT * FROM guru");

?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Wali Kelas - Sistem Pelanggaran Siswa</title>
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
                    <a href="wali_kelas.php" class="nav-link active">Wali Kelas</a>
                </li>
                <li class="nav-item mb-2">
                    <a href="penempatan_siswa.php" class="nav-link text-white">Penempatan Siswa</a>
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
            <h2 class="mb-2">Tambah Data Wali Kelas</h2>
            <p class="text-secondary mb-4">Formulir penambahan penugasan wali kelas baru.</p>

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
                            <label for="tahun_ajaran_id" class="form-label fw-bold">Tahun Ajaran</label>
                            <select class="form-select" id="tahun_ajaran_id" name="tahun_ajaran_id" required>
                                <option value="" selected disabled>-- Pilih Tahun Ajaran --</option>
                                <?php if ($query_ta and mysqli_num_rows($query_ta) > 0): ?>
                                    <?php while ($ta = mysqli_fetch_assoc($query_ta)):$id_ta = isset($ta['id']) ?$ta['id'] : ($ta['id_tahun_ajaran'] ?? 0);$nama_ta = '-';
                                        if (!empty($ta['tahun_ajaran'])) { $nama_ta =$ta['tahun_ajaran']; }
                                        elseif (!empty($ta['nama'])) { $nama_ta =$ta['nama']; }
                                        elseif (!empty($ta['tahun'])) { $nama_ta =$ta['tahun']; }
                                    ?>
                                        <option value="<?= $id_ta; ?>">
                                            <?= htmlspecialchars($nama_ta); ?>
                                        </option>
                                    <?php endwhile; ?>
                                <?php endif; ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="kelas_id" class="form-label fw-bold">Kelas</label>
                            <select class="form-select" id="kelas_id" name="kelas_id" required>
                                <option value="" selected disabled>-- Pilih Kelas --</option>
                                <?php if ($query_kelas and mysqli_num_rows($query_kelas) > 0): ?>
                                    <?php while ($k = mysqli_fetch_assoc($query_kelas)):$id_k = isset($k['id']) ?$k['id'] : ($k['id_kelas'] ?? 0);$nama_k = '-';
                                        if (!empty($k['nama_kelas'])) { $nama_k =$k['nama_kelas']; }
                                        elseif (!empty($k['kelas'])) { $nama_k =$k['kelas']; }
                                        elseif (!empty($k['name'])) {$nama_k = $k['name']; }$tingkat = isset($k['tingkat']) ?$k['tingkat'] : '';
                                    ?>
                                        <option value="<?= $id_k; ?>">
                                            <?= htmlspecialchars($nama_k . ($tingkat ? ' (' . $tingkat . ')' : '')); ?>
                                        </option>
                                    <?php endwhile; ?>
                                <?php endif; ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="guru_id" class="form-label fw-bold">Guru (Wali Kelas)</label>
                            <select class="form-select" id="guru_id" name="guru_id" required>
                                <option value="" selected disabled>-- Pilih Guru --</option>
                                <?php if ($query_guru and mysqli_num_rows($query_guru) > 0): ?>
                                    <?php while ($g = mysqli_fetch_assoc($query_guru)):$id_g = isset($g['id']) ?$g['id'] : ($g['id_guru'] ?? 0);$nama_g = '-';
                                        if (!empty($g['nama'])) { $nama_g =$g['nama']; }
                                        elseif (!empty($g['nama_lengkap'])) { $nama_g =$g['nama_lengkap']; }
                                        elseif (!empty($g['nama_guru'])) { $nama_g =$g['nama_guru']; }
                                        elseif (!empty($g['name'])) {$nama_g = $g['name']; }$nip = '-';
                                        if (!empty($g['nip'])) { $nip =$g['nip']; }
                                        elseif (!empty($g['nuptk'])) { $nip =$g['nuptk']; }
                                    ?>
                                        <option value="<?= $id_g; ?>">
                                            <?= htmlspecialchars($nama_g . ' (NIP: ' .$nip . ')'); ?>
                                        </option>
                                    <?php endwhile; ?>
                                <?php endif; ?>
                            </select>
                        </div>

                        <?php if (in_array('tanggal_mulai', $kolom_tersedia) or in_array('tgl_mulai',$kolom_tersedia)): ?>
                        <div class="mb-3">
                            <label for="tanggal_mulai" class="form-label fw-bold">Tanggal Mulai</label>
                            <input type="date" class="form-control" id="tanggal_mulai" name="tanggal_mulai">
                        </div>
                        <?php endif; ?>

                        <?php if (in_array('tanggal_selesai', $kolom_tersedia) or in_array('tgl_selesai',$kolom_tersedia)): ?>
                        <div class="mb-3">
                            <label for="tanggal_selesai" class="form-label fw-bold">Tanggal Selesai</label>
                            <input type="date" class="form-control" id="tanggal_selesai" name="tanggal_selesai">
                        </div>
                        <?php endif; ?>

                        <?php if (in_array('status_aktif', $kolom_tersedia) or in_array('status',$kolom_tersedia)): ?>
                        <div class="mb-4">
                            <label for="status_aktif" class="form-label fw-bold">Status Aktif</label>
                            <select class="form-select" id="status_aktif" name="status_aktif" required>
                                <option value="Aktif" selected>Aktif</option>
                                <option value="Tidak Aktif">Tidak Aktif</option>
                            </select>
                        </div>
                        <?php endif; ?>

                        <div class="d-flex justify-content-between">
                            <a href="wali_kelas.php" class="btn btn-secondary px-4">Kembali</a>
                            <button type="submit" class="btn btn-primary px-4">Simpan Wali Kelas</button>
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