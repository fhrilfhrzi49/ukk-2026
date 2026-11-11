<?php
require_once __DIR__ . '/config/koneksi.php';

mysqli_report(MYSQLI_REPORT_OFF);

echo '<h2>Pemeriksaan Akun Guru</h2>';

$tables = mysqli_query($koneksi, "SHOW TABLES");

if (!$tables) {
    exit('Gagal memeriksa database.');
}

while ($row = mysqli_fetch_row($tables)) {
    $table = $row[0];

    if (!preg_match('/user|akun|guru|pengguna/i', $table)) {
        continue;
    }

    echo '<h3>' . htmlspecialchars($table) . '</h3>';

    $safeTable = str_replace('`', '``', $table);
    $columns = mysqli_query($koneksi, "DESCRIBE `$safeTable`");

    if (!$columns) {
        continue;
    }

    $fields = [];

    while ($col = mysqli_fetch_assoc($columns)) {
        $fields[] = $col['Field'];
    }

    echo '<p>Kolom: ' .
        htmlspecialchars(implode(', ', $fields)) .
        '</p>';
}
?>