-- phpMyAdmin SQL Dump
-- version 5.0.4
-- https://www.phpmyadmin.net/
--
-- Хост: 127.0.0.1:3306
-- Время создания: Июн 10 2025 г., 12:37
-- Версия сервера: 8.0.19
-- Версия PHP: 7.1.33

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- База данных: `girlfeeria`
--

-- --------------------------------------------------------

--
-- Структура таблицы `bryuki`
--

CREATE TABLE `bryuki` (
  `id` int NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `image_path` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `color` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'не указан',
  `quantity` int NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Дамп данных таблицы `bryuki`
--

INSERT INTO `bryuki` (`id`, `name`, `price`, `image_path`, `description`, `color`, `quantity`) VALUES
(1, 'Розовые брюки с бантиками', '3500.00', 'брюки\\1b.jpg', 'Яркие розовые брюки- добавьте цвета в свой гардероб. Стильный и смелый выбор для создания запоминающегося образа. Комфортная посадка и актуальный дизайн.', 'Розовый', 15),
(2, 'Яркие брюки желтого цвета', '2400.00', 'брюки\\2b.jpg', 'Солнечные широкие желтые брюки. Изготовлены из легкой дышащей ткани,обеспечивающей комфорт в течение всего дня. Яркий желтый цвет привлекает внимание и поднимает настроение. Свободный крой не сковывает движения. ', 'Желтый', 14),
(3, 'Широкие розовые брюки в клетку', '2800.00', 'брюки\\3b.jpg', 'Очаровательные розовые широкие брюки в клетку с зайчиками.Этот необычный принт не оставит вас незамеченными. Свободный крой и яркий цвет. Создают неповторимый стильный образ. Идеально подходят для тех, кто любит выделяться из толпы.', 'Розовый', 23),
(4, 'Черные брюки-клеш', '3000.00', 'брюки\\5b.jpg', 'Классические черные брюки-клеш - вневременная элегантность и безупречный стиль. Высокое качество ткани и безупречный крой подчеркнут вашу фигуру и добавят изюминку любому образу.', 'Черный', 22),
(5, 'Бежевые прямые брюки', '3300.00', 'брюки\\4b.jpg', 'Классические бежевые прямые брюки - основа стильного гардероба. Универсальный крой, благородный цвет и безупречный комфорт. Прямой крой визуально вытягивает фигуру, а нейтральный бежевый легко сочетается с другими элементами гардероба. Идеальный выбор для офиса, деловых встреч и повседневной носки.', 'Бежевый', 31);

-- --------------------------------------------------------

--
-- Структура таблицы `cart`
--

CREATE TABLE `cart` (
  `id` int NOT NULL,
  `session_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `product_id` int NOT NULL,
  `category` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `image_path` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `quantity` int NOT NULL DEFAULT '1',
  `status` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'В корзине',
  `added_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Дамп данных таблицы `cart`
--

INSERT INTO `cart` (`id`, `session_id`, `product_id`, `category`, `name`, `image_path`, `quantity`, `status`, `added_at`) VALUES
(9, '7v6ro0p5862rhj1g3oif1p0pvamsg2a2', 2, 'kofty', 'Бордовая кофта с бантами', 'кофты\\2k.jpg', 1, 'Доставлен', '2025-05-13 08:52:14'),
(10, '7v6ro0p5862rhj1g3oif1p0pvamsg2a2', 4, 'kofty', 'Бордовая кофта- свитер', 'кофты\\4k.jpg', 1, 'Отправлен', '2025-05-13 08:52:18'),
(41, 'b67e3vaiq33be0oj3l8up9l5l2t5mq69', 3, 'kofty', 'Розовая вязанная кофта ', 'кофты\\3k.jpg', 3, 'В корзине', '2025-05-20 08:14:22'),
(43, 'b67e3vaiq33be0oj3l8up9l5l2t5mq69', 4, 'kofty', 'Бордовая кофта- свитер', 'кофты\\4k.jpg', 1, 'В корзине', '2025-05-20 08:20:17'),
(47, 'ct71o9ijs1eeag6sauhuisg6oktrh601', 3, 'kofty', 'Розовая вязанная кофта ', 'кофты\\3k.jpg', 3, 'В корзине', '2025-06-04 07:44:27'),
(48, 'ct71o9ijs1eeag6sauhuisg6oktrh601', 1, 'futbolki', 'Футболка \"Cute citten\"', 'футболки/1f.jpg', 1, 'В корзине', '2025-06-04 07:56:24'),
(49, 'ct71o9ijs1eeag6sauhuisg6oktrh601', 2, 'futbolki', 'Футболка \"NewYork\"', 'футболки/2f.jpg', 1, 'В корзине', '2025-06-04 08:10:07'),
(50, '7mpmahhihteb0ev9sk6s7r7jcsjtssuv', 3, 'kofty', 'Розовая вязанная кофта ', 'кофты\\3k.jpg', 1, 'В корзине', '2025-06-04 08:10:50');

-- --------------------------------------------------------

--
-- Структура таблицы `futbolki`
--

CREATE TABLE `futbolki` (
  `id` int NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `image_path` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `color` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'не указан',
  `quantity` int NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Дамп данных таблицы `futbolki`
--

INSERT INTO `futbolki` (`id`, `name`, `price`, `image_path`, `description`, `color`, `quantity`) VALUES
(1, 'Футболка \"Cute citten\"', '1500.00', 'футболки/1f.jpg', 'Качественная футболка из 100% хлопка, модная, 2025 год, молодежная', 'Белый', 25),
(2, 'Футболка \"NewYork\"', '1700.00', 'футболки/2f.jpg', 'Розовая, яркая и стильная футболка на каждый день из качественного материала с надписью \"NewYork\"', 'Розовый', 48),
(3, 'Футболка \"Horror Movies\"', '1650.00', 'футболки\\3f.jpg', 'Стильная футболка темного цвета, из 100% хлопка, легко сочетаемая и подойдет для ваших стильных повседневных нарядов.', 'Черный', 13),
(4, 'Футболка \"Good Times\"', '1000.00', 'футболки\\4f.jpg', 'Стильная футболка бежевого цвета с принтом бабочки и надписью. Комфортный крой и приятная к телу.', 'Бежевый', 10),
(5, 'Футболка \"Strawberry\"', '1370.00', 'футболки\\5f.jpg', 'Розовая, яркая футболка с аппетитным принтом клубники, милая и молодежная.Идеальный выбор для создания летнего и беззаботного образа.', 'Розовый', 17);

-- --------------------------------------------------------

--
-- Структура таблицы `kofty`
--

CREATE TABLE `kofty` (
  `id` int NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `image_path` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `color` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'не указан',
  `quantity` int NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Дамп данных таблицы `kofty`
--

INSERT INTO `kofty` (`id`, `name`, `price`, `image_path`, `description`, `color`, `quantity`) VALUES
(1, 'Голубая нежная кофта-топ             ', '2500.00', 'кофты\\1k.jpg', 'Нежная голубая кофта-топ. Это идеальное сочетание комфорта и стиля. Легкая ткань, приятный оттенок и утонченный дизайн создают романтичный образ. Прекрасно дополнит ваш летний гардероб.', 'Голубой', 38),
(2, 'Бордовая кофта с бантами', '2700.00', 'кофты\\2k.jpg', 'Изысканная бордовая вязанная кофта-майка с очаровательными бантиками на рукавах. Идеальное сочетание стиля и комфорта. Мягкая пряжа,глубокий цвет и кокетливые детали создают неповторимый образ для прохладных летних вечеров.', 'Бордовый', 20),
(3, 'Розовая вязанная кофта ', '2000.00', 'кофты\\3k.jpg', 'Очаровательная, мягкая и уютная кофта с принтом зайки. Она согреет вас в прохладную погоду и подарит хорошее настроение. Идеальный выбор для создания нежного образа.', 'Розовый', 18),
(4, 'Бордовая кофта- свитер', '2200.00', 'кофты\\4k.jpg', 'Элегантная бордовая кофта с открытыми плечами - идеальное сочетание стиля и соблазна. Мягкая ткань, глубокий оттенок и акцент на плечах создают женственный и незабываемый образ.', 'Бордовый', 10),
(5, 'Свитер \"Зайчик\" розовый', '3000.00', 'кофты\\5k.jpg', 'Очаровательный розово-белый свитер с милым зайчиком. Теплый, уютный и очень симпатичный- идеальный выбор для создания нежного образа. Подходит как для детей так и для взрослых. Мягкий, как облачко.', 'Розовый', 50);

-- --------------------------------------------------------

--
-- Структура таблицы `orders`
--

CREATE TABLE `orders` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `order_date` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `status` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Новый',
  `total_amount` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Дамп данных таблицы `orders`
--

INSERT INTO `orders` (`id`, `user_id`, `order_date`, `status`, `total_amount`) VALUES
(9, 1, '2025-06-06 18:58:38', 'Новый', '2800.00'),
(10, 1, '2025-06-06 19:06:11', 'Отменен', '10800.00'),
(11, 1, '2025-06-06 19:11:15', 'Новый', '4000.00'),
(12, 1, '2025-06-06 19:12:55', 'Новый', '4800.00'),
(29, 1, '2025-06-06 19:17:33', 'Новый', '2800.00'),
(30, 1, '2025-06-06 19:17:46', 'Новый', '2800.00'),
(32, 1, '2025-06-06 19:18:08', 'Новый', '1370.00'),
(33, 1, '2025-06-06 19:19:26', 'Новый', '1650.00'),
(34, 1, '2025-06-06 19:19:33', 'Новый', '3000.00'),
(35, 1, '2025-06-06 19:20:51', 'Новый', '3400.00'),
(36, 1, '2025-06-06 19:22:27', 'Новый', '4950.00'),
(37, 1, '2025-06-06 19:22:39', 'Новый', '1650.00'),
(38, 1, '2025-06-06 19:22:55', 'Новый', '1650.00'),
(39, 2, '2025-06-06 19:34:42', 'Отправлен', '2500.00'),
(40, 3, '2025-06-08 15:16:55', 'Новый', '3500.00'),
(41, 4, '2025-06-09 18:20:00', 'Новый', '1500.00'),
(42, 4, '2025-06-09 18:27:44', 'Новый', '4150.00'),
(43, 4, '2025-06-09 18:28:49', 'Новый', '1500.00'),
(44, 4, '2025-06-10 09:29:53', 'Новый', '2000.00');

-- --------------------------------------------------------

--
-- Структура таблицы `order_items`
--

CREATE TABLE `order_items` (
  `id` int NOT NULL,
  `order_id` int NOT NULL,
  `product_id` int NOT NULL,
  `category` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `image_path` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `quantity` int NOT NULL,
  `price` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Дамп данных таблицы `order_items`
--

INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `category`, `name`, `image_path`, `quantity`, `price`) VALUES
(2, 9, 3, '', 'Широкие розовые брюки в клетку', 'брюки\\3b.jpg', 1, '2800.00'),
(3, 10, 2, '', 'Бордовая кофта с бантами', 'кофты\\2k.jpg', 4, '2700.00'),
(4, 11, 3, '', 'Розовая вязанная кофта ', 'кофты\\3k.jpg', 2, '2000.00'),
(5, 12, 2, '', 'Яркие брюки желтого цвета', 'брюки\\2b.jpg', 2, '2400.00'),
(22, 29, 3, 'bryuki', 'Широкие розовые брюки в клетку', 'брюки\\3b.jpg', 1, '2800.00'),
(23, 30, 3, 'bryuki', 'Широкие розовые брюки в клетку', 'брюки\\3b.jpg', 1, '2800.00'),
(25, 32, 5, 'futbolki', 'Футболка \"Strawberry\"', 'футболки\\5f.jpg', 1, '1370.00'),
(26, 33, 3, 'futbolki', 'Футболка \"Horror Movies\"', 'футболки\\3f.jpg', 1, '1650.00'),
(27, 34, 1, 'futbolki', 'Футболка \"Cute citten\"', 'футболки/1f.jpg', 2, '1500.00'),
(28, 35, 2, 'futbolki', 'Футболка \"NewYork\"', 'футболки/2f.jpg', 2, '1700.00'),
(29, 36, 3, 'futbolki', 'Футболка \"Horror Movies\"', 'футболки\\3f.jpg', 3, '1650.00'),
(30, 37, 3, 'futbolki', 'Футболка \"Horror Movies\"', 'футболки\\3f.jpg', 1, '1650.00'),
(31, 38, 3, 'futbolki', 'Футболка \"Horror Movies\"', 'футболки\\3f.jpg', 1, '1650.00'),
(32, 39, 1, 'kofty', 'Голубая нежная кофта-топ             ', 'кофты\\1k.jpg', 1, '2500.00'),
(33, 40, 3, 'kofty', 'Розовая вязанная кофта ', 'кофты\\3k.jpg', 1, '2000.00'),
(34, 40, 1, 'futbolki', 'Футболка \"Cute citten\"', 'футболки/1f.jpg', 1, '1500.00'),
(35, 41, 1, 'futbolki', 'Футболка \"Cute citten\"', 'футболки/1f.jpg', 1, '1500.00'),
(36, 42, 3, 'futbolki', 'Футболка \"Horror Movies\"', 'футболки\\3f.jpg', 1, '1650.00'),
(37, 42, 1, 'kofty', 'Голубая нежная кофта-топ             ', 'кофты\\1k.jpg', 1, '2500.00'),
(38, 43, 1, 'futbolki', 'Футболка \"Cute citten\"', 'футболки/1f.jpg', 1, '1500.00'),
(39, 44, 3, 'kofty', 'Розовая вязанная кофта ', 'кофты\\3k.jpg', 1, '2000.00');

-- --------------------------------------------------------

--
-- Структура таблицы `store_reviews`
--

CREATE TABLE `store_reviews` (
  `id` int NOT NULL,
  `user_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `rating` int NOT NULL,
  `comment` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Дамп данных таблицы `store_reviews`
--

INSERT INTO `store_reviews` (`id`, `user_name`, `rating`, `comment`, `created_at`) VALUES
(1, 'Камила', 5, 'Отлично', '2025-06-06 18:32:37'),
(2, 'Милуня', 4, 'Хороший магазин', '2025-06-06 18:33:12'),
(3, 'Викуля', 5, 'Плюс вайб', '2025-06-06 19:38:23');

-- --------------------------------------------------------

--
-- Структура таблицы `users`
--

CREATE TABLE `users` (
  `id` int NOT NULL,
  `username` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `password_hash` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Дамп данных таблицы `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password_hash`, `created_at`) VALUES
(1, 'Камила', 'kamila.dem2007@gmail.com', '$2y$10$vLslk1bVu0tfCYR7Jt3xmeOvuFSUIaeqG648OOySfNAH7zTPOEomC', '2025-06-06 18:44:17'),
(2, 'Вика зая', '1111@mail.ru', '$2y$10$Bqf4FK/Fp6kpB530HJA5DOixHPCVGIj7CKuiipQAiIUQaxjSXp20W', '2025-06-06 19:32:22'),
(3, 'Лялечка', '1@gmail.com', '$2y$10$OhlZOid/U/knC.bpuCFiBOpzykoh1l7312fUEwbS5f48J2ocEfQ9q', '2025-06-08 15:14:20'),
(4, 'Angelina', 'gelatin0710@gmail.com', '$2y$10$WWWnj2vLwRlaGeMKgorT4.YyLco8/nUNLW7mvuYKrtQLP1ben5JV.', '2025-06-09 18:19:25');

--
-- Индексы сохранённых таблиц
--

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
  ADD KEY `order_id` (`order_id`);

--
-- Индексы таблицы `store_reviews`
--
ALTER TABLE `store_reviews`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT для сохранённых таблиц
--

--
-- AUTO_INCREMENT для таблицы `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=45;

--
-- AUTO_INCREMENT для таблицы `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=40;

--
-- AUTO_INCREMENT для таблицы `store_reviews`
--
ALTER TABLE `store_reviews`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT для таблицы `users`
--
ALTER TABLE `users`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Ограничения внешнего ключа сохраненных таблиц
--

--
-- Ограничения внешнего ключа таблицы `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Ограничения внешнего ключа таблицы `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
