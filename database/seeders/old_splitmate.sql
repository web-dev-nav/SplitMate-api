-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Apr 16, 2026 at 03:46 AM
-- Server version: 11.8.6-MariaDB-log
-- PHP Version: 7.2.34

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `u849062718_splitmate`
--

-- --------------------------------------------------------

--
-- Table structure for table `balance_states`
--

CREATE TABLE `balance_states` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `expense_id` bigint(20) UNSIGNED DEFAULT NULL,
  `settlement_id` bigint(20) UNSIGNED DEFAULT NULL,
  `user_balances` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`user_balances`)),
  `transaction_date` timestamp NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `expenses`
--

CREATE TABLE `expenses` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `description` varchar(191) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `paid_by_user_id` bigint(20) UNSIGNED NOT NULL,
  `receipt_photo` varchar(191) DEFAULT NULL,
  `expense_date` date NOT NULL,
  `is_payback` tinyint(1) NOT NULL DEFAULT 0,
  `payback_to_user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `payback_amount` decimal(10,2) DEFAULT NULL,
  `user_count_at_time` int(11) DEFAULT NULL,
  `participant_ids` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`participant_ids`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `expenses`
--

INSERT INTO `expenses` (`id`, `description`, `amount`, `paid_by_user_id`, `receipt_photo`, `expense_date`, `is_payback`, `payback_to_user_id`, `payback_amount`, `user_count_at_time`, `participant_ids`, `created_at`, `updated_at`) VALUES
(1, 'Fresco Groceries', 208.11, 2, 'receipts/3PXDs96h3GHQp5ulnAIkQXdQtKMDf5J03lhgyNEK.jpg', '2025-09-07', 0, NULL, NULL, 3, '[1,2,3]', '2025-09-16 04:36:05', '2025-09-16 04:36:05'),
(2, 'Freshco Grocery', 97.25, 1, 'receipts/DbKhnzCTevaCCcHdT6s1Y4thuY7YKvVuCdTqFPAj.jpg', '2025-09-17', 0, NULL, NULL, 3, '[1,2,3]', '2025-09-24 03:09:00', '2025-09-24 03:09:00'),
(3, 'Freshco', 44.70, 2, 'receipts/sIKo4bU0HDFS9UAEAzdq4WBYSCCSgMSuS1qkHJC9.jpg', '2025-09-24', 0, NULL, NULL, 3, '[1,2,3]', '2025-09-24 03:11:17', '2025-09-24 03:11:17'),
(4, 'Freshco Grocery', 94.44, 2, 'receipts/UcpsN0821srLNDZQsv9pe4KYnV1OuATLcq5jHYyy.jpg', '2025-10-02', 0, NULL, NULL, 3, '[1,2,3]', '2025-10-02 02:18:13', '2025-10-02 02:18:13'),
(5, 'Freshco Grocery', 140.71, 1, 'receipts/OCFU1wFVLLHTLrJCEMXS3CPId1ZnjukRSEomMYvF.jpg', '2025-10-10', 0, NULL, NULL, 3, '[1,2,3]', '2025-10-10 02:01:00', '2025-10-10 02:01:00'),
(6, 'Wallmart Groceries', 70.44, 1, 'receipts/9YcJDVqOHYNCGHnkI0nQkcbpA8jDvBo6NjhQrlnR.jpg', '2025-10-28', 0, NULL, NULL, 3, '[1,2,3]', '2025-10-28 13:21:48', '2025-10-28 13:21:48'),
(7, 'Grocery', 58.46, 3, 'receipts/bLA6qLXzn5LWmzD3shgrmzU9KohiPK3k8XJMw4QA.jpg', '2025-11-01', 0, NULL, NULL, 3, '[1,2,3]', '2025-11-02 02:06:04', '2025-11-02 02:06:04'),
(8, 'Freshco Grocery', 64.46, 3, 'receipts/A3MBjOTZMtZuMTuJ9NghTwmjuj0ggfS04o0x7Xkt.jpg', '2025-11-03', 0, NULL, NULL, 3, '[1,2,3]', '2025-11-05 10:35:21', '2025-11-05 10:35:21'),
(9, 'Utility', 11.87, 1, 'receipts/YKlqZA0juQYeBwsifQmAMX3u4vGy7QgAhFkuUAQI.jpg', '2025-11-06', 0, NULL, NULL, 3, '[1,2,3]', '2025-11-06 21:54:21', '2025-11-06 21:54:21'),
(10, 'Freshco', 105.49, 2, 'receipts/DDiSf7DMMvZpDRX2HK7JXimKNp2zTIs6A5F4xrTC.jpg', '2025-11-09', 0, NULL, NULL, 3, '[1,2,3]', '2025-11-12 05:22:54', '2025-11-12 05:22:54'),
(11, 'Wallmart groceries', 21.93, 1, 'receipts/9B8KG3ugJJaf0JpqQdUM5rD1Ebfe31BDXNlTFHu7.jpg', '2025-11-19', 0, NULL, NULL, 3, '[1,2,3]', '2025-11-19 05:12:08', '2025-11-19 05:12:08'),
(12, 'Grocery', 183.00, 3, 'receipts/N0VJwgnPOGUgaLenP2GYs636YlhBaIoFk3dvpPaJ.jpg', '2025-11-27', 0, NULL, NULL, 3, '[1,2,3]', '2025-11-27 18:57:23', '2025-11-27 18:57:23'),
(13, 'Freshco grocery paid by sapna card', 109.00, 2, 'receipts/W6TH68uS6BQ2JQt2w5YA17kpFTLknOjWGfBsrbuk.jpg', '2025-12-21', 0, NULL, NULL, 3, '[1,2,3]', '2025-12-21 22:06:25', '2025-12-21 22:06:25'),
(14, 'India parcel - DHL CUSTOM CLEAR', 20.00, 1, 'receipts/rtKcxQ8tXLa8cbv87jOmPCeqQLQanNOFjkGBeena.png', '2025-12-21', 0, NULL, NULL, 3, '[1,2,3]', '2025-12-21 22:08:15', '2025-12-21 22:08:15'),
(15, 'Household utilities', 17.00, 1, 'receipts/jJbNHIOD2d5rSQlDi6vhoeggqQrDxGI7TwuVjaEe.jpg', '2025-12-30', 0, NULL, NULL, 3, '[1,2,3]', '2025-12-30 05:48:42', '2025-12-30 05:48:42'),
(16, 'Foodbasic', 101.00, 1, 'receipts/HxoLGs9o6NadPGt2Rqsogo3A2opBHdpvcY3zXtRA.jpg', '2026-01-03', 0, NULL, NULL, 3, '[1,2,3]', '2026-01-03 00:42:03', '2026-01-03 00:42:03'),
(17, 'Freshco', 65.00, 1, 'receipts/abcdxm8mxo0ecNPV9Pb91DOImMiCV17vBIN7UKx5.jpg', '2026-01-03', 0, NULL, NULL, 3, '[1,2,3]', '2026-01-03 00:42:34', '2026-01-03 00:42:34'),
(18, 'Utility', 17.00, 1, 'receipts/10a0Ep1XPWim46BG7lYdDYArOg1rqmWB492VSGlj.jpg', '2026-01-07', 0, NULL, NULL, 3, '[1,2,3]', '2026-01-07 13:32:20', '2026-01-07 13:32:20'),
(19, 'Freshco grocery', 163.79, 3, 'receipts/43aUEk4G7PU6rQaniyPlF6jvwlVjHUbFIorotXHx.jpg', '2026-01-15', 0, NULL, NULL, 3, '[1,2,3]', '2026-01-15 23:33:54', '2026-01-15 23:33:54'),
(20, 'Freshco Grocery small', 30.00, 1, 'receipts/HAGNvMMvCKmxkAHgvFlFtyVbKDIqTrK7e9hZmFFa.jpg', '2026-01-23', 0, NULL, NULL, 3, '[1,2,3]', '2026-01-23 13:18:47', '2026-01-23 13:18:47'),
(21, 'Freshco', 177.00, 2, 'receipts/QAbOj8GPjL6X6bXsspNWm5lVS3UhiskoqZrjTpPo.jpg', '2026-01-28', 0, NULL, NULL, 3, '[1,2,3]', '2026-01-28 02:20:19', '2026-01-28 02:20:19'),
(22, 'freshco', 128.64, 3, 'receipts/8JaYn0slPjwXbpaUBhTiZeTbvBVtpR2CMWpWQC9X.jpg', '2026-02-08', 0, NULL, NULL, 3, '[1,2,3]', '2026-02-08 02:05:30', '2026-02-08 02:05:30'),
(23, 'Grocery', 86.00, 1, 'receipts/DH1T0PkpraxQBMPB9sna6WnuFTV9Tw8pzmKSjamR.png', '2026-02-17', 0, NULL, NULL, 3, '[1,2,3]', '2026-02-17 08:15:20', '2026-02-17 08:15:20'),
(24, 'Pressure cooker', 72.00, 1, 'receipts/x3EHZn2xdTpJ25DVlR6YDbzCDPxEUJhkPxc5FApI.png', '2026-02-24', 0, NULL, NULL, 3, '[1,2,3]', '2026-02-24 17:51:16', '2026-02-24 17:51:16'),
(25, 'freshco', 38.00, 1, 'receipts/Mj6haaFjXYBFENjLkh7VwxvcIMBGjXGoBAU94r10.jpg', '2026-02-26', 0, NULL, NULL, 3, '[1,2,3]', '2026-02-26 03:25:50', '2026-02-26 03:25:50'),
(26, 'Freshco Grocery', 219.00, 2, 'receipts/Ntr2chnST7aRfq1gMw7mFhhv5ZvNfqyQt7FDsQYG.jpg', '2026-02-26', 0, NULL, NULL, 3, '[1,2,3]', '2026-02-26 03:26:27', '2026-02-26 03:26:27'),
(27, 'Grocery', 16.62, 1, 'receipts/71ClF52nB7rRjAZZEOPIJZCglVrxfvByZFjeAkFV.jpg', '2026-02-28', 0, NULL, NULL, 3, '[1,2,3]', '2026-02-28 02:46:46', '2026-02-28 02:46:46'),
(28, 'Freshco', 28.00, 2, 'receipts/YJI8hDYFY3nj0cOQElkjNyetlK7Ov2GN2NMSboiR.jpg', '2026-03-01', 0, NULL, NULL, 3, '[1,2,3]', '2026-03-02 20:08:33', '2026-03-02 20:08:33'),
(29, 'coffe', 8.00, 1, 'receipts/MlRNdnzmP0jEu8ogxBTFLYakEkMgsLhQHJZN7yaw.jpg', '2026-03-05', 0, NULL, NULL, 3, '[1,2,3]', '2026-03-05 01:37:47', '2026-03-05 01:37:47'),
(30, 'Dollerrama', 63.00, 2, 'receipts/nGo47aRmKkliKorhvjocJqU89grKUWiNamdGYzkl.jpg', '2026-03-07', 0, NULL, NULL, 3, '[1,2,3]', '2026-03-07 22:47:49', '2026-03-07 22:47:49'),
(31, 'Groceries', 171.00, 3, 'receipts/i4UI8CHOFsmkXCK4PcK4Uo2CpTqwY35gKQXc6ceN.jpg', '2026-03-10', 0, NULL, NULL, 3, '[1,2,3]', '2026-03-10 05:15:44', '2026-03-10 05:15:44'),
(32, 'Utility', 72.00, 2, 'receipts/XAw45h6CMI8cvx7Ns7G6G4oEHHDIcYoVQhAOxJCb.jpg', '2026-03-16', 0, NULL, NULL, 3, '[1,2,3]', '2026-03-16 15:39:15', '2026-03-16 15:39:15'),
(33, 'Freshco Grocery', 190.00, 1, 'receipts/PRYVgNPB2Xl6bOND0DFUnPVAAafWkWGQZn4UKjJO.jpg', '2026-03-23', 0, NULL, NULL, 3, '[1,2,3]', '2026-03-23 07:12:08', '2026-03-23 07:12:08'),
(34, 'Freshco Grocery', 100.00, 3, 'receipts/FGHkdh4jsRSg6cMPXetaNPjRJdLIg7YAdCzEwALj.jpg', '2026-03-23', 0, NULL, NULL, 3, '[1,2,3]', '2026-03-23 07:12:29', '2026-03-23 07:12:29'),
(35, 'Freshco Grocery', 217.67, 2, 'receipts/eOk05yDsGCH8tduY1H4jeSHNzGXWoTQvpouj0A1X.jpg', '2026-04-05', 0, NULL, NULL, 3, '[1,2,3]', '2026-04-05 01:17:48', '2026-04-05 01:17:48'),
(36, 'Freshco Grocery', 198.00, 1, 'receipts/ZlcfUro0Amt0fAdBYjfgHgWvx5XP5e8yqjP6cB1D.jpg', '2026-04-14', 0, NULL, NULL, 3, '[1,2,3]', '2026-04-14 12:46:44', '2026-04-14 12:46:44');

-- --------------------------------------------------------

--
-- Table structure for table `expense_paybacks`
--

CREATE TABLE `expense_paybacks` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `expense_id` bigint(20) UNSIGNED NOT NULL,
  `payback_to_user_id` bigint(20) UNSIGNED NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

CREATE TABLE `migrations` (
  `id` int(10) UNSIGNED NOT NULL,
  `migration` varchar(191) NOT NULL,
  `batch` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '0001_01_01_000000_create_users_table', 1),
(2, '2025_09_04_085459_create_expenses_table', 1),
(3, '2025_09_04_085505_create_settlements_table', 1),
(4, '2025_09_13_053431_create_balance_states_table', 1),
(5, '2025_09_14_195007_create_statement_records_table', 1);

-- --------------------------------------------------------

--
-- Table structure for table `password_reset_tokens`
--

CREATE TABLE `password_reset_tokens` (
  `email` varchar(191) NOT NULL,
  `token` varchar(191) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sessions`
