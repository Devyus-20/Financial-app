-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jul 19, 2025 at 08:47 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.1.25

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `zakat`
--

-- --------------------------------------------------------

--
-- Table structure for table `add_akun`
--

CREATE TABLE `add_akun` (
  `id` varchar(10) NOT NULL,
  `nama_akun` varchar(255) NOT NULL,
  `pembayaran` varchar(50) NOT NULL,
  `kelompok` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `add_akun`
--

INSERT INTO `add_akun` (`id`, `nama_akun`, `pembayaran`, `kelompok`) VALUES
('1', 'AKTIVA', 'Debit', 'Neraca'),
('1.01', 'kas', 'Debit', 'Neraca'),
('2', 'PASSIVA', 'kredit', 'Neraca'),
('3', 'Modal', 'Kredit', 'Neraca'),
('4', 'Penerimaan', 'Kredit', 'Penerimaan'),
('4.1', 'Penerimaan zakat', 'Debit', 'Penerimaan'),
('5', 'Biaya', 'Debit', 'Beban');

-- --------------------------------------------------------

--
-- Table structure for table `buku_besar`
--

CREATE TABLE `buku_besar` (
  `id` int(11) NOT NULL,
  `id_jurnal` int(11) DEFAULT NULL,
  `date` date NOT NULL,
  `notes` varchar(100) NOT NULL,
  `tipe` varchar(200) NOT NULL,
  `akun` varchar(100) NOT NULL,
  `perkiraan` varchar(100) NOT NULL,
  `akun_perkiraan` varchar(100) NOT NULL,
  `debit` int(50) NOT NULL,
  `kredit` int(50) NOT NULL,
  `nilai` int(50) NOT NULL,
  `status` varchar(100) NOT NULL,
  `kelompok_debit` varchar(100) NOT NULL,
  `kelompok_kredit` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `buku_besar`
--

INSERT INTO `buku_besar` (`id`, `id_jurnal`, `date`, `notes`, `tipe`, `akun`, `perkiraan`, `akun_perkiraan`, `debit`, `kredit`, `nilai`, `status`, `kelompok_debit`, `kelompok_kredit`) VALUES
(110, 2221116, '2025-07-14', 'jasa pengembangan aplikasi', 'Debit', '1 - AKTIVA', 'AKTIVA', '1 - AKTIVA', 120000, 0, 120000, 'posting', 'Neraca', 'Neraca'),
(111, 2221116, '2025-07-14', 'jasa pengembangan aplikasi', 'Kredit', '3 - Modal', 'Modal', '3 - Modal', 0, 120000, 120000, 'posting', 'Neraca', 'Neraca');

-- --------------------------------------------------------

--
-- Table structure for table `jurnal_transaksi`
--

CREATE TABLE `jurnal_transaksi` (
  `id_jurnal` int(11) NOT NULL,
  `tgl_trans` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `deskripsi` varchar(50) NOT NULL,
  `referensi` varchar(100) NOT NULL,
  `akun_debit` varchar(100) NOT NULL,
  `akun_kredit` varchar(100) NOT NULL,
  `jumlah` int(15) NOT NULL,
  `status` varchar(100) NOT NULL,
  `kelompok_debit` varchar(100) NOT NULL,
  `kelompok_kredit` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `jurnal_transaksi`
--

INSERT INTO `jurnal_transaksi` (`id_jurnal`, `tgl_trans`, `deskripsi`, `referensi`, `akun_debit`, `akun_kredit`, `jumlah`, `status`, `kelompok_debit`, `kelompok_kredit`) VALUES
(2221116, '2025-07-14 00:00:00', 'jasa pengembangan aplikasi', 'Pengembangan Aplikasi', '1 - AKTIVA', '3 - Modal', 120000, 'posting', 'Neraca', 'Neraca');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `first_name` varchar(255) NOT NULL,
  `last_name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `role` enum('admin','manager','user') DEFAULT 'user',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `last_login` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `first_name`, `last_name`, `email`, `password_hash`, `role`, `created_at`, `updated_at`, `last_login`) VALUES
(3, 'Awaludin', 'Yusuf Sou', 'awaludinyusuf23@gmail.com', '$2y$10$8r1oc.sdLWMqIK14OgktvupwIjsZLAuG/7U9Rjfzd5dE1EZnscM/O', 'admin', '2025-07-08 06:45:16', '2025-07-19 18:11:17', '2025-07-20 01:11:17'),
(5, 'Nurul', 'Khalimah', 'nurulkhalimah@gmail.com', '$2y$10$APuun1UcioUEfEs2OIfE9.o9nFO4hDXVDAMXlJ63BxKxFkrJPe0le', 'manager', '2025-07-08 06:45:16', '2025-07-08 14:12:25', NULL),
(7, 'Super', 'Admin', 'admin@example.com', '$2y$10$KPLB5mI0...hashdi', 'admin', '2025-07-08 06:45:16', '2025-07-08 06:45:16', NULL),
(11, 'Juan', 'Rizky', 'Juanr@gmail.com', '$2y$10$uEd/m1wSg711k1rceFX2c.E6jMTaWO8swik8d93vMQp.Ebcd63hhC', 'manager', '2025-07-09 02:58:28', '2025-07-09 04:22:02', NULL),
(12, 'Reyvan', 'Sou', 'Reyvan23@gmail.com', '$2y$10$XTVpYPBUVfa82BCw2FBXUe486Uif/ZYrPZT4cO/5Fq8nF8sHpFnHi', 'manager', '2025-07-13 05:41:06', '2025-07-16 10:38:12', '2025-07-16 17:38:12'),
(13, 'zahra', 'nurul', 'zahranurul20@gmail.com', '$2y$10$XcIc7Il8qN61Ou.9eSuPiOBNbz3CARGkBkb37pRo/pTySQOX/cS5m', 'manager', '2025-07-17 10:14:42', '2025-07-17 10:20:38', '2025-07-17 17:20:38'),
(14, 'Alif Noor', 'Fauzan', 'Alifnoorfauzan20@gmail.com', '$2y$10$wWrR7f7sCEIqi6qXCuEy8u0Y2PRE3EO1zNSkTimWHTZDOUrYbiFky', 'manager', '2025-07-19 17:18:56', '2025-07-19 17:18:56', NULL),
(15, 'Hendra', 'Setiawan', 'Hendra20@gmail.com', '$2y$10$LZ.ayv9NrcsmTrNloMaMHOp5AEfg34O3xqY3tiCPfwiecfIKHUmua', 'manager', '2025-07-19 17:21:02', '2025-07-19 17:21:02', NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `add_akun`
--
ALTER TABLE `add_akun`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `buku_besar`
--
ALTER TABLE `buku_besar`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_jurnal` (`id_jurnal`);

--
-- Indexes for table `jurnal_transaksi`
--
ALTER TABLE `jurnal_transaksi`
  ADD PRIMARY KEY (`id_jurnal`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_user_email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `buku_besar`
--
ALTER TABLE `buku_besar`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=112;

--
-- AUTO_INCREMENT for table `jurnal_transaksi`
--
ALTER TABLE `jurnal_transaksi`
  MODIFY `id_jurnal` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2221117;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `buku_besar`
--
ALTER TABLE `buku_besar`
  ADD CONSTRAINT `buku_besar_ibfk_1` FOREIGN KEY (`id_jurnal`) REFERENCES `jurnal_transaksi` (`id_jurnal`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
