-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Aug 03, 2025 at 02:28 PM
-- Server version: 10.4.24-MariaDB
-- PHP Version: 8.1.6

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `vaycamco_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `activity_logs`
--

CREATE TABLE `activity_logs` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `action` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `table_name` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `record_id` int(11) DEFAULT NULL,
  `old_values` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`old_values`)),
  `new_values` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`new_values`)),
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `activity_logs`
--

INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `table_name`, `record_id`, `old_values`, `new_values`, `ip_address`, `user_agent`, `created_at`) VALUES
(1, 1, 'login', 'User logged in successfully', 'users', 1, NULL, NULL, '127.0.0.1', NULL, '2025-07-22 11:20:17'),
(2, 1, 'create', 'Created new customer: Nguyễn Văn A', 'customers', 1, NULL, NULL, '127.0.0.1', NULL, '2025-07-22 11:20:17'),
(3, 1, 'create', 'Created new contract: HD001', 'contracts', 1, NULL, NULL, '127.0.0.1', NULL, '2025-07-22 11:20:17'),
(4, 1, 'login', 'User logged in successfully', NULL, NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36', '2025-07-24 06:36:35'),
(5, 1, 'login', 'User logged in successfully', NULL, NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36', '2025-07-25 06:47:39'),
(6, 1, 'login', 'User logged in successfully', NULL, NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36', '2025-07-25 11:04:54'),
(7, 1, 'login', 'User logged in successfully', NULL, NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36', '2025-07-26 12:56:02'),
(8, 1, 'login', 'User logged in successfully', NULL, NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36', '2025-07-27 06:12:59'),
(9, 1, 'login', 'User logged in successfully', NULL, NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36', '2025-07-28 04:04:58'),
(10, 1, 'login', 'User logged in successfully', NULL, NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36', '2025-07-29 09:01:30'),
(11, 1, 'login', 'User logged in successfully', NULL, NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36', '2025-07-30 05:51:22'),
(12, 1, 'login', 'User logged in successfully', NULL, NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36', '2025-08-01 07:32:50'),
(13, 2, 'login', 'User logged in successfully', NULL, NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36', '2025-08-02 07:06:43'),
(14, 1, 'login', 'User logged in successfully', NULL, NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36', '2025-08-02 07:24:07'),
(15, 6, 'login', 'User logged in successfully', NULL, NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36', '2025-08-02 07:30:57'),
(16, 1, 'login', 'User logged in successfully', NULL, NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36', '2025-08-02 09:05:35'),
(17, 8, 'login', 'User logged in successfully', NULL, NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36', '2025-08-02 09:18:39'),
(18, 7, 'login', 'User logged in successfully', NULL, NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36', '2025-08-02 09:19:29'),
(19, 8, 'login', 'User logged in successfully', NULL, NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36', '2025-08-02 09:22:12'),
(20, 1, 'login', 'User logged in successfully', NULL, NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36', '2025-08-02 15:14:01'),
(21, 7, 'login', 'User logged in successfully', NULL, NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36', '2025-08-02 16:24:23'),
(22, 6, 'login', 'User logged in successfully', NULL, NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36', '2025-08-02 16:26:37'),
(23, 8, 'login', 'User logged in successfully', NULL, NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36', '2025-08-03 05:12:33'),
(24, 7, 'login', 'User logged in successfully', NULL, NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36', '2025-08-03 05:27:54'),
(25, 6, 'login', 'User logged in successfully', NULL, NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36', '2025-08-03 05:29:29');

-- --------------------------------------------------------

--
-- Table structure for table `assets`
--

CREATE TABLE `assets` (
  `id` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `category_id` int(11) DEFAULT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `condition_status` enum('excellent','good','fair','poor') COLLATE utf8mb4_unicode_ci DEFAULT 'good',
  `estimated_value` decimal(15,2) NOT NULL,
  `pawn_value` decimal(15,2) NOT NULL,
  `images` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`images`)),
  `status` enum('pawned','redeemed','sold','lost') COLLATE utf8mb4_unicode_ci DEFAULT 'pawned',
  `notes` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `quantity` int(11) DEFAULT 1 COMMENT 'Số lượng tài sản',
  `license_plate` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Biển kiểm soát xe',
  `frame_number` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Số khung xe',
  `engine_number` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Số máy xe',
  `registration_number` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Giấy tờ đăng ký số hiệu',
  `registration_date` date DEFAULT NULL COMMENT 'Ngày cấp giấy tờ đăng ký',
  `asset_type` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT 'vehicle' COMMENT 'Loại tài sản: vehicle, property, etc.',
  `asset_value` decimal(15,2) DEFAULT NULL COMMENT 'Giá trị tài sản',
  `asset_description` text COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Mô tả chi tiết tài sản'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `assets`
--

INSERT INTO `assets` (`id`, `customer_id`, `category_id`, `name`, `description`, `condition_status`, `estimated_value`, `pawn_value`, `images`, `status`, `notes`, `created_by`, `created_at`, `updated_at`, `quantity`, `license_plate`, `frame_number`, `engine_number`, `registration_number`, `registration_date`, `asset_type`, `asset_value`, `asset_description`) VALUES
(5, 3, 2, 'vf', NULL, 'excellent', '10000000.00', '8000000.00', NULL, 'pawned', NULL, 1, '2025-08-02 16:11:09', '2025-08-02 16:11:09', 1, NULL, NULL, NULL, NULL, NULL, 'vehicle', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `asset_categories`
--

CREATE TABLE `asset_categories` (
  `id` int(11) NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` enum('active','inactive') COLLATE utf8mb4_unicode_ci DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `asset_categories`
--

INSERT INTO `asset_categories` (`id`, `name`, `description`, `status`, `created_at`, `updated_at`) VALUES
(1, 'Điện tử', 'Điện thoại, máy tính, tivi, v.v.', 'active', '2025-07-22 11:20:16', '2025-07-22 11:20:16'),
(2, 'Xe cộ', 'Xe máy, ô tô, xe đạp', 'active', '2025-07-22 11:20:16', '2025-07-22 11:20:16'),
(3, 'Trang sức', 'Vàng, bạc, đá quý', 'active', '2025-07-22 11:20:16', '2025-07-22 11:20:16'),
(4, 'Bất động sản', 'Sổ đỏ, sổ hồng', 'active', '2025-07-22 11:20:16', '2025-07-22 11:20:16'),
(5, 'Khác', 'Các loại tài sản khác', 'active', '2025-07-22 11:20:16', '2025-07-22 11:20:16');

-- --------------------------------------------------------

--
-- Table structure for table `consult_histories`
--

