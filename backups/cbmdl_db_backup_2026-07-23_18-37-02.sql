-- ========================================================
-- Cantonment Digital Library (MCB) Database Backup
-- Generated On: 2026-07-23 18:37:02 IST
-- Database Host: localhost via TCP/IP
-- PHP Version: 8.2.12
-- ========================================================

SET FOREIGN_KEY_CHECKS=0;
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;

-- --------------------------------------------------------
-- Table structure for table `admin_login_logs`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `admin_login_logs`;
CREATE TABLE `admin_login_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(60) NOT NULL,
  `ip_address` varchar(45) NOT NULL,
  `user_agent` varchar(255) DEFAULT NULL,
  `status` varchar(30) NOT NULL DEFAULT 'Success',
  `login_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table `admin_login_logs`
INSERT INTO `admin_login_logs` VALUES("1", "admin", "192.168.1.7", "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/109.0.0.0 Safari/537.36", "Success", "2026-07-23 17:11:24");
INSERT INTO `admin_login_logs` VALUES("2", "admin", "::1", "Mozilla/5.0 (Linux; Android 15; Pixel 9) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Mobile Safari/537.36", "Success", "2026-07-23 17:17:22");
INSERT INTO `admin_login_logs` VALUES("3", "admin", "192.168.1.7", "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/109.0.0.0 Safari/537.36", "Success", "2026-07-23 17:58:32");
INSERT INTO `admin_login_logs` VALUES("4", "admin", "192.168.1.7", "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/109.0.0.0 Safari/537.36", "Success", "2026-07-23 18:30:02");

-- --------------------------------------------------------
-- Table structure for table `admins`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `admins`;
CREATE TABLE `admins` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(60) NOT NULL,
  `password` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table `admins`
INSERT INTO `admins` VALUES("1", "admin", "$2y$10$GG5Ae.SxYw09wz/28b50SebtV84lpEi7NKqN7w2kU.UhLCRclGW0O");

-- --------------------------------------------------------
-- Table structure for table `categories`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `categories`;
CREATE TABLE `categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table `categories`
INSERT INTO `categories` VALUES("1", "UPSC", "2026-07-13 07:53:32");
INSERT INTO `categories` VALUES("2", "UP Police", "2026-07-13 07:53:46");
INSERT INTO `categories` VALUES("4", "SSC", "2026-07-14 22:30:05");
INSERT INTO `categories` VALUES("5", "UPTET", "2026-07-14 22:30:24");
INSERT INTO `categories` VALUES("6", "NEET", "2026-07-14 22:30:33");
INSERT INTO `categories` VALUES("7", "IIT", "2026-07-14 22:30:46");
INSERT INTO `categories` VALUES("8", "Bank PO", "2026-07-14 22:31:17");
INSERT INTO `categories` VALUES("9", "Railway", "2026-07-14 22:31:43");
INSERT INTO `categories` VALUES("13", "Computer Science", "2026-07-22 10:46:57");
INSERT INTO `categories` VALUES("14", "Physics", "2026-07-22 10:46:57");
INSERT INTO `categories` VALUES("15", "Literature", "2026-07-22 10:46:57");
INSERT INTO `categories` VALUES("17", "Fiction", "2026-07-22 10:46:57");
INSERT INTO `categories` VALUES("18", "Technology", "2026-07-22 10:46:57");
INSERT INTO `categories` VALUES("19", "Mathematics", "2026-07-22 10:46:57");
INSERT INTO `categories` VALUES("21", "Business", "2026-07-23 13:15:43");

-- --------------------------------------------------------
-- Table structure for table `ebooks`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `ebooks`;
CREATE TABLE `ebooks` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `category_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `keywords` varchar(500) DEFAULT NULL,
  `pdf_file` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_category_title` (`category_id`,`title`),
  KEY `category_id` (`category_id`),
  CONSTRAINT `ebooks_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table `ebooks`
