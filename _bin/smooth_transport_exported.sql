-- phpMyAdmin SQL Dump
-- version 4.1.14
-- http://www.phpmyadmin.net
--
-- Host: 127.0.0.1
-- Generation Time: Dec 09, 2015 at 02:49 AM
-- Server version: 5.6.17
-- PHP Version: 5.3.28

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `smooth_transport`
--

-- --------------------------------------------------------

--
-- Table structure for table `ccst16_car`
--

CREATE TABLE IF NOT EXISTS `ccst16_car` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `brand` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `model` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `manufacture_dt` datetime NOT NULL,
  `is_new` bit(1) DEFAULT b'1',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci AUTO_INCREMENT=2 ;

--
-- Dumping data for table `ccst16_car`
--

INSERT INTO `ccst16_car` (`id`, `brand`, `model`, `manufacture_dt`, `is_new`) VALUES
(1, 'Renault', 'Clio', '2015-11-10 00:00:00', b'1');

-- --------------------------------------------------------

--
-- Table structure for table `ccst16_delivery_order`
--

CREATE TABLE IF NOT EXISTS `ccst16_delivery_order` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `creation_ts` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `take_cars_dt` datetime NOT NULL,
  `deliver_cars_dt` datetime NOT NULL,
  `status` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `truck_id` bigint(20) unsigned NOT NULL,
  `car_id` bigint(20) unsigned NOT NULL,
  `manufacturer_id` bigint(20) unsigned NOT NULL,
  `shop_id` bigint(20) unsigned NOT NULL,
  `quantity` tinyint(4) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci AUTO_INCREMENT=2 ;

--
-- Dumping data for table `ccst16_delivery_order`
--

INSERT INTO `ccst16_delivery_order` (`id`, `creation_ts`, `take_cars_dt`, `deliver_cars_dt`, `status`, `truck_id`, `car_id`, `manufacturer_id`, `shop_id`, `quantity`) VALUES
(1, '2015-12-02 14:56:11', '2015-12-03 01:00:00', '2015-12-03 07:00:00', 'En Route T', 1, 1, 1, 2, 4);

-- --------------------------------------------------------

--
-- Table structure for table `ccst16_logistician`
--

