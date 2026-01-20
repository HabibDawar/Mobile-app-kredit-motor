-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 03, 2025 at 10:53 PM
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
-- Database: `jskreditmotor`
--

DELIMITER $$
--
-- Procedures
--
CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_pelanggan_motor_150cc` ()   BEGIN
    SELECT 
        kr.`idkreditor`,
        kr.`nama`,
        kr.`alamat`,
        kr.`telp`,
        m.`nama` as `motor`,
        m.`kdmotor`
    FROM `kreditor` kr
    JOIN `kredit` k ON kr.`idkreditor` = k.`idkreditor`
    JOIN `motor` m ON k.`kdmotor` = m.`kdmotor`
    WHERE m.`nama` LIKE '%150%' OR m.`kdmotor` LIKE '%150%';
END$$

--
-- Functions
--
CREATE DEFINER=`root`@`localhost` FUNCTION `fn_total_kredit_bulanan` (`bulan` INT, `tahun` INT) RETURNS DECIMAL(15,2) DETERMINISTIC READS SQL DATA BEGIN
    DECLARE total DECIMAL(15,2);
    SELECT COALESCE(SUM(`totalkredit`), 0) INTO total 
    FROM `kredit` 
    WHERE MONTH(`tanggal`) = bulan AND YEAR(`tanggal`) = tahun;
    RETURN total;
END$$

DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `angsuran`
--

CREATE TABLE `angsuran` (
  `id_angsuran` int(11) NOT NULL,
  `invoice` varchar(50) NOT NULL,
  `angsuran_ke` int(11) NOT NULL,
  `jatuh_tempo` date NOT NULL,
  `jumlah` decimal(15,2) NOT NULL,
  `status` enum('belum bayar','lunas','lewati jatuh tempo') DEFAULT 'belum bayar',
  `tanggal_bayar` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `angsuran`
--

INSERT INTO `angsuran` (`id_angsuran`, `invoice`, `angsuran_ke`, `jatuh_tempo`, `jumlah`, `status`, `tanggal_bayar`, `created_at`) VALUES
(1, '3', 1, '2025-12-03', 2147483647.00, 'belum bayar', NULL, '2025-11-03 20:18:35'),
(2, '3', 2, '2026-01-03', 2147483647.00, 'belum bayar', NULL, '2025-11-03 20:18:35'),
(3, '3', 3, '2026-02-03', 2147483647.00, 'belum bayar', NULL, '2025-11-03 20:18:35'),
(4, '3', 4, '2026-03-03', 2147483647.00, 'belum bayar', NULL, '2025-11-03 20:18:35'),
(5, '3', 5, '2026-04-03', 2147483647.00, 'belum bayar', NULL, '2025-11-03 20:18:35'),
(6, '3', 6, '2026-05-03', 2147483647.00, 'belum bayar', NULL, '2025-11-03 20:18:35'),
(7, '3', 7, '2026-06-03', 2147483647.00, 'belum bayar', NULL, '2025-11-03 20:18:35'),
(8, '3', 8, '2026-07-03', 2147483647.00, 'belum bayar', NULL, '2025-11-03 20:18:35'),
(9, '3', 9, '2026-08-03', 2147483647.00, 'belum bayar', NULL, '2025-11-03 20:18:35'),
(10, '3', 10, '2026-09-03', 2147483647.00, 'belum bayar', NULL, '2025-11-03 20:18:35'),
(11, '3', 11, '2026-10-03', 2147483647.00, 'belum bayar', NULL, '2025-11-03 20:18:35'),
(12, '3', 12, '2026-11-03', 2147483647.00, 'belum bayar', NULL, '2025-11-03 20:18:35');

-- --------------------------------------------------------

--
-- Table structure for table `kredit`
--

CREATE TABLE `kredit` (
  `invoice` int(11) NOT NULL,
  `tanggal` timestamp NOT NULL DEFAULT current_timestamp(),
  `idkreditor` int(11) NOT NULL,
  `kdmotor` varchar(10) NOT NULL,
  `hrgtunai` int(11) DEFAULT NULL,
  `dp` int(11) DEFAULT NULL,
  `hrgkredit` int(11) DEFAULT NULL,
  `bunga` int(11) DEFAULT NULL,
  `lama` int(11) DEFAULT NULL,
  `totalkredit` int(11) DEFAULT NULL,
  `angsuran` int(11) DEFAULT NULL,
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `approved_by` int(11) DEFAULT NULL,
  `approved_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `kredit`
