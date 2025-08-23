-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Aug 22, 2025 at 10:30 AM
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
-- Database: `studentrecord`
--

-- --------------------------------------------------------

--
-- Table structure for table `user`
--

CREATE TABLE `user` (
  `uid` int(11) NOT NULL,
  `uname` varchar(100) NOT NULL,
  `pass` longtext NOT NULL,
  `email` varchar(50) NOT NULL,
  `address` varchar(150) NOT NULL,
  `city` varchar(50) NOT NULL,
  `state` varchar(20) NOT NULL,
  `contact` varchar(11) NOT NULL,
  `proPic` longtext DEFAULT NULL,
  `status` int(1) NOT NULL DEFAULT 1 COMMENT '1 - active\r\n0 - deleted',
  `level` int(1) NOT NULL DEFAULT 0 COMMENT '0 - member\r\n1 - admin',
  `superadmin` int(1) NOT NULL DEFAULT 0 COMMENT '0 - normal admin\r\n1 - superadmin',
  `remark` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user`
--

INSERT INTO `user` (`uid`, `uname`, `pass`, `email`, `address`, `city`, `state`, `contact`, `proPic`, `status`, `level`, `superadmin`, `remark`) VALUES
(1, 'Teoh Kai Xin', 'e6a0a4ddc6257dad5a6160cbed5371b31923fcf6', 'teoh@gmail.com', '', '', '', '0162626262', '68a81ab8b355a.jpg', 1, 1, 1, 'Teoh123@'),
(2, 'Teoh', '4861ffec59dc9dcafabfdd6ecbde0038119707d1', 'hitem54041@cetnob.com', '', '', '', '0182938493', '66ec16b20143e.png', 1, 0, 0, '9jQ1Ibj5-P2'),
(3, 'teohmember', '9bc1c0bf7d4f25d2782ca9ee887ca81a5a832292', 'teohmember2@gmail.com', '', '', '', '0123365456', '68a8280b62a9d.png', 1, 0, 0, 'teohMem123!'),
(4, 'testing', 'c60ed1f0696c2a220aee8c2fb34902285d181640', 'test@gmail.com', '', '', '', '0123654789', '66e90eb0dadb3.png', 1, 1, 0, 'gXJ^WQRx-R9'),
(5, 'tests', '6ff1c6112f8f0ebe8bb7b3e89a3976e80113ded4', 'tests@gmail.com', '', '', '', '0147896523', '66e92e7867b03.png', 1, 1, 0, 'nyOoZ*LU*H2'),
(6, 'testrr', 'cad4a9a0c9632b3e34b19ba83e05b40228745523', 'test2rr@gmail.com', '', '', '', '0147896523', NULL, 1, 1, 1, 'z1y$7b!A%U1'),
(7, 'testrr', '896929858b6e3d465021be4399b3c9f7fdf2eb55', 'test2rr@gmail.com', '', '', '', '0147896523', NULL, 1, 1, 1, 'bYNe8PLx*D9'),
(8, 'teoh', 'd8d48206af16acbf7824a9cfaa9671e26ab09bbf', 'teohtest@gmail.com', '', '', '', '0147852369', '66e68a36710e4.png', 1, 1, 1, 'kAlpV@Rs!X0'),
(9, 'testqq', 'd3a7b75cad7de1ea48356b42da304a583b86b96e', 'dfsf@fds.fsd', '', '', '', '0192837465', NULL, 0, 1, 0, '!@^dZkR0$A7'),
(10, 'newcustomer', 'cdd2554d5ce7647fbf0d050bdbbdbba0671f0777', 'newcustomer@gmail.com', '', '', '', '0192849382', NULL, 1, 0, 0, 'ISbhuheu^L4');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`uid`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `user`
--
ALTER TABLE `user`
  MODIFY `uid` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