--

CREATE TABLE `sessions` (
  `id` varchar(191) NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `payload` longtext NOT NULL,
  `last_activity` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `sessions`
--

INSERT INTO `sessions` (`id`, `user_id`, `ip_address`, `user_agent`, `payload`, `last_activity`) VALUES
('7OBU4THKbiojI7i4Qd0LG4bClvQ2sstN8Jj6Ximh', NULL, '192.36.109.128', 'Mozilla/5.0 (iPhone; CPU iPhone OS 17_1_2 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.1.2 Mobile/15E148 Safari/604', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiS2U5dzR1bDhXeWlhMnIyOTNaOG9KU1J0cWJxNktwS21XVFcwMFo3OSI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6NTI6Imh0dHBzOi8vc3BsaXRtYXRlLmJyYWluYW5kYm9sdC5jb20vc3RhdGVtZW50cy91c2VyLzEiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19', 1776183678),
('B2VXe9nMAejfxgRlv2sGmYgudfoH2HzV6CpNpOAv', NULL, '2605:8d80:6be0:b877:6480:c44e:ee0b:e2fa', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiMDR0VlBsTGZQSTZlNWI1Q0dHVDdMT2hYU2xkQXkyZEtNMEJMN0xKeSI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MzQ6Imh0dHBzOi8vc3BsaXRtYXRlLmJyYWluYW5kYm9sdC5jb20iO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19', 1776310710),
('C3Vaq79Nku9SL16C5ASk1MOXkBBkYp3qptsYxE3J', NULL, '2607:f2c0:e759:ff20::ccb0', 'Mozilla/5.0 (iPhone; CPU iPhone OS 26_4_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/147.0.7727.47 Mobile/15E148 Safari/604.1', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiVjJnQURFcWpRVXJlcjVLcmJ5TlpLVFdTRVBPMFJXeUVCZ2FwaHVSTyI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MzQ6Imh0dHBzOi8vc3BsaXRtYXRlLmJyYWluYW5kYm9sdC5jb20iO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19', 1776170805),
('hFrMXaF5gNqrVQhlqMHbYW3cCIpVWNctzurWq3Gz', NULL, '2607:f2c0:e759:ff20::b653', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiZ01SZkVGakQ5cjhLSlZualJUSWtHMFlBTnBRRW1kQThHOTI3M25KUCI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MzQ6Imh0dHBzOi8vc3BsaXRtYXRlLmJyYWluYW5kYm9sdC5jb20iO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19', 1776203529),
('hKDeM2FUyWjpjAbqtkarO5g6NZ2Jbxv7QY0ApyAe', NULL, '192.36.109.89', 'Mozilla/5.0 (iPhone; CPU iPhone OS 17_1_2 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.1.2 Mobile/15E148 Safari/604', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiTXVOUVhFSmVBVUwwM1VWZWpyU1VmajZ5WG1hNk5iS0hKeFJTOGpCWCI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MzQ6Imh0dHBzOi8vc3BsaXRtYXRlLmJyYWluYW5kYm9sdC5jb20iO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19', 1776183677),
('SUI8G6PnvuvhquuO9MTKxVkWFVnTffDCoJnDGCy3', NULL, '2a02:4780:2c:3::2', 'Go-http-client/2.0', 'YToyOntzOjY6Il90b2tlbiI7czo0MDoiR3VrSm1KOVFQbnU2WlBLZkRnUTZldGpmaTVyUDRzVmhrUG9OY3dXUiI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1776240416),
('tayGi5oO3l9dSgjiRS4SsNShyvHGqSwEtpuoTiZj', NULL, '192.36.109.86', 'Mozilla/5.0 (iPhone; CPU iPhone OS 17_1_2 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.1.2 Mobile/15E148 Safari/604', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoib2pTbkpaYnJQZEU5Z2VSd2pzMjY1enI3c0tRUzRkbFJiNnoyUm9YbCI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6NTk6Imh0dHBzOi8vc3BsaXRtYXRlLmJyYWluYW5kYm9sdC5jb20vc3RhdGVtZW50cy91c2VyLzE/cGFnZT0yIjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1776183678),
('xzQGXY1cm2wWKhkYDCqs54CyjlHE09ZbeIdfhsHq', NULL, '192.36.109.102', 'Mozilla/5.0 (iPhone; CPU iPhone OS 17_1_2 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.1.2 Mobile/15E148 Safari/604', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiaVdtM3ZhNVBjNnBDZVlsREpJSXkxcjVjQmlYbVRTUFNCN0ZqY3VpWCI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6NTk6Imh0dHBzOi8vc3BsaXRtYXRlLmJyYWluYW5kYm9sdC5jb20vc3RhdGVtZW50cy91c2VyLzE/cGFnZT0xIjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1776183679);

-- --------------------------------------------------------

--
-- Table structure for table `settlements`
--

CREATE TABLE `settlements` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `from_user_id` bigint(20) UNSIGNED NOT NULL,
  `to_user_id` bigint(20) UNSIGNED NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `settlement_date` date NOT NULL,
  `payment_screenshot` varchar(191) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `statement_records`
--

CREATE TABLE `statement_records` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `expense_id` bigint(20) UNSIGNED DEFAULT NULL,
  `settlement_id` bigint(20) UNSIGNED DEFAULT NULL,
  `transaction_type` varchar(191) NOT NULL,
  `description` varchar(191) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `reference_number` varchar(191) NOT NULL,
  `balance_before` decimal(10,2) NOT NULL,
  `balance_after` decimal(10,2) NOT NULL,
  `balance_change` decimal(10,2) NOT NULL,
  `transaction_details` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`transaction_details`)),
  `transaction_date` datetime NOT NULL,
  `status` varchar(191) NOT NULL DEFAULT 'completed',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `statement_records`
--

INSERT INTO `statement_records` (`id`, `user_id`, `expense_id`, `settlement_id`, `transaction_type`, `description`, `amount`, `reference_number`, `balance_before`, `balance_after`, `balance_change`, `transaction_details`, `transaction_date`, `status`, `created_at`, `updated_at`) VALUES
(1, 1, 1, NULL, 'expense', '🛒 Expense: Fresco Groceries', -69.37, 'EXP20250916001', 0.00, -69.37, -69.37, '{\"note\":\"Your share: $69.37 (paid by Sapna)\",\"expense_total\":\"208.11\",\"your_share\":69.37,\"participants\":3,\"debt_details\":[\"\\ud83d\\udcb3 You now owe $69.37\"],\"is_payer\":false}', '2025-09-16 04:36:05', 'completed', '2025-09-16 04:36:05', '2025-09-16 04:36:05'),
(2, 2, 1, NULL, 'expense', '🛒 Expense: Fresco Groceries', 138.74, 'EXP20250916002', 0.00, 138.74, 138.74, '{\"note\":\"Your share: $69.37 (paid by Sapna)\",\"expense_total\":\"208.11\",\"your_share\":69.37,\"participants\":3,\"debt_details\":[],\"is_payer\":false}', '2025-09-16 04:36:05', 'completed', '2025-09-16 04:36:05', '2025-09-16 04:36:05'),
(3, 3, 1, NULL, 'expense', '🛒 Expense: Fresco Groceries', -69.37, 'EXP20250916003', 0.00, -69.37, -69.37, '{\"note\":\"Your share: $69.37 (paid by Sapna)\",\"expense_total\":\"208.11\",\"your_share\":69.37,\"participants\":3,\"debt_details\":[\"\\ud83d\\udcb3 You now owe $69.37\"],\"is_payer\":false}', '2025-09-16 04:36:05', 'completed', '2025-09-16 04:36:05', '2025-09-16 04:36:05'),
(4, 1, 2, NULL, 'expense', '🛒 Expense: Freshco Grocery', 64.83, 'EXP20250924001', -69.37, -4.54, 64.83, '{\"note\":\"Your share: $32.42 (paid by Navjot)\",\"expense_total\":\"97.25\",\"your_share\":32.42,\"participants\":3,\"debt_details\":[],\"is_payer\":false}', '2025-09-24 03:09:00', 'completed', '2025-09-24 03:09:00', '2025-09-24 03:09:00'),
(5, 2, 2, NULL, 'expense', '🛒 Expense: Freshco Grocery', -32.42, 'EXP20250924002', 138.74, 106.32, -32.42, '{\"note\":\"Your share: $32.42 (paid by Navjot)\",\"expense_total\":\"97.25\",\"your_share\":32.42,\"participants\":3,\"debt_details\":[\"\\ud83d\\udcb0 Navjot owes you $36.95\"],\"is_payer\":false}', '2025-09-24 03:09:00', 'completed', '2025-09-24 03:09:00', '2025-09-24 03:09:00'),
(6, 3, 2, NULL, 'expense', '🛒 Expense: Freshco Grocery', -32.41, 'EXP20250924003', -69.37, -101.78, -32.41, '{\"note\":\"Your share: $32.42 (paid by Navjot)\",\"expense_total\":\"97.25\",\"your_share\":32.42,\"participants\":3,\"debt_details\":[\"\\ud83d\\udcb3 You now owe $32.41\"],\"is_payer\":false}', '2025-09-24 03:09:00', 'completed', '2025-09-24 03:09:00', '2025-09-24 03:09:00'),
(7, 1, 3, NULL, 'expense', '🛒 Expense: Freshco', -14.90, 'EXP20250924004', -4.54, -19.44, -14.90, '{\"note\":\"Your share: $14.90 (paid by Sapna)\",\"expense_total\":\"44.70\",\"your_share\":14.9,\"participants\":3,\"debt_details\":[\"\\ud83d\\udcb3 You now owe $51.85\"],\"is_payer\":false}', '2025-09-24 03:11:17', 'completed', '2025-09-24 03:11:17', '2025-09-24 03:11:17'),
(8, 2, 3, NULL, 'expense', '🛒 Expense: Freshco', 29.80, 'EXP20250924005', 106.32, 136.12, 29.80, '{\"note\":\"Your share: $14.90 (paid by Sapna)\",\"expense_total\":\"44.70\",\"your_share\":14.9,\"participants\":3,\"debt_details\":[],\"is_payer\":false}', '2025-09-24 03:11:17', 'completed', '2025-09-24 03:11:17', '2025-09-24 03:11:17'),
(9, 3, 3, NULL, 'expense', '🛒 Expense: Freshco', -14.90, 'EXP20250924006', -101.78, -116.68, -14.90, '{\"note\":\"Your share: $14.90 (paid by Sapna)\",\"expense_total\":\"44.70\",\"your_share\":14.9,\"participants\":3,\"debt_details\":[\"\\ud83d\\udcb3 You now owe $84.27\"],\"is_payer\":false}', '2025-09-24 03:11:17', 'completed', '2025-09-24 03:11:17', '2025-09-24 03:11:17'),
(10, 1, 4, NULL, 'expense', '🛒 Expense: Freshco Grocery', -31.48, 'EXP20251002001', -19.44, -50.92, -31.48, '{\"note\":\"Your share: $31.48 (paid by Sapna)\",\"expense_total\":\"94.44\",\"your_share\":31.48,\"participants\":3,\"debt_details\":[\"\\ud83d\\udcb3 You now owe $83.33\"],\"is_payer\":false}', '2025-10-02 02:18:13', 'completed', '2025-10-02 02:18:13', '2025-10-02 02:18:13'),
(11, 2, 4, NULL, 'expense', '🛒 Expense: Freshco Grocery', 62.96, 'EXP20251002002', 136.12, 199.08, 62.96, '{\"note\":\"Your share: $31.48 (paid by Sapna)\",\"expense_total\":\"94.44\",\"your_share\":31.48,\"participants\":3,\"debt_details\":[],\"is_payer\":false}', '2025-10-02 02:18:13', 'completed', '2025-10-02 02:18:13', '2025-10-02 02:18:13'),
(12, 3, 4, NULL, 'expense', '🛒 Expense: Freshco Grocery', -31.48, 'EXP20251002003', -116.68, -148.16, -31.48, '{\"note\":\"Your share: $31.48 (paid by Sapna)\",\"expense_total\":\"94.44\",\"your_share\":31.48,\"participants\":3,\"debt_details\":[\"\\ud83d\\udcb3 You now owe $115.75\"],\"is_payer\":false}', '2025-10-02 02:18:13', 'completed', '2025-10-02 02:18:13', '2025-10-02 02:18:13'),
(13, 1, 5, NULL, 'expense', '🛒 Expense: Freshco Grocery', 93.80, 'EXP20251010001', -50.92, 42.88, 93.80, '{\"note\":\"Your share: $46.90 (paid by Navjot)\",\"expense_total\":\"140.71\",\"your_share\":46.9,\"participants\":3,\"debt_details\":[],\"is_payer\":false}', '2025-10-10 02:01:00', 'completed', '2025-10-10 02:01:00', '2025-10-10 02:01:00'),
(14, 2, 5, NULL, 'expense', '🛒 Expense: Freshco Grocery', -46.90, 'EXP20251010002', 199.08, 152.18, -46.90, '{\"note\":\"Your share: $46.90 (paid by Navjot)\",\"expense_total\":\"140.71\",\"your_share\":46.9,\"participants\":3,\"debt_details\":[\"\\ud83d\\udcb0 Navjot owes you $36.43\"],\"is_payer\":false}', '2025-10-10 02:01:00', 'completed', '2025-10-10 02:01:00', '2025-10-10 02:01:00'),
(15, 3, 5, NULL, 'expense', '🛒 Expense: Freshco Grocery', -46.90, 'EXP20251010003', -148.16, -195.06, -46.90, '{\"note\":\"Your share: $46.90 (paid by Navjot)\",\"expense_total\":\"140.71\",\"your_share\":46.9,\"participants\":3,\"debt_details\":[\"\\ud83d\\udcb3 You now owe $79.31\"],\"is_payer\":false}', '2025-10-10 02:01:00', 'completed', '2025-10-10 02:01:00', '2025-10-10 02:01:00'),
(16, 1, 6, NULL, 'expense', '🛒 Expense: Wallmart Groceries', 46.96, 'EXP20251028001', 42.88, 89.84, 46.96, '{\"note\":\"Your share: $23.48 (paid by Navjot)\",\"expense_total\":\"70.44\",\"your_share\":23.48,\"participants\":3,\"debt_details\":[],\"is_payer\":false}', '2025-10-28 13:21:48', 'completed', '2025-10-28 13:21:48', '2025-10-28 13:21:48'),
(17, 2, 6, NULL, 'expense', '🛒 Expense: Wallmart Groceries', -23.48, 'EXP20251028002', 152.18, 128.70, -23.48, '{\"note\":\"Your share: $23.48 (paid by Navjot)\",\"expense_total\":\"70.44\",\"your_share\":23.48,\"participants\":3,\"debt_details\":[\"\\ud83d\\udcb0 Navjot owes you $12.95\"],\"is_payer\":false}', '2025-10-28 13:21:48', 'completed', '2025-10-28 13:21:48', '2025-10-28 13:21:48'),
(18, 3, 6, NULL, 'expense', '🛒 Expense: Wallmart Groceries', -23.48, 'EXP20251028003', -195.06, -218.54, -23.48, '{\"note\":\"Your share: $23.48 (paid by Navjot)\",\"expense_total\":\"70.44\",\"your_share\":23.48,\"participants\":3,\"debt_details\":[\"\\ud83d\\udcb3 You now owe $102.79\"],\"is_payer\":false}', '2025-10-28 13:21:48', 'completed', '2025-10-28 13:21:48', '2025-10-28 13:21:48'),
(19, 1, 7, NULL, 'expense', '🛒 Expense: Grocery', -19.49, 'EXP20251102001', 89.84, 70.35, -19.49, '{\"note\":\"Your share: $19.49 (paid by Anu)\",\"expense_total\":\"58.46\",\"your_share\":19.49,\"participants\":3,\"debt_details\":[\"\\ud83d\\udcb0 Anu owes you $83.30\"],\"is_payer\":false}', '2025-11-02 02:06:04', 'completed', '2025-11-02 02:06:04', '2025-11-02 02:06:04'),
(20, 2, 7, NULL, 'expense', '🛒 Expense: Grocery', -19.49, 'EXP20251102002', 128.70, 109.21, -19.49, '{\"note\":\"Your share: $19.49 (paid by Anu)\",\"expense_total\":\"58.46\",\"your_share\":19.49,\"participants\":3,\"debt_details\":[\"\\ud83d\\udcb0 Anu owes you $96.26\"],\"is_payer\":false}', '2025-11-02 02:06:04', 'completed', '2025-11-02 02:06:04', '2025-11-02 02:06:04'),
(21, 3, 7, NULL, 'expense', '🛒 Expense: Grocery', 38.98, 'EXP20251102003', -218.54, -179.56, 38.98, '{\"note\":\"Your share: $19.49 (paid by Anu)\",\"expense_total\":\"58.46\",\"your_share\":19.49,\"participants\":3,\"debt_details\":[],\"is_payer\":false}', '2025-11-02 02:06:04', 'completed', '2025-11-02 02:06:04', '2025-11-02 02:06:04'),
(22, 1, 8, NULL, 'expense', '🛒 Expense: Freshco Grocery', -21.49, 'EXP20251105001', 70.35, 48.86, -21.49, '{\"note\":\"Your share: $21.49 (paid by Anu)\",\"expense_total\":\"64.46\",\"your_share\":21.49,\"participants\":3,\"debt_details\":[\"\\ud83d\\udcb0 Anu owes you $61.81\"],\"is_payer\":false}', '2025-11-05 10:35:21', 'completed', '2025-11-05 10:35:21', '2025-11-05 10:35:21'),
(23, 2, 8, NULL, 'expense', '🛒 Expense: Freshco Grocery', -21.49, 'EXP20251105002', 109.21, 87.72, -21.49, '{\"note\":\"Your share: $21.49 (paid by Anu)\",\"expense_total\":\"64.46\",\"your_share\":21.49,\"participants\":3,\"debt_details\":[\"\\ud83d\\udcb0 Anu owes you $74.77\"],\"is_payer\":false}', '2025-11-05 10:35:21', 'completed', '2025-11-05 10:35:21', '2025-11-05 10:35:21'),
(24, 3, 8, NULL, 'expense', '🛒 Expense: Freshco Grocery', 42.98, 'EXP20251105003', -179.56, -136.58, 42.98, '{\"note\":\"Your share: $21.49 (paid by Anu)\",\"expense_total\":\"64.46\",\"your_share\":21.49,\"participants\":3,\"debt_details\":[],\"is_payer\":false}', '2025-11-05 10:35:21', 'completed', '2025-11-05 10:35:21', '2025-11-05 10:35:21'),
(25, 1, 9, NULL, 'expense', '🛒 Expense: Utility', 7.91, 'EXP20251106001', 48.86, 56.77, 7.91, '{\"note\":\"Your share: $3.96 (paid by Navjot)\",\"expense_total\":\"11.87\",\"your_share\":3.96,\"participants\":3,\"debt_details\":[],\"is_payer\":false}', '2025-11-06 21:54:21', 'completed', '2025-11-06 21:54:21', '2025-11-06 21:54:21'),
(26, 2, 9, NULL, 'expense', '🛒 Expense: Utility', -3.96, 'EXP20251106002', 87.72, 83.76, -3.96, '{\"note\":\"Your share: $3.96 (paid by Navjot)\",\"expense_total\":\"11.87\",\"your_share\":3.96,\"participants\":3,\"debt_details\":[\"\\ud83d\\udcb0 Navjot owes you $8.99\"],\"is_payer\":false}', '2025-11-06 21:54:21', 'completed', '2025-11-06 21:54:21', '2025-11-06 21:54:21'),
(27, 3, 9, NULL, 'expense', '🛒 Expense: Utility', -3.95, 'EXP20251106003', -136.58, -140.53, -3.95, '{\"note\":\"Your share: $3.96 (paid by Navjot)\",\"expense_total\":\"11.87\",\"your_share\":3.96,\"participants\":3,\"debt_details\":[\"\\ud83d\\udcb3 You now owe $65.76\"],\"is_payer\":false}', '2025-11-06 21:54:21', 'completed', '2025-11-06 21:54:21', '2025-11-06 21:54:21'),
(28, 1, 10, NULL, 'expense', '🛒 Expense: Freshco', -35.17, 'EXP20251112001', 56.77, 21.60, -35.17, '{\"note\":\"Your share: $35.16 (paid by Sapna)\",\"expense_total\":\"105.49\",\"your_share\":35.16,\"participants\":3,\"debt_details\":[\"\\ud83d\\udcb3 You now owe $44.16\"],\"is_payer\":false}', '2025-11-12 05:22:54', 'completed', '2025-11-12 05:22:54', '2025-11-12 05:22:54'),
(29, 2, 10, NULL, 'expense', '🛒 Expense: Freshco', 70.33, 'EXP20251112002', 83.76, 154.09, 70.33, '{\"note\":\"Your share: $35.16 (paid by Sapna)\",\"expense_total\":\"105.49\",\"your_share\":35.16,\"participants\":3,\"debt_details\":[],\"is_payer\":false}', '2025-11-12 05:22:54', 'completed', '2025-11-12 05:22:54', '2025-11-12 05:22:54'),
(30, 3, 10, NULL, 'expense', '🛒 Expense: Freshco', -35.16, 'EXP20251112003', -140.53, -175.69, -35.16, '{\"note\":\"Your share: $35.16 (paid by Sapna)\",\"expense_total\":\"105.49\",\"your_share\":35.16,\"participants\":3,\"debt_details\":[\"\\ud83d\\udcb3 You now owe $109.93\"],\"is_payer\":false}', '2025-11-12 05:22:54', 'completed', '2025-11-12 05:22:54', '2025-11-12 05:22:54'),
(31, 1, 11, NULL, 'expense', '🛒 Expense: Wallmart groceries', 14.62, 'EXP20251119001', 21.60, 36.22, 14.62, '{\"note\":\"Your share: $7.31 (paid by Navjot)\",\"expense_total\":\"21.93\",\"your_share\":7.31,\"participants\":3,\"debt_details\":[],\"is_payer\":false}', '2025-11-19 05:12:08', 'completed', '2025-11-19 05:12:08', '2025-11-19 05:12:08'),
(32, 2, 11, NULL, 'expense', '🛒 Expense: Wallmart groceries', -7.31, 'EXP20251119002', 154.09, 146.78, -7.31, '{\"note\":\"Your share: $7.31 (paid by Navjot)\",\"expense_total\":\"21.93\",\"your_share\":7.31,\"participants\":3,\"debt_details\":[\"\\ud83d\\udcb0 Navjot owes you $36.85\"],\"is_payer\":false}', '2025-11-19 05:12:08', 'completed', '2025-11-19 05:12:08', '2025-11-19 05:12:08'),
(33, 3, 11, NULL, 'expense', '🛒 Expense: Wallmart groceries', -7.31, 'EXP20251119003', -175.69, -183.00, -7.31, '{\"note\":\"Your share: $7.31 (paid by Navjot)\",\"expense_total\":\"21.93\",\"your_share\":7.31,\"participants\":3,\"debt_details\":[\"\\ud83d\\udcb3 You now owe $73.07\"],\"is_payer\":false}', '2025-11-19 05:12:08', 'completed', '2025-11-19 05:12:08', '2025-11-19 05:12:08'),
(34, 1, 12, NULL, 'expense', '🛒 Expense: Grocery', -61.00, 'EXP20251127001', 36.22, -24.78, -61.00, '{\"note\":\"Your share: $61.00 (paid by Anu)\",\"expense_total\":\"183.00\",\"your_share\":61,\"participants\":3,\"debt_details\":[\"\\ud83d\\udcb0 Anu owes you $12.07\"],\"is_payer\":false}', '2025-11-27 18:57:23', 'completed', '2025-11-27 18:57:23', '2025-11-27 18:57:23'),
(35, 2, 12, NULL, 'expense', '🛒 Expense: Grocery', -61.00, 'EXP20251127002', 146.78, 85.78, -61.00, '{\"note\":\"Your share: $61.00 (paid by Anu)\",\"expense_total\":\"183.00\",\"your_share\":61,\"participants\":3,\"debt_details\":[\"\\ud83d\\udcb0 Anu owes you $48.93\"],\"is_payer\":false}', '2025-11-27 18:57:23', 'completed', '2025-11-27 18:57:23', '2025-11-27 18:57:23'),
(36, 3, 12, NULL, 'expense', '🛒 Expense: Grocery', 122.00, 'EXP20251127003', -183.00, -61.00, 122.00, '{\"note\":\"Your share: $61.00 (paid by Anu)\",\"expense_total\":\"183.00\",\"your_share\":61,\"participants\":3,\"debt_details\":[],\"is_payer\":false}', '2025-11-27 18:57:23', 'completed', '2025-11-27 18:57:23', '2025-11-27 18:57:23'),
(37, 1, 13, NULL, 'expense', '🛒 Expense: Freshco grocery paid by sapna card', -36.34, 'EXP20251221001', -24.78, -61.12, -36.34, '{\"note\":\"Your share: $36.33 (paid by Sapna)\",\"expense_total\":\"109.00\",\"your_share\":36.33,\"participants\":3,\"debt_details\":[\"\\ud83d\\udcb3 You now owe $73.19\"],\"is_payer\":false}', '2025-12-21 22:06:25', 'completed', '2025-12-21 22:06:25', '2025-12-21 22:06:25'),
(38, 2, 13, NULL, 'expense', '🛒 Expense: Freshco grocery paid by sapna card', 72.67, 'EXP20251221002', 85.78, 158.45, 72.67, '{\"note\":\"Your share: $36.33 (paid by Sapna)\",\"expense_total\":\"109.00\",\"your_share\":36.33,\"participants\":3,\"debt_details\":[],\"is_payer\":false}', '2025-12-21 22:06:25', 'completed', '2025-12-21 22:06:25', '2025-12-21 22:06:25'),
(39, 3, 13, NULL, 'expense', '🛒 Expense: Freshco grocery paid by sapna card', -36.33, 'EXP20251221003', -61.00, -97.33, -36.33, '{\"note\":\"Your share: $36.33 (paid by Sapna)\",\"expense_total\":\"109.00\",\"your_share\":36.33,\"participants\":3,\"debt_details\":[\"\\ud83d\\udcb3 You now owe $85.26\"],\"is_payer\":false}', '2025-12-21 22:06:25', 'completed', '2025-12-21 22:06:25', '2025-12-21 22:06:25'),
(40, 1, 14, NULL, 'expense', '🛒 Expense: India parcel - DHL CUSTOM CLEAR', 13.33, 'EXP20251221004', -61.12, -47.79, 13.33, '{\"note\":\"Your share: $6.67 (paid by Navjot)\",\"expense_total\":\"20.00\",\"your_share\":6.67,\"participants\":3,\"debt_details\":[],\"is_payer\":false}', '2025-12-21 22:08:15', 'completed', '2025-12-21 22:08:15', '2025-12-21 22:08:15'),
(41, 2, 14, NULL, 'expense', '🛒 Expense: India parcel - DHL CUSTOM CLEAR', -6.67, 'EXP20251221005', 158.45, 151.78, -6.67, '{\"note\":\"Your share: $6.67 (paid by Navjot)\",\"expense_total\":\"20.00\",\"your_share\":6.67,\"participants\":3,\"debt_details\":[\"\\ud83d\\udcb0 Navjot owes you $66.52\"],\"is_payer\":false}', '2025-12-21 22:08:15', 'completed', '2025-12-21 22:08:15', '2025-12-21 22:08:15'),
(42, 3, 14, NULL, 'expense', '🛒 Expense: India parcel - DHL CUSTOM CLEAR', -6.66, 'EXP20251221006', -97.33, -103.99, -6.66, '{\"note\":\"Your share: $6.67 (paid by Navjot)\",\"expense_total\":\"20.00\",\"your_share\":6.67,\"participants\":3,\"debt_details\":[\"\\ud83d\\udcb3 You now owe $18.73\"],\"is_payer\":false}', '2025-12-21 22:08:15', 'completed', '2025-12-21 22:08:15', '2025-12-21 22:08:15'),
(43, 1, 15, NULL, 'expense', '🛒 Expense: Household utilities', 11.33, 'EXP20251230001', -47.79, -36.46, 11.33, '{\"note\":\"Your share: $5.67 (paid by Navjot)\",\"expense_total\":\"17.00\",\"your_share\":5.67,\"participants\":3,\"debt_details\":[],\"is_payer\":false}', '2025-12-30 05:48:42', 'completed', '2025-12-30 05:48:42', '2025-12-30 05:48:42'),
(44, 2, 15, NULL, 'expense', '🛒 Expense: Household utilities', -5.67, 'EXP20251230002', 151.78, 146.11, -5.67, '{\"note\":\"Your share: $5.67 (paid by Navjot)\",\"expense_total\":\"17.00\",\"your_share\":5.67,\"participants\":3,\"debt_details\":[\"\\ud83d\\udcb0 Navjot owes you $60.85\"],\"is_payer\":false}', '2025-12-30 05:48:42', 'completed', '2025-12-30 05:48:42', '2025-12-30 05:48:42'),
(45, 3, 15, NULL, 'expense', '🛒 Expense: Household utilities', -5.66, 'EXP20251230003', -103.99, -109.65, -5.66, '{\"note\":\"Your share: $5.67 (paid by Navjot)\",\"expense_total\":\"17.00\",\"your_share\":5.67,\"participants\":3,\"debt_details\":[\"\\ud83d\\udcb3 You now owe $24.39\"],\"is_payer\":false}', '2025-12-30 05:48:42', 'completed', '2025-12-30 05:48:42', '2025-12-30 05:48:42'),
(46, 1, 16, NULL, 'expense', '🛒 Expense: Foodbasic', 67.33, 'EXP20260103001', -36.46, 30.87, 67.33, '{\"note\":\"Your share: $33.67 (paid by Navjot)\",\"expense_total\":\"101.00\",\"your_share\":33.67,\"participants\":3,\"debt_details\":[],\"is_payer\":false}', '2026-01-03 00:42:03', 'completed', '2026-01-03 00:42:03', '2026-01-03 00:42:03'),
(47, 2, 16, NULL, 'expense', '🛒 Expense: Foodbasic', -33.67, 'EXP20260103002', 146.11, 112.44, -33.67, '{\"note\":\"Your share: $33.67 (paid by Navjot)\",\"expense_total\":\"101.00\",\"your_share\":33.67,\"participants\":3,\"debt_details\":[\"\\ud83d\\udcb0 Navjot owes you $27.18\"],\"is_payer\":false}', '2026-01-03 00:42:03', 'completed', '2026-01-03 00:42:03', '2026-01-03 00:42:03'),
(48, 3, 16, NULL, 'expense', '🛒 Expense: Foodbasic', -33.66, 'EXP20260103003', -109.65, -143.31, -33.66, '{\"note\":\"Your share: $33.67 (paid by Navjot)\",\"expense_total\":\"101.00\",\"your_share\":33.67,\"participants\":3,\"debt_details\":[\"\\ud83d\\udcb3 You now owe $58.05\"],\"is_payer\":false}', '2026-01-03 00:42:03', 'completed', '2026-01-03 00:42:03', '2026-01-03 00:42:03'),
(49, 1, 17, NULL, 'expense', '🛒 Expense: Freshco', 43.33, 'EXP20260103004', 30.87, 74.20, 43.33, '{\"note\":\"Your share: $21.67 (paid by Navjot)\",\"expense_total\":\"65.00\",\"your_share\":21.67,\"participants\":3,\"debt_details\":[],\"is_payer\":false}', '2026-01-03 00:42:34', 'completed', '2026-01-03 00:42:34', '2026-01-03 00:42:34'),
(50, 2, 17, NULL, 'expense', '🛒 Expense: Freshco', -21.67, 'EXP20260103005', 112.44, 90.77, -21.67, '{\"note\":\"Your share: $21.67 (paid by Navjot)\",\"expense_total\":\"65.00\",\"your_share\":21.67,\"participants\":3,\"debt_details\":[\"\\ud83d\\udcb0 Navjot owes you $5.51\"],\"is_payer\":false}', '2026-01-03 00:42:34', 'completed', '2026-01-03 00:42:34', '2026-01-03 00:42:34'),
(51, 3, 17, NULL, 'expense', '🛒 Expense: Freshco', -21.66, 'EXP20260103006', -143.31, -164.97, -21.66, '{\"note\":\"Your share: $21.67 (paid by Navjot)\",\"expense_total\":\"65.00\",\"your_share\":21.67,\"participants\":3,\"debt_details\":[\"\\ud83d\\udcb3 You now owe $79.71\"],\"is_payer\":false}', '2026-01-03 00:42:34', 'completed', '2026-01-03 00:42:34', '2026-01-03 00:42:34'),
(52, 1, 18, NULL, 'expense', '🛒 Expense: Utility', 11.33, 'EXP20260107001', 74.20, 85.53, 11.33, '{\"note\":\"Your share: $5.67 (paid by Navjot)\",\"expense_total\":\"17.00\",\"your_share\":5.67,\"participants\":3,\"debt_details\":[],\"is_payer\":false}', '2026-01-07 13:32:20', 'completed', '2026-01-07 13:32:20', '2026-01-07 13:32:20'),
(53, 2, 18, NULL, 'expense', '🛒 Expense: Utility', -5.67, 'EXP20260107002', 90.77, 85.10, -5.67, '{\"note\":\"Your share: $5.67 (paid by Navjot)\",\"expense_total\":\"17.00\",\"your_share\":5.67,\"participants\":3,\"debt_details\":[\"\\ud83d\\udcb3 You now owe $0.16\"],\"is_payer\":false}', '2026-01-07 13:32:20', 'completed', '2026-01-07 13:32:20', '2026-01-07 13:32:20'),
(54, 3, 18, NULL, 'expense', '🛒 Expense: Utility', -5.66, 'EXP20260107003', -164.97, -170.63, -5.66, '{\"note\":\"Your share: $5.67 (paid by Navjot)\",\"expense_total\":\"17.00\",\"your_share\":5.67,\"participants\":3,\"debt_details\":[\"\\ud83d\\udcb3 You now owe $85.37\"],\"is_payer\":false}', '2026-01-07 13:32:20', 'completed', '2026-01-07 13:32:20', '2026-01-07 13:32:20'),
(55, 1, 19, NULL, 'expense', '🛒 Expense: Freshco grocery', -54.60, 'EXP20260115001', 85.53, 30.93, -54.60, '{\"note\":\"Your share: $54.60 (paid by Anu)\",\"expense_total\":\"163.79\",\"your_share\":54.6,\"participants\":3,\"debt_details\":[\"\\ud83d\\udcb0 Anu owes you $30.77\"],\"is_payer\":false}', '2026-01-15 23:33:54', 'completed', '2026-01-15 23:33:54', '2026-01-15 23:33:54'),
(56, 2, 19, NULL, 'expense', '🛒 Expense: Freshco grocery', -54.60, 'EXP20260115002', 85.10, 30.50, -54.60, '{\"note\":\"Your share: $54.60 (paid by Anu)\",\"expense_total\":\"163.79\",\"your_share\":54.6,\"participants\":3,\"debt_details\":[\"\\ud83d\\udcb0 Anu owes you $30.66\"],\"is_payer\":false}', '2026-01-15 23:33:54', 'completed', '2026-01-15 23:33:54', '2026-01-15 23:33:54'),
(57, 3, 19, NULL, 'expense', '🛒 Expense: Freshco grocery', 109.20, 'EXP20260115003', -170.63, -61.43, 109.20, '{\"note\":\"Your share: $54.60 (paid by Anu)\",\"expense_total\":\"163.79\",\"your_share\":54.6,\"participants\":3,\"debt_details\":[],\"is_payer\":false}', '2026-01-15 23:33:54', 'completed', '2026-01-15 23:33:54', '2026-01-15 23:33:54'),
(58, 1, 20, NULL, 'expense', '🛒 Expense: Freshco Grocery small', 20.00, 'EXP20260123001', 30.93, 50.93, 20.00, '{\"note\":\"Your share: $10.00 (paid by Navjot)\",\"expense_total\":\"30.00\",\"your_share\":10,\"participants\":3,\"debt_details\":[],\"is_payer\":false}', '2026-01-23 13:18:47', 'completed', '2026-01-23 13:18:47', '2026-01-23 13:18:47'),
(59, 2, 20, NULL, 'expense', '🛒 Expense: Freshco Grocery small', -10.00, 'EXP20260123002', 30.50, 20.50, -10.00, '{\"note\":\"Your share: $10.00 (paid by Navjot)\",\"expense_total\":\"30.00\",\"your_share\":10,\"participants\":3,\"debt_details\":[\"\\ud83d\\udcb3 You now owe $10.16\"],\"is_payer\":false}', '2026-01-23 13:18:47', 'completed', '2026-01-23 13:18:47', '2026-01-23 13:18:47'),
(60, 3, 20, NULL, 'expense', '🛒 Expense: Freshco Grocery small', -10.00, 'EXP20260123003', -61.43, -71.43, -10.00, '{\"note\":\"Your share: $10.00 (paid by Navjot)\",\"expense_total\":\"30.00\",\"your_share\":10,\"participants\":3,\"debt_details\":[\"\\ud83d\\udcb3 You now owe $40.77\"],\"is_payer\":false}', '2026-01-23 13:18:47', 'completed', '2026-01-23 13:18:47', '2026-01-23 13:18:47'),
(61, 1, 21, NULL, 'expense', '🛒 Expense: Freshco', -59.00, 'EXP20260128001', 50.93, -8.07, -59.00, '{\"note\":\"Your share: $59.00 (paid by Sapna)\",\"expense_total\":\"177.00\",\"your_share\":59,\"participants\":3,\"debt_details\":[\"\\ud83d\\udcb3 You now owe $48.84\"],\"is_payer\":false}', '2026-01-28 02:20:19', 'completed', '2026-01-28 02:20:19', '2026-01-28 02:20:19'),
(62, 2, 21, NULL, 'expense', '🛒 Expense: Freshco', 118.00, 'EXP20260128002', 20.50, 138.50, 118.00, '{\"note\":\"Your share: $59.00 (paid by Sapna)\",\"expense_total\":\"177.00\",\"your_share\":59,\"participants\":3,\"debt_details\":[],\"is_payer\":false}', '2026-01-28 02:20:19', 'completed', '2026-01-28 02:20:19', '2026-01-28 02:20:19'),
(63, 3, 21, NULL, 'expense', '🛒 Expense: Freshco', -59.00, 'EXP20260128003', -71.43, -130.43, -59.00, '{\"note\":\"Your share: $59.00 (paid by Sapna)\",\"expense_total\":\"177.00\",\"your_share\":59,\"participants\":3,\"debt_details\":[\"\\ud83d\\udcb3 You now owe $89.66\"],\"is_payer\":false}', '2026-01-28 02:20:19', 'completed', '2026-01-28 02:20:19', '2026-01-28 02:20:19'),
(64, 1, 22, NULL, 'expense', '🛒 Expense: freshco', -42.88, 'EXP20260208001', -8.07, -50.95, -42.88, '{\"note\":\"Your share: $42.88 (paid by Anu)\",\"expense_total\":\"128.64\",\"your_share\":42.88,\"participants\":3,\"debt_details\":[\"\\ud83d\\udcb3 You now owe $2.11\"],\"is_payer\":false}', '2026-02-08 02:05:30', 'completed', '2026-02-08 02:05:30', '2026-02-08 02:05:30'),
(65, 2, 22, NULL, 'expense', '🛒 Expense: freshco', -42.88, 'EXP20260208002', 138.50, 95.62, -42.88, '{\"note\":\"Your share: $42.88 (paid by Anu)\",\"expense_total\":\"128.64\",\"your_share\":42.88,\"participants\":3,\"debt_details\":[\"\\ud83d\\udcb0 Anu owes you $46.78\"],\"is_payer\":false}', '2026-02-08 02:05:30', 'completed', '2026-02-08 02:05:30', '2026-02-08 02:05:30'),
(66, 3, 22, NULL, 'expense', '🛒 Expense: freshco', 85.76, 'EXP20260208003', -130.43, -44.67, 85.76, '{\"note\":\"Your share: $42.88 (paid by Anu)\",\"expense_total\":\"128.64\",\"your_share\":42.88,\"participants\":3,\"debt_details\":[],\"is_payer\":false}', '2026-02-08 02:05:30', 'completed', '2026-02-08 02:05:30', '2026-02-08 02:05:30'),
(67, 1, 23, NULL, 'expense', '🛒 Expense: Grocery', 57.33, 'EXP20260217001', -50.95, 6.38, 57.33, '{\"note\":\"Your share: $28.67 (paid by Navjot)\",\"expense_total\":\"86.00\",\"your_share\":28.67,\"participants\":3,\"debt_details\":[],\"is_payer\":false}', '2026-02-17 08:15:20', 'completed', '2026-02-17 08:15:20', '2026-02-17 08:15:20'),
(68, 2, 23, NULL, 'expense', '🛒 Expense: Grocery', -28.67, 'EXP20260217002', 95.62, 66.95, -28.67, '{\"note\":\"Your share: $28.67 (paid by Navjot)\",\"expense_total\":\"86.00\",\"your_share\":28.67,\"participants\":3,\"debt_details\":[\"\\ud83d\\udcb0 Navjot owes you $20.17\"],\"is_payer\":false}', '2026-02-17 08:15:20', 'completed', '2026-02-17 08:15:20', '2026-02-17 08:15:20'),
(69, 3, 23, NULL, 'expense', '🛒 Expense: Grocery', -28.66, 'EXP20260217003', -44.67, -73.33, -28.66, '{\"note\":\"Your share: $28.67 (paid by Navjot)\",\"expense_total\":\"86.00\",\"your_share\":28.67,\"participants\":3,\"debt_details\":[\"\\ud83d\\udcb3 You now owe $26.55\"],\"is_payer\":false}', '2026-02-17 08:15:20', 'completed', '2026-02-17 08:15:20', '2026-02-17 08:15:20'),
(70, 1, 24, NULL, 'expense', '🛒 Expense: Pressure cooker', 48.00, 'EXP20260224001', 6.38, 54.38, 48.00, '{\"note\":\"Your share: $24.00 (paid by Navjot)\",\"expense_total\":\"72.00\",\"your_share\":24,\"participants\":3,\"debt_details\":[],\"is_payer\":false}', '2026-02-24 17:51:16', 'completed', '2026-02-24 17:51:16', '2026-02-24 17:51:16'),
(71, 2, 24, NULL, 'expense', '🛒 Expense: Pressure cooker', -24.00, 'EXP20260224002', 66.95, 42.95, -24.00, '{\"note\":\"Your share: $24.00 (paid by Navjot)\",\"expense_total\":\"72.00\",\"your_share\":24,\"participants\":3,\"debt_details\":[\"\\ud83d\\udcb3 You now owe $3.83\"],\"is_payer\":false}', '2026-02-24 17:51:16', 'completed', '2026-02-24 17:51:16', '2026-02-24 17:51:16'),
(72, 3, 24, NULL, 'expense', '🛒 Expense: Pressure cooker', -24.00, 'EXP20260224003', -73.33, -97.33, -24.00, '{\"note\":\"Your share: $24.00 (paid by Navjot)\",\"expense_total\":\"72.00\",\"your_share\":24,\"participants\":3,\"debt_details\":[\"\\ud83d\\udcb3 You now owe $50.55\"],\"is_payer\":false}', '2026-02-24 17:51:16', 'completed', '2026-02-24 17:51:16', '2026-02-24 17:51:16'),
(73, 1, 25, NULL, 'expense', '🛒 Expense: freshco', 25.33, 'EXP20260226001', 54.38, 79.71, 25.33, '{\"note\":\"Your share: $12.67 (paid by Navjot)\",\"expense_total\":\"38.00\",\"your_share\":12.67,\"participants\":3,\"debt_details\":[],\"is_payer\":false}', '2026-02-26 03:25:50', 'completed', '2026-02-26 03:25:50', '2026-02-26 03:25:50'),
(74, 2, 25, NULL, 'expense', '🛒 Expense: freshco', -12.67, 'EXP20260226002', 42.95, 30.28, -12.67, '{\"note\":\"Your share: $12.67 (paid by Navjot)\",\"expense_total\":\"38.00\",\"your_share\":12.67,\"participants\":3,\"debt_details\":[\"\\ud83d\\udcb3 You now owe $16.50\"],\"is_payer\":false}', '2026-02-26 03:25:50', 'completed', '2026-02-26 03:25:50', '2026-02-26 03:25:50'),
(75, 3, 25, NULL, 'expense', '🛒 Expense: freshco', -12.66, 'EXP20260226003', -97.33, -109.99, -12.66, '{\"note\":\"Your share: $12.67 (paid by Navjot)\",\"expense_total\":\"38.00\",\"your_share\":12.67,\"participants\":3,\"debt_details\":[\"\\ud83d\\udcb3 You now owe $63.21\"],\"is_payer\":false}', '2026-02-26 03:25:50', 'completed', '2026-02-26 03:25:50', '2026-02-26 03:25:50'),
(76, 1, 26, NULL, 'expense', '🛒 Expense: Freshco Grocery', -73.00, 'EXP20260226004', 79.71, 6.71, -73.00, '{\"note\":\"Your share: $73.00 (paid by Sapna)\",\"expense_total\":\"219.00\",\"your_share\":73,\"participants\":3,\"debt_details\":[\"\\ud83d\\udcb3 You now owe $56.50\"],\"is_payer\":false}', '2026-02-26 03:26:27', 'completed', '2026-02-26 03:26:27', '2026-02-26 03:26:27'),
(77, 2, 26, NULL, 'expense', '🛒 Expense: Freshco Grocery', 146.00, 'EXP20260226005', 30.28, 176.28, 146.00, '{\"note\":\"Your share: $73.00 (paid by Sapna)\",\"expense_total\":\"219.00\",\"your_share\":73,\"participants\":3,\"debt_details\":[],\"is_payer\":false}', '2026-02-26 03:26:27', 'completed', '2026-02-26 03:26:27', '2026-02-26 03:26:27'),
(78, 3, 26, NULL, 'expense', '🛒 Expense: Freshco Grocery', -73.00, 'EXP20260226006', -109.99, -182.99, -73.00, '{\"note\":\"Your share: $73.00 (paid by Sapna)\",\"expense_total\":\"219.00\",\"your_share\":73,\"participants\":3,\"debt_details\":[\"\\ud83d\\udcb3 You now owe $119.78\"],\"is_payer\":false}', '2026-02-26 03:26:27', 'completed', '2026-02-26 03:26:27', '2026-02-26 03:26:27'),
(79, 1, 27, NULL, 'expense', '🛒 Expense: Grocery', 11.08, 'EXP20260228001', 6.71, 17.79, 11.08, '{\"note\":\"Your share: $5.54 (paid by Navjot)\",\"expense_total\":\"16.62\",\"your_share\":5.54,\"participants\":3,\"debt_details\":[],\"is_payer\":false}', '2026-02-28 02:46:46', 'completed', '2026-02-28 02:46:46', '2026-02-28 02:46:46'),
(80, 2, 27, NULL, 'expense', '🛒 Expense: Grocery', -5.54, 'EXP20260228002', 176.28, 170.74, -5.54, '{\"note\":\"Your share: $5.54 (paid by Navjot)\",\"expense_total\":\"16.62\",\"your_share\":5.54,\"participants\":3,\"debt_details\":[\"\\ud83d\\udcb0 Navjot owes you $50.96\"],\"is_payer\":false}', '2026-02-28 02:46:46', 'completed', '2026-02-28 02:46:46', '2026-02-28 02:46:46'),
(81, 3, 27, NULL, 'expense', '🛒 Expense: Grocery', -5.54, 'EXP20260228003', -182.99, -188.53, -5.54, '{\"note\":\"Your share: $5.54 (paid by Navjot)\",\"expense_total\":\"16.62\",\"your_share\":5.54,\"participants\":3,\"debt_details\":[\"\\ud83d\\udcb3 You now owe $68.75\"],\"is_payer\":false}', '2026-02-28 02:46:46', 'completed', '2026-02-28 02:46:46', '2026-02-28 02:46:46'),
(82, 1, 28, NULL, 'expense', '🛒 Expense: Freshco', -9.34, 'EXP20260302001', 17.79, 8.45, -9.34, '{\"note\":\"Your share: $9.33 (paid by Sapna)\",\"expense_total\":\"28.00\",\"your_share\":9.33,\"participants\":3,\"debt_details\":[\"\\ud83d\\udcb3 You now owe $60.30\"],\"is_payer\":false}', '2026-03-02 20:08:33', 'completed', '2026-03-02 20:08:33', '2026-03-02 20:08:33'),
(83, 2, 28, NULL, 'expense', '🛒 Expense: Freshco', 18.67, 'EXP20260302002', 170.74, 189.41, 18.67, '{\"note\":\"Your share: $9.33 (paid by Sapna)\",\"expense_total\":\"28.00\",\"your_share\":9.33,\"participants\":3,\"debt_details\":[],\"is_payer\":false}', '2026-03-02 20:08:33', 'completed', '2026-03-02 20:08:33', '2026-03-02 20:08:33'),
(84, 3, 28, NULL, 'expense', '🛒 Expense: Freshco', -9.33, 'EXP20260302003', -188.53, -197.86, -9.33, '{\"note\":\"Your share: $9.33 (paid by Sapna)\",\"expense_total\":\"28.00\",\"your_share\":9.33,\"participants\":3,\"debt_details\":[\"\\ud83d\\udcb3 You now owe $129.11\"],\"is_payer\":false}', '2026-03-02 20:08:33', 'completed', '2026-03-02 20:08:33', '2026-03-02 20:08:33'),
(85, 1, 29, NULL, 'expense', '🛒 Expense: coffe', 5.33, 'EXP20260305001', 8.45, 13.78, 5.33, '{\"note\":\"Your share: $2.67 (paid by Navjot)\",\"expense_total\":\"8.00\",\"your_share\":2.67,\"participants\":3,\"debt_details\":[],\"is_payer\":false}', '2026-03-05 01:37:47', 'completed', '2026-03-05 01:37:47', '2026-03-05 01:37:47'),
(86, 2, 29, NULL, 'expense', '🛒 Expense: coffe', -2.67, 'EXP20260305002', 189.41, 186.74, -2.67, '{\"note\":\"Your share: $2.67 (paid by Navjot)\",\"expense_total\":\"8.00\",\"your_share\":2.67,\"participants\":3,\"debt_details\":[\"\\ud83d\\udcb0 Navjot owes you $57.63\"],\"is_payer\":false}', '2026-03-05 01:37:47', 'completed', '2026-03-05 01:37:47', '2026-03-05 01:37:47'),
(87, 3, 29, NULL, 'expense', '🛒 Expense: coffe', -2.66, 'EXP20260305003', -197.86, -200.52, -2.66, '{\"note\":\"Your share: $2.67 (paid by Navjot)\",\"expense_total\":\"8.00\",\"your_share\":2.67,\"participants\":3,\"debt_details\":[\"\\ud83d\\udcb3 You now owe $71.41\"],\"is_payer\":false}', '2026-03-05 01:37:47', 'completed', '2026-03-05 01:37:47', '2026-03-05 01:37:47'),
(88, 1, 30, NULL, 'expense', '🛒 Expense: Dollerrama', -21.00, 'EXP20260307001', 13.78, -7.22, -21.00, '{\"note\":\"Your share: $21.00 (paid by Sapna)\",\"expense_total\":\"63.00\",\"your_share\":21,\"participants\":3,\"debt_details\":[\"\\ud83d\\udcb3 You now owe $78.63\"],\"is_payer\":false}', '2026-03-07 22:47:49', 'completed', '2026-03-07 22:47:49', '2026-03-07 22:47:49'),
(89, 2, 30, NULL, 'expense', '🛒 Expense: Dollerrama', 42.00, 'EXP20260307002', 186.74, 228.74, 42.00, '{\"note\":\"Your share: $21.00 (paid by Sapna)\",\"expense_total\":\"63.00\",\"your_share\":21,\"participants\":3,\"debt_details\":[],\"is_payer\":false}', '2026-03-07 22:47:49', 'completed', '2026-03-07 22:47:49', '2026-03-07 22:47:49'),
(90, 3, 30, NULL, 'expense', '🛒 Expense: Dollerrama', -21.00, 'EXP20260307003', -200.52, -221.52, -21.00, '{\"note\":\"Your share: $21.00 (paid by Sapna)\",\"expense_total\":\"63.00\",\"your_share\":21,\"participants\":3,\"debt_details\":[\"\\ud83d\\udcb3 You now owe $150.11\"],\"is_payer\":false}', '2026-03-07 22:47:49', 'completed', '2026-03-07 22:47:49', '2026-03-07 22:47:49'),
(91, 1, 31, NULL, 'expense', '🛒 Expense: Groceries', -57.00, 'EXP20260310001', -7.22, -64.22, -57.00, '{\"note\":\"Your share: $57.00 (paid by Anu)\",\"expense_total\":\"171.00\",\"your_share\":57,\"participants\":3,\"debt_details\":[\"\\ud83d\\udcb0 Anu owes you $14.41\"],\"is_payer\":false}', '2026-03-10 05:15:44', 'completed', '2026-03-10 05:15:44', '2026-03-10 05:15:44'),
(92, 2, 31, NULL, 'expense', '🛒 Expense: Groceries', -57.00, 'EXP20260310002', 228.74, 171.74, -57.00, '{\"note\":\"Your share: $57.00 (paid by Anu)\",\"expense_total\":\"171.00\",\"your_share\":57,\"participants\":3,\"debt_details\":[\"\\ud83d\\udcb0 Anu owes you $93.11\"],\"is_payer\":false}', '2026-03-10 05:15:44', 'completed', '2026-03-10 05:15:44', '2026-03-10 05:15:44'),
(93, 3, 31, NULL, 'expense', '🛒 Expense: Groceries', 114.00, 'EXP20260310003', -221.52, -107.52, 114.00, '{\"note\":\"Your share: $57.00 (paid by Anu)\",\"expense_total\":\"171.00\",\"your_share\":57,\"participants\":3,\"debt_details\":[],\"is_payer\":false}', '2026-03-10 05:15:44', 'completed', '2026-03-10 05:15:44', '2026-03-10 05:15:44'),
(94, 1, 32, NULL, 'expense', '🛒 Expense: Utility', -24.00, 'EXP20260316001', -64.22, -88.22, -24.00, '{\"note\":\"Your share: $24.00 (paid by Sapna)\",\"expense_total\":\"72.00\",\"your_share\":24,\"participants\":3,\"debt_details\":[\"\\ud83d\\udcb3 You now owe $102.63\"],\"is_payer\":false}', '2026-03-16 15:39:15', 'completed', '2026-03-16 15:39:15', '2026-03-16 15:39:15'),
(95, 2, 32, NULL, 'expense', '🛒 Expense: Utility', 48.00, 'EXP20260316002', 171.74, 219.74, 48.00, '{\"note\":\"Your share: $24.00 (paid by Sapna)\",\"expense_total\":\"72.00\",\"your_share\":24,\"participants\":3,\"debt_details\":[],\"is_payer\":false}', '2026-03-16 15:39:15', 'completed', '2026-03-16 15:39:15', '2026-03-16 15:39:15'),
(96, 3, 32, NULL, 'expense', '🛒 Expense: Utility', -24.00, 'EXP20260316003', -107.52, -131.52, -24.00, '{\"note\":\"Your share: $24.00 (paid by Sapna)\",\"expense_total\":\"72.00\",\"your_share\":24,\"participants\":3,\"debt_details\":[\"\\ud83d\\udcb3 You now owe $117.11\"],\"is_payer\":false}', '2026-03-16 15:39:15', 'completed', '2026-03-16 15:39:15', '2026-03-16 15:39:15'),
(97, 1, 33, NULL, 'expense', '🛒 Expense: Freshco Grocery', 126.66, 'EXP20260323001', -88.22, 38.44, 126.66, '{\"note\":\"Your share: $63.33 (paid by Navjot)\",\"expense_total\":\"190.00\",\"your_share\":63.33,\"participants\":3,\"debt_details\":[],\"is_payer\":false}', '2026-03-23 07:12:08', 'completed', '2026-03-23 07:12:08', '2026-03-23 07:12:08'),
(98, 2, 33, NULL, 'expense', '🛒 Expense: Freshco Grocery', -63.33, 'EXP20260323002', 219.74, 156.41, -63.33, '{\"note\":\"Your share: $63.33 (paid by Navjot)\",\"expense_total\":\"190.00\",\"your_share\":63.33,\"participants\":3,\"debt_details\":[\"\\ud83d\\udcb0 Navjot owes you $39.30\"],\"is_payer\":false}', '2026-03-23 07:12:08', 'completed', '2026-03-23 07:12:08', '2026-03-23 07:12:08'),
(99, 3, 33, NULL, 'expense', '🛒 Expense: Freshco Grocery', -63.33, 'EXP20260323003', -131.52, -194.85, -63.33, '{\"note\":\"Your share: $63.33 (paid by Navjot)\",\"expense_total\":\"190.00\",\"your_share\":63.33,\"participants\":3,\"debt_details\":[\"\\ud83d\\udcb3 You now owe $77.74\"],\"is_payer\":false}', '2026-03-23 07:12:08', 'completed', '2026-03-23 07:12:08', '2026-03-23 07:12:08'),
(100, 1, 34, NULL, 'expense', '🛒 Expense: Freshco Grocery', -33.34, 'EXP20260323004', 38.44, 5.10, -33.34, '{\"note\":\"Your share: $33.33 (paid by Anu)\",\"expense_total\":\"100.00\",\"your_share\":33.33,\"participants\":3,\"debt_details\":[\"\\ud83d\\udcb0 Anu owes you $44.40\"],\"is_payer\":false}', '2026-03-23 07:12:29', 'completed', '2026-03-23 07:12:29', '2026-03-23 07:12:29'),
(101, 2, 34, NULL, 'expense', '🛒 Expense: Freshco Grocery', -33.33, 'EXP20260323005', 156.41, 123.08, -33.33, '{\"note\":\"Your share: $33.33 (paid by Anu)\",\"expense_total\":\"100.00\",\"your_share\":33.33,\"participants\":3,\"debt_details\":[\"\\ud83d\\udcb0 Anu owes you $83.78\"],\"is_payer\":false}', '2026-03-23 07:12:29', 'completed', '2026-03-23 07:12:29', '2026-03-23 07:12:29'),
(102, 3, 34, NULL, 'expense', '🛒 Expense: Freshco Grocery', 66.67, 'EXP20260323006', -194.85, -128.18, 66.67, '{\"note\":\"Your share: $33.33 (paid by Anu)\",\"expense_total\":\"100.00\",\"your_share\":33.33,\"participants\":3,\"debt_details\":[],\"is_payer\":false}', '2026-03-23 07:12:29', 'completed', '2026-03-23 07:12:29', '2026-03-23 07:12:29'),
(103, 1, 35, NULL, 'expense', '🛒 Expense: Freshco Grocery', -72.56, 'EXP20260405001', 5.10, -67.46, -72.56, '{\"note\":\"Your share: $72.56 (paid by Sapna)\",\"expense_total\":\"217.67\",\"your_share\":72.56,\"participants\":3,\"debt_details\":[\"\\ud83d\\udcb3 You now owe $111.86\"],\"is_payer\":false}', '2026-04-05 01:17:48', 'completed', '2026-04-05 01:17:48', '2026-04-05 01:17:48'),
(104, 2, 35, NULL, 'expense', '🛒 Expense: Freshco Grocery', 145.11, 'EXP20260405002', 123.08, 268.19, 145.11, '{\"note\":\"Your share: $72.56 (paid by Sapna)\",\"expense_total\":\"217.67\",\"your_share\":72.56,\"participants\":3,\"debt_details\":[],\"is_payer\":false}', '2026-04-05 01:17:48', 'completed', '2026-04-05 01:17:48', '2026-04-05 01:17:48'),
(105, 3, 35, NULL, 'expense', '🛒 Expense: Freshco Grocery', -72.55, 'EXP20260405003', -128.18, -200.73, -72.55, '{\"note\":\"Your share: $72.56 (paid by Sapna)\",\"expense_total\":\"217.67\",\"your_share\":72.56,\"participants\":3,\"debt_details\":[\"\\ud83d\\udcb3 You now owe $156.33\"],\"is_payer\":false}', '2026-04-05 01:17:48', 'completed', '2026-04-05 01:17:48', '2026-04-05 01:17:48'),
(106, 1, 36, NULL, 'expense', '🛒 Expense: Freshco Grocery', 132.00, 'EXP20260414001', -67.46, 64.54, 132.00, '{\"note\":\"Your share: $66.00 (paid by Navjot)\",\"expense_total\":\"198.00\",\"your_share\":66,\"participants\":3,\"debt_details\":[],\"is_payer\":false}', '2026-04-14 12:46:44', 'completed', '2026-04-14 12:46:44', '2026-04-14 12:46:44'),
(107, 2, 36, NULL, 'expense', '🛒 Expense: Freshco Grocery', -66.00, 'EXP20260414002', 268.19, 202.19, -66.00, '{\"note\":\"Your share: $66.00 (paid by Navjot)\",\"expense_total\":\"198.00\",\"your_share\":66,\"participants\":3,\"debt_details\":[\"\\ud83d\\udcb0 Navjot owes you $45.86\"],\"is_payer\":false}', '2026-04-14 12:46:44', 'completed', '2026-04-14 12:46:44', '2026-04-14 12:46:44'),
(108, 3, 36, NULL, 'expense', '🛒 Expense: Freshco Grocery', -66.00, 'EXP20260414003', -200.73, -266.73, -66.00, '{\"note\":\"Your share: $66.00 (paid by Navjot)\",\"expense_total\":\"198.00\",\"your_share\":66,\"participants\":3,\"debt_details\":[\"\\ud83d\\udcb3 You now owe $110.40\"],\"is_payer\":false}', '2026-04-14 12:46:44', 'completed', '2026-04-14 12:46:44', '2026-04-14 12:46:44');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(191) NOT NULL,
  `password` varchar(191) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `password`, `is_active`, `remember_token`, `created_at`, `updated_at`) VALUES
(1, 'Navjot', '$2y$12$KLfXY8ab7Y7SFsaxHLCy0.qVvY53LzQcjkHQlueWI.6x8cG41BGgy', 1, NULL, '2025-09-16 08:17:03', '2025-09-16 08:17:03'),
(2, 'Sapna', '$2y$12$.fK8PWZ3B2NJh8ULs0iCp.ZbZ2VDF7JedUEI/hdgB/muDDQPagHpW', 1, NULL, '2025-09-16 08:17:03', '2025-09-16 08:17:03'),
(3, 'Anu', '$2y$12$ddX0GloP.2ytKxl2leHPCO8sn7qCkqRdKs/BoovNs8oPpeXM4ye0e', 1, NULL, '2025-09-16 08:17:03', '2025-09-16 08:17:03');

-- --------------------------------------------------------

--
-- Table structure for table `wallet_snapshots`
--

CREATE TABLE `wallet_snapshots` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `expense_id` bigint(20) UNSIGNED DEFAULT NULL,
  `settlement_id` bigint(20) UNSIGNED DEFAULT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `net_balance` decimal(10,2) NOT NULL,
  `owes_details` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`owes_details`)),
  `receives_details` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`receives_details`)),
  `snapshot_date` timestamp NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `balance_states`
