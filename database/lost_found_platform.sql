-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Anamakine: localhost
-- Üretim Zamanı: 28 May 2025, 06:27:20
-- Sunucu sürümü: 10.4.28-MariaDB
-- PHP Sürümü: 8.0.28

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Veritabanı: `lost_found_platform`
--

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `items`
--

CREATE TABLE `items` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `title` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `location` varchar(255) DEFAULT NULL,
  `date_lost` date DEFAULT NULL,
  `image_path` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Tablo döküm verisi `items`
--

INSERT INTO `items` (`id`, `user_id`, `title`, `description`, `location`, `date_lost`, `image_path`, `created_at`) VALUES
(12, 14, 'Black Backpack', 'It&#039;s a Nike backpack, medium size, made of black nylon with silver zippers and three compartments. The backpack has a small Nike swoosh logo on the front and a slightly faded bottom corner from regular use. Inside were my university textbooks and a blue water bottle with stickers on it.', 'Central Park', '2025-05-25', 'uploads/1748405450_indir.jpeg', '2025-05-28 04:10:50'),
(13, 14, 'Black Backpack', 'It&#039;s a Nike backpack, medium size, made of black nylon with silver zippers and three compartments. The backpack has a small Nike swoosh logo on the front and a slightly faded bottom corner from regular use. Inside were my university textbooks and a blue water bottle with stickers on it.', 'Central Park', '2025-05-25', 'uploads/1748405654_indir.jpeg', '2025-05-28 04:14:14'),
(14, 14, 'jdfnj', 'dfjkdf', 'dfk', '7333-03-08', 'uploads/1748405768_airpods.jpg', '2025-05-28 04:16:08'),
(15, 14, 'dsd', '. It&#039;s a Nike backpack, medium size, made of black nylon with silver zippers and three compartments. The backpack has a small Nike swoosh logo on the front and a slightly faded bottom corner from regular use. Inside were my university textbooks and a blue water bottle with stickers on it.', 'Istanbul', '4333-03-31', 'uploads/1748406390_IMG_7176-scaled.jpg', '2025-05-28 04:26:30');

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `matches`
--

