-- phpMyAdmin SQL Dump
-- version 5.1.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sep 18, 2025 at 06:13 AM
-- Server version: 10.4.22-MariaDB-log
-- PHP Version: 8.4.11

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `metrotv_budgeting`
--

-- --------------------------------------------------------

--
-- Table structure for table `budgets`
--

CREATE TABLE `budgets` (
  `id_budget` int(11) NOT NULL,
  `master_name` varchar(100) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `periode_from` date DEFAULT NULL,
  `periode_to` date DEFAULT NULL,
  `pic` varchar(100) DEFAULT NULL,
  `dept` varchar(100) DEFAULT NULL,
  `template_id` int(11) DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `budgets`
--

INSERT INTO `budgets` (`id_budget`, `master_name`, `description`, `periode_from`, `periode_to`, `pic`, `dept`, `template_id`, `created_by`, `created_at`, `updated_at`) VALUES
(1, 'Budget Event Akhir Tahun 2024', 'Budget untuk event spesial akhir tahun', '2024-12-20', '2024-12-31', 'John Doe', 'Production', 1, 2, '2025-08-06 09:43:31', '2025-08-06 09:43:31'),
(2, 'Budget Merdeka Concert 2024', 'Budget untuk konser kemerdekaan', '2024-08-10', '2024-08-17', 'Jane Smith', 'Entertainment', 2, 1, '2025-08-06 09:43:31', '2025-08-06 09:43:31'),
(3, 'Budget Talkshow Ramadan 2024', 'Budget untuk program talkshow ramadan', '2024-03-10', '2024-04-10', 'Ahmad Rahman', 'News', 3, 2, '2025-08-06 09:43:31', '2025-08-06 09:43:31'),
(4, 'Budget Workshop Video Editing', 'Budget untuk workshop internal', '2024-09-01', '2024-09-03', 'Sarah Wilson', 'Creative', 1, 2, '2025-08-06 09:43:31', '2025-08-06 09:43:31'),
(5, 'Budget Peluncuran Produk Baru', 'Budget untuk event peluncuran produk', '2024-10-15', '2024-10-16', 'Michael Chen', 'Marketing', 2, 1, '2025-08-06 09:43:31', '2025-08-06 09:43:31'),
(6, 'Budget Pelatihan Jurnalis Muda', 'Budget untuk program pelatihan jurnalis', '2024-11-05', '2024-11-07', 'Lisa Rodriguez', 'Training', 3, 2, '2025-08-06 09:43:31', '2025-08-06 09:43:31');

-- --------------------------------------------------------

--
-- Table structure for table `budget_approvals`
--

CREATE TABLE `budget_approvals` (
  `id_approval` int(11) NOT NULL,
  `budget_id` int(11) NOT NULL,
  `approved_by` int(11) NOT NULL,
  `status` enum('Pending','Approved','Rejected','SendBack') DEFAULT 'Pending',
  `comment` text DEFAULT NULL,
  `approved_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `budget_approvals`
--

INSERT INTO `budget_approvals` (`id_approval`, `budget_id`, `approved_by`, `status`, `comment`, `approved_at`) VALUES
(1, 1, 1, 'Approved', 'Budget telah disetujui sesuai proposal', '2025-08-01 01:48:12'),
(2, 2, 1, 'Pending', NULL, '2025-08-04 01:48:12'),
(3, 3, 1, 'Approved', 'Disetujui dengan revisi minor', '2025-07-27 01:48:12'),
(4, 4, 1, 'Pending', NULL, '2025-08-05 01:48:12'),
(5, 5, 1, 'SendBack', 'Perlu revisi pada item catering', '2025-08-03 01:48:12'),
(6, 6, 1, 'Approved', 'Budget disetujui penuh', '2025-07-30 01:48:12');

-- --------------------------------------------------------

--
-- Table structure for table `budget_items`
--

CREATE TABLE `budget_items` (
  `id_budget_item` int(11) NOT NULL,
  `budget_id` int(11) NOT NULL,
  `item_id` int(11) NOT NULL,
  `qty` int(11) DEFAULT NULL,
  `unit` varchar(50) DEFAULT NULL,
  `top_price` decimal(15,2) DEFAULT NULL,
  `bottom_price` decimal(15,2) DEFAULT NULL,
  `amount` decimal(15,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `event_programs`
--

CREATE TABLE `event_programs` (
  `id_event_program` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `category` varchar(20) NOT NULL,
  `pic_user_id` int(11) DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `event_programs`
--

INSERT INTO `event_programs` (`id_event_program`, `name`, `category`, `pic_user_id`, `description`, `created_at`, `updated_at`) VALUES
(1, 'Bansos', 'On Air', 3, 'duit', '2025-08-26 23:48:15', '2025-09-08 00:58:00'),
(2, 'tes', 'Off Air', 1, 'p', '2025-09-07 23:38:20', '2025-09-08 02:38:20'),
(3, 'mbg', 'On Air', 2, 'mangan', '2025-09-08 00:27:10', '2025-09-08 00:41:42');

-- --------------------------------------------------------

--
-- Table structure for table `master_items`
--

CREATE TABLE `master_items` (
  `id_item` int(11) NOT NULL,
  `item_name` varchar(100) NOT NULL,
  `unit_id` bigint(20) UNSIGNED DEFAULT NULL,
  `unit` varchar(50) DEFAULT NULL,
  `bottom_price` decimal(15,2) DEFAULT NULL,
  `top_price` decimal(15,2) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `category` varchar(50) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `master_items`
--

INSERT INTO `master_items` (`id_item`, `item_name`, `unit_id`, `unit`, `bottom_price`, `top_price`, `description`, `category`, `created_at`, `updated_at`) VALUES
(1, 'Sound System', 2, 'Set', '500000000.00', '800000000.00', 'Audio equipment untuk acara', NULL, '2025-08-19 07:08:04', '2025-09-16 16:38:06'),
(2, 'Lighting Equipment', 2, 'Set', '300000000.00', '600000000.00', 'Peralatan pencahayaan panggung', NULL, '2025-08-19 07:08:04', '2025-09-16 16:38:00'),
(3, 'Catering', 3, 'Pax', '5000000.00', '10000000.00', 'Konsumsi untuk crew dan talent', NULL, '2025-08-19 07:08:04', '2025-09-16 16:37:27'),
(4, 'Talent Fee', 2, 'Orang', '1000000000.00', '5000000000.00', 'Honor untuk talent atau pembicara', NULL, '2025-08-19 07:08:04', '2025-09-16 16:38:32'),
(5, 'Venue Rental', 1, 'Hari', '1500000000.00', '3000000000.00', 'Sewa venue untuk acara', NULL, '2025-08-19 07:08:04', '2025-09-16 16:38:46'),
(6, 'transportasi', 1, 'Hari', '30000000.00', '60000000.00', 'ongkos kru harian', NULL, '2025-08-19 00:24:35', '2025-09-16 16:38:41');

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

CREATE TABLE `migrations` (
  `id` int(10) UNSIGNED NOT NULL,
  `migration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '2025_08_06_093543_create_budgets_table', 1),
(2, '2025_01_01_000001_create_units_table', 2),
(3, '2025_08_21_000000_create_or_align_event_programs_table', 3),
(4, '2025_08_21_000100_add_pic_user_to_event_programs_table', 4),
(5, '2025_09_03_000000_create_templates_table', 5),
(6, '2025_09_03_000001_create_template_items_table', 6);

-- --------------------------------------------------------

--
-- Table structure for table `templates`
--

CREATE TABLE `templates` (
  `id_template` int(11) NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `event_program_id` bigint(20) UNSIGNED NOT NULL,
  `pic_user_id` int(11) NOT NULL,
  `category` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `templates`
--

INSERT INTO `templates` (`id_template`, `name`, `event_program_id`, `pic_user_id`, `category`, `description`, `created_at`, `updated_at`) VALUES
(1, 'pkh', 1, 3, 'off_air', 'duit', '2025-09-09 00:59:09', '2025-09-09 00:59:09'),
(2, 'gb', 3, 2, 'On Air', 'mabok', '2025-09-09 02:07:26', '2025-09-09 02:07:26'),
(3, 'aa', 1, 3, NULL, 'aa', '2025-09-09 02:11:58', '2025-09-09 02:11:58'),
(4, 'aa', 1, 3, 'Off Air', 'aa', '2025-09-09 02:12:06', '2025-09-09 02:12:06'),
(5, 'a', 1, 1, 'On Air', 'a', '2025-09-09 02:20:14', '2025-09-09 02:20:14'),
(6, 'a', 1, 1, 'Off Air', 'a', '2025-09-09 02:29:55', '2025-09-09 02:29:55'),
(7, 'KK', 1, 1, 'Off Air', 'KK', '2025-09-10 22:01:18', '2025-09-10 22:01:37'),
(8, 'op', 1, 2, 'On Air', 'op', '2025-09-11 01:50:19', '2025-09-11 01:50:19'),
(9, 'n', 3, 1, 'Off Air', 'n', '2025-09-11 01:57:26', '2025-09-11 01:57:26'),
(10, 'aa', 1, 1, 'On Air', 'aa', '2025-09-14 05:24:44', '2025-09-14 05:24:44'),
(11, 'aab', 3, 2, 'On Air', 'mangan', '2025-09-14 05:59:39', '2025-09-14 05:59:39'),
(12, 'testing', 2, 1, 'On Air', 'p', '2025-09-14 06:10:30', '2025-09-14 06:10:30'),
(13, 'mangan', 3, 2, 'On Air', 'duit', '2025-09-14 06:49:48', '2025-09-14 06:49:48'),
(14, 'pp', 1, 3, 'On Air', 'duit', '2025-09-14 07:06:06', '2025-09-14 07:06:06'),
(15, 'aa', 1, 3, 'On Air', 'duit', '2025-09-15 00:03:25', '2025-09-15 00:04:19'),
(16, 'duitduitduit', 1, 3, 'On Air', 'duit', '2025-09-15 00:08:18', '2025-09-15 00:12:34'),
(17, 'ass', 2, 1, 'Off Air', 'p', '2025-09-16 01:22:06', '2025-09-16 01:22:06'),
(18, 'aaa', 1, 3, 'On Air', 'duit', '2025-09-16 08:00:54', '2025-09-16 08:00:54'),
(19, 'aa', 2, 1, 'Off Air', 'p', '2025-09-16 08:31:17', '2025-09-16 08:31:17'),
(20, 'kk', 1, 3, 'On Air', 'duit', '2025-09-16 09:40:00', '2025-09-16 09:40:00'),
(21, 'popo', 2, 1, 'Off Air', 'p', '2025-09-16 10:14:27', '2025-09-16 10:14:27');

-- --------------------------------------------------------

--
-- Table structure for table `template_items`
--

CREATE TABLE `template_items` (
  `id_template_item` int(11) NOT NULL,
  `template_id` int(11) NOT NULL,
  `item_id` int(11) NOT NULL,
  `qty` int(11) NOT NULL DEFAULT 0,
  `item_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `unit` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `unit_price` decimal(15,2) NOT NULL DEFAULT 0.00,
  `short_desc` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `template_items`
--

INSERT INTO `template_items` (`id_template_item`, `template_id`, `item_id`, `qty`, `item_name`, `unit`, `unit_price`, `short_desc`, `created_at`, `updated_at`) VALUES
(1, 14, 2, 1, '', NULL, '6000000.00', NULL, '2025-09-14 07:15:23', '2025-09-14 07:15:23'),
(3, 16, 6, 12, 'transportasi', '', '600000.00', 'ongkos kru harian', '2025-09-15 00:08:27', '2025-09-15 00:08:27'),
(4, 16, 4, 1, 'Talent Fee', '', '50000000.00', 'Honor untuk talent atau pembicara', '2025-09-15 00:11:44', '2025-09-15 00:11:44'),
(5, 19, 4, 1, 'Talent Fee', '', '50000000.00', 'Honor untuk talent atau pembicara', '2025-09-16 08:36:36', '2025-09-16 08:36:36'),
(6, 20, 2, 1, 'Lighting Equipment', 'Paket', '600000000.00', 'Peralatan pencahayaan panggung', '2025-09-16 09:40:18', '2025-09-16 09:40:18'),
(7, 20, 3, 1, 'Catering', 'Box', '10000000.00', 'Konsumsi untuk crew dan talent', '2025-09-16 09:42:29', '2025-09-16 09:42:29'),
(8, 20, 1, 3, 'Sound System', 'Paket', '800000000.00', 'Audio equipment untuk acara', '2025-09-16 09:42:39', '2025-09-16 09:42:39');

-- --------------------------------------------------------

--
-- Table structure for table `units`
--

CREATE TABLE `units` (
  `id_unit` bigint(20) UNSIGNED NOT NULL,
  `unit_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `units`
--

INSERT INTO `units` (`id_unit`, `unit_name`, `created_at`, `updated_at`) VALUES
(1, 'Hari', NULL, NULL),
(2, 'Paket', NULL, NULL),
(3, 'Box', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id_user` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `user_level_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id_user`, `name`, `email`, `username`, `password`, `user_level_id`) VALUES
(1, 'Administrator', 'admin@metrotv.com', 'admin123', '$2y$12$HWO.sphxUibgl/My6C1QguJpaHoiyGT6oWq5OHbgdTAd3e3Y/zb.a', 1),
(2, 'John Doe', 'john@metrotv.com', 'john123', '$2y$12$KjzsZ/7xmHI5ATuExwZ1w.41pjDMZu/bAXDBi7ZHbM0qEJIBInjfS', 2),
(3, 'Hacker', 'anonim@metrotv.com', 'anonimous', '$2y$12$NhZ1LE/6sz2qOAlbUKI5deHBBNwXWoauvYNZyHFead9G0IAkWMbmO', 4);

-- --------------------------------------------------------

--
-- Table structure for table `user_levels`
--

CREATE TABLE `user_levels` (
  `id_level` int(11) NOT NULL,
  `level_name` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `user_levels`
--

INSERT INTO `user_levels` (`id_level`, `level_name`) VALUES
(1, 'Admin'),
(2, 'Staff'),
(3, 'Manager'),
(4, 'Director');

-- --------------------------------------------------------

--
-- Table structure for table `user_level_access`
--

CREATE TABLE `user_level_access` (
  `id_access` int(11) NOT NULL,
  `user_level_id` int(11) NOT NULL,
  `feature_name` varchar(100) DEFAULT NULL,
  `access` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `budgets`
--
ALTER TABLE `budgets`
  ADD PRIMARY KEY (`id_budget`),
  ADD KEY `template_id` (`template_id`),
  ADD KEY `created_by` (`created_by`);

--
-- Indexes for table `budget_approvals`
--
ALTER TABLE `budget_approvals`
  ADD PRIMARY KEY (`id_approval`),
  ADD KEY `budget_id` (`budget_id`),
  ADD KEY `approved_by` (`approved_by`);

--
-- Indexes for table `budget_items`
--
ALTER TABLE `budget_items`
  ADD PRIMARY KEY (`id_budget_item`),
  ADD KEY `budget_id` (`budget_id`),
  ADD KEY `item_id` (`item_id`);

--
-- Indexes for table `event_programs`
--
ALTER TABLE `event_programs`
  ADD PRIMARY KEY (`id_event_program`),
  ADD KEY `pic_user_id` (`pic_user_id`);

--
-- Indexes for table `master_items`
--
ALTER TABLE `master_items`
  ADD PRIMARY KEY (`id_item`),
  ADD KEY `unit_id` (`unit_id`);

--
-- Indexes for table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `templates`
--
ALTER TABLE `templates`
  ADD PRIMARY KEY (`id_template`),
  ADD KEY `templates_event_program_id_foreign` (`event_program_id`),
  ADD KEY `templates_pic_user_id_foreign` (`pic_user_id`);

--
-- Indexes for table `template_items`
--
ALTER TABLE `template_items`
  ADD PRIMARY KEY (`id_template_item`),
  ADD KEY `template_items_template_id_foreign` (`template_id`);

--
-- Indexes for table `units`
--
ALTER TABLE `units`
  ADD PRIMARY KEY (`id_unit`),
  ADD UNIQUE KEY `units_unit_name_unique` (`unit_name`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id_user`),
  ADD KEY `user_level_id` (`user_level_id`);

--
-- Indexes for table `user_levels`
--
ALTER TABLE `user_levels`
  ADD PRIMARY KEY (`id_level`);

--
-- Indexes for table `user_level_access`
--
ALTER TABLE `user_level_access`
  ADD PRIMARY KEY (`id_access`),
  ADD KEY `user_level_id` (`user_level_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `budgets`
--
ALTER TABLE `budgets`
  MODIFY `id_budget` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `budget_approvals`
--
ALTER TABLE `budget_approvals`
  MODIFY `id_approval` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `budget_items`
--
ALTER TABLE `budget_items`
  MODIFY `id_budget_item` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `event_programs`
--
ALTER TABLE `event_programs`
  MODIFY `id_event_program` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `master_items`
--
ALTER TABLE `master_items`
  MODIFY `id_item` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `templates`
--
ALTER TABLE `templates`
  MODIFY `id_template` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `template_items`
--
ALTER TABLE `template_items`
  MODIFY `id_template_item` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `units`
--
ALTER TABLE `units`
  MODIFY `id_unit` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id_user` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `user_levels`
--
ALTER TABLE `user_levels`
  MODIFY `id_level` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `user_level_access`
--
ALTER TABLE `user_level_access`
  MODIFY `id_access` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `budgets`
--
ALTER TABLE `budgets`
  ADD CONSTRAINT `budgets_ibfk_1` FOREIGN KEY (`template_id`) REFERENCES `templates` (`id_template`),
  ADD CONSTRAINT `budgets_ibfk_2` FOREIGN KEY (`created_by`) REFERENCES `users` (`id_user`);

--
-- Constraints for table `budget_approvals`
--
ALTER TABLE `budget_approvals`
  ADD CONSTRAINT `budget_approvals_ibfk_1` FOREIGN KEY (`budget_id`) REFERENCES `budgets` (`id_budget`),
  ADD CONSTRAINT `budget_approvals_ibfk_2` FOREIGN KEY (`approved_by`) REFERENCES `users` (`id_user`);

--
-- Constraints for table `budget_items`
--
ALTER TABLE `budget_items`
  ADD CONSTRAINT `budget_items_ibfk_1` FOREIGN KEY (`budget_id`) REFERENCES `budgets` (`id_budget`),
  ADD CONSTRAINT `budget_items_ibfk_2` FOREIGN KEY (`item_id`) REFERENCES `master_items` (`id_item`);

--
-- Constraints for table `event_programs`
--
ALTER TABLE `event_programs`
  ADD CONSTRAINT `event_programs_ibfk_1` FOREIGN KEY (`pic_user_id`) REFERENCES `users` (`id_user`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `master_items`
--
ALTER TABLE `master_items`
  ADD CONSTRAINT `master_items_ibfk_1` FOREIGN KEY (`unit_id`) REFERENCES `units` (`id_unit`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `templates`
--
ALTER TABLE `templates`
  ADD CONSTRAINT `templates_event_program_id_foreign` FOREIGN KEY (`event_program_id`) REFERENCES `event_programs` (`id_event_program`) ON UPDATE CASCADE,
  ADD CONSTRAINT `templates_pic_user_id_foreign` FOREIGN KEY (`pic_user_id`) REFERENCES `users` (`id_user`) ON UPDATE CASCADE;

--
-- Constraints for table `template_items`
--
ALTER TABLE `template_items`
  ADD CONSTRAINT `template_items_template_id_foreign` FOREIGN KEY (`template_id`) REFERENCES `templates` (`id_template`) ON DELETE CASCADE;

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_ibfk_1` FOREIGN KEY (`user_level_id`) REFERENCES `user_levels` (`id_level`);

--
-- Constraints for table `user_level_access`
--
ALTER TABLE `user_level_access`
  ADD CONSTRAINT `user_level_access_ibfk_1` FOREIGN KEY (`user_level_id`) REFERENCES `user_levels` (`id_level`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
