-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1:3306
-- Généré le : ven. 02 mai 2025 à 02:24
-- Version du serveur : 8.3.0
-- Version de PHP : 8.2.18

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `shop_db`
--

-- --------------------------------------------------------

--
-- Structure de la table `cart`
--

DROP TABLE IF EXISTS `cart`;
CREATE TABLE IF NOT EXISTS `cart` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `product_id` int NOT NULL,
  `name` varchar(100) NOT NULL,
  `image` varchar(100) NOT NULL,
  `quantity` int NOT NULL,
  `allotted_quantity` int DEFAULT NULL,
  `status` varchar(20) DEFAULT 'pending',
  `date_commande` date NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `status_updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=93 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `cart`
--

INSERT INTO `cart` (`id`, `user_id`, `product_id`, `name`, `image`, `quantity`, `allotted_quantity`, `status`, `date_commande`, `created_at`, `status_updated_at`) VALUES
(64, 66, 162, 'printer', 'images/6812e0453e568.jpg', 1, 1, 'livrée', '2025-05-01', '2025-05-01 03:38:41', '2025-05-01 19:20:38'),
(65, 66, 161, 'keyboard', 'images/6812e016b9c4d.jpg', 8, NULL, 'rejetée', '2025-05-01', '2025-05-01 03:38:45', '2025-05-01 17:00:34'),
(66, 66, 96, 'pen', 'images/6812db35b04b7.jpg', 1, 1, 'validée', '2025-05-01', '2025-05-01 03:42:30', '2025-05-01 19:00:15'),
(67, 66, 162, 'printer', 'images/6812e0453e568.jpg', 1, 1, 'livrée', '2025-05-01', '2025-05-01 03:42:47', '2025-05-01 19:00:15'),
(68, 77, 160, 'Drawing  instruments', 'images/6812de055d509.jpg', 1, 1, 'validée', '2025-05-01', '2025-05-01 04:03:56', '2025-05-01 19:00:15'),
(69, 77, 110, 'sharpener', 'images/6812dc7c54b42.jpg', 1, NULL, 'rejetée', '2025-05-01', '2025-05-01 04:04:05', '2025-05-02 00:51:03'),
(70, 76, 100, 'pen', 'images/6812db35b04b7.jpg', 1, 1, 'validée', '2025-05-01', '2025-05-01 04:04:41', '2025-05-01 19:00:15'),
(71, 76, 123, 'netebook', 'images/6812dc9a8c316.jpg', 3, NULL, 'pending', '2025-05-01', '2025-05-01 04:04:44', '2025-05-02 01:42:11'),
(72, 66, 161, 'keyboard', 'images/6812e016b9c4d.jpg', 1, NULL, 'Not Approved', '2025-05-01', '2025-05-01 06:36:14', '2025-05-01 17:00:34'),
(73, 66, 162, 'printer', 'images/6812e0453e568.jpg', 4, NULL, 'pending', '2025-05-01', '2025-05-01 06:36:50', '2025-05-02 01:37:26'),
(78, 66, 161, 'keyboard', 'images/6812e016b9c4d.jpg', 1, NULL, 'rejetée', '2025-05-01', '2025-05-01 07:34:00', '2025-05-01 18:31:16'),
(79, 66, 96, 'pen', 'images/6812db35b04b7.jpg', 9, 2, 'validée', '2025-05-01', '2025-05-01 07:34:02', '2025-05-01 19:20:57'),
(85, 66, 161, 'keyboard', 'images/6812e016b9c4d.jpg', 2, 1, 'validée', '2025-05-01', '2025-05-01 17:03:48', '2025-05-01 19:00:46'),
(86, 66, 119, 'netebook', 'images/6812dc9a8c316.jpg', 1, NULL, 'rejetée', '2025-05-01', '2025-05-01 17:03:57', '2025-05-01 17:25:59'),
(87, 66, 162, 'printer', 'images/6812e0453e568.jpg', 1, 1, 'validée', '2025-05-01', '2025-05-01 17:04:02', '2025-05-01 19:03:09'),
(88, 66, 162, 'printer', 'images/6812e0453e568.jpg', 1, 1, 'livrée', '2025-05-01', '2025-05-01 17:04:10', '2025-05-01 19:00:15'),
(89, 76, 104, 'scissors', 'images/6812dc57808c3.jpg', 10, 4, 'livrée', '2025-05-01', '2025-05-01 19:03:45', '2025-05-01 23:07:52'),
(90, 66, 162, 'printer', 'images/6812e0453e568.jpg', 1, NULL, 'rejetée', '2025-05-01', '2025-05-01 19:10:51', '2025-05-01 19:11:27'),
(91, 81, 119, 'netebook', 'images/6812dc9a8c316.jpg', 6, NULL, 'rejetée', '2025-05-01', '2025-05-01 21:19:57', '2025-05-01 21:20:53'),
(92, 81, 96, 'pen', 'images/6812db35b04b7.jpg', 10, 5, 'validée', '2025-05-01', '2025-05-01 21:20:02', '2025-05-01 21:20:51');

-- --------------------------------------------------------

--
-- Structure de la table `departement`
--

DROP TABLE IF EXISTS `departement`;
CREATE TABLE IF NOT EXISTS `departement` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nom` varchar(100) NOT NULL,
  `code` varchar(10) NOT NULL,
  `speciality` varchar(50) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `speciality` (`speciality`)
) ENGINE=InnoDB AUTO_INCREMENT=23 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `departement`
--

