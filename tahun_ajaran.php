<?php

session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$role = $_SESSION['role'] ?? '';

if ($role !== 'admin') {
    die("ANDA TIDAK BISA MENGAKSES HALAMAN INI");
}

require_once "config/koneksi.php";


/*
|--------------------------------------------------------------------------
| TAMBAH DATA
|--------------------------------------------------------------------------
*/

if (isset($_POST['tambah'])) {

    $tahun_ajaran = mysqli_real_escape_string(
        $koneksi,
        $_POST['tahun_ajaran']
    );

    $tanggal_mulai = $_POST['tanggal_mulai'];
    $tanggal_selesai = $_POST['tanggal_selesai'];

    $status = mysqli_real_escape_string(
        $koneksi,
        $_POST['status']
    );

    $query = mysqli_query(
        $koneksi,
        "INSERT INTO tahun_ajaran
        (
            tahun_ajaran,
            tanggal_mulai,
            tanggal_selesai,
            status
        )
        VALUES
        (
            '$tahun_ajaran',
            '$tanggal_mulai',
            '$tanggal_selesai',
            '$status'
        )"
    );

    if ($query) {
        header("Location: tahun_ajaran.php");
        exit;
    }
}


/*
|--------------------------------------------------------------------------
| UPDATE DATA
|--------------------------------------------------------------------------
*/

if (isset($_POST['update'])) {

    $id = (int) $_POST['id'];

    $tahun_ajaran = mysqli_real_escape_string(
        $koneksi,
        $_POST['tahun_ajaran']
    );

    $tanggal_mulai = $_POST['tanggal_mulai'];
    $tanggal_selesai = $_POST['tanggal_selesai'];

    $status = mysqli_real_escape_string(
        $koneksi,
        $_POST['status']
    );

    $query = mysqli_query(
        $koneksi,
        "UPDATE tahun_ajaran SET
            tahun_ajaran = '$tahun_ajaran',
            tanggal_mulai = '$tanggal_mulai',
            tanggal_selesai = '$tanggal_selesai',
            status = '$status'
        WHERE id = $id"
    );

    if ($query) {
        header("Location: tahun_ajaran.php");
        exit;
    }
}


/*
|--------------------------------------------------------------------------
| HAPUS DATA
|--------------------------------------------------------------------------
*/

if (isset($_GET['hapus'])) {

    $id = (int) $_GET['hapus'];

    mysqli_query(
        $koneksi,
        "DELETE FROM tahun_ajaran
         WHERE id = $id"
    );

    header("Location: tahun_ajaran.php");
    exit;
}


/*
|--------------------------------------------------------------------------
| AMBIL DATA
|--------------------------------------------------------------------------
*/

$data_tahun = mysqli_query(
    $koneksi,
    "SELECT *
     FROM tahun_ajaran
     ORDER BY id DESC"
);

?>

<!DOCTYPE html>
<html lang="id">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>
        Tahun Ajaran - Sistem Pelanggaran Siswa
    </title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

</head>

<body>

