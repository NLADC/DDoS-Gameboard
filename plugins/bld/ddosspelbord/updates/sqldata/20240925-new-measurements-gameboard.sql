-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Gegenereerd op: 25 sep 2024 om 15:17
-- Serverversie: 10.11.6-MariaDB
-- PHP-versie: 8.0.28

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `gameboard`
--

DROP TABLE IF EXISTS `bld_ddosspelbord_measurements`;
CREATE TABLE `bld_ddosspelbord_measurements` (
  `id` int(10) UNSIGNED NOT NULL,
  `target_id` bigint(20) UNSIGNED DEFAULT NULL,
  `ipv` varchar(10) DEFAULT NULL,
  `timestamp` datetime DEFAULT NULL,
  `responsetime` double DEFAULT NULL,
  `measurement_api_data_id` bigint(20) UNSIGNED DEFAULT NULL,
  `number_of_probes` int(11) DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `bld_ddosspelbord_measurements`
--
ALTER TABLE `bld_ddosspelbord_measurements`
  ADD PRIMARY KEY (`id`),
  ADD KEY `bld_ddosspelbord_measurements_timestamp_target_id_index` (`timestamp`,`target_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `bld_ddosspelbord_measurements`
--
ALTER TABLE `bld_ddosspelbord_measurements`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;


-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `bld_ddosspelbord_measurement_api`
--

DROP TABLE IF EXISTS `bld_ddosspelbord_measurement_api`;
CREATE TABLE `bld_ddosspelbord_measurement_api` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `modulename` varchar(255) DEFAULT NULL,
  `configjson` text DEFAULT NULL,
  `apikey` varchar(255) DEFAULT NULL,
  `billingemail` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `measurement_type_id` int(10) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `bld_ddosspelbord_measurement_api_data`
--

DROP TABLE IF EXISTS `bld_ddosspelbord_measurement_api_data`;
CREATE TABLE `bld_ddosspelbord_measurement_api_data` (
  `id` int(10) UNSIGNED NOT NULL,
  `measurement_api_id` int(10) UNSIGNED DEFAULT NULL,
  `target_id` int(10) UNSIGNED DEFAULT NULL,
  `datajson` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `bld_ddosspelbord_measurement_nodes`
--

DROP TABLE IF EXISTS `bld_ddosspelbord_measurement_nodes`;
CREATE TABLE `bld_ddosspelbord_measurement_nodes` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `bld_ddosspelbord_measurement_node_pivot`
--

DROP TABLE IF EXISTS `bld_ddosspelbord_measurement_node_pivot`;
CREATE TABLE `bld_ddosspelbord_measurement_node_pivot` (
  `id` int(10) UNSIGNED NOT NULL,
  `measurement_type_id` int(10) UNSIGNED DEFAULT NULL,
  `measurement_nodelist_id` int(10) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `bld_ddosspelbord_nodelists`
--

DROP TABLE IF EXISTS `bld_ddosspelbord_nodelists`;
CREATE TABLE `bld_ddosspelbord_nodelists` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Indexen voor geëxporteerde tabellen
--

--
-- Indexen voor tabel `bld_ddosspelbord_measurement_api`
--
ALTER TABLE `bld_ddosspelbord_measurement_api`
  ADD PRIMARY KEY (`id`);

--
-- Indexen voor tabel `bld_ddosspelbord_measurement_api_data`
--
ALTER TABLE `bld_ddosspelbord_measurement_api_data`
  ADD PRIMARY KEY (`id`);

--
-- Indexen voor tabel `bld_ddosspelbord_measurement_nodes`
--
ALTER TABLE `bld_ddosspelbord_measurement_nodes`
  ADD PRIMARY KEY (`id`);

--
-- Indexen voor tabel `bld_ddosspelbord_measurement_node_pivot`
--
ALTER TABLE `bld_ddosspelbord_measurement_node_pivot`
  ADD PRIMARY KEY (`id`);

--
-- Indexen voor tabel `bld_ddosspelbord_nodelists`
--
ALTER TABLE `bld_ddosspelbord_nodelists`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT voor geëxporteerde tabellen
--

--
-- AUTO_INCREMENT voor een tabel `bld_ddosspelbord_measurement_api`
--
ALTER TABLE `bld_ddosspelbord_measurement_api`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT voor een tabel `bld_ddosspelbord_measurement_api_data`
--
ALTER TABLE `bld_ddosspelbord_measurement_api_data`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT voor een tabel `bld_ddosspelbord_measurement_nodes`
--
ALTER TABLE `bld_ddosspelbord_measurement_nodes`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT voor een tabel `bld_ddosspelbord_measurement_node_pivot`
--
ALTER TABLE `bld_ddosspelbord_measurement_node_pivot`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT voor een tabel `bld_ddosspelbord_nodelists`
--
ALTER TABLE `bld_ddosspelbord_nodelists`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