--

INSERT INTO `kredit` (`invoice`, `tanggal`, `idkreditor`, `kdmotor`, `hrgtunai`, `dp`, `hrgkredit`, `bunga`, `lama`, `totalkredit`, `angsuran`, `status`, `approved_by`, `approved_at`) VALUES
(3, '2025-10-20 19:17:35', 5, '(15)', 35000000, 5000000, 30000000, 3000000, 12, 2147483647, 2147483647, 'approved', 0, '2025-11-03 20:18:35'),
(4, '2025-11-03 16:37:00', 1, '0012', 25000000, 1000000, 24000000, 100000, 12, 2147483647, 2002000000, 'pending', NULL, NULL);

--
-- Triggers `kredit`
--
DELIMITER $$
CREATE TRIGGER `tr_after_insert_kredit` AFTER INSERT ON `kredit` FOR EACH ROW BEGIN
    -- Insert log aktivitas (jika ada tabel log)
    INSERT INTO `tb_log` (`aksi`, `tabel`, `id_data`, `keterangan`)
    VALUES ('INSERT', 'kredit', NEW.`invoice`, CONCAT('Kredit baru dibuat - Invoice: ', NEW.`invoice`));
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `kreditor`
--

CREATE TABLE `kreditor` (
  `idkreditor` int(11) NOT NULL,
  `nama` varchar(50) NOT NULL,
  `pekerjaan` varchar(50) NOT NULL,
  `telp` varchar(16) NOT NULL,
  `alamat` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `kreditor`
--

INSERT INTO `kreditor` (`idkreditor`, `nama`, `pekerjaan`, `telp`, `alamat`) VALUES
(1, 'ERDIAN', 'Karyawan', '0812233400', 'Jl. Garuda'),
(2, 'JOIN', 'Mahasiswa', '0812345', 'Jl. Majapahit'),
(3, 'TRI', 'SATPAM', '08123', 'Jl. Bentang'),
(4, 'IKA', 'GURU', '0812333', 'Jl. Kanguru'),
(5, 'ZULFA NURUL', 'MAHASISWA', '081234566', 'Jl. Majapahit');

-- --------------------------------------------------------

--
-- Table structure for table `motor`
--

CREATE TABLE `motor` (
  `idmotor` int(11) NOT NULL,
  `kdmotor` varchar(10) NOT NULL,
  `nama` varchar(30) DEFAULT NULL,
  `harga` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `motor`
--

INSERT INTO `motor` (`idmotor`, `kdmotor`, `nama`, `harga`) VALUES
(1, '0125', 'SUPPA X 125 CC RACING', 16500000),
(2, 'W125', 'SUPPA X 125CC TROMOL', 15500000),
(3, 'T150', 'VARIO TECHNO 150CC', 21000000),
(4, '50H', 'Ninja Sport 250CC', 55000000),
(5, '150', 'HONDA POX 150CC', 35000000),
(6, '(15)', 'Yamaha NMAX 155cc', 35000000),
(7, '0012', 'SUZUKI NSX100CC', 25000000),
(8, '125', 'HONDA BEAT 150CC', 18000000),
(9, '01', 'HONDA POX PUTIH', 35000000),
(10, 'TEST001', 'Motor Test Commit', 10000000);

-- --------------------------------------------------------

--
-- Table structure for table `petugas`
--

CREATE TABLE `petugas` (
  `idpetugas` int(11) NOT NULL,
  `kdpetugas` int(11) NOT NULL,
  `nama` varchar(50) DEFAULT NULL,
  `jabatan` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `petugas`
--

INSERT INTO `petugas` (`idpetugas`, `kdpetugas`, `nama`, `jabatan`) VALUES
(1, 111, 'AGus SAntoso', 'Manager'),
(2, 222, 'BUDI SANTOSO', 'SALES'),
(3, 333, 'CANOVA', 'SALES'),
(4, 444, 'DONOAQUS', 'SALES'),
(5, 555, 'JONO', 'SUPERVISOR'),
(6, 190, '2PUTRI ABC', 'KASIR'),
(7, 125, 'Alex Xander', 'Manager');

-- --------------------------------------------------------

--
-- Table structure for table `tbpetugas`
--

CREATE TABLE `tbpetugas` (
  `idpetugas` int(11) NOT NULL,
  `kdpetugas` varchar(10) NOT NULL,
  `nama` varchar(50) NOT NULL,
  `jabatan` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbpetugas`
--

INSERT INTO `tbpetugas` (`idpetugas`, `kdpetugas`, `nama`, `jabatan`) VALUES
(1, '111', 'AGus SAntoso', 'Manager'),
(2, '222', 'BUDI SANTOSO', 'SALES'),
(3, '333', 'CANOVA', 'SALES'),
(4, '444', 'DONOAQUS', 'SALES'),
(5, '555', 'JONO', 'SUPERVISOR'),
(6, '190', '2PUTRI ABC', 'KASIR'),
(7, '125', 'Alex Xander', 'Manager');

-- --------------------------------------------------------

--
-- Table structure for table `tbuser`
--

CREATE TABLE `tbuser` (
  `iduser` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `level` enum('admin','pelanggan') NOT NULL,
  `idkreditor` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbuser`
--

INSERT INTO `tbuser` (`iduser`, `username`, `password`, `level`, `idkreditor`, `created_at`, `updated_at`) VALUES
(1, 'admin', '0192023a7bbd73250516f069df18b500', 'admin', NULL, '2025-11-02 20:16:17', '2025-11-02 20:16:17'),
(2, 'manager', '0795151defba7a4b5dfa89170de46277', 'admin', NULL, '2025-11-02 20:16:17', '2025-11-02 20:16:17'),
(3, 'erdian', '325077d1d7b6fa325b095fb212f3bc42', 'pelanggan', 1, '2025-11-02 20:16:17', '2025-11-02 20:16:17'),
(4, 'join', '325077d1d7b6fa325b095fb212f3bc42', 'pelanggan', 2, '2025-11-02 20:16:17', '2025-11-02 20:16:17'),
(5, 'tri', '325077d1d7b6fa325b095fb212f3bc42', 'pelanggan', 3, '2025-11-02 20:16:17', '2025-11-02 20:16:17'),
(6, 'ika', '325077d1d7b6fa325b095fb212f3bc42', 'pelanggan', 4, '2025-11-02 20:16:17', '2025-11-02 20:16:17'),
(7, 'zulfa', '325077d1d7b6fa325b095fb212f3bc42', 'pelanggan', 5, '2025-11-02 20:16:17', '2025-11-02 20:16:17');

-- --------------------------------------------------------

--
-- Table structure for table `tb_log`
--

CREATE TABLE `tb_log` (
  `idlog` int(11) NOT NULL,
  `aksi` varchar(50) DEFAULT NULL,
  `tabel` varchar(50) DEFAULT NULL,
  `id_data` int(11) DEFAULT NULL,
  `iduser` int(11) DEFAULT NULL,
  `waktu` timestamp NOT NULL DEFAULT current_timestamp(),
  `keterangan` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tb_log`
--

INSERT INTO `tb_log` (`idlog`, `aksi`, `tabel`, `id_data`, `iduser`, `waktu`, `keterangan`) VALUES
(1, 'INSERT', 'kredit', 4, NULL, '2025-11-03 16:37:00', 'Kredit baru dibuat - Invoice: 4');

-- --------------------------------------------------------

--
-- Stand-in structure for view `view_laporan_kredit`
-- (See below for the actual view)
--
CREATE TABLE `view_laporan_kredit` (
`invoice` int(11)
,`tanggal` timestamp
,`nama_kreditor` varchar(50)
,`alamat` varchar(50)
,`nama_motor` varchar(30)
,`hrgtunai` int(11)
,`dp` int(11)
,`hrgkredit` int(11)
,`bunga` int(11)
,`lama` int(11)
,`totalkredit` int(11)
,`angsuran` int(11)
,`status` enum('pending','approved','rejected')
);

-- --------------------------------------------------------

--
-- Structure for view `view_laporan_kredit`
--
DROP TABLE IF EXISTS `view_laporan_kredit`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `view_laporan_kredit`  AS SELECT `k`.`invoice` AS `invoice`, `k`.`tanggal` AS `tanggal`, `kr`.`nama` AS `nama_kreditor`, `kr`.`alamat` AS `alamat`, `m`.`nama` AS `nama_motor`, `k`.`hrgtunai` AS `hrgtunai`, `k`.`dp` AS `dp`, `k`.`hrgkredit` AS `hrgkredit`, `k`.`bunga` AS `bunga`, `k`.`lama` AS `lama`, `k`.`totalkredit` AS `totalkredit`, `k`.`angsuran` AS `angsuran`, `k`.`status` AS `status` FROM ((`kredit` `k` join `kreditor` `kr` on(`k`.`idkreditor` = `kr`.`idkreditor`)) join `motor` `m` on(`k`.`kdmotor` = `m`.`kdmotor`)) ;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `angsuran`
--
ALTER TABLE `angsuran`
  ADD PRIMARY KEY (`id_angsuran`);

--
-- Indexes for table `kredit`
--
ALTER TABLE `kredit`
  ADD PRIMARY KEY (`invoice`);

--
-- Indexes for table `kreditor`
--
ALTER TABLE `kreditor`
  ADD PRIMARY KEY (`idkreditor`);

--
-- Indexes for table `motor`
--
ALTER TABLE `motor`
  ADD PRIMARY KEY (`idmotor`);

--
-- Indexes for table `petugas`
--
ALTER TABLE `petugas`
  ADD PRIMARY KEY (`idpetugas`);

--
-- Indexes for table `tbpetugas`
--
ALTER TABLE `tbpetugas`
  ADD PRIMARY KEY (`idpetugas`);

--
-- Indexes for table `tbuser`
--
ALTER TABLE `tbuser`
  ADD PRIMARY KEY (`iduser`),
  ADD UNIQUE KEY `username` (`username`),
  ADD KEY `idkreditor` (`idkreditor`);

--
-- Indexes for table `tb_log`
--
ALTER TABLE `tb_log`
  ADD PRIMARY KEY (`idlog`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `angsuran`
--
ALTER TABLE `angsuran`
  MODIFY `id_angsuran` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `kredit`
--
ALTER TABLE `kredit`
  MODIFY `invoice` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `kreditor`
--
ALTER TABLE `kreditor`
  MODIFY `idkreditor` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `motor`
--
ALTER TABLE `motor`
  MODIFY `idmotor` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `petugas`
--
ALTER TABLE `petugas`
  MODIFY `idpetugas` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `tbpetugas`
--
ALTER TABLE `tbpetugas`
  MODIFY `idpetugas` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `tbuser`
--
ALTER TABLE `tbuser`
  MODIFY `iduser` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `tb_log`
--
ALTER TABLE `tb_log`
  MODIFY `idlog` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `tbuser`
--
ALTER TABLE `tbuser`
  ADD CONSTRAINT `tbuser_ibfk_1` FOREIGN KEY (`idkreditor`) REFERENCES `kreditor` (`idkreditor`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