<div class="container-fluid">

    <div class="row">


        <!-- SIDEBAR -->

        <div class="col-md-3 col-lg-2 bg-dark min-vh-100 p-3">

            <h4 class="text-white mb-4">
                My Website
            </h4>

            <ul class="nav nav-pills flex-column">

                <li class="nav-item mb-2">
                    <a
                        href="dashboard.php"
                        class="nav-link text-white"
                    >
                        Dashboard
                    </a>
                </li>

                <li class="nav-item mb-2">
                    <a
                        href="tahun_ajaran.php"
                        class="nav-link active"
                    >
                        Tahun Ajaran
                    </a>
                </li>

                <li class="nav-item mb-2">
                    <a
                        href="siswa.php"
                        class="nav-link text-white"
                    >
                        Data Siswa
                    </a>
                </li>

                <li class="nav-item mb-2">
                    <a
                        href="guru.php"
                        class="nav-link text-white"
                    >
                        Data Guru
                    </a>
                </li>

                <li class="nav-item mb-2">
                    <a
                        href="kelas.php"
                        class="nav-link text-white"
                    >
                        Kelas
                    </a>
                </li>

                <li class="nav-item mb-2">
                    <a
                        href="wali_kelas.php"
                        class="nav-link text-white"
                    >
                        Wali Kelas
                    </a>
                </li>

                <li class="nav-item mb-2">
                    <a
                        href="penempatan_siswa.php"
                        class="nav-link text-white"
                    >
                        Penempatan Siswa
                    </a>
                </li>

                <li class="nav-item mb-2">
                    <a
                        href="kategori_pelanggaran.php"
                        class="nav-link text-white"
                    >
                        Kategori Pelanggaran
                    </a>
                </li>

                <li class="nav-item mb-2">
                    <a
                        href="jenis_pelanggaran.php"
                        class="nav-link text-white"
                    >
                        Jenis Pelanggaran
                    </a>
                </li>

                <li class="nav-item mb-2">
                    <a
                        href="laporan.php"
                        class="nav-link text-white"
                    >
                        Laporan
                    </a>
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
                    <a
                        href="about.php"
                        class="nav-link text-white"
                    >
                        About Me
                    </a>
                </li>

                <hr class="text-secondary">

                <li class="nav-item">
                    <a
                        href="logout.php"
                        class="text-white"
                        onclick="return confirm('Yakin ingin logout?');"
                    >
                        Logout
                    </a><dl>
                </li>
                <footer class="mt-4 pt-3 border-top text-center text-secondary">
        <small>
            © 2026 Sistem Pelanggaran Siswa — Muhamad Fahril Fahrurozi
        </small>
    </footer>

                

            </ul>

        </div>


        <!-- KONTEN -->

        <main class="col-md-9 col-lg-10 p-4">


            <div class="d-flex justify-content-between align-items-center mb-4">

                <div>

                    <h2 class="mb-2">
                        Data Tahun Ajaran
                    </h2>

                    <p class="text-secondary mb-0">
                        Kelola data Tahun Ajaran.
                    </p>

                </div>


                <button
                    type="button"
                    class="btn btn-danger"
                    data-bs-toggle="modal"
                    data-bs-target="#modalTambah"
                >
                    + Tambah Tahun Ajaran
                </button>

            </div>


            <!-- TABEL -->

            <div class="card border-0 shadow-sm">

                <div class="card-body">

                    <div class="table-responsive">

                        <table class="table table-bordered table-hover align-middle">

                            <thead class="table-dark">

                                <tr>

                                    <th
                                        width="60"
                                        class="text-center"
                                    >
                                        No
                                    </th>

                                    <th>
                                        Tahun Ajaran
                                    </th>

                                    <th>
                                        Tanggal Mulai
                                    </th>

                                    <th>
                                        Tanggal Selesai
                                    </th>

                                    <th
                                        width="150"
                                        class="text-center"
                                    >
                                        Status
                                    </th>

                                    <th
                                        width="180"
                                        class="text-center"
                                    >
                                        Aksi
                                    </th>

                                </tr>

                            </thead>


                            <tbody>

                            <?php

                            $no = 1;

                            if (mysqli_num_rows($data_tahun) > 0):

                                while ($tahun = mysqli_fetch_assoc($data_tahun)):

                            ?>

                                <tr>

                                    <td class="text-center">
                                        <?= $no++; ?>
                                    </td>


                                    <td>
                                        <?= htmlspecialchars(
                                            $tahun['tahun_ajaran']
                                        ); ?>
                                    </td>


                                    <td>
                                        <?= !empty($tahun['tanggal_mulai'])
                                            ? date(
                                                'd-m-Y',
                                                strtotime(
                                                    $tahun['tanggal_mulai']
                                                )
                                            )
                                            : '-'; ?>
                                    </td>


                                    <td>
                                        <?= !empty($tahun['tanggal_selesai'])
                                            ? date(
                                                'd-m-Y',
                                                strtotime(
                                                    $tahun['tanggal_selesai']
                                                )
                                            )
                                            : '-'; ?>
                                    </td>


                                    <td class="text-center">

                                        <?php if ($tahun['status'] === 'Aktif'): ?>

                                            <span class="badge bg-success">
                                                Aktif
                                            </span>

                                        <?php else: ?>

                                            <span class="badge bg-secondary">
                                                Tidak Aktif
                                            </span>

                                        <?php endif; ?>

                                    </td>


                                    <td class="text-center">

                                        <button
                                            type="button"
                                            class="btn btn-warning btn-sm"
                                            data-bs-toggle="modal"
                                            data-bs-target="#modalEdit<?= $tahun['id']; ?>"
                                        >
                                            Edit
                                        </button>


                                        <a
                                            href="tahun_ajaran.php?hapus=<?= $tahun['id']; ?>"
                                            class="btn btn-danger btn-sm"
                                            onclick="return confirm('Yakin ingin menghapus tahun ajaran ini?');"
                                        >
                                            Hapus
                                        </a>

                                    </td>

                                </tr>


                                <!-- MODAL EDIT -->

                                <div
                                    class="modal fade"
                                    id="modalEdit<?= $tahun['id']; ?>"
                                    tabindex="-1"
                                >

                                    <div class="modal-dialog">

                                        <div class="modal-content">


                                            <div class="modal-header">

                                                <h5 class="modal-title">
                                                    Edit Tahun Ajaran
                                                </h5>

                                                <button
                                                    type="button"
                                                    class="btn-close"
                                                    data-bs-dismiss="modal"
                                                ></button>

                                            </div>


                                            <form method="POST">

                                                <div class="modal-body">

                                                    <input
                                                        type="hidden"
                                                        name="id"
                                                        value="<?= $tahun['id']; ?>"
                                                    >


                                                    <div class="mb-3">

                                                        <label class="form-label">
                                                            Tahun Ajaran
                                                        </label>

                                                        <input
                                                            type="text"
                                                            name="tahun_ajaran"
                                                            class="form-control"
                                                            value="<?= htmlspecialchars(
                                                                $tahun['tahun_ajaran']
                                                            ); ?>"
                                                            required
                                                        >

                                                    </div>


                                                    <div class="mb-3">

                                                        <label class="form-label">
                                                            Tanggal Mulai
                                                        </label>

                                                        <input
                                                            type="date"
                                                            name="tanggal_mulai"
                                                            class="form-control"
                                                            value="<?= $tahun['tanggal_mulai']; ?>"
                                                            required
                                                        >

                                                    </div>


                                                    <div class="mb-3">

                                                        <label class="form-label">
                                                            Tanggal Selesai
                                                        </label>

                                                        <input
                                                            type="date"
                                                            name="tanggal_selesai"
                                                            class="form-control"
                                                            value="<?= $tahun['tanggal_selesai']; ?>"
                                                            required
                                                        >

                                                    </div>


                                                    <div class="mb-3">

                                                        <label class="form-label">
                                                            Status
                                                        </label>

                                                        <select
                                                            name="status"
                                                            class="form-select"
                                                            required
                                                        >

                                                            <option
                                                                value="Aktif"
                                                                <?= $tahun['status'] === 'Aktif'
                                                                    ? 'selected'
                                                                    : ''; ?>
                                                            >
                                                                Aktif
                                                            </option>

                                                            <option
                                                                value="Tidak Aktif"
                                                                <?= $tahun['status'] === 'Tidak Aktif'
                                                                    ? 'selected'
                                                                    : ''; ?>
                                                            >
                                                                Tidak Aktif
                                                            </option>

                                                        </select>

                                                    </div>

                                                </div>


                                                <div class="modal-footer">

                                                    <button
                                                        type="button"
                                                        class="btn btn-secondary"
                                                        data-bs-dismiss="modal"
                                                    >
                                                        Batal
                                                    </button>

                                                    <button
                                                        type="submit"
                                                        name="update"
                                                        class="btn btn-danger"
                                                    >
                                                        Simpan Perubahan
                                                    </button>

                                                </div>

                                            </form>

                                        </div>

                                    </div>

                                </div>


                            <?php

                                endwhile;

                            else:

                            ?>

                                <tr>

                                    <td
                                        colspan="6"
                                        class="text-center text-secondary py-4"
                                    >
                                        Belum ada data Tahun Ajaran.
                                    </td>

                                </tr>

                            <?php endif; ?>

                            </tbody>

                        </table>

                    </div>

                </div>

            </div>

        </main>

    </div>

