-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jul 22, 2026 at 05:33 PM
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
  `password` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`id`, `username`, `password`) VALUES
(1, 'admin', '$2y$10$intEzrKqjx8znpaerVq1JOjnM2ZRhor7PqStfIUGgMqaJ0sZEEP2y');

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
(10, 'Engineering', '2026-07-19 12:49:18'),
(13, 'Computer Science', '2026-07-22 05:16:57'),
(14, 'Physics', '2026-07-22 05:16:57'),
(15, 'Literature', '2026-07-22 05:16:57'),
(16, 'Business', '2026-07-22 05:16:57'),
(17, 'Fiction', '2026-07-22 05:16:57'),
(18, 'Technology', '2026-07-22 05:16:57'),
(19, 'Mathematics', '2026-07-22 05:16:57');

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
(4, 10, 'Computer Science ', 'cse, computer science', 'book_6a5de4d1f1803.pdf', '2026-07-20 09:05:22'),
(5, 10, 'Information Technology', 'IT, btech', 'book_6a5de6adb311f.pdf', '2026-07-20 09:13:17'),
(6, 10, 'Mechanical Engineering', 'me, btech', 'book_6a5def5334a65.pdf', '2026-07-20 09:50:11'),
(10, 8, '1 gb', 'gbh', 'book_6a6023aaafe08.pdf', '2026-07-22 01:58:08'),
(11, 8, 'yyyy', 'aaa', 'book_6a606671122e8.pdf', '2026-07-22 06:42:57'),
(12, 8, 'hhhh', 'sss', 'book_6a6066c59853a.pdf', '2026-07-22 06:44:36'),
(13, 2, 'SI Police Parikshaaa', 'police, up police, SI, government', 'book_6a6097c5582f0.pdf', '2026-07-22 10:13:25');

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
(5, 2, 2, '2026-07-22 15:50:36', '2026-07-21', '786ji', NULL, 0.00, 'None', NULL);

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
  `shift` enum('Both','Morning','Evening') DEFAULT 'Both',
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
(1, 'CBMDL1', 'Abhinav Garg', 'Male', 'Pradeep Kumar', '9457421088', '$2y$10$DZgs2pJISWJqBE8hbpxmK.tfY5CdHke2NxbwBBaBZwT3WvgrSz5rC', 'er.abhinavgarg17@gmail.com', 'TP Nagar', 'xxxx xxxx 1234', 'Yearly', 'Both', '2026-07-13', '2026-07-24', '3500', 'TXN1234', '2026-07-13 02:30:34', 1, 1, 1),
(2, 'CBMDL2', 'Vivek Kumar', 'Male', 'Ramesh Singh', '9058721088', '$2y$10$wF.MYzB.uF41PvhBS5njYexp/th1BVGwzNaUQnDAeOeCqD25WJZxq', 'rameshkumar11@gmail.com', 'H.No. 1187, Cariappa Road, Meerut Cantt., Meerut.', 'xxxx xxxx 1894', 'Half Yearly', 'Evening', '2026-07-21', '2026-07-21', '2200', 'TXN123422', '2026-07-21 05:33:51', 1, 2, 1),
(3, 'CBMDL3', 'Kunal', 'Male', 'Kuldeep', '8218772351', '$2y$10$mZRwB4TtwxEreOdK2Qvtoed9AhNguCxrtt/Ahip33iBE2917wsFTK', '', '111/20 abcd', 'xxxxxxxx1234', 'Yearly', 'Evening', '2026-07-21', '2027-07-21', '3500', 'id-789', '2026-07-21 14:37:46', 1, 1, 1),
(4, 'CBMDL4', 'kkkkk', 'Male', 'uuuuu', '8888888888', '$2y$10$S3j1HzulHXKM6nxPNg1gpOqnnowW3lJCgFc3GzN5Q2rtqcdn4YqI.', '', 'jasflaf', 'xxxxxxxx9999', '', 'Both', '2026-07-21', '2027-07-21', '0', 'id999', '2026-07-21 17:40:08', 1, 2, 1),
(5, 'CBMDLM5', 'Bharti Bansal', 'Female', 'Pawan Bansal', '8791891088', '$2y$10$QcUdqhaBuwEjxuWl793asOUdReq1OiRtmCnl3XzhxQjD3E2aqn/jy', 'bhartibansal18oct@gmail.com', '27 Sant Vihar, Meerut', '123456789123', '', 'Morning', '2026-07-22', '2027-07-22', '0', 'TXN123445', '2026-07-22 13:26:29', 1, 4, 1),
(9, 'CBMDLM9', 'Bharti Garg', 'Female', 'Pawan Bansal', '9411067157', '$2y$10$NjcMfUeM4j.1y0m59qM3HesULaI.XWgRXnrlkaKRfY5U5oORfjoSC', 'bhartibansal18oct@gmail.com', '27 TP Nagar', '123456009125', 'Monthly', 'Evening', '2026-07-22', '2026-08-22', '500', 'TXN1234sdf', '2026-07-22 13:56:43', 1, 4, 1);

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
(5, 'Daily', 'Daily', 50.00);

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
(16, 'Title 12', 'CBMDLB005', '1', 12.00, 'author 12', 'Publisher 12', '2026-07-22 12:16:10');