CREATE TABLE `consult_histories` (
  `id` int(11) NOT NULL,
  `contract_id` int(11) NOT NULL,
  `result` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `content` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `appointment_date` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `contracts`
--

CREATE TABLE `contracts` (
  `id` int(11) NOT NULL,
  `contract_code` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `customer_id` int(11) NOT NULL,
  `asset_id` int(11) DEFAULT NULL,
  `amount` decimal(15,2) NOT NULL,
  `interest_rate` decimal(5,2) DEFAULT 0.00,
  `interest_rate_id` int(11) NOT NULL,
  `monthly_rate` decimal(5,2) NOT NULL,
  `daily_rate` decimal(5,4) NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `grace_period_days` int(11) DEFAULT 0,
  `late_fee_rate` decimal(5,2) DEFAULT 0.00,
  `total_interest` decimal(15,2) DEFAULT 0.00,
  `total_paid` decimal(15,2) DEFAULT 0.00,
  `remaining_balance` decimal(15,2) DEFAULT 0.00,
  `status` enum('active','overdue','warning','closed','defaulted') COLLATE utf8mb4_unicode_ci DEFAULT 'active',
  `notes` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `detailed_reason` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `contracts`
--

INSERT INTO `contracts` (`id`, `contract_code`, `customer_id`, `asset_id`, `amount`, `interest_rate`, `interest_rate_id`, `monthly_rate`, `daily_rate`, `start_date`, `end_date`, `grace_period_days`, `late_fee_rate`, `total_interest`, `total_paid`, `remaining_balance`, `status`, `notes`, `created_by`, `created_at`, `updated_at`, `detailed_reason`) VALUES
(3, 'HD003', 3, NULL, '28000000.00', '0.00', 2, '3.00', '0.0670', '2024-01-20', '2024-10-20', 0, '0.00', '5040000.00', '0.00', '0.00', 'active', NULL, 1, '2025-07-22 11:20:17', '2025-07-25 08:48:23', ''),
(4, 'HD004', 4, NULL, '24000000.00', '0.00', 2, '2.00', '0.0670', '2024-01-25', '2024-05-25', 0, '0.00', '1920000.00', '0.00', '0.00', 'active', NULL, 1, '2025-07-22 11:20:17', '2025-07-22 11:20:17', ''),
(6, 'HD005', 5, NULL, '22222222222.00', '0.00', 1, '2.50', '0.0830', '2025-07-25', '2025-08-25', 5, '0.50', '0.00', '0.00', '0.00', 'active', NULL, NULL, '2025-07-25 08:51:32', '2025-07-25 08:54:02', '');

-- --------------------------------------------------------

--
-- Table structure for table `contract_details`
--

CREATE TABLE `contract_details` (
  `id` int(11) NOT NULL,
  `contract_id` int(11) NOT NULL,
  `material_insurance` enum('yes','no') COLLATE utf8mb4_unicode_ci DEFAULT 'no',
  `hospital_insurance` enum('yes','no') COLLATE utf8mb4_unicode_ci DEFAULT 'no',
  `insurance_status` enum('pending','sent','active','expired') COLLATE utf8mb4_unicode_ci DEFAULT 'pending',
  `digital_signature` enum('yes','no') COLLATE utf8mb4_unicode_ci DEFAULT 'no',
  `store_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `store_code` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `has_location_tracking` enum('yes','no') COLLATE utf8mb4_unicode_ci DEFAULT 'no',
  `location_tracking_type` enum('real_time','periodic','on_demand') COLLATE utf8mb4_unicode_ci DEFAULT 'real_time',
  `tima_customer_link` enum('linked','unlinked') COLLATE utf8mb4_unicode_ci DEFAULT 'unlinked',
  `tima_agent_code` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ndt_customer_link` enum('linked','unlinked') COLLATE utf8mb4_unicode_ci DEFAULT 'unlinked',
  `re_data_enabled` enum('yes','no') COLLATE utf8mb4_unicode_ci DEFAULT 'no',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `contract_details`
--

INSERT INTO `contract_details` (`id`, `contract_id`, `material_insurance`, `hospital_insurance`, `insurance_status`, `digital_signature`, `store_name`, `store_code`, `has_location_tracking`, `location_tracking_type`, `tima_customer_link`, `tima_agent_code`, `ndt_customer_link`, `re_data_enabled`, `created_at`, `updated_at`) VALUES
(9, 1, 'no', 'no', 'pending', 'no', 'PDV Bắc Giang', 'PDV_BG_001', 'yes', 'real_time', 'linked', 'TIMA', 'linked', 'yes', '2025-07-28 04:02:08', '2025-07-28 04:02:08'),
(10, 2, 'no', 'no', 'pending', 'no', 'PDV Hà Nội', 'PDV_HN_001', 'yes', 'real_time', 'linked', 'TIMA', 'linked', 'yes', '2025-07-28 04:02:08', '2025-07-28 04:02:08'),
(11, 3, 'yes', 'no', 'sent', 'yes', 'PDV TP.HCM', 'PDV_HCM_001', 'yes', 'periodic', 'linked', 'TIMA', 'unlinked', 'yes', '2025-07-28 04:02:08', '2025-07-28 04:02:08'),
(12, 4, 'no', 'yes', 'active', 'no', 'PDV Đà Nẵng', 'PDV_DN_001', 'no', 'on_demand', 'unlinked', 'TIMA', 'linked', 'no', '2025-07-28 04:02:08', '2025-07-28 04:02:08');

-- --------------------------------------------------------

--
-- Table structure for table `customers`
--

CREATE TABLE `customers` (
  `id` int(11) NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `id_number` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `id_type` enum('cccd','cmnd','passport') COLLATE utf8mb4_unicode_ci DEFAULT 'cccd',
  `date_of_birth` date DEFAULT NULL,
  `gender` enum('male','female','other') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `occupation` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `monthly_income` decimal(15,2) DEFAULT NULL,
  `status` enum('active','inactive','blacklisted') COLLATE utf8mb4_unicode_ci DEFAULT 'active',
  `notes` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `tax_code` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `verified` tinyint(1) NOT NULL,
  `cif` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `loan_date` date NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `customers`
--

INSERT INTO `customers` (`id`, `name`, `phone`, `email`, `address`, `id_number`, `id_type`, `date_of_birth`, `gender`, `occupation`, `monthly_income`, `status`, `notes`, `created_by`, `created_at`, `updated_at`, `tax_code`, `verified`, `cif`, `loan_date`) VALUES
(2, 'Trần Thị B', '0901234568', 'tranthib@email.com', '456 Đường XYZ, Quận 2, TP.HCM', '123456789013', 'cccd', '1985-05-15', 'female', 'Kinh doanh', '25000000.00', 'active', NULL, 1, '2025-07-22 11:20:17', '2025-07-30 08:39:21', '', 0, 'CIF005', '2025-07-30'),
(3, 'Lê Minh C', '0901234569', 'leminhc@email.com', '789 Đường DEF, Quận 3, TP.HCM', '123456789014', 'cccd', '1988-12-20', 'male', 'Kỹ sư', '20000000.00', 'active', NULL, 1, '2025-07-22 11:20:17', '2025-07-30 08:39:18', '', 0, 'CIF004', '2025-07-30'),
(4, 'Phạm Văn D', '0901234570', 'phamvand@email.com', '321 Đường GHI, Quận 4, TP.HCM', '123456789015', 'cccd', '1992-08-10', 'male', 'Giáo viên', '12000000.00', 'active', NULL, 1, '2025-07-22 11:20:17', '2025-07-30 08:39:15', '', 0, 'CIF003\\', '2025-07-30'),
(5, 'fdafafaf fwefwefwf', '0852217455', 'k40modgame@gmail.com', NULL, NULL, 'cccd', NULL, NULL, NULL, NULL, 'active', NULL, NULL, '2025-07-25 08:41:27', '2025-07-30 08:39:08', '', 0, 'CIF00', '2025-07-30');

-- --------------------------------------------------------

--
-- Table structure for table `debt_collection_activities`
--

CREATE TABLE `debt_collection_activities` (
  `id` int(11) NOT NULL,
  `contract_id` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `activity_type` enum('call','visit','sms','email','legal_notice','other') COLLATE utf8mb4_unicode_ci NOT NULL,
  `activity_date` date NOT NULL,
  `activity_time` time DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `result` enum('successful','unsuccessful','pending','rescheduled') COLLATE utf8mb4_unicode_ci DEFAULT 'pending',
  `next_action` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `next_action_date` date DEFAULT NULL,
  `created_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `debt_collection_roles`
--

CREATE TABLE `debt_collection_roles` (
  `id` int(11) NOT NULL,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `approval_limit` decimal(15,2) NOT NULL DEFAULT 0.00,
  `status` enum('active','inactive') COLLATE utf8mb4_unicode_ci DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `debt_collection_roles`
--

INSERT INTO `debt_collection_roles` (`id`, `name`, `description`, `approval_limit`, `status`, `created_at`, `updated_at`) VALUES
(1, 'TP Xử lý nợ', 'Trưởng phòng xử lý nợ - phê duyệt miễn giảm từ 0-50 triệu', '50000000.00', 'active', '2025-07-30 05:39:25', '2025-07-30 05:39:25'),
(2, 'GĐ Xử lý nợ', 'Giám đốc xử lý nợ - phê duyệt miễn giảm từ 50-100 triệu', '100000000.00', 'active', '2025-07-30 05:39:25', '2025-07-30 05:39:25'),
(3, 'TGĐ', 'Tổng giám đốc - phê duyệt miễn giảm trên 100 triệu', '999999999999.00', 'active', '2025-07-30 05:39:25', '2025-07-30 05:39:25');

-- --------------------------------------------------------

--
-- Table structure for table `debt_collection_users`
--

CREATE TABLE `debt_collection_users` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `role_id` int(11) NOT NULL,
  `status` enum('active','inactive') COLLATE utf8mb4_unicode_ci DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `debt_collection_users`
--

INSERT INTO `debt_collection_users` (`id`, `user_id`, `role_id`, `status`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 'active', '2025-07-30 05:39:25', '2025-07-30 05:39:25'),
(2, 1, 2, 'active', '2025-07-30 05:39:25', '2025-07-30 05:39:25'),
(3, 1, 3, 'active', '2025-07-30 05:39:25', '2025-07-30 05:39:25');

-- --------------------------------------------------------

--
-- Table structure for table `departments`
--

CREATE TABLE `departments` (
  `id` int(11) NOT NULL,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `code` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` enum('active','inactive') COLLATE utf8mb4_unicode_ci DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `departments`
--

INSERT INTO `departments` (`id`, `name`, `code`, `description`, `status`, `created_at`, `updated_at`) VALUES
(1, 'Phòng Miền Bắc', 'MB', 'Phòng xử lý nợ khu vực Miền Bắc', 'active', '2025-07-30 08:30:00', '2025-07-30 08:30:00'),
(2, 'Phòng Miền Nam', 'MN', 'Phòng xử lý nợ khu vực Miền Nam', 'active', '2025-07-30 08:30:00', '2025-07-30 08:30:00'),
(3, 'Phòng Miền Trung', 'MT', 'Phòng xử lý nợ khu vực Miền Trung', 'active', '2025-07-30 08:30:00', '2025-07-30 08:30:00');

-- --------------------------------------------------------

--
-- Table structure for table `disbursement_history`
--

CREATE TABLE `disbursement_history` (
  `id` int(11) NOT NULL,
  `contract_id` int(11) NOT NULL,
  `application_id` int(11) NOT NULL,
  `amount` decimal(15,2) NOT NULL,
  `disbursement_method` enum('cash','bank_transfer','check') NOT NULL DEFAULT 'bank_transfer',
  `bank_account` varchar(50) DEFAULT NULL,
  `bank_name` varchar(100) DEFAULT NULL,
  `reference_number` varchar(100) DEFAULT NULL,
  `disbursed_by` int(11) NOT NULL,
  `approved_by` int(11) NOT NULL,
  `disbursed_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `notes` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `disbursement_history`
--

INSERT INTO `disbursement_history` (`id`, `contract_id`, `application_id`, `amount`, `disbursement_method`, `bank_account`, `bank_name`, `reference_number`, `disbursed_by`, `approved_by`, `disbursed_at`, `notes`) VALUES
(1, 14, 14, '51000000.00', 'bank_transfer', '0852217488', 'MBBANK', '424234242', 6, 6, '2025-08-03 06:24:41', 'hello');

-- --------------------------------------------------------

--
-- Table structure for table `documents`
--

CREATE TABLE `documents` (
  `id` int(11) NOT NULL,
  `contract_id` int(11) NOT NULL,
  `category` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `file_path` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `file_type` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `electronic_contracts`
--

CREATE TABLE `electronic_contracts` (
  `id` int(11) NOT NULL,
  `contract_code` varchar(50) NOT NULL,
  `application_id` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `asset_id` int(11) DEFAULT NULL,
  `loan_amount` decimal(15,2) NOT NULL,
  `approved_amount` decimal(15,2) NOT NULL,
  `interest_rate_id` int(11) NOT NULL,
  `monthly_rate` decimal(5,2) NOT NULL,
  `daily_rate` decimal(5,2) NOT NULL,
  `loan_term_months` int(11) NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `status` enum('draft','active','disbursed','completed','cancelled','overdue') NOT NULL DEFAULT 'draft',
  `disbursement_status` enum('pending','disbursed','cancelled') DEFAULT 'pending',
  `disbursed_amount` decimal(15,2) DEFAULT NULL,
  `disbursed_date` datetime DEFAULT NULL,
  `disbursed_by` int(11) DEFAULT NULL,
  `remaining_balance` decimal(15,2) DEFAULT NULL,
  `total_paid` decimal(15,2) DEFAULT 0.00,
  `next_payment_date` date DEFAULT NULL,
  `monthly_payment` decimal(15,2) DEFAULT NULL,
  `customer_signature` text DEFAULT NULL,
  `company_signature` text DEFAULT NULL,
  `signed_date` datetime DEFAULT NULL,
  `created_by` int(11) NOT NULL,
  `approved_by` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `electronic_contracts`
--

INSERT INTO `electronic_contracts` (`id`, `contract_code`, `application_id`, `customer_id`, `asset_id`, `loan_amount`, `approved_amount`, `interest_rate_id`, `monthly_rate`, `daily_rate`, `loan_term_months`, `start_date`, `end_date`, `status`, `disbursement_status`, `disbursed_amount`, `disbursed_date`, `disbursed_by`, `remaining_balance`, `total_paid`, `next_payment_date`, `monthly_payment`, `customer_signature`, `company_signature`, `signed_date`, `created_by`, `approved_by`, `created_at`, `updated_at`) VALUES
(14, 'CT2025083247', 14, 5, 5, '51000000.00', '51000000.00', 5, '1.00', '0.05', 12, '2025-08-03', '2026-08-03', 'active', 'disbursed', '51000000.00', '2025-08-03 13:24:41', 6, NULL, '0.00', NULL, NULL, 'Nguyễn tuấn thành', 'nguyễn văn tuấn', '2025-08-03 13:22:41', 1, 1, '2025-08-03 06:21:09', '2025-08-03 06:24:41');

-- --------------------------------------------------------

--
-- Table structure for table `insurance_config`
--

CREATE TABLE `insurance_config` (
  `id` int(11) NOT NULL,
  `insurance_type` enum('health','life','vehicle') NOT NULL,
  `rate` decimal(5,4) NOT NULL DEFAULT 0.0000,
  `min_amount` decimal(15,2) DEFAULT 0.00,
  `max_amount` decimal(15,2) DEFAULT 999999999999.00,
  `default_months` int(11) DEFAULT 12,
  `is_active` tinyint(1) DEFAULT 1,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `insurance_config`
--

INSERT INTO `insurance_config` (`id`, `insurance_type`, `rate`, `min_amount`, `max_amount`, `default_months`, `is_active`, `description`, `created_at`, `updated_at`) VALUES
(1, 'health', '0.0125', '0.00', '999999999999.00', 3, 1, 'Bảo hiểm sức khỏe người vay tin dụng - 1.25%', '2025-07-30 11:14:16', '2025-07-30 11:14:16'),
(2, 'life', '0.0200', '0.00', '999999999999.00', 12, 1, 'Bảo hiểm tử cấp năm viên - 2%', '2025-07-30 11:14:16', '2025-07-30 11:14:16'),
(3, 'vehicle', '0.0075', '0.00', '999999999999.00', 3, 1, 'Bảo hiểm tử nguyên xe ô tô - 0.75%', '2025-07-30 11:14:16', '2025-07-30 11:14:16');

-- --------------------------------------------------------

--
-- Table structure for table `interest_rates`
--

CREATE TABLE `interest_rates` (
  `id` int(11) NOT NULL,
  `loan_type` enum('short_term','medium_term','long_term','penalty_standard','penalty_heavy') COLLATE utf8mb4_unicode_ci NOT NULL,
  `min_amount` decimal(15,2) NOT NULL,
  `max_amount` decimal(15,2) NOT NULL,
  `monthly_rate` decimal(5,2) NOT NULL,
  `daily_rate` decimal(5,4) NOT NULL,
  `grace_period_days` int(11) DEFAULT 0,
  `late_fee_rate` decimal(5,2) DEFAULT 0.00,
  `status` enum('active','inactive') COLLATE utf8mb4_unicode_ci DEFAULT 'active',
  `effective_from` date NOT NULL,
  `effective_to` date DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `description` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `interest_rates`
--

INSERT INTO `interest_rates` (`id`, `loan_type`, `min_amount`, `max_amount`, `monthly_rate`, `daily_rate`, `grace_period_days`, `late_fee_rate`, `status`, `effective_from`, `effective_to`, `created_by`, `created_at`, `updated_at`, `description`) VALUES
(1, 'short_term', '1000000.00', '10000000.00', '2.50', '0.0830', 5, '0.50', 'active', '2025-07-22', NULL, NULL, '2025-07-22 11:20:16', '2025-07-22 11:20:16', ''),
(2, 'medium_term', '10000000.00', '50000000.00', '2.00', '0.0670', 7, '0.30', 'active', '2025-07-22', NULL, NULL, '2025-07-22 11:20:16', '2025-07-22 11:20:16', ''),
(3, 'long_term', '50000000.00', '1000000000.00', '1.50', '0.0500', 10, '0.20', 'active', '2025-07-22', NULL, NULL, '2025-07-22 11:20:16', '2025-07-22 11:20:16', ''),
(4, 'medium_term', '222.00', '2222.00', '22.00', '9.9999', 0, '0.00', 'active', '2025-07-25', NULL, NULL, '2025-07-25 11:19:22', '2025-07-25 11:20:20', '22dd'),
(5, 'short_term', '1111.00', '1111111.00', '1.00', '9.9999', 0, '0.00', 'active', '2025-07-25', NULL, NULL, '2025-07-25 11:20:39', '2025-07-25 11:20:39', '111'),
(6, 'long_term', '2222.00', '222.00', '222.00', '9.9999', 0, '0.00', 'active', '2025-07-25', NULL, NULL, '2025-07-25 11:21:17', '2025-07-25 11:21:17', '2fe'),
(7, '', '3333.00', '333.00', '333.00', '9.9999', 0, '0.00', 'active', '2025-07-25', NULL, NULL, '2025-07-25 11:22:58', '2025-07-25 11:22:58', '3333'),
(8, 'medium_term', '222.00', '2222.00', '999.99', '9.9999', 0, '0.00', 'active', '2025-07-25', NULL, NULL, '2025-07-25 11:25:55', '2025-07-25 11:25:55', '22'),
(10, 'penalty_standard', '222.00', '2222.00', '999.99', '9.9999', 0, '0.00', 'active', '2025-07-25', NULL, NULL, '2025-07-25 11:33:05', '2025-07-25 11:33:05', '222'),
(11, 'penalty_standard', '44444444.00', '9999999999999.99', '444.00', '9.9999', 0, '0.00', 'active', '2025-07-25', NULL, NULL, '2025-07-25 11:35:57', '2025-07-25 11:35:57', '44'),
(12, 'short_term', '100000.00', '0.00', '19.00', '0.0000', 0, '0.00', 'active', '2025-08-02', NULL, NULL, '2025-08-02 15:49:28', '2025-08-02 15:49:28', '');

-- --------------------------------------------------------

--
-- Table structure for table `interest_waiver_applications`
--

CREATE TABLE `interest_waiver_applications` (
  `id` int(11) NOT NULL,
  `application_code` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `contract_id` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `waiver_amount` decimal(15,2) NOT NULL,
  `waiver_percentage` decimal(5,2) NOT NULL,
  `waiver_type` enum('interest','principal','both','fee') COLLATE utf8mb4_unicode_ci DEFAULT 'interest',
  `original_amount` decimal(15,2) NOT NULL DEFAULT 0.00,
  `remaining_amount_after_waiver` decimal(15,2) NOT NULL DEFAULT 0.00,
  `reason` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `supporting_documents` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`supporting_documents`)),
  `status` enum('pending','approved','rejected','cancelled') COLLATE utf8mb4_unicode_ci DEFAULT 'pending',
  `current_approval_level` int(11) DEFAULT 1,
  `total_approval_levels` int(11) DEFAULT 3,
  `approved_amount` decimal(15,2) DEFAULT 0.00,
  `approved_percentage` decimal(5,2) DEFAULT 0.00,
  `final_decision` enum('approved','rejected','partially_approved') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `decision_date` timestamp NULL DEFAULT NULL,
  `decision_notes` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `department_id` int(11) NOT NULL,
  `expected_collection_amount` int(11) NOT NULL,
  `wallet_amount` int(11) NOT NULL,
  `highest_approval_level` int(11) NOT NULL,
  `exception_notes` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `effective_expiry_date` date NOT NULL,
  `detailed_reason` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `interest_waiver_applications`
--

INSERT INTO `interest_waiver_applications` (`id`, `application_code`, `contract_id`, `customer_id`, `waiver_amount`, `waiver_percentage`, `waiver_type`, `original_amount`, `remaining_amount_after_waiver`, `reason`, `supporting_documents`, `status`, `current_approval_level`, `total_approval_levels`, `approved_amount`, `approved_percentage`, `final_decision`, `decision_date`, `decision_notes`, `created_by`, `created_at`, `updated_at`, `department_id`, `expected_collection_amount`, `wallet_amount`, `highest_approval_level`, `exception_notes`, `effective_expiry_date`, `detailed_reason`) VALUES
(1, 'MG202507300001', 3, 3, '10517717.00', '15.00', 'interest', '28000000.00', '17482283.00', 'Khách hàng gặp khó khăn tài chính do ảnh hưởng dịch bệnh, cần hỗ trợ miễn giảm lãi suất', NULL, 'cancelled', 2, 3, '8000000.00', '12.00', '', '2025-07-30 09:43:43', NULL, 1, '2025-07-30 08:36:57', '2025-07-30 09:43:43', 1, 10001589, 17000000, 2, 'Chứng từ không đủ điều kiện', '2025-07-31', ''),
(2, 'MG202507300002', 4, 4, '25000000.00', '20.00', 'both', '24000000.00', '-1000000.00', 'Khách hàng có hoàn cảnh đặc biệt, cần hỗ trợ miễn giảm cả gốc và lãi', NULL, 'pending', 2, 3, '15000000.00', '15.00', NULL, NULL, NULL, 1, '2025-07-30 08:36:57', '2025-07-30 08:36:57', 2, 20000000, 5000000, 3, 'MGLP 1 HĐ', '2025-08-15', ''),
(5, 'MGP20250730165039402', 3, 3, '28000000.00', '100.00', '', '0.00', '0.00', 'KH khó khăn về thu nhập, công việc', NULL, 'pending', 2, 3, '7000000.00', '25.00', NULL, NULL, NULL, 1, '2025-07-30 09:50:39', '2025-07-30 13:52:31', 1, 0, 0, 1, '', '2025-07-31', '');

-- --------------------------------------------------------

--
-- Table structure for table `interest_waiver_approvals`
--

CREATE TABLE `interest_waiver_approvals` (
  `id` int(11) NOT NULL,
  `application_id` int(11) NOT NULL,
  `approver_id` int(11) NOT NULL,
  `approval_level` int(11) NOT NULL,
  `action` enum('approve','reject','return','partially_approve','cancel') COLLATE utf8mb4_unicode_ci NOT NULL,
  `approved_amount` decimal(15,2) DEFAULT 0.00,
  `approved_percentage` decimal(5,2) DEFAULT 0.00,
  `comments` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `approval_conditions` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `next_approval_level` int(11) DEFAULT NULL,
  `approved_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `interest_waiver_approvals`
--

INSERT INTO `interest_waiver_approvals` (`id`, `application_id`, `approver_id`, `approval_level`, `action`, `approved_amount`, `approved_percentage`, `comments`, `approval_conditions`, `next_approval_level`, `approved_at`) VALUES
(1, 1, 1, 1, 'approve', '8000000.00', '12.00', 'Phê duyệt một phần theo đề xuất', NULL, NULL, '2025-07-30 08:36:57'),
(2, 2, 1, 1, 'partially_approve', '15000000.00', '15.00', 'Phê duyệt một phần, chuyển lên cấp cao hơn', NULL, NULL, '2025-07-30 08:36:57'),
(5, 1, 1, 0, 'cancel', '0.00', '0.00', 'Đơn bị hủy bởi người tạo', NULL, NULL, '2025-07-30 09:02:31'),
(6, 1, 1, 0, 'cancel', '0.00', '0.00', 'Đơn bị hủy bởi người tạo', NULL, NULL, '2025-07-30 09:43:43'),
(7, 5, 1, 1, 'approve', '7000000.00', '25.00', 'Kinh trinh ban lãnh đạo phê duyệt', '', 2, '2025-07-30 13:52:31');

-- --------------------------------------------------------

--
-- Table structure for table `lenders`
--

CREATE TABLE `lenders` (
  `id` int(11) NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `id_number` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `date_of_birth` date DEFAULT NULL,
  `gender` enum('male','female','other') COLLATE utf8mb4_unicode_ci DEFAULT 'male',
  `status` enum('active','inactive','blacklisted') COLLATE utf8mb4_unicode_ci DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `verified` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `lenders`
--

INSERT INTO `lenders` (`id`, `name`, `phone`, `email`, `address`, `id_number`, `date_of_birth`, `gender`, `status`, `created_at`, `updated_at`, `verified`) VALUES
(1, 'Nguyễn Văn Lender', '0901111222', 'lender1@email.com', '12 Đường Lender, Q1, TP.HCM', '123456789111', '1980-01-01', 'male', 'inactive', '2025-07-28 07:56:25', '2025-07-28 08:00:40', 0),
(2, 'Trần Thị Đầu Tư', '0902222333', 'lender2@email.com', '34 Đường Đầu Tư, Q2, TP.HCM', '123456789222', '1985-05-05', 'female', 'active', '2025-07-28 07:56:25', '2025-07-28 07:56:25', 0);

-- --------------------------------------------------------

--
-- Table structure for table `loan_applications`
--

CREATE TABLE `loan_applications` (
  `id` int(11) NOT NULL,
  `application_code` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `customer_id` int(11) NOT NULL,
  `asset_id` int(11) DEFAULT NULL,
  `loan_amount` decimal(15,2) NOT NULL,
  `loan_purpose` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `loan_term_months` int(11) NOT NULL DEFAULT 12,
  `interest_rate_id` int(11) NOT NULL,
  `monthly_rate` decimal(5,2) NOT NULL,
  `daily_rate` decimal(5,4) NOT NULL,
  `customer_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `customer_cmnd` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `customer_address` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `customer_phone_main` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `customer_birth_date` date DEFAULT NULL,
  `customer_id_issued_place` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `customer_id_issued_date` date DEFAULT NULL,
  `customer_email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `customer_job` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `customer_income` decimal(15,2) DEFAULT NULL,
  `customer_company` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `asset_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `asset_quantity` int(11) DEFAULT 1,
  `asset_license_plate` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `asset_frame_number` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `asset_engine_number` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `asset_registration_number` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `asset_registration_date` date DEFAULT NULL,
  `asset_value` decimal(15,2) DEFAULT NULL,
  `asset_description` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `emergency_contact_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `emergency_contact_phone` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `emergency_contact_relationship` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `emergency_contact_address` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `emergency_contact_note` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `has_health_insurance` tinyint(1) DEFAULT 0,
  `has_life_insurance` tinyint(1) DEFAULT 0,
  `has_vehicle_insurance` tinyint(1) DEFAULT 0,
  `otp_code` varchar(6) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `otp_expires_at` timestamp NULL DEFAULT NULL,
  `otp_verified_at` timestamp NULL DEFAULT NULL,
  `status` enum('draft','pending','approved','rejected','disbursed','cancelled') COLLATE utf8mb4_unicode_ci DEFAULT 'pending',
  `current_approval_level` int(11) DEFAULT 1,
  `highest_approval_level` int(11) DEFAULT 1,
  `total_approval_levels` int(11) DEFAULT 3,
  `created_by` int(11) DEFAULT NULL,
  `department_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `final_decision` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `decision_date` date NOT NULL DEFAULT current_timestamp(),
  `approved_amount` int(20) DEFAULT NULL,
  `decision_notes` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `loan_applications`
--

INSERT INTO `loan_applications` (`id`, `application_code`, `customer_id`, `asset_id`, `loan_amount`, `loan_purpose`, `loan_term_months`, `interest_rate_id`, `monthly_rate`, `daily_rate`, `customer_name`, `customer_cmnd`, `customer_address`, `customer_phone_main`, `customer_birth_date`, `customer_id_issued_place`, `customer_id_issued_date`, `customer_email`, `customer_job`, `customer_income`, `customer_company`, `asset_name`, `asset_quantity`, `asset_license_plate`, `asset_frame_number`, `asset_engine_number`, `asset_registration_number`, `asset_registration_date`, `asset_value`, `asset_description`, `emergency_contact_name`, `emergency_contact_phone`, `emergency_contact_relationship`, `emergency_contact_address`, `emergency_contact_note`, `has_health_insurance`, `has_life_insurance`, `has_vehicle_insurance`, `otp_code`, `otp_expires_at`, `otp_verified_at`, `status`, `current_approval_level`, `highest_approval_level`, `total_approval_levels`, `created_by`, `department_id`, `created_at`, `updated_at`, `final_decision`, `decision_date`, `approved_amount`, `decision_notes`) VALUES
(2, 'grgegege', 3, NULL, '11111.00', 'regergeger', 12, 8, '111.00', '9.9999', 'egergege', '22211112222', 'gregegegerer', '0988877777', '0000-00-00', 'ẻgegegege', '2025-08-08', 'thanh@gmail.com', 'fwefwefwefwf', '33.00', 'ghrthrthrthrt', 'hrthrthrthrthr', 1, '33333', 'gegreerg', 'greger', 'gẻgerge', '2001-08-01', '32313.00', 'gegereg', 'gẻgerergre', 'gegre', 'ềwefwe', 'fwefwewf', 'fwefewf', 0, 0, 0, NULL, NULL, NULL, 'pending', 1, 1, 3, NULL, NULL, '2025-08-02 03:16:11', '2025-08-02 03:16:11', NULL, '2025-08-02', NULL, NULL),
(3, 'LA2025080001', 3, NULL, '11111.00', 'regergeger', 12, 8, '111.00', '9.9999', 'egergege', '22211112222', 'gregegegerer', '0988877777', '0000-00-00', 'ẻgegegege', '2025-08-08', 'thanh@gmail.com', 'fwefwefwefwf', '33.00', 'ghrthrthrthrt', 'hrthrthrthrthr', 1, '33333', 'gegreerg', 'greger', 'gẻgerge', '2001-08-01', '32313.00', 'gegereg', 'gẻgerergre', 'gegre', 'ềwefwe', 'fwefwewf', 'fwefewf', 0, 0, 0, NULL, NULL, NULL, 'pending', 1, 1, 3, NULL, NULL, '2025-08-02 03:16:11', '2025-08-02 03:16:11', NULL, '2025-08-02', NULL, NULL),
(5, 'LA2025087747', 3, NULL, '1000000000.00', '', 12, 5, '1.00', '9.9999', 'fdafafaf fwefwefwf', '0302000066666', 'qưedwqdqdqw', '0852217455', '2000-01-02', 'Bộ Công an', '2025-07-02', 'k40modgame@gmail.com', 'đưqdqdqư', '122332323.00', '2313131', '3123123', 1, '321321', '321321', '123312', '321321', '2025-08-02', '3213123.00', '23131231', '213213', '31231231', 'Cha', '123123', '123213', 1, 1, 0, '914454', '2025-08-02 09:17:52', '2025-08-02 09:17:02', 'approved', 3, 3, 3, 1, NULL, '2025-08-02 09:16:26', '2025-08-02 09:45:39', 'approved', '2025-08-02', 1000000000, 'dưqdqdqw'),
(6, 'LA2025086011', 3, 5, '80000000.00', '', 12, 12, '19.00', '0.0000', 'Lê Minh C', '0302000066666', 'fffffffffffffffffffffff', '0901234569', '2000-01-02', 'Cục Quản Lý hành chính về Trật Tự xã Hội', '2025-07-02', '', '', '0.00', '', 'huyndai i10', 1, '30B-61421', '3423424', '432423', '2412421', '2024-01-30', '10000000.00', '', '', '', '', '', '', 1, 1, 0, '653028', '2025-08-02 16:16:16', '2025-08-02 16:15:59', 'approved', 1, 1, 3, 1, NULL, '2025-08-02 16:13:37', '2025-08-02 16:29:14', 'approved', '2025-08-02', 80000000, 'đồng ý'),
(7, 'LA2025085569', 4, 5, '1.00', 'sadsd', 18, 1, '2.50', '0.0830', 'Hanoi Royal Hotel', '111111111', '11111111111', '123456741', '2025-08-05', 'Bộ Công an', '2025-08-08', 'info@hanoiroyalhotel.com', 'đưqdqdqư', '1111111111.00', '111111111', 'huyndai i10', 1, '30B-61421', 'edgergeg', '432423', '2412421', '2025-08-03', '11111111.00', '1111', 'fdafafaf fwefwefwf', '0852217455', 'Cha', 'dqwqwdqw', '11111111', 1, 1, 0, NULL, NULL, NULL, 'pending', 1, 1, 3, 8, NULL, '2025-08-03 05:13:50', '2025-08-03 05:13:50', NULL, '2025-08-03', NULL, NULL),
(8, 'LA2025085526', 3, 5, '11.11', '1111', 18, 5, '1.00', '9.9999', 'Lê Minh C', '11', '11', '0901234569', '2025-08-03', 'Cục Quản Lý hành chính về Trật Tự xã Hội', '2025-08-03', '111@gmail.com', 'sdfgs', '11111111.00', '1111', '111', 11, '111', '11', '11', '1111', '2025-08-03', '111.00', '111', '111', '', 'Cha', '11', '11', 1, 1, 0, NULL, NULL, NULL, 'pending', 1, 1, 3, 8, NULL, '2025-08-03 05:15:00', '2025-08-03 05:15:00', NULL, '2025-08-03', NULL, NULL),
(9, 'LA2025085945', 3, 5, '11.11', '1111', 18, 5, '1.00', '9.9999', 'Lê Minh C', '11', '11', '0901234569', '2025-08-03', 'Cục Quản Lý hành chính về Trật Tự xã Hội', '2025-08-03', '111@gmail.com', 'sdfgs', '11111111.00', '1111', '111', 11, '111', '11', '11', '1111', '2025-08-03', '111.00', '111', '111', '', 'Cha', '11', '11', 1, 1, 0, NULL, NULL, NULL, 'pending', 1, 1, 3, 8, NULL, '2025-08-03 05:17:09', '2025-08-03 05:17:09', NULL, '2025-08-03', NULL, NULL),
(10, 'LA2025089532', 3, 5, '1000000000.00', '1111', 6, 7, '333.00', '9.9999', 'tuanthanh', '111111', '1111111', '0852217488', '2025-08-03', 'Bộ Công an', '2025-08-03', '1111111', '11111111', '1111111111.00', 'ffwfwfwf', '1111111', 1, '111111', '11111', '111111', '1111111', '2025-08-03', '1111111.00', '11111111', '1111111', '11111111', 'Vợ', '111111', '1111111111', 1, 1, 0, '991838', '2025-08-03 05:22:44', '2025-08-03 05:21:49', 'pending', 3, 1, 3, 8, NULL, '2025-08-03 05:21:34', '2025-08-03 05:48:36', NULL, '2025-08-03', NULL, NULL),
(11, 'LA2025088819', 5, 5, '51000000.00', '111111111', 12, 5, '1.00', '9.9999', 'fdafafaf fwefwefwf', '11111111', '1111111', '0852217455', '2025-08-03', 'Bộ Công an', '2025-08-03', 'k@gmail.com', 'fswfwew', '1111111111111.00', '1111', '111', 1, '11', '111', '11', '111', '2025-08-03', '111.00', '1111', '111', '111111111', 'Mẹ', 'dqwqwdqw', '111111111', 1, 1, 0, '100601', '2025-08-03 06:04:14', '2025-08-03 06:03:19', 'pending', 1, 1, 3, 6, NULL, '2025-08-03 05:32:26', '2025-08-03 06:03:19', NULL, '2025-08-03', NULL, NULL),
(12, 'LA2025089555', 5, 5, '1.11', '111111', 18, 5, '1.00', '9.9999', 'Hanoi Royal Hotel', '1111111111', '1111111111111', '123456741', '2025-08-03', 'Bộ Công an', '2025-08-03', 'info@hanoiroyalhotel.com', 'đưqdqdqư', '11111111111.00', '111111111', '11111', 1, '11111', '111', '11111', '111', '2025-08-03', '1111111.00', '111111', 'tuanthanh', '0852217488', 'Cha', '342423', '111111', 1, 1, 0, NULL, NULL, NULL, 'pending', 1, 1, 3, 6, NULL, '2025-08-03 05:38:24', '2025-08-03 05:38:24', NULL, '2025-08-03', NULL, NULL),
(13, 'LA2025082117', 4, 5, '51000000.00', '1111', 12, 5, '1.00', '9.9999', 'Hanoi Royal Hotel', '11111111111', '11111', '123456741', '2025-08-03', 'Bộ Công an', '2025-08-03', 'info@hanoiroyalhotel.com', 'ewfwfew', '111111.00', '11111', '11111', 1, '1111', '11111', '1', '11111111111', '2025-08-03', '1111111.00', '1', 'tuanthanh', '0852217488', 'Mẹ', '1111111111', '1111111', 1, 1, 0, '984769', '2025-08-03 05:43:05', '2025-08-03 05:42:09', 'approved', 2, 2, 3, 6, NULL, '2025-08-03 05:41:57', '2025-08-03 05:49:43', 'approved', '2025-08-03', 51000000, 'ok'),
(14, 'LA2025088797', 5, 5, '51000000.00', '111', 12, 5, '1.00', '9.9999', 'Hanoi Royal Hotel', '111', '11', '123456741', '2025-08-03', 'Bộ Công an', '2025-08-03', 'info@hanoiroyalhotel.com', 'fswfwew', '11111111111.00', '1111', '111', 1, '111', '111', '111', '111', '2025-08-03', '11111111.00', '111', '111', '111', 'Mẹ', '111', '111', 1, 1, 0, '299926', '2025-08-03 06:05:51', '2025-08-03 06:04:55', 'disbursed', 2, 2, 3, 6, NULL, '2025-08-03 06:04:46', '2025-08-03 06:24:41', 'approved', '2025-08-03', 51000000, 'fewfw');

-- --------------------------------------------------------

--
-- Table structure for table `loan_approvals`
--

CREATE TABLE `loan_approvals` (
  `id` int(11) NOT NULL,
  `application_id` int(11) NOT NULL,
  `approver_id` int(11) NOT NULL,
  `approval_level` int(11) NOT NULL,
  `action` enum('approve','reject','request_info','cancel') NOT NULL,
  `approved_amount` decimal(15,2) DEFAULT NULL,
  `comments` text DEFAULT NULL,
  `approval_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `loan_approvals`
--

INSERT INTO `loan_approvals` (`id`, `application_id`, `approver_id`, `approval_level`, `action`, `approved_amount`, `comments`, `approval_date`) VALUES
(5, 5, 8, 3, 'approve', '1000000000.00', 'dưqdqdqw', '2025-08-02 09:45:39'),
(6, 6, 6, 1, 'approve', '80000000.00', 'đồng ý', '2025-08-02 16:29:14'),
(7, 13, 6, 2, 'approve', '51000000.00', 'ok', '2025-08-03 05:49:43'),
(8, 14, 6, 2, 'approve', '51000000.00', 'fewfw', '2025-08-03 06:14:17');

-- --------------------------------------------------------

--
-- Table structure for table `loan_approval_roles`
--

CREATE TABLE `loan_approval_roles` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `approval_order` int(11) NOT NULL,
  `min_amount` decimal(15,2) NOT NULL,
  `max_amount` decimal(15,2) NOT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `loan_approval_roles`
--

INSERT INTO `loan_approval_roles` (`id`, `name`, `description`, `approval_order`, `min_amount`, `max_amount`, `status`, `created_at`, `updated_at`) VALUES
(9, 'Nhân Viên', 'Nhân viên phê duyệt khoản vay nhỏ', 1, '0.00', '50000000.00', 'active', '2025-08-03 05:00:09', '2025-08-03 05:00:09'),
(10, 'Trưởng Phòng', 'Trưởng phòng phê duyệt khoản vay trung bình', 2, '50000001.00', '200000000.00', 'active', '2025-08-03 05:00:09', '2025-08-03 05:00:09'),
(11, 'Giám Đốc', 'Giám đốc phê duyệt khoản vay lớn', 3, '200000001.00', '1000000000.00', 'active', '2025-08-03 05:00:09', '2025-08-03 05:00:09'),
(12, 'Tổng Giám Đốc', 'Tổng giám đốc phê duyệt khoản vay rất lớn', 4, '1000000001.00', '999999999999.00', 'active', '2025-08-03 05:00:09', '2025-08-03 05:00:09');

-- --------------------------------------------------------

--
-- Table structure for table `loan_approval_users`
--

CREATE TABLE `loan_approval_users` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `role_id` int(11) NOT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `loan_approval_users`
--

INSERT INTO `loan_approval_users` (`id`, `user_id`, `role_id`, `status`, `created_at`, `updated_at`) VALUES
(1, 2, 9, 'active', '2025-08-02 07:23:18', '2025-08-03 05:25:19'),
(2, 6, 10, 'active', '2025-08-02 07:29:56', '2025-08-03 05:25:24'),
(3, 7, 11, 'active', '2025-08-02 07:29:56', '2025-08-03 05:25:28'),
(4, 8, 12, 'active', '2025-08-02 07:29:56', '2025-08-03 05:25:32');

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `message` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` enum('info','success','warning','error') COLLATE utf8mb4_unicode_ci DEFAULT 'info',
  `icon` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT 'info-circle',
  `read_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`id`, `user_id`, `title`, `message`, `type`, `icon`, `read_at`, `created_at`) VALUES
(1, 1, 'Hợp đồng mới', 'Đã tạo hợp đồng HD001 cho khách hàng Nguyễn Văn A', 'success', 'file-contract', NULL, '2025-07-22 11:20:17'),
(2, 1, 'Thanh toán lãi', 'Khách hàng Trần Thị B đã thanh toán lãi tháng 2', 'info', 'money-bill-wave', NULL, '2025-07-22 11:20:17'),
(3, 1, 'Hợp đồng quá hạn', 'Hợp đồng HD002 đã quá hạn 5 ngày', 'warning', 'exclamation-triangle', NULL, '2025-07-22 11:20:17');

-- --------------------------------------------------------

--
-- Table structure for table `otp_logs`
--

CREATE TABLE `otp_logs` (
  `id` int(11) NOT NULL,
  `application_id` int(11) NOT NULL,
  `otp_code` varchar(6) COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone_number` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` enum('sent','delivered','failed','verified','expired') COLLATE utf8mb4_unicode_ci DEFAULT 'sent',
  `sent_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `verified_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `customer_id` int(11) NOT NULL,
  `ip_address` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `otp_logs`
--

INSERT INTO `otp_logs` (`id`, `application_id`, `otp_code`, `phone_number`, `status`, `sent_at`, `verified_at`, `expires_at`, `customer_id`, `ip_address`) VALUES
(2, 5, '914454', '0901234569', 'verified', '2025-08-02 09:16:52', '2025-08-02 09:17:02', '2025-08-02 09:17:52', 3, '::1'),
(3, 6, '525090', '0901234569', 'sent', '2025-08-02 16:14:12', NULL, '2025-08-02 16:15:12', 3, '::1'),
(4, 6, '653028', '0901234569', 'verified', '2025-08-02 16:15:16', '2025-08-02 16:15:59', '2025-08-02 16:16:16', 3, '::1'),
(5, 10, '991838', '0901234569', 'verified', '2025-08-03 05:21:44', '2025-08-03 05:21:49', '2025-08-03 05:22:44', 3, '::1'),
(6, 13, '984769', '0901234570', 'verified', '2025-08-03 05:42:05', '2025-08-03 05:42:09', '2025-08-03 05:43:05', 4, '::1'),
(7, 11, '100601', '0852217455', 'verified', '2025-08-03 06:03:14', '2025-08-03 06:03:19', '2025-08-03 06:04:14', 5, '::1'),
(8, 14, '299926', '0852217455', 'verified', '2025-08-03 06:04:51', '2025-08-03 06:04:55', '2025-08-03 06:05:51', 5, '::1');

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `id` int(11) NOT NULL,
  `contract_id` int(11) NOT NULL,
  `payment_type_id` int(11) NOT NULL,
  `amount` decimal(15,2) NOT NULL,
  `payment_date` date NOT NULL,
  `payment_method` enum('cash','bank_transfer','mobile_money','other') COLLATE utf8mb4_unicode_ci DEFAULT 'cash',
  `reference_number` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `payments`
--

INSERT INTO `payments` (`id`, `contract_id`, `payment_type_id`, `amount`, `payment_date`, `payment_method`, `reference_number`, `description`, `created_by`, `created_at`, `updated_at`) VALUES
(4, 3, 1, '560000.00', '2024-02-20', 'bank_transfer', NULL, NULL, 1, '2025-07-22 11:20:17', '2025-07-22 11:20:17');

-- --------------------------------------------------------

--
-- Table structure for table `payment_schedule`
--

CREATE TABLE `payment_schedule` (
  `id` int(11) NOT NULL,
  `contract_id` int(11) NOT NULL,
  `due_date` date NOT NULL,
  `principal` decimal(15,2) DEFAULT 0.00,
  `interest` decimal(15,2) DEFAULT 0.00,
  `fee` decimal(15,2) DEFAULT 0.00,
  `penalty` decimal(15,2) DEFAULT 0.00,
  `status` enum('pending','paid','overdue') COLLATE utf8mb4_unicode_ci DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `payment_types`
--

CREATE TABLE `payment_types` (
  `id` int(11) NOT NULL,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` enum('active','inactive') COLLATE utf8mb4_unicode_ci DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `payment_types`
--

INSERT INTO `payment_types` (`id`, `name`, `description`, `status`, `created_at`) VALUES
(1, 'Tiền lãi', 'Thanh toán tiền lãi định kỳ', 'active', '2025-07-22 11:20:16'),
(2, 'Gốc', 'Thanh toán tiền gốc', 'active', '2025-07-22 11:20:16'),
(3, 'Phí phạt', 'Phí phạt quá hạn', 'active', '2025-07-22 11:20:16'),
(4, 'Phí dịch vụ', 'Phí dịch vụ khác', 'active', '2025-07-22 11:20:16'),
(5, 'Khác', 'Các khoản thanh toán khác', 'active', '2025-07-22 11:20:16');

-- --------------------------------------------------------

--
-- Table structure for table `system_settings`
--

CREATE TABLE `system_settings` (
  `id` int(11) NOT NULL,
  `setting_key` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `setting_value` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `setting_type` enum('string','number','boolean','json') COLLATE utf8mb4_unicode_ci DEFAULT 'string',
  `description` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `updated_by` int(11) DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `system_settings`
--

INSERT INTO `system_settings` (`id`, `setting_key`, `setting_value`, `setting_type`, `description`, `updated_by`, `updated_at`) VALUES
(1, 'company_name', 'VayCamCo', 'string', 'Tên công ty', NULL, '2025-07-22 11:20:16'),
(2, 'company_address', '123 Đường ABC, Quận 1, TP.HCM', 'string', 'Địa chỉ công ty', NULL, '2025-07-22 11:20:16'),
(3, 'company_phone', '0901234567', 'string', 'Số điện thoại công ty', NULL, '2025-07-22 11:20:16'),
(4, 'company_email', 'info@vaycamco.com', 'string', 'Email công ty', NULL, '2025-07-22 11:20:16'),
(5, 'default_currency', 'VND', 'string', 'Đơn vị tiền tệ mặc định', NULL, '2025-07-22 11:20:16'),
(6, 'date_format', 'd/m/Y', 'string', 'Định dạng ngày tháng', NULL, '2025-07-22 11:20:16'),
(7, 'timezone', 'Asia/Ho_Chi_Minh', 'string', 'Múi giờ', NULL, '2025-07-22 11:20:16'),
(8, 'maintenance_mode', 'false', 'boolean', 'Chế độ bảo trì', NULL, '2025-07-22 11:20:16'),
(9, 'max_file_size', '5242880', 'number', 'Kích thước file tối đa (bytes)', NULL, '2025-07-22 11:20:16'),
(10, 'allowed_file_types', '[\"jpg\",\"jpeg\",\"png\",\"pdf\",\"doc\",\"docx\"]', 'json', 'Các loại file được phép upload', NULL, '2025-07-22 11:20:16');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `role` enum('admin','manager','user') COLLATE utf8mb4_unicode_ci DEFAULT 'user',
  `status` enum('active','inactive','suspended') COLLATE utf8mb4_unicode_ci DEFAULT 'active',
  `avatar` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `last_login` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `username` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `department` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password`, `role`, `status`, `avatar`, `last_login`, `created_at`, `updated_at`, `username`, `department`) VALUES
(1, 'Admin', 'admin@gmail.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 'active', NULL, NULL, '2025-07-22 11:20:16', '2025-07-26 12:55:52', '', ''),
(2, 'Manager', 'manager@vaycamco.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'manager', 'active', NULL, NULL, '2025-07-22 11:20:16', '2025-07-22 11:20:16', '', ''),
(3, 'User', 'user@vaycamco.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user', 'active', NULL, NULL, '2025-07-22 11:20:16', '2025-07-22 11:20:16', '', ''),
(4, 'tuanthanh', 'k40modgame@gmail.com', '$2y$10$Fjtefrpq/UFIfOLgkqgKpuijKZi2qcu4IH6UHSGFNU7IvY8jPhAmq', 'user', 'active', NULL, NULL, '2025-07-30 13:59:41', '2025-07-30 13:59:41', '', ''),
(6, 'Trưởng Phòng Kinh Doanh', 'manager1@vaycamco.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'manager', 'active', NULL, NULL, '2025-08-02 07:29:56', '2025-08-02 07:29:56', 'manager1', 'business'),
(7, 'Giám Đốc Tài Chính', 'director@vaycamco.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '', 'active', NULL, NULL, '2025-08-02 07:29:56', '2025-08-02 07:29:56', 'director1', 'capital'),
(8, 'Tổng Giám Đốc', 'ceo@vaycamco.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '', 'active', NULL, NULL, '2025-08-02 07:29:56', '2025-08-02 07:29:56', 'ceo1', 'risk_management');

-- --------------------------------------------------------

--
-- Table structure for table `user_departments`
--

CREATE TABLE `user_departments` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `department_id` int(11) NOT NULL,
  `role_in_department` enum('member','manager','director') COLLATE utf8mb4_unicode_ci DEFAULT 'member',
  `status` enum('active','inactive') COLLATE utf8mb4_unicode_ci DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `user_departments`
--

INSERT INTO `user_departments` (`id`, `user_id`, `department_id`, `role_in_department`, `status`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 'director', 'active', '2025-07-30 08:30:00', '2025-07-30 08:30:00'),
(2, 1, 2, 'director', 'active', '2025-07-30 08:30:00', '2025-07-30 08:30:00'),
(3, 1, 3, 'director', 'active', '2025-07-30 08:30:00', '2025-07-30 08:30:00');

-- --------------------------------------------------------

--
-- Table structure for table `waiver_approval_limits`
--

CREATE TABLE `waiver_approval_limits` (
  `id` int(11) NOT NULL,
  `year` int(4) NOT NULL,
  `month` int(2) NOT NULL,
  `level_1_limit` decimal(15,2) NOT NULL DEFAULT 0.00,
  `level_2_limit` decimal(15,2) NOT NULL DEFAULT 0.00,
  `level_3_limit` decimal(15,2) NOT NULL DEFAULT 0.00,
  `level_1_used` decimal(15,2) NOT NULL DEFAULT 0.00,
  `level_2_used` decimal(15,2) NOT NULL DEFAULT 0.00,
  `level_3_used` decimal(15,2) NOT NULL DEFAULT 0.00,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `waiver_approval_limits`
--

INSERT INTO `waiver_approval_limits` (`id`, `year`, `month`, `level_1_limit`, `level_2_limit`, `level_3_limit`, `level_1_used`, `level_2_used`, `level_3_used`, `created_at`, `updated_at`) VALUES
(1, 2025, 7, '500000000.00', '500000000.00', '124000000.00', '450000000.00', '450000000.00', '222131513.00', '2025-07-30 08:30:00', '2025-07-30 08:30:00');

-- --------------------------------------------------------

--
-- Table structure for table `waiver_documents`
--

CREATE TABLE `waiver_documents` (
  `id` int(11) NOT NULL,
  `application_id` int(11) NOT NULL,
  `document_type` enum('customer_letter','image','certificate','other') COLLATE utf8mb4_unicode_ci NOT NULL,
  `file_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `file_path` varchar(500) COLLATE utf8mb4_unicode_ci NOT NULL,
  `file_size` int(11) DEFAULT NULL,
  `mime_type` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `uploaded_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `waiver_documents`
--

INSERT INTO `waiver_documents` (`id`, `application_id`, `document_type`, `file_name`, `file_path`, `file_size`, `mime_type`, `description`, `uploaded_by`, `created_at`) VALUES
(1, 1, 'customer_letter', 'don_xin_mien_giam_001.pdf', 'uploads/documents/don_xin_mien_giam_001.pdf', 1024000, 'application/pdf', 'Đơn xin miễn giảm của khách hàng', 1, '2025-07-30 08:36:57'),
(2, 1, 'image', 'chung_minh_kho_khan_001.jpg', 'uploads/images/chung_minh_kho_khan_001.jpg', 512000, 'image/jpeg', 'Chứng minh hoàn cảnh khó khăn', 1, '2025-07-30 08:36:57'),
(3, 2, 'customer_letter', 'don_xin_mien_giam_002.pdf', 'uploads/documents/don_xin_mien_giam_002.pdf', 2048000, 'application/pdf', 'Đơn xin miễn giảm của khách hàng', 1, '2025-07-30 08:36:57'),
(4, 2, 'certificate', 'chung_tu_hoan_canh_002.pdf', 'uploads/documents/chung_tu_hoan_canh_002.pdf', 1536000, 'application/pdf', 'Chứng từ hoàn cảnh đặc biệt', 1, '2025-07-30 08:36:57');

-- --------------------------------------------------------

--
-- Table structure for table `waiver_fee_types`
--

CREATE TABLE `waiver_fee_types` (
  `id` int(11) NOT NULL,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `fee_category` enum('interest','principal','penalty','service','other') COLLATE utf8mb4_unicode_ci DEFAULT 'other',
  `status` enum('active','inactive') COLLATE utf8mb4_unicode_ci DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `waiver_fee_types`
--

INSERT INTO `waiver_fee_types` (`id`, `name`, `description`, `fee_category`, `status`, `created_at`, `updated_at`) VALUES
(1, 'Lãi suất', 'Miễn giảm lãi suất', 'interest', 'active', '2025-07-30 05:39:25', '2025-07-30 05:39:25'),
(2, 'Gốc vay', 'Miễn giảm gốc vay', 'principal', 'active', '2025-07-30 05:39:25', '2025-07-30 05:39:25'),
(3, 'Phí phạt', 'Miễn giảm phí phạt quá hạn', 'penalty', 'active', '2025-07-30 05:39:25', '2025-07-30 05:39:25'),
(4, 'Phí dịch vụ', 'Miễn giảm phí dịch vụ', 'service', 'active', '2025-07-30 05:39:25', '2025-07-30 05:39:25'),
(5, 'Phí khác', 'Miễn giảm các loại phí khác', 'other', 'active', '2025-07-30 05:39:25', '2025-07-30 05:39:25');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `activity_logs`
--
ALTER TABLE `activity_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_action` (`action`),
  ADD KEY `idx_table_name` (`table_name`),
  ADD KEY `idx_created_at` (`created_at`);

--
-- Indexes for table `assets`
--
ALTER TABLE `assets`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_customer_id` (`customer_id`),
  ADD KEY `idx_category_id` (`category_id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_created_by` (`created_by`),
  ADD KEY `idx_assets_license_plate` (`license_plate`),
  ADD KEY `idx_assets_frame_number` (`frame_number`),
  ADD KEY `idx_assets_engine_number` (`engine_number`),
  ADD KEY `idx_assets_registration_number` (`registration_number`);

--
-- Indexes for table `asset_categories`
--
ALTER TABLE `asset_categories`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `consult_histories`
--
ALTER TABLE `consult_histories`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_contract_id` (`contract_id`),
  ADD KEY `idx_result` (`result`),
  ADD KEY `idx_appointment_date` (`appointment_date`);

--
-- Indexes for table `contracts`
--
ALTER TABLE `contracts`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `contract_code` (`contract_code`),
  ADD KEY `interest_rate_id` (`interest_rate_id`),
  ADD KEY `idx_contract_code` (`contract_code`),
  ADD KEY `idx_customer_id` (`customer_id`),
  ADD KEY `idx_asset_id` (`asset_id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_start_date` (`start_date`),
  ADD KEY `idx_end_date` (`end_date`),
  ADD KEY `idx_created_by` (`created_by`);

--
-- Indexes for table `contract_details`
--
ALTER TABLE `contract_details`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_contract_id` (`contract_id`),
  ADD KEY `idx_material_insurance` (`material_insurance`),
  ADD KEY `idx_hospital_insurance` (`hospital_insurance`),
  ADD KEY `idx_insurance_status` (`insurance_status`),
  ADD KEY `idx_digital_signature` (`digital_signature`),
  ADD KEY `idx_store_code` (`store_code`),
  ADD KEY `idx_tima_customer_link` (`tima_customer_link`),
  ADD KEY `idx_ndt_customer_link` (`ndt_customer_link`);

--
-- Indexes for table `customers`
--
ALTER TABLE `customers`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_phone` (`phone`),
  ADD KEY `idx_email` (`email`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_created_by` (`created_by`),
  ADD KEY `idx_cif` (`cif`);

--
-- Indexes for table `debt_collection_activities`
--
ALTER TABLE `debt_collection_activities`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_contract_id` (`contract_id`),
  ADD KEY `idx_customer_id` (`customer_id`),
  ADD KEY `idx_activity_type` (`activity_type`),
  ADD KEY `idx_activity_date` (`activity_date`),
  ADD KEY `idx_result` (`result`),
  ADD KEY `idx_created_by` (`created_by`);

--
-- Indexes for table `debt_collection_roles`
--
ALTER TABLE `debt_collection_roles`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_name` (`name`),
  ADD KEY `idx_status` (`status`);

--
-- Indexes for table `debt_collection_users`
--
ALTER TABLE `debt_collection_users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_user_role` (`user_id`,`role_id`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_role_id` (`role_id`),
  ADD KEY `idx_status` (`status`);

--
-- Indexes for table `departments`
--
ALTER TABLE `departments`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `code` (`code`),
  ADD KEY `idx_name` (`name`),
  ADD KEY `idx_status` (`status`);

--
-- Indexes for table `disbursement_history`
--
ALTER TABLE `disbursement_history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_contract_id` (`contract_id`),
  ADD KEY `idx_application_id` (`application_id`),
  ADD KEY `disbursed_by` (`disbursed_by`),
  ADD KEY `approved_by` (`approved_by`);

--
-- Indexes for table `documents`
--
ALTER TABLE `documents`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_contract_id` (`contract_id`),
  ADD KEY `idx_category` (`category`);

--
-- Indexes for table `electronic_contracts`
--
ALTER TABLE `electronic_contracts`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `contract_code` (`contract_code`),
  ADD KEY `idx_application_id` (`application_id`),
  ADD KEY `idx_customer_id` (`customer_id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `asset_id` (`asset_id`),
  ADD KEY `interest_rate_id` (`interest_rate_id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `approved_by` (`approved_by`),
  ADD KEY `disbursed_by` (`disbursed_by`);

--
-- Indexes for table `insurance_config`
--
ALTER TABLE `insurance_config`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_insurance_type` (`insurance_type`);

--
-- Indexes for table `interest_rates`
--
ALTER TABLE `interest_rates`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `idx_loan_type` (`loan_type`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_effective_from` (`effective_from`);

--
-- Indexes for table `interest_waiver_applications`
--
ALTER TABLE `interest_waiver_applications`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `application_code` (`application_code`),
  ADD KEY `idx_application_code` (`application_code`),
  ADD KEY `idx_contract_id` (`contract_id`),
  ADD KEY `idx_customer_id` (`customer_id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_created_by` (`created_by`),
  ADD KEY `idx_created_at` (`created_at`);

--
-- Indexes for table `interest_waiver_approvals`
--
ALTER TABLE `interest_waiver_approvals`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_application_id` (`application_id`),
  ADD KEY `idx_approver_id` (`approver_id`),
  ADD KEY `idx_approval_level` (`approval_level`),
  ADD KEY `idx_action` (`action`);

--
-- Indexes for table `lenders`
--
ALTER TABLE `lenders`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `loan_applications`
--
ALTER TABLE `loan_applications`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `application_code` (`application_code`),
  ADD KEY `idx_customer_id` (`customer_id`),
  ADD KEY `idx_asset_id` (`asset_id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_created_by` (`created_by`),
  ADD KEY `idx_created_at` (`created_at`),
  ADD KEY `idx_application_code` (`application_code`),
  ADD KEY `loan_applications_ibfk_3` (`interest_rate_id`);

--
-- Indexes for table `loan_approvals`
--
ALTER TABLE `loan_approvals`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_application_id` (`application_id`),
  ADD KEY `idx_approver_id` (`approver_id`),
  ADD KEY `idx_approval_level` (`approval_level`);

--
-- Indexes for table `loan_approval_roles`
--
ALTER TABLE `loan_approval_roles`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `loan_approval_users`
--
ALTER TABLE `loan_approval_users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_role_unique` (`user_id`,`role_id`),
  ADD KEY `role_id` (`role_id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_read_at` (`read_at`),
  ADD KEY `idx_created_at` (`created_at`);

--
-- Indexes for table `otp_logs`
--
ALTER TABLE `otp_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_application_id` (`application_id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_sent_at` (`sent_at`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_contract_id` (`contract_id`),
  ADD KEY `idx_payment_type_id` (`payment_type_id`),
  ADD KEY `idx_payment_date` (`payment_date`),
  ADD KEY `idx_created_by` (`created_by`);

--
-- Indexes for table `payment_schedule`
--
ALTER TABLE `payment_schedule`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_contract_id` (`contract_id`),
  ADD KEY `idx_due_date` (`due_date`),
  ADD KEY `idx_status` (`status`);

--
-- Indexes for table `payment_types`
--
ALTER TABLE `payment_types`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `system_settings`
--
ALTER TABLE `system_settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `setting_key` (`setting_key`),
  ADD KEY `updated_by` (`updated_by`),
  ADD KEY `idx_setting_key` (`setting_key`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_email` (`email`),
  ADD KEY `idx_role` (`role`),
  ADD KEY `idx_status` (`status`);

--
-- Indexes for table `user_departments`
--
ALTER TABLE `user_departments`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_user_department` (`user_id`,`department_id`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_department_id` (`department_id`),
  ADD KEY `idx_role` (`role_in_department`);

--
-- Indexes for table `waiver_approval_limits`
--
ALTER TABLE `waiver_approval_limits`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_year_month` (`year`,`month`);

--
-- Indexes for table `waiver_documents`
--
ALTER TABLE `waiver_documents`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_application_id` (`application_id`),
  ADD KEY `idx_document_type` (`document_type`),
  ADD KEY `idx_uploaded_by` (`uploaded_by`);

--
-- Indexes for table `waiver_fee_types`
--
ALTER TABLE `waiver_fee_types`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_name` (`name`),
  ADD KEY `idx_fee_category` (`fee_category`),
  ADD KEY `idx_status` (`status`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `activity_logs`
--
ALTER TABLE `activity_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT for table `assets`
--
ALTER TABLE `assets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `asset_categories`
--
ALTER TABLE `asset_categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `consult_histories`
--
ALTER TABLE `consult_histories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `contracts`
--
ALTER TABLE `contracts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `contract_details`
--
ALTER TABLE `contract_details`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `customers`
--
ALTER TABLE `customers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `debt_collection_activities`
--
ALTER TABLE `debt_collection_activities`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `debt_collection_roles`
--
ALTER TABLE `debt_collection_roles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `debt_collection_users`
--
ALTER TABLE `debt_collection_users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `departments`
--
ALTER TABLE `departments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `disbursement_history`
--
ALTER TABLE `disbursement_history`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `documents`
--
ALTER TABLE `documents`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `electronic_contracts`
--
ALTER TABLE `electronic_contracts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `insurance_config`
--
ALTER TABLE `insurance_config`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `interest_rates`
--
ALTER TABLE `interest_rates`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `interest_waiver_applications`
--
ALTER TABLE `interest_waiver_applications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `interest_waiver_approvals`
--
ALTER TABLE `interest_waiver_approvals`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `lenders`
--
ALTER TABLE `lenders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `loan_applications`
--
ALTER TABLE `loan_applications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `loan_approvals`
--
ALTER TABLE `loan_approvals`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `loan_approval_roles`
--
ALTER TABLE `loan_approval_roles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `loan_approval_users`
--
ALTER TABLE `loan_approval_users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `otp_logs`
--
ALTER TABLE `otp_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `payment_schedule`
--
ALTER TABLE `payment_schedule`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `payment_types`
--
ALTER TABLE `payment_types`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `system_settings`
--
ALTER TABLE `system_settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `user_departments`
--
ALTER TABLE `user_departments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `waiver_approval_limits`
--
ALTER TABLE `waiver_approval_limits`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `waiver_documents`
--
ALTER TABLE `waiver_documents`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `waiver_fee_types`
--
ALTER TABLE `waiver_fee_types`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `activity_logs`
--
ALTER TABLE `activity_logs`
  ADD CONSTRAINT `activity_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `assets`
--
ALTER TABLE `assets`
  ADD CONSTRAINT `assets_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `assets_ibfk_2` FOREIGN KEY (`category_id`) REFERENCES `asset_categories` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `assets_ibfk_3` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `consult_histories`
--
ALTER TABLE `consult_histories`
  ADD CONSTRAINT `consult_histories_ibfk_1` FOREIGN KEY (`contract_id`) REFERENCES `contracts` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `contracts`
--
ALTER TABLE `contracts`
  ADD CONSTRAINT `contracts_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `contracts_ibfk_2` FOREIGN KEY (`asset_id`) REFERENCES `assets` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `contracts_ibfk_3` FOREIGN KEY (`interest_rate_id`) REFERENCES `interest_rates` (`id`),
  ADD CONSTRAINT `contracts_ibfk_4` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `contract_details`
--
ALTER TABLE `contract_details`
  ADD CONSTRAINT `contract_details_ibfk_1` FOREIGN KEY (`contract_id`) REFERENCES `contracts` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `customers`
--
ALTER TABLE `customers`
  ADD CONSTRAINT `customers_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `debt_collection_activities`
--
ALTER TABLE `debt_collection_activities`
  ADD CONSTRAINT `debt_collection_activities_ibfk_1` FOREIGN KEY (`contract_id`) REFERENCES `loan_applications` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `debt_collection_activities_ibfk_2` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `debt_collection_activities_ibfk_3` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`);

--
-- Constraints for table `debt_collection_users`
--
ALTER TABLE `debt_collection_users`
  ADD CONSTRAINT `debt_collection_users_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `debt_collection_users_ibfk_2` FOREIGN KEY (`role_id`) REFERENCES `debt_collection_roles` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `disbursement_history`
--
ALTER TABLE `disbursement_history`
  ADD CONSTRAINT `disbursement_history_ibfk_1` FOREIGN KEY (`contract_id`) REFERENCES `electronic_contracts` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `disbursement_history_ibfk_2` FOREIGN KEY (`application_id`) REFERENCES `loan_applications` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `disbursement_history_ibfk_3` FOREIGN KEY (`disbursed_by`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `disbursement_history_ibfk_4` FOREIGN KEY (`approved_by`) REFERENCES `users` (`id`);

--
-- Constraints for table `documents`
--
ALTER TABLE `documents`
  ADD CONSTRAINT `documents_ibfk_1` FOREIGN KEY (`contract_id`) REFERENCES `contracts` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `electronic_contracts`
--
ALTER TABLE `electronic_contracts`
  ADD CONSTRAINT `electronic_contracts_ibfk_1` FOREIGN KEY (`application_id`) REFERENCES `loan_applications` (`id`),
  ADD CONSTRAINT `electronic_contracts_ibfk_2` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `electronic_contracts_ibfk_3` FOREIGN KEY (`asset_id`) REFERENCES `assets` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `electronic_contracts_ibfk_4` FOREIGN KEY (`interest_rate_id`) REFERENCES `interest_rates` (`id`),
  ADD CONSTRAINT `electronic_contracts_ibfk_5` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `electronic_contracts_ibfk_6` FOREIGN KEY (`approved_by`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `electronic_contracts_ibfk_7` FOREIGN KEY (`disbursed_by`) REFERENCES `users` (`id`);

--
-- Constraints for table `interest_rates`
--
ALTER TABLE `interest_rates`
  ADD CONSTRAINT `interest_rates_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `interest_waiver_applications`
--
ALTER TABLE `interest_waiver_applications`
  ADD CONSTRAINT `interest_waiver_applications_ibfk_1` FOREIGN KEY (`contract_id`) REFERENCES `contracts` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `interest_waiver_applications_ibfk_2` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `interest_waiver_applications_ibfk_3` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`);

--
-- Constraints for table `interest_waiver_approvals`
--
ALTER TABLE `interest_waiver_approvals`
  ADD CONSTRAINT `interest_waiver_approvals_ibfk_1` FOREIGN KEY (`application_id`) REFERENCES `interest_waiver_applications` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `interest_waiver_approvals_ibfk_2` FOREIGN KEY (`approver_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `loan_applications`
--
ALTER TABLE `loan_applications`
  ADD CONSTRAINT `loan_applications_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `loan_applications_ibfk_2` FOREIGN KEY (`asset_id`) REFERENCES `assets` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `loan_applications_ibfk_3` FOREIGN KEY (`interest_rate_id`) REFERENCES `interest_rates` (`id`),
  ADD CONSTRAINT `loan_applications_ibfk_4` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `loan_approvals`
--
ALTER TABLE `loan_approvals`
  ADD CONSTRAINT `loan_approvals_ibfk_1` FOREIGN KEY (`application_id`) REFERENCES `loan_applications` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `loan_approvals_ibfk_2` FOREIGN KEY (`approver_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `loan_approval_users`
--
ALTER TABLE `loan_approval_users`
  ADD CONSTRAINT `loan_approval_users_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `loan_approval_users_ibfk_2` FOREIGN KEY (`role_id`) REFERENCES `loan_approval_roles` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `otp_logs`
--
ALTER TABLE `otp_logs`
  ADD CONSTRAINT `otp_logs_ibfk_1` FOREIGN KEY (`application_id`) REFERENCES `loan_applications` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `payments_ibfk_1` FOREIGN KEY (`contract_id`) REFERENCES `contracts` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `payments_ibfk_2` FOREIGN KEY (`payment_type_id`) REFERENCES `payment_types` (`id`),
  ADD CONSTRAINT `payments_ibfk_3` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `payment_schedule`
--
ALTER TABLE `payment_schedule`
  ADD CONSTRAINT `payment_schedule_ibfk_1` FOREIGN KEY (`contract_id`) REFERENCES `contracts` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `system_settings`
--
ALTER TABLE `system_settings`
  ADD CONSTRAINT `system_settings_ibfk_1` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `user_departments`
--
ALTER TABLE `user_departments`
  ADD CONSTRAINT `user_departments_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `user_departments_ibfk_2` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `waiver_documents`
--
ALTER TABLE `waiver_documents`
  ADD CONSTRAINT `waiver_documents_ibfk_1` FOREIGN KEY (`application_id`) REFERENCES `interest_waiver_applications` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `waiver_documents_ibfk_2` FOREIGN KEY (`uploaded_by`) REFERENCES `users` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
