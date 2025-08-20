-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Aug 20, 2025 at 11:14 AM
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
-- Database: `cricket_league`
--

-- --------------------------------------------------------

--
-- Table structure for table `matches`
--

CREATE TABLE `matches` (
  `match_id` int(11) NOT NULL,
  `team1_id` int(11) NOT NULL,
  `team2_id` int(11) NOT NULL,
  `match_date` datetime NOT NULL,
  `venue` varchar(100) NOT NULL,
  `result_summary` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `matches`
--

INSERT INTO `matches` (`match_id`, `team1_id`, `team2_id`, `match_date`, `venue`, `result_summary`) VALUES
(2, 6, 9, '2025-08-08 08:24:00', 'sdfsdfsfsdfd', 'cascc'),
(3, 6, 9, '2025-08-09 09:52:00', 'sdfsdfsfsdfd', 'cascc'),
(4, 8, 9, '2025-08-09 10:42:00', 'rwr', NULL),
(5, 8, 9, '2025-08-09 10:45:00', 'sdfsdfsfsdfd', 'gdfgg');

-- --------------------------------------------------------

--
-- Table structure for table `players`
--

CREATE TABLE `players` (
  `player_id` int(11) NOT NULL,
  `player_name` varchar(100) NOT NULL,
  `role` varchar(50) NOT NULL,
  `team_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `players`
--

INSERT INTO `players` (`player_id`, `player_name`, `role`, `team_id`) VALUES
(3, 'Virat Kohli', 'Batsman', 14),
(4, 'nabil', 'Bowler', 8);

-- --------------------------------------------------------

--
-- Table structure for table `player_scores`
--

CREATE TABLE `player_scores` (
  `score_id` int(11) NOT NULL,
  `match_id` int(11) NOT NULL,
  `player_id` int(11) NOT NULL,
  `runs_scored` int(11) DEFAULT 0,
  `wickets_taken` int(11) DEFAULT 0,
  `balls_faced` int(11) DEFAULT 0,
  `overs_bowled` decimal(4,1) DEFAULT 0.0,
  `runs_conceded` int(11) DEFAULT 0,
  `fours` int(11) DEFAULT 0,
  `sixes` int(11) DEFAULT 0,
  `maidens` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `player_scores`
--

INSERT INTO `player_scores` (`score_id`, `match_id`, `player_id`, `runs_scored`, `wickets_taken`, `balls_faced`, `overs_bowled`, `runs_conceded`, `fours`, `sixes`, `maidens`) VALUES
(5, 2, 3, 213, 32, 0, 0.0, 0, 0, 0, 0),
(6, 3, 3, 343, 4, 0, 0.0, 0, 0, 0, 0),
(7, 5, 4, 45, 534, 0, 0.0, 0, 0, 0, 0);

-- --------------------------------------------------------

--
-- Table structure for table `scorecard`
--

CREATE TABLE `scorecard` (
  `id` int(11) NOT NULL,
  `match_id` int(11) NOT NULL,
  `player_id` int(11) NOT NULL,
  `player_name` varchar(100) NOT NULL,
  `team_id` int(11) NOT NULL,
  `innings_type` enum('batting','bowling') NOT NULL,
  `runs` int(11) DEFAULT 0,
  `balls` int(11) DEFAULT 0,
  `fours` int(11) DEFAULT 0,
  `sixes` int(11) DEFAULT 0,
  `strike_rate` decimal(5,2) DEFAULT 0.00,
  `overs` decimal(4,1) DEFAULT 0.0,
  `maidens` int(11) DEFAULT 0,
  `runs_conceded` int(11) DEFAULT 0,
  `wickets` int(11) DEFAULT 0,
  `economy` decimal(4,2) DEFAULT 0.00,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `teams`
--

CREATE TABLE `teams` (
  `team_id` int(11) NOT NULL,
  `team_name` varchar(100) NOT NULL,
  `logo_url` varchar(255) DEFAULT NULL,
  `captain_id` int(11) DEFAULT NULL,
  `wk_id` int(11) DEFAULT NULL,
  `wicket_keeper_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `teams`
--

INSERT INTO `teams` (`team_id`, `team_name`, `logo_url`, `captain_id`, `wk_id`, `wicket_keeper_id`) VALUES
(6, 'asdrfae', NULL, NULL, NULL, NULL),
(7, 'fddf', NULL, NULL, NULL, NULL),
(8, 'efef', NULL, NULL, NULL, NULL),
(9, 'dfe', 'images/download (1).png', NULL, NULL, NULL),
(10, 'dfsdf', 'images/WBS.png', NULL, NULL, NULL),
(11, 'sfs', 'images/baf95319-b411-4b79-91a4-7d1be4489984.jpeg', NULL, NULL, NULL),
(12, 'sdsd', 'images/WBS.png', NULL, NULL, NULL),
(14, 'sdsdsd', 'images/logo_6896d099839797.99793971.webp', 3, NULL, 3);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','team_manager','viewer') NOT NULL DEFAULT 'viewer'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `username`, `email`, `password`, `role`) VALUES
(1, 'admin', '', '$2y$10$8rXuoehuEuaZi3uM50lHYujG.xCdP.GgxJx8TX7Lx7cS33tnlRTHK', 'viewer'),
(4, 'mnm', 'nabil.4.mahmud@gmail.com', '$2y$10$cqH9YOjUoop0j2HNsHARAePAS79ysbhP..kDVpXThYUSQH9Yhe.kC', 'team_manager'),
(9, 'admin1', 'nabil4.4@outlook.com', '$2y$10$kJSYFUGltqDiyroWjcA6e.1H8Y4GCvRORpsDie0H0Bbg5F6Fz9sD2', 'team_manager'),
(15, 'admin12', 'shimun@dffdsa', '$2y$10$fqK3TBFow1jKJm.lr0pCPe2pou.zIQQbk3gweun2dz1M8h529AUl6', 'team_manager');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `matches`
--
ALTER TABLE `matches`
  ADD PRIMARY KEY (`match_id`),
  ADD KEY `team1_id` (`team1_id`),
  ADD KEY `team2_id` (`team2_id`);

--
-- Indexes for table `players`
--
ALTER TABLE `players`
  ADD PRIMARY KEY (`player_id`),
  ADD KEY `team_id` (`team_id`);

--
-- Indexes for table `player_scores`
--
ALTER TABLE `player_scores`
  ADD PRIMARY KEY (`score_id`),
  ADD KEY `match_id` (`match_id`),
  ADD KEY `player_id` (`player_id`);

--
-- Indexes for table `scorecard`
--
ALTER TABLE `scorecard`
  ADD PRIMARY KEY (`id`),
  ADD KEY `match_id` (`match_id`),
  ADD KEY `team_id` (`team_id`),
  ADD KEY `player_id` (`player_id`);

--
-- Indexes for table `teams`
--
ALTER TABLE `teams`
  ADD PRIMARY KEY (`team_id`),
  ADD KEY `wk_id` (`wk_id`),
  ADD KEY `captain_id` (`captain_id`),
  ADD KEY `wicket_keeper_id` (`wicket_keeper_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `matches`
--
ALTER TABLE `matches`
  MODIFY `match_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `players`
--
ALTER TABLE `players`
  MODIFY `player_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `player_scores`
--
ALTER TABLE `player_scores`
  MODIFY `score_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `scorecard`
--
ALTER TABLE `scorecard`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `teams`
--
ALTER TABLE `teams`
  MODIFY `team_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `matches`
--
ALTER TABLE `matches`
  ADD CONSTRAINT `matches_ibfk_1` FOREIGN KEY (`team1_id`) REFERENCES `teams` (`team_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `matches_ibfk_2` FOREIGN KEY (`team2_id`) REFERENCES `teams` (`team_id`) ON DELETE CASCADE;

--
-- Constraints for table `players`
--
ALTER TABLE `players`
  ADD CONSTRAINT `players_ibfk_1` FOREIGN KEY (`team_id`) REFERENCES `teams` (`team_id`) ON DELETE CASCADE;

--
-- Constraints for table `player_scores`
--
ALTER TABLE `player_scores`
  ADD CONSTRAINT `scores_ibfk_1` FOREIGN KEY (`match_id`) REFERENCES `matches` (`match_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `scores_ibfk_2` FOREIGN KEY (`player_id`) REFERENCES `players` (`player_id`) ON DELETE CASCADE;

--
-- Constraints for table `scorecard`
--
ALTER TABLE `scorecard`
  ADD CONSTRAINT `scorecard_ibfk_1` FOREIGN KEY (`match_id`) REFERENCES `matches` (`match_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `scorecard_ibfk_2` FOREIGN KEY (`team_id`) REFERENCES `teams` (`team_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `scorecard_ibfk_3` FOREIGN KEY (`player_id`) REFERENCES `players` (`player_id`) ON DELETE CASCADE;

--
-- Constraints for table `teams`
--
ALTER TABLE `teams`
  ADD CONSTRAINT `teams_ibfk_1` FOREIGN KEY (`captain_id`) REFERENCES `players` (`player_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `teams_ibfk_2` FOREIGN KEY (`wk_id`) REFERENCES `players` (`player_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `teams_ibfk_3` FOREIGN KEY (`captain_id`) REFERENCES `players` (`player_id`),
  ADD CONSTRAINT `teams_ibfk_4` FOREIGN KEY (`wicket_keeper_id`) REFERENCES `players` (`player_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
