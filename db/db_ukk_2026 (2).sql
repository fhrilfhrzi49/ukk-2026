-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 05, 2026 at 04:22 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

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
-- Table structure for table `t_catatan_pelanggaran`
--

CREATE TABLE `t_catatan_pelanggaran` (
  `id` int(11) NOT NULL,
  `siswa_id` int(11) NOT NULL,
  `pelanggaran_id` int(11) DEFAULT NULL,
  `jenis_pelanggaran_id` int(11) DEFAULT NULL,
  `tanggal_pelanggaran` date NOT NULL,
  `keterangan` text NOT NULL,
  `dicatat_oleh` varchar(150) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `t_catatan_pelanggaran`
--

INSERT INTO `t_catatan_pelanggaran` (`id`, `siswa_id`, `pelanggaran_id`, `jenis_pelanggaran_id`, `tanggal_pelanggaran`, `keterangan`, `dicatat_oleh`, `created_at`) VALUES
(3, 120, NULL, 1, '2026-11-05', 'Terlambat datang ke sekolah', 'Ari Sandriana, S.Kom', '2026-11-05 01:22:25'),
(4, 125, NULL, 4, '2026-11-05', 'Berisik di kelas', 'Ari Sandriana, S.Kom', '2026-11-05 01:23:03');

-- --------------------------------------------------------

--
-- Table structure for table `t_guru`
--

CREATE TABLE `t_guru` (
  `id` int(11) NOT NULL,
  `nip` varchar(30) NOT NULL,
  `nama` varchar(150) DEFAULT NULL,
  `email` varchar(150) NOT NULL,
  `status_aktif` tinyint(1) NOT NULL,
  `user_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `t_guru`
--

INSERT INTO `t_guru` (`id`, `nip`, `nama`, `email`, `status_aktif`, `user_id`, `created_at`, `updated_at`) VALUES
(538, '199203102015022002', 'Muhamad Fahril, S.Krim', 'guru@gmail.com', 1, 605, '2026-10-22 01:29:01', '2026-10-22 01:29:01'),
(597, '600984633062951800', 'Ai Nuryaldah, S.Pd', 'ai@gmail.com', 1, 609, '2026-11-04 03:55:33', '2026-11-04 04:06:17'),
(598, '624088433872990700', 'Maya Permata Endah, S.Pd', 'maya@gmail.com', 1, 610, '2026-11-04 03:55:33', '2026-11-04 04:06:17'),
(599, '317488297836378500', 'Dody Jaenudin, S.Pd', 'dody@gmail.com', 1, 611, '2026-11-04 03:55:33', '2026-11-04 04:06:17'),
(600, '515176215223200830', 'Dedi Kardiman, S.Pd', 'dedi@gmail.com', 1, 612, '2026-11-04 03:55:33', '2026-11-04 04:06:17'),
(601, '623416210267149000', 'Uca Musa Yusenda, S.Pd', 'uca@gmail.com', 1, 613, '2026-11-04 03:55:33', '2026-11-04 04:06:17'),
(602, '571552433326423550', 'Drs. Ahmad Nur Mukhlis', 'ahmad@gmail.com', 1, 614, '2026-11-04 03:55:33', '2026-11-04 04:06:17'),
(603, '887513563490951000', 'Drs. Heri', 'heri@gmail.com', 1, 615, '2026-11-04 03:55:33', '2026-11-04 04:06:17'),
(605, '452012062866251970', 'H. Encep Maemun, S.T., M.Pd', 'encep@gmail.com', 1, 617, '2026-11-04 03:55:33', '2026-11-04 04:06:17'),
(606, '591328706584245500', 'Opik', 'opik@gmail.com', 1, 618, '2026-11-04 03:55:33', '2026-11-04 04:06:17'),
(607, '600607371982752800', 'Ida Farida, S.Pd', 'ida@gmail.com', 1, 619, '2026-11-04 03:55:33', '2026-11-04 04:06:17'),
(608, '229050767821307070', 'Idah Mahmudah, S.Pd', 'idah@gmail.com', 1, 620, '2026-11-04 03:55:33', '2026-11-04 04:06:17'),
(609, '143431750818557810', 'Drs. Muhamad Ali', 'muhamad@gmail.com', 1, 621, '2026-11-04 03:55:33', '2026-11-04 04:06:17'),
(610, '830006478289148300', 'Helmi Ali Akbar, S.Pd.I', 'helmi@gmail.com', 1, 622, '2026-11-04 03:55:33', '2026-11-04 04:06:17'),
(611, '919737166650348500', 'Syeni Suswarini, S.Pd', 'syeni@gmail.com', 1, 623, '2026-11-04 03:55:33', '2026-11-04 04:06:17'),
(612, '208666426044578100', 'Wiwid Yulianti, S.Pd', 'wiwid@gmail.com', 1, 624, '2026-11-04 03:55:33', '2026-11-04 04:06:17'),
(613, '884120657932125600', 'Ansor Muhajir, S.T', 'ansor@gmail.com', 1, 625, '2026-11-04 03:55:33', '2026-11-04 04:06:17'),
(614, '994604039187174300', 'Ogi Gaosun Gias, S.Pd.I', 'ogi@gmail.com', 1, 626, '2026-11-04 03:55:33', '2026-11-04 04:06:17'),
(615, '420658249799775170', 'Sandi Mulyana, S.Pd', 'sandi@gmail.com', 1, 627, '2026-11-04 03:55:33', '2026-11-04 04:06:17'),
(616, '819479159097633500', 'Erna Suryani, S.Pd', 'erna@gmail.com', 1, 628, '2026-11-04 03:55:33', '2026-11-04 04:06:17'),
(617, '935421073749122300', 'Ai Wahidah, S.Ag', 'ai.wahidah@gmail.com', 1, 629, '2026-11-04 03:55:33', '2026-11-04 04:06:17'),
(618, '318667919112991500', 'Chandra Aditya Yusup, S.Pd', 'chandra@gmail.com', 1, 630, '2026-11-04 03:55:33', '2026-11-04 04:06:17'),
(620, '479378280210661060', 'Asep Taufiq Hidayat, S.Kom', 'asep.taufiq@gmail.com', 1, 632, '2026-11-04 03:55:33', '2026-11-04 04:06:17'),
(621, '835662222779972700', 'Rendy Ichsan Kusumah, S.T', 'rendy@gmail.com', 1, 633, '2026-11-04 03:55:33', '2026-11-04 04:06:17'),
(622, '840176300928160800', 'Feri Rahmawan, S.Pd', 'feri@gmail.com', 1, 634, '2026-11-04 03:55:33', '2026-11-04 04:06:17'),
(624, '848945444681630800', 'Yaneu Nurdian, S.Pd, M.Pd', 'yaneu@gmail.com', 1, 636, '2026-11-04 03:55:33', '2026-11-04 04:06:17'),
(625, '263042659184935200', 'Ucu Muhammad Khoer N, S.Pd', 'ucu@gmail.com', 1, 637, '2026-11-04 03:55:33', '2026-11-04 04:06:17'),
(626, '468376989540063800', 'Mira Dwi Rahmawati, S.Ag', 'mira@gmail.com', 1, 638, '2026-11-04 03:55:33', '2026-11-04 04:06:17'),
(627, '552756997805793700', 'Aldi Maulana, S.Pd', 'aldi@gmail.com', 1, 639, '2026-11-04 03:55:33', '2026-11-04 04:06:17'),
(628, '358654048069058050', 'Eli Rosliah, S.Pd', 'eli@gmail.com', 1, 640, '2026-11-04 03:55:33', '2026-11-04 04:06:17'),
(629, '934999274588189300', 'Isni Nur Fitriani, S.Pd', 'isni@gmail.com', 1, 641, '2026-11-04 03:55:33', '2026-11-04 04:06:17'),
(630, '799034256394053100', 'Aanisah Putri Nur Aini, S.Pd', 'aanisah@gmail.com', 1, 642, '2026-11-04 03:55:33', '2026-11-04 04:06:17'),
(631, '190173485865978050', 'Yusi Luthfiani, S.Sos.I', 'yusi@gmail.com', 1, 643, '2026-11-04 03:55:33', '2026-11-04 04:06:17'),
(632, '253764687808011360', 'Lilis Rustandy Mawardi, S.Pd., M.Pd', 'lilis@gmail.com', 1, 644, '2026-11-04 03:55:33', '2026-11-04 04:06:17'),
(633, '598303009102403200', 'Yusrin Noer Sabrina, S.Pd', 'yusrin@gmail.com', 1, 645, '2026-11-04 03:55:33', '2026-11-04 04:06:17'),
(634, '330221009748262400', 'Senny Indayani, S.Pd', 'senny@gmail.com', 1, 646, '2026-11-04 03:55:33', '2026-11-04 04:06:17'),
(635, '656196049094382700', 'Ilma Amalia, S.Pd', 'ilma@gmail.com', 1, 647, '2026-11-04 03:55:33', '2026-11-04 04:06:17'),
(636, '390317243887407040', 'Alvira Sofwatunnisa, S.Pd', 'alvira@gmail.com', 1, 648, '2026-11-04 03:55:33', '2026-11-04 04:06:17'),
(637, '782998099814167400', 'Risna Andriyani, S.Pd', 'risna@gmail.com', 1, 649, '2026-11-04 03:55:33', '2026-11-04 04:06:17'),
(638, '844038795068896100', 'Dra. Tintin Noerhayati', 'tintin@gmail.com', 1, 650, '2026-11-04 03:55:33', '2026-11-04 04:06:17'),
(639, '871199703562259300', 'Rizkia Fahira, S.Pd', 'rizkia@gmail.com', 1, 651, '2026-11-04 03:55:33', '2026-11-04 04:06:17'),
(640, '823882160264889000', 'Frasetio', 'frasetio@gmail.com', 1, 652, '2026-11-04 03:55:33', '2026-11-04 04:06:17'),
(641, '505811718297946940', 'Ratnaningsih Hati Aeni, S.Pd.', 'ratnaningsih@gmail.com', 1, 653, '2026-11-04 03:55:33', '2026-11-04 04:06:17'),
(642, '857412138355348400', 'Dheny Darajatul Hasani, S.Pd.', 'dheny@gmail.com', 1, 654, '2026-11-04 03:55:33', '2026-11-04 04:06:17'),
(643, '869625564543181600', 'Fahrullah, S.Pd', 'fahrullah@gmail.com', 1, 655, '2026-11-04 03:55:33', '2026-11-04 04:06:17'),
(645, '270580510581452860', 'Fitri Fauziah, S.T', 'fitri@gmail.com', 1, 657, '2026-11-04 03:55:33', '2026-11-04 04:06:17'),
(646, '725228274637114500', 'Nida Aulia, S.Pd', 'nida@gmail.com', 1, 658, '2026-11-04 03:55:33', '2026-11-04 04:06:17'),
(647, '914399869101494400', 'Sri Nurrizki Putri Krisnadi, S.Pd', 'sri@gmail.com', 1, 659, '2026-11-04 03:55:33', '2026-11-04 04:06:17'),
(648, '496314549256409100', 'Riesy Putri Navia, S.Kom', 'riesy@gmail.com', 1, 660, '2026-11-04 03:55:33', '2026-11-04 04:06:17'),
(649, '538373166637842700', 'Rurry Febriani Maryadi, S.Pd', 'rurry@gmail.com', 1, 661, '2026-11-04 03:55:33', '2026-11-04 04:06:17'),
(650, '202922213080266700', 'Irvan Rizal, S.Pd', 'irvan@gmail.com', 1, 662, '2026-11-04 03:55:33', '2026-11-04 04:06:17'),
(660, '775891435310143500', 'Aan Andriana, S.T', 'aan@gmail.com', 1, 671, '2026-11-04 04:17:18', '2026-11-04 04:17:18'),
(661, '487076401977871000', 'Arif Kusmawan, S.Pd', 'arif@gmail.com', 1, 672, '2026-11-04 04:22:24', '2026-11-04 04:22:24'),
(662, '160278253034016350', 'Wawan Kurniawan, S.Pd.I', 'wawan@gmail.com', 1, 673, '2026-11-04 04:23:36', '2026-11-04 04:23:36'),
(663, '693894863961166600', 'Ari Sandriana, S.Kom', 'ari@gmail.com', 1, 674, '2026-11-04 04:24:51', '2026-11-04 04:24:51'),
(664, '822910545135765000', 'Asep Bastaman', 'asep@gmail.com', 1, 678, '2026-11-04 05:20:04', '2026-11-04 05:20:04');

-- --------------------------------------------------------

--
-- Table structure for table `t_jenis_pelanggaran`
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
-- Dumping data for table `t_jenis_pelanggaran`
--

INSERT INTO `t_jenis_pelanggaran` (`id`, `kategori_id`, `nama_pelanggaran`, `deskripsi`, `poin`, `status_aktif`, `created_at`) VALUES
(1, 102, 'Terlambat datang ke sekolah', 'Siswa datang melewati waktu masuk yang ditetapkan sekolah.', 5, 1, '2026-10-22 04:06:37'),
(2, 103, 'Membolos', 'Siswa tidak mengikuti pembelajaran tanpa izin atau keterangan yang sah.', 10, 1, '2026-10-22 04:06:37'),
(3, 104, 'Tidak memakai atribut lengkap', 'Siswa tidak menggunakan atribut seragam sesuai ketentuan sekolah.', 5, 1, '2026-10-22 04:06:37'),
(4, 105, 'Mengganggu proses pembelajaran', 'Siswa mengganggu kegiatan belajar mengajar di kelas.', 5, 1, '2026-10-22 04:06:37'),
(9, 107, 'Membuang sampah sembarangan', 'Siswa membuang sampah sembarangan', 5, 1, '2026-11-04 02:03:19');

-- --------------------------------------------------------

--
-- Table structure for table `t_kelas`
--

CREATE TABLE `t_kelas` (
  `id` int(11) NOT NULL,
  `nama` varchar(100) DEFAULT NULL,
  `tingkat` varchar(20) NOT NULL,
  `jurusan` varchar(100) NOT NULL,
  `status_aktif` tinyint(1) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `t_kelas`
--

INSERT INTO `t_kelas` (`id`, `nama`, `tingkat`, `jurusan`, `status_aktif`, `created_at`, `updated_at`) VALUES
(1, 'XII TBSM 1', 'XII', 'TBSM 1', 1, '2026-10-22 01:42:46', '2026-10-22 01:42:46'),
(2, 'XII RPL 2', 'XII', 'RPL', 1, '2026-10-22 01:29:01', '2026-11-04 01:20:05'),
(102, 'XI RPL', 'XI', 'RPL', 1, '2026-11-04 04:58:52', '2026-11-04 04:58:52'),
(103, '	XI TBSM', 'XI', 'TBSM', 1, '2026-11-04 04:59:12', '2026-11-04 04:59:12');

-- --------------------------------------------------------

--
-- Table structure for table `t_kelas_siswa`
--

CREATE TABLE `t_kelas_siswa` (
  `id` int(11) NOT NULL,
  `siswa_id` int(11) DEFAULT NULL,
  `tahun_ajaran_id` int(11) DEFAULT NULL,
  `kelas_id` int(11) DEFAULT NULL,
  `tanggal_mulai` date NOT NULL,
  `tanggal_selesai` date NOT NULL,
  `status_aktif` tinyint(1) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `t_kelas_siswa`
--

INSERT INTO `t_kelas_siswa` (`id`, `siswa_id`, `tahun_ajaran_id`, `kelas_id`, `tanggal_mulai`, `tanggal_selesai`, `status_aktif`, `created_at`, `updated_at`) VALUES
(107, 111, 101, 2, '2026-11-09', '2027-06-30', 1, '2026-11-05 01:11:22', '2026-11-05 01:11:22'),
(108, 112, 101, 2, '2026-11-09', '2027-06-30', 1, '2026-11-05 01:11:44', '2026-11-05 01:11:44'),
(109, 113, 101, 2, '2026-11-09', '2027-06-30', 1, '2026-11-05 01:12:03', '2026-11-05 01:12:03'),
(110, 114, 101, 2, '2026-11-09', '2027-06-30', 1, '2026-11-05 01:12:25', '2026-11-05 01:12:25'),
(111, 115, 101, 2, '2026-11-09', '2027-06-30', 1, '2026-11-05 01:12:52', '2026-11-05 01:12:52'),
(112, 116, 101, 2, '2026-11-09', '2027-06-30', 1, '2026-11-05 01:15:57', '2026-11-05 01:15:57'),
(113, 117, 101, 2, '2026-11-09', '2027-06-30', 1, '2026-11-05 01:15:57', '2026-11-05 01:15:57'),
(114, 118, 101, 2, '2026-11-09', '2027-06-30', 1, '2026-11-05 01:15:57', '2026-11-05 01:15:57'),
(115, 119, 101, 2, '2026-11-09', '2027-06-30', 1, '2026-11-05 01:15:57', '2026-11-05 01:15:57'),
(116, 120, 101, 2, '2026-11-09', '2027-06-30', 1, '2026-11-05 01:15:57', '2026-11-05 01:15:57'),
(117, 121, 101, 2, '2026-11-09', '2027-06-30', 1, '2026-11-05 01:15:57', '2026-11-05 01:15:57'),
(118, 122, 101, 2, '2026-11-09', '2027-06-30', 1, '2026-11-05 01:15:57', '2026-11-05 01:15:57'),
(119, 123, 101, 2, '2026-11-09', '2027-06-30', 1, '2026-11-05 01:15:57', '2026-11-05 01:15:57'),
(120, 124, 101, 2, '2026-11-09', '2027-06-30', 1, '2026-11-05 01:15:57', '2026-11-05 01:15:57'),
(121, 125, 101, 2, '2026-11-09', '2027-06-30', 1, '2026-11-05 01:15:57', '2026-11-05 01:15:57'),
(122, 126, 101, 2, '2026-11-09', '2027-06-30', 1, '2026-11-05 01:15:57', '2026-11-05 01:15:57'),
(123, 127, 101, 2, '2026-11-09', '2027-06-30', 1, '2026-11-05 01:15:57', '2026-11-05 01:15:57'),
(124, 128, 101, 2, '2026-11-09', '2027-06-30', 1, '2026-11-05 01:15:57', '2026-11-05 01:15:57'),
(125, 129, 101, 2, '2026-11-09', '2027-06-30', 1, '2026-11-05 01:15:57', '2026-11-05 01:15:57'),
(126, 130, 101, 2, '2026-11-09', '2027-06-30', 1, '2026-11-05 01:15:57', '2026-11-05 01:15:57'),
(127, 131, 101, 2, '2026-11-09', '2027-06-30', 1, '2026-11-05 01:15:57', '2026-11-05 01:15:57'),
(128, 132, 101, 2, '2026-11-09', '2027-06-30', 1, '2026-11-05 01:15:57', '2026-11-05 01:15:57'),
(129, 133, 101, 2, '2026-11-09', '2027-06-30', 1, '2026-11-05 01:15:57', '2026-11-05 01:15:57'),
(130, 134, 101, 2, '2026-11-09', '2027-06-30', 1, '2026-11-05 01:15:57', '2026-11-05 01:15:57'),
(131, 135, 101, 2, '2026-11-09', '2027-06-30', 1, '2026-11-05 01:15:57', '2026-11-05 01:15:57');

-- --------------------------------------------------------

--
-- Table structure for table `t_pelanggaran`
--

CREATE TABLE `t_pelanggaran` (
  `id` int(11) NOT NULL,
  `pelanggaran_kategori_id` int(11) DEFAULT NULL,
  `kode` varchar(30) DEFAULT NULL,
  `nama` varchar(150) DEFAULT NULL,
  `poin` int(11) NOT NULL,
  `deskripsi` text NOT NULL,
  `status_aktif` tinyint(1) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `t_pelanggaran`
--

INSERT INTO `t_pelanggaran` (`id`, `pelanggaran_kategori_id`, `kode`, `nama`, `poin`, `deskripsi`, `status_aktif`, `created_at`, `updated_at`) VALUES
(101, 103, 'KHD-001', 'Terlambat datang ke sekolah', 5, 'Siswa datang setelah batas waktu masuk sekolah.', 1, '2026-10-22 04:11:15', '2026-10-22 04:11:15'),
(102, 103, 'KHD-002', 'Tidak masuk tanpa keterangan', 15, 'Siswa tidak hadir tanpa keterangan yang sah.', 1, '2026-10-22 04:11:15', '2026-10-22 04:11:15'),
(103, 103, 'KHD-003', 'Meninggalkan sekolah tanpa izin', 15, 'Siswa meninggalkan lingkungan sekolah tanpa izin.', 1, '2026-10-22 04:11:15', '2026-10-22 04:11:15'),
(104, 104, 'SRG-001', 'Seragam tidak sesuai ketentuan', 5, 'Siswa menggunakan seragam yang tidak sesuai jadwal.', 1, '2026-10-22 04:11:15', '2026-10-22 04:11:15'),
(105, 104, 'SRG-002', 'Atribut seragam tidak lengkap', 5, 'Siswa tidak menggunakan atribut seragam secara lengkap.', 1, '2026-10-22 04:11:15', '2026-10-22 04:11:15'),
(106, 102, 'KDS-001', 'Tidak mengikuti upacara tanpa alasan', 5, 'Siswa tidak mengikuti upacara tanpa alasan yang sah.', 1, '2026-10-22 04:11:15', '2026-10-22 04:11:15'),
(107, 102, 'KDS-002', 'Tidak mematuhi instruksi guru', 10, 'Siswa tidak mematuhi instruksi guru yang berlaku.', 1, '2026-10-22 04:11:15', '2026-10-22 04:11:15'),
(108, 105, 'KTB-001', 'Keluar kelas tanpa izin', 10, 'Siswa meninggalkan kelas saat pembelajaran tanpa izin.', 1, '2026-10-22 04:11:15', '2026-10-22 04:11:15'),
(109, 105, 'KTB-002', 'Mengganggu proses pembelajaran', 5, 'Siswa mengganggu kegiatan belajar mengajar.', 1, '2026-10-22 04:11:15', '2026-10-22 04:11:15'),
(110, 107, 'KBL-001', 'Membuang sampah sembarangan', 5, 'Siswa membuang sampah tidak pada tempatnya.', 1, '2026-10-22 04:11:15', '2026-10-22 04:11:15'),
(111, 107, 'KBL-002', 'Tidak melaksanakan piket kelas', 5, 'Siswa tidak melaksanakan tugas piket yang ditetapkan.', 1, '2026-10-22 04:11:15', '2026-10-22 04:11:15');

-- --------------------------------------------------------

--
-- Table structure for table `t_pelanggaran_kategori`
--

CREATE TABLE `t_pelanggaran_kategori` (
  `id` int(11) NOT NULL,
  `nama` varchar(100) DEFAULT NULL,
  `deskripsi` text NOT NULL,
  `status_aktif` tinyint(1) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `t_pelanggaran_kategori`
--

INSERT INTO `t_pelanggaran_kategori` (`id`, `nama`, `deskripsi`, `status_aktif`, `created_at`, `updated_at`) VALUES
(102, 'Kedisiplinan', 'Pelanggaran terhadap aturan kedisiplinan sekolah, seperti terlambat datang, meninggalkan kelas tanpa izin, atau tidak mematuhi tata tertib.', 1, '2026-10-22 04:01:19', '2026-10-22 04:01:19'),
(103, 'Kehadiran', 'Tidak masuk sekolah tanpa keterangan, membolos, atau tidak mengikuti kegiatan pembelajaran tanpa izin.', 1, '2026-10-22 04:01:33', '2026-10-22 04:01:33'),
(104, 'Seragam dan Atribut', 'Tidak menggunakan seragam sesuai ketentuan, tidak memakai atribut wajib, atau mengenakan seragam yang tidak sesuai jadwal.', 1, '2026-10-22 04:01:46', '2026-10-22 04:01:46'),
(105, 'Ketertiban Belajar', 'Mengganggu proses pembelajaran, tidak mengerjakan tugas, membuat keributan di kelas, atau tidak mengikuti instruksi guru.', 1, '2026-10-22 04:02:05', '2026-11-04 01:46:30'),
(107, 'Kebersihan Lingkungan', 'Membuang sampah sembarangan, tidak menjaga kebersihan kelas, atau tidak melaksanakan piket sesuai jadwal.', 1, '2026-10-22 04:02:32', '2026-11-05 02:40:23');

-- --------------------------------------------------------

--
-- Table structure for table `t_pelanggaran_siswa`
--

CREATE TABLE `t_pelanggaran_siswa` (
  `id` int(11) NOT NULL,
  `tahun_ajaran_id` int(11) DEFAULT NULL,
  `siswa_id` int(11) DEFAULT NULL,
  `nama_siswa` varchar(150) NOT NULL,
  `kelas_id` int(11) DEFAULT NULL,
  `nama_kelas` varchar(100) NOT NULL,
  `pelanggaran_id` int(11) DEFAULT NULL,
  `nama_pelanggaran` varchar(150) NOT NULL,
  `pelanggaran_kategori_id` int(11) DEFAULT NULL,
  `guru_id` int(11) DEFAULT NULL,
  `nama_guru` varchar(150) NOT NULL,
  `tanggal` date DEFAULT NULL,
  `keterangan` text NOT NULL,
  `poin` int(11) NOT NULL,
  `tindakan` text NOT NULL,
  `status` varchar(30) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `t_siswa`
--

CREATE TABLE `t_siswa` (
  `id` int(11) NOT NULL,
  `nis` varchar(30) DEFAULT NULL,
  `nisn` varchar(20) NOT NULL,
  `nama` varchar(150) DEFAULT NULL,
  `jenis_kelamin` char(1) NOT NULL,
  `tanggal_lahir` date NOT NULL,
  `alamat` text NOT NULL,
  `status_aktif` tinyint(1) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `t_siswa`
--

INSERT INTO `t_siswa` (`id`, `nis`, `nisn`, `nama`, `jenis_kelamin`, `tanggal_lahir`, `alamat`, `status_aktif`, `created_at`, `updated_at`) VALUES
(111, '102410544', '0082716401', 'ADITTIA NUR LANTIP', 'L', '2008-01-14', 'Jl. Cihideung, Kelurahan Yudanagara, Kecamatan Cihideung, Kota Tasikmalaya', 1, '2026-11-05 00:51:59', '2026-11-05 00:56:58'),
(112, '102410545', '0082716402', 'AGNIA ROSYA NOERILLAH', 'P', '2008-03-22', 'Jl. Tentara Pelajar, Kelurahan Nagarawangi, Kecamatan Cihideung, Kota Tasikmalaya', 1, '2026-11-05 00:51:59', '2026-11-05 00:51:59'),
(113, '102410546', '0082716403', 'ANISA AMIRULLA', 'P', '2008-05-09', 'Jl. Siliwangi, Kelurahan Tawangsari, Kecamatan Tawang, Kota Tasikmalaya', 1, '2026-11-05 00:51:59', '2026-11-05 00:51:59'),
(114, '102410547', '0082716404', 'ARADEA KANDI', 'P', '2008-07-17', 'Jl. R. Ikik Wiradikarta, Kelurahan Empangsari, Kecamatan Tawang, Kota Tasikmalaya', 1, '2026-11-05 00:51:59', '2026-11-05 00:51:59'),
(115, '102410548', '0082716405', 'ARFA DIESA OKMALELIZA', 'P', '2008-09-03', 'Jl. Cigeureung, Kelurahan Nagarasari, Kecamatan Cipedes, Kota Tasikmalaya', 1, '2026-11-05 00:51:59', '2026-11-05 00:51:59'),
(116, '102410549', '0082716406', 'FITRI NUR SYIAMI', 'P', '2008-11-26', 'Jl. Panglayungan, Kelurahan Panglayungan, Kecamatan Cipedes, Kota Tasikmalaya', 1, '2026-11-05 00:51:59', '2026-11-05 00:51:59'),
(117, '102410550', '0082716407', 'JUNITA PUTRI NUR RAHIMAN', 'P', '2008-02-11', 'Jl. Indihiang, Kelurahan Parakannyasag, Kecamatan Indihiang, Kota Tasikmalaya', 1, '2026-11-05 00:51:59', '2026-11-05 00:51:59'),
(118, '102410551', '0082716408', 'MOHAMMAD ATALARIK ALI SYAHBANA', 'L', '2008-04-19', 'Jl. Letnan Harun, Kelurahan Sukamulya, Kecamatan Bungursari, Kota Tasikmalaya', 1, '2026-11-05 00:51:59', '2026-11-05 00:51:59'),
(119, '102410552', '0082716409', 'MUHAMAD FAHRIL FAHRUROZI', 'L', '2008-06-25', 'Jl. Perintis Kemerdekaan, Kelurahan Kersanagara, Kecamatan Cibeureum, Kota Tasikmalaya', 1, '2026-11-05 00:51:59', '2026-11-05 00:51:59'),
(120, '102410750', '0082716410', 'MUHAMMAD ADNAN SYAEFUL ISLAMI', 'L', '2008-08-12', 'Jl. Awipari, Kelurahan Awipari, Kecamatan Cibeureum, Kota Tasikmalaya', 1, '2026-11-05 00:51:59', '2026-11-05 00:51:59'),
(121, '102410553', '0082716411', 'MUHAMMAD ZAHRU AQILA FIKRI', 'L', '2008-10-07', 'Jl. Mangkubumi, Kelurahan Mangkubumi, Kecamatan Mangkubumi, Kota Tasikmalaya', 1, '2026-11-05 00:51:59', '2026-11-05 00:51:59'),
(122, '102410554', '0082716412', 'NADIA ZULFA SORAYA', 'P', '2008-12-15', 'Jl. Sambongpari, Kelurahan Sambongpari, Kecamatan Mangkubumi, Kota Tasikmalaya', 1, '2026-11-05 00:51:59', '2026-11-05 00:51:59'),
(123, '102410555', '0082716413', 'NAZRIL ILHAM', 'L', '2008-01-28', 'Jl. Kawalu, Kelurahan Talagasari, Kecamatan Kawalu, Kota Tasikmalaya', 1, '2026-11-05 00:51:59', '2026-11-05 00:51:59'),
(124, '102410556', '0082716414', 'PUSPITA DEWI', 'P', '2008-03-16', 'Jl. Karanganyar, Kelurahan Karanganyar, Kecamatan Kawalu, Kota Tasikmalaya', 1, '2026-11-05 00:51:59', '2026-11-05 00:51:59'),
(125, '102410558', '0082716415', 'RAKHA MUHAMMAD HAYKAL', 'L', '2008-05-21', 'Jl. Tamansari, Kelurahan Tamanjaya, Kecamatan Tamansari, Kota Tasikmalaya', 1, '2026-11-05 00:51:59', '2026-11-05 00:51:59'),
(126, '102410559', '0082716416', 'RANI MEILANI', 'P', '2008-07-04', 'Jl. Setiamulya, Kelurahan Setiamulya, Kecamatan Tamansari, Kota Tasikmalaya', 1, '2026-11-05 00:51:59', '2026-11-05 00:51:59'),
(127, '102410560', '0082716417', 'REIHAN MUZAKI', 'L', '2008-09-18', 'Jl. Purbaratu, Kelurahan Purbaratu, Kecamatan Purbaratu, Kota Tasikmalaya', 1, '2026-11-05 00:51:59', '2026-11-05 00:51:59'),
(128, '102410561', '0082716418', 'RESA JULIANSAH', 'L', '2008-11-09', 'Jl. Singkup, Kelurahan Singkup, Kecamatan Purbaratu, Kota Tasikmalaya', 1, '2026-11-05 00:51:59', '2026-11-05 00:51:59'),
(129, '102410562', '0082716419', 'RIZKI PERMATA SUGIANTO', 'L', '2008-02-23', 'Jl. Gobras, Kelurahan Sukahurip, Kecamatan Tamansari, Kota Tasikmalaya', 1, '2026-11-05 00:51:59', '2026-11-05 00:51:59'),
(130, '102410563', '0082716420', 'SAHIRA FIRAJ', 'P', '2008-04-06', 'Jl. RE Martadinata, Kelurahan Cipedes, Kecamatan Cipedes, Kota Tasikmalaya', 1, '2026-11-05 00:51:59', '2026-11-05 00:51:59'),
(131, '102410564', '0082716421', 'SENDI MARDIAN', 'L', '2008-06-13', 'Jl. Nagarawangi, Kelurahan Nagarawangi, Kecamatan Cihideung, Kota Tasikmalaya', 1, '2026-11-05 00:51:59', '2026-11-05 00:51:59'),
(132, '102410565', '0082716422', 'SHERLY SATRIANI', 'P', '2008-08-29', 'Jl. Leuwidahu, Kelurahan Parakannyasag, Kecamatan Indihiang, Kota Tasikmalaya', 1, '2026-11-05 00:51:59', '2026-11-05 00:51:59'),
(133, '102410566', '0082716423', 'SOFI NURJANAH', 'P', '2008-10-16', 'Jl. Sukarindik, Kelurahan Sukarindik, Kecamatan Bungursari, Kota Tasikmalaya', 1, '2026-11-05 00:51:59', '2026-11-05 00:51:59'),
(134, '102410567', '0082716424', 'SURAIYA SALSABILA AZZAHRA', 'P', '2008-12-02', 'Jl. AH Nasution, Kelurahan Mulyasari, Kecamatan Tamansari, Kota Tasikmalaya', 1, '2026-11-05 00:51:59', '2026-11-05 00:51:59'),
(135, '102410568', '0082716425', 'TONI ANTONIO', 'L', '2008-05-30', 'Jl. Letjen Mashudi, Kelurahan Setiaratu, Kecamatan Cibeureum, Kota Tasikmalaya', 1, '2026-11-05 00:51:59', '2026-11-05 00:51:59');

-- --------------------------------------------------------

--
-- Table structure for table `t_tahun_ajaran`
--

CREATE TABLE `t_tahun_ajaran` (
  `id` int(11) NOT NULL,
  `nama` varchar(20) DEFAULT NULL,
  `tanggal_mulai` date NOT NULL,
  `tanggal_selesai` date NOT NULL,
  `status_aktif` tinyint(1) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `t_tahun_ajaran`
--

INSERT INTO `t_tahun_ajaran` (`id`, `nama`, `tanggal_mulai`, `tanggal_selesai`, `status_aktif`, `created_at`, `updated_at`) VALUES
(101, '2026/2027', '2026-07-01', '2027-06-30', 1, '2026-10-22 01:29:01', '2026-11-04 03:44:07'),
(103, '2027/2028', '2027-06-09', '2028-06-09', 0, '2026-11-04 03:44:48', '2026-11-04 03:44:48');

-- --------------------------------------------------------

--
-- Table structure for table `t_tentang_saya`
--

CREATE TABLE `t_tentang_saya` (
  `id` int(11) NOT NULL,
  `nama` varchar(150) NOT NULL,
  `kelas` varchar(100) NOT NULL,
  `sekolah` varchar(150) NOT NULL,
  `jurusan` varchar(150) NOT NULL,
  `hobi` varchar(255) NOT NULL,
  `cita_cita` varchar(255) NOT NULL,
  `deskripsi` text NOT NULL,
  `foto` varchar(255) DEFAULT 'assets/img/profil.jpg',
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `t_tentang_saya`
--

INSERT INTO `t_tentang_saya` (`id`, `nama`, `kelas`, `sekolah`, `jurusan`, `hobi`, `cita_cita`, `deskripsi`, `foto`, `updated_at`) VALUES
(1, 'Muhamad Fahril Fahrurozi', 'XII RPL 2', 'SMK Muhammadiyah Tasikmalaya', 'Rekayasa Perangkat Lunak', 'Gaming dan Sepak Bola', 'Sesuai Jalan Yang Allah Kasih Aja', 'Saya adalah seorang pelajar kelas XII RPL 2 di SMK Muhammadiyah Tasikmalaya, jurusan Rekayasa Perangkat Lunak. Saya senang mempelajari teknologi dan membuat website. Saya ingin terus belajar, mengembangkan kemampuan, dan berusaha menjadi seorang Web Developer. Aamiin.', 'assets/img/profil.jpg', '2026-11-05 02:42:12');

-- --------------------------------------------------------

--
-- Table structure for table `t_tindakan_siswa`
--

CREATE TABLE `t_tindakan_siswa` (
  `id` int(11) NOT NULL,
  `catatan_pelanggaran_id` int(11) NOT NULL,
  `jenis_tindakan` varchar(100) NOT NULL,
  `keterangan` text NOT NULL,
  `tanggal_tindakan` date NOT NULL,
  `dicatat_oleh` varchar(150) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `t_tindakan_siswa`
--

INSERT INTO `t_tindakan_siswa` (`id`, `catatan_pelanggaran_id`, `jenis_tindakan`, `keterangan`, `tanggal_tindakan`, `dicatat_oleh`, `created_at`) VALUES
(1, 2, 'Pembinaan', 'Hasil dari pembinaan, baik dan madev', '2026-11-04', 'Alex Bizer, S.H', '2026-11-04 00:43:27'),
(2, 4, 'Teguran Lisan', 'Memberi tahu dan memberi teguran', '2026-11-05', 'Ari Sandriana, S.Kom', '2026-11-05 01:23:49'),
(3, 3, 'Skorsing', 'Skorsing dan teguran lisan', '2026-11-05', 'Ari Sandriana, S.Kom', '2026-11-05 01:24:14');

-- --------------------------------------------------------

--
-- Table structure for table `t_users`
--

CREATE TABLE `t_users` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) DEFAULT NULL,
  `email_verified_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `password` varchar(255) DEFAULT NULL,
  `remember_token` varchar(100) NOT NULL,
  `role` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `t_users`
--

INSERT INTO `t_users` (`id`, `name`, `email`, `email_verified_at`, `password`, `remember_token`, `role`, `created_at`, `updated_at`) VALUES
(604, 'Administrator', 'admin@gmail.com', '2026-10-22 01:29:01', '$2y$10$h9554MUCUcD1Tnu.fq42euuCxiV2RS8knileKUqhPhO2WuXFOk7Pi', '', 'admin', '2026-10-22 01:29:01', '2026-10-22 01:29:01'),
(605, 'Muhamad Fahril', 'guru@gmail.com', '2026-10-22 01:29:01', '$2y$10$tGUQCMV8i8q0orGCt2KPGOLEEYa7c1VP5U7nK98UwsjBRJqsegLXS', '', 'guru', '2026-10-22 01:29:01', '2026-10-22 01:29:01'),
(609, 'Ai Nuryaldah, S.Pd', 'ai@gmail.com', '2026-11-04 03:55:33', NULL, '', 'guru', '2026-11-04 03:55:33', '2026-11-04 04:06:17'),
(610, 'Maya Permata Endah, S.Pd', 'maya@gmail.com', '2026-11-04 03:55:33', NULL, '', 'guru', '2026-11-04 03:55:33', '2026-11-04 04:06:17'),
(611, 'Dody Jaenudin, S.Pd', 'dody@gmail.com', '2026-11-04 03:55:33', NULL, '', 'guru', '2026-11-04 03:55:33', '2026-11-04 04:06:17'),
(612, 'Dedi Kardiman, S.Pd', 'dedi@gmail.com', '2026-11-04 03:55:33', NULL, '', 'guru', '2026-11-04 03:55:33', '2026-11-04 04:06:17'),
(613, 'Uca Musa Yusenda, S.Pd', 'uca@gmail.com', '2026-11-04 03:55:33', NULL, '', 'guru', '2026-11-04 03:55:33', '2026-11-04 04:06:17'),
(614, 'Drs. Ahmad Nur Mukhlis', 'ahmad@gmail.com', '2026-11-04 03:55:33', NULL, '', 'guru', '2026-11-04 03:55:33', '2026-11-04 04:06:17'),
(615, 'Drs. Heri', 'heri@gmail.com', '2026-11-04 03:55:33', NULL, '', 'guru', '2026-11-04 03:55:33', '2026-11-04 04:06:17'),
(617, 'H. Encep Maemun, S.T., M.Pd', 'encep@gmail.com', '2026-11-04 03:55:33', NULL, '', 'guru', '2026-11-04 03:55:33', '2026-11-04 04:06:17'),
(618, 'Opik', 'opik@gmail.com', '2026-11-04 03:55:33', NULL, '', 'guru_wali_kelas', '2026-11-04 03:55:33', '2026-11-04 04:06:17'),
(619, 'Ida Farida, S.Pd', 'ida@gmail.com', '2026-11-04 03:55:33', NULL, '', 'guru', '2026-11-04 03:55:33', '2026-11-04 04:06:17'),
(620, 'Idah Mahmudah, S.Pd', 'idah@gmail.com', '2026-11-04 03:55:33', NULL, '', 'guru', '2026-11-04 03:55:33', '2026-11-04 04:06:17'),
(621, 'Drs. Muhamad Ali', 'muhamad@gmail.com', '2026-11-04 03:55:33', NULL, '', 'guru', '2026-11-04 03:55:33', '2026-11-04 04:06:17'),
(622, 'Helmi Ali Akbar, S.Pd.I', 'helmi@gmail.com', '2026-11-04 03:55:33', NULL, '', 'guru', '2026-11-04 03:55:33', '2026-11-04 04:06:17'),
(623, 'Syeni Suswarini, S.Pd', 'syeni@gmail.com', '2026-11-04 03:55:33', NULL, '', 'guru', '2026-11-04 03:55:33', '2026-11-04 04:06:17'),
(624, 'Wiwid Yulianti, S.Pd', 'wiwid@gmail.com', '2026-11-04 03:55:33', NULL, '', 'guru', '2026-11-04 03:55:33', '2026-11-04 04:06:17'),
(625, 'Ansor Muhajir, S.T', 'ansor@gmail.com', '2026-11-04 03:55:33', NULL, '', 'guru', '2026-11-04 03:55:33', '2026-11-04 04:06:17'),
(626, 'Ogi Gaosun Gias, S.Pd.I', 'ogi@gmail.com', '2026-11-04 03:55:33', NULL, '', 'guru', '2026-11-04 03:55:33', '2026-11-04 04:06:17'),
(627, 'Sandi Mulyana, S.Pd', 'sandi@gmail.com', '2026-11-04 03:55:33', NULL, '', 'guru', '2026-11-04 03:55:33', '2026-11-04 04:06:17'),
(628, 'Erna Suryani, S.Pd', 'erna@gmail.com', '2026-11-04 03:55:33', NULL, '', 'guru', '2026-11-04 03:55:33', '2026-11-04 04:06:17'),
(629, 'Ai Wahidah, S.Ag', 'ai.wahidah@gmail.com', '2026-11-04 03:55:33', NULL, '', 'guru', '2026-11-04 03:55:33', '2026-11-04 04:06:17'),
(630, 'Chandra Aditya Yusup, S.Pd', 'chandra@gmail.com', '2026-11-04 03:55:33', NULL, '', 'guru', '2026-11-04 03:55:33', '2026-11-04 04:06:17'),
(632, 'Asep Taufiq Hidayat, S.Kom', 'asep.taufiq@gmail.com', '2026-11-04 03:55:33', NULL, '', 'guru', '2026-11-04 03:55:33', '2026-11-04 04:06:17'),
(633, 'Rendy Ichsan Kusumah, S.T', 'rendy@gmail.com', '2026-11-04 03:55:33', NULL, '', 'guru', '2026-11-04 03:55:33', '2026-11-04 04:06:17'),
(634, 'Feri Rahmawan, S.Pd', 'feri@gmail.com', '2026-11-04 03:55:33', NULL, '', 'guru', '2026-11-04 03:55:33', '2026-11-04 04:06:17'),
(636, 'Yaneu Nurdian, S.Pd, M.Pd', 'yaneu@gmail.com', '2026-11-04 03:55:33', NULL, '', 'guru', '2026-11-04 03:55:33', '2026-11-04 04:06:17'),
(637, 'Ucu Muhammad Khoer N, S.Pd', 'ucu@gmail.com', '2026-11-04 03:55:33', NULL, '', 'guru', '2026-11-04 03:55:33', '2026-11-04 04:06:17'),
(638, 'Mira Dwi Rahmawati, S.Ag', 'mira@gmail.com', '2026-11-04 03:55:33', NULL, '', 'guru', '2026-11-04 03:55:33', '2026-11-04 04:06:17'),
(639, 'Aldi Maulana, S.Pd', 'aldi@gmail.com', '2026-11-04 03:55:33', NULL, '', 'guru', '2026-11-04 03:55:33', '2026-11-04 04:06:17'),
(640, 'Eli Rosliah, S.Pd', 'eli@gmail.com', '2026-11-04 03:55:33', NULL, '', 'guru', '2026-11-04 03:55:33', '2026-11-04 04:06:17'),
(641, 'Isni Nur Fitriani, S.Pd', 'isni@gmail.com', '2026-11-04 03:55:33', NULL, '', 'guru', '2026-11-04 03:55:33', '2026-11-04 04:06:17'),
(642, 'Aanisah Putri Nur Aini, S.Pd', 'aanisah@gmail.com', '2026-11-04 03:55:33', NULL, '', 'guru', '2026-11-04 03:55:33', '2026-11-04 04:06:17'),
(643, 'Yusi Luthfiani, S.Sos.I', 'yusi@gmail.com', '2026-11-04 03:55:33', NULL, '', 'guru', '2026-11-04 03:55:33', '2026-11-04 04:06:17'),
(644, 'Lilis Rustandy Mawardi, S.Pd., M.Pd', 'lilis@gmail.com', '2026-11-04 03:55:33', NULL, '', 'wali_kelas', '2026-11-04 03:55:33', '2026-11-04 04:06:17'),
(645, 'Yusrin Noer Sabrina, S.Pd', 'yusrin@gmail.com', '2026-11-04 03:55:33', NULL, '', 'guru', '2026-11-04 03:55:33', '2026-11-04 04:06:17'),
(646, 'Senny Indayani, S.Pd', 'senny@gmail.com', '2026-11-04 03:55:33', NULL, '', 'guru', '2026-11-04 03:55:33', '2026-11-04 04:06:17'),
(647, 'Ilma Amalia, S.Pd', 'ilma@gmail.com', '2026-11-04 03:55:33', NULL, '', 'guru', '2026-11-04 03:55:33', '2026-11-04 04:06:17'),
(648, 'Alvira Sofwatunnisa, S.Pd', 'alvira@gmail.com', '2026-11-04 03:55:33', NULL, '', 'guru', '2026-11-04 03:55:33', '2026-11-04 04:06:17'),
(649, 'Risna Andriyani, S.Pd', 'risna@gmail.com', '2026-11-04 03:55:33', NULL, '', 'guru', '2026-11-04 03:55:33', '2026-11-04 04:06:17'),
(650, 'Dra. Tintin Noerhayati', 'tintin@gmail.com', '2026-11-04 03:55:33', NULL, '', 'guru', '2026-11-04 03:55:33', '2026-11-04 04:06:17'),
(651, 'Rizkia Fahira, S.Pd', 'rizkia@gmail.com', '2026-11-04 03:55:33', NULL, '', 'guru', '2026-11-04 03:55:33', '2026-11-04 04:06:17'),
(652, 'Frasetio', 'frasetio@gmail.com', '2026-11-04 03:55:33', NULL, '', 'guru', '2026-11-04 03:55:33', '2026-11-04 04:06:17'),
(653, 'Ratnaningsih Hati Aeni, S.Pd.', 'ratnaningsih@gmail.com', '2026-11-04 03:55:33', NULL, '', 'guru', '2026-11-04 03:55:33', '2026-11-04 04:06:17'),
(654, 'Dheny Darajatul Hasani, S.Pd.', 'dheny@gmail.com', '2026-11-04 03:55:33', NULL, '', 'guru', '2026-11-04 03:55:33', '2026-11-04 04:06:17'),
(655, 'Fahrullah, S.Pd', 'fahrullah@gmail.com', '2026-11-04 03:55:33', NULL, '', 'guru', '2026-11-04 03:55:33', '2026-11-04 04:06:17'),
(657, 'Fitri Fauziah, S.T', 'fitri@gmail.com', '2026-11-04 03:55:33', NULL, '', 'guru', '2026-11-04 03:55:33', '2026-11-04 04:06:17'),
(658, 'Nida Aulia, S.Pd', 'nida@gmail.com', '2026-11-04 03:55:33', NULL, '', 'guru', '2026-11-04 03:55:33', '2026-11-04 04:06:17'),
(659, 'Sri Nurrizki Putri Krisnadi, S.Pd', 'sri@gmail.com', '2026-11-04 03:55:33', NULL, '', 'guru', '2026-11-04 03:55:33', '2026-11-04 04:06:17'),
(660, 'Riesy Putri Navia, S.Kom', 'riesy@gmail.com', '2026-11-04 03:55:33', NULL, '', 'guru', '2026-11-04 03:55:33', '2026-11-04 04:06:17'),
(661, 'Rurry Febriani Maryadi, S.Pd', 'rurry@gmail.com', '2026-11-04 03:55:33', NULL, '', 'guru', '2026-11-04 03:55:33', '2026-11-04 04:06:17'),
(662, 'Irvan Rizal, S.Pd', 'irvan@gmail.com', '2026-11-04 03:55:33', NULL, '', 'guru', '2026-11-04 03:55:33', '2026-11-04 04:06:17'),
(671, 'Aan Andriana, S.T', 'aan@gmail.com', '2026-11-04 04:17:18', '$2y$10$bKSm3Tzzv85Dit1/sy1DcuEfo6EAfJ6FcCZDvBrG8bUQMmzowIp/G', '', 'guru', '2026-11-04 04:17:18', '2026-11-04 04:17:18'),
(672, 'Arif Kusmawan, S.Pd', 'arif@gmail.com', '2026-11-04 04:22:24', '$2y$10$3Ycz7gLW0qp7j0CX79kyJ.fEjdO12pZRZIOx2gVsWga4vbEvFHUsi', '', 'guru', '2026-11-04 04:22:24', '2026-11-04 04:22:24'),
(673, 'Wawan Kurniawan, S.Pd.I', 'wawan@gmail.com', '2026-11-04 04:23:36', '$2y$10$eh5rkfFlxsSD4gumaLs2S.CeeyQxNqj6PkMOt/3B2gzKcLTPkSfcm', '', 'guru', '2026-11-04 04:23:36', '2026-11-04 04:23:36'),
(674, 'Ari Sandriana, S.Kom', 'ari@gmail.com', '2026-11-04 04:24:51', '$2y$10$JeQlCqMoWqg9Tmdv1m.h.ONlAuE8coLb/MBR80uHK.QkwbfZWc/QK', '', 'guru', '2026-11-04 04:24:51', '2026-11-04 04:24:51'),
(675, 'Ansor Muhajir, S.T', 'ansor1@gmail.com', '2026-11-04 05:00:52', '$2y$10$k4vLtWUZXJrc7TmjTtW5qezf9DTCoeXNHpb9Fq4JGrUw5w6P4icbS', '', 'wali_kelas', '2026-11-04 05:00:52', '2026-11-04 05:00:52'),
(676, 'Ansor Muhajir, S.T', 'ansor2@gmail.com', '2026-11-04 05:07:47', '$2y$10$6uEB/VxL1fSrVcqLO7R.5uexHERnTZZ57jZ0c4giCh2vbO3iqKZ9W', '', 'wali_kelas', '2026-11-04 05:07:47', '2026-11-04 05:07:47'),
(677, 'inda Safitri', 'dinda@gmail.com', '2026-11-04 05:11:13', '$2y$10$C0R35hzUQ6aHVoltEkl3LesaQZ0BUlXH3wfSRluhYZN86X2RZD9gm', '', 'wali_kelas', '2026-11-04 05:11:13', '2026-11-04 05:11:13'),
(678, 'Asep Bastaman', 'asep@gmail.com', '2026-11-04 05:20:04', '$2y$10$u1o.ucrVDrO47AdZubcXqu/wYDeQC8Jhkda7BOjCw1s5Pg9FeNENK', '', 'guru', '2026-11-04 05:20:04', '2026-11-04 05:20:04');

-- --------------------------------------------------------

--
-- Table structure for table `t_wali_kelas`
--

CREATE TABLE `t_wali_kelas` (
  `id` int(11) NOT NULL,
  `tahun_ajaran_id` int(11) DEFAULT NULL,
  `kelas_id` int(11) DEFAULT NULL,
  `guru_id` int(11) DEFAULT NULL,
  `user_id` int(11) NOT NULL,
  `tanggal_mulai` date NOT NULL,
  `tanggal_selesai` date NOT NULL,
  `status_aktif` tinyint(1) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `t_wali_kelas`
--

INSERT INTO `t_wali_kelas` (`id`, `tahun_ajaran_id`, `kelas_id`, `guru_id`, `user_id`, `tanggal_mulai`, `tanggal_selesai`, `status_aktif`, `created_at`, `updated_at`) VALUES
(234, 101, 1, 662, 673, '2026-07-01', '2027-06-30', 1, '2026-11-04 04:31:55', '2026-11-04 04:23:58'),
(235, 101, 2, 663, 674, '2026-07-01', '2027-06-30', 1, '2026-11-04 04:31:55', '2026-11-04 04:25:21'),
(238, 101, 103, NULL, 677, '2026-07-01', '2027-06-30', 1, '2026-11-04 05:11:13', '2026-11-04 05:11:13');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `t_catatan_pelanggaran`
--
ALTER TABLE `t_catatan_pelanggaran`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_siswa` (`siswa_id`),
  ADD KEY `idx_pelanggaran` (`pelanggaran_id`),
  ADD KEY `idx_tanggal` (`tanggal_pelanggaran`);

--
-- Indexes for table `t_guru`
--
ALTER TABLE `t_guru`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `t_jenis_pelanggaran`
--
ALTER TABLE `t_jenis_pelanggaran`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_kategori` (`kategori_id`);

--
-- Indexes for table `t_kelas`
--
ALTER TABLE `t_kelas`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `t_kelas_siswa`
--
ALTER TABLE `t_kelas_siswa`
  ADD PRIMARY KEY (`id`),
  ADD KEY `siswa_id` (`siswa_id`),
  ADD KEY `tahun_ajaran_id` (`tahun_ajaran_id`),
  ADD KEY `kelas_id` (`kelas_id`);

--
-- Indexes for table `t_pelanggaran`
--
ALTER TABLE `t_pelanggaran`
  ADD PRIMARY KEY (`id`),
  ADD KEY `pelanggaran_kategori_id` (`pelanggaran_kategori_id`);

--
-- Indexes for table `t_pelanggaran_kategori`
--
ALTER TABLE `t_pelanggaran_kategori`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `t_pelanggaran_siswa`
--
ALTER TABLE `t_pelanggaran_siswa`
  ADD PRIMARY KEY (`id`),
  ADD KEY `tahun_ajaran_id` (`tahun_ajaran_id`),
  ADD KEY `siswa_id` (`siswa_id`),
  ADD KEY `kelas_id` (`kelas_id`),
  ADD KEY `pelanggaran_id` (`pelanggaran_id`),
  ADD KEY `pelanggaran_kategori_id` (`pelanggaran_kategori_id`),
  ADD KEY `guru_id` (`guru_id`);

--
-- Indexes for table `t_siswa`
--
ALTER TABLE `t_siswa`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `t_tahun_ajaran`
--
ALTER TABLE `t_tahun_ajaran`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `t_tentang_saya`
--
ALTER TABLE `t_tentang_saya`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `t_tindakan_siswa`
--
ALTER TABLE `t_tindakan_siswa`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_catatan_pelanggaran` (`catatan_pelanggaran_id`);

--
-- Indexes for table `t_users`
--
ALTER TABLE `t_users`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `t_wali_kelas`
--
ALTER TABLE `t_wali_kelas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `tahun_ajaran_id` (`tahun_ajaran_id`),
  ADD KEY `kelas_id` (`kelas_id`),
  ADD KEY `guru_id` (`guru_id`),
  ADD KEY `idx_wali_kelas_user_id` (`user_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `t_catatan_pelanggaran`
--
ALTER TABLE `t_catatan_pelanggaran`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `t_guru`
--
ALTER TABLE `t_guru`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=665;

--
-- AUTO_INCREMENT for table `t_jenis_pelanggaran`
--
ALTER TABLE `t_jenis_pelanggaran`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `t_kelas`
--
ALTER TABLE `t_kelas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=104;

--
-- AUTO_INCREMENT for table `t_kelas_siswa`
--
ALTER TABLE `t_kelas_siswa`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=143;

--
-- AUTO_INCREMENT for table `t_pelanggaran`
--
ALTER TABLE `t_pelanggaran`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=116;

--
-- AUTO_INCREMENT for table `t_pelanggaran_kategori`
--
ALTER TABLE `t_pelanggaran_kategori`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=108;

--
-- AUTO_INCREMENT for table `t_pelanggaran_siswa`
--
ALTER TABLE `t_pelanggaran_siswa`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=101;

--
-- AUTO_INCREMENT for table `t_siswa`
--
ALTER TABLE `t_siswa`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=136;

--
-- AUTO_INCREMENT for table `t_tahun_ajaran`
--
ALTER TABLE `t_tahun_ajaran`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=104;

--
-- AUTO_INCREMENT for table `t_tentang_saya`
--
ALTER TABLE `t_tentang_saya`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `t_tindakan_siswa`
--
ALTER TABLE `t_tindakan_siswa`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `t_users`
--
ALTER TABLE `t_users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=679;

--
-- AUTO_INCREMENT for table `t_wali_kelas`
--
ALTER TABLE `t_wali_kelas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=239;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `t_guru`
--
ALTER TABLE `t_guru`
  ADD CONSTRAINT `t_guru_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `t_users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `t_jenis_pelanggaran`
--
ALTER TABLE `t_jenis_pelanggaran`
  ADD CONSTRAINT `fk_jenis_kategori` FOREIGN KEY (`kategori_id`) REFERENCES `t_pelanggaran_kategori` (`id`) ON UPDATE CASCADE;

--
-- Constraints for table `t_kelas_siswa`
--
ALTER TABLE `t_kelas_siswa`
  ADD CONSTRAINT `t_kelas_siswa_ibfk_1` FOREIGN KEY (`tahun_ajaran_id`) REFERENCES `t_tahun_ajaran` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `t_kelas_siswa_ibfk_2` FOREIGN KEY (`kelas_id`) REFERENCES `t_kelas` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `t_kelas_siswa_ibfk_3` FOREIGN KEY (`siswa_id`) REFERENCES `t_siswa` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `t_pelanggaran`
--
ALTER TABLE `t_pelanggaran`
  ADD CONSTRAINT `t_pelanggaran_ibfk_1` FOREIGN KEY (`pelanggaran_kategori_id`) REFERENCES `t_pelanggaran_kategori` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `t_pelanggaran_siswa`
--
ALTER TABLE `t_pelanggaran_siswa`
  ADD CONSTRAINT `t_pelanggaran_siswa_ibfk_1` FOREIGN KEY (`tahun_ajaran_id`) REFERENCES `t_tahun_ajaran` (`id`),
  ADD CONSTRAINT `t_pelanggaran_siswa_ibfk_2` FOREIGN KEY (`guru_id`) REFERENCES `t_guru` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `t_pelanggaran_siswa_ibfk_3` FOREIGN KEY (`kelas_id`) REFERENCES `t_kelas` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `t_pelanggaran_siswa_ibfk_4` FOREIGN KEY (`pelanggaran_kategori_id`) REFERENCES `t_pelanggaran_kategori` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `t_pelanggaran_siswa_ibfk_5` FOREIGN KEY (`pelanggaran_id`) REFERENCES `t_pelanggaran` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `t_pelanggaran_siswa_ibfk_6` FOREIGN KEY (`siswa_id`) REFERENCES `t_siswa` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `t_wali_kelas`
--
ALTER TABLE `t_wali_kelas`
  ADD CONSTRAINT `fk_wali_kelas_user` FOREIGN KEY (`user_id`) REFERENCES `t_users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `t_wali_kelas_ibfk_1` FOREIGN KEY (`guru_id`) REFERENCES `t_guru` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `t_wali_kelas_ibfk_2` FOREIGN KEY (`kelas_id`) REFERENCES `t_kelas` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `t_wali_kelas_ibfk_3` FOREIGN KEY (`tahun_ajaran_id`) REFERENCES `t_tahun_ajaran` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
