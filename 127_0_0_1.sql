-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1:3306
-- Généré le : dim. 18 mai 2025 à 06:20
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
-- Base de données : `gestion_stock.sql`
--
CREATE DATABASE IF NOT EXISTS `gestion_stock.sql` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci;
USE `gestion_stock.sql`;

-- --------------------------------------------------------

--
-- Structure de la table `article`
--

DROP TABLE IF EXISTS `article`;
CREATE TABLE IF NOT EXISTS `article` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nom_article` varchar(50) NOT NULL,
  `id_categorie` int NOT NULL,
  `quantite` int NOT NULL,
  `prix_unitaire` int NOT NULL,
  `date_fabrication` datetime NOT NULL,
  `date_expiration` datetime NOT NULL,
  `images` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `id_categorie` (`id_categorie`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `article`
--

INSERT INTO `article` (`id`, `nom_article`, `id_categorie`, `quantite`, `prix_unitaire`, `date_fabrication`, `date_expiration`, `images`) VALUES
(1, 'HP', 1, 3, 200000, '2022-09-15 22:32:00', '2022-09-18 19:36:00', NULL),
(2, 'Imprimante scanner', 2, 1, 50000, '2022-09-09 20:41:00', '2022-10-02 19:47:00', NULL),
(3, 'Cable VGA', 3, 65, 1500, '2022-09-18 18:55:00', '2022-09-16 18:57:00', NULL),
(4, 'souris', 3, 105, 6000, '2022-09-16 19:58:00', '2022-09-16 19:02:00', NULL),
(5, 'Ecouteur', 3, 3, 1000, '2022-09-23 00:26:00', '2022-09-23 20:33:00', NULL),
(6, 'Chargeur', 3, 35, 500, '2022-09-23 22:27:00', '2022-09-23 01:27:00', NULL),
(7, 'HP 15', 1, 7, 7888, '2023-03-04 18:13:00', '2023-03-04 18:13:00', NULL),
(8, 'Télécommande', 3, 10, 1000, '2023-03-03 18:35:00', '2023-04-09 18:35:00', '../public/images/WhatsApp Image 2023-01-23 at 12.57.19.jpeg');

-- --------------------------------------------------------

--
-- Structure de la table `categorie_article`
--

DROP TABLE IF EXISTS `categorie_article`;
CREATE TABLE IF NOT EXISTS `categorie_article` (
  `id` int NOT NULL AUTO_INCREMENT,
  `libelle_categorie` varchar(60) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `categorie_article`
--

INSERT INTO `categorie_article` (`id`, `libelle_categorie`) VALUES
(1, 'Ordinateur'),
(2, 'Imprimante'),
(3, 'Accessoire');

-- --------------------------------------------------------

--
-- Structure de la table `client`
--

DROP TABLE IF EXISTS `client`;
CREATE TABLE IF NOT EXISTS `client` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nom` varchar(30) NOT NULL,
  `prenom` varchar(30) NOT NULL,
  `telephone` varchar(30) NOT NULL,
  `adresse` varchar(50) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `client`
--

INSERT INTO `client` (`id`, `nom`, `prenom`, `telephone`, `adresse`) VALUES
(1, 'Adamou', 'Abdoul Razak', '+22798960382', 'Tahoua Niger'),
(2, 'Maiga', 'Abdoul rachid Amadou', '+22758907514', '45 rue saint pallais');

-- --------------------------------------------------------

--
-- Structure de la table `commande`
--