INSERT INTO `ebooks` VALUES("1", "1", "upsc title 1", "pcs, ias, ides", "book_6a544d39ab335.pdf", "2026-07-13 07:58:09");
INSERT INTO `ebooks` VALUES("2", "1", "Title 2", "keywords", "book_6a544e7bc9639.pdf", "2026-07-13 08:03:31");
INSERT INTO `ebooks` VALUES("3", "1", "Prelims", "upsc pre", "book_6a5cc5efcfe49.pdf", "2026-07-19 18:11:19");
INSERT INTO `ebooks` VALUES("10", "8", "1 gb", "gbh", "book_6a61da0f318bd.pdf", "2026-07-22 07:28:08");
INSERT INTO `ebooks` VALUES("11", "8", "yyyy", "aaa", "book_6a606671122e8.pdf", "2026-07-22 12:12:57");
INSERT INTO `ebooks` VALUES("12", "8", "hhhh", "sss", "book_6a6066c59853a.pdf", "2026-07-22 12:14:36");
INSERT INTO `ebooks` VALUES("13", "2", "SI Police Parikshaaa", "vivkek", "book_6a61cc2f740ab.pdf", "2026-07-22 15:43:25");

-- --------------------------------------------------------
-- Table structure for table `hold_requests`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `hold_requests`;
CREATE TABLE `hold_requests` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `member_id` int(11) NOT NULL,
  `physical_book_id` int(11) NOT NULL,
  `requested_at` datetime DEFAULT current_timestamp(),
  `status` enum('Active','Fulfilled','Cancelled') DEFAULT 'Active',
  PRIMARY KEY (`id`),
  KEY `member_id` (`member_id`),
  KEY `physical_book_id` (`physical_book_id`),
  CONSTRAINT `hold_requests_ibfk_1` FOREIGN KEY (`member_id`) REFERENCES `members` (`id`) ON DELETE CASCADE,
  CONSTRAINT `hold_requests_ibfk_2` FOREIGN KEY (`physical_book_id`) REFERENCES `physical_books` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- Table structure for table `lendings`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `lendings`;
