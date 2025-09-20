-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Sep 17, 2025 at 01:44 PM
-- Server version: 9.1.0
-- PHP Version: 8.3.14

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `core2`
--

-- --------------------------------------------------------

--
-- Table structure for table `alerts`
--

DROP TABLE IF EXISTS `alerts`;
CREATE TABLE IF NOT EXISTS `alerts`   (
  `id`   int NOT NULL AUTO_INCREMENT,
  `type`   enum('traffic','maintenance','incident','weather','emergency') NOT NULL,
  `severity`   enum('low','medium','high','critical') NOT NULL DEFAULT 'medium',
  `title`   varchar(255) NOT NULL,
  `message`   text NOT NULL,
  `latitude`   decimal(10,8) DEFAULT NULL,
  `longitude`   decimal(11,8) DEFAULT NULL,
  `status`   enum('active','resolved','dismissed') NOT NULL DEFAULT 'active',
  `created_by`   int UNSIGNED DEFAULT NULL,
  `created_at`   timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`   timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `resolved_at`   timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`  ),
  KEY `created_by`   (`created_by`  ),
  KEY `idx_alerts_status_created`   (`status`  ,`created_at`  )
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cargo`
--

DROP TABLE IF EXISTS `cargo`;
CREATE TABLE IF NOT EXISTS `cargo` (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `cargo_code` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `service_id` int UNSIGNED NOT NULL,
  `item_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `item_type` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `quantity` decimal(10,2) NOT NULL,
  `unit` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `weight` decimal(10,2) DEFAULT NULL COMMENT 'in kg',
  `volume` decimal(10,2) DEFAULT NULL COMMENT 'in cubic meters',
  `value` decimal(12,2) DEFAULT NULL COMMENT 'cargo value',
  `dangerous_goods` tinyint(1) NOT NULL DEFAULT '0',
  `special_instructions` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `loading_point` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `unloading_point` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `loaded_at` timestamp NULL DEFAULT NULL,
  `unloaded_at` timestamp NULL DEFAULT NULL,
  `status` enum('pending','loaded','in_transit','delivered','damaged','lost') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `recipient_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `recipient_phone` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sender_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sender_phone` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tracking_number` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `notes` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `cargo_code` (`cargo_code`),
  KEY `idx_service_id` (`service_id`),
  KEY `idx_status` (`status`),
  KEY `idx_tracking_number` (`tracking_number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `csrf_tokens`
--

DROP TABLE IF EXISTS `csrf_tokens`;
CREATE TABLE IF NOT EXISTS `csrf_tokens` (
  `id` int NOT NULL AUTO_INCREMENT,
  `token` varchar(64) NOT NULL,
  `session_id` varchar(128) NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `expires_at` timestamp NOT NULL,
  `used` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `token` (`token`),
  KEY `idx_token` (`token`),
  KEY `idx_session` (`session_id`),
  KEY `idx_expires` (`expires_at`)
) ENGINE=MyISAM AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `csrf_tokens`
--

INSERT INTO `csrf_tokens` (`id`, `token`, `session_id`, `created_at`, `expires_at`, `used`) VALUES
(11, '15478aa272ad2e111b7d8a6fde483ce4563888603440bc3eb87eb9e0bee44897', 'ja0pk9khvip6eknbhafrkg2hkb', '2025-09-17 11:47:47', '2025-09-17 04:47:47', 0);

-- --------------------------------------------------------

--
-- Table structure for table `drivers`
--

