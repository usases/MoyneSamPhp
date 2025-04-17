-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Хост: 127.0.0.1
-- Время создания: Апр 18 2025 г., 00:49
-- Версия сервера: 10.4.32-MariaDB
-- Версия PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- База данных: `moyne_sam`
--

-- --------------------------------------------------------

--
-- Структура таблицы `service_requests`
--

CREATE TABLE `service_requests` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `service_type_id` int(11) NOT NULL,
  `address` text NOT NULL,
  `contact_info` text NOT NULL,
  `date_time` datetime NOT NULL,
  `payment_type` enum('cash','card','online') NOT NULL DEFAULT 'cash',
  `status` enum('new','in_progress','completed','cancelled') NOT NULL DEFAULT 'new',
  `cancellation_reason` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Дамп данных таблицы `service_requests`
--

INSERT INTO `service_requests` (`id`, `user_id`, `service_type_id`, `address`, `contact_info`, `date_time`, `payment_type`, `status`, `cancellation_reason`, `created_at`, `updated_at`) VALUES
(9, 4, 4, '123', '123', '2025-04-18 03:48:00', 'cash', 'new', NULL, '2025-04-17 22:46:58', '2025-04-17 22:46:58');

-- --------------------------------------------------------

--
-- Структура таблицы `service_types`
--

CREATE TABLE `service_types` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Дамп данных таблицы `service_types`
--

INSERT INTO `service_types` (`id`, `name`, `description`, `price`) VALUES
(4, 'Уборка', 'Уборка', 1000.00);

-- --------------------------------------------------------

--
-- Структура таблицы `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `full_name` varchar(255) NOT NULL COMMENT 'ФИО',
  `phone` varchar(20) NOT NULL COMMENT 'Телефон',
  `email` varchar(255) NOT NULL COMMENT 'Email',
  `login` varchar(50) NOT NULL COMMENT 'Логин',
  `password` varchar(255) NOT NULL COMMENT 'Пароль',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp() COMMENT 'Дата регистрации',
  `role` varchar(20) NOT NULL DEFAULT 'user' COMMENT 'Роль пользователя (user/admin)'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Дамп данных таблицы `users`
--

INSERT INTO `users` (`id`, `full_name`, `phone`, `email`, `login`, `password`, `created_at`, `role`) VALUES
(4, 'Ыавва Аваав Яавва', '+7 (999) 999 99 99', 'usases@mail.ru', 'usases', '$2y$10$gxWHcmxVXys60xBnmavZR.JDY8ZgAGkxdXH.rsC3t3KbVjbbGNCVS', '2025-04-17 22:45:32', 'admin');

--
-- Индексы сохранённых таблиц
--

--
-- Индексы таблицы `service_requests`
--
ALTER TABLE `service_requests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `service_type_id` (`service_type_id`);

--
-- Индексы таблицы `service_types`
--
ALTER TABLE `service_types`
  ADD PRIMARY KEY (`id`);

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
-- AUTO_INCREMENT для таблицы `service_requests`
--
ALTER TABLE `service_requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT для таблицы `service_types`
--
ALTER TABLE `service_types`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT для таблицы `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Ограничения внешнего ключа сохраненных таблиц
--

--
-- Ограничения внешнего ключа таблицы `service_requests`
--
ALTER TABLE `service_requests`
  ADD CONSTRAINT `service_requests_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `service_requests_ibfk_2` FOREIGN KEY (`service_type_id`) REFERENCES `service_types` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
