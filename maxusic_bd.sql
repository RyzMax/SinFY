-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Хост: 127.0.0.1:3306
-- Время создания: Мар 09 2026 г., 18:34
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
(1, 8, 1, 'User', 'jjj', '2026-03-06 11:33:13', 1, NULL),
(2, 14, 1, 'User', 'ggg', '2026-03-06 11:55:57', 1, NULL),
(3, 8, 1, 'tozavi', 'xcvxcv', '2026-03-06 14:35:38', 1, NULL),
(6, 4, NULL, 'ку', 'куку', '2026-03-06 14:51:54', 0, NULL),
(8, 1, 1, 'tozavi', 'ываываы', '2026-03-06 14:59:42', 1, NULL),
(9, 14, 1, 'tozavi', 'asdasdas', '2026-03-06 17:00:55', 1, NULL),
(10, 9, NULL, 'Гость', 'ыыы', '2026-03-06 17:13:57', 1, NULL),
(11, 9, 1, 'tozavi', 'klkk', '2026-03-06 17:44:06', 1, NULL),
(14, 9, 1, 'tozavi', 'АВ', '2026-03-06 17:57:39', 1, 10),
(16, 9, 1, 'tozavi', 'фывфыв', '2026-03-06 17:57:47', 1, 11),
(17, 9, 1, 'tozavi', 'фывфывфывфыф', '2026-03-06 17:57:50', 1, 16),
(19, 9, 1, 'tozavi', 'dfsf', '2026-03-06 18:46:54', 1, 14),
(20, 9, 1, 'tozavi', 'ыфвфыв', '2026-03-06 18:48:49', 1, 14),
(21, 9, 1, 'tozavi', 'фывфыв', '2026-03-06 18:48:52', 1, 10),
(22, 9, 1, 'tozavi', 'asdasdasd', '2026-03-06 18:50:40', 1, 21),
(23, 14, 1, 'tozavi', 'шш', '2026-03-06 18:58:34', 1, 2),
(25, 14, 1, 'tozavi', 'das', '2026-03-06 19:09:00', 1, 23),
(27, 14, 1, 'tozavi', 'sadasd', '2026-03-06 19:15:25', 1, 2),
(28, 14, 1, 'tozavi', 'asdasd', '2026-03-06 19:15:31', 1, 9),
(31, 14, 1, 'tozavi', 'фывфыв', '2026-03-06 19:21:35', 1, 2),
(32, 14, 1, 'tozavi', 'ячсявыа', '2026-03-06 19:21:42', 1, NULL),
(33, 14, 1, 'tozavi', 'фывфывыв', '2026-03-06 19:21:47', 1, 32),
(34, 14, 1, 'tozavi', 'бь', '2026-03-06 19:22:43', 1, 9),
(35, 14, 1, 'tozavi', 'asdas', '2026-03-06 19:38:32', 1, 25),
(36, 14, 1, 'tozavi', 'asd', '2026-03-06 19:38:35', 1, 27),
(37, 14, 1, 'tozavi', 'asdad', '2026-03-06 19:38:42', 1, 31),
(38, 14, 1, 'tozavi', 'asdasda', '2026-03-06 19:38:49', 1, 31),
(39, 14, 1, 'tozavi', 'sadasds', '2026-03-06 19:38:56', 1, 33),
(40, 14, 1, 'tozavi', 'assdd', '2026-03-06 19:39:09', 1, 34),
(41, 14, 1, 'tozavi', 'asdf', '2026-03-06 19:39:17', 1, 9),
(42, 15, 1, 'tozavi', 'ыфвфыв', '2026-03-06 19:39:45', 1, NULL),
(43, 15, 1, 'tozavi', 'ыфвфыв', '2026-03-06 19:39:48', 1, 42),
(44, 15, 1, 'tozavi', 'фывфыв', '2026-03-06 19:39:53', 1, NULL),
(45, 15, 1, 'tozavi', 'ыфвфыв', '2026-03-06 19:41:22', 1, 43),
(46, 15, 1, 'tozavi', 'ЫВАЫВ', '2026-03-06 19:41:26', 1, NULL),
(49, 15, 1, 'tozavi', 'ВФЫВВФ', '2026-03-06 19:41:33', 1, 44),
(50, 15, 1, 'tozavi', 'фывфыв', '2026-03-06 19:41:51', 1, 46),
(52, 15, 1, 'tozavi', 'фывфыв', '2026-03-06 19:44:30', 1, 50),
(53, 15, 1, 'tozavi', 'фывфыв', '2026-03-06 19:44:32', 1, 52),
(54, 15, 1, 'tozavi', 'фывфы', '2026-03-06 19:44:35', 1, 53),
(56, 14, 2, 'kawij0', 'фывфыв', '2026-03-06 19:49:48', 1, 35),
(57, 15, 2, 'kawij0', 'вфыыфвфыв', '2026-03-06 19:52:23', 1, NULL),
(59, 15, 1, 'tozavi', 'ывф', '2026-03-06 20:11:09', 1, 52),
(60, 15, 1, 'tozavi', 'фыв', '2026-03-06 20:11:20', 1, 49),
(61, 15, 1, 'tozavi', 'фывф', '2026-03-06 20:11:23', 1, 43),
(62, 14, NULL, 'Tozavi', 'жжж', '2026-03-06 20:22:59', 1, NULL);

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
(7, 2, 1, '2026-03-09 18:26:21');

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
(10, 1, 9, '2026-03-09 15:15:03'),
(12, 1, 18, '2026-03-09 15:16:39'),
(14, 1, 17, '2026-03-09 15:26:03'),
(15, 2, 19, '2026-03-09 15:26:24'),
(16, 2, 18, '2026-03-09 15:26:26');

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