INSERT INTO `departement` (`id`, `nom`, `code`, `speciality`) VALUES


(18, '', '', 'Agronomy'),
(19, '', '', 'Sports Science (STAPS)'),
(20, '', '', 'Medicine'),
(21, '', '', 'Science & Technology'),
(22, '', '', 'Public Administration ');

-- --------------------------------------------------------

--
-- Structure de la table `orders`
--

DROP TABLE IF EXISTS `orders`;
CREATE TABLE IF NOT EXISTS `orders` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `product_id` int NOT NULL,
  `quantity` int NOT NULL,
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `department` varchar(100) NOT NULL,
  `product_name` varchar(100) NOT NULL,
  `department_id` int DEFAULT NULL,
  `order_id` int DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `department_id` (`department_id`),
  KEY `fk_orders_master` (`order_id`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Structure de la table `products`
--

DROP TABLE IF EXISTS `products`;
CREATE TABLE IF NOT EXISTS `products` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `image` varchar(100) NOT NULL,
  `speciality` varchar(50) NOT NULL,
  `quantity` int NOT NULL,
  `department` varchar(100) NOT NULL DEFAULT '',
  `stock` int DEFAULT '0',
  `department_id` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=164 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `products`
--

INSERT INTO `products` (`id`, `name`, `image`, `speciality`, `quantity`, `department`, `stock`, `department_id`, `created_at`) VALUES
(93, 'pen', 'images/6812db35b04b7.jpg', 'Agronomy', 50, '', 0, NULL, '2025-05-01 06:19:17'),
(94, 'pen', 'images/6812db35b04b7.jpg', 'biologie', 50, '', 0, NULL, '2025-05-01 06:19:17'),
(95, 'pen', 'images/6812db35b04b7.jpg', 'Chemistry', 50, '', 0, NULL, '2025-05-01 06:19:17'),
(96, 'pen', 'images/6812db35b04b7.jpg', 'Computer_Science', 50, '', 0, NULL, '2025-05-01 06:19:17'),
(97, 'pen', 'images/6812db35b04b7.jpg', 'library', 50, '', 0, NULL, '2025-05-01 06:19:17'),
(98, 'pen', 'images/6812db35b04b7.jpg', 'mathematics', 50, '', 0, NULL, '2025-05-01 06:19:17'),
(99, 'pen', 'images/6812db35b04b7.jpg', 'Medicine', 50, '', 0, NULL, '2025-05-01 06:19:17'),
(100, 'pen', 'images/6812db35b04b7.jpg', 'Physics', 50, '', 0, NULL, '2025-05-01 06:19:17'),
(101, 'pen', 'images/6812db35b04b7.jpg', 'Public Administration ', 50, '', 0, NULL, '2025-05-01 06:19:17'),
(102, 'pen', 'images/6812db35b04b7.jpg', 'Science & Technology', 50, '', 0, NULL, '2025-05-01 06:19:17'),
(103, 'pen', 'images/6812db35b04b7.jpg', 'Sports Science (STAPS)', 50, '', 0, NULL, '2025-05-01 06:19:17'),
(104, 'scissors', 'images/6812dc57808c3.jpg', 'Physics', 2, '', 0, NULL, '2025-05-01 06:19:17'),
(105, 'sharpener', 'images/6812dc7c54b42.jpg', 'Agronomy', 10, '', 0, NULL, '2025-05-01 06:19:17'),
(106, 'sharpener', 'images/6812dc7c54b42.jpg', 'biologie', 10, '', 0, NULL, '2025-05-01 06:19:17'),
(107, 'sharpener', 'images/6812dc7c54b42.jpg', 'Chemistry', 10, '', 0, NULL, '2025-05-01 06:19:17'),
(108, 'sharpener', 'images/6812dc7c54b42.jpg', 'Computer_Science', 10, '', 0, NULL, '2025-05-01 06:19:17'),
(109, 'sharpener', 'images/6812dc7c54b42.jpg', 'library', 10, '', 0, NULL, '2025-05-01 06:19:17'),
(110, 'sharpener', 'images/6812dc7c54b42.jpg', 'mathematics', 10, '', 0, NULL, '2025-05-01 06:19:17'),
(111, 'sharpener', 'images/6812dc7c54b42.jpg', 'Medicine', 10, '', 0, NULL, '2025-05-01 06:19:17'),
(112, 'sharpener', 'images/6812dc7c54b42.jpg', 'Physics', 10, '', 0, NULL, '2025-05-01 06:19:17'),
(113, 'sharpener', 'images/6812dc7c54b42.jpg', 'Public Administration ', 10, '', 0, NULL, '2025-05-01 06:19:17'),
(114, 'sharpener', 'images/6812dc7c54b42.jpg', 'Science & Technology', 10, '', 0, NULL, '2025-05-01 06:19:17'),
(115, 'sharpener', 'images/6812dc7c54b42.jpg', 'Sports Science (STAPS)', 10, '', 0, NULL, '2025-05-01 06:19:17'),
(116, 'netebook', 'images/6812dc9a8c316.jpg', 'Agronomy', 8, '', 0, NULL, '2025-05-01 06:19:17'),
(117, 'netebook', 'images/6812dc9a8c316.jpg', 'biologie', 8, '', 0, NULL, '2025-05-01 06:19:17'),
(118, 'netebook', 'images/6812dc9a8c316.jpg', 'Chemistry', 8, '', 0, NULL, '2025-05-01 06:19:17'),
(119, 'netebook', 'images/6812dc9a8c316.jpg', 'Computer_Science', 8, '', 0, NULL, '2025-05-01 06:19:17'),
(120, 'netebook', 'images/6812dc9a8c316.jpg', 'library', 8, '', 0, NULL, '2025-05-01 06:19:17'),
(121, 'netebook', 'images/6812dc9a8c316.jpg', 'mathematics', 8, '', 0, NULL, '2025-05-01 06:19:17'),
(122, 'netebook', 'images/6812dc9a8c316.jpg', 'Medicine', 8, '', 0, NULL, '2025-05-01 06:19:17'),
(123, 'netebook', 'images/6812dc9a8c316.jpg', 'Physics', 8, '', 0, NULL, '2025-05-01 06:19:17'),
(124, 'netebook', 'images/6812dc9a8c316.jpg', 'Public Administration ', 8, '', 0, NULL, '2025-05-01 06:19:17'),
(125, 'netebook', 'images/6812dc9a8c316.jpg', 'Science & Technology', 8, '', 0, NULL, '2025-05-01 06:19:17'),
(126, 'netebook', 'images/6812dc9a8c316.jpg', 'Sports Science (STAPS)', 8, '', 0, NULL, '2025-05-01 06:19:17'),
(135, 'netebook', 'images/6812dd131bdf0.jpg', 'Public Administration ', 8, '', 0, NULL, '2025-05-01 06:19:17'),
(136, 'netebook', 'images/6812dd131bdf0.jpg', 'Science & Technology', 8, '', 0, NULL, '2025-05-01 06:19:17'),
(137, 'netebook', 'images/6812dd131bdf0.jpg', 'Sports Science (STAPS)', 8, '', 0, NULL, '2025-05-01 06:19:17'),
(146, 'netebook', 'images/6812dd639bd04.jpg', 'Public Administration ', 8, '', 0, NULL, '2025-05-01 06:19:17'),
(157, 'netebook', 'images/6812dd7742f8e.jpg', 'Public Administration ', 8, '', 0, NULL, '2025-05-01 06:19:17'),
(159, 'netebook', 'images/6812dd7742f8e.jpg', 'Sports Science (STAPS)', 8, '', 0, NULL, '2025-05-01 06:19:17'),
(160, 'Drawing  instruments', 'images/6812de055d509.jpg', 'mathematics', 3, '', 0, NULL, '2025-05-01 06:19:17'),
(161, 'keyboard', 'images/6812e016b9c4d.jpg', 'Computer_Science', 5, '', 0, NULL, '2025-05-01 06:19:17'),
(162, 'printer', 'images/6812e0453e568.jpg', 'Computer_Science', 1, '', 0, NULL, '2025-05-01 06:19:17'),
(163, 'Beaker', 'images/6812e0b5d796b.jpg', 'Physics', 7, '', 0, NULL, '2025-05-01 06:19:17');

-- --------------------------------------------------------

--
-- Structure de la table `user_info`
--

DROP TABLE IF EXISTS `user_info`;
CREATE TABLE IF NOT EXISTS `user_info` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(100) NOT NULL,
  `approved` tinyint(1) DEFAULT '0',
  `type` varchar(50) DEFAULT NULL,
  `speciality` varchar(50) NOT NULL,
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `registration_date` datetime DEFAULT CURRENT_TIMESTAMP,
  `approval_date` datetime DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `department_id` int DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=85 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `user_info`
--

INSERT INTO `user_info` (`id`, `name`, `email`, `password`, `approved`, `type`, `speciality`, `status`, `registration_date`, `approval_date`, `is_active`, `department_id`) VALUES
(12, 'sabrina benckemache', 'sabrina@gmaiil.com', '00e45749508fe15ca1af3397eab8db78', 1, 'admin', '', 'approved', '2025-04-18 14:17:20', NULL, 1, NULL),
(66, 'ROUFAIDA KANOUN', 'roufaidakanoun52@gmail.com', '7312438587983da6877a56e96088f271', 1, 'user', 'Computer_Science', 'pending', '2025-04-30 18:24:08', '2025-04-30 18:25:06', 1, NULL),
(76, 'ferial', 'ferialchettab22@gmail.com', '38509af90339cbad729a571331e720bc', 1, NULL, 'Physics', 'pending', '2025-04-30 21:00:55', '2025-04-30 21:02:44', 1, NULL),
(78, 'mustapha', 'mostafakanoun79@gmail.com', '83f524549cb31ef48c790091e87405b2', 1, NULL, 'Administration', 'pending', '2025-05-01 12:38:31', '2025-05-01 12:38:48', 1, NULL),
(79, 'allaa', 'allakanoun943@gmail.com', 'd06f3a6fbdcb0d6d860a3e04682dbdf3', 1, NULL, 'STAPS', 'pending', '2025-05-01 12:40:47', '2025-05-01 12:41:06', 1, NULL),
(80, 'sab', 'sabrinalotfi123@gmail.com', '25f9e794323b453885f5181f1b624d0b', 1, NULL, 'Library', 'pending', '2025-05-01 12:46:51', '2025-05-01 12:47:08', 1, NULL),
(83, 'oussma', 'ousshcn34@gmail.com', '3910dd2d465bc1bc499d47c7ca86d435', 1, NULL, 'Computer_Science', 'pending', '2025-05-01 15:12:33', '2025-05-01 15:13:16', 1, NULL);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
