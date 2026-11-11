
<?php
include 'includes/cek_session.php';
include 'config/koneksi.php';

$siswa = mysqli_query($koneksi, "SELECT id, nis, nama FROM t_siswa ORDER BY nama ASC");
$kelas = mysqli_query($koneksi, "SELECT id, nama, tingkat, jurusan FROM t_kelas ORDER BY tingkat ASC, nama ASC");
$tahunAjaran = mysqli_query($koneksi, "SELECT id, nama FROM t_tahun_ajaran ORDER BY nama DESC");

if (!$siswa || !$kelas || !$tahunAjaran) {
    die('Gagal mengambil data: ' . htmlspecialchars(mysqli_error($koneksi)));
}

function h($nilai): string
{
    return htmlspecialchars((string) ($nilai ?? ''), ENT_QUOTES, 'UTF-8');
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Penempatan Siswa | SI-TIB</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container py-4 py-md-5">
        <div class="row justify-content-center">
            <div class="col-12 col-md-10 col-lg-8">
                <div class="mb-4">
                    <a href="penempatan_siswa.php" class="btn btn-outline-secondary btn-sm mb-3">
                        <i class="bi bi-arrow-left"></i> Kembali ke Penempatan Siswa
                    </a>
                    <h2 class="fw-bold mb-1"><i class="bi bi-person-plus-fill text-primary"></i> Tambah Penempatan Siswa</h2>
                    <p class="text-secondary mb-0">SI-TIB — Sistem Informasi Tata Tertib</p>
                </div>

                <div class="card border-0 shadow-sm rounded-3">
                    <div class="card-header bg-white py-3">
                        <h5 class="fw-semibold mb-0"><i class="bi bi-card-list"></i> Formulir Penempatan Siswa</h5>
                    </div>

                    <div class="card-body p-3 p-md-4">
                        <form action="proses_simpan_penempatan.php" method="POST">
                            <div class="row g-3">
                                <div class="col-12">
                                    <label for="cari_siswa" class="form-label fw-semibold">Nama Siswa</label>
                                    <div class="dropdown">
                                        <input type="text" class="form-control" id="cari_siswa" placeholder="Cari berdasarkan NIS atau nama siswa..." autocomplete="off" data-bs-toggle="dropdown" aria-expanded="false" required>
                                        <input type="hidden" id="siswa_id" name="siswa_id" required>

                                        <div class="dropdown-menu w-100 p-2 shadow overflow-auto" id="daftar_siswa" style="max-height: 220px;">
                                            <?php while ($row = mysqli_fetch_assoc($siswa)): ?>
                                                <button type="button" class="dropdown-item rounded pilihan-siswa" data-value="<?= (int) $row['id'] ?>" data-label="<?= h($row['nis'] . ' - ' . $row['nama']) ?>">
                                                    <?= h($row['nis'] . ' - ' . $row['nama']) ?>
                                                </button>
                                            <?php endwhile; ?>
                                            <div class="dropdown-item-text text-secondary small d-none" id="siswa_tidak_ditemukan">Siswa tidak ditemukan.</div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-12">
                                    <label for="cari_tahun_ajaran" class="form-label fw-semibold">Tahun Ajaran</label>
                                    <div class="dropdown">
                                        <input type="text" class="form-control" id="cari_tahun_ajaran" placeholder="Cari atau pilih tahun ajaran..." autocomplete="off" data-bs-toggle="dropdown" aria-expanded="false" required>
                                        <input type="hidden" id="tahun_ajaran_id" name="tahun_ajaran_id" required>

                                        <div class="dropdown-menu w-100 p-2 shadow overflow-auto" id="daftar_tahun_ajaran" style="max-height: 220px;">
                                            <?php while ($row = mysqli_fetch_assoc($tahunAjaran)): ?>
                                                <button type="button" class="dropdown-item rounded pilihan-tahun-ajaran" data-value="<?= (int) $row['id'] ?>" data-label="<?= h($row['nama']) ?>">
                                                    <?= h($row['nama']) ?>
                                                </button>
                                            <?php endwhile; ?>
                                            <div class="dropdown-item-text text-secondary small d-none" id="tahun_tidak_ditemukan">Tahun ajaran tidak ditemukan.</div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-12">
                                    <label for="cari_kelas" class="form-label fw-semibold">Kelas</label>
                                    <div class="dropdown">
                                        <input type="text" class="form-control" id="cari_kelas" placeholder="Cari atau pilih kelas..." autocomplete="off" data-bs-toggle="dropdown" aria-expanded="false" required>
                                        <input type="hidden" id="kelas_id" name="kelas_id" required>

                                        <div class="dropdown-menu w-100 p-2 shadow overflow-auto" id="daftar_kelas" style="max-height: 220px;">
                                            <?php while ($row = mysqli_fetch_assoc($kelas)): ?>
                                                <?php $labelKelas = $row['nama'] . ' - ' . $row['tingkat'] . (!empty($row['jurusan']) ? ' ' . $row['jurusan'] : ''); ?>
                                                <button type="button" class="dropdown-item rounded pilihan-kelas" data-value="<?= (int) $row['id'] ?>" data-label="<?= h($labelKelas) ?>">
                                                    <?= h($labelKelas) ?>
                                                </button>
                                            <?php endwhile; ?>
                                            <div class="dropdown-item-text text-secondary small d-none" id="kelas_tidak_ditemukan">Kelas tidak ditemukan.</div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-12 col-md-6">
                                    <label for="tanggal_mulai" class="form-label fw-semibold">Tanggal Mulai</label>
                                    <input type="date" class="form-control" id="tanggal_mulai" name="tanggal_mulai">
                                </div>

                                <div class="col-12 col-md-6">
                                    <label for="tanggal_selesai" class="form-label fw-semibold">Tanggal Selesai</label>
                                    <input type="date" class="form-control" id="tanggal_selesai" name="tanggal_selesai">
                                </div>

                                <div class="col-12 col-md-6">
                                    <label for="status_aktif" class="form-label fw-semibold">Status Aktif</label>
                                    <select class="form-select" id="status_aktif" name="status_aktif" required>
                                        <option value="1" selected>Aktif</option>
                                        <option value="0">Tidak Aktif</option>
                                    </select>
                                </div>
                            </div>

                            <hr class="my-4">

                            <div class="d-flex flex-column flex-sm-row gap-2 justify-content-end">
                                <a href="penempatan_siswa.php" class="btn btn-outline-secondary">
                                    <i class="bi bi-x-circle"></i> Batal
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-save"></i> Simpan Penempatan
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <footer class="text-center text-secondary small mt-4">
                    &copy; <?= date('Y') ?> SI-TIB — Sistem Informasi Tata Tertib
                </footer>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>

    <script>
    document.addEventListener('DOMContentLoaded', function () {
        function aktifkanDropdown(inputId, hiddenId, pilihanSelector, pesanId) {
            const input = document.getElementById(inputId);
            const hidden = document.getElementById(hiddenId);
            const pilihan = document.querySelectorAll(pilihanSelector);
            const pesan = document.getElementById(pesanId);
            const dropdown = bootstrap.Dropdown.getOrCreateInstance(input);

            function filterPilihan() {
                const kataKunci = input.value.toLowerCase().trim();
                let jumlahTampil = 0;

                pilihan.forEach(function (item) {
                    const cocok = item.textContent.toLowerCase().includes(kataKunci);
                    item.classList.toggle('d-none', !cocok);

                    if (cocok) jumlahTampil++;
                });

                pesan.classList.toggle('d-none', jumlahTampil > 0);
                dropdown.show();
            }

            input.addEventListener('input', function () {
                hidden.value = '';
                filterPilihan();
            });

            input.addEventListener('focus', filterPilihan);

            pilihan.forEach(function (item) {
                item.addEventListener('click', function () {
                    input.value = this.dataset.label;
                    hidden.value = this.dataset.value;
                    dropdown.hide();
                });
            });
        }

        aktifkanDropdown(
            'cari_siswa',
            'siswa_id',
            '.pilihan-siswa',
            'siswa_tidak_ditemukan'
        );

        aktifkanDropdown(
            'cari_tahun_ajaran',
            'tahun_ajaran_id',
            '.pilihan-tahun-ajaran',
            'tahun_tidak_ditemukan'
        );

        aktifkanDropdown(
            'cari_kelas',
            'kelas_id',
            '.pilihan-kelas',
            'kelas_tidak_ditemukan'
        );

        document.querySelector('form').addEventListener('submit', function (event) {
            if (
                !document.getElementById('siswa_id').value ||
                !document.getElementById('tahun_ajaran_id').value ||
                !document.getElementById('kelas_id').value
            ) {
                event.preventDefault();
            }
        });
    });
    </script>
</body>
</html>