-- --------------------------------------------------------

--
-- Table structure for table `print_requests`
--

CREATE TABLE `print_requests` (
  `id` int(11) NOT NULL,
  `member_id` int(11) NOT NULL,
  `ebook_id` int(11) NOT NULL,
  `pages` varchar(100) NOT NULL,
  `status` enum('Pending','Completed') DEFAULT 'Pending',
  `requested_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `print_requests`
--

INSERT INTO `print_requests` (`id`, `member_id`, `ebook_id`, `pages`, `status`, `requested_at`) VALUES
(1, 1, 2, '1-2', 'Completed', '2026-07-19 07:49:08'),
(2, 1, 3, '1 to 8', 'Completed', '2026-07-19 19:03:35'),
(3, 3, 5, '8', 'Completed', '2026-07-21 21:12:55'),
(4, 3, 1, '9', 'Completed', '2026-07-21 21:38:51'),
(5, 3, 4, '2', 'Completed', '2026-07-22 10:24:54'),
(6, 3, 4, '1-6', 'Completed', '2026-07-22 12:15:13'),
(7, 1, 10, '3 - 9', 'Completed', '2026-07-22 15:07:42'),
(8, 9, 10, '1 to 5', 'Completed', '2026-07-22 20:43:49');

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
  `expires_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `reading_requests`
--

INSERT INTO `reading_requests` (`id`, `member_id`, `ebook_id`, `status`, `requested_at`, `approved_at`, `expires_at`) VALUES
(1, 1, 2, 'Approved', '2026-07-13 02:44:50', '2026-07-13 02:46:47', '2026-07-13 02:56:47'),
(2, 1, 2, 'Approved', '2026-07-19 07:30:08', '2026-07-19 07:30:34', '2026-07-19 07:32:34'),
(3, 1, 1, 'Approved', '2026-07-19 08:01:29', '2026-07-19 08:01:49', '2026-07-19 08:16:49'),
(4, 1, 2, 'Approved', '2026-07-19 08:08:43', '2026-07-19 08:08:53', '2026-07-19 08:23:53'),
(5, 1, 2, 'Approved', '2026-07-19 09:30:39', '2026-07-19 09:30:51', '2026-07-19 09:40:51'),
(6, 1, 2, 'Approved', '2026-07-19 09:57:00', '2026-07-19 09:57:12', '2026-07-19 14:57:12'),
(7, 1, 3, 'Approved', '2026-07-19 18:53:47', '2026-07-19 18:54:26', '2026-07-19 18:59:26'),
(8, 1, 6, 'Approved', '2026-07-21 09:10:13', '2026-07-21 09:10:23', '2026-07-21 09:20:23'),
(9, 1, 4, 'Approved', '2026-07-21 09:14:57', '2026-07-21 09:15:13', '2026-07-21 09:20:13'),
(10, 1, 3, 'Approved', '2026-07-21 19:46:45', '2026-07-21 19:48:03', '2026-07-21 19:53:03'),
(11, 3, 4, 'Approved', '2026-07-21 21:08:16', '2026-07-21 21:10:45', '2026-07-21 21:20:45'),
(12, 3, 5, 'Rejected', '2026-07-21 21:11:57', NULL, NULL),
(13, 3, 5, 'Approved', '2026-07-21 21:16:46', '2026-07-21 21:37:10', '2026-07-21 21:47:10'),
(14, 3, 6, 'Approved', '2026-07-21 21:17:19', '2026-07-21 21:17:39', '2026-07-21 21:27:39'),
(15, 3, 1, 'Approved', '2026-07-21 21:18:05', '2026-07-21 21:42:19', '2026-07-21 21:52:19'),
(16, 3, 2, 'Approved', '2026-07-21 22:02:23', '2026-07-21 22:02:51', '2026-07-21 22:03:51'),
(17, 3, 5, 'Rejected', '2026-07-21 22:51:18', NULL, NULL),
(18, 3, 4, 'Approved', '2026-07-22 10:20:34', '2026-07-22 10:20:50', '2026-07-22 10:21:50'),
(19, 3, 4, 'Approved', '2026-07-22 10:31:57', '2026-07-22 10:32:32', '2026-07-22 10:33:32'),
(20, 3, 10, 'Approved', '2026-07-22 10:32:12', '2026-07-22 10:35:32', '2026-07-22 10:36:32'),
(21, 3, 5, 'Approved', '2026-07-22 10:35:38', '2026-07-22 10:36:01', '2026-07-22 10:37:01'),
(22, 3, 5, 'Approved', '2026-07-22 10:37:50', '2026-07-22 10:38:10', '2026-07-22 10:39:10'),
(23, 3, 3, 'Rejected', '2026-07-22 10:38:00', NULL, NULL),
(24, 3, 4, 'Approved', '2026-07-22 12:07:53', '2026-07-22 12:08:36', '2026-07-22 12:09:36'),
(25, 3, 10, 'Rejected', '2026-07-22 13:10:35', NULL, NULL),
(26, 1, 10, 'Approved', '2026-07-22 14:15:14', '2026-07-22 14:15:48', '2026-07-22 14:17:48'),
(27, 1, 6, 'Approved', '2026-07-22 14:29:26', '2026-07-22 14:31:22', '2026-07-22 14:32:22'),
(28, 1, 4, 'Approved', '2026-07-22 15:07:56', '2026-07-22 15:09:42', '2026-07-22 15:14:42'),
(29, 1, 5, 'Rejected', '2026-07-22 15:08:10', NULL, NULL),
(30, 1, 11, 'Approved', '2026-07-22 16:15:00', '2026-07-22 16:15:21', '2026-07-22 16:17:21'),
(31, 3, 4, 'Approved', '2026-07-22 18:50:12', '2026-07-22 18:50:28', '2026-07-22 18:51:28'),
(32, 3, 10, 'Approved', '2026-07-22 20:31:23', '2026-07-22 20:35:18', '2026-07-22 20:40:18'),
(33, 9, 12, 'Approved', '2026-07-22 20:31:26', '2026-07-22 20:35:54', '2026-07-22 20:45:54'),
(34, 9, 13, 'Rejected', '2026-07-22 20:40:45', NULL, NULL),
(35, 9, 13, 'Approved', '2026-07-22 20:41:26', '2026-07-22 20:41:42', '2026-07-22 20:49:42');

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
(1, 'Morning', '09:00:00', '12:00:00'),
(2, 'Evening', '16:00:00', '21:00:00'),
(3, 'Both', '08:00:00', '20:00:00');

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
-- Indexes for table `membership_plans`
--
ALTER TABLE `membership_plans`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

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
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `ebooks`
--
ALTER TABLE `ebooks`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `hold_requests`
--
ALTER TABLE `hold_requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `lendings`
--
ALTER TABLE `lendings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `members`
--
ALTER TABLE `members`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `membership_plans`
--
ALTER TABLE `membership_plans`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `physical_books`
--
ALTER TABLE `physical_books`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `print_requests`
--
ALTER TABLE `print_requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `reading_requests`
--
ALTER TABLE `reading_requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=36;

--
-- AUTO_INCREMENT for table `work_shifts`
--
ALTER TABLE `work_shifts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

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
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
