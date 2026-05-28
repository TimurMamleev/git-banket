-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Хост: MySQL-5.7
-- Время создания: Апр 26 2026 г., 20:04
-- Версия сервера: 5.7.44
-- Версия PHP: 8.3.6

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- База данных: `database`
--

-- --------------------------------------------------------

--
-- Структура таблицы `cart` (корзина заказов на перевозку)
--

CREATE TABLE `cart` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `vehicle_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT '1',
  `route_id` int(11) DEFAULT NULL COMMENT 'Маршрут перевозки'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Структура таблицы `categories` (категории грузов)
--

CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `parent_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Дамп данных таблицы `categories`
--

INSERT INTO `categories` (`id`, `name`, `description`, `parent_id`) VALUES
(1, 'Стандартные грузы', 'Обычные товары, не требующие особых условий перевозки', NULL),
(2, 'Скоропортящиеся грузы', 'Продукты питания, медикаменты, требующие температурного режима', NULL),
(3, 'Опасные грузы', 'Химикаты, легковоспламеняющиеся вещества, требующие лицензии', NULL),
(4, 'Крупногабаритные грузы', 'Грузы с превышением стандартных размеров (негабарит)', NULL),
(5, 'Тяжеловесные грузы', 'Грузы весом более 20 тонн', NULL),
(6, 'Сборные грузы', 'Несколько заказов от разных клиентов в одном транспорте', NULL);

-- --------------------------------------------------------

--
-- Структура таблицы `routes` (маршруты перевозок)
--

