/*
 * Copyright (C) 2024 Anti-DDoS Coalitie Netherlands (ADC-NL)
 *
 * This file is part of the DDoS gameboard.
 *
 * DDoS gameboard is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * DDoS gameboard is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; If not, see @link https://www.gnu.org/licenses/.
 *
 */

--
-- Tabelstructuur voor tabel `attacks`
--
DROP TABLE IF EXISTS `bld_ddosspelbord_attacks`;

CREATE TABLE `bld_ddosspelbord_attacks` (
                                            `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
                                            `name` varchar(999) DEFAULT NULL,
                                            `party_id` bigint(20) unsigned DEFAULT NULL,
                                            `user_id` bigint(20) unsigned DEFAULT NULL,
                                            `status` varchar(191) DEFAULT NULL,
                                            `created_at` timestamp NULL DEFAULT NULL,
                                            `updated_at` timestamp NULL DEFAULT NULL,
                                            `timestamp` datetime DEFAULT NULL,
                                            `deleted_at` timestamp NULL DEFAULT NULL,
                                            PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;



--
-- Tabelstructuur voor tabel `actions`
--
DROP TABLE IF EXISTS `bld_ddosspelbord_actions`;

CREATE TABLE `bld_ddosspelbord_actions` (
                                            `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                                            `party_id` bigint(20) unsigned DEFAULT NULL,
                                            `name` varchar(191) NOT NULL,
                                            `description` varchar(191) NOT NULL,
                                            `tag` varchar(191) NOT NULL,
                                            `start` datetime NOT NULL,
                                            `length` int(10) unsigned NOT NULL DEFAULT 0,
                                            `delay` int(11) NOT NULL DEFAULT 0,
                                            `extension` int(10) unsigned NOT NULL DEFAULT 0,
                                            `has_issues` tinyint(1) NOT NULL DEFAULT 0,
                                            `is_cancelled` tinyint(1) NOT NULL DEFAULT 0,
                                            `highlight` varchar(6) NOT NULL,
                                            `created_at` timestamp NULL DEFAULT NULL,
                                            `updated_at` timestamp NULL DEFAULT NULL,
                                            `deleted_at` timestamp NULL DEFAULT NULL,
                                            PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


--
-- Tabelstructuur voor tabel `logs`
--
DROP TABLE IF EXISTS `bld_ddosspelbord_logs`;

CREATE TABLE `bld_ddosspelbord_logs` (
                                         `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                                         `user_id` bigint(20) unsigned DEFAULT NULL,
                                         `log` text DEFAULT NULL,
                                         `timestamp` datetime DEFAULT NULL,
                                         `created_at` timestamp NULL DEFAULT NULL,
                                         `updated_at` timestamp NULL DEFAULT NULL,
                                         `deleted_at` timestamp NULL DEFAULT NULL,
                                         PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `parties`
--
DROP TABLE IF EXISTS `bld_ddosspelbord_parties`;

CREATE TABLE `bld_ddosspelbord_parties` (
                                            `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                                            `name` varchar(191) NOT NULL,
                                            `logo` varchar(191) NOT NULL,
                                            `created_at` timestamp NULL DEFAULT NULL,
                                            `updated_at` timestamp NULL DEFAULT NULL,
                                            `deleted_at` timestamp NULL DEFAULT NULL,
                                            PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `roles`
--

DROP TABLE IF EXISTS `bld_ddosspelbord_roles`;

CREATE TABLE `bld_ddosspelbord_roles` (
                                          `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                                          `name` varchar(191) NOT NULL,
                                          `display_name` varchar(191) NOT NULL,
                                          `created_at` timestamp NULL DEFAULT NULL,
                                          `updated_at` timestamp NULL DEFAULT NULL,
                                          `deleted_at` timestamp NULL DEFAULT NULL,
                                          PRIMARY KEY (`id`),
                                          UNIQUE KEY `roles_name_unique` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;



-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `spelbordusers`
--

DROP TABLE IF EXISTS `bld_ddosspelbord_users`;

CREATE TABLE `bld_ddosspelbord_users` (
                                          `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                                          `user_id` bigint(20) unsigned NOT NULL,
                                          `role_id` bigint(20) unsigned DEFAULT NULL,
                                          `party_id` bigint(20) unsigned NOT NULL,
                                          `name` varchar(191) NOT NULL,
                                          `email` varchar(191) NOT NULL,
                                          `avatar` varchar(191) DEFAULT 'users/default.png',
                                          `email_verified_at` timestamp NULL DEFAULT NULL,
                                          `password` varchar(191) NOT NULL,
                                          `api_token` varchar(80) DEFAULT NULL,
                                          `remember_token` varchar(100) DEFAULT NULL,
                                          `settings` text DEFAULT NULL,
                                          `created_at` timestamp NULL DEFAULT NULL,
                                          `updated_at` timestamp NULL DEFAULT NULL,
                                          `heartbeat` datetime DEFAULT NULL,
                                          `deleted_at` timestamp NULL DEFAULT NULL,
                                          PRIMARY KEY (`id`),
                                          UNIQUE KEY `users_email_unique` (`email`),
                                          UNIQUE KEY `users_api_token_unique` (`api_token`),
                                          KEY `users_role_id_foreign` (`role_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `transactions`
--
DROP TABLE IF EXISTS `bld_ddosspelbord_transactions`;

CREATE TABLE `bld_ddosspelbord_transactions` (
                                                 `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                                                 `hash` varchar(32) NOT NULL,
                                                 `type` varchar(191) NOT NULL,
                                                 `data` text NOT NULL,
                                                 `created_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
                                                 `updated_at` timestamp NULL DEFAULT NULL,
                                                 `deleted_at` timestamp NULL DEFAULT NULL,
                                                 PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Tabelstructuur voor alle Target Measurements
--


DROP TABLE IF EXISTS `bld_ddosspelbord_targets`;

CREATE TABLE `bld_ddosspelbord_targets` (
                                            `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
                                            `created_at` timestamp NULL DEFAULT NULL,
                                            `updated_at` timestamp NULL DEFAULT NULL,
                                            `deleted_at` timestamp NULL DEFAULT NULL,
                                            `name` varchar(191) DEFAULT NULL,
                                            `target` varchar(191) DEFAULT NULL,
                                            `ipv` varchar(191) DEFAULT NULL,
                                            `measurement_api_id` int(10) unsigned DEFAULT NULL,
                                            `type` varchar(40) NOT NULL,
                                            `party_id` int(10) unsigned NOT NULL,
                                            `enabled` tinyint(1) NOT NULL DEFAULT 0,
                                            `threshold_orange` double NOT NULL DEFAULT 0,
                                            `threshold_red` double NOT NULL DEFAULT 0,
                                            `groups` varchar(255) NOT NULL,
                                            PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Tabelstructuur voor tabel `Target Groups`
--

DROP TABLE IF EXISTS `bld_ddosspelbord_measurements`;

CREATE TABLE `bld_ddosspelbord_measurements` (
                                                 `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
                                                 `target_id` int(10) unsigned DEFAULT NULL,
                                                 `ipv` varchar(10) DEFAULT NULL,
                                                 `timestamp` datetime DEFAULT NULL,
                                                 `responsetime` double DEFAULT NULL,
                                                 `measurement_api_data_id` int(10) unsigned DEFAULT NULL,
                                                 `number_of_probes` int(11) NOT NULL DEFAULT 0,
                                                 PRIMARY KEY (`id`),
                                                 KEY `timestamp_target` (`timestamp`,`target_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `bld_ddosspelbord_target_groups`;

CREATE TABLE `bld_ddosspelbord_target_groups` (
                                                  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
                                                  `created_at` timestamp NULL DEFAULT NULL,
                                                  `updated_at` timestamp NULL DEFAULT NULL,
                                                  `deleted_at` timestamp NULL DEFAULT NULL,
                                                  `name` varchar(255) NOT NULL,
                                                  `sortnr` int(11) NOT NULL DEFAULT 1,
                                                  `graphresponsetimeclipvalue` double NOT NULL DEFAULT 200,
                                                  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `bld_ddosspelbord_measurement_api`;

CREATE TABLE `bld_ddosspelbord_measurement_api` (
                                                    `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
                                                    `created_at` timestamp NULL DEFAULT NULL,
                                                    `updated_at` timestamp NULL DEFAULT NULL,
                                                    `deleted_at` timestamp NULL DEFAULT NULL,
                                                    `name` varchar(255) NOT NULL,
                                                    `description` text NOT NULL,
                                                    `modulename` varchar(255) NOT NULL,
                                                    `configjson` text NOT NULL,
                                                    `type` varchar(40) NOT NULL DEFAULT 'website',
                                                    `apikey` varchar(255) DEFAULT NULL,
                                                    `billingemail` varchar(255) DEFAULT NULL,
                                                    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `bld_ddosspelbord_measurement_api_data`;

CREATE TABLE `bld_ddosspelbord_measurement_api_data` (
                                                         `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
                                                         `created_at` timestamp NULL DEFAULT NULL,
                                                         `updated_at` timestamp NULL DEFAULT NULL,
                                                         `deleted_at` timestamp NULL DEFAULT NULL,
                                                         `measurement_api_id` int(10) unsigned NOT NULL,
                                                         `datajson` text NOT NULL,
                                                         `target_id` int(10) unsigned NOT NULL,
                                                         `start_at` datetime DEFAULT NULL,
                                                         `end_at` datetime DEFAULT NULL,
                                                         PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;



--
-- Indexen voor tabel `logs`
--
ALTER TABLE `bld_ddosspelbord_logs`
    ADD PRIMARY KEY (`id`);

--
-- Indexen voor tabel `parties`
--
ALTER TABLE `bld_ddosspelbord_parties`
    ADD PRIMARY KEY (`id`);

--
-- Indexen voor tabel `roles`
--
ALTER TABLE `bld_ddosspelbord_roles`
    ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `roles_name_unique` (`name`);

--
-- Indexen voor tabel `spelbordusers`
--
ALTER TABLE `bld_ddosspelbord_users`
    ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_email_unique` (`email`),
  ADD UNIQUE KEY `users_api_token_unique` (`api_token`),
  ADD KEY `users_role_id_foreign` (`role_id`);

--
-- Indexen voor tabel `transactions`
--
ALTER TABLE `bld_ddosspelbord_transactions`
    ADD PRIMARY KEY (`id`);


