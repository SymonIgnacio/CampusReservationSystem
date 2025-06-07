-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 06, 2025 at 09:58 PM
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
-- Database: `campus_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `approved_request`
--

CREATE TABLE `approved_request` (
  `id` int(11) NOT NULL,
  `reference_number` varchar(50) DEFAULT NULL,
  `date_created` datetime DEFAULT current_timestamp(),
  `request_by` varchar(100) NOT NULL,
  `department_organization` varchar(100) NOT NULL,
  `activity` varchar(255) NOT NULL,
  `purpose` text NOT NULL,
  `nature_of_activity` enum('curricular','co-curricular') NOT NULL,
  `date_need_from` date NOT NULL,
  `date_need_until` date NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `participants` text DEFAULT NULL,
  `total_male_attendees` int(11) DEFAULT 0,
  `total_female_attendees` int(11) DEFAULT 0,
  `total_attendees` int(11) GENERATED ALWAYS AS (`total_male_attendees` + `total_female_attendees`) STORED,
  `venue` varchar(100) NOT NULL,
  `equipments_needed` text DEFAULT NULL,
  `status` enum('pending','approved','declined') DEFAULT 'pending',
  `approved_by` varchar(255) NOT NULL,
  `approved_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `approved_request`
--

INSERT INTO `approved_request` (`id`, `reference_number`, `date_created`, `request_by`, `department_organization`, `activity`, `purpose`, `nature_of_activity`, `date_need_from`, `date_need_until`, `start_time`, `end_time`, `participants`, `total_male_attendees`, `total_female_attendees`, `venue`, `equipments_needed`, `status`, `approved_by`, `approved_at`) VALUES
(1, 'REQ-228747', '2025-06-06 01:55:50', 'Symon Ignacio', 'College of Computer Studies', 'suntukan', 'makatulog', 'curricular', '2025-06-21', '2025-06-27', '00:00:00', '00:00:00', 'maganda', 54, 545, 'Main Hall', 'ELECTRIC FAN (1), WATER DISPENSER (1)', 'approved', 'Admin', '2025-06-05 09:56:54'),
(2, 'REQ-228747-20250622', '2025-06-06 01:55:50', 'Symon Ignacio', 'College of Computer Studies', 'suntukan', 'makatulog', 'curricular', '2025-06-22', '2025-06-22', '01:55:00', '13:55:00', 'maganda', 54, 545, 'Conference Room B', 'ELECTRIC FAN (1), WATER DISPENSER (1)', 'approved', 'Admin', '2025-06-05 09:56:54'),
(3, 'REQ-228747-20250623', '2025-06-06 01:55:50', 'Symon Ignacio', 'College of Computer Studies', 'suntukan', 'makatulog', 'curricular', '2025-06-23', '2025-06-23', '01:55:00', '13:55:00', 'maganda', 54, 545, 'Conference Room B', 'ELECTRIC FAN (1), WATER DISPENSER (1)', 'approved', 'Admin', '2025-06-05 09:56:54'),
(4, 'REQ-228747-20250624', '2025-06-06 01:55:50', 'Symon Ignacio', 'College of Computer Studies', 'suntukan', 'makatulog', 'curricular', '2025-06-24', '2025-06-24', '01:55:00', '13:55:00', 'maganda', 54, 545, 'Conference Room B', 'ELECTRIC FAN (1), WATER DISPENSER (1)', 'approved', 'Admin', '2025-06-05 09:56:54'),
(5, 'REQ-228747-20250625', '2025-06-06 01:55:50', 'Symon Ignacio', 'College of Computer Studies', 'suntukan', 'makatulog', 'curricular', '2025-06-25', '2025-06-25', '01:55:00', '13:55:00', 'maganda', 54, 545, 'Conference Room B', 'ELECTRIC FAN (1), WATER DISPENSER (1)', 'approved', 'Admin', '2025-06-05 09:56:54'),
(6, 'REQ-228747-20250626', '2025-06-06 01:55:50', 'Symon Ignacio', 'College of Computer Studies', 'suntukan', 'makatulog', 'curricular', '2025-06-26', '2025-06-26', '01:55:00', '13:55:00', 'maganda', 54, 545, 'Conference Room B', 'ELECTRIC FAN (1), WATER DISPENSER (1)', 'approved', 'Admin', '2025-06-05 09:56:54'),
(7, 'REQ-228747-20250627', '2025-06-06 01:55:50', 'Symon Ignacio', 'College of Computer Studies', 'suntukan', 'makatulog', 'curricular', '2025-06-27', '2025-06-27', '03:04:00', '15:04:00', 'maganda', 54, 545, 'Sports Field', 'ELECTRIC FAN (1), WATER DISPENSER (1)', 'approved', 'Admin', '2025-06-05 09:56:54');

-- --------------------------------------------------------

--
-- Table structure for table `declined_request`
--