CREATE TABLE `routes` (
  `id` int(11) NOT NULL,
  `name` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `from_city` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `to_city` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `from_country` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT 'Россия',
  `to_country` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT 'Россия',
  `distance_km` int(11) NOT NULL COMMENT 'Расстояние в км',
  `base_price` decimal(10,2) NOT NULL COMMENT 'Базовая цена перевозки',
  `is_active` tinyint(1) DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Дамп данных таблицы `routes`
--

INSERT INTO `routes` (`id`, `name`, `from_city`, `to_city`, `from_country`, `to_country`, `distance_km`, `base_price`, `is_active`) VALUES
(1, 'Москва - Санкт-Петербург', 'Москва', 'Санкт-Петербург', 'Россия', 'Россия', 710, 25000.00, 1),
(2, 'Москва - Казань', 'Москва', 'Казань', 'Россия', 'Россия', 820, 28000.00, 1),
(3, 'Москва - Екатеринбург', 'Москва', 'Екатеринбург', 'Россия', 'Россия', 1770, 55000.00, 1),
(4, 'Москва - Новосибирск', 'Москва', 'Новосибирск', 'Россия', 'Россия', 3350, 105000.00, 1),
(5, 'Санкт-Петербург - Минск', 'Санкт-Петербург', 'Минск', 'Россия', 'Беларусь', 780, 26000.00, 1),
(6, 'Москва - Алматы', 'Москва', 'Алматы', 'Россия', 'Казахстан', 3800, 120000.00, 1),
(7, 'Краснодар - Москва', 'Краснодар', 'Москва', 'Россия', 'Россия', 1350, 42000.00, 1);

-- --------------------------------------------------------

--
-- Структура таблицы `orders` (заказы на перевозку)
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `total_price` decimal(10,2) NOT NULL COMMENT 'Общая стоимость перевозки',
  `status` enum('new','processing','on_way','delivered','cancelled') COLLATE utf8mb4_unicode_ci DEFAULT 'new' COMMENT 'статус: новый, в обработке, в пути, доставлен, отменён',
  `address_from` text COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Адрес забора груза',
  `address_to` text COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Адрес доставки груза',
  `cargo_description` text COLLATE utf8mb4_unicode_ci COMMENT 'Описание груза',
  `cargo_weight` decimal(10,2) DEFAULT NULL COMMENT 'Вес груза в кг',
  `delivery_date` date DEFAULT NULL COMMENT 'Плановая дата доставки',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Дамп данных таблицы `orders`
--

INSERT INTO `orders` (`id`, `user_id`, `total_price`, `status`, `address_from`, `address_to`, `cargo_description`, `cargo_weight`, `delivery_date`, `created_at`) VALUES
(1, 1, 25000.00, 'delivered', 'г. Москва, ул. Тверская, 1', 'г. Санкт-Петербург, Невский пр-т, 10', 'Бытовая техника', 850.00, '2026-04-15', '2026-04-12 21:48:38'),
(2, 1, 55000.00, 'on_way', 'г. Москва, ул. Ленина, 15', 'г. Екатеринбург, ул. Малышева, 30', 'Строительные материалы', 3200.00, '2026-04-20', '2026-04-12 21:51:34');

-- --------------------------------------------------------

--
-- Структура таблицы `order_items` (позиции заказа — транспортные средства)
--

CREATE TABLE `order_items` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `vehicle_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT '1',
  `price` decimal(10,2) NOT NULL COMMENT 'Цена за единицу техники'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Дамп данных таблицы `order_items`
--

INSERT INTO `order_items` (`id`, `order_id`, `vehicle_id`, `quantity`, `price`) VALUES
(1, 1, 11, 1, 25000.00),
(2, 2, 13, 1, 55000.00);

-- --------------------------------------------------------

--
-- Структура таблицы `products` (переименована в `vehicles`, оставлена нетронутой)
-- ВНИМАНИЕ: Эта таблица была заменена на `vehicles` ниже
-- --------------------------------------------------------

--
-- Структура таблицы `vehicles` (транспортные средства) - ОСТАВЛЕНА НЕТРОНУТОЙ
--

CREATE TABLE `vehicles` (
  `id` int(11) NOT NULL,
  `name` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `price` decimal(10,2) NOT NULL COMMENT 'Тариф за рейс/км в рублях',
  `image` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `category_id` int(11) DEFAULT NULL,
  `stock` int(11) DEFAULT '0' COMMENT 'Количество машин в автопарке',
  `is_active` tinyint(1) DEFAULT '1',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Дамп данных таблицы `vehicles`
--

INSERT INTO `vehicles` (`id`, `name`, `description`, `price`, `image`, `category_id`, `stock`, `is_active`, `created_at`) VALUES
(11, 'Газель NEXT', 'Грузоподъёмность 1.5т, объём кузова 12м³. Подходит для мелких партий товара и городских перевозок.', 3500.00, NULL, 1, 24, 1, '2026-04-12 21:41:30'),
(12, 'Ford Transit', 'Грузоподъёмность 1.2т, объём 10м³. Маневренный фургон для доставки по городу.', 3000.00, NULL, 1, 15, 1, '2026-04-12 21:41:30'),
(13, 'MAN TGS', 'Грузоподъёмность 8т, объём 45м³. Тентованный полуприцеп для междугородних перевозок.', 12000.00, NULL, 2, 10, 1, '2026-04-12 21:41:30'),
(14, 'Mercedes Atego', 'Грузоподъёмность 6т, объём 32м³. Изотермический кузов для температурных грузов.', 10500.00, NULL, 2, 7, 1, '2026-04-12 21:41:30'),
(15, 'Scania R450', 'Грузоподъёмность 22т, объём 86м³. Еврофура для дальнемагистральных рейсов.', 25000.00, NULL, 3, 8, 1, '2026-04-12 21:41:30'),
(16, 'Автовоз', 'Для перевозки легковых автомобилей до 10 машин. Спецтехника с гидробортом и усиленной подвеской.', 35000.00, 'https://example.com/autotransport.jpg', 3, 4, 1, '2026-04-12 21:41:30'),
(17, 'Рефрижератор ThermoKing', 'Температурный режим от -25°С до +15°С. Грузоподъёмность 5т для скоропортящихся грузов.', 18000.00, NULL, 2, 6, 1, '2026-04-12 21:41:30'),
(18, 'Низкорамный трал', 'Для перевозки строительной техники до 40т. Длина платформы 14м, ширина 3м.', 45000.00, NULL, 4, 3, 1, '2026-04-12 21:41:30'),
(19, 'Контейнеровоз', 'Перевозка 20 и 40 футовых контейнеров грузоподъёмностью до 30т. Контейнеры фиксируются поворотами.', 42000.00, NULL, 4, 5, 1, '2026-04-12 21:41:30'),
(20, 'Платформа для негабарита', 'Длина платформы до 18м, ширина до 3.5м. Требуется сопровождение ГИБДД.', 58000.00, NULL, 4, 2, 1, '2026-04-12 21:41:30'),
(21, 'Грузовое такси', 'Ручная загрузка. До 800 кг для буксировки, переездов и мелких грузов. Работает по городу.', 2000.00, NULL, 1, 20, 1, '2026-04-12 22:13:40');

-- --------------------------------------------------------

--
-- Структура таблицы `vehicle_categories` (категории транспорта) - ОСТАВЛЕНА НЕТРОНУТОЙ
--

CREATE TABLE `vehicle_categories` (
  `id` int(11) NOT NULL,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Дамп данных таблицы `vehicle_categories`
--

INSERT INTO `vehicle_categories` (`id`, `name`, `description`) VALUES
(1, 'Маленькие грузовики', 'Грузоподъёмность до 2 тонн, для городских перевозок и мелких партий груза'),
(2, 'Средние грузовики', 'Грузоподъёмность от 2 до 10 тонн, для междугородних перевозок'),
(3, 'Большие грузовики', 'Грузоподъёмность от 10 до 25 тонн, магистральные перевозки'),
(4, 'Крупногабаритный транспорт', 'Спецтехника для негабаритных и тяжеловесных грузов');

-- --------------------------------------------------------

--
-- Структура таблицы `users` - ОСТАВЛЕНА НЕТРОНУТОЙ
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `login` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `full_name` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `role` enum('user','admin') COLLATE utf8mb4_unicode_ci DEFAULT 'user',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Дамп данных таблицы `users`
--

INSERT INTO `users` (`id`, `login`, `password`, `full_name`, `email`, `phone`, `role`, `created_at`) VALUES
(1, 'farengeit', '$2y$10$ZBIoU6uoL69fwMnMmSr4DeXwAXx935tAYyaTHZdAfrMD9lLKK6SAK', 'Пятайкин Иван Иванович', 'po_farengeity@mail.ru', '9061560170', 'user', '2026-04-12 21:45:14'),
(2, 'rumit', '$2y$10$/JT3j07pRnHl6fKaVbs0jeq8dNtln90pgMLk8xwNoFHq8KMhKJryO', 'мамлеев тимур сергеевич', 'hol@mail.ru', '7(987)589-75-34', 'user', '2026-04-26 19:36:10'),
(9, 'admin', '$2y$10$1SP.WIuRI70V4xmDa5vdc.VTcp2vBXMeJFpvNkbsQLBAzxmGfxpkS', 'administrator', 'admin@mail.ru', '+7(999)800-55-53', 'admin', '2026-04-26 20:03:14');

--
-- Индексы сохранённых таблиц
--

--
-- Индексы таблицы `cart`
--
ALTER TABLE `cart`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `vehicle_id` (`vehicle_id`),
  ADD KEY `route_id` (`route_id`);

--
-- Индексы таблицы `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`),
  ADD KEY `parent_id` (`parent_id`);

--
-- Индексы таблицы `routes`
--
ALTER TABLE `routes`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Индексы таблицы `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `vehicle_id` (`vehicle_id`);

--
-- Индексы таблицы `vehicles`
--
ALTER TABLE `vehicles`
  ADD PRIMARY KEY (`id`),
  ADD KEY `category_id` (`category_id`);

--
-- Индексы таблицы `vehicle_categories`
--
ALTER TABLE `vehicle_categories`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `login` (`login`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT для сохранённых таблиц
--

--
-- AUTO_INCREMENT для таблицы `cart`
--
ALTER TABLE `cart`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT для таблицы `routes`
--
ALTER TABLE `routes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT для таблицы `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT для таблицы `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT для таблицы `vehicles`
--
ALTER TABLE `vehicles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT для таблицы `vehicle_categories`
--
ALTER TABLE `vehicle_categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT для таблицы `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- Ограничения внешнего ключа сохраненных таблиц
--

--
-- Ограничения таблицы `cart`
--
ALTER TABLE `cart`
  ADD CONSTRAINT `cart_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `cart_ibfk_2` FOREIGN KEY (`vehicle_id`) REFERENCES `vehicles` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `cart_ibfk_3` FOREIGN KEY (`route_id`) REFERENCES `routes` (`id`) ON DELETE SET NULL;

--
-- Ограничения таблицы `categories`
--
ALTER TABLE `categories`
  ADD CONSTRAINT `categories_ibfk_1` FOREIGN KEY (`parent_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL;

--
-- Ограничения таблицы `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Ограничения таблицы `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`vehicle_id`) REFERENCES `vehicles` (`id`) ON DELETE CASCADE;

--
-- Ограничения таблицы `vehicles`
--
ALTER TABLE `vehicles`
  ADD CONSTRAINT `vehicles_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `vehicle_categories` (`id`) ON DELETE SET NULL;

COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;