DROP TABLE IF EXISTS `commande`;
CREATE TABLE IF NOT EXISTS `commande` (
  `id` int NOT NULL AUTO_INCREMENT,
  `id_article` int NOT NULL,
  `id_fournisseur` int NOT NULL,
  `quantite` int NOT NULL,
  `prix` int NOT NULL,
  `date_commande` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `id_article` (`id_article`),
  KEY `id_fournisseur` (`id_fournisseur`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `commande`
--

INSERT INTO `commande` (`id`, `id_article`, `id_fournisseur`, `quantite`, `prix`, `date_commande`) VALUES
(1, 2, 2, 4, 200000, '2022-09-23 17:54:48'),
(2, 4, 1, 5, 30000, '2022-09-23 17:56:45'),
(3, 1, 2, 12, 2400000, '2022-09-23 19:23:07'),
(4, 4, 2, 56, 336000, '2022-09-24 10:23:22');

-- --------------------------------------------------------

--
-- Structure de la table `contact`
--

DROP TABLE IF EXISTS `contact`;
CREATE TABLE IF NOT EXISTS `contact` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nom` varchar(2) NOT NULL,
  `prenom` varchar(3) NOT NULL,
  `email` varchar(4) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Structure de la table `fournisseur`
--

DROP TABLE IF EXISTS `fournisseur`;
CREATE TABLE IF NOT EXISTS `fournisseur` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nom` varchar(30) NOT NULL,
  `prenom` varchar(30) NOT NULL,
  `telephone` varchar(15) NOT NULL,
  `adresse` varchar(50) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `fournisseur`
--

INSERT INTO `fournisseur` (`id`, `nom`, `prenom`, `telephone`, `adresse`) VALUES
(1, 'Komche', 'Issa', '+22792470763', 'Yantala, Niamey'),
(2, 'MAMAN SANI', 'HASSAN', '+22798655425', 'Zinder, Jeune cadre');

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `commande`
--
ALTER TABLE `commande`
  ADD CONSTRAINT `commande_ibfk_1` FOREIGN KEY (`id_article`) REFERENCES `article` (`id`),
  ADD CONSTRAINT `commande_ibfk_2` FOREIGN KEY (`id_fournisseur`) REFERENCES `fournisseur` (`id`);
--
-- Base de données : `gestion_stock_dclic`
--
CREATE DATABASE IF NOT EXISTS `gestion_stock_dclic` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci;
USE `gestion_stock_dclic`;
--
-- Base de données : `project`
--
CREATE DATABASE IF NOT EXISTS `project` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci;
USE `project`;

-- --------------------------------------------------------

--
-- Structure de la table `users`
--

DROP TABLE IF EXISTS `users`;
CREATE TABLE IF NOT EXISTS `users` (
  `id` int NOT NULL AUTO_INCREMENT,
  `first_name` varchar(100) NOT NULL,
  `last_name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `speciality` varchar(100) DEFAULT NULL,
  `password` varchar(100) NOT NULL,
  `is_approved` tinyint(1) DEFAULT '0',
  `user_role` enum('user','admin') DEFAULT 'user',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `users`
--

INSERT INTO `users` (`id`, `first_name`, `last_name`, `email`, `speciality`, `password`, `is_approved`, `user_role`, `created_at`) VALUES
(1, 'Admin', 'User', 'admin@example.com', 'Administration', '', 1, 'admin', '2025-03-27 04:28:43');
--
-- Base de données : `shop_db`
--
CREATE DATABASE IF NOT EXISTS `shop_db` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci;
USE `shop_db`;

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
  `quantity_delivered` int DEFAULT '0',
  `date_validation` datetime DEFAULT NULL,
  `department_id` int DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=127 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `cart`
--

INSERT INTO `cart` (`id`, `user_id`, `product_id`, `name`, `image`, `quantity`, `allotted_quantity`, `status`, `date_commande`, `created_at`, `status_updated_at`, `quantity_delivered`, `date_validation`, `department_id`) VALUES
(119, 76, 178, 'pen', 'images/681b9a4d7d524.jpg', 10, 5, 'validée', '2025-05-07', '2025-05-07 17:39:42', '2025-05-07 17:43:14', 0, NULL, NULL),
(120, 76, 182, 'scissors', 'images/681b9a8dc13e8.jpg', 5, 2, 'validée', '2025-05-07', '2025-05-07 17:39:48', '2025-05-07 23:21:06', 0, NULL, NULL),
(121, 85, 168, 'computer', 'images/681b9994e874e.jpg', 2, NULL, 'Not Approved', '2025-05-07', '2025-05-07 17:41:52', '2025-05-07 17:42:30', 0, NULL, NULL),
(122, 85, 170, 'keyboard', 'images/681b9a370b886.jpg', 2, NULL, 'Not Approved', '2025-05-07', '2025-05-07 17:41:56', '2025-05-07 17:42:30', 0, NULL, NULL),
(123, 85, 167, 'printer', 'images/681b996b99792.jpg', 1, NULL, 'Not Approved', '2025-05-07', '2025-05-07 17:42:01', '2025-05-07 17:42:30', 0, NULL, NULL),
(124, 85, 174, 'pen', 'images/681b9a4d7d524.jpg', 6, NULL, 'Not Approved', '2025-05-07', '2025-05-07 17:42:10', '2025-05-07 17:42:30', 0, NULL, NULL),
(125, 85, 169, 'mouse', 'images/681b9a0d19ffe.jpg', 3, NULL, 'Not Approved', '2025-05-07', '2025-05-07 17:42:20', '2025-05-07 17:42:30', 0, NULL, NULL),
(126, 66, 172, 'pen', 'images/681b9a4d7d524.jpg', 10, NULL, 'rejetée', '2025-05-07', '2025-05-07 23:22:37', '2025-05-07 23:23:46', 0, NULL, NULL);

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
(1, '', '', 'Computer_Science'),
(3, '', '', 'Physics'),
(4, '', '', 'Chemistry'),
(7, '', '', 'biologie'),
(8, '', '', 'mathematics'),
(9, '', '', 'library'),
(18, '', '', 'Agronomy'),
(19, '', '', 'Sports Science (STAPS)'),
(20, '', '', 'Medicine'),
(21, '', '', 'Science & Technology'),
(22, '', '', 'Public administration');

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
-- Structure de la table `orders_archive`
--

DROP TABLE IF EXISTS `orders_archive`;
CREATE TABLE IF NOT EXISTS `orders_archive` (
  `id` int NOT NULL AUTO_INCREMENT,
  `original_order_id` int DEFAULT NULL,
  `user_id` int DEFAULT NULL,
  `product_id` int DEFAULT NULL,
  `quantity` int DEFAULT NULL,
  `allotted_quantity` int DEFAULT NULL,
  `status` varchar(50) DEFAULT NULL,
  `status_updated_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `archived_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
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
  `department` varchar(100) NOT NULL DEFAULT '',
  `stock` int DEFAULT '0',
  `department_id` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=184 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `products`
--

INSERT INTO `products` (`id`, `name`, `image`, `speciality`, `quantity`, `department`, `stock`, `department_id`, `created_at`) VALUES
(167, 'printer', 'images/681b996b99792.jpg', 'Computer_Science', 20, '', 0, NULL, '2025-05-07 17:33:31'),
(168, 'computer', 'images/681b9994e874e.jpg', 'Computer_Science', 4, '', 0, NULL, '2025-05-07 17:34:12'),
(169, 'mouse', 'images/681b9a0d19ffe.jpg', 'Computer_Science', 50, '', 0, NULL, '2025-05-07 17:36:13'),
(170, 'keyboard', 'images/681b9a370b886.jpg', 'Computer_Science', 60, '', 0, NULL, '2025-05-07 17:36:55'),
(171, 'pen', 'images/681b9a4d7d524.jpg', 'Agronomy', 100, '', 0, NULL, '2025-05-07 17:37:17'),
(172, 'pen', 'images/681b9a4d7d524.jpg', 'biologie', 100, '', 0, NULL, '2025-05-07 17:37:17'),
(173, 'pen', 'images/681b9a4d7d524.jpg', 'Chemistry', 100, '', 0, NULL, '2025-05-07 17:37:17'),
(174, 'pen', 'images/681b9a4d7d524.jpg', 'Computer_Science', 100, '', 0, NULL, '2025-05-07 17:37:17'),
(175, 'pen', 'images/681b9a4d7d524.jpg', 'library', 100, '', 0, NULL, '2025-05-07 17:37:17'),
(176, 'pen', 'images/681b9a4d7d524.jpg', 'mathematics', 100, '', 0, NULL, '2025-05-07 17:37:17'),
(177, 'pen', 'images/681b9a4d7d524.jpg', 'Medicine', 100, '', 0, NULL, '2025-05-07 17:37:17'),
(178, 'pen', 'images/681b9a4d7d524.jpg', 'Physics', 95, '', 0, NULL, '2025-05-07 17:37:17'),
(179, 'pen', 'images/681b9a4d7d524.jpg', 'Public Administration ', 100, '', 0, NULL, '2025-05-07 17:37:17'),
(180, 'pen', 'images/681b9a4d7d524.jpg', 'Science & Technology', 100, '', 0, NULL, '2025-05-07 17:37:17'),
(181, 'pen', 'images/681b9a4d7d524.jpg', 'Sports Science (STAPS)', 100, '', 0, NULL, '2025-05-07 17:37:17'),
(182, 'scissors', 'images/681b9a8dc13e8.jpg', 'Physics', 38, '', 0, NULL, '2025-05-07 17:38:21'),
(183, 'netebook', 'images/681b9f6bd0fa9.jpg', 'Public administration', 20, '', 0, NULL, '2025-05-07 17:59:07');

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
) ENGINE=InnoDB AUTO_INCREMENT=86 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `user_info`
--

INSERT INTO `user_info` (`id`, `name`, `email`, `password`, `approved`, `type`, `speciality`, `status`, `registration_date`, `approval_date`, `is_active`, `department_id`) VALUES
(12, 'sabrina benckemache', 'sabrina@gmaiil.com', '00e45749508fe15ca1af3397eab8db78', 1, 'admin', '', 'approved', '2025-04-18 14:17:20', NULL, 1, NULL),
(66, 'ROUFAIDA KANOUN', 'roufaidakanoun52@gmail.com', '7312438587983da6877a56e96088f271', 1, 'user', 'biologie', 'pending', '2025-04-30 18:24:08', '2025-04-30 18:25:06', 1, NULL),
(76, 'ferial', 'ferialchettab22@gmail.com', '38509af90339cbad729a571331e720bc', 1, 'user', 'Physics', 'pending', '2025-04-30 21:00:55', '2025-04-30 21:02:44', 1, NULL),
(78, 'mustapha', 'mostafakanoun79@gmail.com', '83f524549cb31ef48c790091e87405b2', 1, 'user', 'Mathematics', 'pending', '2025-05-01 12:38:31', '2025-05-01 12:38:48', 1, NULL),
(79, 'allaa', 'allakanoun943@gmail.com', 'd06f3a6fbdcb0d6d860a3e04682dbdf3', 1, 'user', 'sports science (STAPS)', 'pending', '2025-05-01 12:40:47', '2025-05-01 12:41:06', 1, NULL),
(83, 'oussma', 'ousshcn34@gmail.com', '3910dd2d465bc1bc499d47c7ca86d435', 1, 'user', 'Public administration', 'pending', '2025-05-01 15:12:33', '2025-05-01 15:13:16', 1, NULL),
(85, 'mahdi ', 'yahiaouimahdi43@gmail.com', 'f9c24b8f961d48841a9838cca5274d8d', 1, 'user', 'Computer_Science', 'pending', '2025-05-02 21:38:49', '2025-05-02 21:39:53', 1, NULL);
--
-- Base de données : `test_data`
--
CREATE DATABASE IF NOT EXISTS `test_data` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci;
USE `test_data`;
--
-- Base de données : `user_database`
--
CREATE DATABASE IF NOT EXISTS `user_database` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci;
USE `user_database`;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
