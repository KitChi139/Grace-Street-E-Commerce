-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 25, 2026 at 10:13 AM
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
-- Database: `grace_street1`
--

-- --------------------------------------------------------

--
-- Table structure for table `locked_accounts`
--

CREATE TABLE `locked_accounts` (
  `id` int(11) NOT NULL,
  `username` varchar(255) NOT NULL,
  `tries` int(11) NOT NULL,
  `locked_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `locked_accounts`
--

INSERT INTO `locked_accounts` (`id`, `username`, `tries`, `locked_at`) VALUES
(1, 'Didia', 0, '2026-04-25 03:15:58'),
(2, 'Didia', 4, '2026-04-25 03:16:34'),
(3, 'Patricia Cruz', 0, '2026-04-25 03:17:32');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `locked_accounts`
--
ALTER TABLE `locked_accounts`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `locked_accounts`
--
ALTER TABLE `locked_accounts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
