-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Waktu pembuatan: 13 Nov 2026 pada 09.59
-- Versi server: 10.4.32-MariaDB
-- Versi PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `db_ukk_2026`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `t_jenis_pelanggaran`
--

CREATE TABLE `t_jenis_pelanggaran` (
  `id` int(11) NOT NULL,
  `kategori_id` int(11) NOT NULL,
  `nama_pelanggaran` varchar(150) NOT NULL,
  `deskripsi` text NOT NULL,
  `poin` int(11) NOT NULL DEFAULT 0,
  `status_aktif` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `t_jenis_pelanggaran`
--

INSERT INTO `t_jenis_pelanggaran` (`id`, `kategori_id`, `nama_pelanggaran`, `deskripsi`, `poin`, `status_aktif`, `created_at`) VALUES
(1, 102, 'Terlambat datang ke sekolah', 'Siswa datang melewati waktu masuk yang ditetapkan sekolah.', 5, 1, '2026-10-22 04:06:37'),
(2, 103, 'Membolos', 'Siswa tidak mengikuti pembelajaran tanpa izin atau keterangan yang sah.', 10, 1, '2026-10-22 04:06:37'),
(3, 104, 'Tidak memakai atribut lengkap', 'Siswa tidak menggunakan atribut seragam sesuai ketentuan sekolah.', 5, 1, '2026-10-22 04:06:37'),
(4, 105, 'Mengganggu proses pembelajaran', 'Siswa mengganggu kegiatan belajar mengajar di kelas.', 5, 1, '2026-10-22 04:06:37'),
(9, 107, 'Membuang sampah sembarangan', 'Siswa membuang sampah sembarangan', 5, 1, '2026-11-04 02:03:19');

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `t_jenis_pelanggaran`
--
ALTER TABLE `t_jenis_pelanggaran`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_kategori` (`kategori_id`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `t_jenis_pelanggaran`
--
ALTER TABLE `t_jenis_pelanggaran`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `t_jenis_pelanggaran`
--
ALTER TABLE `t_jenis_pelanggaran`
  ADD CONSTRAINT `fk_jenis_kategori` FOREIGN KEY (`kategori_id`) REFERENCES `t_pelanggaran_kategori` (`id`) ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
