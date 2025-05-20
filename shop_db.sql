-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1:3306
-- Généré le : dim. 20 avr. 2025 à 19:10
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
  `status` varchar(20) DEFAULT 'pending',
  `date_commande` date NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=37 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `cart`
--

INSERT INTO `cart` (`id`, `user_id`, `product_id`, `name`, `image`, `quantity`, `status`, `date_commande`, `created_at`) VALUES
(36, 10, 0, 'Introduction to Programming Textbook', 'cs-textbook.jpg', 7, 'pending', '0000-00-00', '2025-04-19 07:31:37');

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
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `departement`
--

INSERT INTO `departement` (`id`, `nom`, `code`, `speciality`) VALUES
(1, 'Informatique', 'CS', 'Computer_Science'),
(3, 'Physique', 'PHYS', 'Physics'),
(4, 'Chimie', 'CHEM', 'Chemistry'),
(5, 'Génie Civil', 'CIVIL', 'Civil_Engineering'),
(6, 'Electronique', 'ELEC', 'Electronics'),
(7, '', '', 'biologie');

-- --------------------------------------------------------

--
-- Structure de la table `departments`
--

DROP TABLE IF EXISTS `departments`;
CREATE TABLE IF NOT EXISTS `departments` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `departments`
--

INSERT INTO `departments` (`id`, `name`) VALUES
(1, 'Informatique'),
(2, 'Marketing'),
(3, 'Logistique');

-- --------------------------------------------------------

--
-- Structure de la table `orders`
--

DROP TABLE IF EXISTS `orders`;
CREATE TABLE IF NOT EXISTS `orders` (
  `id` int NOT NULL AUTO_INCREMENT,
  `product_name` varchar(100) NOT NULL,
  `quantity` int NOT NULL,
  `status` enum('en attente','validée','rejetée') DEFAULT 'en attente',
  `department_id` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `department_id` (`department_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

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
  `department_id` int DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=52 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `products`
--

INSERT INTO `products` (`id`, `name`, `image`, `speciality`, `quantity`, `department_id`) VALUES
(45, 'stelos', 'images/6805321d058a9.jpg', 'biologie', 200, NULL),
(46, 'stelos', 'images/6805321d058a9.jpg', 'Chemistry', 200, NULL),
(47, 'stelos', 'images/6805321d058a9.jpg', 'Civil_Engineering', 200, NULL),
(48, 'stelos', 'images/6805321d058a9.jpg', 'Computer_Science', 200, NULL),
(49, 'stelos', 'images/6805321d058a9.jpg', 'Electronics', 200, NULL),
(50, 'stelos', 'images/6805321d058a9.jpg', 'Physics', 200, NULL),
(51, 'TABLE', 'images/6805323e36453.jpg', 'Physics', 37, NULL);

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
  `department_id` int DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=51 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `user_info`
--

INSERT INTO `user_info` (`id`, `name`, `email`, `password`, `approved`, `type`, `speciality`, `status`, `registration_date`, `approval_date`, `department_id`) VALUES
(10, 'KANOUN ROUFAIDA', 'roufaidakanoun52@gmail.com', '7312438587983da6877a56e96088f271', 1, 'user', 'Computer_Science', 'approved', '2025-04-18 14:17:20', NULL, NULL),
(12, 'sabrina benckemache', 'sabrina@gmaiil.com', '00e45749508fe15ca1af3397eab8db78', 1, 'admin', '', 'approved', '2025-04-18 14:17:20', NULL, NULL),
(38, 'ferial', 'ferial@gmail.com', '38509af90339cbad729a571331e720bc', 1, NULL, '', 'rejected', '2025-04-18 14:17:20', NULL, NULL),
(39, 'rania', 'rania@gmail.com', 'd6bd4288dbcf5d2ae2053a35389e8c56', 1, NULL, '', 'rejected', '2025-04-18 14:17:20', NULL, NULL),
(40, 'madame sismail', 'sismail@gmail.com', '656d24634b45437f51924a62025e4ecd', 1, NULL, 'informatique', 'rejected', '2025-04-18 14:17:20', NULL, NULL),
(41, 'KANOUN ROUFAIDA', 'roufaidakanoun5222@gmail.com', '8655d3720b075366774edcc97706b274', 1, 'user', 'Mathematics', 'rejected', '2025-04-18 14:17:20', NULL, NULL),
(43, 'ROUFI', 'roufaidakanoun5882@gmail.com', '$2y$10$l6PaR.vd1o5h6fvvMXoa4Osx6RAmTu2WDO06xK3kHEYKolzi6.SL.', 1, 'user', 'MAISON', 'rejected', '2025-04-18 14:17:20', NULL, NULL),
(44, 'ramzikan', 'rrrrrrrrrrrrrrrrrrrrrrrrrrr@gmail.com', 'dd4b21e9ef71e1291183a46b913ae6f2', 1, NULL, 'Computer_Science', 'rejected', '2025-04-18 14:17:20', NULL, NULL),
(46, 'KANOUN ROUFAIDA', 'kanounroufaida5@gmail.com', '9e4d29800e8161ecc02053cc8178a7be', 1, 'user', 'AAAAA', 'pending', '2025-04-18 14:17:20', NULL, NULL),
(47, 'ROUFI', 'roufaidakanoun5200@gmail.com', '56be4aabcb91a115f7c1fccf596659d8', 1, NULL, 'informatique', 'pending', '2025-04-18 14:21:09', '2025-04-18 14:21:39', NULL),
(49, 'KANOUN ROUFAIDA', 'roufaidakanoun5552@gmail.com', '1ea49faf9019945c59a8030d0b498cb4', -1, NULL, 'Computer_Science', 'pending', '2025-04-18 14:42:52', NULL, NULL),
(50, 'lotfi', 'lotfi@gmail.com', 'ef5d109180179e3e273a485fed7b9267', 0, NULL, 'informatique', 'pending', '2025-04-19 17:48:28', NULL, NULL);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
