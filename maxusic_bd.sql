-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Хост: 127.0.0.1:3306
-- Время создания: Мар 21 2026 г., 18:59
-- Версия сервера: 8.0.30
-- Версия PHP: 7.2.34

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- База данных: `maxusic_bd`
--

-- --------------------------------------------------------

--
-- Структура таблицы `comments`
--

CREATE TABLE `comments` (
  `id` int NOT NULL,
  `track_id` int NOT NULL,
  `user_id` int DEFAULT NULL,
  `username` varchar(100) NOT NULL,
  `comment_text` text NOT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `is_approved` tinyint DEFAULT '0',
  `parent_id` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Дамп данных таблицы `comments`
--

INSERT INTO `comments` (`id`, `track_id`, `user_id`, `username`, `comment_text`, `created_at`, `is_approved`, `parent_id`) VALUES
(63, 24, 1, 'tozavi', 'Как всегда имба', '2026-03-21 18:07:33', 1, NULL),
(64, 24, NULL, 'Гость', 'Ого', '2026-03-21 18:08:58', 1, 63),
(65, 24, NULL, 'Гость', 'вв', '2026-03-21 18:09:01', 1, 64),
(66, 24, NULL, 'Ратата', 'Имба', '2026-03-21 18:09:24', 1, NULL),
(67, 24, 2, 'kawij0', 'Согласен', '2026-03-21 18:12:48', 1, 63);

-- --------------------------------------------------------

--
-- Структура таблицы `follows`
--

CREATE TABLE `follows` (
  `id` int NOT NULL,
  `follower_id` int NOT NULL,
  `followed_id` int NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Дамп данных таблицы `follows`
--

INSERT INTO `follows` (`id`, `follower_id`, `followed_id`, `created_at`) VALUES
(6, 1, 2, '2026-03-06 20:22:16'),
(7, 2, 1, '2026-03-09 18:26:21'),
(8, 5, 1, '2026-03-21 18:06:23');

-- --------------------------------------------------------

--
-- Структура таблицы `likes`
--

CREATE TABLE `likes` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `track_id` int NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Дамп данных таблицы `likes`
--

INSERT INTO `likes` (`id`, `user_id`, `track_id`, `created_at`) VALUES
(20, 2, 23, '2026-03-21 14:33:41'),
(21, 1, 24, '2026-03-21 15:57:35');

-- --------------------------------------------------------

--
-- Структура таблицы `reports`
--

CREATE TABLE `reports` (
  `id` int NOT NULL,
  `comment_id` int NOT NULL,
  `user_id` int DEFAULT NULL,
  `reason` varchar(255) DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `resolved` tinyint(1) DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Структура таблицы `tracks`
--

CREATE TABLE `tracks` (
  `id` int NOT NULL,
  `user_id` int DEFAULT NULL,
  `title` varchar(255) NOT NULL,
  `author` varchar(255) NOT NULL,
  `description` text,
  `audio_path` varchar(500) DEFAULT NULL,
  `cover_path` varchar(500) DEFAULT NULL,
  `upload_date` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `plays` int DEFAULT '0',
  `is_approved` tinyint(1) NOT NULL DEFAULT '0',
  `genres` text,
  `likes_count` int DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Дамп данных таблицы `tracks`
--

INSERT INTO `tracks` (`id`, `user_id`, `title`, `author`, `description`, `audio_path`, `cover_path`, `upload_date`, `plays`, `is_approved`, `genres`, `likes_count`) VALUES
(23, 1, 'Rollin (Air Raid Venicle)', 'Limp Bizkit', '«Rollin’ (Air Raid Vehicle)» — песня группы Limp Bizkit, выпущенная в качестве третьего сингла вместе с «My Generation» 10 октября 2000 года с третьего студийного альбома Chocolate Starfish and the Hot Dog Flavored Water.\r\n\r\nСуществует ремикс «Rollin’ (Urban Assault Vehicle)» с участием хип-хоп исполнителей DMX, Method Man и Redman при создании Swizz Beatz. Он был саундтреком к фильму «Форсаж». Ремикс был также включён в треклист альбома.', 'uploads/audio/audio_69bd543a256f4.mp3', 'uploads/covers/cover_69bd543a26132.jpg', '2026-03-20 14:05:46', 0, 1, '[\"rock\"]', 0),
(24, 5, 'This ffire', 'Franz Ferdinand', '\"This Fire\" is a song by Scottish indie rock band Franz Ferdinand, the seventh track on their self-titled debut album. It was written by Alex Kapranos and Nick McCarthy and produced by the band themselves at their studio in Scotland during 2003. A new version of the song, produced by Rich Costey, was released as a single on 4 October 2004, titled \"This Fffire\". The single artwork is based on El Lissitzky\'s art work Beat the Whites with the Red Wedge', 'uploads/audio/audio_69beb3d827d3d.mp3', 'uploads/covers/cover_69beb3d82820e.jpg', '2026-03-21 15:06:00', 0, 1, '[\"rock\"]', 0);

-- --------------------------------------------------------

--
-- Структура таблицы `users`
--

CREATE TABLE `users` (
  `id` int NOT NULL,
  `email` varchar(255) NOT NULL,
  `login` varchar(50) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `avatar` varchar(255) DEFAULT 'avatar.jpg',
  `about` text,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `is_admin` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Дамп данных таблицы `users`
--

INSERT INTO `users` (`id`, `email`, `login`, `password_hash`, `avatar`, `about`, `created_at`, `is_admin`) VALUES
(1, 'msjdajsd@g.com', 'tozavi', '$2y$10$AZ4lgj899kt1KHyqY3kQ0u.oBjma7KQZ.G4b37zlMAk46iQ.MGw6u', 'uploads/avatars/1_1772565998.jpg', 'Вау', '2026-03-03 19:26:07', 1),
(2, 'nnn@g.com', 'kawij0', '$2y$10$W.zolxAXtdM0XWDekeYUae6ANxrVWFozs7GizwSGunhgaYT3zZ0mK', 'avatar.jpg', NULL, '2026-03-03 20:02:47', 0),
(3, 'sadasd@ddd', 'ttttttt', '$2y$10$y1CyfZd0ifTKPxWOIfl/t.s89eU4x/nJERS0Rxf1i7J9gsoFwtCaq', 'avatar.jpg', NULL, '2026-03-03 20:21:46', 0),
(5, 'masd@g.com', 'Kaiwai', '$2y$10$.lgNLiiMT8lOwijM2lxKSuHUJRhIVv.Gy.CZXCg1OhCwrKvoPEKO.', 'uploads/avatars/5_1774104891.jpg', '', '2026-03-21 14:34:05', 0);

--
-- Индексы сохранённых таблиц
--

--
-- Индексы таблицы `comments`
--
ALTER TABLE `comments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_track` (`track_id`),
  ADD KEY `idx_user` (`user_id`),
  ADD KEY `parent_id` (`parent_id`);

--
-- Индексы таблицы `follows`
--
ALTER TABLE `follows`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_follow` (`follower_id`,`followed_id`),
  ADD KEY `fk_followed` (`followed_id`);

--
-- Индексы таблицы `likes`
--
ALTER TABLE `likes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_like` (`user_id`,`track_id`),
  ADD KEY `track_user` (`track_id`,`user_id`);

--
-- Индексы таблицы `reports`
--
ALTER TABLE `reports`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_comment` (`comment_id`),
  ADD KEY `idx_user` (`user_id`);

--
-- Индексы таблицы `tracks`
--
ALTER TABLE `tracks`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Индексы таблицы `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `login` (`login`);

--
-- AUTO_INCREMENT для сохранённых таблиц
--

--
-- AUTO_INCREMENT для таблицы `comments`
--
ALTER TABLE `comments`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=68;

--
-- AUTO_INCREMENT для таблицы `follows`
--
ALTER TABLE `follows`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT для таблицы `likes`
--
ALTER TABLE `likes`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT для таблицы `reports`
--
ALTER TABLE `reports`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT для таблицы `tracks`
--
ALTER TABLE `tracks`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT для таблицы `users`
--
ALTER TABLE `users`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Ограничения внешнего ключа сохраненных таблиц
--

--
-- Ограничения внешнего ключа таблицы `comments`
--
ALTER TABLE `comments`
  ADD CONSTRAINT `comments_ibfk_1` FOREIGN KEY (`track_id`) REFERENCES `tracks` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `comments_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `comments_ibfk_3` FOREIGN KEY (`parent_id`) REFERENCES `comments` (`id`) ON DELETE CASCADE;

--
-- Ограничения внешнего ключа таблицы `follows`
--
ALTER TABLE `follows`
  ADD CONSTRAINT `fk_followed` FOREIGN KEY (`followed_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_follower` FOREIGN KEY (`follower_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Ограничения внешнего ключа таблицы `likes`
--
ALTER TABLE `likes`
  ADD CONSTRAINT `likes_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `likes_ibfk_2` FOREIGN KEY (`track_id`) REFERENCES `tracks` (`id`) ON DELETE CASCADE;

--
-- Ограничения внешнего ключа таблицы `reports`
--
ALTER TABLE `reports`
  ADD CONSTRAINT `fk_reports_comment` FOREIGN KEY (`comment_id`) REFERENCES `comments` (`id`) ON DELETE CASCADE;

--
-- Ограничения внешнего ключа таблицы `tracks`
--
ALTER TABLE `tracks`
  ADD CONSTRAINT `tracks_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
