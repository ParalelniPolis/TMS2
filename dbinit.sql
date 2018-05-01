SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

CREATE DATABASE IF NOT EXISTS `tms` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;
USE `tms`;

CREATE TABLE `activation` (
  `id` int(11) NOT NULL,
  `validation_string` char(128) COLLATE utf8_czech_ci NOT NULL,
  `email` varchar(60) COLLATE utf8_czech_ci NOT NULL,
  `active` tinyint(1) NOT NULL,
  `timestamp` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;

CREATE TABLE `admins` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `place_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;

CREATE TABLE `bookings` (
  `id_booking` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `room_id` int(11) NOT NULL,
  `term_from` int(11) NOT NULL,
  `term_to` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;

CREATE TABLE `csrf` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `token` varchar(128) COLLATE utf8_czech_ci NOT NULL,
  `active` tinyint(4) NOT NULL,
  `timestamp` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;

CREATE TABLE `extras` (
  `id_extra` int(11) NOT NULL,
  `payment_id` int(11) DEFAULT NULL,
  `fakturoid_id` int(11) DEFAULT NULL,
  `description` varchar(120) COLLATE utf8_czech_ci NOT NULL,
  `priceCZK` int(11) NOT NULL,
  `blank_user_id` int(11) DEFAULT NULL,
  `vat` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;

CREATE TABLE `locks` (
  `id_lock` int(11) NOT NULL,
  `id_place` int(11) NOT NULL,
  `name` varchar(60) COLLATE utf8_czech_ci NOT NULL,
  `lock_name` varchar(60) COLLATE utf8_czech_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;

CREATE TABLE `lock_attempts` (
  `id` int(11) NOT NULL,
  `uid_key` varchar(40) COLLATE utf8_czech_ci NOT NULL,
  `lock_name` varchar(30) COLLATE utf8_czech_ci NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;

CREATE TABLE `login_attempts` (
  `id` int(11) NOT NULL,
  `login` varchar(60) CHARACTER SET utf8 NOT NULL,
  `success` tinyint(1) NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;

CREATE TABLE `payments` (
  `id_payment` int(11) NOT NULL,
  `id_payer` int(11) NOT NULL,
  `time_generated` datetime NOT NULL,
  `invoice_fakturoid_id` int(11) NOT NULL,
  `invoice_fakturoid_number` varchar(12) COLLATE utf8_czech_ci NOT NULL,
  `status` varchar(60) COLLATE utf8_czech_ci NOT NULL,
  `tariff_id` int(11) NOT NULL,
  `price_CZK` int(11) NOT NULL,
  `bitcoinpay_payment_id` varchar(16) COLLATE utf8_czech_ci DEFAULT NULL COMMENT '16 chars (by try, not from documentation!)',
  `payed_price_BTC` decimal(12,5) DEFAULT NULL,
  `payment_first_date` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;

CREATE TABLE `places` (
  `id` int(11) NOT NULL,
  `name` varchar(30) COLLATE utf8_czech_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;

CREATE TABLE `restart_brutforce` (
  `id` int(11) NOT NULL,
  `validation_string` char(128) COLLATE utf8_czech_ci NOT NULL,
  `email` varchar(60) COLLATE utf8_czech_ci NOT NULL,
  `active` tinyint(1) NOT NULL,
  `timestamp` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;

CREATE TABLE `restart_password` (
  `id` int(11) NOT NULL,
  `validation_string` char(128) COLLATE utf8_czech_ci NOT NULL,
  `email` varchar(60) COLLATE utf8_czech_ci NOT NULL,
  `active` tinyint(1) NOT NULL,
  `timestamp` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;

CREATE TABLE `rooms` (
  `id_room` int(11) NOT NULL,
  `nameCZE` varchar(60) COLLATE utf8_czech_ci NOT NULL,
  `nameENG` varchar(60) COLLATE utf8_czech_ci NOT NULL,
  `descriptionCZE` text COLLATE utf8_czech_ci,
  `descriptionENG` text COLLATE utf8_czech_ci
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;

CREATE TABLE `tariffs` (
  `id_tariff` int(11) NOT NULL,
  `place_id` int(11) NOT NULL,
  `tariffCZE` varchar(60) COLLATE utf8_czech_ci NOT NULL,
  `tariffENG` varchar(60) COLLATE utf8_czech_ci NOT NULL,
  `priceCZK` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;

CREATE TABLE `tickets` (
  `id_ticket` int(11) NOT NULL,
  `type` varchar(60) COLLATE utf8_czech_ci NOT NULL,
  `title` varchar(256) COLLATE utf8_czech_ci NOT NULL,
  `message` text COLLATE utf8_czech_ci NOT NULL,
  `timestamp` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;

CREATE TABLE `users` (
  `id_user` int(16) NOT NULL,
  `fakturoid_id` int(11) NOT NULL,
  `first_name` varchar(60) COLLATE utf8_czech_ci NOT NULL,
  `last_name` varchar(60) COLLATE utf8_czech_ci NOT NULL,
  `telephone` varchar(16) COLLATE utf8_czech_ci DEFAULT NULL,
  `address` varchar(120) COLLATE utf8_czech_ci DEFAULT NULL,
  `ic` varchar(20) COLLATE utf8_czech_ci DEFAULT NULL,
  `company` varchar(120) COLLATE utf8_czech_ci DEFAULT NULL,
  `user_tariff` int(11) NOT NULL,
  `active` tinyint(1) NOT NULL,
  `email` varchar(60) COLLATE utf8_czech_ci NOT NULL,
  `password` char(128) COLLATE utf8_czech_ci NOT NULL,
  `salt` char(128) COLLATE utf8_czech_ci NOT NULL,
  `invoicing_start_date` date NOT NULL,
  `uid_key` varchar(40) COLLATE utf8_czech_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;


ALTER TABLE `activation`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `admins`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `bookings`
  ADD PRIMARY KEY (`id_booking`);

ALTER TABLE `csrf`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `extras`
  ADD PRIMARY KEY (`id_extra`);

ALTER TABLE `locks`
  ADD PRIMARY KEY (`id_lock`);

ALTER TABLE `lock_attempts`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `login_attempts`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `payments`
  ADD PRIMARY KEY (`id_payment`);

ALTER TABLE `places`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `restart_brutforce`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `restart_password`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `rooms`
  ADD PRIMARY KEY (`id_room`);

ALTER TABLE `tariffs`
  ADD PRIMARY KEY (`id_tariff`);

ALTER TABLE `tickets`
  ADD PRIMARY KEY (`id_ticket`);

ALTER TABLE `users`
  ADD PRIMARY KEY (`id_user`);


ALTER TABLE `activation`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=214;
ALTER TABLE `admins`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;
ALTER TABLE `bookings`
  MODIFY `id_booking` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;
ALTER TABLE `csrf`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7158;
ALTER TABLE `extras`
  MODIFY `id_extra` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=363;
ALTER TABLE `locks`
  MODIFY `id_lock` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;
ALTER TABLE `lock_attempts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=115;
ALTER TABLE `login_attempts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4531;
ALTER TABLE `payments`
  MODIFY `id_payment` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2753;
ALTER TABLE `restart_brutforce`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;
ALTER TABLE `restart_password`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=209;
ALTER TABLE `rooms`
  MODIFY `id_room` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;
ALTER TABLE `tariffs`
  MODIFY `id_tariff` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;
ALTER TABLE `tickets`
  MODIFY `id_ticket` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=33579;
ALTER TABLE `users`
  MODIFY `id_user` int(16) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=253;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
