-- CCMS sanitized demonstration SQL dump
-- Demo login for both accounts: Demo@12345
-- Change these demo credentials after importing if this database will be used beyond testing.
-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 11, 2026 at 10:39 AM
-- Server version: 10.11.16-MariaDB
-- PHP Version: 8.3.36

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `ccms_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `cases`
--

CREATE TABLE `cases` (
  `case_id` int(10) UNSIGNED NOT NULL,
  `case_number` varchar(50) NOT NULL,
  `title` varchar(200) NOT NULL,
  `case_type` varchar(100) NOT NULL,
  `client_id` int(10) UNSIGNED NOT NULL,
  `advocate_name` varchar(150) NOT NULL,
  `court_name` varchar(200) NOT NULL,
  `filing_date` date NOT NULL,
  `status` enum('Pending','In Progress','Disposed','On Hold') NOT NULL DEFAULT 'Pending',
  `priority` enum('Low','Medium','High') NOT NULL DEFAULT 'Medium',
  `description` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `cases`
--

INSERT INTO `cases` (`case_id`, `case_number`, `title`, `case_type`, `client_id`, `advocate_name`, `court_name`, `filing_date`, `status`, `priority`, `description`, `created_at`) VALUES
(1, 'CCMS/2026/001', 'Property Dispute - Demo Client One', 'Civil', 1, 'Demo Advocate One', 'District Court, Jodhpur', '2026-11-05', 'Pending', 'Medium', 'Fictional test case for CCMS functionality.', '2026-11-05 09:46:57'),
(2, 'CCMS/2026/002', 'Commercial Rent Dispute', 'Civil', 3, 'Demo Advocate Two', 'District Court, Jodhpur', '2026-02-12', 'In Progress', 'High', 'Dispute concerning unpaid commercial property rent.', '2026-11-10 06:19:19'),
(3, 'CCMS/2026/003', 'Family Maintenance Petition', 'Family', 4, 'Demo Advocate Three', 'Family Court, Jodhpur', '2026-03-05', 'Pending', 'Medium', 'Petition seeking maintenance under applicable family law.', '2026-11-10 06:19:19'),
(4, 'CCMS/2026/004', 'Cheque Bounce Matter', 'Criminal', 5, 'Demo Advocate Four', 'JMFC Court, Jodhpur', '2026-04-18', 'In Progress', 'High', 'Proceedings relating to dishonour of a cheque.', '2026-11-10 06:19:19'),
(5, 'CCMS/2026/005', 'Consumer Compensation Claim', 'Consumer', 6, 'Demo Advocate Five', 'District Consumer Commission, Jodhpur', '2026-05-22', 'Pending', 'Low', 'Consumer complaint seeking compensation for defective services.', '2026-11-10 06:19:19'),
(6, 'CCMS/2026/006', 'Employment Contract Dispute', 'Civil', 7, 'Demo Advocate Six', 'District Court, Jodhpur', '2026-06-14', 'On Hold', 'Medium', 'Dispute concerning terms and obligations of an employment contract.', '2026-11-10 06:19:19'),
(7, 'CCMS/2026/007', 'Property Inheritance Dispute', 'Civil', 8, 'Demo Advocate Seven', 'District Court, Jodhpur', '2026-07-09', 'Disposed', 'Low', 'Dispute concerning inheritance and distribution of property.', '2026-11-10 06:19:19');

-- --------------------------------------------------------

--
-- Table structure for table `clients`
--

CREATE TABLE `clients` (
  `client_id` int(10) UNSIGNED NOT NULL,
  `full_name` varchar(150) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `email` varchar(150) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `status` enum('Active','Inactive') NOT NULL DEFAULT 'Active',
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `clients`
--

INSERT INTO `clients` (`client_id`, `full_name`, `phone`, `email`, `address`, `status`, `created_at`) VALUES
(1, 'Demo Client One', '0000000001', 'client1@example.com', 'Demo Address 1', 'Active', '2026-11-05 09:42:02'),
(2, 'Demo Client Two', '0000000002', 'client2@example.com', 'Demo Address 2', 'Active', '2026-11-10 06:10:51'),
(3, 'Demo Client Three', '0000000003', 'client3@example.com', 'Demo Address 3', 'Active', '2026-11-10 06:18:07'),
(4, 'Demo Client Four', '0000000004', 'client4@example.com', 'Demo Address 4', 'Active', '2026-11-10 06:18:07'),
(5, 'Demo Client Five', '0000000005', 'client5@example.com', 'Demo Address 5', 'Active', '2026-11-10 06:18:07'),
(6, 'Demo Client Six', '0000000006', 'client6@example.com', 'Demo Address 6', 'Inactive', '2026-11-10 06:18:07'),
(7, 'Demo Client Seven', '0000000007', 'client7@example.com', 'Demo Address 7', 'Active', '2026-11-10 06:18:07'),
(8, 'Demo Client Eight', '0000000008', 'client8@example.com', 'Demo Address 8', 'Active', '2026-11-10 06:18:07');

-- --------------------------------------------------------

--
-- Table structure for table `documents`
--

CREATE TABLE `documents` (
  `document_id` int(10) UNSIGNED NOT NULL,
  `case_id` int(10) UNSIGNED NOT NULL,
  `uploaded_by` int(10) UNSIGNED NOT NULL,
  `document_name` varchar(255) NOT NULL,
  `stored_filename` varchar(255) NOT NULL,
  `file_path` varchar(500) NOT NULL,
  `file_type` varchar(100) NOT NULL DEFAULT 'application/pdf',
  `uploaded_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `documents`