CREATE TABLE IF NOT EXISTS `ccst16_logistician` (
  `id` bigint(20) unsigned NOT NULL,
  `username` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `first_name` varchar(25) COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_name` varchar(25) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `ccst16_logistician`
--

INSERT INTO `ccst16_logistician` (`id`, `username`, `password`, `first_name`, `last_name`) VALUES
(3, 'admin', '8d969eef6ecad3c29a3a629280e686cf0c3f5d5a86aff3ca12020c923adc6c92', 'Admin', 'Парола:123456');

-- --------------------------------------------------------

--
-- Table structure for table `ccst16_logistician_and_truck_driver`
--

CREATE TABLE IF NOT EXISTS `ccst16_logistician_and_truck_driver` (
  `logistician_id` bigint(20) unsigned NOT NULL DEFAULT '0',
  `truck_driver_id` bigint(20) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`logistician_id`,`truck_driver_id`),
  KEY `truck_driver_id` (`truck_driver_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `ccst16_point_of_interest`
--

CREATE TABLE IF NOT EXISTS `ccst16_point_of_interest` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `longitude` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `latitude` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `location` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci AUTO_INCREMENT=7 ;

--
-- Dumping data for table `ccst16_point_of_interest`
--

INSERT INTO `ccst16_point_of_interest` (`id`, `name`, `type`, `longitude`, `latitude`, `location`) VALUES
(1, 'Renault Factory', 'Manufacturer', '55.6712206', '12.5279906', 'Copenhagen'),
(2, 'Renault Shop', 'Shop', '57.0251403', '9.9354078', 'Aalborg'),
(3, 'THansen Aalborg', 'service', '15.0000', '15.000', 'Aalborg'),
(4, 'Aarhus Repair A/S', 'service', '15.0000', '15.000', 'Aarhus'),
(5, 'QuickFix Ltd.', 'service', '15.0000', '15.000', 'Odense'),
(6, 'CPH Truck Service Station', 'service', '15.0000', '15.000', 'Copenhagen');

-- --------------------------------------------------------

--
-- Table structure for table `ccst16_sms`
--

CREATE TABLE IF NOT EXISTS `ccst16_sms` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `saved_on_ts` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `message` varchar(160) COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_sent` tinyint(1) DEFAULT '0',
  `is_received` tinyint(1) DEFAULT '0',
  `sender_id` bigint(20) unsigned NOT NULL,
  `recipient_id` bigint(20) unsigned NOT NULL,
  `clicksend_ts` timestamp NULL DEFAULT NULL,
  `is_seen` bit(1) NOT NULL DEFAULT b'0',
  `is_resolved` bit(1) NOT NULL DEFAULT b'0',
  PRIMARY KEY (`id`),
  KEY `sender_id` (`sender_id`),
  KEY `recipient_id` (`recipient_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci AUTO_INCREMENT=74 ;

--
-- Dumping data for table `ccst16_sms`
--

INSERT INTO `ccst16_sms` (`id`, `saved_on_ts`, `message`, `is_sent`, `is_received`, `sender_id`, `recipient_id`, `clicksend_ts`, `is_seen`, `is_resolved`) VALUES
(18, '2015-11-30 21:54:42', 'Truckdown', 0, 0, 4, 3, '2015-11-27 22:10:00', b'1', b'0'),
(19, '2015-11-30 21:54:43', 'We have received your inquiry.', 0, 0, 3, 4, '2015-11-30 20:54:43', b'0', b'0'),
(20, '2015-12-01 14:51:11', 'Truckdown', 0, 0, 4, 3, '2015-11-27 22:10:00', b'1', b'0'),
(21, '2015-12-01 14:51:14', 'We have received your inquiry.', 0, 0, 3, 4, '2015-12-01 13:51:14', b'0', b'0'),
(27, '2015-12-08 21:49:25', 'Bravo', 0, 0, 4, 3, '2015-12-03 13:35:27', b'1', b'1'),
(28, '2015-12-08 21:49:27', 'We have received your inquiry.', 0, 0, 3, 4, '2015-12-08 20:49:27', b'0', b'0'),
(29, '2015-12-08 21:50:40', 'Bravo', 0, 0, 4, 3, '2015-12-03 13:35:27', b'1', b'1'),
(30, '2015-12-08 21:50:42', 'We have received your inquiry.', 0, 0, 3, 4, '2015-12-08 20:50:42', b'0', b'0');

-- --------------------------------------------------------

--
-- Table structure for table `ccst16_sms_user`
--

CREATE TABLE IF NOT EXISTS `ccst16_sms_user` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `telephone` varchar(16) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci AUTO_INCREMENT=6 ;

--
-- Dumping data for table `ccst16_sms_user`
--

INSERT INTO `ccst16_sms_user` (`id`, `telephone`) VALUES
(3, '+45609946244003'),
(4, '+4581589467'),
(5, '+359888888888');

-- --------------------------------------------------------

--
-- Table structure for table `ccst16_truck`
--

CREATE TABLE IF NOT EXISTS `ccst16_truck` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `vehicle_capacity` tinyint(4) NOT NULL,
  `brand` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `model` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `engine` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tires_serial` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `age` tinyint(4) NOT NULL,
  `number_plate` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci AUTO_INCREMENT=2 ;

--
-- Dumping data for table `ccst16_truck`
--

INSERT INTO `ccst16_truck` (`id`, `vehicle_capacity`, `brand`, `model`, `engine`, `tires_serial`, `age`, `number_plate`) VALUES
(1, 6, 'Scania', 'Streamline`', '16-liter Euro 6 engi', 'DOT J3J9 1001', 2, 'PC 10 100');

-- --------------------------------------------------------

--
-- Table structure for table `ccst16_truck_driver`
--

CREATE TABLE IF NOT EXISTS `ccst16_truck_driver` (
  `id` bigint(20) unsigned NOT NULL,
  `first_name` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_name` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `ccst16_truck_driver`
--

INSERT INTO `ccst16_truck_driver` (`id`, `first_name`, `last_name`) VALUES
(4, 'Ivan', 'Petrov'),
(5, 'Stoycho', 'Stoychev');

-- --------------------------------------------------------

--
-- Table structure for table `ccst16_truck_driver_and_truck`
--

CREATE TABLE IF NOT EXISTS `ccst16_truck_driver_and_truck` (
  `truck_driver_id` bigint(20) unsigned NOT NULL DEFAULT '0',
  `truck_id` bigint(20) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`truck_driver_id`,`truck_id`),
  KEY `truck_id` (`truck_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `ccst16_truck_driver_and_truck`
--

INSERT INTO `ccst16_truck_driver_and_truck` (`truck_driver_id`, `truck_id`) VALUES
(4, 1);

--
-- Constraints for dumped tables
--

--
-- Constraints for table `ccst16_logistician`
--
ALTER TABLE `ccst16_logistician`
  ADD CONSTRAINT `ccst16_logistician_ibfk_1` FOREIGN KEY (`id`) REFERENCES `ccst16_sms_user` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `ccst16_logistician_and_truck_driver`
--
ALTER TABLE `ccst16_logistician_and_truck_driver`
  ADD CONSTRAINT `ccst16_logistician_and_truck_driver_ibfk_1` FOREIGN KEY (`logistician_id`) REFERENCES `ccst16_logistician` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `ccst16_logistician_and_truck_driver_ibfk_2` FOREIGN KEY (`truck_driver_id`) REFERENCES `ccst16_truck_driver` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `ccst16_sms`
--
ALTER TABLE `ccst16_sms`
  ADD CONSTRAINT `ccst16_sms_ibfk_1` FOREIGN KEY (`sender_id`) REFERENCES `ccst16_sms_user` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `ccst16_sms_ibfk_2` FOREIGN KEY (`recipient_id`) REFERENCES `ccst16_sms_user` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `ccst16_truck_driver`
--
ALTER TABLE `ccst16_truck_driver`
  ADD CONSTRAINT `ccst16_truck_driver_ibfk_1` FOREIGN KEY (`id`) REFERENCES `ccst16_sms_user` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `ccst16_truck_driver_and_truck`
--
ALTER TABLE `ccst16_truck_driver_and_truck`
  ADD CONSTRAINT `ccst16_truck_driver_and_truck_ibfk_1` FOREIGN KEY (`truck_driver_id`) REFERENCES `ccst16_truck_driver` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `ccst16_truck_driver_and_truck_ibfk_2` FOREIGN KEY (`truck_id`) REFERENCES `ccst16_truck` (`id`) ON DELETE CASCADE;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
