-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 05, 2025 at 11:38 AM
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
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `purpose` text DEFAULT NULL,
  `approved_by` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `reservations`
--

INSERT INTO `reservations` (`reservation_id`, `user_id`, `resource_id`, `event_name`, `start_time`, `end_time`, `status`, `purpose`, `approved_by`) VALUES
(1, 1, 1, 'CAMP YESU', '2024-11-20 09:00:00', '2024-11-20 17:00:00', 'approved', 'Annual student retreat', NULL),
(2, 2, 2, 'CHRISTMAS PARTY', '2024-12-13 18:00:00', '2024-12-13 22:00:00', 'pending', 'Department celebration', NULL),
(3, 3, 3, 'THE FIST', '2024-12-20 10:00:00', '2024-12-20 12:00:00', 'pending', 'Programming competition', NULL),
(4, 5, 1, 'Graduation 2025', '2025-05-22 10:00:00', '2025-05-22 15:30:00', 'approved', 'graduation', 5),
(5, 5, 1, 'akjhgsdfkljshfakj', '2025-05-07 06:47:00', '2025-05-07 19:44:00', 'approved', 'asdfalskdufhalskuehf', 5);

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
(3, 'Sapientia Hall', 'classroom', 'Academic Building', 100, 0);

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
(7, 'James', 'Michael', 'Solamo', NULL, 'SolamoJames123@gmail.com', 'jmsolamo', '$2y$10$wtBCkoE2g2yQBRyF6fdI.uPsSGr3wvdUwvV.Q0WCj0KPWMBNuy0h6', 'student', '2025-05-04 08:41:58');

--
-- Indexes for dumped tables
--

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
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `notification_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `reservations`
--
ALTER TABLE `reservations`
  MODIFY `reservation_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `resources`
--
ALTER TABLE `resources`
  MODIFY `resource_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

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
