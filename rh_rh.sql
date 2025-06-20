-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 22, 2025 at 04:26 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `rh_rh`
--

-- --------------------------------------------------------

--
-- Table structure for table `demandes_conge`
--

CREATE TABLE `demandes_conge` (
  `id` int(11) NOT NULL,
  `utilisateur_id` int(11) NOT NULL,
  `date_debut` date NOT NULL,
  `date_fin` date NOT NULL,
  `type_conge` varchar(50) NOT NULL,
  `raison` text DEFAULT NULL,
  `statut` enum('en_attente','approuve','refuse') DEFAULT 'en_attente',
  `date_demande` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `demandes_conge`
--

INSERT INTO `demandes_conge` (`id`, `utilisateur_id`, `date_debut`, `date_fin`, `type_conge`, `raison`, `statut`, `date_demande`) VALUES
(7, 9, '2025-05-21', '2025-05-31', 'Maladie', 'je suis gravement malade ', 'refuse', '2025-05-21 18:38:54'),
(9, 9, '2025-05-22', '2025-05-31', 'Maladie', '', 'refuse', '2025-05-22 15:05:32'),
(10, 9, '2025-05-30', '2025-06-19', 'Congé annuel', '', 'refuse', '2025-05-22 15:05:59');

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `utilisateur_id` int(11) NOT NULL,
  `message` text NOT NULL,
  `lien` varchar(255) DEFAULT NULL,
  `vue` tinyint(1) DEFAULT 0,
  `date_notification` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`id`, `utilisateur_id`, `message`, `lien`, `vue`, `date_notification`) VALUES
(8, 9, 'Votre demande de congé a été refusée', NULL, 0, '2025-05-21 18:40:01'),
(9, 9, 'Votre demande de congé a été refusée', NULL, 0, '2025-05-22 15:09:15'),
(10, 9, 'Votre demande de congé a été refusée', NULL, 0, '2025-05-22 15:09:17');

-- --------------------------------------------------------

--
-- Table structure for table `utilisateurs`
--

CREATE TABLE `utilisateurs` (
  `id` int(11) NOT NULL,
  `nom` varchar(50) NOT NULL,
  `prenom` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','user') DEFAULT 'user',
  `date_inscription` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `utilisateurs`
--

INSERT INTO `utilisateurs` (`id`, `nom`, `prenom`, `email`, `password`, `role`, `date_inscription`) VALUES
(8, 'system', 'admin', 'admin@gmail.com', '$2y$10$T8YtclXeFNpN43YsHTt8LOvDq25.g5cKWxnCA3W2MkycyMUXYd3Va', 'admin', '2025-05-21 18:36:02'),
(9, 'oussama', 'aoudjit', 'oussamaaoudjit@gmail.com', '$2y$10$o1WNMxU1RNV/RJlvyqJTEeLdna5eqp6557CgEJQJ42bK2nEkCmZyK', 'user', '2025-05-21 18:38:37'),
(11, 'Admin', 'System', 'admin@rh.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', '2025-04-18 17:20:06');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `demandes_conge`
--
ALTER TABLE `demandes_conge`
  ADD PRIMARY KEY (`id`),
  ADD KEY `utilisateur_id` (`utilisateur_id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `utilisateur_id` (`utilisateur_id`);

--
-- Indexes for table `utilisateurs`
--
ALTER TABLE `utilisateurs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `demandes_conge`
--
ALTER TABLE `demandes_conge`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `utilisateurs`
--
ALTER TABLE `utilisateurs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `demandes_conge`
--
ALTER TABLE `demandes_conge`
  ADD CONSTRAINT `demandes_conge_ibfk_1` FOREIGN KEY (`utilisateur_id`) REFERENCES `utilisateurs` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`utilisateur_id`) REFERENCES `utilisateurs` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
