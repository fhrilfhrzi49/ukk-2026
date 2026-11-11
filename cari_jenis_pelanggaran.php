
<?php


if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/config/koneksi.php';

header('Content-Type: application/json; charset=utf-8');

if (!$koneksi) {
    http_response_code(500);
    echo json_encode(['hasil' => [], 'pesan' => 'Koneksi database gagal.']);
    exit;
}

$q = trim($_GET['q'] ?? '');

if (mb_strlen($q) < 2) {
    echo json_encode(['hasil' => []], JSON_UNESCAPED_UNICODE);
    exit;
}

$kataKunci = '%' . $q . '%';

$sql = "SELECT
            j.id,
            j.nama_pelanggaran,
            j.deskripsi,
            j.poin,
            k.nama AS nama_kategori
        FROM t_jenis_pelanggaran j
        INNER JOIN t_pelanggaran_kategori k
            ON k.id = j.kategori_id
        WHERE j.status_aktif = 1
          AND k.status_aktif = 1
          AND (
              j.nama_pelanggaran LIKE ?
              OR j.deskripsi LIKE ?
              OR k.nama LIKE ?
          )
        ORDER BY j.nama_pelanggaran ASC
        LIMIT 20";

$stmt = mysqli_prepare($koneksi, $sql);

if (!$stmt) {
    http_response_code(500);
    echo json_encode(['hasil' => [], 'pesan' => 'Pencarian gagal disiapkan.']);
    exit;
}

mysqli_stmt_bind_param(
    $stmt,
    'sss',
    $kataKunci,
    $kataKunci,
    $kataKunci
);

mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);
$hasil = [];

while ($row = mysqli_fetch_assoc($result)) {
    $hasil[] = [
        'id' => (int) $row['id'],
        'nama' => $row['nama_pelanggaran'],
        'kategori' => $row['nama_kategori'],
        'deskripsi' => $row['deskripsi'],
        'poin' => (int) $row['poin']
    ];
}

mysqli_stmt_close($stmt);

echo json_encode(
    ['hasil' => $hasil],
    JSON_UNESCAPED_UNICODE
);