CREATE TABLE `declined_request` (
  `id` int(11) NOT NULL,
  `reference_number` varchar(50) DEFAULT NULL,
  `date_created` datetime DEFAULT current_timestamp(),
  `request_by` varchar(100) NOT NULL,
  `department_organization` varchar(100) NOT NULL,
  `activity` varchar(255) NOT NULL,
  `purpose` text NOT NULL,
  `nature_of_activity` enum('curricular','co-curricular') NOT NULL,
  `date_need_from` date NOT NULL,
  `date_need_until` date NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `participants` text DEFAULT NULL,
  `total_male_attendees` int(11) DEFAULT 0,
  `total_female_attendees` int(11) DEFAULT 0,
  `total_attendees` int(11) GENERATED ALWAYS AS (`total_male_attendees` + `total_female_attendees`) STORED,
  `venue` varchar(100) NOT NULL,
  `equipments_needed` text DEFAULT NULL,
  `status` enum('pending','approved','declined') DEFAULT 'pending',
  `reason` text NOT NULL,
  `rejected_by` varchar(255) NOT NULL,
  `rejected_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `declined_request`
--

INSERT INTO `declined_request` (`id`, `reference_number`, `date_created`, `request_by`, `department_organization`, `activity`, `purpose`, `nature_of_activity`, `date_need_from`, `date_need_until`, `start_time`, `end_time`, `participants`, `total_male_attendees`, `total_female_attendees`, `venue`, `equipments_needed`, `status`, `reason`, `rejected_by`, `rejected_at`) VALUES
(1, 'REQ-347973', '2025-06-06 01:55:12', 'Symon Ignacio', 'College of Computer Studies', 'Jumping rope', 'mabusog', 'curricular', '2025-06-06', '2025-06-14', '01:54:00', '13:54:00', 'maganda', 200, 202, 'Main Auditorium', 'MICROPHONES (1), TABLES (1)', 'declined', 'ayaw ko', 'Admin', '2025-06-05 09:57:23');

-- --------------------------------------------------------

--
-- Table structure for table `equipment`
--

CREATE TABLE `equipment` (
  `equipment_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `quantity_available` int(11) NOT NULL DEFAULT 0,
  `location` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `equipment`
--

INSERT INTO `equipment` (`equipment_id`, `name`, `quantity_available`, `location`, `created_at`, `updated_at`) VALUES
(1, 'CHAIRS', 100, NULL, '2025-05-29 02:29:14', '2025-05-29 02:29:14'),
(2, 'TABLES', 30, NULL, '2025-05-29 02:29:14', '2025-05-29 02:29:14'),
(3, 'MICROPHONES', 5, NULL, '2025-05-29 02:29:14', '2025-05-29 02:29:14'),
(4, 'WHITE BOARD', 15, NULL, '2025-05-29 02:29:14', '2025-05-29 02:29:14'),
(5, 'LED PROJECTOR', 8, NULL, '2025-05-29 02:29:14', '2025-05-29 02:29:14'),
(6, 'ELECTRIC FAN', 20, NULL, '2025-05-29 02:29:14', '2025-05-29 02:29:14'),
(7, 'WATER DISPENSER', 3, NULL, '2025-05-29 02:29:14', '2025-05-29 02:29:14'),
(8, 'LED MONITOR', 12, NULL, '2025-05-29 02:29:14', '2025-05-29 02:29:14');

-- --------------------------------------------------------

--
-- Table structure for table `equipments`
--

CREATE TABLE `equipments` (
  `equipment_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `stock` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `equipments`
--

INSERT INTO `equipments` (`equipment_id`, `name`, `stock`) VALUES
(1, 'Projector', 10),
(2, 'Microphone', 20),
(4, 'Speaker System', 5),
(6, 'Extension Cord', 100);

-- --------------------------------------------------------

--
-- Table structure for table `facilities`
--

CREATE TABLE `facilities` (
  `id` int(11) NOT NULL,
  `venue` varchar(255) NOT NULL,
  `campus` varchar(255) NOT NULL,
  `capacity` int(11) DEFAULT NULL,
  `description` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `facilities`
--

INSERT INTO `facilities` (`id`, `venue`, `campus`, `capacity`, `description`) VALUES
(1, 'Main Hall', 'Main Campus', 500, 'Large auditorium for events'),
(2, 'Conference Room A', 'Main Campus', 50, 'Medium-sized conference room'),
(3, 'Lecture Hall 101', 'East Campus', 200, 'Lecture hall with projector'),
(4, 'Sports Field', 'West Campus', 1000, 'Outdoor sports field'),
(5, 'Computer Lab', 'North Campus', 30, 'Computer lab with 30 workstations'),
(6, 'Inspired Lab', 'Main Campus', 10000, 'wlala');

-- --------------------------------------------------------

--
-- Table structure for table `request`
--

CREATE TABLE `request` (
  `id` int(11) NOT NULL,
  `reference_number` varchar(50) DEFAULT NULL,
  `date_created` datetime DEFAULT current_timestamp(),
  `request_by` varchar(100) NOT NULL,
  `department_organization` varchar(100) NOT NULL,
  `activity` varchar(255) NOT NULL,
  `purpose` text NOT NULL,
  `nature_of_activity` enum('curricular','co-curricular') NOT NULL,
  `date_need_from` date NOT NULL,
  `date_need_until` date NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `participants` text DEFAULT NULL,
  `total_male_attendees` int(11) DEFAULT 0,
  `total_female_attendees` int(11) DEFAULT 0,
  `total_attendees` int(11) GENERATED ALWAYS AS (`total_male_attendees` + `total_female_attendees`) STORED,
  `venue` varchar(100) NOT NULL,
  `equipments_needed` text DEFAULT NULL,
  `status` enum('pending','approved','declined') DEFAULT 'pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `request`
--

INSERT INTO `request` (`id`, `reference_number`, `date_created`, `request_by`, `department_organization`, `activity`, `purpose`, `nature_of_activity`, `date_need_from`, `date_need_until`, `start_time`, `end_time`, `participants`, `total_male_attendees`, `total_female_attendees`, `venue`, `equipments_needed`, `status`) VALUES
(3, 'REQ-700855', '2025-06-07 03:44:32', 'Symon Ignacio', 'College of Computer Studies', 'Jumping rope', 'nothing', 'curricular', '2025-06-25', '2025-06-25', '01:55:00', '13:55:00', 'asdasd', 454, 454, 'Conference Room A', 'CHAIRS (10)', 'pending');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `firstname` varchar(50) NOT NULL,
  `middlename` varchar(50) DEFAULT NULL,
  `lastname` varchar(50) NOT NULL,
  `department` varchar(100) DEFAULT NULL,
  `email` varchar(100) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('student','faculty','admin') DEFAULT 'student',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `firstname`, `middlename`, `lastname`, `department`, `email`, `username`, `password`, `role`, `created_at`) VALUES
(1, 'James', 'Michael', 'Solamo', NULL, 'james.solamo@uni.edu', 'j.solamo', 'hashed_pass123', 'student', '2025-05-03 18:27:54'),
(2, 'John', 'Robert', 'Gumban', NULL, 'john.gumban@uni.edu', 'j.gumban', 'hashed_pass456', 'student', '2025-05-03 18:27:54'),
(3, 'Genalin', 'Marie DG', 'Censon', NULL, 'genalin.censon@uni.edu', 'g.censon', 'hashed_pass789', 'faculty', '2025-05-03 18:27:54'),
(5, 'Symon', 'Balilla', 'Ignacio', NULL, 'Symonignacio1@gmail.com', 'Symon', '$2y$10$bbVFf1Muvi/91QtsvUOOZOfRUspkiWEgHS1HaDxUJcioBhs4kvVFq', 'admin', '2025-05-03 18:59:36'),
(7, 'James', 'Michael', 'Solamo', NULL, 'SolamoJames123@gmail.com', 'jmsolamo', '$2y$10$wtBCkoE2g2yQBRyF6fdI.uPsSGr3wvdUwvV.Q0WCj0KPWMBNuy0h6', 'student', '2025-05-04 08:41:58'),
(8, 'Symon', 'Balilla', 'Ignacio', 'College of Computer Studies', 'Ignaciosymon11@gmail.com', 'Symie', '$2y$10$PGeB3O7g3cHFF7kKFJLjRusCOgI8mUWte4dOCt05sXKIc/bLrRE36', 'faculty', '2025-05-05 18:50:22'),
(10, 'james', 'michael', 'solamo', 'College of Computer Studies', 'Jmsolamo@gmail.com', 'jjmsolamo', '$2y$10$3G/zPUzXhBd000CQ5Rhsrukkq/avDICbfJ1mCZNk7KV.7SgwGxkPq', 'student', '2025-05-08 14:21:33'),
(11, 'Admin', NULL, 'User', 'IT Department', 'admin@example.com', 'admin', '$2y$10$NRXPcRocILu6NcAZiHNGguPle3iIh2KrlALZBarKT9AQ3aieaLDV6', 'admin', '2025-05-18 21:03:22'),
(12, 'Jr', 'DC', 'Gumban', 'College of Computer Studies', 'jrguman@email.com', 'Gumban', '$2y$10$XsZiSZuVsPeFMMYWeDCNYupn9w1KacVKXaBH2LEkKSsmU5cNeTGa6', 'student', '2025-05-29 09:03:29'),
(13, 'Anne ', 'asdjakl', 'Fortez', 'College of Accountancy', 'AnneF@email.com', 'anne', '$2y$10$xIrBuCSJ.lJKNn5TNoia4ebmJ4Dxp785CtYlEFbthG.Ec3a/FphZy', 'faculty', '2025-05-29 11:20:22');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `equipments`
--
ALTER TABLE `equipments`
  ADD PRIMARY KEY (`equipment_id`);

--
-- Indexes for table `facilities`
--
ALTER TABLE `facilities`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `request`
--
ALTER TABLE `request`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_reference_number` (`reference_number`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `equipments`
--
ALTER TABLE `equipments`
  MODIFY `equipment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `facilities`
--
ALTER TABLE `facilities`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `request`
--
ALTER TABLE `request`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
