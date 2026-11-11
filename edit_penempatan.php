
<?php
include 'includes/cek_session.php';
include 'config/koneksi.php';

$id = (int) ($_GET['id'] ?? 0);
$stmt = mysqli_prepare($koneksi, "SELECT * FROM t_kelas_siswa WHERE id = ?");
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$data = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
mysqli_stmt_close($stmt);

if (!$data) {
    echo "<script>alert('Data penempatan siswa tidak ditemukan!'); window.location='penempatan_siswa.php';</script>";
    exit;
}

$siswa = mysqli_query($koneksi, "SELECT id, nis, nama FROM t_siswa ORDER BY nama ASC");
$kelas = mysqli_query($koneksi, "SELECT id, nama, tingkat, jurusan FROM t_kelas ORDER BY tingkat ASC, nama ASC");
$tahunAjaran = mysqli_query($koneksi, "SELECT id, nama FROM t_tahun_ajaran ORDER BY nama DESC");

$tanggalMulai = ($data['tanggal_mulai'] ?? '') === '0000-00-00' ? '' : ($data['tanggal_mulai'] ?? '');
$tanggalSelesai = ($data['tanggal_selesai'] ?? '') === '0000-00-00' ? '' : ($data['tanggal_selesai'] ?? '');

function e($value): string {
    return htmlspecialchars((string) ($value ?? ''), ENT_QUOTES, 'UTF-8');
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Penempatan Siswa | SI-TIB</title>
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
                <h2 class="fw-bold mb-1">
                    <i class="bi bi-person-gear text-primary"></i> Edit Penempatan Siswa
                </h2>
                <p class="text-secondary mb-0">SI-TIB — Sistem Informasi Tata Tertib</p>
            </div>

            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-header bg-white py-3">
                    <h5 class="fw-semibold mb-0">
                        <i class="bi bi-card-list"></i> Formulir Edit Penempatan Siswa
                    </h5>
                </div>

                <div class="card-body p-3 p-md-4">
                    <form action="proses_edit_penempatan.php" method="POST" id="formPenempatan">
                        <input type="hidden" name="id" value="<?= e($data['id']) ?>">

                        <div class="row g-3">
                            <div class="col-12">
                                <label for="cari_siswa" class="form-label fw-semibold">Nama Siswa</label>
                                <div class="dropdown">
                                    <input type="text" class="form-control" id="cari_siswa" placeholder="Cari atau pilih siswa..." autocomplete="off" data-bs-toggle="dropdown" aria-expanded="false" required>
                                    <input type="hidden" name="siswa_id" id="siswa_id" value="<?= (int) $data['siswa_id'] ?>">
                                    <div class="dropdown-menu w-100 p-2 shadow overflow-auto" id="daftar_siswa" style="max-height: 220px;">
                                        <?php
                                        $namaSiswaTerpilih = '';
                                        mysqli_data_seek($siswa, 0);
                                        while ($row = mysqli_fetch_assoc($siswa)):
                                            $label = $row['nis'] . ' - ' . $row['nama'];
                                            if ((int) $data['siswa_id'] === (int) $row['id']) {
                                                $namaSiswaTerpilih = $label;
                                            }
                                        ?>
                                            <button type="button" class="dropdown-item rounded pilihan-siswa" data-value="<?= (int) $row['id'] ?>" data-label="<?= e($label) ?>"><?= e($label) ?></button>
                                        <?php endwhile; ?>
                                    </div>
                                </div>
                            </div>

                            <div class="col-12">
                                <label for="cari_tahun_ajaran" class="form-label fw-semibold">Tahun Ajaran</label>
                                <div class="dropdown">
                                    <input type="text" class="form-control" id="cari_tahun_ajaran" placeholder="Cari atau pilih tahun ajaran..." autocomplete="off" data-bs-toggle="dropdown" aria-expanded="false" required>
                                    <input type="hidden" name="tahun_ajaran_id" id="tahun_ajaran_id" value="<?= (int) $data['tahun_ajaran_id'] ?>">
                                    <div class="dropdown-menu w-100 p-2 shadow overflow-auto" id="daftar_tahun_ajaran" style="max-height: 220px;">
                                        <?php
                                        $namaTahunTerpilih = '';
                                        mysqli_data_seek($tahunAjaran, 0);
                                        while ($row = mysqli_fetch_assoc($tahunAjaran)):
                                            if ((int) $data['tahun_ajaran_id'] === (int) $row['id']) {
                                                $namaTahunTerpilih = $row['nama'];
                                            }
                                        ?>
                                            <button type="button" class="dropdown-item rounded pilihan-tahun" data-value="<?= (int) $row['id'] ?>" data-label="<?= e($row['nama']) ?>"><?= e($row['nama']) ?></button>
                                        <?php endwhile; ?>
                                    </div>
                                </div>
                            </div>

                            <div class="col-12">
                                <label for="cari_kelas" class="form-label fw-semibold">Kelas</label>
                                <div class="dropdown">
                                    <input type="text" class="form-control" id="cari_kelas" placeholder="Cari atau pilih kelas..." autocomplete="off" data-bs-toggle="dropdown" aria-expanded="false" required>
                                    <input type="hidden" name="kelas_id" id="kelas_id" value="<?= (int) $data['kelas_id'] ?>">
                                    <div class="dropdown-menu w-100 p-2 shadow overflow-auto" id="daftar_kelas" style="max-height: 220px;">
                                        <?php
                                        $namaKelasTerpilih = '';
                                        mysqli_data_seek($kelas, 0);
                                        while ($row = mysqli_fetch_assoc($kelas)):
                                            $label = $row['nama'] . ' - ' . $row['tingkat'] . (!empty($row['jurusan']) ? ' ' . $row['jurusan'] : '');
                                            if ((int) $data['kelas_id'] === (int) $row['id']) {
                                                $namaKelasTerpilih = $label;
                                            }
                                        ?>
                                            <button type="button" class="dropdown-item rounded pilihan-kelas" data-value="<?= (int) $row['id'] ?>" data-label="<?= e($label) ?>"><?= e($label) ?></button>
                                        <?php endwhile; ?>
                                    </div>
                                </div>
                            </div>

                            <div class="col-12 col-md-6">
                                <label for="tanggal_mulai" class="form-label fw-semibold">Tanggal Mulai</label>
                                <input type="date" class="form-control" id="tanggal_mulai" name="tanggal_mulai" value="<?= e($tanggalMulai) ?>">
                            </div>

                            <div class="col-12 col-md-6">
                                <label for="tanggal_selesai" class="form-label fw-semibold">Tanggal Selesai</label>
                                <input type="date" class="form-control" id="tanggal_selesai" name="tanggal_selesai" value="<?= e($tanggalSelesai) ?>">
                            </div>

                            <div class="col-12 col-md-6">
                                <label for="status_aktif" class="form-label fw-semibold">Status Aktif</label>
                                <select class="form-select" id="status_aktif" name="status_aktif" required>
                                    <option value="1" <?= (int) $data['status_aktif'] === 1 ? 'selected' : '' ?>>Aktif</option>
                                    <option value="0" <?= (int) $data['status_aktif'] === 0 ? 'selected' : '' ?>>Tidak Aktif</option>
                                </select>
                            </div>
                        </div>

                        <hr class="my-4">

                        <div class="d-flex flex-column flex-sm-row gap-2 justify-content-end">
                            <a href="penempatan_siswa.php" class="btn btn-outline-secondary">
                                <i class="bi bi-x-circle"></i> Batal
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save"></i> Update Penempatan Siswa
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
function aktifkanDropdown(inputId, hiddenId, pilihanSelector) {
    const input = document.getElementById(inputId);
    const hidden = document.getElementById(hiddenId);
    const pilihan = document.querySelectorAll(pilihanSelector);
    const dropdown = bootstrap.Dropdown.getOrCreateInstance(input);

    input.addEventListener('focus', function () {
        if (hidden.value) {
            input.value = '';
            hidden.value = '';
        }

        pilihan.forEach(item => item.classList.remove('d-none'));
        dropdown.show();
    });

    input.addEventListener('input', function () {
        const pencarian = input.value.toLowerCase().trim();

        pilihan.forEach(item => {
            const cocok = item.dataset.label.toLowerCase().includes(pencarian);
            item.classList.toggle('d-none', !cocok);
        });

        dropdown.show();
    });

    pilihan.forEach(item => {
        item.addEventListener('click', function () {
            input.value = this.dataset.label;
            hidden.value = this.dataset.value;
            dropdown.hide();
        });
    });

    input.addEventListener('keydown', function (event) {
        if (event.key === 'Escape') {
            dropdown.hide();
        }
    });
}

aktifkanDropdown('cari_siswa', 'siswa_id', '.pilihan-siswa');
aktifkanDropdown('cari_tahun_ajaran', 'tahun_ajaran_id', '.pilihan-tahun');
aktifkanDropdown('cari_kelas', 'kelas_id', '.pilihan-kelas');

document.getElementById('cari_siswa').value = <?= json_encode($namaSiswaTerpilih, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>;
document.getElementById('cari_tahun_ajaran').value = <?= json_encode($namaTahunTerpilih, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>;
document.getElementById('cari_kelas').value = <?= json_encode($namaKelasTerpilih, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>;

document.getElementById('formPenempatan').addEventListener('submit', function (event) {
    const siswa = document.getElementById('siswa_id').value;
    const tahun = document.getElementById('tahun_ajaran_id').value;
    const kelas = document.getElementById('kelas_id').value;

    if (!siswa || !tahun || !kelas) {
        event.preventDefault();
        alert('Nama siswa, tahun ajaran, dan kelas wajib dipilih dari daftar.');
    }
});
</script>
</body>
</html>