-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jul 06, 2026 at 08:22 AM
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
-- Database: `projekdss`
--

-- --------------------------------------------------------

--
-- Table structure for table `bobot_kriteria`
--

CREATE TABLE `bobot_kriteria` (
  `id` int(11) NOT NULL,
  `jenis_beasiswa` enum('prestasi','kurang_mampu') NOT NULL,
  `bobot_raport` decimal(3,2) NOT NULL,
  `bobot_penghasilan` decimal(3,2) NOT NULL,
  `bobot_tanggungan` decimal(3,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `bobot_kriteria`
--

INSERT INTO `bobot_kriteria` (`id`, `jenis_beasiswa`, `bobot_raport`, `bobot_penghasilan`, `bobot_tanggungan`) VALUES
(1, 'prestasi', 0.80, 0.60, 0.40),
(2, 'kurang_mampu', 0.60, 0.80, 0.40);

-- --------------------------------------------------------

--
-- Table structure for table `setting_kuota`
--

CREATE TABLE `setting_kuota` (
  `id` int(11) NOT NULL,
  `jenis_beasiswa` enum('prestasi','kurang_mampu') NOT NULL,
  `jumlah_kuota` int(11) NOT NULL DEFAULT 0,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `setting_kuota`
--

INSERT INTO `setting_kuota` (`id`, `jenis_beasiswa`, `jumlah_kuota`, `updated_at`) VALUES
(1, 'prestasi', 3, '2026-06-11 11:09:58'),
(2, 'kurang_mampu', 3, '2026-06-11 11:09:58');

-- --------------------------------------------------------

--
-- Table structure for table `siswa`
--

CREATE TABLE `siswa` (
  `id` int(11) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `nis` varchar(20) NOT NULL,
  `jenis_kelamin` enum('P','L') NOT NULL,
  `tanggal_lahir` date NOT NULL,
  `alamat` text NOT NULL,
  `kelas` varchar(20) NOT NULL,
  `nilai_raport` decimal(5,2) NOT NULL,
  `penghasilan_ortu` decimal(15,2) NOT NULL,
  `tanggungan_ortu` int(11) NOT NULL,
  `jenis_beasiswa` enum('prestasi','kurang_mampu') NOT NULL,
  `skor_akhir` decimal(10,4) DEFAULT 0.0000,
  `ranking` int(11) DEFAULT 0,
  `status_penerima` enum('pending','diterima','ditolak') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `siswa`
--

INSERT INTO `siswa` (`id`, `nama`, `nis`, `jenis_kelamin`, `tanggal_lahir`, `alamat`, `kelas`, `nilai_raport`, `penghasilan_ortu`, `tanggungan_ortu`, `jenis_beasiswa`, `skor_akhir`, `ranking`, `status_penerima`, `created_at`, `updated_at`) VALUES
(18, 'andi', '101', 'L', '2005-11-10', 'semarang', 'x TKJ 1', 90.00, 2000000.00, 3, 'prestasi', 1.2740, 4, 'ditolak', '2026-06-11 04:25:22', '2026-06-11 04:30:57'),
(19, 'evi', '102', 'P', '2005-06-03', 'kendal', 'x TKj 2', 80.00, 3000000.00, 2, 'prestasi', 1.0271, 5, 'ditolak', '2026-06-11 04:26:50', '2026-06-11 04:30:57'),
(20, 'galeh', '103', 'L', '2006-07-10', 'kendal', 'x tkj3', 90.00, 870000.00, 3, 'prestasi', 1.5779, 2, 'diterima', '2026-06-11 04:27:58', '2026-06-11 04:32:25'),
(21, 'Ikah', '104', 'P', '2006-06-04', 'semarang', 'x tkj 4', 89.00, 780000.00, 4, 'prestasi', 1.7111, 1, 'diterima', '2026-06-11 04:29:27', '2026-06-11 04:32:25'),
(22, 'lia', '105', 'P', '2006-05-10', 'boja', 'x TKJ5', 87.00, 2300000.00, 5, 'prestasi', 1.3768, 3, 'diterima', '2026-06-11 04:30:35', '2026-06-11 11:17:53'),
(23, 'april', '106', 'P', '2006-08-10', 'sukoharjo', 'x tkj 5', 68.00, 1500000.00, 4, 'kurang_mampu', 1.3895, 2, 'diterima', '2026-06-11 11:11:37', '2026-06-11 11:17:53'),
(24, 'feri', '107', 'L', '2006-05-04', 'kendal', 'x tkj 4', 78.00, 1500000.00, 1, 'kurang_mampu', 1.2126, 4, 'ditolak', '2026-06-11 11:12:52', '2026-06-11 11:17:53'),
(26, 'heni', '108', 'P', '2006-03-02', 'semarang', 'x tkj3', 82.00, 1200000.00, 5, 'kurang_mampu', 1.7179, 1, 'diterima', '2026-06-11 11:14:48', '2026-06-11 11:17:53'),
(27, 'joko', '109', 'L', '2006-06-04', 'kebumen', 'x tkj 2', 57.00, 4000000.00, 3, 'kurang_mampu', 0.8400, 5, 'ditolak', '2026-06-11 11:15:45', '2026-06-11 11:17:53'),
(28, 'robin', '110', 'L', '2006-03-05', 'bandungan', 'x tkj 1', 95.00, 1700000.00, 2, 'kurang_mampu', 1.3247, 3, 'diterima', '2026-06-11 11:16:37', '2026-06-11 11:17:53');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','staff') DEFAULT 'staff',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `role`, `created_at`) VALUES
(3, 'yusuf', '$2y$10$isRjnkTL.GBhBXavQzhoD.CB.dwJcRgVOxUKgLYu8rtfSTbx5S78.', 'admin', '2026-04-14 11:35:35');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `bobot_kriteria`
--
ALTER TABLE `bobot_kriteria`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `jenis_beasiswa` (`jenis_beasiswa`);

--
-- Indexes for table `setting_kuota`
--
ALTER TABLE `setting_kuota`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `jenis_beasiswa` (`jenis_beasiswa`);

--
-- Indexes for table `siswa`
--
ALTER TABLE `siswa`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nis` (`nis`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `bobot_kriteria`
--
ALTER TABLE `bobot_kriteria`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `setting_kuota`
--
ALTER TABLE `setting_kuota`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `siswa`
--
ALTER TABLE `siswa`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
