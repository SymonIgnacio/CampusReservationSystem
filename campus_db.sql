-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 29, 2025 at 02:44 PM
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
(1, 'CHAIRS', 100, NULL, '2025-05-29 10:29:14', '2025-05-29 10:29:14'),
(2, 'TABLES', 30, NULL, '2025-05-29 10:29:14', '2025-05-29 10:29:14'),
(3, 'MICROPHONES', 5, NULL, '2025-05-29 10:29:14', '2025-05-29 10:29:14'),
(4, 'WHITE BOARD', 15, NULL, '2025-05-29 10:29:14', '2025-05-29 10:29:14'),
(5, 'LED PROJECTOR', 8, NULL, '2025-05-29 10:29:14', '2025-05-29 10:29:14'),
(6, 'ELECTRIC FAN', 20, NULL, '2025-05-29 10:29:14', '2025-05-29 10:29:14'),
(7, 'WATER DISPENSER', 3, NULL, '2025-05-29 10:29:14', '2025-05-29 10:29:14'),
(8, 'LED MONITOR', 12, NULL, '2025-05-29 10:29:14', '2025-05-29 10:29:14');

-- --------------------------------------------------------

--
-- Table structure for table `equipments`
--

CREATE TABLE `equipments` (
  `equipment_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `stock` int(11) DEFAULT 0,
  `description` text DEFAULT NULL,
  `location` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `equipments`
--

INSERT INTO `equipments` (`equipment_id`, `name`, `stock`, `description`, `location`) VALUES
(1, 'Projector', 10, 'Standard HD projector', 'Main Campus'),
(2, 'Microphone', 20, 'Wireless microphone', 'Main Campus'),
(3, 'Laptop', 15, 'Dell Latitude', 'East Campus'),
(4, 'Speaker System', 5, 'High-quality audio system', 'West Campus'),
(5, 'Extension Cord', 30, '10-meter extension cord', 'Main Campus');

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `notification_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `reservation_id` int(11) NOT NULL,
  `type` enum('confirmation','reminder','approval','cancellation') DEFAULT NULL,
  `message` text DEFAULT NULL,
  `sent_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`notification_id`, `user_id`, `reservation_id`, `type`, `message`, `sent_at`) VALUES
(1, 1, 1, 'confirmation', 'Your booking for CAMP YESU is approved.', '2025-05-03 18:28:43'),
(2, 2, 2, 'reminder', 'Reminder: CHRISTMAS PARTY starts in 1 hour.', '2025-05-03 18:28:43');

-- --------------------------------------------------------

--
-- Table structure for table `reservations`
--

CREATE TABLE `reservations` (
  `reservation_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `resource_id` int(11) NOT NULL,
  `event_name` varchar(255) NOT NULL,
  `start_time` datetime NOT NULL,
  `end_time` datetime NOT NULL,
  `status` enum('pending','approved','declined') DEFAULT 'pending',
  `purpose` text DEFAULT NULL,
  `approved_by` int(11) DEFAULT NULL,
  `department` varchar(100) DEFAULT NULL,
  `activity` varchar(255) DEFAULT NULL,
  `nature_of_activity` enum('CURRICULAR','CO-CURRICULAR','OTHERS') DEFAULT NULL,
  `participants` text DEFAULT NULL,
  `male_participants` int(11) DEFAULT 0,
  `female_participants` int(11) DEFAULT 0,
  `total_participants` int(11) GENERATED ALWAYS AS (`male_participants` + `female_participants`) STORED,
  `equipment_needed` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `reservations`
--

INSERT INTO `reservations` (`reservation_id`, `user_id`, `resource_id`, `event_name`, `start_time`, `end_time`, `status`, `purpose`, `approved_by`, `department`, `activity`, `nature_of_activity`, `participants`, `male_participants`, `female_participants`, `equipment_needed`) VALUES
(1, 1, 1, 'CAMP YESU', '2024-11-20 09:00:00', '2024-11-20 17:00:00', '', 'Annual student retreat', NULL, NULL, NULL, NULL, NULL, 0, 0, NULL),
(2, 2, 2, 'CHRISTMAS PARTY', '2024-12-13 18:00:00', '2024-12-13 22:00:00', '', 'Department celebration', NULL, NULL, NULL, NULL, NULL, 0, 0, NULL),
(3, 3, 3, 'THE FIST', '2024-12-20 10:00:00', '2024-12-20 12:00:00', '', 'Programming competition', NULL, NULL, NULL, NULL, NULL, 0, 0, NULL),
(4, 5, 1, 'Graduation 2025', '2025-05-22 10:00:00', '2025-05-22 15:30:00', 'approved', 'graduation', 5, NULL, NULL, NULL, NULL, 0, 0, NULL),
(5, 5, 1, 'akjhgsdfkljshfakj', '2025-05-07 06:47:00', '2025-05-07 19:44:00', 'approved', 'asdfalskdufhalskuehf', 5, NULL, NULL, NULL, NULL, 0, 0, NULL),
(29, 8, 4, 'Moving up', '2025-05-07 07:10:00', '2025-05-07 15:00:00', 'approved', 'For Grade 10 Students', NULL, 'College of Computer Studies', 'Moving up', 'CURRICULAR', 'Grade 10 Students', 200, 100, '[{\"id\":1,\"quantity\":1},{\"id\":5,\"quantity\":1}]'),
(30, 8, 5, 'Moving Up', '2025-05-07 07:15:00', '2025-05-07 15:30:00', '', 'For Grade 10 Students', NULL, 'College of Computer Studies', 'Moving Up', 'CURRICULAR', 'Grade 10 Students', 200, 100, '[{\"id\":1,\"quantity\":1},{\"id\":6,\"quantity\":310},{\"id\":7,\"quantity\":4},{\"id\":3,\"quantity\":1}]'),
(31, 8, 5, 'Moving up ', '2025-05-07 03:32:00', '2025-05-07 07:36:00', '', 'for grade 10 ', NULL, 'College of Computer Studies', 'Moving up ', 'CURRICULAR', 'Grade 10 Students', 100, 100, '[{\"id\":2,\"quantity\":1}]'),
(32, 5, 6, 'Test Activity', '2025-05-06 09:00:00', '2025-05-06 10:00:00', '', 'Test Purpose', NULL, 'Test Department', 'Test Activity', 'CURRICULAR', NULL, 0, 0, NULL),
(33, 11, 1, 'Pending Test Event', '2025-05-19 23:38:38', '2025-05-20 01:38:38', 'approved', 'Testing pending status', NULL, NULL, NULL, NULL, NULL, 0, 0, NULL),
(34, 11, 1, 'Declined Test Event', '2025-05-20 23:38:38', '2025-05-21 02:38:38', 'declined', 'Testing declined status', NULL, NULL, NULL, NULL, NULL, 0, 0, NULL),
(35, 8, 7, 'Meeting', '2025-05-29 07:30:00', '2025-05-29 09:30:00', 'pending', 'SSC meeting', NULL, NULL, NULL, NULL, NULL, 0, 0, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `resources`
--

CREATE TABLE `resources` (
  `resource_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `type` enum('classroom','event_hall','lab','equipment') NOT NULL,
  `location` varchar(100) DEFAULT NULL,
  `capacity` int(11) DEFAULT NULL,
  `requires_approval` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `resources`
--

INSERT INTO `resources` (`resource_id`, `name`, `type`, `location`, `capacity`, `requires_approval`) VALUES
(1, 'Marciano Covered Court', 'event_hall', 'Sports Complex', 500, 1),
(2, 'Elida Covered Court', 'event_hall', 'Campus East', 300, 1),
(3, 'Sapientia Hall', 'classroom', 'Academic Building', 100, 0),
(4, '4', 'event_hall', 'Unknown location', 100, 1),
(5, 'Main Auditorium', 'event_hall', 'Unknown location', 100, 1),
(6, 'Test Venue', 'event_hall', 'Unknown location', 100, 1),
(7, 'Conference Room A', 'event_hall', 'Campus', 100, 1);

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
-- Indexes for table `equipment`
--
ALTER TABLE `equipment`
  ADD PRIMARY KEY (`equipment_id`);

--
-- Indexes for table `equipments`
--
ALTER TABLE `equipments`
  ADD PRIMARY KEY (`equipment_id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`notification_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `reservation_id` (`reservation_id`);

--
-- Indexes for table `reservations`
--
ALTER TABLE `reservations`
  ADD PRIMARY KEY (`reservation_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `resource_id` (`resource_id`);

--
-- Indexes for table `resources`
--
ALTER TABLE `resources`
  ADD PRIMARY KEY (`resource_id`);

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
-- AUTO_INCREMENT for table `equipment`
--
ALTER TABLE `equipment`
  MODIFY `equipment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `equipments`
--
ALTER TABLE `equipments`
  MODIFY `equipment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `notification_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `reservations`
--
ALTER TABLE `reservations`
  MODIFY `reservation_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=36;

--
-- AUTO_INCREMENT for table `resources`
--
ALTER TABLE `resources`
  MODIFY `resource_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`),
  ADD CONSTRAINT `notifications_ibfk_2` FOREIGN KEY (`reservation_id`) REFERENCES `reservations` (`reservation_id`);

--
-- Constraints for table `reservations`
--
ALTER TABLE `reservations`
  ADD CONSTRAINT `reservations_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`),
  ADD CONSTRAINT `reservations_ibfk_2` FOREIGN KEY (`resource_id`) REFERENCES `resources` (`resource_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