CREATE TABLE `lendings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `member_id` int(11) NOT NULL,
  `physical_book_id` int(11) NOT NULL,
  `lent_at` datetime NOT NULL,
  `due_date` date NOT NULL,
  `transaction_id` varchar(150) DEFAULT NULL,
  `returned_at` datetime DEFAULT NULL,
  `fine_amount` decimal(10,2) DEFAULT 0.00,
  `fine_status` enum('None','Outstanding','Paid','Waived') DEFAULT 'None',
  `fine_payment_id` varchar(150) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `member_id` (`member_id`),
  KEY `physical_book_id` (`physical_book_id`),
  CONSTRAINT `lendings_ibfk_1` FOREIGN KEY (`member_id`) REFERENCES `members` (`id`),
  CONSTRAINT `lendings_ibfk_2` FOREIGN KEY (`physical_book_id`) REFERENCES `physical_books` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table `lendings`
INSERT INTO `lendings` VALUES("1", "1", "1", "2026-07-13 02:40:00", "2026-07-20", "TXN00123", "2026-07-19 12:47:05", "0.00", "None", NULL);
INSERT INTO `lendings` VALUES("2", "1", "1", "2026-07-19 18:28:00", "2026-07-27", "TXN0012356", "2026-07-21 19:25:12", "0.00", "None", NULL);
INSERT INTO `lendings` VALUES("3", "3", "3", "2026-07-21 21:46:00", "2026-07-22", "tijojioj", "2026-07-21 22:57:49", "0.00", "None", NULL);
INSERT INTO `lendings` VALUES("4", "1", "1", "2026-07-22 13:54:08", "2026-07-21", "qwe455", "2026-07-22 18:02:14", "0.00", "None", NULL);
INSERT INTO `lendings` VALUES("5", "2", "2", "2026-07-22 15:50:36", "2026-07-21", "786ji", NULL, "0.00", "None", NULL);
INSERT INTO `lendings` VALUES("6", "10", "1", "2026-07-23 14:19:30", "2026-07-23", "aaass", NULL, "0.00", "None", NULL);

-- --------------------------------------------------------
-- Table structure for table `member_login_logs`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `member_login_logs`;
CREATE TABLE `member_login_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `member_id` int(11) DEFAULT NULL,
  `mobile` varchar(20) NOT NULL,
  `member_name` varchar(100) DEFAULT NULL,
  `shift` varchar(50) DEFAULT NULL,
  `ip_address` varchar(45) NOT NULL,
  `user_agent` varchar(255) DEFAULT NULL,
  `status` varchar(50) NOT NULL DEFAULT 'Success',
  `login_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table `member_login_logs`
INSERT INTO `member_login_logs` VALUES("1", "10", "7840887205", "Pradeep kumar", "Morning", "192.168.1.7", "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/109.0.0.0 Safari/537.36", "Success", "2026-07-23 17:11:27");
INSERT INTO `member_login_logs` VALUES("2", "3", "8218772351", "Kunal", "Both", "::1", "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36", "Success", "2026-07-23 18:05:06");

-- --------------------------------------------------------
-- Table structure for table `members`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `members`;
CREATE TABLE `members` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
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
  `approved` tinyint(1) DEFAULT 1,
  PRIMARY KEY (`id`),
  UNIQUE KEY `membership_id` (`membership_id`),
  UNIQUE KEY `idx_aadhar_no` (`aadhar_no`),
  UNIQUE KEY `idx_payment_id` (`payment_id`),
  UNIQUE KEY `idx_mobile` (`mobile`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table `members`
INSERT INTO `members` VALUES("1", "CBMDL1", "Abhinav Garg", "Male", "Pradeep Kumar", "9457421088", "$2y$10$DZgs2pJISWJqBE8hbpxmK.tfY5CdHke2NxbwBBaBZwT3WvgrSz5rC", "er.abhinavgarg17@gmail.com", "TP Nagar", "xxxx xxxx 1234", "Monthly", "Morning", "2026-07-23", "2026-08-23", "500", "TXN1234", "2026-07-13 08:00:34", "1", "4", "1");
INSERT INTO `members` VALUES("2", "CBMDL2", "Vivek Kumar", "Male", "Ramesh Singh", "9058721088", "$2y$10$wF.MYzB.uF41PvhBS5njYexp/th1BVGwzNaUQnDAeOeCqD25WJZxq", "rameshkumar11@gmail.com", "H.No. 1187, Cariappa Road, Meerut Cantt., Meerut.", "xxxx xxxx 1894", "Monthly", "Morning", "2026-08-27", "2026-09-27", "100", "TEST_RENEW_UTR_1784796178", "2026-07-21 11:03:51", "1", "1", "1");
INSERT INTO `members` VALUES("3", "CBMDL3", "Kunal", "Male", "Kuldeep", "8218772351", "$2y$10$mZRwB4TtwxEreOdK2Qvtoed9AhNguCxrtt/Ahip33iBE2917wsFTK", "", "111/20 abcd", "xxxxxxxx1234", "Yearly", "Both", "2026-07-21", "2027-07-21", "3500", "id-789", "2026-07-21 20:07:46", "1", "1", "1");
INSERT INTO `members` VALUES("4", "CBMDL4", "kkkkk", "Male", "uuuuu", "8888888888", "$2y$10$S3j1HzulHXKM6nxPNg1gpOqnnowW3lJCgFc3GzN5Q2rtqcdn4YqI.", "", "jasflaf", "xxxxxxxx9999", "", "Both", "2026-07-21", "2027-07-21", "0", "id999", "2026-07-21 23:10:08", "1", "2", "1");
INSERT INTO `members` VALUES("5", "CBMDLM5", "Bharti Bansal", "Female", "Pawan Bansal", "8791891088", "$2y$10$QcUdqhaBuwEjxuWl793asOUdReq1OiRtmCnl3XzhxQjD3E2aqn/jy", "bhartibansal18oct@gmail.com", "27 Sant Vihar, Meerut", "123456789123", "", "Morning", "2026-07-22", "2027-07-22", "0", "TXN123445", "2026-07-22 18:56:29", "1", "4", "1");
INSERT INTO `members` VALUES("9", "CBMDLM9", "Anju Garg", "Female", "SC Guptaa", "9411067157", "$2y$10$NjcMfUeM4j.1y0m59qM3HesULaI.XWgRXnrlkaKRfY5U5oORfjoSC", "anjugupta0306@gmail.com", "27 TP Nagar", "123456009125", "Monthly", "Evening", "2026-07-22", "2026-08-22", "500", "TXN1234sdf", "2026-07-22 19:26:43", "1", "4", "1");
INSERT INTO `members` VALUES("10", "CBMDLM10", "Pradeep kumar", "Male", "Gupta ji", "7840887205", "$2y$10$KMBLo9ETDN1CwzwVG3fej.Qp7F7OIzBJfD9mRhd.lLN4DjAvKqN5y", "gargpradeep1964@gmail.com", "B.Puri Meerut", "123567889900", "Daily", "Morning", "2026-07-23", "2026-07-24", "50", "pay5689", "2026-07-23 14:09:07", "1", "5", "1");

-- --------------------------------------------------------
-- Table structure for table `membership_history`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `membership_history`;
CREATE TABLE `membership_history` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
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
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `member_id` (`member_id`),
  CONSTRAINT `membership_history_ibfk_1` FOREIGN KEY (`member_id`) REFERENCES `members` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table `membership_history`
INSERT INTO `membership_history` VALUES("1", "1", "CBMDL1", "1", "Yearly", "Yearly", "Both", "2026-07-13", "2026-07-24", "3500.00", "TXN1234", "Initial Joining", "2026-07-13 08:00:34");
INSERT INTO `membership_history` VALUES("2", "2", "CBMDL2", "2", "Half Yearly", "Half Yearly", "Evening", "2026-07-21", "2026-07-21", "2200.00", "TXN123422", "Initial Joining", "2026-07-21 11:03:51");
INSERT INTO `membership_history` VALUES("3", "3", "CBMDL3", "1", "Yearly", "Yearly", "Evening", "2026-07-21", "2027-07-21", "3500.00", "id-789", "Initial Joining", "2026-07-21 20:07:46");
INSERT INTO `membership_history` VALUES("4", "4", "CBMDL4", "2", "Half Yearly", "Yearly", "Both", "2026-07-21", "2027-07-21", "0.00", "id999", "Initial Joining", "2026-07-21 23:10:08");
INSERT INTO `membership_history` VALUES("5", "5", "CBMDLM5", "4", "Monthly", "Yearly", "Morning", "2026-07-22", "2027-07-22", "0.00", "TXN123445", "Initial Joining", "2026-07-22 18:56:29");
INSERT INTO `membership_history` VALUES("6", "9", "CBMDLM9", "4", "Monthly", "Monthly", "Evening", "2026-07-22", "2026-08-22", "500.00", "TXN1234sdf", "Initial Joining", "2026-07-22 19:26:43");
INSERT INTO `membership_history` VALUES("8", "1", "CBMDL1", "4", "Monthly", "Monthly", "Both", "2026-07-23", "2026-08-23", "500.00", "TXN1234", "Renewal", "2026-07-23 12:26:34");
INSERT INTO `membership_history` VALUES("9", "2", "CBMDL2", "4", "Monthly", "Monthly", "Morning", "2026-07-26", "2026-08-26", "500.00", "TXN1234qq", "Renewal", "2026-07-23 13:56:21");
INSERT INTO `membership_history` VALUES("10", "10", "CBMDLM10", "5", "Daily", "Daily", "Morning", "2026-07-23", "2026-07-24", "50.00", "pay5689", "Initial Joining", "2026-07-23 14:09:07");

-- --------------------------------------------------------
-- Table structure for table `membership_plans`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `membership_plans`;
CREATE TABLE `membership_plans` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `duration` varchar(50) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table `membership_plans`
INSERT INTO `membership_plans` VALUES("1", "Yearly", "Yearly", "3500.00");
INSERT INTO `membership_plans` VALUES("2", "Half Yearly", "Half Yearly", "2200.00");
INSERT INTO `membership_plans` VALUES("3", "Quarterly", "Quarterly", "1200.00");
INSERT INTO `membership_plans` VALUES("4", "Monthly", "Monthly", "500.00");
INSERT INTO `membership_plans` VALUES("5", "Daily", "Daily", "50.00");

-- --------------------------------------------------------
-- Table structure for table `physical_books`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `physical_books`;
CREATE TABLE `physical_books` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `book_code` varchar(100) NOT NULL,
  `shelf_number` varchar(100) DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `author` varchar(150) DEFAULT NULL,
  `publisher` varchar(150) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `book_code` (`book_code`)
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table `physical_books`
INSERT INTO `physical_books` VALUES("1", "UPSC Prelims", "CBMDLB001", "1", "875.00", "Abhinav Author", "Arihant Publication", "2026-07-13 08:10:25");
INSERT INTO `physical_books` VALUES("2", "Jee Main", "CBMDLB002", "2", "1855.00", "H.C. Verma", "Arihant Publications", "2026-07-21 19:10:48");
INSERT INTO `physical_books` VALUES("3", "title1", "CBMDLB003", "2", "1000.00", "kunn", "arihant", "2026-07-21 21:45:58");
INSERT INTO `physical_books` VALUES("4", "Introduction to Algorithms", "CBMDLB101", "3", "1899.00", "Thomas H. Cormen", "MIT Press", "2026-07-22 10:46:57");
INSERT INTO `physical_books` VALUES("5", "Concepts of Physics Vol 1", "CBMDLB102", "1", "450.00", "H.C. Verma", "Bharati Bhawan", "2026-07-22 10:46:57");
INSERT INTO `physical_books` VALUES("6", "To Kill a Mockingbird", "CBMDLB103", "2", "399.00", "Harper Lee", "J. B. Lippincott & Co.", "2026-07-22 10:46:57");
INSERT INTO `physical_books` VALUES("7", "The Intelligent Investor", "CBMDLB104", "3", "799.00", "Benjamin Graham", "HarperBusiness", "2026-07-22 10:46:57");
INSERT INTO `physical_books` VALUES("8", "The Great Gatsby", "CBMDLB105", "1", "299.00", "F. Scott Fitzgerald", "Charles Scribner\'s Sons", "2026-07-22 10:46:57");
INSERT INTO `physical_books` VALUES("9", "Clean Code: A Handbook of Agile Software Craftsmanship", "CBMDLB106", "2", "1250.00", "Robert C. Martin", "Prentice Hall", "2026-07-22 10:46:57");
INSERT INTO `physical_books` VALUES("10", "Thomas\' Calculus", "CBMDLB107", "3", "1499.00", "George B. Thomas", "Pearson", "2026-07-22 10:46:57");
INSERT INTO `physical_books` VALUES("11", "Title 11", "CBMDLB004", "1", "111.00", "author 11", "Publisher 11", "2026-07-22 15:41:07");
INSERT INTO `physical_books` VALUES("16", "Title 12", "CBMDLB005", "1", "12.00", "author 12", "Publisher 12", "2026-07-22 17:46:10");

-- --------------------------------------------------------
-- Table structure for table `print_requests`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `print_requests`;
CREATE TABLE `print_requests` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `member_id` int(11) NOT NULL,
  `ebook_id` int(11) NOT NULL,
  `pages` varchar(100) NOT NULL,
  `status` enum('Pending','Completed','Rejected') DEFAULT 'Pending',
  `requested_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `member_id` (`member_id`),
  KEY `ebook_id` (`ebook_id`),
  CONSTRAINT `print_requests_ibfk_1` FOREIGN KEY (`member_id`) REFERENCES `members` (`id`) ON DELETE CASCADE,
  CONSTRAINT `print_requests_ibfk_2` FOREIGN KEY (`ebook_id`) REFERENCES `ebooks` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table `print_requests`
