-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Oct 28, 2026 at 01:35 AM
-- Server version: 8.4.3
-- PHP Version: 8.3.33

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `db5d`
--

-- --------------------------------------------------------

--
-- Table structure for table `nilai_mhsw`
--

CREATE TABLE `nilai_mhsw` (
  `nim` varchar(20) NOT NULL,
  `nama_mhsw` char(10) NOT NULL,
  `nilai_akhir` decimal(5,2) NOT NULL,
  `nilai_huruf` char(1) NOT NULL,
  `predikat` varchar(50) NOT NULL,
  `reg_date` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `foto` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `nilai_mhsw`
--

INSERT INTO `nilai_mhsw` (`nim`, `nama_mhsw`, `nilai_akhir`, `nilai_huruf`, `predikat`, `reg_date`, `foto`) VALUES
('3202416101', 'Ahmad', 85.00, 'A', 'Sangat Memuaskan', '2026-10-08 03:58:29', '3202416101_1793103291.jpg'),
('3202416102', 'Budi', 78.00, 'B', 'Memuaskan', '2026-10-08 03:58:29', '3202416102_1793103316.jpg'),
('3202416103', 'Citra', 92.00, 'A', 'Sangat Memuaskan', '2026-10-08 03:58:29', '3202416103_1793103325.jpg'),
('3202416104', 'Deni', 67.00, 'B', 'Memuaskan', '2026-10-08 03:58:29', '3202416104_1793103342.jpg'),
('3202416105', 'Eka', 55.00, 'C', 'Cukup Memuaskan', '2026-10-08 03:58:29', '3202416105_1793103356.jpg'),
('3202416106', 'Fajar', 45.00, 'D', 'Kurang Memuaskan', '2026-10-08 03:58:29', '3202416106_1793103377.jpg'),
('3202416107', 'Gita', 88.00, 'A', 'Sangat Memuaskan', '2026-10-08 03:58:29', '3202416107_1793103392.jpg'),
('3202416108', 'Hadi', 73.00, 'B', 'Memuaskan', '2026-10-08 03:58:29', '3202416108_1793103405.jpg'),
('3202416109', 'Intan', 64.00, 'C', 'Cukup Memuaskan', '2026-10-08 03:58:29', '3202416109_1793103448.jpg'),
('3202416110', 'Joko', 32.00, 'E', 'Tidak Lulus', '2026-10-08 03:58:29', '3202416110_1793103474.jpg'),
('3202416111', 'Kiki', 81.00, 'A', 'Sangat Memuaskan', '2026-10-08 03:58:29', '3202416111_1793103487.jpg'),
('3202416112', 'Lina', 69.00, 'B', 'Memuaskan', '2026-10-08 03:58:29', '3202416112_1793103501.jpg'),
('3202416113', 'Nazriel', 100.00, 'A', 'Sangat Memuaskan', '2026-10-07 07:42:59', '3202416113_1793103689.jpeg'),
('3202416114', 'Nanda', 49.00, 'D', 'Kurang Memuaskan', '2026-10-08 03:58:29', '3202416114_1793103512.jpg'),
('3202416115', 'Oki', 35.00, 'D', 'Kurang Memuaskan', '2026-10-08 03:58:29', '3202416115_1793104221.jpg'),
('3202416116', 'Putri', 95.00, 'A', 'Sangat Memuaskan', '2026-10-08 03:58:29', '3202416116_1793103550.jpg'),
('3202416118', 'Rian', 62.00, 'C', 'Cukup Memuaskan', '2026-10-08 03:58:29', '3202416118_1793103565.jpg'),
('3202416119', 'Sari', 50.00, 'D', 'Kurang Memuaskan', '2026-10-08 03:58:29', '3202416119_1793103578.jpg'),
('3202416120', 'Tono', 30.00, 'E', 'Tidak Lulus', '2026-10-08 03:58:29', '3202416120_1793103588.jpg'),
('3202416121', 'Udin', 83.00, 'A', 'Sangat Memuaskan', '2026-10-08 03:58:29', '3202416121_1793103610.jpg'),
('3202416122', 'Vina', 71.00, 'B', 'Memuaskan', '2026-10-08 03:58:29', '3202416122_1793103628.jpg'),
('3202416123', 'Wawan', 60.00, 'C', 'Cukup Memuaskan', '2026-10-08 03:58:29', '3202416123_1793104237.jpg'),
('3202416125', 'Zaki', 25.00, 'E', 'Tidak Lulus', '2026-10-08 03:58:29', '3202416125_1793104293.jpg'),
('3202416126', 'Aldi', 90.00, 'A', 'Sangat Memuaskan', '2026-10-08 03:58:29', '3202416126_1793104308.jpg'),
('3202416127', 'Bella', 80.00, 'B', 'Memuaskan', '2026-10-08 03:58:29', '3202416127_1793105311.jpg'),
('3202416128', 'Caca', 65.00, 'C', 'Cukup Memuaskan', '2026-10-08 03:58:29', '3202416128_1793105323.jpg'),
('3202416129', 'Dimas', 52.00, 'C', 'Cukup Memuaskan', '2026-10-08 03:58:29', '3202416129_1793104329.jpg'),
('3202416130', 'Elsa', 48.00, 'D', 'Kurang Memuaskan', '2026-10-08 03:58:29', '3202416130_1793105345.jpg'),
('3202416131', 'Farhan', 100.00, 'A', 'Sangat Memuaskan', '2026-10-08 03:58:29', '3202416131_1793104373.jpg'),
('3202416132', 'Galih', 79.00, 'B', 'Memuaskan', '2026-10-08 03:58:29', '3202416132_1793106080.jpg'),
('3202416133', 'Hana', 66.00, 'B', 'Memuaskan', '2026-10-08 03:58:29', '3202416133_1793106093.jpg'),
('3202416134', 'Maya', 51.00, 'C', 'Cukup Memuaskan', '2026-10-08 03:58:29', '3202416134_1793106105.jpg'),
('3202416135', 'Jihan', 34.00, 'E', 'Tidak Lulus', '2026-10-08 03:58:29', '3202416135_1793106117.jpg'),
('3202416136', 'Kevin', 87.00, 'A', 'Sangat Memuaskan', '2026-10-08 03:58:29', '3202416136_1793104390.jpg'),
('3202416137', 'Laila', 75.00, 'B', 'Memuaskan', '2026-10-08 03:58:29', '3202416137_1793106133.jpg'),
('3202416138', 'Miko', 63.00, 'C', 'Cukup Memuaskan', '2026-10-08 03:58:29', '3202416138_1793104409.jpg'),
('3202416139', 'Nia', 50.50, 'D', 'Kurang Memuaskan', '2026-10-08 03:58:29', '3202416139_1793106147.jpg'),
('3202416140', 'Oscar', 20.00, 'E', 'Tidak Lulus', '2026-10-08 03:58:29', '3202416140_1793104592.jpg'),
('3202416141', 'Pandu', 82.00, 'A', 'Sangat Memuaskan', '2026-10-08 03:58:29', '3202416141_1793104419.jpg'),
('3202416142', 'Rara', 68.00, 'B', 'Memuaskan', '2026-10-08 03:58:29', '3202416142_1793106162.jpg'),
('3202416143', 'Rizky', 57.00, 'C', 'Cukup Memuaskan', '2026-10-08 03:58:29', '3202416143_1793104614.jpg'),
('3202416144', 'Sinta', 44.00, 'D', 'Kurang Memuaskan', '2026-10-08 03:58:29', '3202416144_1793106194.jpg'),
('3202416146', 'Vito', 91.00, 'A', 'Sangat Memuaskan', '2026-10-08 03:58:29', '3202416146_1793104623.jpg'),
('3202416147', 'Wira', 74.00, 'B', 'Memuaskan', '2026-10-08 03:58:29', '3202416147_1793104687.jpg'),
('3202416149', 'Zahra', 70.00, 'B', 'Memuaskan', '2026-10-08 03:58:29', '3202416149_1793106314.jpg'),
('3202416150', 'Ayu', 38.00, 'D', 'Kurang Memuaskan', '2026-10-08 03:58:29', '3202416150_1793106326.jpg'),
('3202416152', 'Ilham', 58.00, 'C', 'Cukup Memuaskan', '2026-10-08 03:58:29', '3202416152_1793104571.jpg'),
('3202416170', 'Qori', 76.00, 'B', 'Memuaskan', '2026-10-08 03:58:29', '3202416170_1793104560.jpg'),
('3202416300', 'Zidan', 100.00, 'A', 'Sangat Memuaskan', '2026-10-14 07:18:50', '3202416300_1793103283.jpg'),
('3202416400', 'ahyas', 90.00, 'A', 'Sangat Memuaskan', '2026-10-26 03:44:58', '3202416400_1793103275.jpg');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `nilai_mhsw`
--
ALTER TABLE `nilai_mhsw`
  ADD PRIMARY KEY (`nim`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