--
ALTER TABLE `balance_states`
  ADD PRIMARY KEY (`id`),
  ADD KEY `balance_states_settlement_id_foreign` (`settlement_id`),
  ADD KEY `balance_states_expense_id_settlement_id_index` (`expense_id`,`settlement_id`);

--
-- Indexes for table `expenses`
--
ALTER TABLE `expenses`
  ADD PRIMARY KEY (`id`),
  ADD KEY `expenses_paid_by_user_id_foreign` (`paid_by_user_id`),
  ADD KEY `expenses_payback_to_user_id_foreign` (`payback_to_user_id`);

--
-- Indexes for table `expense_paybacks`
--
ALTER TABLE `expense_paybacks`
  ADD PRIMARY KEY (`id`),
  ADD KEY `expense_paybacks_expense_id_foreign` (`expense_id`),
  ADD KEY `expense_paybacks_payback_to_user_id_foreign` (`payback_to_user_id`);

--
-- Indexes for table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD PRIMARY KEY (`email`);

--
-- Indexes for table `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sessions_user_id_index` (`user_id`),
  ADD KEY `sessions_last_activity_index` (`last_activity`);

--
-- Indexes for table `settlements`
--
ALTER TABLE `settlements`
  ADD PRIMARY KEY (`id`),
  ADD KEY `settlements_from_user_id_foreign` (`from_user_id`),
  ADD KEY `settlements_to_user_id_foreign` (`to_user_id`);