</div>


<!-- MODAL TAMBAH -->

<div
    class="modal fade"
    id="modalTambah"
    tabindex="-1"
>

    <div class="modal-dialog">

        <div class="modal-content">


            <div class="modal-header">

                <h5 class="modal-title">
                    Tambah Tahun Ajaran
                </h5>

                <button
                    type="button"
                    class="btn-close"
                    data-bs-dismiss="modal"
                ></button>

            </div>


            <form method="POST">

                <div class="modal-body">


                    <div class="mb-3">

                        <label class="form-label">
                            Tahun Ajaran
                        </label>

                        <input
                            type="text"
                            name="tahun_ajaran"
                            class="form-control"
                            placeholder="Contoh: 2026/2027"
                            required
                        >

                    </div>


                    <div class="mb-3">

                        <label class="form-label">
                            Tanggal Mulai
                        </label>

                        <input
                            type="date"
                            name="tanggal_mulai"
                            class="form-control"
                            required
                        >

                    </div>


                    <div class="mb-3">

                        <label class="form-label">
                            Tanggal Selesai
                        </label>

                        <input
                            type="date"
                            name="tanggal_selesai"
                            class="form-control"
                            required
                        >

                    </div>


                    <div class="mb-3">

                        <label class="form-label">
                            Status
                        </label>

                        <select
                            name="status"
                            class="form-select"
                            required
                        >

                            <option value="Aktif">
                                Aktif
                            </option>

                            <option value="Tidak Aktif">
                                Tidak Aktif
                            </option>

                        </select>

                    </div>


                </div>


                <div class="modal-footer">

                    <button
                        type="button"
                        class="btn btn-secondary"
                        data-bs-dismiss="modal"
                    >
                        Batal
                    </button>


                    <button
                        type="submit"
                        name="tambah"
                        class="btn btn-danger"
                    >
                        Simpan
                    </button>

                </div>

            </form>

        </div>

    </div>

</div>


<script
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"
></script>

</body>

</html>