<?php
require_once __DIR__ . '/includes/cek_session.php';
require_once __DIR__ . '/config/akses.php';
require_once __DIR__ . '/config/koneksi.php';

wajib_role(['admin']);

$guru = mysqli_query($koneksi, "SELECT g.id, g.nip, g.nama FROM t_guru g INNER JOIN t_users u ON u.id = g.user_id WHERE g.status_aktif = 1 ORDER BY g.nama ASC");
$kelas = mysqli_query($koneksi, "SELECT id, nama, tingkat, jurusan FROM t_kelas WHERE status_aktif = 1 ORDER BY tingkat ASC, nama ASC");
$tahun = mysqli_query($koneksi, "SELECT id, nama, tanggal_mulai, tanggal_selesai FROM t_tahun_ajaran ORDER BY tanggal_mulai DESC");

if (!$guru || !$kelas || !$tahun) {
    die('Gagal mengambil data: ' . htmlspecialchars(mysqli_error($koneksi), ENT_QUOTES, 'UTF-8'));
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Tambah Wali Kelas | SI-TIB</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container py-4 py-md-5">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
            <div>
                <h3 class="fw-bold mb-1"><i class="bi bi-person-badge text-primary"></i> Tambah Wali Kelas</h3>
                <p class="text-secondary mb-0">Tugaskan guru yang sudah terdaftar sebagai wali kelas.</p>
            </div>
            <a href="kelola_wali_kelas.php" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Kembali
            </a>
        </div>

        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-body p-3 p-md-4 p-lg-5">
                <h5 class="fw-semibold mb-1">Formulir Wali Kelas</h5>
                <p class="text-secondary small mb-4">Pilih guru, kelas, dan tahun ajaran untuk membuat penugasan wali kelas.</p>

                <form action="proses_tambah_wali_kelas.php" method="POST">
                    <div class="mb-3">
                        <label for="cari_guru" class="form-label fw-semibold">Pilih Guru <span class="text-danger">*</span></label>
                        <div class="position-relative">
                            <input type="text" id="cari_guru" class="form-control" placeholder="Ketik nama guru atau NIP..." autocomplete="off" required>
                            <input type="hidden" name="guru_id" id="guru_id">

                            <div id="hasil_guru" class="list-group position-absolute w-100 shadow-sm d-none" style="z-index: 10; max-height: 240px; overflow-y: auto;">
                                <?php while ($row = mysqli_fetch_assoc($guru)): ?>
                                    <button type="button" class="list-group-item list-group-item-action pilihan-guru"
                                        data-id="<?= (int) $row['id']; ?>"
                                        data-nama="<?= htmlspecialchars($row['nama'] ?? 'Nama belum tersedia', ENT_QUOTES, 'UTF-8'); ?>"
                                        data-nip="<?= htmlspecialchars($row['nip'] ?? '-', ENT_QUOTES, 'UTF-8'); ?>">
                                        <div class="fw-semibold"><?= htmlspecialchars($row['nama'] ?? 'Nama belum tersedia', ENT_QUOTES, 'UTF-8'); ?></div>
                                        <small class="text-secondary">NIP: <?= htmlspecialchars($row['nip'] ?? '-', ENT_QUOTES, 'UTF-8'); ?></small>
                                    </button>
                                <?php endwhile; ?>
                            </div>
                        </div>
                        <small id="status_guru" class="text-secondary">Ketik nama atau NIP untuk mencari guru.</small>
                    </div>

                    <hr class="my-4">

                    <div class="mb-3">
                        <label for="kelas_id" class="form-label fw-semibold">Kelas <span class="text-danger">*</span></label>
                        <select name="kelas_id" id="kelas_id" class="form-select" required>
                            <option value="">Pilih Kelas</option>
                            <?php while ($row = mysqli_fetch_assoc($kelas)): ?>
                                <option value="<?= (int) $row['id']; ?>">
                                    <?= htmlspecialchars(trim($row['nama'] . ' - ' . $row['tingkat'] . ' ' . $row['jurusan']), ENT_QUOTES, 'UTF-8'); ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="tahun_ajaran_id" class="form-label fw-semibold">Tahun Ajaran <span class="text-danger">*</span></label>
                        <select name="tahun_ajaran_id" id="tahun_ajaran_id" class="form-select" required>
                            <option value="">Pilih Tahun Ajaran</option>
                            <?php while ($row = mysqli_fetch_assoc($tahun)): ?>
                                <option value="<?= (int) $row['id']; ?>" data-mulai="<?= htmlspecialchars($row['tanggal_mulai'], ENT_QUOTES, 'UTF-8'); ?>" data-selesai="<?= htmlspecialchars($row['tanggal_selesai'], ENT_QUOTES, 'UTF-8'); ?>">
                                    <?= htmlspecialchars($row['nama'], ENT_QUOTES, 'UTF-8'); ?>
                                    (<?= htmlspecialchars($row['tanggal_mulai'], ENT_QUOTES, 'UTF-8'); ?> s/d <?= htmlspecialchars($row['tanggal_selesai'], ENT_QUOTES, 'UTF-8'); ?>)
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="tanggal_mulai" class="form-label fw-semibold">Tanggal Mulai <span class="text-danger">*</span></label>
                            <input type="date" name="tanggal_mulai" id="tanggal_mulai" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="tanggal_selesai" class="form-label fw-semibold">Tanggal Selesai <span class="text-danger">*</span></label>
                            <input type="date" name="tanggal_selesai" id="tanggal_selesai" class="form-control" required>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label for="status_aktif" class="form-label fw-semibold">Status Penugasan <span class="text-danger">*</span></label>
                        <select name="status_aktif" id="status_aktif" class="form-select" required>
                            <option value="1" selected>Aktif</option>
                            <option value="0">Tidak Aktif</option>
                        </select>
                    </div>

                    <div class="d-flex flex-wrap gap-2">
                        <button type="submit" class="btn btn-primary px-4">
                            <i class="bi bi-save me-1"></i> Simpan Data
                        </button>
                        <a href="kelola_wali_kelas.php" class="btn btn-outline-secondary px-4">Batal</a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        const cariGuru = document.getElementById('cari_guru');
        const guruId = document.getElementById('guru_id');
        const hasilGuru = document.getElementById('hasil_guru');
        const statusGuru = document.getElementById('status_guru');
        const pilihanGuru = document.querySelectorAll('.pilihan-guru');

        cariGuru.addEventListener('input', function () {
            const kata = this.value.toLowerCase().trim();
            guruId.value = '';

            hasilGuru.classList.remove('d-none');

            let jumlah = 0;

            pilihanGuru.forEach(function (item) {
                const nama = item.dataset.nama.toLowerCase();
                const nip = item.dataset.nip.toLowerCase();
                const cocok = nama.includes(kata) || nip.includes(kata);

                item.classList.toggle('d-none', !cocok);

                if (cocok) jumlah++;
            });

            statusGuru.textContent = jumlah
                ? jumlah + ' guru ditemukan.'
                : 'Guru tidak ditemukan.';
        });

        pilihanGuru.forEach(function (item) {
            item.addEventListener('click', function () {
                guruId.value = this.dataset.id;
                cariGuru.value = this.dataset.nama + ' (NIP: ' + this.dataset.nip + ')';
                hasilGuru.classList.add('d-none');
                statusGuru.textContent = 'Guru berhasil dipilih.';
            });
        });

        document.addEventListener('click', function (event) {
            if (!cariGuru.contains(event.target) && !hasilGuru.contains(event.target)) {
                hasilGuru.classList.add('d-none');
            }
        });

        cariGuru.addEventListener('focus', function () {
            if (this.value && !guruId.value) {
                hasilGuru.classList.remove('d-none');
            }
        });

        const pilihanTahun = document.getElementById('tahun_ajaran_id');
        const inputMulai = document.getElementById('tanggal_mulai');
        const inputSelesai = document.getElementById('tanggal_selesai');

        pilihanTahun.addEventListener('change', function () {
            const opsi = this.options[this.selectedIndex];
            inputMulai.value = opsi.value ? opsi.dataset.mulai || '' : '';
            inputSelesai.value = opsi.value ? opsi.dataset.selesai || '' : '';
        });
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>