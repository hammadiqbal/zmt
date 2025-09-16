-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Dec 26, 2024 at 12:47 PM
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
-- Database: `zmt_new`
--

-- --------------------------------------------------------

--
-- Table structure for table `donors`
--

CREATE TABLE `donors` (
  `id` int(11) NOT NULL,
  `org_id` int(11) NOT NULL,
  `corporate_name` varchar(255) DEFAULT NULL,
  `type` varchar(100) NOT NULL,
  `person_name` varchar(255) NOT NULL,
  `person_email` varchar(255) NOT NULL,
  `person_cell` varchar(255) NOT NULL,
  `person_landline` varchar(255) DEFAULT NULL,
  `address` text NOT NULL,
  `remarks` text NOT NULL,
  `status` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `logid` text DEFAULT NULL,
  `effective_timestamp` int(11) NOT NULL,
  `timestamp` int(11) NOT NULL,
  `last_updated` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `donors`
--

INSERT INTO `donors` (`id`, `org_id`, `corporate_name`, `type`, `person_name`, `person_email`, `person_cell`, `person_landline`, `address`, `remarks`, `status`, `user_id`, `logid`, `effective_timestamp`, `timestamp`, `last_updated`) VALUES
(1, 4, '', 'individual', 'Donor one name', 'donor1@gmail.co', '456456446', '', 'sdsdsd dssdsd', 'sdsdsd wssdsdsd', 1, 6, '1491', 1729536480, 1729536525, 1729536525);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `donors`
--
ALTER TABLE `donors`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `donors`
--
ALTER TABLE `donors`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
