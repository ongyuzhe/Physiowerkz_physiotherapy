-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 30, 2024 at 08:53 AM
-- Server version: 10.4.28-MariaDB
-- PHP Version: 8.2.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `physiowerkz`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin_notifications`
--

CREATE TABLE `admin_notifications` (
  `notification_id` int(11) NOT NULL,
  `patient_id` int(11) DEFAULT NULL,
  `changed_by` varchar(20) DEFAULT NULL,
  `change_type` varchar(20) NOT NULL,
  `log_id` int(11) NOT NULL,
  `notification_time` datetime DEFAULT current_timestamp(),
  `status` enum('Unread','Read') DEFAULT 'Unread',
  `message` text NOT NULL,
  `admin_id` int(11) DEFAULT NULL,
  `appoint_status` enum('Pending','Scheduled','Completed','Cancelled') DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `admin_notifications`
--

INSERT INTO `admin_notifications` (`notification_id`, `patient_id`, `changed_by`, `change_type`, `log_id`, `notification_time`, `status`, `message`, `admin_id`, `appoint_status`) VALUES
(141, 9, '', 'Booking', 0, '2024-09-26 14:44:17', 'Unread', 'A new booking request is pending approval. Please review and approve.', 1, NULL),
(142, 9, 'john', 'Update!', 87, '2024-10-01 08:35:01', 'Unread', 'Patient change logged for patient ID: 9', 2, NULL),
(143, 8, '', 'Booking', 0, '2024-10-01 08:35:31', 'Read', 'A new booking request is pending approval. Please review and approve.', 1, NULL),
(144, 11, NULL, 'Booking', 0, '2024-10-01 12:46:27', 'Read', 'A new booking request is pending approval. Please review and approve.', 1, NULL),
(145, 12, NULL, 'Booking', 0, '2024-10-01 15:10:47', 'Read', 'A new booking request is pending approval. Please review and approve.', 1, NULL),
(146, NULL, 'admin', 'Inserted!', 43, '2024-10-01 21:20:41', 'Unread', 'New Staff has been added!', 1, NULL),
(147, 13, 'admin', 'Inserted!', 88, '2024-10-01 21:23:52', 'Unread', 'New patient has been added!', 1, NULL),
(148, 12, NULL, 'Booking', 0, '2024-10-02 20:48:11', 'Read', 'A new booking request is pending approval. Please review and approve.', 1, NULL),
(149, 9, NULL, 'Booking', 0, '2024-10-16 20:03:34', 'Read', 'A new booking request is pending approval. Please review and approve.', 1, NULL),
(150, 9, NULL, 'Booking', 0, '2024-10-16 20:09:22', 'Read', 'A new booking request is pending approval. Please review and approve.', 1, NULL),
(151, 11, NULL, 'Booking', 0, '2024-10-16 20:28:33', 'Unread', 'A new booking request is pending approval. Please review and approve.', 1, NULL),
(152, 11, NULL, 'Booking', 0, '2024-10-16 20:43:53', 'Read', 'A new booking request is pending approval. Please review and approve.', 1, NULL),
(153, 8, NULL, 'Booking', 0, '2024-10-23 09:53:19', 'Read', 'A new booking request is pending approval. Please review and approve.', 1, NULL),
(156, 9, 'john22', 'Review Alert', 11, '2024-10-29 08:54:09', 'Read', 'Patient john22 left a low review rating of 1 stars for john.', 1, NULL),
(157, 8, 'admin', 'Update!', 89, '2024-10-29 12:55:28', 'Unread', 'Patient change logged for patient ID: 8', 1, NULL),
(158, 8, 'admin', 'Update!', 90, '2024-10-29 12:55:30', 'Unread', 'Patient change logged for patient ID: 8', 1, NULL),
(159, 8, 'admin', 'Update!', 91, '2024-10-29 12:56:20', 'Unread', 'Patient change logged for patient ID: 8', 1, NULL),
(160, 8, 'admin', 'Update!', 92, '2024-10-29 12:57:43', 'Unread', 'Patient change logged for patient ID: 8', 1, NULL),
(161, 8, 'admin', 'Update!', 93, '2024-10-29 13:06:46', 'Unread', 'Patient change logged for patient ID: 8', 1, NULL),
(162, 8, 'admin', 'Update!', 94, '2024-10-29 13:26:01', 'Unread', 'Patient change logged for patient ID: 8', 1, NULL),
(163, 8, 'admin', 'Update!', 95, '2024-10-29 13:29:07', 'Unread', 'Patient change logged for patient ID: 8', 1, NULL),
(164, 8, 'admin', 'Update!', 96, '2024-10-29 13:29:16', 'Unread', 'Patient change logged for patient ID: 8', 1, NULL),
(165, 8, 'admin', 'Update!', 97, '2024-10-29 13:29:19', 'Unread', 'Patient change logged for patient ID: 8', 1, NULL),
(166, 8, 'admin', 'Update!', 98, '2024-10-29 13:29:23', 'Unread', 'Patient change logged for patient ID: 8', 1, NULL),
(167, 9, 'admin', 'Update!', 99, '2024-10-29 13:29:28', 'Unread', 'Patient change logged for patient ID: 9', 1, NULL),
(168, 8, 'admin', 'Update!', 100, '2024-10-29 13:32:44', 'Unread', 'Patient change logged for patient ID: 8', 1, NULL),
(169, 8, 'admin', 'Update!', 101, '2024-10-29 13:37:48', 'Unread', 'Patient change logged for patient ID: 8', 1, NULL),
(170, 8, 'admin', 'Update!', 102, '2024-10-29 13:38:21', 'Unread', 'Patient change logged for patient ID: 8', 1, NULL),
(171, 8, 'admin', 'Update!', 103, '2024-10-29 13:40:39', 'Unread', 'Patient change logged for patient ID: 8', 1, NULL),
(172, 8, 'admin', 'Update!', 104, '2024-10-29 13:40:43', 'Unread', 'Patient change logged for patient ID: 8', 1, NULL),
(173, 8, 'admin', 'Update!', 105, '2024-10-29 13:43:08', 'Unread', 'Patient change logged for patient ID: 8', 1, NULL),
(174, 9, 'admin', 'Update!', 106, '2024-10-29 15:26:04', 'Read', 'Patient change logged for patient ID: 9', 1, NULL),
(175, NULL, 'admin', 'Inserted!', 44, '2024-10-30 09:59:00', 'Read', 'New Staff has been added!', 1, NULL),
(176, NULL, 'superadmin', 'Updated!', 45, '2024-10-30 11:18:09', 'Unread', 'Staff record updated!', 14, NULL),
(177, NULL, 'superadmin', 'Updated!', 46, '2024-10-30 11:20:18', 'Unread', 'Staff record updated!', 14, NULL),
(178, NULL, 'superadmin', 'Updated!', 47, '2024-10-30 11:32:59', 'Unread', 'Staff record updated!', 14, NULL),
(179, 11, 'admin', 'Update!', 107, '2024-10-30 12:01:12', 'Unread', 'Patient change logged for patient ID: 11', 1, NULL),
(180, NULL, 'superadmin', 'Updated!', 48, '2024-10-30 12:05:33', 'Unread', 'Staff record updated!', 14, NULL),
(181, NULL, 'superadmin', 'Updated!', 49, '2024-10-30 13:58:20', 'Unread', 'Staff record updated!', 14, NULL),
(182, NULL, 'admin', 'Updated!', 50, '2024-10-30 13:58:45', 'Unread', 'Staff record updated!', 1, NULL),
(183, NULL, 'superadmin', 'Updated!', 51, '2024-10-30 14:01:59', 'Unread', 'Staff record updated!', 14, NULL),
(184, 8, NULL, 'Booking', 0, '2024-10-30 14:05:06', 'Unread', 'A new booking request is pending approval. Please review and approve.', 1, NULL),
(185, NULL, 'superadmin', 'Updated!', 52, '2024-10-30 15:03:31', 'Unread', 'Staff record updated!', 14, NULL),
(186, NULL, 'superadmin', 'Updated!', 53, '2024-10-30 15:39:35', 'Unread', 'Staff record updated!', 14, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `appointments`
--

CREATE TABLE `appointments` (
  `appointment_id` int(11) NOT NULL,
  `patient_id` int(11) DEFAULT NULL,
  `staff_id` int(11) DEFAULT NULL,
  `therapist_id` int(11) DEFAULT NULL,
  `appointment_datetime` datetime NOT NULL,
  `patient_comments` varchar(120) DEFAULT NULL,
  `staff_comments` varchar(120) DEFAULT NULL,
  `status` enum('Pending','Scheduled','Completed','Cancelled','Unavailable') DEFAULT 'Pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `managed_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `appointments`
--

INSERT INTO `appointments` (`appointment_id`, `patient_id`, `staff_id`, `therapist_id`, `appointment_datetime`, `patient_comments`, `staff_comments`, `status`, `created_at`, `managed_at`) VALUES
(38, 8, 1, 2, '2024-11-01 15:04:00', 'wer', '', 'Scheduled', '2024-10-30 06:05:06', '2024-10-30 15:40:13');

-- --------------------------------------------------------

--
-- Table structure for table `assessment_findings`
--