DROP TABLE IF EXISTS `drivers`;
CREATE TABLE IF NOT EXISTS `drivers` (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `driver_code` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `provider_id` int UNSIGNED NOT NULL,
  `first_name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `license_number` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `license_type` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `license_expiry` date NOT NULL,
  `medical_certificate` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `medical_expiry` date DEFAULT NULL,
  `experience_years` int UNSIGNED DEFAULT NULL,
  `rating` decimal(3,2) DEFAULT NULL COMMENT '1-5 rating',
  `total_ratings` int UNSIGNED NOT NULL DEFAULT '0',
  `emergency_contact_name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `emergency_contact_phone` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` enum('active','inactive','suspended','on_leave') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `assigned_vehicle_id` int UNSIGNED DEFAULT NULL,
  `last_trip_date` date DEFAULT NULL,
  `total_trips` int UNSIGNED NOT NULL DEFAULT '0',
  `notes` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `driver_code` (`driver_code`),
  UNIQUE KEY `license_number` (`license_number`),
  KEY `assigned_vehicle_id` (`assigned_vehicle_id`),
  KEY `idx_provider_id` (`provider_id`),
  KEY `idx_license_number` (`license_number`),
  KEY `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `email_logs`
--

DROP TABLE IF EXISTS `email_logs`;
CREATE TABLE IF NOT EXISTS `email_logs` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int DEFAULT NULL,
  `email` varchar(255) NOT NULL,
  `email_type` enum('verification','password_reset','notification') NOT NULL,
  `subject` varchar(255) DEFAULT NULL,
  `status` enum('sent','failed','pending') DEFAULT 'pending',
  `error_message` text,
  `sent_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_email` (`email`(250)),
  KEY `idx_email_type` (`email_type`),
  KEY `idx_status` (`status`),
  KEY `idx_created_at` (`created_at`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `email_logs`
--

INSERT INTO `email_logs` (`id`, `user_id`, `email`, `email_type`, `subject`, `status`, `error_message`, `sent_at`, `created_at`) VALUES
(1, 36, 'bathanjc23@gmail.com', 'verification', 'Email Verification - SLATE System', 'failed', NULL, NULL, '2025-09-17 11:08:28');

-- --------------------------------------------------------

--
-- Table structure for table `email_verifications`
--

DROP TABLE IF EXISTS `email_verifications`;
CREATE TABLE IF NOT EXISTS `email_verifications` (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` int UNSIGNED NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` enum('registration','login') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'registration',
  `expires_at` timestamp NOT NULL,
  `verified_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `token` (`token`),
  KEY `idx_token` (`token`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_expires_at` (`expires_at`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `email_verifications`
--

INSERT INTO `email_verifications` (`id`, `user_id`, `email`, `token`, `type`, `expires_at`, `verified_at`, `created_at`) VALUES
(1, 36, 'bathanjc23@gmail.com', 'ee676944bccc18924741931d35a902fd6058269f52c5ee1823931765864e202f', 'registration', '2025-09-18 03:08:26', NULL, '2025-09-17 11:08:26');

-- --------------------------------------------------------

--
-- Table structure for table `login_attempts`
--

DROP TABLE IF EXISTS `login_attempts`;
CREATE TABLE IF NOT EXISTS `login_attempts` (
  `id` int NOT NULL AUTO_INCREMENT,
  `ip_address` varchar(45) NOT NULL,
  `username` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `attempt_time` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `success` tinyint(1) DEFAULT '0',
  `failure_reason` varchar(255) DEFAULT NULL,
  `user_agent` text,
  PRIMARY KEY (`id`),
  KEY `idx_ip_address` (`ip_address`),
  KEY `idx_attempt_time` (`attempt_time`),
  KEY `idx_username` (`username`(250)),
  KEY `idx_email` (`email`(250))
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `login_logs`
--

DROP TABLE IF EXISTS `login_logs`;
CREATE TABLE IF NOT EXISTS `login_logs` (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `username` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ip_address` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_agent` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `success` tinyint(1) NOT NULL DEFAULT '0',
  `failure_reason` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `device_fingerprint` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `location_country` varchar(2) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `location_city` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `suspicious_flags` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_username` (`username`),
  KEY `idx_ip_address` (`ip_address`),
  KEY `idx_success` (`success`),
  KEY `idx_created_at` (`created_at`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `login_logs`
--

INSERT INTO `login_logs` (`id`, `username`, `ip_address`, `user_agent`, `success`, `failure_reason`, `device_fingerprint`, `location_country`, `location_city`, `suspicious_flags`, `created_at`) VALUES
(1, 'admin', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', 0, NULL, NULL, NULL, NULL, NULL, '2025-09-17 13:28:07'),
(2, 'admin', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', 1, NULL, NULL, NULL, NULL, NULL, '2025-09-17 13:28:18'),
(3, 'admin', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', 1, NULL, NULL, NULL, NULL, NULL, '2025-09-17 13:28:31');

-- --------------------------------------------------------

--
-- Table structure for table `password_reset_tokens`
--

DROP TABLE IF EXISTS `password_reset_tokens`;
CREATE TABLE IF NOT EXISTS `password_reset_tokens` (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `username` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `expires_at` datetime NOT NULL,
  `used` tinyint(1) NOT NULL DEFAULT '0',
  `used_at` datetime DEFAULT NULL,
  `ip_address` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `token` (`token`),
  KEY `idx_username` (`username`),
  KEY `idx_expires_at` (`expires_at`),
  KEY `idx_used` (`used`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `providers`
--

DROP TABLE IF EXISTS `providers`;
CREATE TABLE IF NOT EXISTS `providers` (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `contact_person` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `contact_email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `contact_phone` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `service_area` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `monthly_rate` decimal(10,2) NOT NULL DEFAULT '0.00',
  `status` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `contract_start` date NOT NULL,
  `contract_end` date NOT NULL,
  `notes` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `providers`
--

INSERT INTO `providers` (`id`, `name`, `type`, `contact_person`, `contact_email`, `contact_phone`, `service_area`, `monthly_rate`, `status`, `contract_start`, `contract_end`, `notes`, `created_at`, `updated_at`) VALUES
(3, 'RORO', 'Express', 'roro', 'roro@gmail.com', '09213531232', 'Manila', 999.00, 'Pending', '2025-09-03', '2026-09-03', 'qasd', '2025-09-03 08:01:07', '2025-09-03 08:01:07'),
(4, 'Test Transport Provider', 'Transport', 'John Doe', 'provider@test.com', '555-0123', 'Metro Area', 25000.00, 'Active', '2025-09-12', '2026-09-12', 'Test provider for dashboard', '2025-09-12 10:49:16', '2025-09-12 10:49:16');

-- --------------------------------------------------------

--
-- Table structure for table `request_status_history`
--

DROP TABLE IF EXISTS `request_status_history`;
CREATE TABLE IF NOT EXISTS `request_status_history` (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `request_id` int UNSIGNED NOT NULL,
  `previous_status` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `new_status` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `changed_by_user_id` int UNSIGNED DEFAULT NULL,
  `changed_by_role` enum('user','admin','provider') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `comments` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `changed_by_user_id` (`changed_by_user_id`),
  KEY `idx_request_id` (`request_id`),
  KEY `idx_created_at` (`created_at`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `request_status_history`
--

INSERT INTO `request_status_history` (`id`, `request_id`, `previous_status`, `new_status`, `changed_by_user_id`, `changed_by_role`, `comments`, `created_at`) VALUES
(1, 1, NULL, 'pending', 25, 'user', 'Request created', '2025-09-16 04:54:48'),
(2, 2, NULL, 'pending', 25, 'user', 'Request created', '2025-09-16 04:57:37');

-- --------------------------------------------------------

--
-- Table structure for table `routes`
--

DROP TABLE IF EXISTS `routes`;
CREATE TABLE IF NOT EXISTS `routes` (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `start_point` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `end_point` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `distance` decimal(10,1) NOT NULL DEFAULT '0.0',
  `frequency` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `estimated_time` int NOT NULL,
  `notes` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `provider_id` int UNSIGNED DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_routes_provider` (`provider_id`)
) ENGINE=InnoDB AUTO_INCREMENT=25 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `routes`
--

INSERT INTO `routes` (`id`, `name`, `type`, `start_point`, `end_point`, `distance`, `frequency`, `status`, `estimated_time`, `notes`, `created_at`, `updated_at`, `provider_id`) VALUES
(11, 'Bus Route 101', 'Secondary', 'City Center', 'Airport', 18.2, 'Every 30 min', 'Active', 35, 'Direct bus service to airport', '2025-09-04 03:53:35', '2025-09-04 03:53:35', NULL),
(12, 'Express Route X1', 'Express', 'Downtown', 'Business District', 12.8, 'Every hour', 'Active', 20, 'Express service for business commuters', '2025-09-04 03:53:35', '2025-09-04 03:53:35', NULL),
(13, 'Local Route L5', 'Local', 'Residential Area A', 'Shopping Mall', 8.4, 'Every 2 hours', 'Active', 25, 'Local service for residential areas', '2025-09-04 03:53:35', '2025-09-04 03:53:35', NULL),
(14, 'Metro Line B', 'Primary', 'East Terminal', 'West Terminal', 22.1, 'Every 15 min', 'Maintenance', 40, 'Secondary metro line under maintenance', '2025-09-04 03:53:35', '2025-09-04 03:53:35', NULL),
(15, 'Bus Route 202', 'Secondary', 'University', 'City Center', 15.6, 'Every 30 min', 'Planned', 30, 'New route serving university area', '2025-09-04 03:53:35', '2025-09-04 03:53:35', NULL),
(16, 'Provider Route A', 'Primary', 'Downtown', 'Airport', 15.5, 'Every 20 min', 'Active', 35, 'Main route for provider', '2025-09-12 10:49:16', '2025-09-12 10:49:16', NULL),
(17, 'Provider Route B', 'Secondary', 'Mall', 'University', 8.2, 'Every 45 min', 'Active', 25, 'Secondary route for provider', '2025-09-12 10:49:16', '2025-09-12 10:49:16', NULL),
(18, 'Provider Route C', 'Express', 'Business District', 'Residential Area', 12.1, 'Every hour', 'Planned', 30, 'Planned express route', '2025-09-12 10:49:16', '2025-09-12 10:49:16', NULL),
(19, 'Metro Line A', 'Primary', 'North Terminal', 'South Terminal', 25.5, 'Every 15 min', 'Active', 45, 'Main metro line connecting north and south districts', '2025-09-12 10:49:17', '2025-09-12 10:49:17', NULL),
(20, 'Bus Route 101', 'Secondary', 'City Center', 'Airport', 18.2, 'Every 30 min', 'Active', 35, 'Direct bus service to airport', '2025-09-12 10:49:17', '2025-09-12 10:49:17', NULL),
(21, 'Express Route X1', 'Express', 'Downtown', 'Business District', 12.8, 'Every hour', 'Active', 20, 'Express service for business commuters', '2025-09-12 10:49:17', '2025-09-12 10:49:17', NULL),
(22, 'Local Route L5', 'Local', 'Residential Area A', 'Shopping Mall', 8.4, 'Every 2 hours', 'Active', 25, 'Local service for residential areas', '2025-09-12 10:49:17', '2025-09-12 10:49:17', NULL),
(23, 'Metro Line B', 'Primary', 'East Terminal', 'West Terminal', 22.1, 'Every 15 min', 'Maintenance', 40, 'Secondary metro line under maintenance', '2025-09-12 10:49:17', '2025-09-12 10:49:17', NULL),
(24, 'Bus Route 202', 'Secondary', 'University', 'City Center', 15.6, 'Every 30 min', 'Planned', 30, 'New route serving university area', '2025-09-12 10:49:17', '2025-09-12 10:49:17', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `route_coordinates`
--

DROP TABLE IF EXISTS `route_coordinates`;
CREATE TABLE IF NOT EXISTS `route_coordinates` (
  `id` int NOT NULL AUTO_INCREMENT,
  `route_id` int UNSIGNED NOT NULL,
  `coordinates` text NOT NULL COMMENT 'JSON array of lat/lng coordinates',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_route` (`route_id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `route_coordinates`
--

INSERT INTO `route_coordinates` (`id`, `route_id`, `coordinates`, `created_at`, `updated_at`) VALUES
(1, 11, '[[14.5995,120.9842],[14.6042,120.9917],[14.6158,121.0117],[14.6378,121.0342]]', '2025-09-12 10:49:17', '2025-09-12 10:49:17'),
(2, 12, '[[14.5995,120.9842],[14.6042,120.9917],[14.6158,121.0117],[14.6378,121.0342]]', '2025-09-12 10:49:17', '2025-09-12 10:49:17'),
(3, 13, '[[14.5995,120.9842],[14.6042,120.9917],[14.6158,121.0117],[14.6378,121.0342]]', '2025-09-12 10:49:17', '2025-09-12 10:49:17'),
(4, 14, '[[14.5995,120.9842],[14.6042,120.9917],[14.6158,121.0117],[14.6378,121.0342]]', '2025-09-12 10:49:17', '2025-09-12 10:49:17'),
(5, 15, '[[14.5995,120.9842],[14.6042,120.9917],[14.6158,121.0117],[14.6378,121.0342]]', '2025-09-12 10:49:17', '2025-09-12 10:49:17'),
(6, 16, '[[14.5995,120.9842],[14.6042,120.9917],[14.6158,121.0117],[14.6378,121.0342]]', '2025-09-12 10:49:17', '2025-09-12 10:49:17'),
(7, 17, '[[14.5995,120.9842],[14.6042,120.9917],[14.6158,121.0117],[14.6378,121.0342]]', '2025-09-12 10:49:17', '2025-09-12 10:49:17'),
(8, 18, '[[14.5995,120.9842],[14.6042,120.9917],[14.6158,121.0117],[14.6378,121.0342]]', '2025-09-12 10:49:17', '2025-09-12 10:49:17');

-- --------------------------------------------------------

--
-- Table structure for table `schedules`
--

DROP TABLE IF EXISTS `schedules`;
CREATE TABLE IF NOT EXISTS `schedules` (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `route` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `vehicle_type` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `departure` time NOT NULL,
  `arrival` time NOT NULL,
  `frequency` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `capacity` int NOT NULL,
  `notes` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `provider_id` int UNSIGNED DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_schedules_provider` (`provider_id`)
) ENGINE=InnoDB AUTO_INCREMENT=20 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `schedules`
--

INSERT INTO `schedules` (`id`, `name`, `route`, `vehicle_type`, `departure`, `arrival`, `frequency`, `status`, `start_date`, `end_date`, `capacity`, `notes`, `created_at`, `updated_at`, `provider_id`) VALUES
(1, 'asd', 'East-West Connector', 'Bus', '15:40:00', '04:41:00', 'Weekdays', 'Maintenance', '2025-09-07', '2025-09-12', 12, 'asd', '2025-08-15 07:40:33', '2025-09-12 10:49:16', NULL),
(14, '123', 'North-South Express', 'Metro', '12:17:00', '00:17:00', 'Daily', 'Active', '2025-09-08', '2025-09-13', 5, 'asd', '2025-09-06 04:17:54', '2025-09-08 03:31:09', NULL),
(17, 'Morning Express', 'Provider Route A', 'Bus', '06:00:00', '06:35:00', 'Daily', 'Active', '2025-09-12', '2026-03-12', 45, 'Morning commuter service', '2025-09-12 10:49:16', '2025-09-12 10:49:16', NULL),
(18, 'Evening Return', 'Provider Route A', 'Bus', '17:30:00', '18:05:00', 'Daily', 'Active', '2025-09-12', '2026-03-12', 45, 'Evening return service', '2025-09-12 10:49:16', '2025-09-12 10:49:16', NULL),
(19, 'Weekend Special', 'Provider Route B', 'Minivan', '09:00:00', '09:25:00', 'Weekends', 'Active', '2025-09-12', '2025-12-12', 25, 'Weekend shopping service', '2025-09-12 10:49:16', '2025-09-12 10:49:16', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `security_events`
--

DROP TABLE IF EXISTS `security_events`;
CREATE TABLE IF NOT EXISTS `security_events` (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `event_type` enum('login_attempt','password_change','account_lockout','suspicious_activity','session_hijack','brute_force') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `severity` enum('low','medium','high','critical') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'medium',
  `username` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ip_address` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_agent` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `additional_data` json DEFAULT NULL,
  `resolved` tinyint(1) NOT NULL DEFAULT '0',
  `resolved_at` datetime DEFAULT NULL,
  `resolved_by` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_event_type` (`event_type`),
  KEY `idx_severity` (`severity`),
  KEY `idx_username` (`username`),
  KEY `idx_ip_address` (`ip_address`),
  KEY `idx_created_at` (`created_at`),
  KEY `idx_resolved` (`resolved`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `security_logs`
--

DROP TABLE IF EXISTS `security_logs`;
CREATE TABLE IF NOT EXISTS `security_logs` (
  `id` int NOT NULL AUTO_INCREMENT,
  `event_type` varchar(50) NOT NULL,
  `ip_address` varchar(45) NOT NULL,
  `username` varchar(255) DEFAULT NULL,
  `description` text,
  `severity` enum('LOW','MEDIUM','HIGH','CRITICAL') DEFAULT 'MEDIUM',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_event_time` (`event_type`,`created_at`),
  KEY `idx_ip_time` (`ip_address`,`created_at`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `security_logs`
--

INSERT INTO `security_logs` (`id`, `event_type`, `ip_address`, `username`, `description`, `severity`, `created_at`) VALUES
(1, 'CSRF_FAILURE', '::1', '', 'CSRF token validation failed', 'HIGH', '2025-09-17 11:35:14'),
(2, 'CSRF_FAILURE', '::1', '', 'CSRF token validation failed', 'HIGH', '2025-09-17 11:35:27');

-- --------------------------------------------------------

--
-- Table structure for table `services`
--

DROP TABLE IF EXISTS `services`;
CREATE TABLE IF NOT EXISTS `services` (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `service_code` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `provider_id` int UNSIGNED NOT NULL,
  `vehicle_id` int UNSIGNED DEFAULT NULL,
  `driver_id` int UNSIGNED DEFAULT NULL,
  `service_type` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `transport_mode` enum('truck','motor','boat','ship','bus','van','motorcycle') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `route` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `origin` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `origin_lat` decimal(10,8) DEFAULT NULL,
  `origin_lng` decimal(11,8) DEFAULT NULL,
  `destination` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `destination_lat` decimal(10,8) DEFAULT NULL,
  `destination_lng` decimal(11,8) DEFAULT NULL,
  `distance_km` decimal(10,2) DEFAULT NULL,
  `capacity` int UNSIGNED NOT NULL DEFAULT '0',
  `capacity_weight` decimal(10,2) DEFAULT NULL COMMENT 'in kg',
  `capacity_volume` decimal(10,2) DEFAULT NULL COMMENT 'in cubic meters',
  `capacity_passengers` int UNSIGNED DEFAULT NULL,
  `current_load_weight` decimal(10,2) NOT NULL DEFAULT '0.00',
  `current_passengers` int UNSIGNED NOT NULL DEFAULT '0',
  `status` enum('active','inactive','pending','maintenance','completed') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `scheduled_start` datetime DEFAULT NULL,
  `scheduled_end` datetime DEFAULT NULL,
  `actual_start` datetime DEFAULT NULL,
  `actual_end` datetime DEFAULT NULL,
  `estimated_duration` int UNSIGNED DEFAULT NULL COMMENT 'in minutes',
  `base_fare` decimal(10,2) NOT NULL DEFAULT '0.00',
  `per_km_rate` decimal(10,2) NOT NULL DEFAULT '0.00',
  `per_weight_rate` decimal(10,2) NOT NULL DEFAULT '0.00',
  `fuel_surcharge` decimal(10,2) NOT NULL DEFAULT '0.00',
  `total_fare` decimal(10,2) NOT NULL DEFAULT '0.00',
  `revenue` decimal(10,2) NOT NULL DEFAULT '0.00',
  `fuel_consumed` decimal(8,2) DEFAULT NULL COMMENT 'liters',
  `current_location_lat` decimal(10,8) DEFAULT NULL,
  `current_location_lng` decimal(11,8) DEFAULT NULL,
  `last_location_update` timestamp NULL DEFAULT NULL,
  `rating` decimal(3,2) DEFAULT NULL COMMENT 'average rating 1-5',
  `total_ratings` int UNSIGNED NOT NULL DEFAULT '0',
  `notes` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `service_data` json DEFAULT NULL COMMENT 'additional service specific data',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `service_code` (`service_code`),
  KEY `idx_provider_id` (`provider_id`),
  KEY `idx_status` (`status`),
  KEY `idx_service_type` (`service_type`),
  KEY `idx_vehicle_id` (`vehicle_id`),
  KEY `idx_driver_id` (`driver_id`),
  KEY `idx_transport_mode` (`transport_mode`),
  KEY `idx_scheduled_start` (`scheduled_start`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `service_history`
--

DROP TABLE IF EXISTS `service_history`;
CREATE TABLE IF NOT EXISTS `service_history` (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `service_id` int UNSIGNED NOT NULL,
  `action` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'started, completed, cancelled, etc',
  `previous_status` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `new_status` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `passenger_count` int UNSIGNED DEFAULT NULL,
  `cargo_weight` decimal(10,2) DEFAULT NULL,
  `revenue_amount` decimal(10,2) DEFAULT NULL,
  `location_lat` decimal(10,8) DEFAULT NULL,
  `location_lng` decimal(11,8) DEFAULT NULL,
  `notes` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `action_by_user_id` int UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `action_by_user_id` (`action_by_user_id`),
  KEY `idx_service_id` (`service_id`),
  KEY `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `service_points`
--

DROP TABLE IF EXISTS `service_points`;
CREATE TABLE IF NOT EXISTS `service_points` (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `location` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `services` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `notes` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `provider_id` int UNSIGNED DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_service_points_provider` (`provider_id`)
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `service_points`
--

INSERT INTO `service_points` (`id`, `name`, `type`, `location`, `services`, `status`, `notes`, `created_at`, `updated_at`, `provider_id`) VALUES
(3, 'Central Hub Station', 'Transport Hub', 'City Center, Main Plaza', 'Bus Terminal, Metro Access, Ticketing', 'Active', 'Main transportation hub serving multiple routes', '2025-09-04 03:53:35', '2025-09-12 10:49:16', NULL),
(4, 'North Terminal', 'Terminal', 'North District, Terminal Road', 'Bus Services, Parking, Waiting Area', 'Active', 'Primary terminal for northern routes', '2025-09-04 03:53:35', '2025-09-04 03:53:35', NULL),
(5, 'Airport Transfer Point', 'Transfer Point', 'Airport Access Road', 'Airport Shuttle, Taxi Stand', 'Active', 'Connection point for airport services', '2025-09-04 03:53:35', '2025-09-04 03:53:35', NULL),
(6, 'Business District Station', 'Station', 'Business District, Corporate Ave', 'Metro Platform, Express Services', 'Active', 'Serves business district with express connections', '2025-09-04 03:53:35', '2025-09-04 03:53:35', NULL),
(7, 'University Hub', 'Station', 'University Campus, Education Blvd', 'Student Services, Multiple Bus Lines', 'Planned', 'New service point under construction', '2025-09-04 03:53:35', '2025-09-04 03:53:35', NULL),
(8, 'Maintenance Depot', 'Depot', 'Industrial Area, Service Road', 'Vehicle Maintenance, Storage', 'Active', 'Central maintenance facility for all routes', '2025-09-04 03:53:35', '2025-09-04 03:53:35', NULL),
(9, 'East Terminal', 'Terminal', 'East District, Harbor View', 'Ferry Connection, Bus Terminal', 'Maintenance', 'Currently undergoing renovation', '2025-09-04 03:53:35', '2025-09-04 03:53:35', NULL),
(11, 'Provider Hub A', 'Station', 'Downtown Terminal, Main Street', 'Ticketing, Information, Parking', 'Active', 'Main service hub for provider', '2025-09-12 10:49:16', '2025-09-12 10:49:16', NULL),
(12, 'Provider Hub B', 'Stop', 'Mall Entrance, Shopping District', 'Basic Services, Shelter', 'Active', 'Shopping district service point', '2025-09-12 10:49:16', '2025-09-12 10:49:16', NULL),
(13, 'Provider Depot', 'Depot', 'Industrial Park, Service Road', 'Maintenance, Storage, Dispatch', 'Active', 'Provider maintenance facility', '2025-09-12 10:49:16', '2025-09-12 10:49:16', NULL),
(14, 'Central Hub Station', 'Transport Hub', 'City Center, Main Plaza', 'Bus Terminal, Metro Access, Ticketing', 'Active', 'Main transportation hub serving multiple routes', '2025-09-12 10:49:17', '2025-09-12 10:49:17', NULL),
(15, 'North Terminal', 'Terminal', 'North District, Terminal Road', 'Bus Services, Parking, Waiting Area', 'Active', 'Primary terminal for northern routes', '2025-09-12 10:49:17', '2025-09-12 10:49:17', NULL),
(16, 'Airport Transfer Point', 'Transfer Point', 'Airport Access Road', 'Airport Shuttle, Taxi Stand', 'Active', 'Connection point for airport services', '2025-09-12 10:49:17', '2025-09-12 10:49:17', NULL),
(17, 'Business District Station', 'Station', 'Business District, Corporate Ave', 'Metro Platform, Express Services', 'Active', 'Serves business district with express connections', '2025-09-12 10:49:17', '2025-09-12 10:49:17', NULL),
(18, 'University Hub', 'Station', 'University Campus, Education Blvd', 'Student Services, Multiple Bus Lines', 'Planned', 'New service point under construction', '2025-09-12 10:49:17', '2025-09-12 10:49:17', NULL),
(19, 'Maintenance Depot', 'Depot', 'Industrial Area, Service Road', 'Vehicle Maintenance, Storage', 'Active', 'Central maintenance facility for all routes', '2025-09-12 10:49:17', '2025-09-12 10:49:17', NULL),
(20, 'East Terminal', 'Terminal', 'East District, Harbor View', 'Ferry Connection, Bus Terminal', 'Maintenance', 'Currently undergoing renovation', '2025-09-12 10:49:17', '2025-09-12 10:49:17', NULL),
(21, 'Shopping Mall Stop', 'Station', 'Commercial District, Mall Entrance', 'Shopping Access, Local Routes', 'Active', 'Popular stop serving major shopping center', '2025-09-12 10:49:17', '2025-09-12 10:49:17', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `service_point_coordinates`
--

DROP TABLE IF EXISTS `service_point_coordinates`;
CREATE TABLE IF NOT EXISTS `service_point_coordinates` (
  `id` int NOT NULL AUTO_INCREMENT,
  `service_point_id` int UNSIGNED NOT NULL,
  `latitude` decimal(10,8) NOT NULL,
  `longitude` decimal(11,8) NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_service_point` (`service_point_id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `service_point_coordinates`
--

INSERT INTO `service_point_coordinates` (`id`, `service_point_id`, `latitude`, `longitude`, `created_at`, `updated_at`) VALUES
(1, 3, 14.59950000, 120.98420000, '2025-09-12 10:49:17', '2025-09-12 10:49:17'),
(2, 4, 14.59950000, 120.98420000, '2025-09-12 10:49:17', '2025-09-12 10:49:17'),
(3, 5, 14.59950000, 120.98420000, '2025-09-12 10:49:17', '2025-09-12 10:49:17'),
(4, 6, 14.59950000, 120.98420000, '2025-09-12 10:49:17', '2025-09-12 10:49:17'),
(5, 7, 14.59950000, 120.98420000, '2025-09-12 10:49:17', '2025-09-12 10:49:17'),
(6, 8, 14.59950000, 120.98420000, '2025-09-12 10:49:17', '2025-09-12 10:49:17'),
(7, 9, 14.59950000, 120.98420000, '2025-09-12 10:49:17', '2025-09-12 10:49:17'),
(8, 11, 14.59950000, 120.98420000, '2025-09-12 10:49:17', '2025-09-12 10:49:17'),
(9, 12, 14.59950000, 120.98420000, '2025-09-12 10:49:17', '2025-09-12 10:49:17'),
(10, 13, 14.59950000, 120.98420000, '2025-09-12 10:49:17', '2025-09-12 10:49:17');

-- --------------------------------------------------------

--
-- Table structure for table `session_security`
--

DROP TABLE IF EXISTS `session_security`;
CREATE TABLE IF NOT EXISTS `session_security` (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `session_id` varchar(128) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `username` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `ip_address` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_agent` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `device_fingerprint` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `last_activity` datetime NOT NULL,
  `expires_at` datetime NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `session_id` (`session_id`),
  KEY `idx_username` (`username`),
  KEY `idx_ip_address` (`ip_address`),
  KEY `idx_is_active` (`is_active`),
  KEY `idx_expires_at` (`expires_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `shipments`
--

DROP TABLE IF EXISTS `shipments`;
CREATE TABLE IF NOT EXISTS `shipments` (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `waybill` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `customer_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `contact_phone` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `contact_email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `provider_id` int UNSIGNED DEFAULT NULL,
  `route_id` int UNSIGNED DEFAULT NULL,
  `origin` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `destination` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `current_location` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `current_lat` decimal(10,6) DEFAULT NULL,
  `current_lng` decimal(10,6) DEFAULT NULL,
  `status` enum('Created','In Transit','At Hub','Out for Delivery','Delivered','Delayed','Exception','Cancelled') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Created',
  `priority` enum('Standard','Express','Overnight') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Standard',
  `weight_kg` decimal(10,2) DEFAULT NULL,
  `volume_cbm` decimal(10,3) DEFAULT NULL,
  `pickup_date` datetime DEFAULT NULL,
  `eta` datetime DEFAULT NULL,
  `delivered_at` datetime DEFAULT NULL,
  `notes` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `waybill` (`waybill`),
  KEY `idx_provider_id` (`provider_id`),
  KEY `idx_route_id` (`route_id`),
  KEY `idx_status` (`status`),
  KEY `idx_eta` (`eta`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sops`
--

DROP TABLE IF EXISTS `sops`;
CREATE TABLE IF NOT EXISTS `sops` (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `category` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `department` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `version` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `review_date` date NOT NULL,
  `purpose` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `scope` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `responsibilities` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `procedures` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `equipment` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `safety_notes` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `notes` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `sops`
--

INSERT INTO `sops` (`id`, `title`, `category`, `department`, `version`, `status`, `review_date`, `purpose`, `scope`, `responsibilities`, `procedures`, `equipment`, `safety_notes`, `notes`, `created_at`, `updated_at`) VALUES
(1, 'asd', 'Safety', 'Operations', '1.0', 'Approved', '2025-08-23', 'ad', 'adasdads', 'asdasd', 'asdasd', 'asdasd', 'asdasd', 'asdasda', '2025-08-15 07:32:06', '2025-09-03 00:46:32');

-- --------------------------------------------------------

--
-- Table structure for table `tariffs`
--

DROP TABLE IF EXISTS `tariffs`;
CREATE TABLE IF NOT EXISTS `tariffs` (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `category` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `base_rate` decimal(10,2) NOT NULL DEFAULT '0.00',
  `per_km_rate` decimal(10,2) NOT NULL DEFAULT '0.00',
  `per_hour_rate` decimal(10,2) NOT NULL DEFAULT '0.00',
  `priority_multiplier` decimal(4,2) NOT NULL DEFAULT '1.00',
  `status` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Draft',
  `effective_date` date NOT NULL,
  `expiry_date` date NOT NULL,
  `notes` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `source` enum('admin','user_submission') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'admin',
  `source_submission_id` int UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `tariffs`
--

INSERT INTO `tariffs` (`id`, `name`, `category`, `base_rate`, `per_km_rate`, `per_hour_rate`, `priority_multiplier`, `status`, `effective_date`, `expiry_date`, `notes`, `source`, `source_submission_id`, `created_at`, `updated_at`) VALUES
(10, 'JNT', 'Transport', 199.00, 69.00, 120.00, 1.50, 'Active', '2025-09-06', '2026-09-06', 'Auto-created from approved user submission by jc', 'admin', NULL, '2025-09-06 04:47:02', '2025-09-06 04:47:02');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
CREATE TABLE IF NOT EXISTS `users` (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `username` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `password_hash` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `profile_picture` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phone` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `service_area` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `provider_type` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `experience` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `role` enum('admin','user','provider') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'user',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `last_login` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `email_verified` tinyint(1) NOT NULL DEFAULT '0',
  `email_verification_token` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email_verification_sent_at` timestamp NULL DEFAULT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `require_email_verification` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB AUTO_INCREMENT=37 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password_hash`, `email`, `name`, `profile_picture`, `phone`, `service_area`, `provider_type`, `experience`, `description`, `role`, `is_active`, `last_login`, `created_at`, `updated_at`, `email_verified`, `email_verification_token`, `email_verification_sent_at`, `email_verified_at`, `require_email_verification`) VALUES
(1, 'admin', '$2y$10$Ag.3ztDb5HbRgr1pG.9dV.FXVlgYKFySn8N/LjeC70z9gR0FPHziK', 'admin@slate.com', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'admin', 1, '2025-09-17 09:32:17', '2025-08-15 13:28:14', '2025-09-17 09:32:17', 1, NULL, NULL, '2025-09-17 07:57:25', 1),
(25, 'jc', '$2y$10$seGiCQupt1Z1eEG/Fslxu.mYbG0kionuzgad2hqU4CNtrtbfgxkJm', 'jc@gmail.com', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'user', 1, '2025-09-16 05:51:54', '2025-09-03 07:34:23', '2025-09-17 07:57:25', 1, NULL, NULL, '2025-09-17 07:57:25', 1),
(26, 'bat', '$2y$10$9AbFMGAPVOgb7NbG.OYcdON.GcWMDs8o9F6zR6zmMnHNUus8lOXby', 'bathanronald19@gmail.com', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'user', 1, '2025-09-03 07:40:36', '2025-09-03 07:40:25', '2025-09-17 07:57:25', 1, NULL, NULL, '2025-09-17 07:57:25', 1),
(29, 'slate', '$2y$10$Ds8X0oXoHOexqXdrqrbCr..xbo33dOnfDXWLpGTYvj1GjVQ.9SOqq', 'slate@gmail.com', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'user', 1, '2025-09-03 08:00:02', '2025-09-03 07:59:48', '2025-09-17 07:57:25', 1, NULL, NULL, '2025-09-17 07:57:25', 1),
(35, 'jcbat', '$2y$10$bQEkFXk.ZDyRLsaQP0HnhOAtxnnJfDmNVEFJHkoT29B9vfgU9.wSe', 'bathanjc160@gmail.com', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'user', 1, '2025-09-17 11:26:54', '2025-09-17 07:53:22', '2025-09-17 11:26:54', 1, NULL, NULL, '2025-09-17 07:57:25', 1),
(36, 'jezi', '$2y$10$JPxkcrs9Kjg23qmnqUsioO1xrlu2ueA.k6mmRUqJntQ/vABPaZl0O', 'bathanjc23@gmail.com', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'user', 1, '2025-09-17 11:08:44', '2025-09-17 11:08:26', '2025-09-17 11:08:44', 0, NULL, NULL, NULL, 1);

-- --------------------------------------------------------

--
-- Table structure for table `user_profiles`
--

DROP TABLE IF EXISTS `user_profiles`;
CREATE TABLE IF NOT EXISTS `user_profiles` (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` int UNSIGNED NOT NULL,
  `first_name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `company` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `user_profiles`
--

INSERT INTO `user_profiles` (`id`, `user_id`, `first_name`, `last_name`, `phone`, `company`, `created_at`, `updated_at`) VALUES
(1, 29, 'slate', 'core', '0923121423', 'core2', '2025-09-03 07:59:48', '2025-09-03 07:59:48'),
(2, 35, 'jc', 'bathan', '0912312323', 'BATS', '2025-09-17 07:53:22', '2025-09-17 07:53:22'),
(3, 36, 'jc', 'bathan', '09067123762', 'JEZI', '2025-09-17 11:08:26', '2025-09-17 11:08:26');

-- --------------------------------------------------------

--
-- Table structure for table `user_requests`
--

DROP TABLE IF EXISTS `user_requests`;
CREATE TABLE IF NOT EXISTS `user_requests` (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `request_id` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` int UNSIGNED NOT NULL,
  `service_type` enum('freight','express','warehouse','logistics') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `origin` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `destination` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `cargo_type` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `weight` decimal(10,2) DEFAULT NULL COMMENT 'in kg',
  `volume` decimal(10,2) DEFAULT NULL COMMENT 'in cubic meters',
  `estimated_cost` decimal(10,2) DEFAULT NULL,
  `final_cost` decimal(10,2) DEFAULT NULL,
  `status` enum('pending','processing','completed','cancelled') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `priority` enum('normal','high','urgent') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'normal',
  `provider_id` int UNSIGNED DEFAULT NULL,
  `assigned_at` datetime DEFAULT NULL,
  `started_at` datetime DEFAULT NULL,
  `completed_at` datetime DEFAULT NULL,
  `cancelled_at` datetime DEFAULT NULL,
  `cancel_reason` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `tracking_number` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `estimated_duration` int UNSIGNED DEFAULT NULL COMMENT 'in minutes',
  `actual_duration` int UNSIGNED DEFAULT NULL COMMENT 'in minutes',
  `pickup_address` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `delivery_address` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `special_instructions` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `contact_person` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `contact_phone` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `rating` decimal(3,2) DEFAULT NULL COMMENT '1-5 rating',
  `review_text` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `review_date` datetime DEFAULT NULL,
  `notes` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `request_id` (`request_id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_provider_id` (`provider_id`),
  KEY `idx_status` (`status`),
  KEY `idx_service_type` (`service_type`),
  KEY `idx_created_at` (`created_at`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `user_requests`
--

INSERT INTO `user_requests` (`id`, `request_id`, `user_id`, `service_type`, `title`, `description`, `origin`, `destination`, `cargo_type`, `weight`, `volume`, `estimated_cost`, `final_cost`, `status`, `priority`, `provider_id`, `assigned_at`, `started_at`, `completed_at`, `cancelled_at`, `cancel_reason`, `tracking_number`, `estimated_duration`, `actual_duration`, `pickup_address`, `delivery_address`, `special_instructions`, `contact_person`, `contact_phone`, `rating`, `review_text`, `review_date`, `notes`, `created_at`, `updated_at`) VALUES
(1, 'REQ-2025-2809', 25, 'express', 'sadasd', 'sdsadsad', 'sadasd', 'asdasasd', 'asdasdasd', 11.00, NULL, NULL, NULL, 'pending', 'normal', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'SADASDASD', 'SADASDS', '098217381278', NULL, NULL, NULL, NULL, '2025-09-16 04:54:48', '2025-09-16 04:54:48'),
(2, 'REQ-2025-9980', 25, 'express', 'sadasdads', 'sadasdas', 'asdasd', 'asdasdsad', 'asdasdasd', 12.00, NULL, NULL, NULL, 'pending', 'urgent', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'sadsadasd', '0912321382', NULL, NULL, NULL, NULL, '2025-09-16 04:57:37', '2025-09-16 04:57:37');

-- --------------------------------------------------------

--
-- Table structure for table `user_security`
--

DROP TABLE IF EXISTS `user_security`;
CREATE TABLE IF NOT EXISTS `user_security` (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `username` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_attempts` int UNSIGNED NOT NULL DEFAULT '0',
  `lockout_until` datetime DEFAULT NULL,
  `last_login` datetime DEFAULT NULL,
  `last_failed_login` datetime DEFAULT NULL,
  `password_changed_at` datetime DEFAULT NULL,
  `two_factor_enabled` tinyint(1) NOT NULL DEFAULT '0',
  `two_factor_secret` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `backup_codes` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  KEY `idx_lockout_until` (`lockout_until`),
  KEY `idx_failed_attempts` (`failed_attempts`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `user_security`
--

INSERT INTO `user_security` (`id`, `username`, `failed_attempts`, `lockout_until`, `last_login`, `last_failed_login`, `password_changed_at`, `two_factor_enabled`, `two_factor_secret`, `backup_codes`, `created_at`, `updated_at`) VALUES
(1, 'admin', 0, NULL, '2025-09-17 21:28:31', '2025-09-17 21:28:07', NULL, 0, NULL, NULL, '2025-09-17 13:28:07', '2025-09-17 13:28:31');

-- --------------------------------------------------------

--
-- Table structure for table `user_tariff_submissions`
--

DROP TABLE IF EXISTS `user_tariff_submissions`;
CREATE TABLE IF NOT EXISTS `user_tariff_submissions` (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `category` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `base_rate` decimal(10,2) NOT NULL DEFAULT '0.00',
  `per_km_rate` decimal(10,2) NOT NULL DEFAULT '0.00',
  `per_hour_rate` decimal(10,2) NOT NULL DEFAULT '0.00',
  `priority_multiplier` decimal(4,2) NOT NULL DEFAULT '1.00',
  `service_area` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `justification` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `notes` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `status` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Pending Review',
  `submitted_by_user_id` int UNSIGNED NOT NULL,
  `submitted_by_username` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `reviewed_by_user_id` int UNSIGNED DEFAULT NULL,
  `reviewed_at` timestamp NULL DEFAULT NULL,
  `review_notes` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `submitted_by_user_id` (`submitted_by_user_id`),
  KEY `reviewed_by_user_id` (`reviewed_by_user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `user_tariff_submissions`
--

INSERT INTO `user_tariff_submissions` (`id`, `name`, `category`, `base_rate`, `per_km_rate`, `per_hour_rate`, `priority_multiplier`, `service_area`, `justification`, `notes`, `status`, `submitted_by_user_id`, `submitted_by_username`, `reviewed_by_user_id`, `reviewed_at`, `review_notes`, `created_at`, `updated_at`) VALUES
(1, 'JNT', 'Transport', 199.00, 69.00, 120.00, 1.50, 'Manila', 'adasdads', 'adaddsasd', 'Approved', 25, 'jc', 1, '2025-09-06 04:47:02', 'Approved by administrator', '2025-09-06 04:01:44', '2025-09-06 04:47:02'),
(5, 'LBC', 'Technology', 299.00, 55.00, 120.00, 1.00, 'Bulacan', 'taena', 'asd\n', 'Rejected', 25, 'jc', 1, '2025-09-06 04:46:43', 'asdsad', '2025-09-06 04:15:02', '2025-09-06 04:46:43');

-- --------------------------------------------------------

--
-- Table structure for table `vehicles`
--

DROP TABLE IF EXISTS `vehicles`;
CREATE TABLE IF NOT EXISTS `vehicles` (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `vehicle_code` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `provider_id` int UNSIGNED NOT NULL,
  `vehicle_type` enum('truck','motor','boat','ship','bus','van','motorcycle') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `brand` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `model` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `year` year NOT NULL,
  `license_plate` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `vin_number` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `engine_number` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `fuel_type` enum('petrol','diesel','electric','hybrid','cng','lpg') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `capacity_weight` decimal(10,2) DEFAULT NULL COMMENT 'in kg',
  `capacity_volume` decimal(10,2) DEFAULT NULL COMMENT 'in cubic meters',
  `capacity_passengers` int UNSIGNED DEFAULT NULL,
  `color` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `insurance_number` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `insurance_expiry` date DEFAULT NULL,
  `registration_expiry` date DEFAULT NULL,
  `last_maintenance` date DEFAULT NULL,
  `next_maintenance` date DEFAULT NULL,
  `gps_device_id` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` enum('active','inactive','maintenance','retired') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `current_location_lat` decimal(10,8) DEFAULT NULL,
  `current_location_lng` decimal(11,8) DEFAULT NULL,
  `last_location_update` timestamp NULL DEFAULT NULL,
  `odometer_reading` decimal(10,1) DEFAULT NULL COMMENT 'in km',
  `notes` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `vehicle_code` (`vehicle_code`),
  UNIQUE KEY `license_plate` (`license_plate`),
  KEY `idx_provider_id` (`provider_id`),
  KEY `idx_vehicle_type` (`vehicle_type`),
  KEY `idx_status` (`status`),
  KEY `idx_license_plate` (`license_plate`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `vehicle_maintenance`
--

DROP TABLE IF EXISTS `vehicle_maintenance`;
CREATE TABLE IF NOT EXISTS `vehicle_maintenance` (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `vehicle_id` int UNSIGNED NOT NULL,
  `maintenance_type` enum('routine','repair','inspection','emergency') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `scheduled_date` date NOT NULL,
  `completed_date` date DEFAULT NULL,
  `service_provider` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cost` decimal(10,2) DEFAULT NULL,
  `odometer_reading` decimal(10,1) DEFAULT NULL COMMENT 'km at time of maintenance',
  `status` enum('scheduled','in_progress','completed','cancelled') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'scheduled',
  `next_maintenance_km` decimal(10,1) DEFAULT NULL,
  `next_maintenance_date` date DEFAULT NULL,
  `notes` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_vehicle_id` (`vehicle_id`),
  KEY `idx_scheduled_date` (`scheduled_date`),
  KEY `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `vehicle_tracking`
--

DROP TABLE IF EXISTS `vehicle_tracking`;
CREATE TABLE IF NOT EXISTS `vehicle_tracking` (
  `id` int NOT NULL AUTO_INCREMENT,
  `vehicle_id` int UNSIGNED NOT NULL,
  `latitude` decimal(10,8) NOT NULL,
  `longitude` decimal(11,8) NOT NULL,
  `status` enum('Active','Inactive','Maintenance','Emergency') NOT NULL DEFAULT 'Active',
  `speed` decimal(5,2) DEFAULT '0.00',
  `heading` int DEFAULT '0',
  `fuel_level` decimal(5,2) DEFAULT '0.00',
  `passengers` int DEFAULT '0',
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_vehicle` (`vehicle_id`),
  KEY `idx_vehicle_tracking_updated` (`updated_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `alerts`
--
ALTER TABLE `alerts`
  ADD CONSTRAINT `alerts_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `cargo`
--
ALTER TABLE `cargo`
  ADD CONSTRAINT `cargo_ibfk_1` FOREIGN KEY (`service_id`) REFERENCES `services` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `drivers`
--
ALTER TABLE `drivers`
  ADD CONSTRAINT `drivers_ibfk_1` FOREIGN KEY (`provider_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `drivers_ibfk_2` FOREIGN KEY (`assigned_vehicle_id`) REFERENCES `vehicles` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `email_verifications`
--
ALTER TABLE `email_verifications`
  ADD CONSTRAINT `email_verifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `request_status_history`
--
ALTER TABLE `request_status_history`
  ADD CONSTRAINT `request_status_history_ibfk_1` FOREIGN KEY (`request_id`) REFERENCES `user_requests` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `request_status_history_ibfk_2` FOREIGN KEY (`changed_by_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `routes`
--
ALTER TABLE `routes`
  ADD CONSTRAINT `fk_routes_provider` FOREIGN KEY (`provider_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `route_coordinates`
--
ALTER TABLE `route_coordinates`
  ADD CONSTRAINT `route_coordinates_ibfk_1` FOREIGN KEY (`route_id`) REFERENCES `routes` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `schedules`
--
ALTER TABLE `schedules`
  ADD CONSTRAINT `fk_schedules_provider` FOREIGN KEY (`provider_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `services`
--
ALTER TABLE `services`
  ADD CONSTRAINT `services_ibfk_1` FOREIGN KEY (`provider_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `services_ibfk_2` FOREIGN KEY (`vehicle_id`) REFERENCES `vehicles` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `services_ibfk_3` FOREIGN KEY (`driver_id`) REFERENCES `drivers` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `service_history`
--
ALTER TABLE `service_history`
  ADD CONSTRAINT `service_history_ibfk_1` FOREIGN KEY (`service_id`) REFERENCES `services` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `service_history_ibfk_2` FOREIGN KEY (`action_by_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `service_points`
--
ALTER TABLE `service_points`
  ADD CONSTRAINT `fk_service_points_provider` FOREIGN KEY (`provider_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `service_point_coordinates`
--
ALTER TABLE `service_point_coordinates`
  ADD CONSTRAINT `service_point_coordinates_ibfk_1` FOREIGN KEY (`service_point_id`) REFERENCES `service_points` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `user_profiles`
--
ALTER TABLE `user_profiles`
  ADD CONSTRAINT `user_profiles_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `user_requests`
--
ALTER TABLE `user_requests`
  ADD CONSTRAINT `user_requests_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `user_requests_ibfk_2` FOREIGN KEY (`provider_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `user_tariff_submissions`
--
ALTER TABLE `user_tariff_submissions`
  ADD CONSTRAINT `user_tariff_submissions_ibfk_1` FOREIGN KEY (`submitted_by_user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `user_tariff_submissions_ibfk_2` FOREIGN KEY (`reviewed_by_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `vehicles`
--
ALTER TABLE `vehicles`
  ADD CONSTRAINT `vehicles_ibfk_1` FOREIGN KEY (`provider_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `vehicle_maintenance`
--
ALTER TABLE `vehicle_maintenance`
  ADD CONSTRAINT `vehicle_maintenance_ibfk_1` FOREIGN KEY (`vehicle_id`) REFERENCES `vehicles` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `vehicle_tracking`
--
ALTER TABLE `vehicle_tracking`
  ADD CONSTRAINT `vehicle_tracking_ibfk_1` FOREIGN KEY (`vehicle_id`) REFERENCES `vehicles` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
