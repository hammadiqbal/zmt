-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Aug 04, 2025 at 07:32 PM
-- Server version: 10.11.10-MariaDB
-- PHP Version: 7.2.34

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `u640596426_pilot`
--

-- --------------------------------------------------------

--
-- Table structure for table `service_location_scheduling`
--

CREATE TABLE `service_location_scheduling` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `org_id` int(11) NOT NULL,
  `site_id` int(11) NOT NULL,
  `service_location_id` int(11) NOT NULL,
  `start_timestamp` int(11) NOT NULL,
  `end_timestamp` int(11) NOT NULL,
  `schedule_pattern` text DEFAULT NULL,
  `total_patient_limit` int(11) DEFAULT NULL,
  `new_patient_limit` int(11) DEFAULT NULL,
  `followup_patient_limit` int(11) DEFAULT NULL,
  `routine_patient_limit` int(11) DEFAULT NULL,
  `urgent_patient_limit` int(11) DEFAULT NULL,
  `emp_id` int(11) DEFAULT NULL,
  `status` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `logid` text DEFAULT NULL,
  `effective_timestamp` int(11) NOT NULL,
  `timestamp` int(11) NOT NULL,
  `last_updated` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `service_location_scheduling`
--

INSERT INTO `service_location_scheduling` (`id`, `name`, `org_id`, `site_id`, `service_location_id`, `start_timestamp`, `end_timestamp`, `schedule_pattern`, `total_patient_limit`, `new_patient_limit`, `followup_patient_limit`, `routine_patient_limit`, `urgent_patient_limit`, `emp_id`, `status`, `user_id`, `logid`, `effective_timestamp`, `timestamp`, `last_updated`) VALUES
(1, 'General OPD', 4, 3, 14, 1754366400, 1754395200, 'monday to saturday', NULL, NULL, NULL, NULL, NULL, 62, 1, 1, '3150,3151,3153,3275,3286', 0, 1754126377, 1754308899),
(2, 'General OPD', 4, 3, 13, 1754366400, 1754384400, 'monday to saturday', NULL, NULL, NULL, NULL, NULL, 60, 1, 14, '3276', 1754306760, 1754307125, 1754307125),
(3, 'General OPD', 4, 3, 13, 1754380800, 1754395200, 'monday to saturday', NULL, NULL, NULL, NULL, NULL, 63, 1, 14, '3277', 1754306760, 1754307178, 1754307178),
(4, 'Day Care', 4, 3, 2, 1754366400, 1754395200, 'monday to saturday', NULL, NULL, NULL, NULL, NULL, NULL, 1, 14, '3278', 1754306760, 1754307241, 1754307241),
(5, 'Vaccination Room', 4, 3, 3, 1754366400, 1754395200, 'monday to saturday', NULL, NULL, NULL, NULL, NULL, NULL, 1, 14, '3279', 1754307300, 1754307909, 1754307909),
(6, 'Procedure Room', 4, 3, 4, 1754366400, 1754395200, 'monday to saturday', NULL, NULL, NULL, NULL, NULL, NULL, 1, 14, '3280', 1754307300, 1754307981, 1754307981),
(7, 'Lab Sample Collection Room', 4, 3, 6, 1754366400, 1754395200, 'monday to saturday', NULL, NULL, NULL, NULL, NULL, NULL, 1, 14, '3281', 1754307300, 1754308041, 1754308041),
(8, 'Ultrasound Room', 4, 3, 8, 1754546400, 1754737200, 'weekly', NULL, NULL, NULL, NULL, NULL, NULL, 1, 14, '3282', 1754307300, 1754308304, 1754308304),
(9, 'Pharmacy', 4, 3, 9, 1754539200, 1754740800, 'monday to saturday', NULL, NULL, NULL, NULL, NULL, NULL, 1, 14, '3283', 1754307300, 1754308350, 1754308350),
(10, 'Eye Clinic', 4, 3, 15, 1754370000, 1754391600, 'monday to saturday', NULL, NULL, NULL, NULL, NULL, NULL, 1, 14, '3284', 1754308680, 1754308808, 1754308808),
(11, 'General OPD', 4, 16, 13, 1754366400, 1754395200, 'monday to saturday', NULL, NULL, NULL, NULL, NULL, 77, 1, 14, '3285', 1754308680, 1754308862, 1754308862),
(12, 'General OPD', 4, 16, 14, 1754366400, 1754395200, 'monday to saturday', NULL, NULL, NULL, NULL, NULL, 80, 1, 14, '3287', 1754308680, 1754308931, 1754308931),
(13, 'General OPD', 4, 18, 13, 1754366400, 1754395200, 'monday to saturday', NULL, NULL, NULL, NULL, NULL, 89, 1, 14, '3288', 1754308680, 1754309186, 1754309186),
(14, 'Pharmacy', 4, 16, 9, 1754366400, 1754395200, 'monday to saturday', NULL, NULL, NULL, NULL, NULL, NULL, 1, 14, '3289', 1754308680, 1754309294, 1754309294),
(15, 'Day Care', 4, 16, 2, 1754366400, 1754395200, 'monday to saturday', NULL, NULL, NULL, NULL, NULL, NULL, 1, 14, '3290', 1754308680, 1754309345, 1754309345),
(16, 'Lab Sample Collection Room', 4, 16, 6, 1754366400, 1754395200, 'monday to saturday', NULL, NULL, NULL, NULL, NULL, NULL, 1, 14, '3291', 1754308680, 1754309379, 1754309379),
(17, 'Vaccination Room', 4, 16, 3, 1754366400, 1754395200, 'monday to saturday', NULL, NULL, NULL, NULL, NULL, NULL, 1, 14, '3292', 1754308680, 1754309448, 1754309448),
(18, 'Ultasound Room', 4, 16, 8, 1753855200, 1754474400, 'weekly', NULL, NULL, NULL, NULL, NULL, NULL, 1, 14, '3293', 1754308680, 1754309515, 1754309515),
(19, 'Administration Room', 4, 16, 11, 1753848000, 1754481600, 'monday to saturday', NULL, NULL, NULL, NULL, NULL, NULL, 1, 14, '3294', 1754308680, 1754309554, 1754309554),
(20, 'Vaccination Room', 4, 18, 3, 1753848000, 1754481600, 'monday to saturday', NULL, NULL, NULL, NULL, NULL, NULL, 1, 14, '3295', 1754308680, 1754309618, 1754309618),
(21, 'Ultrasound Room', 4, 18, 8, 1754287200, 1754906400, 'weekly', NULL, NULL, NULL, NULL, NULL, NULL, 1, 14, '3296', 1754308680, 1754309662, 1754309662);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `service_location_scheduling`
--
ALTER TABLE `service_location_scheduling`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `service_location_scheduling`
--
ALTER TABLE `service_location_scheduling`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