--
-- Дамп данных таблицы `reports`
--

INSERT INTO `reports` (`id`, `comment_id`, `user_id`, `reason`, `created_at`, `resolved`) VALUES
(6, 10, 2, NULL, '2026-03-06 17:14:20', 1),
(7, 10, 1, NULL, '2026-03-06 17:28:27', 1),
(8, 10, 1, NULL, '2026-03-06 17:28:28', 1),
(9, 10, 1, NULL, '2026-03-06 17:28:29', 1),
(10, 10, 1, NULL, '2026-03-06 17:28:29', 1),
(11, 10, 1, NULL, '2026-03-06 17:28:30', 1),
(12, 10, 1, NULL, '2026-03-06 17:28:30', 1),
(13, 10, 1, NULL, '2026-03-06 17:28:30', 1),
(14, 10, 1, NULL, '2026-03-06 17:28:31', 1),
(15, 10, 1, NULL, '2026-03-06 17:28:31', 1),
(16, 10, 1, NULL, '2026-03-06 17:28:32', 1),
(17, 57, 1, NULL, '2026-03-06 20:00:14', 1),
(18, 56, 1, NULL, '2026-03-06 20:21:46', 1);

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
(1, NULL, 'Milk', 'toz', 'sdadsdfsdf', 'uploads/audio/1772556272_69a70ff054797.mp3', 'uploads/covers/1772556272_69a70ff054a20.png', '2026-03-03 16:44:32', 10, 1, NULL, 0),
(3, NULL, 'Mmm', 'asdasd', 'sfsadfsadsfg', 'uploads/audio/1772557214_69a7139e00bbf.mp3', 'uploads/covers/1772557214_69a7139e00e69.png', '2026-03-03 17:00:14', 5, 1, NULL, 0),
(4, NULL, 'asdzaxdzasdass', 'sdfsdfsdf', 'fdffsdfdfdfgd', 'uploads/audio/1772558653_69a7193d4a971.aac', 'uploads/covers/1772558653_69a7193d4abb5.png', '2026-03-03 17:24:13', 12, 1, NULL, 0),
(8, 1, 'cdfgh', 'dfghdfgh', 'dfghdfghdhgfhd', 'uploads/audio/1_1772567725_69a73cad67679.mp3', 'uploads/covers/1_1772567725_69a73cad678cc.png', '2026-03-03 19:55:25', 88, 1, NULL, 0),
(9, 2, 'фывфыв', 'фывфывфыв', 'фывфывфывфыв', 'uploads/audio/2_1772568577_69a740011cb4d.mp3', 'uploads/covers/2_1772568577_69a740011cd13.png', '2026-03-03 20:09:37', 104, 1, NULL, 1),
(12, 1, 'ываываф', 'фывафываф', 'фывафывафываа', 'uploads/audio/1_1772653011_69a889d31ca3b.wav', 'uploads/covers/1_1772653011_69a889d31cc54.jpg', '2026-03-04 19:36:51', 18, 1, NULL, 0),
(13, 1, 'asdasdasd', 'asdsdasd', 'asdasdasd', 'uploads/audio/audio_69a88f99f22b3.mp3', 'uploads/covers/cover_69a88f99f271a.jpg', '2026-03-04 20:01:29', 28, 1, '[\"electronic\"]', 0),
(14, 1, 'asd', 'asdasasd', 'sdasdasdasd', 'uploads/audio/audio_69a8903a7dd8c.mp3', 'uploads/covers/cover_69a8903a7e102.jpg', '2026-03-04 20:04:10', 99, 1, '[\"metal\"]', 0),
(15, 1, 'вапвпа', 'вапрвапр', 'авпрваервапрва', 'uploads/audio/audio_69aae404a4222.mp3', 'uploads/covers/cover_69aae404a4453.png', '2026-03-06 14:26:12', 12, 1, '[\"rock\",\"pop\",\"hiphop\"]', 0),
(16, 1, 'dfgsdfg', 'sdfgsdfsdgs', '', 'uploads/audio/audio_69ad4eaf996b6.ogg', 'uploads/covers/cover_69ad4eaf999b5.png', '2026-03-08 10:25:51', 0, 1, '[\"rock\",\"rap\"]', 0),
(17, 1, 'тьт', 'ыфваы', 'фвафывафывафа', 'uploads/audio/audio_69ad6e85354d8.wav', 'uploads/covers/cover_69ad6e85357ee.jpg', '2026-03-08 12:41:41', 0, 1, '[\"rock\",\"jazz\"]', 1),
(18, 1, 'ууувапп', 'ввввв', 'вввв', 'uploads/audio/audio_69ada31aa6511.mp3', 'uploads/covers/cover_69ada31aa67de.png', '2026-03-08 16:26:02', 0, 1, '[\"rock\",\"jazz\",\"classical\"]', 1),
(19, 1, 'sss', 'ssss', 'sss', 'uploads/audio/audio_69ada3bee1159.mp3', 'uploads/covers/cover_69ada3bee13d0.png', '2026-03-08 16:28:46', 0, 1, '[\"rock\",\"pop\",\"hiphop\"]', 0),
(20, 2, 'ertgdrg', 'dfgdfgdf', 'gdfgdfgdf', 'uploads/audio/audio_69aee6ebce7cf.mp3', 'uploads/covers/cover_69aee6ebcebc2.png', '2026-03-09 15:27:39', 0, 1, '[\"rock\",\"jazz\",\"rap\"]', 0),
(21, 1, 'asdads', 'sfdsadafs', 'fasdfadfafaf', 'uploads/audio/audio_69aee7f9d3c79.mp3', 'uploads/covers/cover_69aee7f9d3fc6.png', '2026-03-09 15:32:09', 0, 1, '[\"jazz\",\"metal\"]', 0);

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
(3, 'sadasd@ddd', 'ttttttt', '$2y$10$y1CyfZd0ifTKPxWOIfl/t.s89eU4x/nJERS0Rxf1i7J9gsoFwtCaq', 'avatar.jpg', NULL, '2026-03-03 20:21:46', 0);

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
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=63;

--
-- AUTO_INCREMENT для таблицы `follows`
--
ALTER TABLE `follows`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT для таблицы `likes`
--
ALTER TABLE `likes`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT для таблицы `reports`
--
ALTER TABLE `reports`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT для таблицы `tracks`
--
ALTER TABLE `tracks`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT для таблицы `users`
--
ALTER TABLE `users`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

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