CREATE TABLE `matches` (
  `id` int(11) NOT NULL,
  `lost_item_id` int(11) DEFAULT NULL,
  `found_item_id` int(11) DEFAULT NULL,
  `requester_id` int(11) DEFAULT NULL,
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `chat_enabled` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `messages`
--

CREATE TABLE `messages` (
  `id` int(11) NOT NULL,
  `match_id` int(11) NOT NULL,
  `sender_id` int(11) NOT NULL,
  `message` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `message` text NOT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `reports`
--

CREATE TABLE `reports` (
  `id` int(11) NOT NULL,
  `reporter_id` int(11) NOT NULL,
  `reported_user_id` int(11) DEFAULT NULL,
  `match_id` int(11) DEFAULT NULL,
  `message` text NOT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `status` enum('pending','reviewed','resolved') DEFAULT 'pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `role` varchar(20) NOT NULL DEFAULT 'user',
  `created_at` datetime DEFAULT current_timestamp(),
  `profile_image` varchar(255) DEFAULT NULL,
  `gender` enum('male','female') NOT NULL DEFAULT 'male'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Tablo döküm verisi `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password`, `role`, `created_at`, `profile_image`, `gender`) VALUES
(2, 'Gizem Merve Demir', 'gizemervedemir@gmail.com', '$2y$10$bUB4FnkR0e/fXho6HCdMI.vEVDcDA5p3DdU7lWUjnupDmjjq.3i8i', 'user', '2025-05-26 21:03:04', NULL, 'male'),
(8, 'Admin', 'admin@test.com', '$2y$10$9DUDd2c2fX0o3n/MI0RX4u8205Fp/TQpILX2J7E9SoGDjufUnJEXm', 'admin', '2025-05-26 21:03:04', NULL, 'male'),
(9, 'Yunus Emre Açıkoğlu', 'yunusemre.acikoglu@std.yeditepe.edu.tr', '$2y$10$M6CN7D2XMNONJk1rWfHTguFUywmOAFZSqoShkoXSPYWfYAFfLBO.m', 'user', '2025-05-26 21:03:04', NULL, 'male'),
(10, 'Hatice Sena Ses', 'haticesena.ses@std.yeditepe.edu.tr', '$2y$10$S5DmAIWIsvS2r81fWyQvYeYGnYjrcsggrIDL1tZwW3Qw9Aaf7xELy', 'user', '2025-05-26 21:03:04', NULL, 'male'),
(11, 'Aysun Ay', 'aysun.ay@std.yeditepe.edu.tr', '$2y$10$EWdxkvtEgjDcoICq8eDEC.LWqo0b2u8DpLfoL4G1bZCv.ETNNmN9.', 'user', '2025-05-26 21:03:04', NULL, 'male'),
(12, 'Eylül Akboğa', 'eylul.akboga@std.yeditepe.edu.tr', '$2y$10$K73q3IaF8HbGNFvTEas6XeFFt8eamkY62f.cHFCFIY7Eivjy/sbJ2', 'user', '2025-05-26 21:03:04', NULL, 'male'),
(13, 'Eyüp Berk Özer', 'eyupberkozer@gmail.com', '$2y$10$uzJRcmF74RRo8WCUXgq/DOesxdhxt5iO6.qMXcVCcR9Zwr5.3oSIG', 'user', '2025-05-26 21:39:23', NULL, 'male'),
(14, 'Shannen Anthony', 'shannen_anthony@test.com', '$2y$10$K63jSbPbGPXGPiKJLzdhguaByQ4JFwETxdp3iqXY7OgUAkGmTY8BG', 'user', '2025-05-28 06:45:46', NULL, 'female');

--
-- Dökümü yapılmış tablolar için indeksler
--

--
-- Tablo için indeksler `items`
--
ALTER TABLE `items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Tablo için indeksler `matches`
--
ALTER TABLE `matches`
  ADD PRIMARY KEY (`id`),
  ADD KEY `found_item_id` (`found_item_id`),
  ADD KEY `fk_matches_lostitem` (`lost_item_id`),
  ADD KEY `fk_matches_user` (`requester_id`);

--
-- Tablo için indeksler `messages`
--
ALTER TABLE `messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `match_id` (`match_id`),
  ADD KEY `sender_id` (`sender_id`);

--
-- Tablo için indeksler `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`);

--
-- Tablo için indeksler `reports`
--
ALTER TABLE `reports`
  ADD PRIMARY KEY (`id`),
  ADD KEY `reporter_id` (`reporter_id`),
  ADD KEY `reported_user_id` (`reported_user_id`),
  ADD KEY `match_id` (`match_id`);

--
-- Tablo için indeksler `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- Dökümü yapılmış tablolar için AUTO_INCREMENT değeri
--

--
-- Tablo için AUTO_INCREMENT değeri `items`
--
ALTER TABLE `items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- Tablo için AUTO_INCREMENT değeri `matches`
--
ALTER TABLE `matches`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- Tablo için AUTO_INCREMENT değeri `messages`
--
ALTER TABLE `messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Tablo için AUTO_INCREMENT değeri `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Tablo için AUTO_INCREMENT değeri `reports`
--
ALTER TABLE `reports`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Tablo için AUTO_INCREMENT değeri `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- Dökümü yapılmış tablolar için kısıtlamalar
--

--
-- Tablo kısıtlamaları `items`
--
ALTER TABLE `items`
  ADD CONSTRAINT `items_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Tablo kısıtlamaları `matches`
--
ALTER TABLE `matches`
  ADD CONSTRAINT `fk_matches_item` FOREIGN KEY (`lost_item_id`) REFERENCES `items` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_matches_lostitem` FOREIGN KEY (`lost_item_id`) REFERENCES `items` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_matches_user` FOREIGN KEY (`requester_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `matches_ibfk_1` FOREIGN KEY (`lost_item_id`) REFERENCES `items` (`id`),
  ADD CONSTRAINT `matches_ibfk_2` FOREIGN KEY (`found_item_id`) REFERENCES `items` (`id`),
  ADD CONSTRAINT `matches_ibfk_3` FOREIGN KEY (`requester_id`) REFERENCES `users` (`id`);

--
-- Tablo kısıtlamaları `messages`
--
ALTER TABLE `messages`
  ADD CONSTRAINT `messages_ibfk_1` FOREIGN KEY (`match_id`) REFERENCES `matches` (`id`),
  ADD CONSTRAINT `messages_ibfk_2` FOREIGN KEY (`sender_id`) REFERENCES `users` (`id`);

--
-- Tablo kısıtlamaları `reports`
--
ALTER TABLE `reports`
  ADD CONSTRAINT `reports_ibfk_1` FOREIGN KEY (`reporter_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `reports_ibfk_2` FOREIGN KEY (`reported_user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `reports_ibfk_3` FOREIGN KEY (`match_id`) REFERENCES `matches` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
