-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 25, 2026 at 10:11 AM
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
-- Table structure for table `seller_applications`
--

CREATE TABLE `seller_applications` (
  `application_id` int(11) NOT NULL,
  `owner_name` varchar(255) NOT NULL,
  `username` varchar(100) DEFAULT NULL,
  `emailID` int(11) NOT NULL,
  `password` varchar(255) NOT NULL,
  `contact_number` varchar(20) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `document_path` varchar(255) NOT NULL,
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `seller_applications`
--

INSERT INTO `seller_applications` (`application_id`, `owner_name`, `username`, `emailID`, `password`, `contact_number`, `address`, `document_path`, `status`, `created_at`) VALUES
(1, 'Patricia Cruz', NULL, 4, '$2y$10$2ka.4lPlUmdpJDlwI4W2h.i4FlnESyq3J9y3vjXbeKNCAcAkFfntW', NULL, NULL, 'uploads/seller_docs/doc_69eb9e3a1e35d.png', 'approved', '2026-04-24 16:45:46'),
(2, 'Sher Bongolto', 'norman', 6, '$2y$10$juO2eWkznY2.xlYJ3wTE5.oAXbbN4EwCeQTmbn6T6LStSI2uzQkh2', '09614662426', 'bambang pasig city', 'uploads/seller_docs/doc_69ebc92bb1f9c.pdf', 'pending', '2026-04-24 19:48:59');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `seller_applications`
--
ALTER TABLE `seller_applications`
  ADD PRIMARY KEY (`application_id`),
  ADD KEY `emailID` (`emailID`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `seller_applications`
--
ALTER TABLE `seller_applications`
  MODIFY `application_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `seller_applications`
--
ALTER TABLE `seller_applications`
  ADD CONSTRAINT `seller_applications_ibfk_1` FOREIGN KEY (`emailID`) REFERENCES `email` (`emailID`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
