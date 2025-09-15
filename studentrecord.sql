-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Aug 23, 2025 at 03:56 PM
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

DELIMITER $$
--
-- Procedures
--
CREATE DEFINER=`root`@`localhost` PROCEDURE `set_student_auto_increment` ()   BEGIN
    DECLARE next_id INT;
    SELECT IFNULL(MAX(studid), 0) + 1 INTO next_id FROM student;
    SET @sql = CONCAT('ALTER TABLE student AUTO_INCREMENT = ', next_id);
    PREPARE stmt FROM @sql;
    EXECUTE stmt;
    DEALLOCATE PREPARE stmt;
END$$

DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `student`
--

CREATE TABLE `student` (
  `studid` int(11) NOT NULL,
  `studName` varchar(100) NOT NULL,
  `studPic` longtext DEFAULT NULL,
  `studEmail` varchar(50) NOT NULL,
  `studPhone` varchar(11) NOT NULL,
  `studAddress` varchar(150) NOT NULL,
  `studCity` varchar(50) NOT NULL,
  `studState` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `student`
--

INSERT INTO `student` (`studid`, `studName`, `studPic`, `studEmail`, `studPhone`, `studAddress`, `studCity`, `studState`) VALUES
(1, 'Soo Xin Yun', '68a99648b47ee.jpg', 'xinyun@email.com', '0123456788', '72 Jalan Akasia, Taman Akasia Jaya', 'Sandakan', 'Sabah'),
(2, 'Alex Lim Chiang Shing', '68a9c705601cb.png', 'alexLim@email.com', '0123456789', '12 Jalan Melati 1, Taman Melati', 'Kuala Lumpur', 'Wilayah Persekutuan'),
(3, 'Christine Ooi Yan Ming', '68a9c7b04cf60.png', 'christineOoi@email.com', '0134567890', '45 Jalan Kenanga, Taman Sri Hartamas', 'Petaling Jaya', 'Selangor'),
(4, 'Tan Wei Ming', '68a9c7c6775bc.png', 'weiMing@email.com', '01123456789', '78 Jalan Bunga Raya, Taman Bunga Raya', 'Johor Bahru', 'Johor'),
(5, 'Elestine Teoh Ching Yan', '68a9c7cfa2b70.png', 'elestine233@email.com', '0178901234', '23 Lorong Mawar 2, Taman Mawar', 'George Town', 'Pulau Pinang'),
(6, 'Lim Chee Wei', '68a9c7d75eb13.png', 'cheewei125@email.com', '0191234567', '56 Jalan Angsana, Taman Angsana Jaya', 'Ipoh', 'Perak'),
(7, 'Lee Jia Hao', '68a9c7df0045f.png', 'jiahao568@email.com', '0145678901', '89 Jalan Cempaka, Taman Cempaka Indah', 'Kota Kinabalu', 'Sabah'),
(8, 'Teo Boon Keat', '68a9c7f494865.png', 'boonkeat298@email.com', '0167890123', '101 Jalan Teratai, Taman Teratai', 'Kuching', 'Sarawak'),
(9, 'Chan Xin Yi', '68a9c7fd1757c.png', 'xinyi896@email.com', '0189012345', '34 Jalan Dahlia, Taman Dahlia', 'Shah Alam', 'Selangor'),
(10, 'Lau Wei Ming', '68a9c80405b81.png', 'weiming2rr@email.com', '0120987654', '67 Jalan Orkid, Taman Orkid Jaya', 'Malacca City', 'Melaka'),
(11, 'Goh Hui Shan', '68a9c80f2ac25.png', 'huishan@email.com', '0132109876', '90 Jalan Seroja, Taman Seroja Baru', 'Kuantan', 'Pahang'),
(12, 'Ho Kah Wei', NULL, 'kahwei@email.com', '01198765432', '15 Jalan Kemuning, Taman Kemuning', 'Seri Kembangan', 'Selangor'),
(13, 'Yap Xin Rou', NULL, 'xinrou@email.com', '0176543210', '28 Lorong Anggerik, Taman Anggerik Jaya', 'Alor Setar', 'Kedah'),
(14, 'Chew Jun Hao', NULL, 'junhao@email.com', '0198765432', '51 Jalan Kemboja, Taman Kemboja', 'Kangar', 'Perlis'),
(15, 'Ong Pei Ling', NULL, 'peiling@email.com', '0143210987', '84 Jalan Bunga Tanjung, Taman Bunga Tanjung', 'Kota Bharu', 'Kelantan'),
(16, 'Low Wei Shen', NULL, 'weishen@email.com', '0165432109', '17 Jalan Keladi, Taman Keladi Indah', 'Kuala Terengganu', 'Terengganu'),
(17, 'Tee Siew Mei', NULL, 'siewmei@email.com', '0187654321', '40 Jalan Meranti, Taman Meranti Jaya', 'Batu Pahat', 'Johor'),
(18, 'Khoo Boon Seng', NULL, 'boonseng@email.com', '0121098765', '63 Jalan Kembang, Taman Kembang Baru', 'Sibu', 'Sarawak'),
(19, 'Chin Hui Ting', NULL, 'huiting@email.com', '0138765432', '96 Jalan Cendana, Taman Cendana', 'Miri', 'Sarawak'),
(20, 'Liew Jia Wei', NULL, 'jiawei@email.com', '0174321098', '29 Jalan Kasturi, Taman Kasturi', 'Seremban', 'Negeri Sembilan'),
(21, 'Soo Xin Jie', NULL, 'xinjie@email.com', '01154321098', '72 Jalan Akasia, Taman Akasia Jaya', 'Sandakan', 'Sabah'),
(22, 'Hello DEF', NULL, 'abcd@gmail.com', '01234567891', '5245343434345543 abcde', 'abcdefg', 'abcdefge');

-- --------------------------------------------------------

--
-- Table structure for table `user`
--

CREATE TABLE `user` (
  `uid` int(11) NOT NULL,
  `uname` varchar(100) NOT NULL,
  `pass` longtext NOT NULL,
  `email` varchar(50) NOT NULL,
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

INSERT INTO `user` (`uid`, `uname`, `pass`, `email`, `contact`, `proPic`, `status`, `level`, `superadmin`, `remark`) VALUES
(1, 'Teoh Kai Xin', 'e6a0a4ddc6257dad5a6160cbed5371b31923fcf6', 'teoh@gmail.com', '0162626262', '68a81ab8b355a.jpg', 1, 1, 1, 'Teoh123@');
--
-- Indexes for dumped tables
--

--
-- Indexes for table `student`
--
ALTER TABLE `student`
  ADD PRIMARY KEY (`studid`);

--
-- Indexes for table `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`uid`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `student`
--
ALTER TABLE `student`
  MODIFY `studid` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `user`
--
ALTER TABLE `user`
  MODIFY `uid` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