CREATE TABLE `assessment_findings` (
  `id` int(11) NOT NULL,
  `appointment_id` int(11) NOT NULL,
  `patient_id` int(11) NOT NULL,
  `body_part` varchar(255) NOT NULL,
  `condition_type` varchar(255) DEFAULT NULL,
  `severity` varchar(50) DEFAULT NULL,
  `remarks` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `assessment_findings`
--

INSERT INTO `assessment_findings` (`id`, `appointment_id`, `patient_id`, `body_part`, `condition_type`, `severity`, `remarks`) VALUES
(20, 28, 8, 'left_foot', 'varus', 'mild', 'qweqwe'),
(21, 28, 8, 'lumbar_spine', 'varus', 'severe', 'dfgdfg'),
(22, 34, 9, 'left_knee', 'varus', 'moderate', 'eregr'),
(27, 38, 8, 'right_foot', 'SDF', 'mild', 'SDF'),
(28, 38, 8, 'thoracic_spine', 'yujyu', 'moderate', 'yuj');

-- --------------------------------------------------------

--
-- Table structure for table `billing`
--

CREATE TABLE `billing` (
  `billing_id` int(11) NOT NULL,
  `appointment_id` int(11) DEFAULT NULL,
  `amount` decimal(10,2) NOT NULL,
  `status` enum('pending','paid') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `clinical_notes`
--

CREATE TABLE `clinical_notes` (
  `note_id` int(11) NOT NULL,
  `patient_id` int(11) DEFAULT NULL,
  `staff_id` int(11) DEFAULT NULL,
  `note` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `exercise_suggestions`
--

CREATE TABLE `exercise_suggestions` (
  `id` int(11) NOT NULL,
  `appointment_id` int(11) NOT NULL,
  `patient_id` int(11) NOT NULL,
  `patient_name` varchar(255) NOT NULL,
  `suggestion` varchar(255) NOT NULL,
  `situation` enum('Severe','Moderate','Low') NOT NULL,
  `date` date DEFAULT NULL,
  `remarks` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `exercise_suggestions`
--

INSERT INTO `exercise_suggestions` (`id`, `appointment_id`, `patient_id`, `patient_name`, `suggestion`, `situation`, `date`, `remarks`) VALUES
(5, 0, 10, 'Sally Ong', '', 'Severe', '6099-09-06', 'oh no'),
(6, 0, 10, 'Sally Ong', 'Sleep', 'Low', '9999-09-08', ''),
(7, 0, 11, 'Bangla Man', 'Sleep', 'Severe', '9919-10-10', '24 hours'),
(8, 0, 11, 'Bangla Man', 'Run', 'Moderate', '2025-10-10', 'Everyday'),
(9, 28, 8, 'Tan Wen Hau', 'sdfsdf', 'Low', '2024-10-23', 'sdfsdf'),
(10, 36, 11, 'Bangla Man', 'run boy', 'Low', '2024-10-08', 'sit'),
(15, 38, 8, 'Tan Wen Hau', 'yujyuj', 'Severe', '2024-10-08', 'yuj'),
(16, 38, 8, 'Tan Wen Hau', 'werwer', 'Moderate', '2024-10-03', 'werwerwe');

-- --------------------------------------------------------

--
-- Table structure for table `health_questionnaire`
--

CREATE TABLE `health_questionnaire` (
  `id` int(11) NOT NULL,
  `username` varchar(255) NOT NULL,
  `know_us` varchar(255) NOT NULL,
  `referrer` varchar(255) DEFAULT NULL,
  `full_name` varchar(255) NOT NULL,
  `nric_passport_no` varchar(255) DEFAULT NULL,
  `profession` varchar(255) DEFAULT NULL,
  `nationality` varchar(255) DEFAULT NULL,
  `country` varchar(255) DEFAULT NULL,
  `religion` varchar(255) DEFAULT NULL,
  `emergency_contact` varchar(255) DEFAULT NULL,
  `relationship` varchar(255) DEFAULT NULL,
  `emergency_phone` varchar(255) DEFAULT NULL,
  `family_doctor` varchar(255) DEFAULT NULL,
  `clinic_address` varchar(255) DEFAULT NULL,
  `clinic_number` varchar(255) DEFAULT NULL,
  `newsletter` varchar(255) DEFAULT NULL,
  `hospitalized` varchar(255) DEFAULT NULL,
  `hospitalized_details` text DEFAULT NULL,
  `physical_activity` varchar(255) DEFAULT NULL,
  `chest_pain_activity` varchar(255) DEFAULT NULL,
  `chest_pain_rest` varchar(255) DEFAULT NULL,
  `heart_disease` varchar(255) DEFAULT NULL,
  `stroke` varchar(255) DEFAULT NULL,
  `high_blood_pressure` varchar(255) DEFAULT NULL,
  `diabetes` varchar(255) DEFAULT NULL,
  `asthma` varchar(255) DEFAULT NULL,
  `osteoporosis` varchar(255) DEFAULT NULL,
  `epilepsy` varchar(255) DEFAULT NULL,
  `cancer` varchar(255) DEFAULT NULL,
  `weight_loss` varchar(255) DEFAULT NULL,
  `dizziness` varchar(255) DEFAULT NULL,
  `smoking` varchar(255) DEFAULT NULL,
  `pregnant` varchar(255) DEFAULT NULL,
  `fracture_accident` varchar(255) DEFAULT NULL,
  `blood_disorder` varchar(255) DEFAULT NULL,
  `other_conditions` text DEFAULT NULL,
  `pain_killers` varchar(255) DEFAULT NULL,
  `inhaler` varchar(255) DEFAULT NULL,
  `blood_thinners` varchar(255) DEFAULT NULL,
  `steroids` varchar(255) DEFAULT NULL,
  `other_medications` text DEFAULT NULL,
  `signature` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `health_questionnaire`
--

INSERT INTO `health_questionnaire` (`id`, `username`, `know_us`, `referrer`, `full_name`, `nric_passport_no`, `profession`, `nationality`, `country`, `religion`, `emergency_contact`, `relationship`, `emergency_phone`, `family_doctor`, `clinic_address`, `clinic_number`, `newsletter`, `hospitalized`, `hospitalized_details`, `physical_activity`, `chest_pain_activity`, `chest_pain_rest`, `heart_disease`, `stroke`, `high_blood_pressure`, `diabetes`, `asthma`, `osteoporosis`, `epilepsy`, `cancer`, `weight_loss`, `dizziness`, `smoking`, `pregnant`, `fracture_accident`, `blood_disorder`, `other_conditions`, `pain_killers`, `inhaler`, `blood_thinners`, `steroids`, `other_medications`, `signature`) VALUES
(2, 'jason', 'Internet', 'X', 'Jason Tan', '123', 'IT Guy', 'Malaysian', '', 'Hindu', 'John Tan', 'Father', '123', '', '', '', 'Yes', 'No', '', 'No', 'No', 'No', '', '', 'yes', '', '', '', '', '', '', '', '', 'yes', '', '', '', '', '', '', 'yes', '', 'signatures/jason_66a8ca98aba0c.png'),
(3, 'jane', 'Referred by friend or family', 'Mum', 'Jane Doe', '012345', 'Accounts', 'Malaysian', '', 'Buddhist', 'Suzy Wong', 'Mum', '996', '', '', '', 'No', 'No', '', 'No', 'Yes', 'Yes', '', '', 'yes', '', '', '', '', '', 'yes', '', '', '', '', '', '', '', '', '', '', '', 'signatures/jane_66bf069881f1c.png'),
(6, 'wenhau123', 'Referred by friend or family', '', 'Tan Wen Hau', '010622011265', 'student', 'Non-Malaysian', 'Belarus', 'Others', 'Mrs Lee QIQI', 'Friend', '+4601126510938', 'John Doe', 'No22, Lorong Gurun KAW 18, JALAN KAPAR, 41400 KLANG , SELANGOR', '011232331232131', 'No', 'Yes', 'Selangor', 'No', 'Yes', 'Yes', 'yes', '', 'yes', '', '', '', '', '', '', '', '', '', '', '', '', '', '', 'yes', '', '', 'signatures/wenhau123_66c3530c647df.png'),
(8, 'noob', 'Internet', 'X', 'Noob Master', '012345', 'Housewife', 'Malaysian', '', 'Hindu', 'Mr Master Wong', 'Parent', '+60124534543', '', '', '', 'No', '', '', '', '', '', '', '', '', '', '', '', '', '', 'yes', '', '', '', '', '', '', 'yes', '', '', '', '', 'signatures/noob_66fb7d37265ff.png'),
(9, 'abc123', 'Internet', 'X', 'A BC', '012345', 'Accounts', 'Malaysian', '', 'Buddhist', 'Mr Tan', 'Parent', '+60125374784949', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', 'signatures/abc123_66fba02bcd565.png'),
(10, 'john22', 'Internet', 'Mum', 'Sophie Lee', '012345', 'Accounts', 'Malaysian', '', 'Buddhist', 'Mr Suzy Wong', 'Parent', '+60125374784949', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', 'signatures/john22_670cc6b98e36d.png'),
(11, 'bangla', 'Referred by doctor', 'X', 'Bangla Man', '012345', 'IT Guy', 'Malaysian', '', 'Islam', 'Mr Master Wong', 'Friend', '+60125374784949', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', 'signatures/bangla_670cc8045ded7.png');

-- --------------------------------------------------------

--
-- Table structure for table `hr`
--

CREATE TABLE `hr` (
  `hr_id` int(11) NOT NULL,
  `staff_id` int(11) DEFAULT NULL,
  `hire_date` date NOT NULL,
  `leave_balance` int(11) DEFAULT 0,
  `performance_review` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `patient_id` int(11) NOT NULL,
  `message` text NOT NULL,
  `status` enum('Scheduled','Pending','Cancelled','Completed') DEFAULT 'Pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `appointdatetime` timestamp NULL DEFAULT NULL,
  `read_status` enum('read','unread') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`id`, `patient_id`, `message`, `status`, `created_at`, `updated_at`, `appointdatetime`, `read_status`) VALUES
(27, 9, 'Your booking has been cancelled', 'Cancelled', '2024-09-26 06:44:17', '2024-10-01 00:49:11', '2024-09-28 07:44:00', 'read'),
(28, 8, 'Your Booking has been Schedulled!', 'Scheduled', '2024-10-01 00:35:31', '2024-10-01 00:41:51', '2024-10-10 01:35:00', 'unread'),
(29, 11, 'Booking sent. A confirmation will be sent at a later date as soon as possible.', 'Pending', '2024-10-01 04:44:09', '2024-10-01 04:44:09', '2024-10-10 02:45:00', 'unread'),
(30, 11, 'Your booking has been cancelled', 'Cancelled', '2024-10-01 04:46:27', '2024-10-01 04:46:37', '2024-10-10 02:45:00', 'unread'),
(31, 12, 'Your Booking has been Cancelled!', 'Cancelled', '2024-10-01 07:10:47', '2024-10-01 07:17:41', '2024-10-10 00:30:00', 'unread'),
(32, 12, 'Booking sent. A confirmation will be sent at a later date as soon as possible.', 'Pending', '2024-10-02 12:48:11', '2024-10-02 12:48:11', '2024-10-09 23:00:00', 'unread'),
(33, 9, 'Booking sent. A confirmation will be sent at a later date as soon as possible.', 'Pending', '2024-10-16 12:03:34', '2024-10-29 10:10:58', '2024-10-17 03:59:00', 'read'),
(34, 9, 'Booking sent. A confirmation will be sent at a later date as soon as possible.', 'Pending', '2024-10-16 12:09:22', '2024-10-29 01:28:33', '2024-10-18 03:59:00', 'read'),
(35, 11, 'Your booking has been cancelled', 'Cancelled', '2024-10-16 12:28:33', '2024-10-16 12:44:00', '2024-10-20 02:00:00', 'unread'),
(36, 11, 'Your Booking has been Schedulled!', 'Scheduled', '2024-10-16 12:43:53', '2024-10-16 12:44:46', '2024-10-19 02:00:00', 'unread'),
(37, 8, 'Your Session is Completed!', 'Completed', '2024-10-23 01:53:19', '2024-10-29 02:43:20', '2024-10-30 04:53:00', 'unread'),
(38, 8, 'Your Booking has been Schedulled!', 'Scheduled', '2024-10-30 06:05:06', '2024-10-30 07:40:13', '2024-11-01 07:04:00', 'unread');

-- --------------------------------------------------------

--
-- Table structure for table `notificationstaff`
--

CREATE TABLE `notificationstaff` (
  `id` int(11) NOT NULL,
  `staff_id` int(11) NOT NULL,
  `message` text NOT NULL,
  `status` enum('Approved','Pending','Cancelled','Removed') DEFAULT 'Pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `appointdatetime` timestamp NULL DEFAULT NULL,
  `read_status` enum('unread','read') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `notificationstaff`
--

INSERT INTO `notificationstaff` (`id`, `staff_id`, `message`, `status`, `created_at`, `updated_at`, `appointdatetime`, `read_status`) VALUES
(35, 2, 'Patient change logged for patient ID: 9', NULL, '2024-10-01 00:35:01', '2024-10-29 01:42:49', NULL, 'unread'),
(36, 2, 'You have a new session with the client!', 'Approved', '2024-10-01 00:35:31', '2024-10-29 01:42:52', '2024-10-10 01:35:00', 'unread'),
(37, 2, 'A new booking request is pending approval. Please review and approve.', 'Pending', '2024-10-01 04:44:09', '2024-10-29 01:43:02', '2024-10-10 02:45:00', 'read'),
(38, 2, 'A new booking request is pending approval. Please review and approve.', 'Pending', '2024-10-01 04:46:27', '2024-10-29 01:35:37', '2024-10-10 02:45:00', 'read'),
(39, 2, 'Your session have been cancelled!', 'Cancelled', '2024-10-01 07:10:47', '2024-10-29 01:34:39', '2024-10-10 00:30:00', 'read'),
(40, 2, 'The session is unavailable.', 'Cancelled', '2024-10-02 12:48:11', '2024-10-29 01:34:44', '2024-10-09 23:00:00', 'read'),
(41, 2, 'A new booking request is pending approval. Please review and approve.', 'Pending', '2024-10-16 12:03:34', '2024-10-29 01:42:10', '2024-10-17 03:59:00', 'read'),
(42, 2, 'A new booking request is pending approval. Please review and approve.', 'Pending', '2024-10-16 12:09:22', '2024-10-16 12:18:57', '2024-10-18 03:59:00', 'read'),
(43, 2, 'A new booking request is pending approval. Please review and approve.', 'Pending', '2024-10-16 12:28:33', '2024-10-29 01:29:59', '2024-10-20 02:00:00', 'read'),
(44, 2, 'You have a new session with the client!', 'Approved', '2024-10-16 12:43:53', '2024-10-29 01:28:48', '2024-10-19 02:00:00', 'read'),
(45, 2, 'You have a new session with the client!', 'Approved', '2024-10-23 01:53:19', '2024-10-29 01:28:44', '2024-10-30 04:53:00', 'read'),
(46, 2, 'You have a new session with the client!', 'Approved', '2024-10-30 06:05:06', '2024-10-30 07:40:13', '2024-11-01 07:04:00', 'unread');

-- --------------------------------------------------------

--
-- Table structure for table `password_resets`
--

CREATE TABLE `password_resets` (
  `id` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `token` varchar(64) NOT NULL,
  `token_expiry` datetime NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `password_resets`
--

INSERT INTO `password_resets` (`id`, `email`, `token`, `token_expiry`, `created_at`) VALUES
(1, 'admin@physiowerkz.com', '73c4706f5e1c74514926c579a9282e58730c8c14b9af1b5fc4598a36d36c3abd', '2024-07-22 11:09:36', '2024-07-22 02:09:36'),
(2, 'tanwenhau9@gmail.com', '66935e6756849d4860226d6e789ddadd6133ca1ffd38dfb41269a07917ad2766', '2024-10-30 16:45:00', '2024-10-30 07:45:00'),
(5, 'tanwenhau9@gmail.com', '08c0870a21e6272b5189496e7ddc239526e3066e4b7baed3a1a1b2a236cf81cc', '2024-10-30 16:51:46', '2024-10-30 07:51:46');

-- --------------------------------------------------------

--
-- Table structure for table `patients`
--

CREATE TABLE `patients` (
  `patient_id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(100) NOT NULL,
  `gender` varchar(10) DEFAULT NULL,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `dob` date NOT NULL,
  `contact_num` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `lastlogin` timestamp NULL DEFAULT NULL,
  `preferred_communication` varchar(10) NOT NULL,
  `ques_status` int(2) NOT NULL,
  `survey_status` enum('Yes','No') NOT NULL DEFAULT 'Yes',
  `class` text NOT NULL,
  `session_count` int(11) DEFAULT NULL,
  `personality` varchar(30) DEFAULT NULL,
  `compliance` varchar(25) DEFAULT NULL,
  `bodytype` varchar(40) DEFAULT NULL,
  `focus` varchar(40) DEFAULT NULL,
  `lifestyle` text DEFAULT NULL,
  `healthstatus` varchar(40) DEFAULT NULL,
  `goal` text DEFAULT NULL,
  `outcome` varchar(40) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `patients`
--

INSERT INTO `patients` (`patient_id`, `username`, `password`, `email`, `gender`, `first_name`, `last_name`, `dob`, `contact_num`, `created_at`, `lastlogin`, `preferred_communication`, `ques_status`, `survey_status`, `class`, `session_count`, `personality`, `compliance`, `bodytype`, `focus`, `lifestyle`, `healthstatus`, `goal`, `outcome`) VALUES
(8, 'wenhau123', '$2y$10$gUT8b8xHfcYFQa0.bQYPxeFfSY5jxltc520Yf73kmb0TQDosMr45i', 'tanwenhau9@gmail.com', 'Male', 'Tan', 'Wen Hau', '2001-06-22', '+601126510938', '2024-08-19 11:27:55', '2024-10-30 07:49:54', 'Email', 1, 'No', 'Class 1', 1, 'Type A', 'No', 'Restricted', 'Big', 'manager', 'Unhealthy', 'asdasd', 'Set Alarm, Exercise'),
(9, 'john22', '$2y$10$gUT8b8xHfcYFQa0.bQYPxeFfSY5jxltc520Yf73kmb0TQDosMr45i', 'tanwenhau9@gmail.com', 'Female', 'Sophie', 'Lee', '2000-02-11', '+601126510938', '2024-08-19 14:15:49', '2024-10-30 07:38:12', 'Email', 1, 'No', 'Class 1', 20, 'Type B', 'Yes', 'Restricted', 'Open', 'manager', 'Risks', 'good', 'Meditate, Exercise'),
(10, 'nickher', '$2y$10$gUT8b8xHfcYFQa0.bQYPxeFfSY5jxltc520Yf73kmb0TQDosMr45i', 'tanwenhau9@gmail.com', 'Female', 'Sally', 'Ong', '2000-07-08', '+601126510938', '2024-08-28 09:40:56', '2024-08-28 09:41:02', 'Phone', 0, 'No', 'Class 1', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(11, 'bangla', '$2y$10$JroBD1V4zeB.Cw4uROOfSudgILJdUHTaKtOBJoVfouUzBsYY9J.dG', 'bangla@mail.com', 'Male', 'Bangla', 'Man', '2000-10-10', '+609876543216', '2024-10-13 14:01:26', '2024-10-17 10:05:09', 'Email', 1, 'No', 'Class 1', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `patient_changes_log`
--

CREATE TABLE `patient_changes_log` (
  `log_id` int(11) NOT NULL,
  `patient_id` int(11) DEFAULT NULL,
  `change_type` varchar(10) NOT NULL,
  `changed_by` varchar(50) NOT NULL,
  `change_time` datetime DEFAULT current_timestamp(),
  `old_values` text DEFAULT NULL,
  `new_values` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `patient_changes_log`
--

INSERT INTO `patient_changes_log` (`log_id`, `patient_id`, `change_type`, `changed_by`, `change_time`, `old_values`, `new_values`) VALUES
(85, 8, 'Update!', 'john', '2024-09-25 22:05:03', '{\"patient_id\":8,\"username\":\"wenhau123\",\"password\":\"$2y$10$4n9nvH.76pPukwZwxZHwBeJSZ2Ot64s5SEPbp3GhMWzOwnkfKnDx6\",\"email\":\"tanwenhau9@gmail.com\",\"gender\":\"Male\",\"first_name\":\"Tan\",\"last_name\":\"Wen Hau\",\"dob\":\"2001-06-22\",\"contact_num\":\"+601126510938\",\"created_at\":\"2024-08-19 19:27:55\",\"lastlogin\":\"2024-09-06 17:12:51\",\"preferred_communication\":\"Email\",\"ques_status\":1,\"class\":\"Class 2\",\"session_count\":3}', '{\"username\":\"wenhau123\",\"email\":\"tanwenhau9@gmail.com\",\"first_name\":\"Tan\",\"last_name\":\"Wen Hau\",\"dob\":\"2001-06-22\",\"contact_num\":\"+601126510938\",\"gender\":\"Female\",\"preferred_communication\":\"Email\",\"class\":\"Class 2\",\"edit_id\":\"8\"}'),
(86, 8, 'Update!', 'john', '2024-09-25 22:05:48', '{\"patient_id\":8,\"username\":\"wenhau123\",\"password\":\"$2y$10$4n9nvH.76pPukwZwxZHwBeJSZ2Ot64s5SEPbp3GhMWzOwnkfKnDx6\",\"email\":\"tanwenhau9@gmail.com\",\"gender\":\"Female\",\"first_name\":\"Tan\",\"last_name\":\"Wen Hau\",\"dob\":\"2001-06-22\",\"contact_num\":\"+601126510938\",\"created_at\":\"2024-08-19 19:27:55\",\"lastlogin\":\"2024-09-06 17:12:51\",\"preferred_communication\":\"Email\",\"ques_status\":1,\"class\":\"Class 2\",\"session_count\":3}', '{\"username\":\"wenhau123\",\"email\":\"tanwenhau9@gmail.com\",\"first_name\":\"Tan\",\"last_name\":\"Wen Hau\",\"dob\":\"2001-06-22\",\"contact_num\":\"+601126510938\",\"gender\":\"Male\",\"preferred_communication\":\"Email\",\"class\":\"Class 2\",\"edit_id\":\"8\"}'),
(87, 9, 'Update!', 'john', '2024-10-01 08:35:01', '{\"patient_id\":9,\"username\":\"john22\",\"password\":\"$2y$10$RdOK2MQitF46Y10rl9Ohn.XnjvTlYsLw1zQi0h7Jv5i6kK\\/YDroJK\",\"email\":\"tanwenhau9@gmail.com\",\"gender\":\"Male\",\"first_name\":\"Tan\",\"last_name\":\"Wen Hau\",\"dob\":\"2000-02-11\",\"contact_num\":\"+601126510938\",\"created_at\":\"2024-08-19 22:15:49\",\"lastlogin\":\"2024-10-01 08:32:30\",\"preferred_communication\":\"Email\",\"ques_status\":1,\"class\":\"Class 1\",\"session_count\":0}', '{\"username\":\"john22\",\"email\":\"tanwenhau9@gmail.com\",\"first_name\":\"Tan\",\"last_name\":\"Wen Hau\",\"dob\":\"2000-02-11\",\"contact_num\":\"+601126510938\",\"gender\":\"Female\",\"preferred_communication\":\"Email\",\"class\":\"Class 1\",\"edit_id\":\"9\"}'),
(88, 13, 'Inserted!', 'admin', '2024-10-01 21:23:52', '{\"username\":\"lily\",\"password\":\"lily123\",\"confirm_password\":\"lily123\",\"email\":\"lily@mail.com\",\"gender\":\"Female\",\"first_name\":\"Lily\",\"last_name\":\"See\",\"dob\":\"2000-03-20\",\"area_code\":\"+60\",\"contact_num\":\"9876543216\",\"preferred_communication\":\"email\",\"preferred_class\":\"class1\"}', NULL),
(89, 8, 'Update!', 'admin', '2024-10-29 12:55:28', '{\"patient_id\":8,\"username\":\"wenhau123\",\"password\":\"$2y$10$4n9nvH.76pPukwZwxZHwBeJSZ2Ot64s5SEPbp3GhMWzOwnkfKnDx6\",\"email\":\"tanwenhau9@gmail.com\",\"gender\":\"Male\",\"first_name\":\"Tan\",\"last_name\":\"Wen Hau\",\"dob\":\"2001-06-22\",\"contact_num\":\"+601126510938\",\"created_at\":\"2024-08-19 19:27:55\",\"lastlogin\":\"2024-10-29 12:09:28\",\"preferred_communication\":\"Email\",\"ques_status\":1,\"survey_status\":0,\"class\":\"Class 2\",\"session_count\":1,\"personality\":\"Type A\",\"compliance\":\"No\",\"bodytype\":\"Restricted\",\"focus\":\"Big\",\"lifestyle\":\"manager\",\"healthstatus\":\"Unhealthy\",\"goal\":\"asdasd\",\"outcome\":\"Set Alarm, Exercise\"}', '{\"username\":\"wenhau123\",\"email\":\"tanwenhau9@gmail.com\",\"first_name\":\"Tan\",\"last_name\":\"Wen Hau\",\"dob\":\"2001-06-22\",\"contact_num\":\"+601126510938\",\"survey_status\":\"0\",\"gender\":\"Male\",\"preferred_communication\":\"Email\",\"class\":\"Class 2\",\"edit_id\":\"8\"}'),
(90, 8, 'Update!', 'admin', '2024-10-29 12:55:30', '{\"patient_id\":8,\"username\":\"wenhau123\",\"password\":\"$2y$10$4n9nvH.76pPukwZwxZHwBeJSZ2Ot64s5SEPbp3GhMWzOwnkfKnDx6\",\"email\":\"tanwenhau9@gmail.com\",\"gender\":\"Male\",\"first_name\":\"Tan\",\"last_name\":\"Wen Hau\",\"dob\":\"2001-06-22\",\"contact_num\":\"+601126510938\",\"created_at\":\"2024-08-19 19:27:55\",\"lastlogin\":\"2024-10-29 12:09:28\",\"preferred_communication\":\"Email\",\"ques_status\":1,\"survey_status\":0,\"class\":\"Class 2\",\"session_count\":1,\"personality\":\"Type A\",\"compliance\":\"No\",\"bodytype\":\"Restricted\",\"focus\":\"Big\",\"lifestyle\":\"manager\",\"healthstatus\":\"Unhealthy\",\"goal\":\"asdasd\",\"outcome\":\"Set Alarm, Exercise\"}', '{\"username\":\"wenhau123\",\"email\":\"tanwenhau9@gmail.com\",\"first_name\":\"Tan\",\"last_name\":\"Wen Hau\",\"dob\":\"2001-06-22\",\"contact_num\":\"+601126510938\",\"survey_status\":\"0\",\"gender\":\"Male\",\"preferred_communication\":\"Email\",\"class\":\"Class 2\",\"edit_id\":\"8\"}'),
(91, 8, 'Update!', 'admin', '2024-10-29 12:56:20', '{\"patient_id\":8,\"username\":\"wenhau123\",\"password\":\"$2y$10$4n9nvH.76pPukwZwxZHwBeJSZ2Ot64s5SEPbp3GhMWzOwnkfKnDx6\",\"email\":\"tanwenhau9@gmail.com\",\"gender\":\"Male\",\"first_name\":\"Tan\",\"last_name\":\"Wen Hau\",\"dob\":\"2001-06-22\",\"contact_num\":\"+601126510938\",\"created_at\":\"2024-08-19 19:27:55\",\"lastlogin\":\"2024-10-29 12:09:28\",\"preferred_communication\":\"Email\",\"ques_status\":1,\"survey_status\":0,\"class\":\"Class 2\",\"session_count\":1,\"personality\":\"Type A\",\"compliance\":\"No\",\"bodytype\":\"Restricted\",\"focus\":\"Big\",\"lifestyle\":\"manager\",\"healthstatus\":\"Unhealthy\",\"goal\":\"asdasd\",\"outcome\":\"Set Alarm, Exercise\"}', '{\"username\":\"wenhau123\",\"email\":\"tanwenhau9@gmail.com\",\"first_name\":\"Tan\",\"last_name\":\"Wen Hau\",\"dob\":\"2001-06-22\",\"contact_num\":\"+601126510938\",\"survey_status\":\"1\",\"gender\":\"Male\",\"preferred_communication\":\"Email\",\"class\":\"Class 2\",\"edit_id\":\"8\"}'),
(92, 8, 'Update!', 'admin', '2024-10-29 12:57:43', '{\"patient_id\":8,\"username\":\"wenhau123\",\"password\":\"$2y$10$4n9nvH.76pPukwZwxZHwBeJSZ2Ot64s5SEPbp3GhMWzOwnkfKnDx6\",\"email\":\"tanwenhau9@gmail.com\",\"gender\":\"Male\",\"first_name\":\"Tan\",\"last_name\":\"Wen Hau\",\"dob\":\"2001-06-22\",\"contact_num\":\"+601126510938\",\"created_at\":\"2024-08-19 19:27:55\",\"lastlogin\":\"2024-10-29 12:09:28\",\"preferred_communication\":\"Email\",\"ques_status\":1,\"survey_status\":1,\"class\":\"Class 2\",\"session_count\":1,\"personality\":\"Type A\",\"compliance\":\"No\",\"bodytype\":\"Restricted\",\"focus\":\"Big\",\"lifestyle\":\"manager\",\"healthstatus\":\"Unhealthy\",\"goal\":\"asdasd\",\"outcome\":\"Set Alarm, Exercise\"}', '{\"username\":\"wenhau123\",\"email\":\"tanwenhau9@gmail.com\",\"first_name\":\"Tan\",\"last_name\":\"Wen Hau\",\"dob\":\"2001-06-22\",\"contact_num\":\"+601126510938\",\"survey_status\":\"1\",\"gender\":\"Male\",\"preferred_communication\":\"Email\",\"class\":\"Class 2\",\"edit_id\":\"8\"}'),
(93, 8, 'Update!', 'admin', '2024-10-29 13:06:46', '{\"patient_id\":8,\"username\":\"wenhau123\",\"password\":\"$2y$10$4n9nvH.76pPukwZwxZHwBeJSZ2Ot64s5SEPbp3GhMWzOwnkfKnDx6\",\"email\":\"tanwenhau9@gmail.com\",\"gender\":\"Male\",\"first_name\":\"Tan\",\"last_name\":\"Wen Hau\",\"dob\":\"2001-06-22\",\"contact_num\":\"+601126510938\",\"created_at\":\"2024-08-19 19:27:55\",\"lastlogin\":\"2024-10-29 12:09:28\",\"preferred_communication\":\"Email\",\"ques_status\":1,\"survey_status\":1,\"class\":\"Class 2\",\"session_count\":1,\"personality\":\"Type A\",\"compliance\":\"No\",\"bodytype\":\"Restricted\",\"focus\":\"Big\",\"lifestyle\":\"manager\",\"healthstatus\":\"Unhealthy\",\"goal\":\"asdasd\",\"outcome\":\"Set Alarm, Exercise\"}', '{\"username\":\"wenhau123\",\"email\":\"tanwenhau9@gmail.com\",\"first_name\":\"Tan\",\"last_name\":\"Wen Hau\",\"dob\":\"2001-06-22\",\"contact_num\":\"+601126510938\",\"survey_status\":\"1\",\"gender\":\"Male\",\"preferred_communication\":\"Email\",\"class\":\"Class 2\",\"edit_id\":\"8\"}'),
(94, 8, 'Update!', 'admin', '2024-10-29 13:26:01', '{\"patient_id\":8,\"username\":\"wenhau123\",\"password\":\"$2y$10$4n9nvH.76pPukwZwxZHwBeJSZ2Ot64s5SEPbp3GhMWzOwnkfKnDx6\",\"email\":\"tanwenhau9@gmail.com\",\"gender\":\"Male\",\"first_name\":\"Tan\",\"last_name\":\"Wen Hau\",\"dob\":\"2001-06-22\",\"contact_num\":\"+601126510938\",\"created_at\":\"2024-08-19 19:27:55\",\"lastlogin\":\"2024-10-29 12:09:28\",\"preferred_communication\":\"Email\",\"ques_status\":1,\"survey_status\":1,\"class\":\"Class 2\",\"session_count\":1,\"personality\":\"Type A\",\"compliance\":\"No\",\"bodytype\":\"Restricted\",\"focus\":\"Big\",\"lifestyle\":\"manager\",\"healthstatus\":\"Unhealthy\",\"goal\":\"asdasd\",\"outcome\":\"Set Alarm, Exercise\"}', '{\"username\":\"wenhau123\",\"email\":\"tanwenhau9@gmail.com\",\"first_name\":\"Tan\",\"last_name\":\"Wen Hau\",\"dob\":\"2001-06-22\",\"contact_num\":\"+601126510938\",\"survey_status\":\"1\",\"gender\":\"Male\",\"preferred_communication\":\"Email\",\"class\":\"Class 2\",\"edit_id\":\"8\"}'),
(95, 8, 'Update!', 'admin', '2024-10-29 13:29:07', '{\"patient_id\":8,\"username\":\"wenhau123\",\"password\":\"$2y$10$4n9nvH.76pPukwZwxZHwBeJSZ2Ot64s5SEPbp3GhMWzOwnkfKnDx6\",\"email\":\"tanwenhau9@gmail.com\",\"gender\":\"Male\",\"first_name\":\"Tan\",\"last_name\":\"Wen Hau\",\"dob\":\"2001-06-22\",\"contact_num\":\"+601126510938\",\"created_at\":\"2024-08-19 19:27:55\",\"lastlogin\":\"2024-10-29 12:09:28\",\"preferred_communication\":\"Email\",\"ques_status\":1,\"survey_status\":1,\"class\":\"Class 2\",\"session_count\":1,\"personality\":\"Type A\",\"compliance\":\"No\",\"bodytype\":\"Restricted\",\"focus\":\"Big\",\"lifestyle\":\"manager\",\"healthstatus\":\"Unhealthy\",\"goal\":\"asdasd\",\"outcome\":\"Set Alarm, Exercise\"}', '{\"username\":\"wenhau123\",\"email\":\"tanwenhau9@gmail.com\",\"first_name\":\"Tan\",\"last_name\":\"Wen Hau\",\"dob\":\"2001-06-22\",\"contact_num\":\"+601126510938\",\"gender\":\"Male\",\"preferred_communication\":\"Email\",\"class\":\"Class 2\",\"survey_status\":\"No\",\"edit_id\":\"8\"}'),
(96, 8, 'Update!', 'admin', '2024-10-29 13:29:16', '{\"patient_id\":8,\"username\":\"wenhau123\",\"password\":\"$2y$10$4n9nvH.76pPukwZwxZHwBeJSZ2Ot64s5SEPbp3GhMWzOwnkfKnDx6\",\"email\":\"tanwenhau9@gmail.com\",\"gender\":\"Male\",\"first_name\":\"Tan\",\"last_name\":\"Wen Hau\",\"dob\":\"2001-06-22\",\"contact_num\":\"+601126510938\",\"created_at\":\"2024-08-19 19:27:55\",\"lastlogin\":\"2024-10-29 12:09:28\",\"preferred_communication\":\"Email\",\"ques_status\":1,\"survey_status\":0,\"class\":\"Class 2\",\"session_count\":1,\"personality\":\"Type A\",\"compliance\":\"No\",\"bodytype\":\"Restricted\",\"focus\":\"Big\",\"lifestyle\":\"manager\",\"healthstatus\":\"Unhealthy\",\"goal\":\"asdasd\",\"outcome\":\"Set Alarm, Exercise\"}', '{\"username\":\"wenhau123\",\"email\":\"tanwenhau9@gmail.com\",\"first_name\":\"Tan\",\"last_name\":\"Wen Hau\",\"dob\":\"2001-06-22\",\"contact_num\":\"+601126510938\",\"gender\":\"Male\",\"preferred_communication\":\"Email\",\"class\":\"Class 2\",\"survey_status\":\"Yes\",\"edit_id\":\"8\"}'),
(97, 8, 'Update!', 'admin', '2024-10-29 13:29:19', '{\"patient_id\":8,\"username\":\"wenhau123\",\"password\":\"$2y$10$4n9nvH.76pPukwZwxZHwBeJSZ2Ot64s5SEPbp3GhMWzOwnkfKnDx6\",\"email\":\"tanwenhau9@gmail.com\",\"gender\":\"Male\",\"first_name\":\"Tan\",\"last_name\":\"Wen Hau\",\"dob\":\"2001-06-22\",\"contact_num\":\"+601126510938\",\"created_at\":\"2024-08-19 19:27:55\",\"lastlogin\":\"2024-10-29 12:09:28\",\"preferred_communication\":\"Email\",\"ques_status\":1,\"survey_status\":0,\"class\":\"Class 2\",\"session_count\":1,\"personality\":\"Type A\",\"compliance\":\"No\",\"bodytype\":\"Restricted\",\"focus\":\"Big\",\"lifestyle\":\"manager\",\"healthstatus\":\"Unhealthy\",\"goal\":\"asdasd\",\"outcome\":\"Set Alarm, Exercise\"}', '{\"username\":\"wenhau123\",\"email\":\"tanwenhau9@gmail.com\",\"first_name\":\"Tan\",\"last_name\":\"Wen Hau\",\"dob\":\"2001-06-22\",\"contact_num\":\"+601126510938\",\"gender\":\"Male\",\"preferred_communication\":\"Email\",\"class\":\"Class 2\",\"survey_status\":\"No\",\"edit_id\":\"8\"}'),
(98, 8, 'Update!', 'admin', '2024-10-29 13:29:23', '{\"patient_id\":8,\"username\":\"wenhau123\",\"password\":\"$2y$10$4n9nvH.76pPukwZwxZHwBeJSZ2Ot64s5SEPbp3GhMWzOwnkfKnDx6\",\"email\":\"tanwenhau9@gmail.com\",\"gender\":\"Male\",\"first_name\":\"Tan\",\"last_name\":\"Wen Hau\",\"dob\":\"2001-06-22\",\"contact_num\":\"+601126510938\",\"created_at\":\"2024-08-19 19:27:55\",\"lastlogin\":\"2024-10-29 12:09:28\",\"preferred_communication\":\"Email\",\"ques_status\":1,\"survey_status\":0,\"class\":\"Class 2\",\"session_count\":1,\"personality\":\"Type A\",\"compliance\":\"No\",\"bodytype\":\"Restricted\",\"focus\":\"Big\",\"lifestyle\":\"manager\",\"healthstatus\":\"Unhealthy\",\"goal\":\"asdasd\",\"outcome\":\"Set Alarm, Exercise\"}', '{\"username\":\"wenhau123\",\"email\":\"tanwenhau9@gmail.com\",\"first_name\":\"Tan\",\"last_name\":\"Wen Hau\",\"dob\":\"2001-06-22\",\"contact_num\":\"+601126510938\",\"gender\":\"Male\",\"preferred_communication\":\"Email\",\"class\":\"Class 2\",\"survey_status\":\"Yes\",\"edit_id\":\"8\"}'),
(99, 9, 'Update!', 'admin', '2024-10-29 13:29:28', '{\"patient_id\":9,\"username\":\"john22\",\"password\":\"$2y$10$RdOK2MQitF46Y10rl9Ohn.XnjvTlYsLw1zQi0h7Jv5i6kK\\/YDroJK\",\"email\":\"tanwenhau9@gmail.com\",\"gender\":\"Female\",\"first_name\":\"Sophie\",\"last_name\":\"Lee\",\"dob\":\"2000-02-11\",\"contact_num\":\"+601126510938\",\"created_at\":\"2024-08-19 22:15:49\",\"lastlogin\":\"2024-10-29 10:36:40\",\"preferred_communication\":\"Email\",\"ques_status\":1,\"survey_status\":1,\"class\":\"Class 1\",\"session_count\":10,\"personality\":\"Type B\",\"compliance\":\"Yes\",\"bodytype\":\"Restricted\",\"focus\":\"Open\",\"lifestyle\":\"manager\",\"healthstatus\":\"Risks\",\"goal\":\"good\",\"outcome\":\"Meditate, Exercise\"}', '{\"username\":\"john22\",\"email\":\"tanwenhau9@gmail.com\",\"first_name\":\"Sophie\",\"last_name\":\"Lee\",\"dob\":\"2000-02-11\",\"contact_num\":\"+601126510938\",\"gender\":\"Female\",\"preferred_communication\":\"Email\",\"class\":\"Class 1\",\"survey_status\":\"Yes\",\"edit_id\":\"9\"}'),
(100, 8, 'Update!', 'admin', '2024-10-29 13:32:44', '{\"patient_id\":8,\"username\":\"wenhau123\",\"password\":\"$2y$10$4n9nvH.76pPukwZwxZHwBeJSZ2Ot64s5SEPbp3GhMWzOwnkfKnDx6\",\"email\":\"tanwenhau9@gmail.com\",\"gender\":\"Male\",\"first_name\":\"Tan\",\"last_name\":\"Wen Hau\",\"dob\":\"2001-06-22\",\"contact_num\":\"+601126510938\",\"created_at\":\"2024-08-19 19:27:55\",\"lastlogin\":\"2024-10-29 12:09:28\",\"preferred_communication\":\"Email\",\"ques_status\":1,\"survey_status\":0,\"class\":\"Class 2\",\"session_count\":1,\"personality\":\"Type A\",\"compliance\":\"No\",\"bodytype\":\"Restricted\",\"focus\":\"Big\",\"lifestyle\":\"manager\",\"healthstatus\":\"Unhealthy\",\"goal\":\"asdasd\",\"outcome\":\"Set Alarm, Exercise\"}', '{\"username\":\"wenhau123\",\"email\":\"tanwenhau9@gmail.com\",\"first_name\":\"Tan\",\"last_name\":\"Wen Hau\",\"dob\":\"2001-06-22\",\"contact_num\":\"+601126510938\",\"gender\":\"Male\",\"preferred_communication\":\"Email\",\"class\":\"Class 2\",\"survey_status\":\"1\",\"edit_id\":\"8\"}'),
(101, 8, 'Update!', 'admin', '2024-10-29 13:37:48', '{\"patient_id\":8,\"username\":\"wenhau123\",\"password\":\"$2y$10$4n9nvH.76pPukwZwxZHwBeJSZ2Ot64s5SEPbp3GhMWzOwnkfKnDx6\",\"email\":\"tanwenhau9@gmail.com\",\"gender\":\"Male\",\"first_name\":\"Tan\",\"last_name\":\"Wen Hau\",\"dob\":\"2001-06-22\",\"contact_num\":\"+601126510938\",\"created_at\":\"2024-08-19 19:27:55\",\"lastlogin\":\"2024-10-29 12:09:28\",\"preferred_communication\":\"Email\",\"ques_status\":1,\"survey_status\":1,\"class\":\"Class 2\",\"session_count\":1,\"personality\":\"Type A\",\"compliance\":\"No\",\"bodytype\":\"Restricted\",\"focus\":\"Big\",\"lifestyle\":\"manager\",\"healthstatus\":\"Unhealthy\",\"goal\":\"asdasd\",\"outcome\":\"Set Alarm, Exercise\"}', '{\"username\":\"wenhau123\",\"email\":\"tanwenhau9@gmail.com\",\"first_name\":\"Tan\",\"last_name\":\"Wen Hau\",\"dob\":\"2001-06-22\",\"contact_num\":\"+601126510938\",\"edit_id\":\"8\",\"gender\":null,\"preferred_communication\":null,\"class\":null,\"survey_status\":null}'),
(102, 8, 'Update!', 'admin', '2024-10-29 13:38:21', '{\"patient_id\":8,\"username\":\"wenhau123\",\"password\":\"$2y$10$4n9nvH.76pPukwZwxZHwBeJSZ2Ot64s5SEPbp3GhMWzOwnkfKnDx6\",\"email\":\"tanwenhau9@gmail.com\",\"gender\":null,\"first_name\":\"Tan\",\"last_name\":\"Wen Hau\",\"dob\":\"2001-06-22\",\"contact_num\":\"+601126510938\",\"created_at\":\"2024-08-19 19:27:55\",\"lastlogin\":\"2024-10-29 12:09:28\",\"preferred_communication\":\"\",\"ques_status\":1,\"survey_status\":0,\"class\":\"\",\"session_count\":1,\"personality\":\"Type A\",\"compliance\":\"No\",\"bodytype\":\"Restricted\",\"focus\":\"Big\",\"lifestyle\":\"manager\",\"healthstatus\":\"Unhealthy\",\"goal\":\"asdasd\",\"outcome\":\"Set Alarm, Exercise\"}', '{\"username\":\"wenhau123\",\"email\":\"tanwenhau9@gmail.com\",\"first_name\":\"Tan\",\"last_name\":\"Wen Hau\",\"dob\":\"2001-06-22\",\"contact_num\":\"+601126510938\",\"gender\":\"Male\",\"preferred_communication\":\"Email\",\"class\":\"Class 1\",\"survey_status\":\"Yes\",\"edit_id\":\"8\"}'),
(103, 8, 'Update!', 'admin', '2024-10-29 13:40:39', '{\"patient_id\":8,\"username\":\"wenhau123\",\"password\":\"$2y$10$4n9nvH.76pPukwZwxZHwBeJSZ2Ot64s5SEPbp3GhMWzOwnkfKnDx6\",\"email\":\"tanwenhau9@gmail.com\",\"gender\":\"Male\",\"first_name\":\"Tan\",\"last_name\":\"Wen Hau\",\"dob\":\"2001-06-22\",\"contact_num\":\"+601126510938\",\"created_at\":\"2024-08-19 19:27:55\",\"lastlogin\":\"2024-10-29 12:09:28\",\"preferred_communication\":\"Email\",\"ques_status\":1,\"survey_status\":0,\"class\":\"Class 1\",\"session_count\":1,\"personality\":\"Type A\",\"compliance\":\"No\",\"bodytype\":\"Restricted\",\"focus\":\"Big\",\"lifestyle\":\"manager\",\"healthstatus\":\"Unhealthy\",\"goal\":\"asdasd\",\"outcome\":\"Set Alarm, Exercise\"}', '{\"username\":\"wenhau123\",\"email\":\"tanwenhau9@gmail.com\",\"gender\":\"Male\",\"first_name\":\"Tan\",\"last_name\":\"Wen Hau\",\"dob\":\"2001-06-22\",\"contact_num\":\"+601126510938\",\"preferred_communication\":\"Email\",\"class\":\"Class 1\",\"survey_status\":\"No\",\"edit_id\":\"8\"}'),
(104, 8, 'Update!', 'admin', '2024-10-29 13:40:43', '{\"patient_id\":8,\"username\":\"wenhau123\",\"password\":\"$2y$10$4n9nvH.76pPukwZwxZHwBeJSZ2Ot64s5SEPbp3GhMWzOwnkfKnDx6\",\"email\":\"tanwenhau9@gmail.com\",\"gender\":\"Male\",\"first_name\":\"Tan\",\"last_name\":\"Wen Hau\",\"dob\":\"2001-06-22\",\"contact_num\":\"+601126510938\",\"created_at\":\"2024-08-19 19:27:55\",\"lastlogin\":\"2024-10-29 12:09:28\",\"preferred_communication\":\"Email\",\"ques_status\":1,\"survey_status\":0,\"class\":\"Class 1\",\"session_count\":1,\"personality\":\"Type A\",\"compliance\":\"No\",\"bodytype\":\"Restricted\",\"focus\":\"Big\",\"lifestyle\":\"manager\",\"healthstatus\":\"Unhealthy\",\"goal\":\"asdasd\",\"outcome\":\"Set Alarm, Exercise\"}', '{\"username\":\"wenhau123\",\"email\":\"tanwenhau9@gmail.com\",\"gender\":\"Male\",\"first_name\":\"Tan\",\"last_name\":\"Wen Hau\",\"dob\":\"2001-06-22\",\"contact_num\":\"+601126510938\",\"preferred_communication\":\"Email\",\"class\":\"Class 1\",\"survey_status\":\"Yes\",\"edit_id\":\"8\"}'),
(105, 8, 'Update!', 'admin', '2024-10-29 13:43:08', '{\"patient_id\":8,\"username\":\"wenhau123\",\"password\":\"$2y$10$4n9nvH.76pPukwZwxZHwBeJSZ2Ot64s5SEPbp3GhMWzOwnkfKnDx6\",\"email\":\"tanwenhau9@gmail.com\",\"gender\":\"Male\",\"first_name\":\"Tan\",\"last_name\":\"Wen Hau\",\"dob\":\"2001-06-22\",\"contact_num\":\"+601126510938\",\"created_at\":\"2024-08-19 19:27:55\",\"lastlogin\":\"2024-10-29 12:09:28\",\"preferred_communication\":\"Email\",\"ques_status\":1,\"survey_status\":\"Yes\",\"class\":\"Class 1\",\"session_count\":1,\"personality\":\"Type A\",\"compliance\":\"No\",\"bodytype\":\"Restricted\",\"focus\":\"Big\",\"lifestyle\":\"manager\",\"healthstatus\":\"Unhealthy\",\"goal\":\"asdasd\",\"outcome\":\"Set Alarm, Exercise\"}', '{\"username\":\"wenhau123\",\"email\":\"tanwenhau9@gmail.com\",\"first_name\":\"Tan\",\"last_name\":\"Wen Hau\",\"dob\":\"2001-06-22\",\"contact_num\":\"+601126510938\",\"gender\":\"Male\",\"preferred_communication\":\"Email\",\"class\":\"Class 1\",\"survey_status\":\"No\",\"edit_id\":\"8\"}'),
(106, 9, 'Update!', 'admin', '2024-10-29 15:26:04', '{\"patient_id\":9,\"username\":\"john22\",\"password\":\"$2y$10$RdOK2MQitF46Y10rl9Ohn.XnjvTlYsLw1zQi0h7Jv5i6kK\\/YDroJK\",\"email\":\"tanwenhau9@gmail.com\",\"gender\":\"Female\",\"first_name\":\"Sophie\",\"last_name\":\"Lee\",\"dob\":\"2000-02-11\",\"contact_num\":\"+601126510938\",\"created_at\":\"2024-08-19 22:15:49\",\"lastlogin\":\"2024-10-29 15:21:10\",\"preferred_communication\":\"Email\",\"ques_status\":1,\"survey_status\":\"Yes\",\"class\":\"Class 1\",\"session_count\":20,\"personality\":\"Type B\",\"compliance\":\"Yes\",\"bodytype\":\"Restricted\",\"focus\":\"Open\",\"lifestyle\":\"manager\",\"healthstatus\":\"Risks\",\"goal\":\"good\",\"outcome\":\"Meditate, Exercise\"}', '{\"username\":\"john22\",\"email\":\"tanwenhau9@gmail.com\",\"first_name\":\"Sophie\",\"last_name\":\"Lee\",\"dob\":\"2000-02-11\",\"contact_num\":\"+601126510938\",\"gender\":\"Female\",\"preferred_communication\":\"Email\",\"class\":\"Class 1\",\"survey_status\":\"No\",\"edit_id\":\"9\"}'),
(107, 11, 'Update!', 'admin', '2024-10-30 12:01:12', '{\"patient_id\":11,\"username\":\"bangla\",\"password\":\"$2y$10$JroBD1V4zeB.Cw4uROOfSudgILJdUHTaKtOBJoVfouUzBsYY9J.dG\",\"email\":\"bangla@mail.com\",\"gender\":\"male\",\"first_name\":\"Bangla\",\"last_name\":\"Man\",\"dob\":\"2000-10-10\",\"contact_num\":\"+609876543216\",\"created_at\":\"2024-10-13 22:01:26\",\"lastlogin\":\"2024-10-17 18:05:09\",\"preferred_communication\":\"email\",\"ques_status\":1,\"survey_status\":\"Yes\",\"class\":\"class1\",\"session_count\":null,\"personality\":null,\"compliance\":null,\"bodytype\":null,\"focus\":null,\"lifestyle\":null,\"healthstatus\":null,\"goal\":null,\"outcome\":null}', '{\"username\":\"bangla\",\"email\":\"bangla@mail.com\",\"first_name\":\"Bangla\",\"last_name\":\"Man\",\"dob\":\"2000-10-10\",\"contact_num\":\"+609876543216\",\"gender\":\"Male\",\"preferred_communication\":\"Email\",\"class\":\"Class 1\",\"survey_status\":\"No\",\"edit_id\":\"11\"}');

-- --------------------------------------------------------

--
-- Table structure for table `payroll`
--

CREATE TABLE `payroll` (
  `payroll_id` int(11) NOT NULL,
  `staff_id` int(11) DEFAULT NULL,
  `salary` decimal(10,2) NOT NULL,
  `pay_date` date NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `reviews`
--

CREATE TABLE `reviews` (
  `id` int(11) NOT NULL,
  `review` text NOT NULL,
  `suggestion` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `star_rating` int(11) NOT NULL DEFAULT 0,
  `userName` varchar(100) DEFAULT NULL,
  `staffName` varchar(100) DEFAULT NULL,
  `role` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `reviews`
--

INSERT INTO `reviews` (`id`, `review`, `suggestion`, `created_at`, `star_rating`, `userName`, `staffName`, `role`) VALUES
(1, 'good', 'nothing', '2024-10-01 00:26:26', 5, 'john22', 'john', 'Therapist'),
(2, 'Nice doctor', '', '2024-10-01 04:54:18', 5, 'noob', 'john', 'Therapist'),
(3, 'Bad doctor', '', '2024-10-01 07:41:43', 1, 'abc123', 'jane', 'Therapist'),
(4, 'Not bad', 'NOO', '2024-10-01 14:17:05', 4, 'abc123', 'osas', 'HR'),
(5, 'Yer', 'suck', '2024-10-01 14:18:16', 2, 'abc123', 'jane', 'Therapist'),
(6, 'ok', 'sus', '2024-10-01 14:18:27', 3, 'abc123', 'john', 'Therapist'),
(7, 'l', '', '2024-10-01 14:22:54', 3, 'abc123', 'osas', 'HR'),
(8, 'l', '', '2024-10-01 14:23:35', 3, 'abc123', 'osas', 'HR'),
(9, 'too bad worst session ever ', 'the thrapist is so rude to me', '2024-10-29 00:46:09', 1, 'john22', 'john', 'Therapist'),
(10, 'lmao bad session', 'she is trash fire her', '2024-10-29 00:49:36', 2, 'john22', 'jane', 'Therapist'),
(11, 'fghfg', 'fdggh', '2024-10-29 00:54:09', 1, 'john22', 'john', 'Therapist'),
(12, 'asdasd', 'asdasd', '2024-10-29 10:21:23', 3, 'wenhau123', 'john', 'Therapist');

-- --------------------------------------------------------

--
-- Table structure for table `schedules`
--

CREATE TABLE `schedules` (
  `schedule_id` int(11) NOT NULL,
  `staff_id` int(11) NOT NULL,
  `start_time` datetime NOT NULL,
  `end_time` datetime NOT NULL,
  `type` enum('break','leave','booked') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `schedules`
--

INSERT INTO `schedules` (`schedule_id`, `staff_id`, `start_time`, `end_time`, `type`) VALUES
(1, 2, '2024-10-14 12:30:00', '2024-10-14 13:30:00', 'break'),
(2, 2, '2024-11-25 00:00:00', '2024-12-02 00:00:00', 'leave'),
(3, 7, '2024-10-14 15:30:00', '2024-10-14 16:30:00', 'break'),
(4, 7, '2024-10-15 12:30:00', '2024-10-15 13:30:00', 'break'),
(5, 7, '2024-10-20 10:00:00', '2024-10-20 11:00:00', 'booked'),
(6, 2, '2024-10-19 10:00:00', '2024-10-19 11:00:00', 'booked'),
(7, 2, '2024-10-30 12:53:00', '2024-10-30 13:53:00', 'booked'),
(8, 2, '2024-11-01 15:04:00', '2024-11-01 16:04:00', 'booked');

-- --------------------------------------------------------

--
-- Table structure for table `staffs`
--

CREATE TABLE `staffs` (
  `staff_id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(100) NOT NULL,
  `gender` varchar(10) DEFAULT NULL,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `dob` date NOT NULL,
  `contact_num` varchar(100) DEFAULT NULL,
  `role` enum('superadmin','admin','hr','physiotherapist','trainer') NOT NULL,
  `lock_status` enum('No','Yes') NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `lastlogin` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `staffs`
--

INSERT INTO `staffs` (`staff_id`, `username`, `password`, `email`, `gender`, `first_name`, `last_name`, `dob`, `contact_num`, `role`, `lock_status`, `created_at`, `lastlogin`) VALUES
(1, 'admin', '$2y$10$1amslCyGNtLz.mzAOF/zF.AdUsf49Fq31uprPhr08N7s1uMKKeaEm', 'admin@physiowerkz.com', 'Female', 'Admin', 'Real', '2000-01-01', '111', 'admin', 'No', '2024-07-14 07:10:10', '2024-10-30 07:40:05'),
(2, 'john', '$2y$10$xty6ooDseWw8G4IhYuo/juublyQ8t30UWg/G36MwSEy3cZiJh/gvq', 'john@physiowerkz.com', 'Male', 'John', 'Doe', '1994-07-05', '0123456789', 'physiotherapist', 'No', '2024-07-15 05:29:41', '2024-10-30 07:40:53'),
(6, 'osas', '$2y$10$mOfirShM8wCklINSGFrTXe/Uz5kagG8cNWZO.ierAbgiO75p00DLq', 'osas@physiowerkz.com', 'Female', 'Uveuvuevu', 'Osas', '1990-01-01', '123', 'hr', 'No', '2024-07-16 10:50:05', '2024-08-05 08:39:06'),
(7, 'jane', '$2y$10$9an1ESuxe.fHefFNIjjGXeBGNAzrYNc1Wiqozlyaylivev0KntfdO', 'jane@mail.com', 'Female', 'Jane', 'Doe', '1990-10-10', '123455667788', 'physiotherapist', 'No', '2024-10-01 07:23:12', '2024-10-13 12:50:28'),
(13, 'peter', '$2y$10$lGFpWCDZ4mYlCvAa56vMFeDHbGp.LsJ7zeb32yhMtwa242.nswdGe', 'peter@mail.com', 'Male', 'Peter', 'Lim', '1990-10-10', '9876543216', 'hr', 'No', '2024-10-01 13:20:41', NULL),
(14, 'superadmin', '$2y$10$yGNZIFgbIbSxqvOL38W9ae1b4A7dxe9DP/L5yNnV5s9GirPVkLljG', 'superadmin@gmail.com', 'Male', 'super', 'admin', '2000-02-29', '112223133', 'superadmin', 'No', '2024-10-30 01:59:00', '2024-10-30 07:39:40');

-- --------------------------------------------------------

--
-- Table structure for table `staff_changes_log`
--

CREATE TABLE `staff_changes_log` (
  `log_id` int(11) NOT NULL,
  `staff_id` int(11) NOT NULL,
  `change_type` varchar(10) NOT NULL,
  `changed_by` varchar(50) NOT NULL,
  `change_time` datetime DEFAULT current_timestamp(),
  `old_values` text DEFAULT NULL,
  `new_values` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `staff_changes_log`
--

INSERT INTO `staff_changes_log` (`log_id`, `staff_id`, `change_type`, `changed_by`, `change_time`, `old_values`, `new_values`) VALUES
(34, 1, 'Inserted!', 'admin', '2024-09-25 21:32:17', '{\"username\":\"korean\",\"password\":\"nightmaple123\",\"confirm_password\":\"nightmaple123\",\"email\":\"tanwenhau9@gmail.com\",\"gender\":\"Female\",\"first_name\":\"Wen Hau\",\"last_name\":\"Tan\",\"dob\":\"1994-11-24\",\"contact_num\":\"01126510938\",\"role\":\"physiotherapist\"}', NULL),
(35, 1, 'Deleted!', 'admin', '2024-09-25 21:33:43', '{\"staff_id\":10,\"username\":\"korean\",\"password\":\"$2y$10$5bpZWr8oMdTpcDwzg2SYd.K3ds2KT.GiwefqH0WPQEtvXHQb0\\/Mea\",\"email\":\"tanwenhau9@gmail.com\",\"gender\":\"Female\",\"first_name\":\"Wen Hau\",\"last_name\":\"Tan\",\"dob\":\"1994-11-24\",\"contact_num\":\"01126510938\",\"role\":\"physiotherapist\",\"created_at\":\"2024-09-25 21:32:17\",\"lastlogin\":null}', NULL),
(36, 1, 'Updated!', 'admin', '2024-09-25 21:53:30', '{\"staff_id\":1,\"username\":\"admin\",\"password\":\"$2y$10$1amslCyGNtLz.mzAOF\\/zF.AdUsf49Fq31uprPhr08N7s1uMKKeaEm\",\"email\":\"admin@physiowerkz.com\",\"gender\":\"Male\",\"first_name\":\"Admin\",\"last_name\":\"Real\",\"dob\":\"2000-01-01\",\"contact_num\":\"111\",\"role\":\"admin\",\"created_at\":\"2024-07-14 15:10:10\",\"lastlogin\":\"2024-09-25 21:53:21\"}', '{\"username\":\"admin\",\"email\":\"admin@physiowerkz.com\",\"first_name\":\"Admin\",\"last_name\":\"Real\",\"dob\":\"2000-01-01\",\"contact_num\":\"111\",\"gender\":\"Female\",\"role\":\"admin\",\"edit_id\":\"1\"}'),
(37, 1, 'Inserted!', 'admin', '2024-10-01 15:23:12', '{\"username\":\"jane\",\"password\":\"jane123\",\"confirm_password\":\"jane123\",\"email\":\"jane@mail.com\",\"gender\":\"Male\",\"first_name\":\"Jane\",\"last_name\":\"Doe\",\"dob\":\"1990-10-10\",\"contact_num\":\"123455667788\",\"role\":\"physiotherapist\"}', NULL),
(38, 1, 'Inserted!', 'admin', '2024-10-01 15:24:32', '{\"username\":\"jane123\",\"password\":\"jane123\",\"confirm_password\":\"jane123\",\"email\":\"jane@mail.com\",\"gender\":\"Male\",\"first_name\":\"Jane\",\"last_name\":\"Doe\",\"dob\":\"1990-10-10\",\"contact_num\":\"123455667788\",\"role\":\"physiotherapist\"}', NULL),
(39, 1, 'Inserted!', 'admin', '2024-10-01 21:02:32', '{\"username\":\"Peter\",\"password\":\"peter123\",\"confirm_password\":\"peter123\",\"email\":\"peter@mail.com\",\"gender\":\"Male\",\"first_name\":\"Peter\",\"last_name\":\"Lim\",\"dob\":\"1990-10-10\",\"area_code\":\"+60\",\"contact_num\":\"9876543216\",\"role\":\"hr\"}', NULL),
(40, 1, 'Inserted!', 'admin', '2024-10-01 21:03:20', '{\"username\":\"Peter\",\"password\":\"peter123\",\"confirm_password\":\"peter123\",\"email\":\"peter@mail.com\",\"gender\":\"Male\",\"first_name\":\"Peter\",\"last_name\":\"Lim\",\"dob\":\"1990-10-10\",\"area_code\":\"+60\",\"contact_num\":\"9876543216\",\"role\":\"hr\"}', NULL),
(41, 1, 'Inserted!', 'admin', '2024-10-01 21:05:39', '{\"username\":\"Peter\",\"password\":\"peter123\",\"confirm_password\":\"peter123\",\"email\":\"peter@mail.com\",\"gender\":\"Male\",\"first_name\":\"Peter\",\"last_name\":\"Lim\",\"dob\":\"1990-10-10\",\"area_code\":\"+60\",\"contact_num\":\"9876543216\",\"role\":\"hr\"}', NULL),
(42, 1, 'Inserted!', 'admin', '2024-10-01 21:14:09', '{\"username\":\"peter\",\"password\":\"peter123\",\"confirm_password\":\"peter123\",\"email\":\"peter@mail.com\",\"gender\":\"Male\",\"first_name\":\"Peter\",\"last_name\":\"Lim\",\"dob\":\"1990-10-10\",\"area_code\":\"+60\",\"contact_num\":\"9876543216\",\"role\":\"hr\"}', NULL),
(43, 1, 'Inserted!', 'admin', '2024-10-01 21:20:41', '{\"username\":\"peter\",\"password\":\"peter123\",\"confirm_password\":\"peter123\",\"email\":\"peter@mail.com\",\"gender\":\"Male\",\"first_name\":\"Peter\",\"last_name\":\"Lim\",\"dob\":\"1990-10-10\",\"area_code\":\"+60\",\"contact_num\":\"9876543216\",\"role\":\"hr\"}', NULL),
(44, 1, 'Inserted!', 'admin', '2024-10-30 09:59:00', '{\"username\":\"superadmin\",\"password\":\"super123\",\"confirm_password\":\"super123\",\"email\":\"superadmin@gmail.com\",\"gender\":\"Male\",\"first_name\":\"super\",\"last_name\":\"admin\",\"dob\":\"2000-02-29\",\"area_code\":\"+60\",\"contact_num\":\"112223133\",\"role\":\"admin\"}', NULL),
(45, 14, 'Updated!', 'superadmin', '2024-10-30 11:18:09', '{\"staff_id\":1,\"username\":\"admin\",\"password\":\"$2y$10$1amslCyGNtLz.mzAOF\\/zF.AdUsf49Fq31uprPhr08N7s1uMKKeaEm\",\"email\":\"admin@physiowerkz.com\",\"gender\":\"Female\",\"first_name\":\"Admin\",\"last_name\":\"Real\",\"dob\":\"2000-01-01\",\"contact_num\":\"111\",\"role\":\"admin\",\"lock_status\":\"No\",\"created_at\":\"2024-07-14 15:10:10\",\"lastlogin\":\"2024-10-30 11:14:30\"}', '{\"username\":\"admin\",\"email\":\"admin@physiowerkz.com\",\"first_name\":\"Admin\",\"last_name\":\"Real\",\"dob\":\"2000-01-01\",\"contact_num\":\"111\",\"gender\":\"Female\",\"role\":\"admin\",\"lock_status\":\"Yes\",\"edit_id\":\"1\"}'),
(46, 14, 'Updated!', 'superadmin', '2024-10-30 11:20:18', '{\"staff_id\":2,\"username\":\"john\",\"password\":\"$2y$10$xty6ooDseWw8G4IhYuo\\/juublyQ8t30UWg\\/G36MwSEy3cZiJh\\/gvq\",\"email\":\"john@physiowerkz.com\",\"gender\":\"Male\",\"first_name\":\"John\",\"last_name\":\"Doe\",\"dob\":\"1994-07-05\",\"contact_num\":\"0123456789\",\"role\":\"physiotherapist\",\"lock_status\":\"No\",\"created_at\":\"2024-07-15 13:29:41\",\"lastlogin\":\"2024-10-30 11:19:10\"}', '{\"username\":\"john\",\"email\":\"john@physiowerkz.com\",\"first_name\":\"John\",\"last_name\":\"Doe\",\"dob\":\"1994-07-05\",\"contact_num\":\"0123456789\",\"gender\":\"Male\",\"role\":\"physiotherapist\",\"lock_status\":\"Yes\",\"edit_id\":\"2\"}'),
(47, 14, 'Updated!', 'superadmin', '2024-10-30 11:32:59', '{\"staff_id\":1,\"username\":\"admin\",\"password\":\"$2y$10$1amslCyGNtLz.mzAOF\\/zF.AdUsf49Fq31uprPhr08N7s1uMKKeaEm\",\"email\":\"admin@physiowerkz.com\",\"gender\":\"Female\",\"first_name\":\"Admin\",\"last_name\":\"Real\",\"dob\":\"2000-01-01\",\"contact_num\":\"111\",\"role\":\"admin\",\"lock_status\":\"Yes\",\"created_at\":\"2024-07-14 15:10:10\",\"lastlogin\":\"2024-10-30 11:26:15\"}', '{\"username\":\"admin\",\"email\":\"admin@physiowerkz.com\",\"first_name\":\"Admin\",\"last_name\":\"Real\",\"dob\":\"2000-01-01\",\"contact_num\":\"111\",\"gender\":\"Female\",\"role\":\"admin\",\"lock_status\":\"No\",\"edit_id\":\"1\"}'),
(48, 14, 'Updated!', 'superadmin', '2024-10-30 12:05:33', '{\"staff_id\":1,\"username\":\"admin\",\"password\":\"$2y$10$1amslCyGNtLz.mzAOF\\/zF.AdUsf49Fq31uprPhr08N7s1uMKKeaEm\",\"email\":\"admin@physiowerkz.com\",\"gender\":\"Female\",\"first_name\":\"Admin\",\"last_name\":\"Real\",\"dob\":\"2000-01-01\",\"contact_num\":\"111\",\"role\":\"admin\",\"lock_status\":\"No\",\"created_at\":\"2024-07-14 15:10:10\",\"lastlogin\":\"2024-10-30 12:03:33\"}', '{\"username\":\"admin\",\"email\":\"admin@physiowerkz.com\",\"first_name\":\"Admin\",\"last_name\":\"Real\",\"dob\":\"2000-01-01\",\"contact_num\":\"111\",\"gender\":\"Female\",\"role\":\"admin\",\"lock_status\":\"Yes\",\"edit_id\":\"1\"}'),
(49, 14, 'Updated!', 'superadmin', '2024-10-30 13:58:20', '{\"staff_id\":1,\"username\":\"admin\",\"password\":\"$2y$10$1amslCyGNtLz.mzAOF\\/zF.AdUsf49Fq31uprPhr08N7s1uMKKeaEm\",\"email\":\"admin@physiowerkz.com\",\"gender\":\"Female\",\"first_name\":\"Admin\",\"last_name\":\"Real\",\"dob\":\"2000-01-01\",\"contact_num\":\"111\",\"role\":\"admin\",\"lock_status\":\"Yes\",\"created_at\":\"2024-07-14 15:10:10\",\"lastlogin\":\"2024-10-30 13:56:04\"}', '{\"username\":\"admin\",\"email\":\"admin@physiowerkz.com\",\"first_name\":\"Admin\",\"last_name\":\"Real\",\"dob\":\"2000-01-01\",\"contact_num\":\"111\",\"gender\":\"Female\",\"role\":\"admin\",\"lock_status\":\"No\",\"edit_id\":\"1\"}'),
(50, 1, 'Updated!', 'admin', '2024-10-30 13:58:45', '{\"staff_id\":1,\"username\":\"admin\",\"password\":\"$2y$10$1amslCyGNtLz.mzAOF\\/zF.AdUsf49Fq31uprPhr08N7s1uMKKeaEm\",\"email\":\"admin@physiowerkz.com\",\"gender\":\"Female\",\"first_name\":\"Admin\",\"last_name\":\"Real\",\"dob\":\"2000-01-01\",\"contact_num\":\"111\",\"role\":\"admin\",\"lock_status\":\"No\",\"created_at\":\"2024-07-14 15:10:10\",\"lastlogin\":\"2024-10-30 13:58:39\"}', '{\"username\":\"admin\",\"email\":\"admin@physiowerkz.com\",\"first_name\":\"Admin\",\"last_name\":\"Real\",\"dob\":\"2000-01-01\",\"contact_num\":\"111\",\"gender\":\"Female\",\"role\":\"admin\",\"edit_id\":\"1\"}'),
(51, 14, 'Updated!', 'superadmin', '2024-10-30 14:01:59', '{\"staff_id\":1,\"username\":\"admin\",\"password\":\"$2y$10$1amslCyGNtLz.mzAOF\\/zF.AdUsf49Fq31uprPhr08N7s1uMKKeaEm\",\"email\":\"admin@physiowerkz.com\",\"gender\":\"Female\",\"first_name\":\"Admin\",\"last_name\":\"Real\",\"dob\":\"2000-01-01\",\"contact_num\":\"111\",\"role\":\"admin\",\"lock_status\":\"No\",\"created_at\":\"2024-07-14 15:10:10\",\"lastlogin\":\"2024-10-30 14:01:28\"}', '{\"username\":\"admin\",\"email\":\"admin@physiowerkz.com\",\"first_name\":\"Admin\",\"last_name\":\"Real\",\"dob\":\"2000-01-01\",\"contact_num\":\"111\",\"gender\":\"Female\",\"role\":\"admin\",\"lock_status\":\"Yes\",\"edit_id\":\"1\"}'),
(52, 14, 'Updated!', 'superadmin', '2024-10-30 15:03:31', '{\"staff_id\":2,\"username\":\"john\",\"password\":\"$2y$10$xty6ooDseWw8G4IhYuo\\/juublyQ8t30UWg\\/G36MwSEy3cZiJh\\/gvq\",\"email\":\"john@physiowerkz.com\",\"gender\":\"Male\",\"first_name\":\"John\",\"last_name\":\"Doe\",\"dob\":\"1994-07-05\",\"contact_num\":\"0123456789\",\"role\":\"physiotherapist\",\"lock_status\":\"Yes\",\"created_at\":\"2024-07-15 13:29:41\",\"lastlogin\":\"2024-10-30 15:03:10\"}', '{\"username\":\"john\",\"email\":\"john@physiowerkz.com\",\"first_name\":\"John\",\"last_name\":\"Doe\",\"dob\":\"1994-07-05\",\"contact_num\":\"0123456789\",\"gender\":\"Male\",\"role\":\"physiotherapist\",\"lock_status\":\"No\",\"edit_id\":\"2\"}'),
(53, 14, 'Updated!', 'superadmin', '2024-10-30 15:39:35', '{\"staff_id\":1,\"username\":\"admin\",\"password\":\"$2y$10$1amslCyGNtLz.mzAOF\\/zF.AdUsf49Fq31uprPhr08N7s1uMKKeaEm\",\"email\":\"admin@physiowerkz.com\",\"gender\":\"Female\",\"first_name\":\"Admin\",\"last_name\":\"Real\",\"dob\":\"2000-01-01\",\"contact_num\":\"111\",\"role\":\"admin\",\"lock_status\":\"Yes\",\"created_at\":\"2024-07-14 15:10:10\",\"lastlogin\":\"2024-10-30 15:39:04\"}', '{\"username\":\"admin\",\"email\":\"admin@physiowerkz.com\",\"first_name\":\"Admin\",\"last_name\":\"Real\",\"dob\":\"2000-01-01\",\"contact_num\":\"111\",\"gender\":\"Female\",\"role\":\"admin\",\"lock_status\":\"No\",\"edit_id\":\"1\"}');

-- --------------------------------------------------------

--
-- Table structure for table `test_tracking`
--

CREATE TABLE `test_tracking` (
  `id` int(11) NOT NULL,
  `appointment_id` int(11) NOT NULL,
  `patient_id` int(11) NOT NULL,
  `test_result` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `test_tracking`
--

INSERT INTO `test_tracking` (`id`, `appointment_id`, `patient_id`, `test_result`) VALUES
(7, 28, 8, 'Test: Scaption, Restriction: Severe, Pain: Severe, Block: Severe, Remarks: '),
(9, 28, 8, 'Test: Short Flexion (SF), Restriction: Severe, Pain: Severe, Block: Severe, Remarks: wewer'),
(12, 38, 8, 'Test: Short Flexion (SF), Restriction: Severe, Pain: Mild, Block: Severe, Remarks: uyjyu'),
(13, 38, 8, 'Test: Long Traction *Left* / Right *Hip* / Lumbar, Restriction: Severe, Pain: Severe, Block: Severe, Remarks: jy'),
(14, 38, 8, 'Test: PSLR, Restriction: Severe, Pain: Severe, Block: Severe, Remarks: yuj');

-- --------------------------------------------------------

--
-- Table structure for table `treatmentassessment`
--

CREATE TABLE `treatmentassessment` (
  `id` int(11) NOT NULL,
  `appointment_id` int(11) NOT NULL,
  `patient_id` int(11) NOT NULL,
  `Name` varchar(255) DEFAULT NULL,
  `painanddescription` text NOT NULL,
  `activity` varchar(255) NOT NULL,
  `severe_activity` enum('short','medium','long') NOT NULL,
  `remarks_activity` text DEFAULT NULL,
  `vas_activity` int(11) DEFAULT NULL,
  `irritability_activity` enum('low','moderate','high') NOT NULL,
  `symptoms_activity` text DEFAULT NULL,
  `acute_activity` enum('2-6_wk','acute_on_chronic','chronic') NOT NULL,
  `indicates_activity` text DEFAULT NULL,
  `dos_donts_activity` text DEFAULT NULL,
  `duration_activity` varchar(255) DEFAULT NULL,
  `aware_of_pain_body_activity` enum('yes','no') NOT NULL,
  `movement_activity` text DEFAULT NULL,
  `protect_by_activity` text DEFAULT NULL,
  `gradual_load_activity` text DEFAULT NULL,
  `d1_3_activity` varchar(255) DEFAULT NULL,
  `d4_7_activity` varchar(255) DEFAULT NULL,
  `week2_activity` varchar(255) DEFAULT NULL,
  `week3_activity` varchar(255) DEFAULT NULL,
  `week4_activity` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `treatmentassessment`
--

INSERT INTO `treatmentassessment` (`id`, `appointment_id`, `patient_id`, `Name`, `painanddescription`, `activity`, `severe_activity`, `remarks_activity`, `vas_activity`, `irritability_activity`, `symptoms_activity`, `acute_activity`, `indicates_activity`, `dos_donts_activity`, `duration_activity`, `aware_of_pain_body_activity`, `movement_activity`, `protect_by_activity`, `gradual_load_activity`, `d1_3_activity`, `d4_7_activity`, `week2_activity`, `week3_activity`, `week4_activity`, `created_at`) VALUES
(64, 28, 8, 'Tan Wen Hau', 'neck_pain: pain so pain', 'turn neck to left', 'medium', '', 3, 'high', 'idk man', 'acute_on_chronic', '1231231', 'aya', 'qweqwe', 'yes', '123123', '123', 'ffff', 'dfsf', 'sdfs', 'sdf', 'sdf', 'sdf', '2024-10-22 09:15:39'),
(69, 34, 9, 'Sophie Lee', 'shoulder_pain: ', '234234', 'short', '', 5, 'low', '', '2-6_wk', '', '', '', 'yes', '', '', '', '', '', '', '', '3r2w4', '2024-10-22 09:22:31'),
(70, 37, 8, 'Tan Wen Hau', 'low_back_pain: ', '', 'short', '', 5, 'low', '', '2-6_wk', '', '', '', 'yes', '', '', '', '', '', '', '', '', '2024-10-23 02:01:20'),
(72, 38, 8, 'Tan Wen Hau', 'shoulder_pain: SDFSDF', 'Walking for 30 minutes daily.', 'long', '', 5, 'low', 'SDF', '2-6_wk', 'SDFSDF', 'SDF', 'qweqwe', 'yes', 'SDFSDF', 'SDFSDF', 'SDFS', 'SDF', 'SDF', 'SDF', 'SDF', 'SDF', '2024-10-30 07:04:17'),
(73, 38, 8, 'Tan Wen Hau', 'shoulder_pain: hmhm', 'gjm', 'short', '', 8, 'low', '', '2-6_wk', 'hmj', 'h', '', 'yes', 'hjm', 'hjm', '', '', '', '', '', '', '2024-10-30 07:11:01');

-- --------------------------------------------------------

--
-- Table structure for table `treatmentassessment3`
--

CREATE TABLE `treatmentassessment3` (
  `id` int(11) NOT NULL,
  `appointment_id` int(11) NOT NULL,
  `patient_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `tissue_type` varchar(255) NOT NULL,
  `tissue_findings` enum('Superficial Fascia','Deep Fascia','Muscle Fascia','Tone','Band','Fibrosis','Trigger point','Lump','Dead block','Flattened/Inhibited','Swelling','Lymph congestion','Cellulite') NOT NULL,
  `remarks` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `treatmentassessment3`
--

INSERT INTO `treatmentassessment3` (`id`, `appointment_id`, `patient_id`, `name`, `tissue_type`, `tissue_findings`, `remarks`) VALUES
(5, 28, 8, 'Tan Wen Hau', 'Leg Tissue', 'Tone', 'bad'),
(11, 28, 8, 'Tan Wen Hau', 'Hand tissue', 'Deep Fascia', 'bad'),
(14, 38, 8, 'Tan Wen Hau', 'GHFGH', 'Lump', 'er'),
(15, 38, 8, 'Tan Wen Hau', 'cvbcv', 'Flattened/Inhibited', 'fg'),
(16, 38, 8, 'Tan Wen Hau', 'cvbc', 'Flattened/Inhibited', 'fgh');

-- --------------------------------------------------------

--
-- Table structure for table `treatment_survey`
--

CREATE TABLE `treatment_survey` (
  `id` int(11) NOT NULL,
  `patient_id` int(11) NOT NULL,
  `session_num` int(11) NOT NULL,
  `progress` text NOT NULL,
  `therapist_rating` int(11) NOT NULL,
  `therapist_feedback` text NOT NULL,
  `trainer_input` text NOT NULL,
  `goals` text NOT NULL,
  `treatment_plan` text NOT NULL,
  `overall_experience` text NOT NULL,
  `recommend` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `treatment_survey`
--

INSERT INTO `treatment_survey` (`id`, `patient_id`, `session_num`, `progress`, `therapist_rating`, `therapist_feedback`, `trainer_input`, `goals`, `treatment_plan`, `overall_experience`, `recommend`) VALUES
(3, 9, 10, 'sdfsf', 2, 'sdf', 'sdf', 'sdf', 'sdf', 'sdf', 'sdf');

-- --------------------------------------------------------

--
-- Table structure for table `videos`
--

CREATE TABLE `videos` (
  `id` int(11) NOT NULL,
  `appointment_id` int(11) NOT NULL,
  `patient_id` int(11) NOT NULL,
  `username` varchar(255) NOT NULL,
  `name` varchar(255) CHARACTER SET utf16 COLLATE utf16_general_ci NOT NULL,
  `location` varchar(255) CHARACTER SET utf16 COLLATE utf16_general_ci NOT NULL,
  `uploaded_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `doctor_id` int(11) NOT NULL,
  `approved` tinyint(1) DEFAULT 0,
  `log` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `videos`
--

INSERT INTO `videos` (`id`, `appointment_id`, `patient_id`, `username`, `name`, `location`, `uploaded_at`, `doctor_id`, `approved`, `log`) VALUES
(4, 28, 8, 'wenhau123', '2024-08-08 13-05-43.mp4', 'uploads/wenhau123/2024-08-08 13-05-43.mp4', '2024-10-22 14:07:44', 2, 0, NULL),
(6, 38, 8, 'wenhau123', '2024-08-08 13-05-43.mp4', 'uploads/wenhau123/2024-08-08 13-05-43.mp4', '2024-10-30 07:40:39', 2, 0, NULL),
(7, 38, 8, 'wenhau123', '2024-08-08 13-05-43.mp4', 'uploads/wenhau123/6721e312a3c8a_2024-08-08 13-05-43.mp4', '2024-10-30 07:41:06', 2, 0, NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin_notifications`
--
ALTER TABLE `admin_notifications`
  ADD PRIMARY KEY (`notification_id`);

--
-- Indexes for table `appointments`
--
ALTER TABLE `appointments`
  ADD PRIMARY KEY (`appointment_id`),
  ADD KEY `patient_id` (`patient_id`),
  ADD KEY `staff_id` (`staff_id`),
  ADD KEY `therapist_id` (`therapist_id`);

--
-- Indexes for table `assessment_findings`
--
ALTER TABLE `assessment_findings`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `billing`
--
ALTER TABLE `billing`
  ADD PRIMARY KEY (`billing_id`),
  ADD KEY `appointment_id` (`appointment_id`);

--
-- Indexes for table `clinical_notes`
--
ALTER TABLE `clinical_notes`
  ADD PRIMARY KEY (`note_id`),
  ADD KEY `patient_id` (`patient_id`),
  ADD KEY `staff_id` (`staff_id`);

--
-- Indexes for table `exercise_suggestions`
--
ALTER TABLE `exercise_suggestions`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `health_questionnaire`
--
ALTER TABLE `health_questionnaire`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `hr`
--
ALTER TABLE `hr`
  ADD PRIMARY KEY (`hr_id`),
  ADD KEY `staff_id` (`staff_id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `patient_id` (`patient_id`);

--
-- Indexes for table `notificationstaff`
--
ALTER TABLE `notificationstaff`
  ADD PRIMARY KEY (`id`),
  ADD KEY `patient_id` (`staff_id`);

--
-- Indexes for table `password_resets`
--
ALTER TABLE `password_resets`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `patients`
--
ALTER TABLE `patients`
  ADD PRIMARY KEY (`patient_id`);

--
-- Indexes for table `patient_changes_log`
--
ALTER TABLE `patient_changes_log`
  ADD PRIMARY KEY (`log_id`);

--
-- Indexes for table `payroll`
--
ALTER TABLE `payroll`
  ADD PRIMARY KEY (`payroll_id`),
  ADD KEY `staff_id` (`staff_id`);

--
-- Indexes for table `reviews`
--
ALTER TABLE `reviews`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `schedules`
--
ALTER TABLE `schedules`
  ADD PRIMARY KEY (`schedule_id`),
  ADD KEY `staff_id` (`staff_id`);

--
-- Indexes for table `staffs`
--
ALTER TABLE `staffs`
  ADD PRIMARY KEY (`staff_id`);

--
-- Indexes for table `staff_changes_log`
--
ALTER TABLE `staff_changes_log`
  ADD PRIMARY KEY (`log_id`);

--
-- Indexes for table `test_tracking`
--
ALTER TABLE `test_tracking`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `treatmentassessment`
--
ALTER TABLE `treatmentassessment`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `treatmentassessment3`
--
ALTER TABLE `treatmentassessment3`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `treatment_survey`
--
ALTER TABLE `treatment_survey`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `videos`
--
ALTER TABLE `videos`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin_notifications`
--
ALTER TABLE `admin_notifications`
  MODIFY `notification_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=187;

--
-- AUTO_INCREMENT for table `appointments`
--
ALTER TABLE `appointments`
  MODIFY `appointment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=39;

--
-- AUTO_INCREMENT for table `assessment_findings`
--
ALTER TABLE `assessment_findings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT for table `exercise_suggestions`
--
ALTER TABLE `exercise_suggestions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `health_questionnaire`
--
ALTER TABLE `health_questionnaire`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=39;

--
-- AUTO_INCREMENT for table `notificationstaff`
--
ALTER TABLE `notificationstaff`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=47;

--
-- AUTO_INCREMENT for table `password_resets`
--
ALTER TABLE `password_resets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `patients`
--
ALTER TABLE `patients`
  MODIFY `patient_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `patient_changes_log`
--
ALTER TABLE `patient_changes_log`
  MODIFY `log_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=108;

--
-- AUTO_INCREMENT for table `reviews`
--
ALTER TABLE `reviews`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `schedules`
--
ALTER TABLE `schedules`
  MODIFY `schedule_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `staffs`
--
ALTER TABLE `staffs`
  MODIFY `staff_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `staff_changes_log`
--
ALTER TABLE `staff_changes_log`
  MODIFY `log_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=54;

--
-- AUTO_INCREMENT for table `test_tracking`
--
ALTER TABLE `test_tracking`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `treatmentassessment`
--
ALTER TABLE `treatmentassessment`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=75;

--
-- AUTO_INCREMENT for table `treatmentassessment3`
--
ALTER TABLE `treatmentassessment3`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `treatment_survey`
--
ALTER TABLE `treatment_survey`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `videos`
--
ALTER TABLE `videos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `schedules`
--
ALTER TABLE `schedules`
  ADD CONSTRAINT `schedules_ibfk_1` FOREIGN KEY (`staff_id`) REFERENCES `staffs` (`staff_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
