
<?php
require_once __DIR__ . '/config/akses.php';
wajib_role(['admin', 'wali_kelas', 'guru_wali_kelas']);

require_once __DIR__ . '/config/koneksi.php';

$query = mysqli_query($koneksi, "
    SELECT
        ps.id,
        s.nis,
        s.nama,
        pk.nama AS pelanggaran
    FROM t_pelanggaran_siswa ps
    JOIN t_siswa s ON ps.siswa_id = s.id
    JOIN t_pelanggaran_kategori pk ON ps.pelanggaran_id = pk.id
    ORDER BY ps.id DESC
");

if (!$query) {
    die('Gagal mengambil data: ' . mysqli_error($koneksi));
}

$namaFile = 'Laporan_Pelanggaran_Siswa_' . date('Y-m-d') . '.xls';

header('Content-Type: application/vnd.ms-excel; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $namaFile . '"');
header('Cache-Control: max-age=0');

echo "\xEF\xBB\xBF";
?>

<table border="1">
    <thead>
        <tr>
            <th colspan="4">LAPORAN PELANGGARAN SISWA SI-TIB</th>
        </tr>
        <tr>
            <th>No</th>
            <th>NIS</th>
            <th>Nama Siswa</th>
            <th>Jenis Pelanggaran</th>
        </tr>
    </thead>
    <tbody>
        <?php
        $no = 1;

        while ($row = mysqli_fetch_assoc($query)):
        ?>
            <tr>
                <td><?= $no++; ?></td>
                <td><?= htmlspecialchars($row['nis'] ?? '', ENT_QUOTES, 'UTF-8'); ?></td>
                <td><?= htmlspecialchars($row['nama'] ?? '', ENT_QUOTES, 'UTF-8'); ?></td>
                <td><?= htmlspecialchars($row['pelanggaran'] ?? '', ENT_QUOTES, 'UTF-8'); ?></td>
            </tr>
        <?php endwhile; ?>
    </tbody>
</table>