--
-- Indexes for table `statement_records`
--
ALTER TABLE `statement_records`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `statement_records_reference_number_unique` (`reference_number`),
  ADD KEY `statement_records_expense_id_foreign` (`expense_id`),
  ADD KEY `statement_records_settlement_id_foreign` (`settlement_id`),
  ADD KEY `statement_records_user_id_transaction_date_index` (`user_id`,`transaction_date`),
  ADD KEY `statement_records_transaction_type_transaction_date_index` (`transaction_type`,`transaction_date`),
  ADD KEY `statement_records_reference_number_index` (`reference_number`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `wallet_snapshots`
--
ALTER TABLE `wallet_snapshots`
  ADD PRIMARY KEY (`id`),
  ADD KEY `wallet_snapshots_expense_id_foreign` (`expense_id`),
  ADD KEY `wallet_snapshots_settlement_id_foreign` (`settlement_id`),
  ADD KEY `wallet_snapshots_user_id_foreign` (`user_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `balance_states`
--
ALTER TABLE `balance_states`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `expenses`
--
ALTER TABLE `expenses`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=37;

--
-- AUTO_INCREMENT for table `expense_paybacks`
--
ALTER TABLE `expense_paybacks`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `settlements`
--
ALTER TABLE `settlements`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `statement_records`
--
ALTER TABLE `statement_records`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=109;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `wallet_snapshots`
--
ALTER TABLE `wallet_snapshots`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `balance_states`
--
ALTER TABLE `balance_states`
  ADD CONSTRAINT `balance_states_expense_id_foreign` FOREIGN KEY (`expense_id`) REFERENCES `expenses` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `balance_states_settlement_id_foreign` FOREIGN KEY (`settlement_id`) REFERENCES `settlements` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `expenses`
--
ALTER TABLE `expenses`
  ADD CONSTRAINT `expenses_paid_by_user_id_foreign` FOREIGN KEY (`paid_by_user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `expenses_payback_to_user_id_foreign` FOREIGN KEY (`payback_to_user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `expense_paybacks`
--
ALTER TABLE `expense_paybacks`
  ADD CONSTRAINT `expense_paybacks_expense_id_foreign` FOREIGN KEY (`expense_id`) REFERENCES `expenses` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `expense_paybacks_payback_to_user_id_foreign` FOREIGN KEY (`payback_to_user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `settlements`
--
ALTER TABLE `settlements`
  ADD CONSTRAINT `settlements_from_user_id_foreign` FOREIGN KEY (`from_user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `settlements_to_user_id_foreign` FOREIGN KEY (`to_user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `statement_records`
--
ALTER TABLE `statement_records`
  ADD CONSTRAINT `statement_records_expense_id_foreign` FOREIGN KEY (`expense_id`) REFERENCES `expenses` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `statement_records_settlement_id_foreign` FOREIGN KEY (`settlement_id`) REFERENCES `settlements` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `statement_records_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `wallet_snapshots`
--
ALTER TABLE `wallet_snapshots`
  ADD CONSTRAINT `wallet_snapshots_expense_id_foreign` FOREIGN KEY (`expense_id`) REFERENCES `expenses` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `wallet_snapshots_settlement_id_foreign` FOREIGN KEY (`settlement_id`) REFERENCES `settlements` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `wallet_snapshots_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
