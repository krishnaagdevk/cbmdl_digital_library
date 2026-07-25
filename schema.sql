-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jul 25, 2026 at 12:31 PM
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
-- Database: `cbmdl`
--

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

CREATE TABLE `admins` (
  `id` int(11) NOT NULL,
  `username` varchar(60) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(150) DEFAULT 'admin@cantonment.gov.in',
  `recovery_pin` varchar(255) DEFAULT '1953',
  `security_question` varchar(255) DEFAULT 'What is the Cantonment Library establishment year?',
  `security_answer` varchar(255) DEFAULT '1953'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`id`, `username`, `password`, `email`, `recovery_pin`, `security_question`, `security_answer`) VALUES
(1, 'admin', '$2y$10$nqqjmgOR3bo1xoY61uUvi.WaSGgLZN0OPoTdHm8PPNJonbxGbtcYy', 'admin@cantonment.gov.in', '1953', 'What is the Cantonment Library establishment year?', '1953');

-- --------------------------------------------------------

--
-- Table structure for table `admin_login_logs`
--

CREATE TABLE `admin_login_logs` (
  `id` int(11) NOT NULL,
  `username` varchar(60) NOT NULL,
  `ip_address` varchar(45) NOT NULL,
  `user_agent` varchar(255) DEFAULT NULL,
  `status` varchar(30) NOT NULL DEFAULT 'Success',
  `login_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin_login_logs`
--

INSERT INTO `admin_login_logs` (`id`, `username`, `ip_address`, `user_agent`, `status`, `login_at`) VALUES
(1, 'admin', '192.168.1.7', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/109.0.0.0 Safari/537.36', 'Success', '2026-07-23 11:41:24'),
(2, 'admin', '::1', 'Mozilla/5.0 (Linux; Android 15; Pixel 9) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Mobile Safari/537.36', 'Success', '2026-07-23 11:47:22'),
(3, 'admin', '192.168.1.7', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/109.0.0.0 Safari/537.36', 'Success', '2026-07-23 12:28:32'),
(4, 'admin', '192.168.1.7', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/109.0.0.0 Safari/537.36', 'Success', '2026-07-23 13:00:02'),
(5, 'admin', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'Failed Credentials', '2026-07-23 13:34:09'),
(6, 'admin', '::1', 'Browser/System', 'Password Reset Success', '2026-07-23 15:46:48'),
(7, 'admin', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'Password Reset Success', '2026-07-24 05:03:56'),
(8, 'admin', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'Success', '2026-07-24 05:04:04'),
(9, 'admin', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'Failed Credentials', '2026-07-24 05:04:29'),
(10, 'admin', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'Success', '2026-07-24 05:04:34'),
(11, 'admin', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'Failed Credentials', '2026-07-24 05:07:14'),
(12, 'admin', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'Success', '2026-07-24 05:07:18'),
(13, 'admin', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'Success', '2026-07-24 05:07:47'),
(14, 'admin', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'Success', '2026-07-24 06:15:49'),
(15, 'admin', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'Success', '2026-07-24 06:24:09'),
(16, 'admin', '192.168.1.7', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/109.0.0.0 Safari/537.36', 'Password Reset Failed (Verific', '2026-07-24 09:48:08'),
(17, 'admin', '192.168.1.7', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/109.0.0.0 Safari/537.36', 'Password Reset Success', '2026-07-24 09:48:45'),
(18, 'admin', '192.168.1.7', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/109.0.0.0 Safari/537.36', 'Success', '2026-07-24 09:48:53'),
(19, 'admin', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'Success', '2026-07-24 09:57:52'),
(20, 'admin', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'Success', '2026-07-24 11:26:54'),
(21, 'admin', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'Success', '2026-07-25 04:42:32'),
(22, '9457421088', '192.168.1.7', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/109.0.0.0 Safari/537.36', 'Failed Credentials', '2026-07-25 06:33:15'),
(23, 'admin', '192.168.1.7', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/109.0.0.0 Safari/537.36', 'Failed Credentials', '2026-07-25 06:33:25'),
(24, 'admin', '192.168.1.7', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/109.0.0.0 Safari/537.36', 'Success', '2026-07-25 06:33:33'),
(25, 'admin', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'Success', '2026-07-25 07:06:32'),
(26, 'admin', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'Success', '2026-07-25 08:30:52'),
(27, 'admin', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'Success', '2026-07-25 09:03:49'),
(28, 'admin', '192.168.1.7', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/109.0.0.0 Safari/537.36', 'Success', '2026-07-25 10:28:15');

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `name`, `created_at`) VALUES
(1, 'UPSC', '2026-07-13 02:23:32'),
(2, 'UP Police', '2026-07-13 02:23:46'),
(4, 'SSC', '2026-07-14 17:00:05'),
(5, 'UPTET', '2026-07-14 17:00:24'),
(6, 'NEET', '2026-07-14 17:00:33'),
(7, 'IIT', '2026-07-14 17:00:46'),
(8, 'Bank PO', '2026-07-14 17:01:17'),
(9, 'Railway', '2026-07-14 17:01:43'),
(13, 'Computer Science', '2026-07-22 05:16:57'),
(14, 'Physics', '2026-07-22 05:16:57'),
(15, 'Literature', '2026-07-22 05:16:57'),
(17, 'Fiction', '2026-07-22 05:16:57'),
(18, 'Technology', '2026-07-22 05:16:57'),
(19, 'Mathematics', '2026-07-22 05:16:57'),
(21, 'Business', '2026-07-23 07:45:43'),
(22, 'Physical Books', '2026-07-23 13:11:12'),
(23, 'Health Insurance', '2026-07-24 10:52:51'),
(24, 'Term Life', '2026-07-24 10:52:59');

-- --------------------------------------------------------

--
-- Table structure for table `ebooks`
--

CREATE TABLE `ebooks` (
  `id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `keywords` varchar(500) DEFAULT NULL,
  `pdf_file` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `ebooks`
--

INSERT INTO `ebooks` (`id`, `category_id`, `title`, `keywords`, `pdf_file`, `created_at`) VALUES
(1, 1, 'upsc title 1', 'pcs, ias, ides', 'book_6a544d39ab335.pdf', '2026-07-13 02:28:09'),
(2, 1, 'Title 2', 'keywords', 'book_6a544e7bc9639.pdf', '2026-07-13 02:33:31'),
(3, 1, 'Prelims', 'upsc pre', 'book_6a5cc5efcfe49.pdf', '2026-07-19 12:41:19'),
(10, 8, '1 gb', 'gbh', 'book_6a61da0f318bd.pdf', '2026-07-22 01:58:08'),
(11, 8, 'yyyy', 'aaa', 'book_6a606671122e8.pdf', '2026-07-22 06:42:57'),
(12, 8, 'hhhh', 'sss', 'book_6a6066c59853a.pdf', '2026-07-22 06:44:36'),
(13, 2, 'SI Police Parikshaaa', 'vivkek', 'book_6a61cc2f740ab.pdf', '2026-07-22 10:13:25'),
(14, 23, 'Care Supreme', 'unlimited auto restore', 'book_6a63443d51495.pdf', '2026-07-24 10:53:49'),
(15, 23, 'Care Ultimate', 'unlimited bonus, one claim unlimited', 'book_6a63448269e92.pdf', '2026-07-24 10:54:58'),
(16, 24, 'Term Insurance e-Touch 2', 'term life, term plan', 'book_6a6353fc88d87.pdf', '2026-07-24 12:01:00'),
(17, 24, 'Term Plan e-touch 2', '', 'book_6a63543a11623.pdf', '2026-07-24 12:02:02'),
(18, 24, 'POS Goal Suraksha', 'Bajaj Life', 'book_6a635adf36526.pdf', '2026-07-24 12:30:23'),
(19, 24, 'AWG Guranteed Plan', 'Bajaj Life', 'book_6a635b66a8e7b.pdf', '2026-07-24 12:32:38');

-- --------------------------------------------------------

--
-- Table structure for table `hold_requests`
--

CREATE TABLE `hold_requests` (
  `id` int(11) NOT NULL,
  `member_id` int(11) NOT NULL,
  `physical_book_id` int(11) NOT NULL,
  `requested_at` datetime DEFAULT current_timestamp(),
  `status` enum('Active','Fulfilled','Cancelled') DEFAULT 'Active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `lendings`
--

CREATE TABLE `lendings` (
  `id` int(11) NOT NULL,
  `member_id` int(11) NOT NULL,
  `physical_book_id` int(11) NOT NULL,
  `lent_at` datetime NOT NULL,
  `due_date` date NOT NULL,
  `transaction_id` varchar(150) DEFAULT NULL,
  `returned_at` datetime DEFAULT NULL,
  `fine_amount` decimal(10,2) DEFAULT 0.00,
  `fine_status` enum('None','Outstanding','Paid','Waived') DEFAULT 'None',
  `fine_payment_id` varchar(150) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `lendings`
--

INSERT INTO `lendings` (`id`, `member_id`, `physical_book_id`, `lent_at`, `due_date`, `transaction_id`, `returned_at`, `fine_amount`, `fine_status`, `fine_payment_id`) VALUES
(1, 1, 1, '2026-07-13 02:40:00', '2026-07-20', 'TXN00123', '2026-07-19 12:47:05', 0.00, 'None', NULL),
(2, 1, 1, '2026-07-19 18:28:00', '2026-07-27', 'TXN0012356', '2026-07-21 19:25:12', 0.00, 'None', NULL),
(3, 3, 3, '2026-07-21 21:46:00', '2026-07-22', 'tijojioj', '2026-07-21 22:57:49', 0.00, 'None', NULL),
(4, 1, 1, '2026-07-22 13:54:08', '2026-07-21', 'qwe455', '2026-07-22 18:02:14', 0.00, 'None', NULL),
(5, 2, 2, '2026-07-22 15:50:36', '2026-07-21', '786ji', '2026-07-25 10:25:04', 0.00, 'None', NULL),
(6, 10, 1, '2026-07-23 14:19:30', '2026-07-23', 'aaass', NULL, 0.00, 'None', NULL),
(7, 9, 5, '2026-07-24 18:37:44', '2026-07-30', '--', '2026-07-24 18:49:27', 0.00, 'None', NULL),
(8, 9, 9, '2026-07-24 18:59:42', '2026-08-07', 'TXN056', '2026-07-25 10:25:09', 0.00, 'None', NULL),
(9, 9, 7, '2026-07-24 19:06:30', '2026-07-31', 'TXN00123m', '2026-07-24 19:12:35', 0.00, 'None', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `members`
--

CREATE TABLE `members` (
  `id` int(11) NOT NULL,
  `membership_id` varchar(30) NOT NULL,
  `name` varchar(150) NOT NULL,
  `gender` enum('Male','Female','Other') DEFAULT 'Male',
  `guardian_name` varchar(150) NOT NULL,
  `mobile` varchar(20) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(150) DEFAULT NULL,
  `address` text NOT NULL,
  `aadhar_no` varchar(30) NOT NULL,
  `duration` enum('Yearly','Half Yearly','Quarterly','Monthly','Daily') NOT NULL,
  `shift` varchar(100) DEFAULT 'Full Day',
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `membership_fee` varchar(50) NOT NULL,
  `payment_id` varchar(150) NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `is_active` tinyint(1) DEFAULT 1,
  `membership_plan_id` int(11) DEFAULT NULL,
  `approved` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `members`
--

INSERT INTO `members` (`id`, `membership_id`, `name`, `gender`, `guardian_name`, `mobile`, `password`, `email`, `address`, `aadhar_no`, `duration`, `shift`, `start_date`, `end_date`, `membership_fee`, `payment_id`, `created_at`, `is_active`, `membership_plan_id`, `approved`) VALUES
(1, 'CBMDL1', 'Abhinav Garg', 'Male', 'Pradeep Kumar', '9457421088', '$2y$10$DZgs2pJISWJqBE8hbpxmK.tfY5CdHke2NxbwBBaBZwT3WvgrSz5rC', 'er.abhinavgarg17@gmail.com', 'TP Nagar Meerut', 'xxxx xxxx 1234', 'Monthly', 'Morning', '2026-07-23', '2026-08-23', '500', 'TXN1234', '2026-07-13 02:30:34', 1, 4, 1),
(2, 'CBMDL2', 'Monu', 'Male', 'Ramesh Singh', '9058721088', '$2y$10$wF.MYzB.uF41PvhBS5njYexp/th1BVGwzNaUQnDAeOeCqD25WJZxq', 'rameshkumar11@gmail.com', 'H.No. 1187, Cariappa Road, Meerut Cantt., Meerut.', 'xxxx xxxx 1894', 'Monthly', 'Morning', '2026-08-27', '2026-09-27', '100', 'TEST_RENEW_UTR_1784796178', '2026-07-21 05:33:51', 1, 1, 1),
(3, 'CBMDL3', 'Kunal', 'Male', 'Kuldeep', '8218772351', '$2y$10$mZRwB4TtwxEreOdK2Qvtoed9AhNguCxrtt/Ahip33iBE2917wsFTK', '', '111/20 abcd', 'xxxxxxxx1234', 'Yearly', 'Night', '2026-07-21', '2027-07-21', '3500', 'id-789', '2026-07-21 14:37:46', 1, 1, 1),
(4, 'CBMDL4', 'kkkkk', 'Male', 'uuuuu', '8888888888', '$2y$10$S3j1HzulHXKM6nxPNg1gpOqnnowW3lJCgFc3GzN5Q2rtqcdn4YqI.', '', 'jasflaf', 'xxxxxxxx9999', '', 'Full Day', '2026-07-21', '2027-07-21', '0', 'id999', '2026-07-21 17:40:08', 1, 2, 1),
(5, 'CBMDLM5', 'Bharti Bansal', 'Female', 'Pawan Bansal', '8791891088', '$2y$10$QcUdqhaBuwEjxuWl793asOUdReq1OiRtmCnl3XzhxQjD3E2aqn/jy', 'bhartibansal18oct@gmail.com', '27 Sant Vihar, Meerut', '123456789123', '', 'Morning', '2026-07-22', '2027-07-22', '0', 'TXN123445', '2026-07-22 13:26:29', 1, 4, 1),
(9, 'CBMDLM9', 'Anju Garg', 'Female', 'SC Guptaa', '9411067157', '$2y$10$NjcMfUeM4j.1y0m59qM3HesULaI.XWgRXnrlkaKRfY5U5oORfjoSC', 'anjugupta0306@gmail.com', '27 TP Nagar', '123456009125', 'Monthly', 'Evening', '2026-07-22', '2026-08-22', '500', 'TXN1234sdf', '2026-07-22 13:56:43', 1, 4, 1),
(10, 'CBMDLM10', 'Pradeep kumar', 'Male', 'Gupta ji', '7840887205', '$2y$10$KMBLo9ETDN1CwzwVG3fej.Qp7F7OIzBJfD9mRhd.lLN4DjAvKqN5y', 'gargpradeep1964@gmail.com', 'B.Puri Meerut', '123567889900', 'Daily', 'Evening', '2026-07-25', '2026-07-26', '50', 'TXN1234888', '2026-07-23 08:39:07', 1, 5, 1),
(11, 'CBMDLM11', 'Piyush Sir', 'Male', 'Mr. Gautam', '1234567890', '$2y$10$c3uWWPUrjRDg5HmBKsYXru7tZS6fRKJ6PxSa4noBFJhR7a3RvPP2i', 'piyushcbm@gmail.com', 'Meerut Cantt Lal Kurti', '123456788888', 'Yearly', 'Night', '2026-07-24', '2027-07-24', '0', 'NA', '2026-07-24 14:16:00', 1, 7, 1),
(12, 'CBMDLM12', 'Amardeep Agarwal', 'Male', 'Sunder Lal', '9219666624', '$2y$10$tXSt8YLH5IZhNsgwtRqPAuuUmkLre4nR3SDEKG.MoIA37ABbr4zCq', 'er.abhinavgarg17@gmail.com', '111/20 Agarwal', '987654321987', 'Monthly', 'Full Day', '2026-07-25', '2026-08-25', '500', 'TXN89098', '2026-07-25 08:32:29', 1, 4, 1);

-- --------------------------------------------------------

--
-- Table structure for table `membership_history`
--

CREATE TABLE `membership_history` (
  `id` int(11) NOT NULL,
  `member_id` int(11) NOT NULL,
  `membership_id` varchar(30) NOT NULL,
  `membership_plan_id` int(11) DEFAULT NULL,
  `plan_name` varchar(100) DEFAULT NULL,
  `duration` varchar(50) NOT NULL,
  `shift` varchar(50) DEFAULT 'Both',
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `membership_fee` decimal(10,2) NOT NULL DEFAULT 0.00,
  `payment_id` varchar(150) DEFAULT NULL,
  `action_type` enum('Initial Joining','Renewal','Plan Switch','Manual Adjustment') NOT NULL DEFAULT 'Initial Joining',
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `membership_history`
--

INSERT INTO `membership_history` (`id`, `member_id`, `membership_id`, `membership_plan_id`, `plan_name`, `duration`, `shift`, `start_date`, `end_date`, `membership_fee`, `payment_id`, `action_type`, `created_at`) VALUES
(1, 1, 'CBMDL1', 1, 'Yearly', 'Yearly', 'Full Day', '2026-07-13', '2026-07-24', 3500.00, 'TXN1234', 'Initial Joining', '2026-07-13 02:30:34'),
(2, 2, 'CBMDL2', 2, 'Half Yearly', 'Half Yearly', 'Evening', '2026-07-21', '2026-07-21', 2200.00, 'TXN123422', 'Initial Joining', '2026-07-21 05:33:51'),
(3, 3, 'CBMDL3', 1, 'Yearly', 'Yearly', 'Evening', '2026-07-21', '2027-07-21', 3500.00, 'id-789', 'Initial Joining', '2026-07-21 14:37:46'),
(4, 4, 'CBMDL4', 2, 'Yearly', 'Yearly', 'Full Day', '2026-07-21', '2027-07-21', 0.00, 'id999', 'Initial Joining', '2026-07-21 17:40:08'),
(5, 5, 'CBMDLM5', 4, 'Yearly', 'Yearly', 'Morning', '2026-07-22', '2027-07-22', 0.00, 'TXN123445', 'Initial Joining', '2026-07-22 13:26:29'),
(6, 9, 'CBMDLM9', 4, 'Monthly', 'Monthly', 'Evening', '2026-07-22', '2026-08-22', 500.00, 'TXN1234sdf', 'Initial Joining', '2026-07-22 13:56:43'),
(8, 1, 'CBMDL1', 4, 'Monthly', 'Monthly', 'Full Day', '2026-07-23', '2026-08-23', 500.00, 'TXN1234', 'Renewal', '2026-07-23 06:56:34'),
(9, 2, 'CBMDL2', 4, 'Monthly', 'Monthly', 'Morning', '2026-07-26', '2026-08-26', 500.00, 'TXN1234qq', 'Renewal', '2026-07-23 08:26:21'),
(10, 10, 'CBMDLM10', 5, 'Daily', 'Daily', 'Morning', '2026-07-23', '2026-07-24', 50.00, 'pay5689', 'Initial Joining', '2026-07-23 08:39:07'),
(12, 11, 'CBMDLM11', 7, 'Yearly', 'Yearly', 'Full Day', '2026-07-24', '2027-07-24', 0.00, 'NA', 'Initial Joining', '2026-07-24 14:16:00'),
(13, 10, 'CBMDLM10', 5, 'Daily', 'Daily', 'Evening', '2026-07-25', '2026-07-26', 50.00, 'TXN1234888', 'Renewal', '2026-07-25 08:12:38'),
(14, 12, 'CBMDLM12', 4, 'Monthly', 'Monthly', 'Night', '2026-07-25', '2026-08-25', 500.00, 'TXN89098', 'Initial Joining', '2026-07-25 08:32:29');

-- --------------------------------------------------------

--
-- Table structure for table `membership_plans`
--

CREATE TABLE `membership_plans` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `duration` varchar(50) NOT NULL,
  `amount` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `membership_plans`
--

INSERT INTO `membership_plans` (`id`, `name`, `duration`, `amount`) VALUES
(1, 'Yearly', 'Yearly', 3500.00),
(2, 'Half Yearly', 'Half Yearly', 2200.00),
(3, 'Quarterly', 'Quarterly', 1200.00),
(4, 'Monthly', 'Monthly', 500.00),
(5, 'Daily', 'Daily', 50.00),
(7, 'Cantt Board Members', 'Till Service', 1.00);

-- --------------------------------------------------------

--
-- Table structure for table `member_login_logs`
--

CREATE TABLE `member_login_logs` (
  `id` int(11) NOT NULL,
  `member_id` int(11) DEFAULT NULL,
  `mobile` varchar(20) NOT NULL,
  `member_name` varchar(100) DEFAULT NULL,
  `shift` varchar(50) DEFAULT NULL,
  `ip_address` varchar(45) NOT NULL,
  `user_agent` varchar(255) DEFAULT NULL,
  `status` varchar(50) NOT NULL DEFAULT 'Success',
  `login_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `member_login_logs`
--

INSERT INTO `member_login_logs` (`id`, `member_id`, `mobile`, `member_name`, `shift`, `ip_address`, `user_agent`, `status`, `login_at`) VALUES
(1, 10, '7840887205', 'Pradeep kumar', 'Morning', '192.168.1.7', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/109.0.0.0 Safari/537.36', 'Success', '2026-07-23 11:41:27'),
(2, 3, '8218772351', 'Kunal', 'Full Day', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'Success', '2026-07-23 12:35:06'),
(3, 1, '9457421088', 'Abhinav Garg', 'Morning', '192.168.1.7', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/109.0.0.0 Safari/537.36', 'Shift Restricted', '2026-07-23 13:14:49'),
(4, 1, '9457421088', 'Abhinav Garg', 'Morning', '192.168.1.8', 'Mozilla/5.0 (iPad; CPU OS 26_5_2 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/150.0.7871.51 Mobile/15E148 Safari/604.1', 'Shift Restricted', '2026-07-23 13:20:19'),
(5, 9, '9411067157', 'Anju Garg', 'Evening', '192.168.1.8', 'Mozilla/5.0 (iPad; CPU OS 26_5_2 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/150.0.7871.51 Mobile/15E148 Safari/604.1', 'Success', '2026-07-23 13:20:59'),
(6, 3, '8218772351', 'Kunal', 'Full Day', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'Success', '2026-07-23 13:25:20'),
(7, 9, '9411067157', 'Anju Garg', 'Evening', '192.168.1.8', 'Mozilla/5.0 (iPad; CPU OS 26_5_2 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/150.0.7871.51 Mobile/15E148 Safari/604.1', 'Success', '2026-07-23 13:28:46'),
(8, 1, '9457421088', 'Abhinav Garg', 'Morning', '192.168.1.7', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/109.0.0.0 Safari/537.36', 'Shift Restricted', '2026-07-23 13:30:49'),
(9, 10, '7840887205', 'Pradeep kumar', 'Evening', '192.168.1.7', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/109.0.0.0 Safari/537.36', 'Success', '2026-07-23 13:31:36'),
(10, 3, '8218772351', 'Kunal', 'Full Day', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'Shift Restricted', '2026-07-23 15:44:35'),
(11, 10, '7840887205', 'Pradeep kumar', 'Evening', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'Shift Restricted', '2026-07-23 15:44:43'),
(12, 3, '8218772351', 'Kunal', 'Full Day', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'Success', '2026-07-24 04:44:58'),
(13, 3, '8218772351', 'Kunal', 'Full Day', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'Success', '2026-07-24 06:33:16'),
(14, 2, '9058721088', 'Vivek Kumar', 'Morning', '192.168.1.8', 'Mozilla/5.0 (iPad; CPU OS 26_5_2 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/150.0.7871.51 Mobile/15E148 Safari/604.1', 'Success', '2026-07-24 09:56:20'),
(15, 2, '9058721088', 'Vivek Kumar', 'Morning', '192.168.1.8', 'Mozilla/5.0 (iPad; CPU OS 26_5_2 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/150.0.7871.51 Mobile/15E148 Safari/604.1', 'Upcoming Membership', '2026-07-24 10:05:37'),
(16, 2, '9058721088', 'Vivek Kumar', 'Morning', '192.168.1.8', 'Mozilla/5.0 (iPad; CPU OS 26_5_2 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/150.0.7871.51 Mobile/15E148 Safari/604.1', 'Upcoming Membership', '2026-07-24 11:34:28'),
(17, 1, '9457421088', 'Abhinav Garg', 'Morning', '192.168.1.8', 'Mozilla/5.0 (iPad; CPU OS 26_5_2 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/150.0.7871.51 Mobile/15E148 Safari/604.1', 'Inactive Account', '2026-07-24 11:34:55'),
(18, 10, '7840887205', 'Pradeep kumar', 'Evening', '192.168.1.8', 'Mozilla/5.0 (iPad; CPU OS 26_5_2 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/150.0.7871.51 Mobile/15E148 Safari/604.1', 'Success', '2026-07-24 11:36:02'),
(19, 10, '7840887205', 'Pradeep kumar', 'Evening', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'Success', '2026-07-24 11:52:16'),
(20, 10, '7840887205', 'Pradeep kumar', 'Evening', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'Success', '2026-07-24 11:53:18'),
(21, 9, '9411067157', 'Anju Garg', 'Evening', '192.168.1.8', 'Mozilla/5.0 (iPad; CPU OS 26_5_2 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/150.0.7871.51 Mobile/15E148 Safari/604.1', 'Success', '2026-07-24 12:45:09'),
(22, 9, '9411067157', 'Anju Garg', 'Evening', '192.168.1.8', 'Mozilla/5.0 (iPad; CPU OS 26_5_2 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/150.0.7871.51 Mobile/15E148 Safari/604.1', 'Success', '2026-07-24 13:03:20'),
(23, 9, '9411067157', 'Anju Garg', 'Evening', '192.168.1.8', 'Mozilla/5.0 (iPad; CPU OS 26_5_2 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/150.0.7871.51 Mobile/15E148 Safari/604.1', 'Success', '2026-07-24 13:44:20'),
(24, 11, '1234567890', 'Piyush Sir', '', '192.168.1.8', 'Mozilla/5.0 (iPad; CPU OS 26_5_2 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/150.0.7871.51 Mobile/15E148 Safari/604.1', 'Success', '2026-07-24 14:17:56'),
(25, 11, '1234567890', 'Piyush Sir', 'Full Day', '192.168.1.8', 'Mozilla/5.0 (iPad; CPU OS 26_5_2 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/150.0.7871.51 Mobile/15E148 Safari/604.1', 'Shift Restricted', '2026-07-24 14:32:53'),
(26, 11, '1234567890', 'Piyush Sir', 'Full Day', '192.168.1.8', 'Mozilla/5.0 (iPad; CPU OS 26_5_2 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/150.0.7871.51 Mobile/15E148 Safari/604.1', 'Shift Restricted', '2026-07-24 14:33:43'),
(27, 11, '1234567890', 'Piyush Sir', 'Full Day', '192.168.1.8', 'Mozilla/5.0 (iPad; CPU OS 26_5_2 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/150.0.7871.51 Mobile/15E148 Safari/604.1', 'Shift Restricted', '2026-07-24 14:34:10'),
(28, 11, '1234567890', 'Piyush Sir', 'Full Day', '192.168.1.8', 'Mozilla/5.0 (iPad; CPU OS 26_5_2 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/150.0.7871.51 Mobile/15E148 Safari/604.1', 'Shift Restricted', '2026-07-24 14:36:05'),
(29, 11, '1234567890', 'Piyush Sir', 'Full Day', '192.168.1.8', 'Mozilla/5.0 (iPad; CPU OS 26_5_2 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/150.0.7871.51 Mobile/15E148 Safari/604.1', 'Shift Restricted', '2026-07-24 14:37:25'),
(30, 3, '8218772351', 'Kunal', 'Full Day', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36 Edg/150.0.0.0', 'Shift Restricted', '2026-07-24 14:40:41'),
(31, 3, '8218772351', 'Kunal', 'Full Day', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'Shift Restricted', '2026-07-24 14:49:05'),
(32, 3, '8218772351', 'Kunal', 'Full Day', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'Shift Restricted', '2026-07-24 14:49:19'),
(33, 10, '7840887205', 'Pradeep kumar', 'Evening', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'Success', '2026-07-24 14:49:36'),
(34, 11, '1234567890', 'Piyush Sir', 'Full Day', '192.168.1.8', 'Mozilla/5.0 (iPad; CPU OS 26_5_2 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/150.0.7871.51 Mobile/15E148 Safari/604.1', 'Shift Restricted', '2026-07-24 15:24:43'),
(35, 11, '1234567890', 'Piyush Sir', 'Full Day', '192.168.1.8', 'Mozilla/5.0 (iPad; CPU OS 26_5_2 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/150.0.7871.51 Mobile/15E148 Safari/604.1', 'Success', '2026-07-24 15:25:47'),
(36, 3, '8218772351', 'Kunal', 'Night', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'Success', '2026-07-24 15:31:13'),
(37, 11, '1234567890', 'Piyush Sir', 'Night', '192.168.1.8', 'Mozilla/5.0 (iPad; CPU OS 26_5_2 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/150.0.7871.51 Mobile/15E148 Safari/604.1', 'Success', '2026-07-24 15:47:54'),
(38, 11, '1234567890', 'Piyush Sir', 'Night', '192.168.1.8', 'Mozilla/5.0 (iPad; CPU OS 26_5_2 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/150.0.7871.51 Mobile/15E148 Safari/604.1', 'Success', '2026-07-24 15:59:41'),
(39, 11, '1234567890', 'Piyush Sir', 'Night', '192.168.1.8', 'Mozilla/5.0 (iPad; CPU OS 26_5_2 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/150.0.7871.51 Mobile/15E148 Safari/604.1', 'Success', '2026-07-24 16:00:40'),
(40, 11, '1234567890', 'Piyush Sir', 'Night', '192.168.1.8', 'Mozilla/5.0 (iPad; CPU OS 26_5_2 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/150.0.7871.51 Mobile/15E148 Safari/604.1', 'Success', '2026-07-24 16:02:10'),
(41, 3, '8218772351', 'Kunal', 'Night', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'Shift Restricted', '2026-07-25 05:57:56'),
(42, 1, '9457421088', 'Abhinav Garg', 'Morning', '192.168.1.7', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/109.0.0.0 Safari/537.36', 'Shift Restricted', '2026-07-25 06:32:51'),
(43, NULL, '9457421088', NULL, NULL, '192.168.1.8', 'Mozilla/5.0 (iPad; CPU OS 26_5_2 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/150.0.7871.51 Mobile/15E148 Safari/604.1', 'Failed Credentials', '2026-07-25 07:20:35'),
(44, NULL, '4777777777', NULL, NULL, '192.168.1.8', 'Mozilla/5.0 (iPad; CPU OS 26_5_2 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/150.0.7871.51 Mobile/15E148 Safari/604.1', 'Failed Credentials', '2026-07-25 07:21:10'),
(45, 11, '1234567890', 'Piyush Sir', 'Night', '192.168.1.8', 'Mozilla/5.0 (iPad; CPU OS 26_5_2 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/150.0.7871.51 Mobile/15E148 Safari/604.1', 'Shift Restricted', '2026-07-25 07:24:48'),
(46, 11, '1234567890', 'Piyush Sir', 'Night', '192.168.1.8', 'Mozilla/5.0 (iPad; CPU OS 26_5_2 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/150.0.7871.51 Mobile/15E148 Safari/604.1', 'Failed Credentials', '2026-07-25 07:25:23'),
(47, NULL, '4567543214', NULL, NULL, '192.168.1.8', 'Mozilla/5.0 (iPad; CPU OS 26_5_2 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/150.0.7871.51 Mobile/15E148 Safari/604.1', 'Failed Credentials', '2026-07-25 07:26:20'),
(48, 1, '9457421088', 'Abhinav Garg', 'Morning', '192.168.1.8', 'Mozilla/5.0 (iPad; CPU OS 26_5_2 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/150.0.7871.51 Mobile/15E148 Safari/604.1', 'Failed Credentials', '2026-07-25 07:27:15'),
(49, 1, '9457421088', 'Abhinav Garg', 'Morning', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'Success', '2026-07-25 07:28:27'),
(50, 2, '9058721088', 'Monu', 'Morning', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'Upcoming Membership', '2026-07-25 07:31:26'),
(51, 10, '7840887205', 'Pradeep kumar', 'Evening', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'Membership Expired', '2026-07-25 07:31:58'),
(52, 10, '7840887205', 'Pradeep kumar', 'Evening', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'Failed Credentials', '2026-07-25 07:32:08'),
(53, 1, '9457421088', 'Abhinav Garg', 'Morning', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36 Edg/150.0.0.0', 'Inactive Account', '2026-07-25 07:33:36'),
(54, 1, '9457421088', 'Abhinav Garg', 'Morning', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'Failed Credentials', '2026-07-25 07:34:21'),
(55, 3, '8218772351', 'Kunal', 'Night', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'Shift Restricted', '2026-07-25 07:46:47'),
(56, 1, '9457421088', 'Abhinav Garg', 'Morning', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'Success', '2026-07-25 07:47:41'),
(57, 5, '8791891088', 'Bharti Bansal', 'Morning', '192.168.1.8', 'Mozilla/5.0 (iPad; CPU OS 26_5_2 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/150.0.7871.51 Mobile/15E148 Safari/604.1', 'Success', '2026-07-25 07:50:53'),
(58, 10, '7840887205', 'Pradeep kumar', 'Evening', '192.168.1.8', 'Mozilla/5.0 (iPad; CPU OS 26_5_2 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/150.0.7871.51 Mobile/15E148 Safari/604.1', 'Shift Restricted', '2026-07-25 08:20:05'),
(59, 5, '8791891088', 'Bharti Bansal', 'Morning', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'Success', '2026-07-25 08:20:50'),
(60, 10, '7840887205', 'Pradeep kumar', 'Evening', '192.168.1.8', 'Mozilla/5.0 (iPad; CPU OS 26_5_2 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/150.0.7871.51 Mobile/15E148 Safari/604.1', 'Failed Credentials', '2026-07-25 08:25:14'),
(61, 1, '9457421088', 'Abhinav Garg', 'Morning', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'Success', '2026-07-25 08:25:37'),
(62, 5, '8791891088', 'Bharti Bansal', 'Morning', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36 Edg/150.0.0.0', 'Failed Credentials', '2026-07-25 08:26:48'),
(63, 5, '8791891088', 'Bharti Bansal', 'Morning', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36 Edg/150.0.0.0', 'Success', '2026-07-25 08:27:15'),
(64, NULL, '9219666625', NULL, NULL, '192.168.1.8', 'Mozilla/5.0 (iPad; CPU OS 26_5_2 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/150.0.7871.51 Mobile/15E148 Safari/604.1', 'Failed Credentials', '2026-07-25 08:41:25'),
(65, 12, '9219666624', 'Amardeep Agarwal', 'Night', '192.168.1.8', 'Mozilla/5.0 (iPad; CPU OS 26_5_2 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/150.0.7871.51 Mobile/15E148 Safari/604.1', 'Shift Restricted', '2026-07-25 08:42:02'),
(66, 12, '9219666624', 'Amardeep Agarwal', 'Full Day', '192.168.1.8', 'Mozilla/5.0 (iPad; CPU OS 26_5_2 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/150.0.7871.51 Mobile/15E148 Safari/604.1', 'Success', '2026-07-25 08:43:08'),
(67, 12, '9219666624', 'Amardeep Agarwal', 'Full Day', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'Success', '2026-07-25 09:09:16'),
(68, 1, '9457421088', 'Abhinav Garg', 'Morning', '192.168.1.7', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/109.0.0.0 Safari/537.36', 'Shift Restricted', '2026-07-25 09:11:43'),
(69, NULL, '9319666624', NULL, NULL, '192.168.1.7', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/109.0.0.0 Safari/537.36', 'Failed Credentials', '2026-07-25 09:11:54'),
(70, 12, '9219666624', 'Amardeep Agarwal', 'Full Day', '192.168.1.7', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/109.0.0.0 Safari/537.36', 'Success', '2026-07-25 09:12:18'),
(71, 1, '9457421088', 'Abhinav Garg', 'Morning', '192.168.1.8', 'Mozilla/5.0 (iPad; CPU OS 26_5_2 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/150.0.7871.51 Mobile/15E148 Safari/604.1', 'Shift Restricted', '2026-07-25 09:54:46'),
(72, 3, '8218772351', 'Kunal', 'Night', '192.168.1.8', 'Mozilla/5.0 (iPad; CPU OS 26_5_2 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/150.0.7871.51 Mobile/15E148 Safari/604.1', 'Shift Restricted', '2026-07-25 10:03:24'),
(73, 12, '9219666624', 'Amardeep Agarwal', 'Full Day', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'Success', '2026-07-25 10:04:09'),
(74, 12, '9219666624', 'Amardeep Agarwal', 'Full Day', '192.168.1.8', 'Mozilla/5.0 (iPad; CPU OS 26_5_2 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/150.0.7871.51 Mobile/15E148 Safari/604.1', 'Success', '2026-07-25 10:04:32');

-- --------------------------------------------------------

--
-- Table structure for table `physical_books`
--

CREATE TABLE `physical_books` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `book_code` varchar(100) NOT NULL,
  `shelf_number` varchar(100) DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `author` varchar(150) DEFAULT NULL,
  `publisher` varchar(150) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `physical_books`
--

INSERT INTO `physical_books` (`id`, `title`, `book_code`, `shelf_number`, `price`, `author`, `publisher`, `created_at`) VALUES
(1, 'UPSC Prelims', 'CBMDLB001', '1', 875.00, 'Abhinav Author', 'Arihant Publication', '2026-07-13 02:40:25'),
(2, 'Jee Main', 'CBMDLB002', '2', 1855.00, 'H.C. Verma', 'Arihant Publications', '2026-07-21 13:40:48'),
(3, 'title1', 'CBMDLB003', '2', 1000.00, 'kunn', 'arihant', '2026-07-21 16:15:58'),
(4, 'Introduction to Algorithms', 'CBMDLB101', '3', 1899.00, 'Thomas H. Cormen', 'MIT Press', '2026-07-22 05:16:57'),
(5, 'Concepts of Physics Vol 1', 'CBMDLB102', '1', 450.00, 'H.C. Verma', 'Bharati Bhawan', '2026-07-22 05:16:57'),
(6, 'To Kill a Mockingbird', 'CBMDLB103', '2', 399.00, 'Harper Lee', 'J. B. Lippincott & Co.', '2026-07-22 05:16:57'),
(7, 'The Intelligent Investor', 'CBMDLB104', '3', 799.00, 'Benjamin Graham', 'HarperBusiness', '2026-07-22 05:16:57'),
(8, 'The Great Gatsby', 'CBMDLB105', '1', 299.00, 'F. Scott Fitzgerald', 'Charles Scribner\'s Sons', '2026-07-22 05:16:57'),
(9, 'Clean Code: A Handbook of Agile Software Craftsmanship', 'CBMDLB106', '2', 1250.00, 'Robert C. Martin', 'Prentice Hall', '2026-07-22 05:16:57'),
(10, 'Thomas\' Calculus', 'CBMDLB107', '3', 1499.00, 'George B. Thomas', 'Pearson', '2026-07-22 05:16:57'),
(11, 'Title 11', 'CBMDLB004', '1', 111.00, 'author 11', 'Publisher 11', '2026-07-22 10:11:07'),
(16, 'Title 12', 'CBMDLB005', '1', 12.00, 'author 12', 'Publisher 12', '2026-07-22 12:16:10'),
(17, 'Title 13', 'CBMDLB013', '1', 13.00, 'author 13', 'Publisher 13', '2026-07-23 13:11:12');

-- --------------------------------------------------------

--
-- Table structure for table `print_requests`
--

CREATE TABLE `print_requests` (
  `id` int(11) NOT NULL,
  `member_id` int(11) NOT NULL,
  `ebook_id` int(11) NOT NULL,
  `pages` varchar(100) NOT NULL,
  `status` enum('Pending','Completed','Rejected') DEFAULT 'Pending',
  `requested_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `print_requests`
--

INSERT INTO `print_requests` (`id`, `member_id`, `ebook_id`, `pages`, `status`, `requested_at`) VALUES
(1, 1, 2, '1-2', 'Completed', '2026-07-19 07:49:08'),
(2, 1, 3, '1 to 8', 'Completed', '2026-07-19 19:03:35'),
(4, 3, 1, '9', 'Completed', '2026-07-21 21:38:51'),
(7, 1, 10, '3 - 9', 'Completed', '2026-07-22 15:07:42'),
(8, 9, 10, '1 to 5', 'Completed', '2026-07-22 20:43:49'),
(9, 10, 10, '6', 'Completed', '2026-07-23 14:54:10'),
(11, 10, 12, '4', 'Rejected', '2026-07-23 15:00:01'),
(12, 10, 3, '3', 'Completed', '2026-07-23 15:01:00'),
(13, 3, 10, '3', 'Rejected', '2026-07-23 16:58:43'),
(14, 3, 10, '2', 'Rejected', '2026-07-23 18:07:39'),
(15, 3, 1, '3-10', 'Rejected', '2026-07-23 18:10:47'),
(16, 3, 2, '2-6', 'Rejected', '2026-07-23 18:11:00'),
(17, 11, 19, '5', 'Rejected', '2026-07-24 21:20:45'),
(18, 11, 19, '3', 'Completed', '2026-07-24 21:21:24'),
(19, 11, 10, '1', 'Rejected', '2026-07-24 21:26:51'),
(20, 11, 10, '5', 'Completed', '2026-07-24 21:27:16');

-- --------------------------------------------------------

--
-- Table structure for table `reading_requests`
--

CREATE TABLE `reading_requests` (
  `id` int(11) NOT NULL,
  `member_id` int(11) NOT NULL,
  `ebook_id` int(11) NOT NULL,
  `status` enum('Pending','Approved','Rejected','Expired') DEFAULT 'Pending',
  `requested_at` datetime DEFAULT current_timestamp(),
  `approved_at` datetime DEFAULT NULL,
  `expires_at` datetime DEFAULT NULL,
  `duration_minutes` int(11) DEFAULT 15,
  `started_reading_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `reading_requests`
--

INSERT INTO `reading_requests` (`id`, `member_id`, `ebook_id`, `status`, `requested_at`, `approved_at`, `expires_at`, `duration_minutes`, `started_reading_at`) VALUES
(1, 1, 2, 'Expired', '2026-07-13 02:44:50', '2026-07-13 02:46:47', '2026-07-23 16:06:59', 15, NULL),
(2, 1, 2, 'Expired', '2026-07-19 07:30:08', '2026-07-19 07:30:34', '2026-07-23 16:06:59', 15, NULL),
(3, 1, 1, 'Expired', '2026-07-19 08:01:29', '2026-07-19 08:01:49', '2026-07-23 16:06:59', 15, NULL),
(4, 1, 2, 'Expired', '2026-07-19 08:08:43', '2026-07-19 08:08:53', '2026-07-23 16:06:59', 15, NULL),
(5, 1, 2, 'Expired', '2026-07-19 09:30:39', '2026-07-19 09:30:51', '2026-07-23 16:06:59', 15, NULL),
(6, 1, 2, 'Expired', '2026-07-19 09:57:00', '2026-07-19 09:57:12', '2026-07-23 16:06:59', 15, NULL),
(7, 1, 3, 'Expired', '2026-07-19 18:53:47', '2026-07-19 18:54:26', '2026-07-23 16:06:59', 15, NULL),
(10, 1, 3, 'Expired', '2026-07-21 19:46:45', '2026-07-21 19:48:03', '2026-07-23 16:06:59', 15, NULL),
(15, 3, 1, 'Expired', '2026-07-21 21:18:05', '2026-07-21 21:42:19', '2026-07-23 16:14:17', 15, '2026-07-23 15:24:07'),
(16, 3, 2, 'Expired', '2026-07-21 22:02:23', '2026-07-21 22:02:51', '2026-07-23 16:14:17', 15, '2026-07-23 15:22:58'),
(20, 3, 10, 'Expired', '2026-07-22 10:32:12', '2026-07-22 10:35:32', '2026-07-23 16:14:17', 15, '2026-07-23 15:24:00'),
(23, 3, 3, 'Rejected', '2026-07-22 10:38:00', NULL, NULL, 15, NULL),
(25, 3, 10, 'Rejected', '2026-07-22 13:10:35', NULL, NULL, 15, NULL),
(26, 1, 10, 'Expired', '2026-07-22 14:15:14', '2026-07-22 14:15:48', '2026-07-23 16:06:59', 15, '2026-07-23 16:01:50'),
(30, 1, 11, 'Expired', '2026-07-22 16:15:00', '2026-07-22 16:15:21', '2026-07-23 16:06:59', 15, '2026-07-23 15:34:42'),
(32, 3, 10, 'Expired', '2026-07-22 20:31:23', '2026-07-22 20:35:18', '2026-07-23 16:14:17', 15, '2026-07-23 15:23:03'),
(33, 9, 12, 'Expired', '2026-07-22 20:31:26', '2026-07-22 20:35:54', '2026-07-23 16:06:51', 15, '2026-07-23 15:49:58'),
(34, 9, 13, 'Rejected', '2026-07-22 20:40:45', NULL, NULL, 15, NULL),
(35, 9, 13, 'Expired', '2026-07-22 20:41:26', '2026-07-22 20:41:42', '2026-07-23 16:06:51', 15, '2026-07-23 15:49:50'),
(36, 3, 10, 'Expired', '2026-07-23 13:27:21', '2026-07-23 13:27:47', '2026-07-23 16:14:17', 15, '2026-07-23 15:24:02'),
(39, 10, 10, 'Expired', '2026-07-23 14:47:20', '2026-07-23 14:48:23', '2026-07-23 16:08:09', 15, '2026-07-23 14:53:49'),
(41, 10, 12, 'Expired', '2026-07-23 14:53:45', '2026-07-23 14:58:08', '2026-07-23 16:08:09', 2, '2026-07-23 14:58:24'),
(43, 10, 12, 'Expired', '2026-07-23 15:05:43', '2026-07-23 15:06:05', '2026-07-23 16:08:09', 1, '2026-07-23 15:06:21'),
(44, 10, 10, 'Rejected', '2026-07-23 15:14:51', NULL, NULL, 15, NULL),
(45, 10, 3, 'Rejected', '2026-07-23 15:15:01', NULL, NULL, 15, NULL),
(46, 10, 3, 'Expired', '2026-07-23 15:21:34', '2026-07-23 15:30:54', '2026-07-23 16:08:09', 1, '2026-07-23 15:31:21'),
(47, 10, 10, 'Expired', '2026-07-23 15:30:30', '2026-07-23 15:30:58', '2026-07-23 16:08:09', 1, '2026-07-23 15:31:17'),
(48, 10, 2, 'Expired', '2026-07-23 15:31:24', '2026-07-23 15:31:35', '2026-07-23 16:08:09', 2, '2026-07-23 15:32:57'),
(49, 10, 12, 'Rejected', '2026-07-23 15:33:25', NULL, NULL, 15, NULL),
(50, 9, 10, 'Expired', '2026-07-23 15:51:30', '2026-07-23 15:51:45', '2026-07-23 16:06:51', 1, '2026-07-23 15:52:37'),
(51, 9, 10, 'Expired', '2026-07-23 15:57:17', '2026-07-23 15:57:28', '2026-07-23 16:06:51', 1, '2026-07-23 15:57:38'),
(52, 3, 10, 'Expired', '2026-07-23 16:48:27', '2026-07-23 16:53:20', '2026-07-23 17:41:38', 1, '2026-07-23 16:53:23'),
(53, 3, 3, 'Rejected', '2026-07-23 16:53:02', NULL, NULL, 15, NULL),
(54, 3, 2, 'Expired', '2026-07-23 16:56:17', '2026-07-23 16:56:27', '2026-07-23 17:41:38', 1, '2026-07-23 16:59:03'),
(55, 3, 12, 'Expired', '2026-07-23 17:01:23', '2026-07-23 17:01:30', '2026-07-23 17:41:38', 1, '2026-07-23 17:01:35'),
(56, 3, 12, 'Expired', '2026-07-23 17:24:04', '2026-07-23 17:24:14', '2026-07-23 17:41:38', 1, NULL),
(57, 3, 10, 'Expired', '2026-07-23 18:06:54', '2026-07-23 18:07:07', '2026-07-23 18:55:12', 1, '2026-07-23 18:07:14'),
(58, 3, 10, 'Rejected', '2026-07-23 18:10:20', NULL, NULL, 15, NULL),
(59, 3, 3, 'Rejected', '2026-07-23 18:10:35', NULL, NULL, 15, NULL),
(60, 3, 10, 'Expired', '2026-07-23 18:51:38', '2026-07-23 18:52:09', '2026-07-23 18:55:12', 1, '2026-07-23 18:52:14'),
(61, 9, 13, 'Expired', '2026-07-23 18:51:46', '2026-07-23 18:52:19', '2026-07-23 18:57:57', 1, '2026-07-23 18:52:27'),
(62, 3, 12, 'Rejected', '2026-07-23 18:52:32', NULL, NULL, 15, NULL),
(63, 9, 10, 'Rejected', '2026-07-23 18:52:54', NULL, NULL, 15, NULL),
(64, 9, 10, 'Rejected', '2026-07-23 18:53:12', NULL, NULL, 15, NULL),
(65, 3, 12, 'Expired', '2026-07-23 18:54:47', '2026-07-23 18:54:56', '2026-07-23 18:55:12', 2, '2026-07-23 18:55:02'),
(66, 9, 10, 'Rejected', '2026-07-23 18:56:13', NULL, NULL, 15, NULL),
(67, 3, 10, 'Rejected', '2026-07-23 18:56:13', NULL, NULL, 15, NULL),
(68, 9, 13, 'Expired', '2026-07-23 18:56:45', NULL, '2026-07-23 18:57:57', 15, NULL),
(69, 3, 13, 'Expired', '2026-07-23 18:56:47', NULL, '2026-07-23 18:57:15', 15, NULL),
(70, 9, 10, 'Expired', '2026-07-23 18:56:51', NULL, '2026-07-23 18:57:57', 15, NULL),
(71, 3, 10, 'Expired', '2026-07-23 18:56:53', NULL, '2026-07-23 18:57:15', 15, NULL),
(72, 9, 12, 'Expired', '2026-07-23 18:56:55', '2026-07-23 18:57:35', '2026-07-23 18:57:57', 2, NULL),
(73, 3, 12, 'Expired', '2026-07-23 18:56:57', NULL, '2026-07-23 18:57:15', 15, NULL),
(74, 10, 14, 'Expired', '2026-07-24 17:17:43', '2026-07-24 17:18:00', '2026-07-24 17:22:16', 1, '2026-07-24 17:19:19'),
(75, 10, 15, 'Rejected', '2026-07-24 17:21:19', NULL, NULL, 15, NULL),
(76, 10, 15, 'Expired', '2026-07-24 17:21:52', '2026-07-24 17:21:59', '2026-07-24 17:22:16', 2, NULL),
(77, 10, 12, 'Expired', '2026-07-24 17:40:25', '2026-07-24 17:40:42', '2026-07-24 18:14:56', 1, '2026-07-24 17:53:16'),
(78, 9, 19, 'Expired', '2026-07-24 18:22:19', '2026-07-24 18:22:27', '2026-07-24 18:32:33', 1, '2026-07-24 18:22:30'),
(79, 9, 14, 'Rejected', '2026-07-24 18:31:12', NULL, NULL, 15, NULL),
(80, 9, 15, 'Expired', '2026-07-24 18:31:55', '2026-07-24 18:32:20', '2026-07-24 18:32:33', 1, NULL),
(81, 9, 14, 'Expired', '2026-07-24 18:34:16', '2026-07-24 18:34:25', '2026-07-24 19:13:39', 1, '2026-07-24 18:34:31'),
(82, 9, 15, 'Expired', '2026-07-24 19:01:54', '2026-07-24 19:02:25', '2026-07-24 19:13:39', 10000000, '2026-07-24 19:02:38'),
(83, 11, 19, 'Expired', '2026-07-24 19:55:53', '2026-07-24 19:57:00', '2026-07-24 20:55:47', 1, '2026-07-24 19:57:07'),
(84, 11, 19, 'Expired', '2026-07-24 21:20:13', '2026-07-24 21:21:48', '2026-07-24 21:28:42', 2, '2026-07-24 21:21:53'),
(85, 11, 10, 'Expired', '2026-07-24 21:27:59', '2026-07-24 21:28:09', '2026-07-24 21:28:42', 5, '2026-07-24 21:28:22'),
(86, 11, 19, 'Expired', '2026-07-24 21:29:57', '2026-07-24 21:30:05', '2026-07-24 21:30:13', 5, NULL),
(87, 11, 16, 'Expired', '2026-07-24 21:31:04', '2026-07-24 21:31:12', '2026-07-24 21:31:43', 2, '2026-07-24 21:31:15'),
(88, 11, 19, 'Rejected', '2026-07-24 21:32:23', NULL, NULL, 15, NULL),
(89, 1, 19, 'Expired', '2026-07-25 13:17:49', '2026-07-25 13:18:17', '2026-07-25 13:36:41', 1, '2026-07-25 13:18:25'),
(90, 1, 12, 'Expired', '2026-07-25 13:23:30', '2026-07-25 13:23:57', '2026-07-25 13:36:41', 1, '2026-07-25 13:24:01'),
(91, 1, 19, 'Expired', '2026-07-25 13:24:19', '2026-07-25 13:24:26', '2026-07-25 13:36:41', 2, '2026-07-25 13:24:30'),
(92, 1, 19, 'Expired', '2026-07-25 13:27:54', '2026-07-25 13:28:32', '2026-07-25 13:36:41', 2, '2026-07-25 13:28:37'),
(93, 1, 12, 'Rejected', '2026-07-25 13:31:44', NULL, NULL, 15, NULL),
(94, 1, 19, 'Expired', '2026-07-25 13:31:52', '2026-07-25 13:32:02', '2026-07-25 13:36:41', 3, '2026-07-25 13:32:08'),
(95, 5, 19, 'Expired', '2026-07-25 13:51:12', '2026-07-25 13:51:30', '2026-07-25 13:57:15', 5, '2026-07-25 13:52:10'),
(96, 1, 10, 'Approved', '2026-07-25 13:55:43', '2026-07-25 13:57:45', '2026-07-25 13:59:49', 2, '2026-07-25 13:57:49'),
(97, 12, 19, 'Rejected', '2026-07-25 14:17:33', NULL, NULL, 15, NULL),
(98, 12, 10, 'Expired', '2026-07-25 14:18:25', '2026-07-25 14:18:32', '2026-07-25 14:39:16', 5, '2026-07-25 14:18:44'),
(99, 12, 14, 'Expired', '2026-07-25 14:33:57', NULL, '2026-07-25 14:39:16', 15, NULL),
(100, 12, 11, 'Expired', '2026-07-25 14:34:03', NULL, '2026-07-25 14:39:16', 15, NULL),
(101, 12, 10, 'Expired', '2026-07-25 14:34:25', '2026-07-25 14:34:41', '2026-07-25 14:39:16', 5, '2026-07-25 14:34:56'),
(102, 12, 10, 'Expired', '2026-07-25 14:39:38', '2026-07-25 14:39:53', '2026-07-25 14:40:52', 5, '2026-07-25 14:39:59'),
(103, 12, 10, 'Expired', '2026-07-25 14:42:37', '2026-07-25 14:42:43', '2026-07-25 15:34:09', 4, '2026-07-25 14:43:05'),
(104, 12, 14, 'Expired', '2026-07-25 14:46:26', '2026-07-25 14:46:38', '2026-07-25 15:34:09', 5, '2026-07-25 14:46:54'),
(105, 12, 10, 'Expired', '2026-07-25 14:50:36', '2026-07-25 14:50:45', '2026-07-25 15:34:09', 5, '2026-07-25 14:51:00'),
(106, 12, 15, 'Expired', '2026-07-25 14:54:19', '2026-07-25 14:54:28', '2026-07-25 15:34:09', 4, '2026-07-25 14:54:40'),
(107, 12, 10, 'Expired', '2026-07-25 15:22:54', NULL, '2026-07-25 15:34:09', 15, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `renewal_requests`
--

CREATE TABLE `renewal_requests` (
  `id` int(11) NOT NULL,
  `member_id` int(11) NOT NULL,
  `membership_plan_id` int(11) NOT NULL,
  `shift` varchar(50) DEFAULT 'Morning',
  `payment_id` varchar(150) NOT NULL,
  `status` enum('Pending','Approved','Rejected') DEFAULT 'Pending',
  `requested_at` datetime DEFAULT current_timestamp(),
  `approved_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `work_shifts`
--

CREATE TABLE `work_shifts` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `work_shifts`
--

INSERT INTO `work_shifts` (`id`, `name`, `start_time`, `end_time`) VALUES
(1, 'Morning', '09:00:00', '14:00:00'),
(2, 'Evening', '14:00:00', '21:00:00'),
(3, 'Full Day', '08:00:00', '21:25:00'),
(22, 'Night', '20:00:00', '23:30:00');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `admin_login_logs`
--
ALTER TABLE `admin_login_logs`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `ebooks`
--
ALTER TABLE `ebooks`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `idx_category_title` (`category_id`,`title`),
  ADD KEY `category_id` (`category_id`);

--
-- Indexes for table `hold_requests`
--
ALTER TABLE `hold_requests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `member_id` (`member_id`),
  ADD KEY `physical_book_id` (`physical_book_id`);

--
-- Indexes for table `lendings`
--
ALTER TABLE `lendings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `member_id` (`member_id`),
  ADD KEY `physical_book_id` (`physical_book_id`);

--
-- Indexes for table `members`
--
ALTER TABLE `members`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `membership_id` (`membership_id`),
  ADD UNIQUE KEY `idx_aadhar_no` (`aadhar_no`),
  ADD UNIQUE KEY `idx_payment_id` (`payment_id`),
  ADD UNIQUE KEY `idx_mobile` (`mobile`);

--
-- Indexes for table `membership_history`
--
ALTER TABLE `membership_history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `member_id` (`member_id`);

--
-- Indexes for table `membership_plans`
--
ALTER TABLE `membership_plans`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `member_login_logs`
--
ALTER TABLE `member_login_logs`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `physical_books`
--
ALTER TABLE `physical_books`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `book_code` (`book_code`);

--
-- Indexes for table `print_requests`
--
ALTER TABLE `print_requests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `member_id` (`member_id`),
  ADD KEY `ebook_id` (`ebook_id`);

--
-- Indexes for table `reading_requests`
--
ALTER TABLE `reading_requests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `member_id` (`member_id`),
  ADD KEY `ebook_id` (`ebook_id`);

--
-- Indexes for table `renewal_requests`
--
ALTER TABLE `renewal_requests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `member_id` (`member_id`),
  ADD KEY `membership_plan_id` (`membership_plan_id`);

--
-- Indexes for table `work_shifts`
--
ALTER TABLE `work_shifts`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admins`
--
ALTER TABLE `admins`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `admin_login_logs`
--
ALTER TABLE `admin_login_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT for table `ebooks`
--
ALTER TABLE `ebooks`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `hold_requests`
--
ALTER TABLE `hold_requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `lendings`
--
ALTER TABLE `lendings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `members`
--
ALTER TABLE `members`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `membership_history`
--
ALTER TABLE `membership_history`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `membership_plans`
--
ALTER TABLE `membership_plans`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `member_login_logs`
--
ALTER TABLE `member_login_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=75;

--
-- AUTO_INCREMENT for table `physical_books`
--
ALTER TABLE `physical_books`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `print_requests`
--
ALTER TABLE `print_requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `reading_requests`
--
ALTER TABLE `reading_requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=108;

--
-- AUTO_INCREMENT for table `renewal_requests`
--
ALTER TABLE `renewal_requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `work_shifts`
--
ALTER TABLE `work_shifts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `ebooks`
--
ALTER TABLE `ebooks`
  ADD CONSTRAINT `ebooks_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `hold_requests`
--
ALTER TABLE `hold_requests`
  ADD CONSTRAINT `hold_requests_ibfk_1` FOREIGN KEY (`member_id`) REFERENCES `members` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `hold_requests_ibfk_2` FOREIGN KEY (`physical_book_id`) REFERENCES `physical_books` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `lendings`
--
ALTER TABLE `lendings`
  ADD CONSTRAINT `lendings_ibfk_1` FOREIGN KEY (`member_id`) REFERENCES `members` (`id`),
  ADD CONSTRAINT `lendings_ibfk_2` FOREIGN KEY (`physical_book_id`) REFERENCES `physical_books` (`id`);

--
-- Constraints for table `membership_history`
--
ALTER TABLE `membership_history`
  ADD CONSTRAINT `membership_history_ibfk_1` FOREIGN KEY (`member_id`) REFERENCES `members` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `print_requests`
--
ALTER TABLE `print_requests`
  ADD CONSTRAINT `print_requests_ibfk_1` FOREIGN KEY (`member_id`) REFERENCES `members` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `print_requests_ibfk_2` FOREIGN KEY (`ebook_id`) REFERENCES `ebooks` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `reading_requests`
--
ALTER TABLE `reading_requests`
  ADD CONSTRAINT `reading_requests_ibfk_1` FOREIGN KEY (`member_id`) REFERENCES `members` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `reading_requests_ibfk_2` FOREIGN KEY (`ebook_id`) REFERENCES `ebooks` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `renewal_requests`
--
ALTER TABLE `renewal_requests`
  ADD CONSTRAINT `renewal_requests_ibfk_1` FOREIGN KEY (`member_id`) REFERENCES `members` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `renewal_requests_ibfk_2` FOREIGN KEY (`membership_plan_id`) REFERENCES `membership_plans` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
