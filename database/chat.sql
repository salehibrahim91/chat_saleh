-- phpMyAdmin SQL Dump
-- version 4.5.1
-- http://www.phpmyadmin.net
--
-- Host: 127.0.0.1
-- Generation Time: Aug 05, 2022 at 09:08 AM
-- Server version: 10.1.9-MariaDB
-- PHP Version: 5.6.15

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `chat`
--

-- --------------------------------------------------------

--
-- Table structure for table `chat`
--

CREATE TABLE `chat` (
  `id` int(11) NOT NULL,
  `sender` varchar(15) NOT NULL,
  `receiver` varchar(15) NOT NULL,
  `created_date` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `chat`
--

INSERT INTO `chat` (`id`, `sender`, `receiver`, `created_date`) VALUES
(1, '082298872845', '08121913683', '2022-08-05 10:41:50');

-- --------------------------------------------------------

--
-- Table structure for table `chat_detail`
--

CREATE TABLE `chat_detail` (
  `id` int(11) NOT NULL,
  `id_chat` int(11) NOT NULL,
  `message` text NOT NULL,
  `status` int(11) DEFAULT '0' COMMENT '0 = Unread, 1 = Read',
  `read_by` varchar(15) DEFAULT NULL,
  `created_by` varchar(15) NOT NULL,
  `created_date` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `chat_detail`
--

INSERT INTO `chat_detail` (`id`, `id_chat`, `message`, `status`, `read_by`, `created_by`, `created_date`) VALUES
(1, 1, 'Yud, lagi sibuk ngga?', 1, '08121913683', '082298872845', '2022-08-05 11:09:19'),
(2, 1, 'Iya, lumayan leh. Kenapa emang?', 1, '082298872845', '08121913683', '2022-08-05 13:35:47'),
(3, 1, 'Tolong cekin API Pendaftaran Member ya. Kayanya ada error dah', 1, '08121913683', '082298872845', '2022-08-05 13:46:31'),
(4, 1, 'Oh iya siap leh. Nanti abis ishoma gw cekin', 0, '082298872845', '08121913683', '2022-08-05 13:48:13');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `phone` varchar(15) NOT NULL,
  `name` varchar(50) NOT NULL,
  `bio` varchar(100) DEFAULT NULL,
  `photo` varchar(50) DEFAULT NULL,
  `status` int(11) DEFAULT '1' COMMENT '0 = Inactive, 1 = Active',
  `last_login` datetime DEFAULT NULL,
  `token` text,
  `created_by` varchar(32) NOT NULL,
  `created_date` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `phone`, `name`, `bio`, `photo`, `status`, `last_login`, `token`, `created_by`, `created_date`) VALUES
(1, '082298872845', 'Saleh Ibrahim', 'Backend Developer', NULL, 1, '2022-08-05 09:37:39', 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJwaG9uZSI6IjA4MjI5ODg3Mjg0NSIsIm5hbWUiOiJTYWxlaCBJYnJhaGltIn0.BZzhAIjBnOjnzLHWfV_GnlummCLJPITQ4SIEI4g6JIw', 'SYS', '2022-08-04 16:06:54'),
(2, '08121913683', 'Yudi Ripayansah', 'Frontend Developer', NULL, 1, '2022-08-05 11:26:09', 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJwaG9uZSI6IjA4MTIxOTEzNjgzIiwibmFtZSI6Ill1ZGkgUmlwYXlhbnNhaCJ9.QrEKbT6JigNnK7Eh9Cij9QBpe3b93Ynp7UaHIkSg1u0', 'SYS', '2022-08-04 16:07:33'),
(3, '081315662657', 'Mamang eno', 'Event Organizer', NULL, 1, NULL, NULL, 'SYS', '2022-08-05 09:47:08');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `chat`
--
ALTER TABLE `chat`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `chat_detail`
--
ALTER TABLE `chat_detail`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_id_chat_detail` (`id_chat`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `chat`
--
ALTER TABLE `chat`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;
--
-- AUTO_INCREMENT for table `chat_detail`
--
ALTER TABLE `chat_detail`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;
--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;
--
-- Constraints for dumped tables
--

--
-- Constraints for table `chat_detail`
--
ALTER TABLE `chat_detail`
  ADD CONSTRAINT `fk_id_chat_detail` FOREIGN KEY (`id_chat`) REFERENCES `chat` (`id`) ON UPDATE CASCADE;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
