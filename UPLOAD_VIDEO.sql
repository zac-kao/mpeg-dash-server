-- phpMyAdmin SQL Dump
-- version 4.5.4.1deb2ubuntu2
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Oct 04, 2017 at 03:20 PM
-- Server version: 5.7.19-0ubuntu0.16.04.1
-- PHP Version: 7.0.22-0ubuntu0.16.04.1

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `team07`
--

-- --------------------------------------------------------

--
-- Table structure for table `UPLOAD_VIDEO`
--

CREATE TABLE `UPLOAD_VIDEO` (
  `IDX` int(32) NOT NULL,
  `UPLOAD_DEVICE_ID` int(11) NOT NULL,
  `VIDEO_ID` int(32) NOT NULL,
  `TOTAL_NUMBER_OF_STREAMLETS` int(11) NOT NULL,
  `LAST_UPLOADED_STREAMLET` int(11) NOT NULL DEFAULT '0',
  `LAST_UPLOAD_TIME` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `LAST_TRANSCODED_STREAMLET_240P` int(11) NOT NULL DEFAULT '0',
  `LAST_TRANSCODED_STREAMLET_360P` int(11) NOT NULL DEFAULT '0',
  `LAST_TRANSCODED_STREAMLET_480P` int(11) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `UPLOAD_VIDEO`
--

INSERT INTO `UPLOAD_VIDEO` (`IDX`, `UPLOAD_DEVICE_ID`, `VIDEO_ID`, `TOTAL_NUMBER_OF_STREAMLETS`, `LAST_UPLOADED_STREAMLET`, `LAST_UPLOAD_TIME`, `LAST_TRANSCODED_STREAMLET_240P`, `LAST_TRANSCODED_STREAMLET_360P`, `LAST_TRANSCODED_STREAMLET_480P`) VALUES
(1, 12345, 1, 1, 1, '2017-09-26 09:09:05', 0, 0, 0);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `UPLOAD_VIDEO`
--
ALTER TABLE `UPLOAD_VIDEO`
  ADD PRIMARY KEY (`IDX`),
  ADD UNIQUE KEY `VIDEO_ID` (`VIDEO_ID`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `UPLOAD_VIDEO`
--
ALTER TABLE `UPLOAD_VIDEO`
  MODIFY `IDX` int(32) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