INSERT INTO `print_requests` VALUES("1", "1", "2", "1-2", "Completed", "2026-07-19 07:49:08");
INSERT INTO `print_requests` VALUES("2", "1", "3", "1 to 8", "Completed", "2026-07-19 19:03:35");
INSERT INTO `print_requests` VALUES("4", "3", "1", "9", "Completed", "2026-07-21 21:38:51");
INSERT INTO `print_requests` VALUES("7", "1", "10", "3 - 9", "Completed", "2026-07-22 15:07:42");
INSERT INTO `print_requests` VALUES("8", "9", "10", "1 to 5", "Completed", "2026-07-22 20:43:49");
INSERT INTO `print_requests` VALUES("9", "10", "10", "6", "Completed", "2026-07-23 14:54:10");
INSERT INTO `print_requests` VALUES("11", "10", "12", "4", "Rejected", "2026-07-23 15:00:01");
INSERT INTO `print_requests` VALUES("12", "10", "3", "3", "Completed", "2026-07-23 15:01:00");
INSERT INTO `print_requests` VALUES("13", "3", "10", "3", "Rejected", "2026-07-23 16:58:43");
INSERT INTO `print_requests` VALUES("14", "3", "10", "2", "Rejected", "2026-07-23 18:07:39");
INSERT INTO `print_requests` VALUES("15", "3", "1", "3-10", "Rejected", "2026-07-23 18:10:47");
INSERT INTO `print_requests` VALUES("16", "3", "2", "2-6", "Rejected", "2026-07-23 18:11:00");