--

-- No document rows included; add demo files through the application after importing.

-- --------------------------------------------------------

--
-- Table structure for table `hearings`
--

CREATE TABLE `hearings` (
  `hearing_id` int(10) UNSIGNED NOT NULL,
  `case_id` int(10) UNSIGNED NOT NULL,
  `hearing_date` date NOT NULL,
  `hearing_time` time DEFAULT NULL,
  `courtroom` varchar(100) DEFAULT NULL,
  `purpose` varchar(255) NOT NULL,
  `outcome` text DEFAULT NULL,
  `next_hearing_date` date DEFAULT NULL,
  `remarks` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `hearings`
--

INSERT INTO `hearings` (`hearing_id`, `case_id`, `hearing_date`, `hearing_time`, `courtroom`, `purpose`, `outcome`, `next_hearing_date`, `remarks`) VALUES
(1, 1, '2026-11-12', '10:30:00', 'Courtroom 4', 'Initial hearing for property dispute', NULL, '2026-11-26', 'Both parties to submit relevant property documents.'),
(2, 2, '2026-11-15', '11:00:00', 'Courtroom 2', 'Arguments on commercial rent dispute', NULL, '2026-11-29', 'Legal notices and rent records to be presented.'),
(3, 3, '2026-11-18', '12:00:00', 'Family Court 1', 'Maintenance petition hearing', NULL, '2026-12-02', 'Petitioner to submit income-related documents.'),
(4, 4, '2026-11-20', '10:00:00', 'JMFC Court 3', 'Evidence hearing in cheque bounce matter', NULL, '2026-12-04', 'Original cheque and bank return memo required.'),
(5, 5, '2026-11-23', '11:30:00', 'Consumer Court 1', 'Consumer complaint admission hearing', NULL, '2026-12-07', 'Respondent notice to be considered.'),
(6, 6, '2026-11-26', '12:30:00', 'Courtroom 5', 'Review of employment contract dispute', NULL, NULL, 'Proceedings currently on hold.'),
(7, 7, '2026-08-14', '10:00:00', 'Courtroom 1', 'Final hearing in inheritance dispute', 'Matter disposed after final order.', NULL, 'Case proceedings completed.');

-- --------------------------------------------------------

--
-- Table structure for table `ip_login_attempts`
--

CREATE TABLE `ip_login_attempts` (
  `ip_address` varchar(45) NOT NULL,
  `failed_attempts` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `last_attempt_at` datetime NOT NULL,
  `locked_until` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `login_attempts`
--

CREATE TABLE `login_attempts` (
  `attempt_id` bigint(20) UNSIGNED NOT NULL,
  `email` varchar(150) NOT NULL,
  `ip_address` varchar(45) NOT NULL,
  `failed_attempts` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `last_attempt_at` datetime NOT NULL,
  `locked_until` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `login_attempts`
--

-- Login-attempt history is intentionally empty in the demo database.

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(10) UNSIGNED NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('Admin','Staff') NOT NULL DEFAULT 'Staff',
  `status` enum('Active','Inactive') NOT NULL DEFAULT 'Active',
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `full_name`, `email`, `password`, `role`, `status`, `created_at`) VALUES
(1, 'Demo Administrator', 'admin@example.com', '$2y$12$70tuQawdhBWfcqy4AoO1p.wJpTRcpSALQM6a2QCkDIqUT4bZSyzCi', 'Admin', 'Active', '2026-11-04 15:10:29'),
(2, 'Demo Staff', 'staff@example.com', '$2y$12$70tuQawdhBWfcqy4AoO1p.wJpTRcpSALQM6a2QCkDIqUT4bZSyzCi', 'Staff', 'Active', '2026-11-09 12:23:33');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `cases`
--
ALTER TABLE `cases`
  ADD PRIMARY KEY (`case_id`),
  ADD UNIQUE KEY `case_number` (`case_number`),
  ADD KEY `idx_case_client` (`client_id`),
  ADD KEY `idx_case_status` (`status`);

--
-- Indexes for table `clients`
--
ALTER TABLE `clients`
  ADD PRIMARY KEY (`client_id`),
  ADD UNIQUE KEY `unique_client_email` (`email`),
  ADD KEY `idx_client_name` (`full_name`),
  ADD KEY `idx_client_phone` (`phone`);

--
-- Indexes for table `documents`
--
ALTER TABLE `documents`
  ADD PRIMARY KEY (`document_id`),
  ADD UNIQUE KEY `stored_filename` (`stored_filename`),
  ADD KEY `idx_document_case` (`case_id`),
  ADD KEY `idx_document_uploader` (`uploaded_by`);

--
-- Indexes for table `hearings`
--
ALTER TABLE `hearings`
  ADD PRIMARY KEY (`hearing_id`),
  ADD KEY `idx_hearing_case` (`case_id`),
  ADD KEY `idx_hearing_date` (`hearing_date`);

--
-- Indexes for table `ip_login_attempts`
--
ALTER TABLE `ip_login_attempts`
  ADD PRIMARY KEY (`ip_address`),
  ADD KEY `idx_ip_locked_until` (`locked_until`);

--
-- Indexes for table `login_attempts`
--
ALTER TABLE `login_attempts`
  ADD PRIMARY KEY (`attempt_id`),
  ADD UNIQUE KEY `unique_login_identity` (`email`,`ip_address`),
  ADD KEY `idx_locked_until` (`locked_until`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `cases`
--
ALTER TABLE `cases`
  MODIFY `case_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `clients`
--
ALTER TABLE `clients`
  MODIFY `client_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `documents`
--
ALTER TABLE `documents`
  MODIFY `document_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `hearings`
--
ALTER TABLE `hearings`
  MODIFY `hearing_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `login_attempts`
--
ALTER TABLE `login_attempts`
  MODIFY `attempt_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `cases`
--
ALTER TABLE `cases`
  ADD CONSTRAINT `fk_cases_client` FOREIGN KEY (`client_id`) REFERENCES `clients` (`client_id`) ON UPDATE CASCADE;

--
-- Constraints for table `documents`
--
ALTER TABLE `documents`
  ADD CONSTRAINT `fk_documents_case` FOREIGN KEY (`case_id`) REFERENCES `cases` (`case_id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_documents_user` FOREIGN KEY (`uploaded_by`) REFERENCES `users` (`user_id`) ON UPDATE CASCADE;

--
-- Constraints for table `hearings`
--
ALTER TABLE `hearings`
  ADD CONSTRAINT `fk_hearings_case` FOREIGN KEY (`case_id`) REFERENCES `cases` (`case_id`) ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
