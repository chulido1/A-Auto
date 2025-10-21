-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Хост: 127.0.0.1
-- Время создания: Окт 21 2025 г., 12:33
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
-- База данных: `a_auto`
--

-- --------------------------------------------------------

--
-- Структура таблицы `brigades`
--

CREATE TABLE `brigades` (
  `id` int(10) UNSIGNED NOT NULL,
  `section_id` int(10) UNSIGNED NOT NULL,
  `name` varchar(120) NOT NULL,
  `foreman_employee_id` int(10) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Дамп данных таблицы `brigades`
--

INSERT INTO `brigades` (`id`, `section_id`, `name`, `foreman_employee_id`) VALUES
(1, 2, 'Бригада №1', NULL),
(2, 1, 'Бригада №2', NULL),
(3, 2, 'Бригада №3', 1),
(4, 3, 'Бригада №1', 3);

-- --------------------------------------------------------

--
-- Структура таблицы `categories`
--

CREATE TABLE `categories` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(100) NOT NULL,
  `attributes_json` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`attributes_json`))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Дамп данных таблицы `categories`
--

INSERT INTO `categories` (`id`, `name`, `attributes_json`) VALUES
(1, 'Автобусы', '{\"вместимость\": \"int\", \"дверей\": \"int\", \"топливо\": \"string\"}'),
(2, 'Грузовые автомобили', '{\"грузоподъёмность_т\": \"float\", \"оси\": \"int\", \"тип_кузова\": \"string\"}'),
(4, 'Легковые автомобили', '{\"тип кузова\": \"string\", \"двигатель\": \"string\", \"привод\": \"string\", \"мест\": \"int\"}'),
(5, 'Коммерческий транспорт', '{\"тип кузова\": \"string\", \"объем_кузова_м3\": \"float\", \"топливо\": \"string\"}'),
(6, 'Прототипы и опытные образцы', '{\"назначение\": \"string\", \"этап испытаний\": \"string\"}');

-- --------------------------------------------------------

--
-- Структура таблицы `departments`
--

CREATE TABLE `departments` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(120) NOT NULL,
  `chief` varchar(100) DEFAULT NULL,
  `description` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Дамп данных таблицы `departments`
--

INSERT INTO `departments` (`id`, `name`, `chief`, `description`) VALUES
(1, 'Сборочный цех', '', ''),
(2, 'Кузовной цех', NULL, NULL);

-- --------------------------------------------------------

--
-- Структура таблицы `employees`
--

