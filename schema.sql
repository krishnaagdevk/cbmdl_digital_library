-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Jul 21, 2026 at 11:21 AM
-- Server version: 5.7.23-23
-- PHP Version: 8.1.34

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `svpsm27m_cbmdl`
--

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

CREATE TABLE `admins` (
  `id` int(11) NOT NULL,
  `username` varchar(60) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`id`, `username`, `password`) VALUES
(1, 'admin', 'abhinav');

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
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
(10, 'Engineering', '2026-07-19 12:49:18');

-- --------------------------------------------------------

--
-- Table structure for table `ebooks`
--

CREATE TABLE `ebooks` (
  `id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `keywords` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `pdf_file` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
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
(6, 10, 'Mechanical Engineering', 'me, btech', 'book_6a5def5334a65.pdf', '2026-07-20 09:50:11');

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
  `transaction_id` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `returned_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `lendings`
--

INSERT INTO `lendings` (`id`, `member_id`, `physical_book_id`, `lent_at`, `due_date`, `transaction_id`, `returned_at`) VALUES
(1, 1, 1, '2026-07-13 02:40:00', '2026-07-20', 'TXN00123', '2026-07-19 12:47:05'),
(2, 1, 1, '2026-07-19 18:28:00', '2026-07-27', 'TXN0012356', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `members`
--

CREATE TABLE `members` (
  `id` int(11) NOT NULL,
  `membership_id` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `guardian_name` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `mobile` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `aadhar_no` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
  `duration` enum('Yearly','Half Yearly','Quarterly','Monthly','Daily') COLLATE utf8mb4_unicode_ci NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `membership_fee` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payment_id` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `members`
--

INSERT INTO `members` (`id`, `membership_id`, `name`, `guardian_name`, `mobile`, `password`, `email`, `address`, `aadhar_no`, `duration`, `start_date`, `end_date`, `membership_fee`, `payment_id`, `created_at`) VALUES
(1, 'CBMDL1', 'Abhinav Garg', 'Pradeep Kumar', '9457421088', '9457421088', 'er.abhinavgarg17@gmail.com', 'TP Nagar', 'xxxx xxxx 1234', 'Yearly', '2026-07-13', '2027-07-16', '', 'TXN1234', '2026-07-13 02:30:34'),
(2, 'CBMDL2', 'Vivek Kumar', 'Ramesh Singh', '9058721088', '9058721088', 'rameshkumar11@gmail.com', 'H.No. 1187, Cariappa Road, Meerut Cantt., Meerut.', 'xxxx xxxx 1894', 'Half Yearly', '2026-07-21', '2027-01-21', '', 'TXN123422', '2026-07-21 05:33:51');

-- --------------------------------------------------------

--
-- Table structure for table `physical_books`
--

CREATE TABLE `physical_books` (
  `id` int(11) NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `book_code` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `author` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `publisher` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `physical_books`
--

INSERT INTO `physical_books` (`id`, `title`, `book_code`, `price`, `author`, `publisher`, `created_at`) VALUES
(1, 'UPSC Prelims', 'CBMDLB001', 875.00, 'Abhinav Author', 'Arihant Publication', '2026-07-13 02:40:25');

-- --------------------------------------------------------

--
-- Table structure for table `print_requests`
--

CREATE TABLE `print_requests` (
  `id` int(11) NOT NULL,
  `member_id` int(11) NOT NULL,
  `ebook_id` int(11) NOT NULL,
  `pages` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` enum('Pending','Completed') COLLATE utf8mb4_unicode_ci DEFAULT 'Pending',
  `requested_at` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `print_requests`
--

INSERT INTO `print_requests` (`id`, `member_id`, `ebook_id`, `pages`, `status`, `requested_at`) VALUES
(1, 1, 2, '1-2', 'Completed', '2026-07-19 07:49:08'),
(2, 1, 3, '1 to 8', 'Completed', '2026-07-19 19:03:35');

-- --------------------------------------------------------

--
-- Table structure for table `reading_requests`
--

CREATE TABLE `reading_requests` (
  `id` int(11) NOT NULL,
  `member_id` int(11) NOT NULL,
  `ebook_id` int(11) NOT NULL,
  `status` enum('Pending','Approved','Rejected','Expired') COLLATE utf8mb4_unicode_ci DEFAULT 'Pending',
  `requested_at` datetime DEFAULT CURRENT_TIMESTAMP,
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
(9, 1, 4, 'Approved', '2026-07-21 09:14:57', '2026-07-21 09:15:13', '2026-07-21 09:20:13');

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
  ADD KEY `category_id` (`category_id`);

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
  ADD UNIQUE KEY `mobile` (`mobile`);

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `ebooks`
--
ALTER TABLE `ebooks`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `lendings`
--
ALTER TABLE `lendings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `members`
--
ALTER TABLE `members`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `physical_books`
--
ALTER TABLE `physical_books`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `print_requests`
--
ALTER TABLE `print_requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `reading_requests`
--
ALTER TABLE `reading_requests`
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
