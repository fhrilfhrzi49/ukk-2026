<?php

session_start();

include 'includes/cek_session.php';
include 'config/koneksi.php';

$id = $_GET['id'];


$cek = mysqli_query(
    $koneksi,
    "SELECT nama FROM t_kelas WHERE id = '$id'"
);

$data = mysqli_fetch_assoc($cek);


$sql = "DELETE FROM t_kelas WHERE id = '$id'";

if (mysqli_query($koneksi, $sql)) {

    $id_user = $_SESSION['id_user'];
    $waktu = date('Y-m-d H:i:s');

    $aktivitas = "hapus kelas: " . $data['nama'];

    
    $log = "INSERT INTO tbl_log
            (id_user, aktivitas, waktu)
            VALUES
            ('$id_user', '$aktivitas', '$waktu')";

    mysqli_query($koneksi, $log);
}

header('Location: kelola_kelas.php');
exit;
?>