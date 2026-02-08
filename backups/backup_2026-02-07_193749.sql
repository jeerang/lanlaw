-- LanLaw Database Backup
-- Generated: 2026-02-07 19:37:49
-- Tables: users, lawyers

SET FOREIGN_KEY_CHECKS=0;
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;

-- --------------------------------------------------------
-- Table structure for `users`
-- --------------------------------------------------------

DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `fullname` varchar(100) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `role` enum('admin','user') DEFAULT 'user',
  `status` enum('active','inactive') DEFAULT 'active',
  `last_login` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table `users`

INSERT INTO `users` (`id`, `username`, `password`, `fullname`, `email`, `role`, `status`, `last_login`, `created_at`, `updated_at`) VALUES ('1', 'admin', '$2y$10$74TGCHx8fzfdcsTBrhsha.PlbFbe8hHg88FimSZ8CR1z1Gh7cT3Jy', 'ผู้ดูแลระบบ', 'admin@lanlaw.com', 'admin', 'active', '2026-02-07 18:45:23', '2026-01-24 21:56:47', '2026-02-07 18:45:23');
INSERT INTO `users` (`id`, `username`, `password`, `fullname`, `email`, `role`, `status`, `last_login`, `created_at`, `updated_at`) VALUES ('2', 'ging', '$2y$10$VycKKyyNQzfUm2VydCFCqO0BVcgo0KojCWLInsKB2Nk/9EnpMikCq', 'ผู้ใช้งานทั่วไป', 'l.khadsurin@gmail.com', 'user', 'active', '2026-01-25 11:37:32', '2026-01-24 21:56:47', '2026-02-07 19:24:42');

-- --------------------------------------------------------
-- Table structure for `lawyers`
-- --------------------------------------------------------

DROP TABLE IF EXISTS `lawyers`;
CREATE TABLE `lawyers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `code` varchar(20) NOT NULL,
  `prefix` varchar(20) DEFAULT NULL,
  `firstname` varchar(100) NOT NULL,
  `lastname` varchar(100) NOT NULL,
  `license_number` varchar(50) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table `lawyers`

INSERT INTO `lawyers` (`id`, `code`, `prefix`, `firstname`, `lastname`, `license_number`, `phone`, `email`, `status`, `created_at`, `updated_at`) VALUES ('3', 'LAW03', 'นางสาว', 'ลัลฌา', 'ขัดสุรินทร์', 'ท.12347', '081-8840682', 'l.khadsurin@gmail.com', 'active', '2026-01-24 21:56:47', '2026-02-07 19:37:38');

SET FOREIGN_KEY_CHECKS=1;
COMMIT;