CREATE TABLE `employees` (
  `id` int(10) UNSIGNED NOT NULL,
  `last_name` varchar(80) NOT NULL,
  `first_name` varchar(80) NOT NULL,
  `patronymic` varchar(80) DEFAULT NULL,
  `position` varchar(120) NOT NULL,
  `category` enum('ИТП','Рабочий') NOT NULL,
  `profession` varchar(120) DEFAULT NULL,
  `grade` varchar(32) DEFAULT NULL,
  `experience_years` tinyint(3) UNSIGNED DEFAULT NULL,
  `phone` varchar(32) DEFAULT NULL,
  `email` varchar(120) DEFAULT NULL,
  `department_id` int(10) UNSIGNED DEFAULT NULL,
  `section_id` int(10) UNSIGNED DEFAULT NULL,
  `brigade_id` int(10) UNSIGNED DEFAULT NULL,
  `hire_date` date DEFAULT NULL,
  `status` enum('работает','уволен') NOT NULL DEFAULT 'работает',
  `user_id` int(10) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Дамп данных таблицы `employees`
--

INSERT INTO `employees` (`id`, `last_name`, `first_name`, `patronymic`, `position`, `category`, `profession`, `grade`, `experience_years`, `phone`, `email`, `department_id`, `section_id`, `brigade_id`, `hire_date`, `status`, `user_id`) VALUES
(1, 'Иванов', 'Иван', 'Сергеевич', 'Сборщик', 'Рабочий', 'Слесарь', '5', 3, '+7 900 000-00-00', 'andreev@example.com', 2, 3, 4, '2024-09-01', 'работает', NULL),
(3, 'Антонов', 'Антон', 'Антонович', 'Сборщик', 'ИТП', 'Сварщик', '3', 23, '+7 (555) 555-55-55', 'jsfuypig@firstmailler.net', 1, 3, 4, '2000-10-06', 'работает', NULL);

-- --------------------------------------------------------

--
-- Структура таблицы `labs`
--

CREATE TABLE `labs` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(150) NOT NULL,
  `description` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Дамп данных таблицы `labs`
--

INSERT INTO `labs` (`id`, `name`, `description`) VALUES
(1, 'Лаборатория прочности', 'Проводит испытания кузовов и рам автомобилей на устойчивость к нагрузкам.'),
(2, 'Электротехническая лаборатория', 'Тестирование электросистем, аккумуляторов и генераторов.'),
(3, 'Климатическая лаборатория', 'Проверяет работу изделий в экстремальных условиях — мороз, жара, влажность.');

-- --------------------------------------------------------

--
-- Структура таблицы `lab_equipment`
--

CREATE TABLE `lab_equipment` (
  `id` int(10) UNSIGNED NOT NULL,
  `lab_id` int(10) UNSIGNED NOT NULL,
  `name` varchar(150) NOT NULL,
  `type` varchar(120) DEFAULT NULL,
  `serial` varchar(120) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Дамп данных таблицы `lab_equipment`
--

INSERT INTO `lab_equipment` (`id`, `lab_id`, `name`, `type`, `serial`) VALUES
(1, 1, 'Стенд растяжения', 'Механический', 'ST-1001'),
(2, 1, 'Пресс гидравлический', 'Гидравлический', 'PR-2045'),
(3, 2, 'Измеритель сопротивления', 'Электронный', 'EL-3321'),
(4, 2, 'Блок питания', 'Электрический', 'BP-4411'),
(5, 3, 'Климатическая камера', 'Термостатическая', 'CC-5589'),
(6, 3, 'Измеритель влажности', 'Электронный', 'HM-6622');

-- --------------------------------------------------------

--
-- Структура таблицы `products`
--

CREATE TABLE `products` (
  `id` int(10) UNSIGNED NOT NULL,
  `model_name` varchar(150) NOT NULL,
  `category_id` int(10) UNSIGNED NOT NULL,
  `serial_number` varchar(100) DEFAULT NULL,
  `start_date` date DEFAULT NULL,
  `department_id` int(10) UNSIGNED DEFAULT NULL,
  `current_section_id` int(10) UNSIGNED DEFAULT NULL,
  `status` enum('в производстве','на испытаниях','ожидает проверки','передано на упаковку','готово к отправке','приостановлено','отменено') NOT NULL DEFAULT 'в производстве',
  `quantity` int(10) UNSIGNED DEFAULT NULL,
  `attributes_json` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`attributes_json`)),
  `notes` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Дамп данных таблицы `products`
--

INSERT INTO `products` (`id`, `model_name`, `category_id`, `serial_number`, `start_date`, `department_id`, `current_section_id`, `status`, `quantity`, `attributes_json`, `notes`) VALUES
(1, 'AeroCar X1', 1, 'X1-2024-001', '2025-01-15', 2, 3, 'в производстве', 5, '{\"color\":\"red\",\"engine\":\"V8\"}', 'Серия тестовых образцов'),
(2, 'AeroCar X2', 1, 'X2-2024-004', '2025-02-10', 2, 3, 'готово к отправке', 3, '{\"color\":\"blue\",\"engine\":\"V6\"}', 'Прошло финальную сборку'),
(3, 'AeroTruck T5', 1, 'T5-2025-008', '2025-03-20', 2, 3, 'на испытаниях', 2, '{\"color\":\"gray\",\"capacity\":\"5t\"}', 'Ожидает проверки лаборатории'),
(4, 'JetBike Z1', 2, 'Z1-2025-012', '2025-04-05', 1, 1, 'в производстве', 10, '{\"color\":\"black\",\"engine\":\"ECO\"}', 'Сборка продолжается'),
(5, 'JetBike Z2', 2, 'Z2-2025-013', '2025-04-20', 1, 1, 'передано на упаковку', 7, '{\"color\":\"white\",\"engine\":\"Turbo\"}', 'Передано на упаковку'),
(6, 'Speedster S9', 4, 'S9-2025-019', '2025-05-01', 2, 3, 'на испытаниях', 4, '{\"color\":\"yellow\",\"drive\":\"4x4\"}', 'Готово к отправке'),
(10, 'BMW', 4, '57DFS8DF9D', NULL, 1, 1, 'готово к отправке', 1001, NULL, '');

-- --------------------------------------------------------

--
-- Структура таблицы `product_routes`
--

CREATE TABLE `product_routes` (
  `id` int(10) UNSIGNED NOT NULL,
  `product_id` int(10) UNSIGNED NOT NULL,
  `section_id` int(10) UNSIGNED NOT NULL,
  `brigade_id` int(10) UNSIGNED DEFAULT NULL,
  `start_datetime` datetime NOT NULL,
  `end_datetime` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Дамп данных таблицы `product_routes`
--

INSERT INTO `product_routes` (`id`, `product_id`, `section_id`, `brigade_id`, `start_datetime`, `end_datetime`) VALUES
(1, 1, 1, 1, '2025-01-15 08:00:00', '2025-01-16 18:00:00'),
(2, 1, 2, 2, '2025-01-17 08:00:00', '2025-01-18 18:00:00');

-- --------------------------------------------------------

--
-- Структура таблицы `roles`
--

CREATE TABLE `roles` (
  `id` tinyint(3) UNSIGNED NOT NULL,
  `code` varchar(32) NOT NULL,
  `name` varchar(64) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Дамп данных таблицы `roles`
--

INSERT INTO `roles` (`id`, `code`, `name`) VALUES
(1, 'admin', 'Администратор'),
(2, 'hr', 'Отдел кадров'),
(3, 'itp', 'Инженерно‑технический персонал'),
(4, 'worker', 'Рабочий');

-- --------------------------------------------------------

--
-- Структура таблицы `sections`
--

CREATE TABLE `sections` (
  `id` int(10) UNSIGNED NOT NULL,
  `department_id` int(10) UNSIGNED NOT NULL,
  `name` varchar(120) NOT NULL,
  `supervisor` varchar(100) DEFAULT NULL,
  `description` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Дамп данных таблицы `sections`
--

INSERT INTO `sections` (`id`, `department_id`, `name`, `supervisor`, `description`) VALUES
(1, 1, 'Участок финальной сборки', NULL, ''),
(2, 1, 'Участок шасси', NULL, NULL),
(3, 2, 'Участок сварки кузовов', NULL, NULL);

-- --------------------------------------------------------

--
-- Структура таблицы `tests`
--

CREATE TABLE `tests` (
  `id` int(10) UNSIGNED NOT NULL,
  `product_id` int(10) UNSIGNED NOT NULL,
  `lab_id` int(10) UNSIGNED NOT NULL,
  `performed_by_employee_id` int(10) UNSIGNED DEFAULT NULL,
  `test_date` date NOT NULL,
  `result` enum('pending','passed','failed') NOT NULL DEFAULT 'pending',
  `protocol_path` varchar(255) DEFAULT NULL,
  `comments` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Дамп данных таблицы `tests`
--

INSERT INTO `tests` (`id`, `product_id`, `lab_id`, `performed_by_employee_id`, `test_date`, `result`, `protocol_path`, `comments`) VALUES
(1, 1, 1, 1, '2025-01-20', 'pending', '/files/protocols/P-0001.pdf', 'Финальные испытания завершены успешно'),
(2, 1, 2, 1, '2025-10-18', 'failed', '/files/protocols/P-0002.pdf', 'Финальные испытания завершены успешно');

-- --------------------------------------------------------

--
-- Структура таблицы `users`
--

CREATE TABLE `users` (
  `id` int(10) UNSIGNED NOT NULL,
  `login` varchar(64) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `role_id` tinyint(3) UNSIGNED NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Дамп данных таблицы `users`
--

INSERT INTO `users` (`id`, `login`, `password_hash`, `role_id`, `is_active`, `created_at`) VALUES
(10, 'admin', '$2y$10$LA7VN74MNUxwYa1NYehMsuTmi2jpLKtUrgFZnt3XA5DUjBtToXtO6', 1, 1, '2025-10-18 13:57:46'),
(12, 'worker', '$2y$10$JDupUuJF6zpIVx6pDJzNeurr1OLLLHXgiDpmZleYqrqhoShtwnXbW', 3, 1, '2025-10-18 15:22:33'),
(13, 'engineer', '$2y$10$7M11Y.2R74VxzM8bRUbf/ungkayJ24CV9NFhXLBwjNOKdfAh.waze', 2, 1, '2025-10-18 15:25:43');

--
-- Индексы сохранённых таблиц
--

--
-- Индексы таблицы `brigades`
--
ALTER TABLE `brigades`
  ADD PRIMARY KEY (`id`),
  ADD KEY `section_id` (`section_id`),
  ADD KEY `fk_brigades_foreman` (`foreman_employee_id`);

--
-- Индексы таблицы `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Индексы таблицы `departments`
--
ALTER TABLE `departments`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `employees`
--
ALTER TABLE `employees`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_employees_user` (`user_id`),
  ADD KEY `department_id` (`department_id`),
  ADD KEY `section_id` (`section_id`),
  ADD KEY `brigade_id` (`brigade_id`),
  ADD KEY `last_name` (`last_name`,`first_name`),
  ADD KEY `profession` (`profession`);

--
-- Индексы таблицы `labs`
--
ALTER TABLE `labs`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `lab_equipment`
--
ALTER TABLE `lab_equipment`
  ADD PRIMARY KEY (`id`),
  ADD KEY `lab_id` (`lab_id`);

--
-- Индексы таблицы `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD KEY `category_id` (`category_id`),
  ADD KEY `department_id` (`department_id`),
  ADD KEY `current_section_id` (`current_section_id`),
  ADD KEY `model_name` (`model_name`),
  ADD KEY `serial_number` (`serial_number`),
  ADD KEY `status` (`status`);

--
-- Индексы таблицы `product_routes`
--
ALTER TABLE `product_routes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `product_id` (`product_id`),
  ADD KEY `section_id` (`section_id`),
  ADD KEY `brigade_id` (`brigade_id`);

--
-- Индексы таблицы `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `code` (`code`);

--
-- Индексы таблицы `sections`
--
ALTER TABLE `sections`
  ADD PRIMARY KEY (`id`),
  ADD KEY `department_id` (`department_id`);

--
-- Индексы таблицы `tests`
--
ALTER TABLE `tests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_tests_employee` (`performed_by_employee_id`),
  ADD KEY `product_id` (`product_id`),
  ADD KEY `lab_id` (`lab_id`),
  ADD KEY `test_date` (`test_date`),
  ADD KEY `result` (`result`);

--
-- Индексы таблицы `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `login` (`login`),
  ADD KEY `fk_users_role` (`role_id`);

--
-- AUTO_INCREMENT для сохранённых таблиц
--

--
-- AUTO_INCREMENT для таблицы `brigades`
--
ALTER TABLE `brigades`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT для таблицы `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT для таблицы `departments`
--
ALTER TABLE `departments`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT для таблицы `employees`
--
ALTER TABLE `employees`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT для таблицы `labs`
--
ALTER TABLE `labs`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT для таблицы `lab_equipment`
--
ALTER TABLE `lab_equipment`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT для таблицы `products`
--
ALTER TABLE `products`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT для таблицы `product_routes`
--
ALTER TABLE `product_routes`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT для таблицы `roles`
--
ALTER TABLE `roles`
  MODIFY `id` tinyint(3) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT для таблицы `sections`
--
ALTER TABLE `sections`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT для таблицы `tests`
--
ALTER TABLE `tests`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT для таблицы `users`
--
ALTER TABLE `users`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- Ограничения внешнего ключа сохраненных таблиц
--

--
-- Ограничения внешнего ключа таблицы `brigades`
--
ALTER TABLE `brigades`
  ADD CONSTRAINT `fk_brigades_foreman` FOREIGN KEY (`foreman_employee_id`) REFERENCES `employees` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_brigades_section` FOREIGN KEY (`section_id`) REFERENCES `sections` (`id`) ON UPDATE CASCADE;

--
-- Ограничения внешнего ключа таблицы `employees`
--
ALTER TABLE `employees`
  ADD CONSTRAINT `fk_employees_brigade` FOREIGN KEY (`brigade_id`) REFERENCES `brigades` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_employees_department` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_employees_section` FOREIGN KEY (`section_id`) REFERENCES `sections` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_employees_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Ограничения внешнего ключа таблицы `lab_equipment`
--
ALTER TABLE `lab_equipment`
  ADD CONSTRAINT `fk_equipment_lab` FOREIGN KEY (`lab_id`) REFERENCES `labs` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ограничения внешнего ключа таблицы `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `fk_products_category` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_products_department` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_products_section` FOREIGN KEY (`current_section_id`) REFERENCES `sections` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Ограничения внешнего ключа таблицы `product_routes`
--
ALTER TABLE `product_routes`
  ADD CONSTRAINT `fk_routes_brigade` FOREIGN KEY (`brigade_id`) REFERENCES `brigades` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_routes_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_routes_section` FOREIGN KEY (`section_id`) REFERENCES `sections` (`id`) ON UPDATE CASCADE;

--
-- Ограничения внешнего ключа таблицы `sections`
--
ALTER TABLE `sections`
  ADD CONSTRAINT `fk_sections_department` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON UPDATE CASCADE;

--
-- Ограничения внешнего ключа таблицы `tests`
--
ALTER TABLE `tests`
  ADD CONSTRAINT `fk_tests_employee` FOREIGN KEY (`performed_by_employee_id`) REFERENCES `employees` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_tests_lab` FOREIGN KEY (`lab_id`) REFERENCES `labs` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_tests_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ограничения внешнего ключа таблицы `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `fk_users_role` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