-- --------------------------------------------------------
-- Table structure for table `reading_requests`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `reading_requests`;
CREATE TABLE `reading_requests` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `member_id` int(11) NOT NULL,
  `ebook_id` int(11) NOT NULL,
  `status` enum('Pending','Approved','Rejected','Expired') DEFAULT 'Pending',
  `requested_at` datetime DEFAULT current_timestamp(),
  `approved_at` datetime DEFAULT NULL,
  `expires_at` datetime DEFAULT NULL,
  `duration_minutes` int(11) DEFAULT 15,
  `started_reading_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `member_id` (`member_id`),
  KEY `ebook_id` (`ebook_id`),
  CONSTRAINT `reading_requests_ibfk_1` FOREIGN KEY (`member_id`) REFERENCES `members` (`id`) ON DELETE CASCADE,
  CONSTRAINT `reading_requests_ibfk_2` FOREIGN KEY (`ebook_id`) REFERENCES `ebooks` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=60 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table `reading_requests`
INSERT INTO `reading_requests` VALUES("1", "1", "2", "Expired", "2026-07-13 02:44:50", "2026-07-13 02:46:47", "2026-07-23 16:06:59", "15", NULL);
INSERT INTO `reading_requests` VALUES("2", "1", "2", "Expired", "2026-07-19 07:30:08", "2026-07-19 07:30:34", "2026-07-23 16:06:59", "15", NULL);
INSERT INTO `reading_requests` VALUES("3", "1", "1", "Expired", "2026-07-19 08:01:29", "2026-07-19 08:01:49", "2026-07-23 16:06:59", "15", NULL);
INSERT INTO `reading_requests` VALUES("4", "1", "2", "Expired", "2026-07-19 08:08:43", "2026-07-19 08:08:53", "2026-07-23 16:06:59", "15", NULL);
INSERT INTO `reading_requests` VALUES("5", "1", "2", "Expired", "2026-07-19 09:30:39", "2026-07-19 09:30:51", "2026-07-23 16:06:59", "15", NULL);
INSERT INTO `reading_requests` VALUES("6", "1", "2", "Expired", "2026-07-19 09:57:00", "2026-07-19 09:57:12", "2026-07-23 16:06:59", "15", NULL);
INSERT INTO `reading_requests` VALUES("7", "1", "3", "Expired", "2026-07-19 18:53:47", "2026-07-19 18:54:26", "2026-07-23 16:06:59", "15", NULL);
INSERT INTO `reading_requests` VALUES("10", "1", "3", "Expired", "2026-07-21 19:46:45", "2026-07-21 19:48:03", "2026-07-23 16:06:59", "15", NULL);
INSERT INTO `reading_requests` VALUES("15", "3", "1", "Expired", "2026-07-21 21:18:05", "2026-07-21 21:42:19", "2026-07-23 16:14:17", "15", "2026-07-23 15:24:07");
INSERT INTO `reading_requests` VALUES("16", "3", "2", "Expired", "2026-07-21 22:02:23", "2026-07-21 22:02:51", "2026-07-23 16:14:17", "15", "2026-07-23 15:22:58");
INSERT INTO `reading_requests` VALUES("20", "3", "10", "Expired", "2026-07-22 10:32:12", "2026-07-22 10:35:32", "2026-07-23 16:14:17", "15", "2026-07-23 15:24:00");
INSERT INTO `reading_requests` VALUES("23", "3", "3", "Rejected", "2026-07-22 10:38:00", NULL, NULL, "15", NULL);
INSERT INTO `reading_requests` VALUES("25", "3", "10", "Rejected", "2026-07-22 13:10:35", NULL, NULL, "15", NULL);
INSERT INTO `reading_requests` VALUES("26", "1", "10", "Expired", "2026-07-22 14:15:14", "2026-07-22 14:15:48", "2026-07-23 16:06:59", "15", "2026-07-23 16:01:50");
INSERT INTO `reading_requests` VALUES("30", "1", "11", "Expired", "2026-07-22 16:15:00", "2026-07-22 16:15:21", "2026-07-23 16:06:59", "15", "2026-07-23 15:34:42");
INSERT INTO `reading_requests` VALUES("32", "3", "10", "Expired", "2026-07-22 20:31:23", "2026-07-22 20:35:18", "2026-07-23 16:14:17", "15", "2026-07-23 15:23:03");
INSERT INTO `reading_requests` VALUES("33", "9", "12", "Expired", "2026-07-22 20:31:26", "2026-07-22 20:35:54", "2026-07-23 16:06:51", "15", "2026-07-23 15:49:58");
INSERT INTO `reading_requests` VALUES("34", "9", "13", "Rejected", "2026-07-22 20:40:45", NULL, NULL, "15", NULL);
INSERT INTO `reading_requests` VALUES("35", "9", "13", "Expired", "2026-07-22 20:41:26", "2026-07-22 20:41:42", "2026-07-23 16:06:51", "15", "2026-07-23 15:49:50");
INSERT INTO `reading_requests` VALUES("36", "3", "10", "Expired", "2026-07-23 13:27:21", "2026-07-23 13:27:47", "2026-07-23 16:14:17", "15", "2026-07-23 15:24:02");
INSERT INTO `reading_requests` VALUES("39", "10", "10", "Expired", "2026-07-23 14:47:20", "2026-07-23 14:48:23", "2026-07-23 16:08:09", "15", "2026-07-23 14:53:49");
INSERT INTO `reading_requests` VALUES("41", "10", "12", "Expired", "2026-07-23 14:53:45", "2026-07-23 14:58:08", "2026-07-23 16:08:09", "2", "2026-07-23 14:58:24");
INSERT INTO `reading_requests` VALUES("43", "10", "12", "Expired", "2026-07-23 15:05:43", "2026-07-23 15:06:05", "2026-07-23 16:08:09", "1", "2026-07-23 15:06:21");
INSERT INTO `reading_requests` VALUES("44", "10", "10", "Rejected", "2026-07-23 15:14:51", NULL, NULL, "15", NULL);
INSERT INTO `reading_requests` VALUES("45", "10", "3", "Rejected", "2026-07-23 15:15:01", NULL, NULL, "15", NULL);
INSERT INTO `reading_requests` VALUES("46", "10", "3", "Expired", "2026-07-23 15:21:34", "2026-07-23 15:30:54", "2026-07-23 16:08:09", "1", "2026-07-23 15:31:21");
INSERT INTO `reading_requests` VALUES("47", "10", "10", "Expired", "2026-07-23 15:30:30", "2026-07-23 15:30:58", "2026-07-23 16:08:09", "1", "2026-07-23 15:31:17");
INSERT INTO `reading_requests` VALUES("48", "10", "2", "Expired", "2026-07-23 15:31:24", "2026-07-23 15:31:35", "2026-07-23 16:08:09", "2", "2026-07-23 15:32:57");
INSERT INTO `reading_requests` VALUES("49", "10", "12", "Rejected", "2026-07-23 15:33:25", NULL, NULL, "15", NULL);
INSERT INTO `reading_requests` VALUES("50", "9", "10", "Expired", "2026-07-23 15:51:30", "2026-07-23 15:51:45", "2026-07-23 16:06:51", "1", "2026-07-23 15:52:37");
INSERT INTO `reading_requests` VALUES("51", "9", "10", "Expired", "2026-07-23 15:57:17", "2026-07-23 15:57:28", "2026-07-23 16:06:51", "1", "2026-07-23 15:57:38");
INSERT INTO `reading_requests` VALUES("52", "3", "10", "Expired", "2026-07-23 16:48:27", "2026-07-23 16:53:20", "2026-07-23 17:41:38", "1", "2026-07-23 16:53:23");
INSERT INTO `reading_requests` VALUES("53", "3", "3", "Rejected", "2026-07-23 16:53:02", NULL, NULL, "15", NULL);
INSERT INTO `reading_requests` VALUES("54", "3", "2", "Expired", "2026-07-23 16:56:17", "2026-07-23 16:56:27", "2026-07-23 17:41:38", "1", "2026-07-23 16:59:03");
INSERT INTO `reading_requests` VALUES("55", "3", "12", "Expired", "2026-07-23 17:01:23", "2026-07-23 17:01:30", "2026-07-23 17:41:38", "1", "2026-07-23 17:01:35");
INSERT INTO `reading_requests` VALUES("56", "3", "12", "Expired", "2026-07-23 17:24:04", "2026-07-23 17:24:14", "2026-07-23 17:41:38", "1", NULL);
INSERT INTO `reading_requests` VALUES("57", "3", "10", "Approved", "2026-07-23 18:06:54", "2026-07-23 18:07:07", "2026-07-23 18:08:14", "1", "2026-07-23 18:07:14");
INSERT INTO `reading_requests` VALUES("58", "3", "10", "Rejected", "2026-07-23 18:10:20", NULL, NULL, "15", NULL);
INSERT INTO `reading_requests` VALUES("59", "3", "3", "Rejected", "2026-07-23 18:10:35", NULL, NULL, "15", NULL);

-- --------------------------------------------------------
-- Table structure for table `renewal_requests`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `renewal_requests`;
CREATE TABLE `renewal_requests` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `member_id` int(11) NOT NULL,
  `membership_plan_id` int(11) NOT NULL,
  `shift` varchar(50) DEFAULT 'Morning',
  `payment_id` varchar(150) NOT NULL,
  `status` enum('Pending','Approved','Rejected') DEFAULT 'Pending',
  `requested_at` datetime DEFAULT current_timestamp(),
  `approved_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `member_id` (`member_id`),
  KEY `membership_plan_id` (`membership_plan_id`),
  CONSTRAINT `renewal_requests_ibfk_1` FOREIGN KEY (`member_id`) REFERENCES `members` (`id`) ON DELETE CASCADE,
  CONSTRAINT `renewal_requests_ibfk_2` FOREIGN KEY (`membership_plan_id`) REFERENCES `membership_plans` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for table `work_shifts`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `work_shifts`;
CREATE TABLE `work_shifts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table `work_shifts`
INSERT INTO `work_shifts` VALUES("1", "Morning", "09:00:00", "18:00:00");
INSERT INTO `work_shifts` VALUES("2", "Evening", "14:00:00", "21:00:00");
INSERT INTO `work_shifts` VALUES("3", "Both", "08:00:00", "20:00:00");

SET FOREIGN_KEY_CHECKS=1;
COMMIT;
-- End of Backup
