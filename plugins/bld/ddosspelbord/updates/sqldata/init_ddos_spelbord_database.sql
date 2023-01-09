--
-- Tabelstructuur voor tabel `attacks`
--
DROP TABLE IF EXISTS `bld_ddosspelbord_attacks`;
CREATE TABLE `bld_ddosspelbord_attacks` (
                                            `id` bigint(20) UNSIGNED NOT NULL,
                                            `name` varchar(191) COLLATE utf8mb4_unicode_ci NULL,
                                            `party_id` bigint(20) UNSIGNED DEFAULT NULL,
                                            `user_id` bigint(20) UNSIGNED DEFAULT NULL,
                                            `status` varchar(191) COLLATE utf8mb4_unicode_ci NULL,
                                            `timestamp` datetime NULL,
                                            `created_at` timestamp NULL DEFAULT NULL,
                                            `updated_at` timestamp NULL DEFAULT NULL,
                                            `deleted_at` timestamp NULL DEFAULT NULL

) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Tabelstructuur voor tabel `actions`
--
DROP TABLE IF EXISTS `bld_ddosspelbord_actions`;
CREATE TABLE `bld_ddosspelbord_actions` (
                                            `id` bigint(20) UNSIGNED NOT NULL,
                                            `party_id` bigint(20) UNSIGNED DEFAULT NULL,
                                            `name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
                                            `description` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
                                            `tag` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
                                            `start` datetime NOT NULL,
                                            `length` int(10) UNSIGNED NOT NULL DEFAULT '0',
                                            `delay` int(10) NOT NULL DEFAULT '0',
                                            `extension` int(10) UNSIGNED NOT NULL DEFAULT '0',
                                            `has_issues` tinyint(3) NOT NULL DEFAULT '0',
                                            `is_cancelled` tinyint(3) NOT NULL DEFAULT '0',
                                            `highlight` varchar(6) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
                                            `created_at` timestamp NULL DEFAULT NULL,
                                            `updated_at` timestamp NULL DEFAULT NULL,
                                            `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


--
-- Tabelstructuur voor tabel `logs`
--
DROP TABLE IF EXISTS `bld_ddosspelbord_logs`;
CREATE TABLE `bld_ddosspelbord_logs` (
                                         `id` bigint(20) UNSIGNED NOT NULL,
                                         `user_id` bigint(20) UNSIGNED NULL,
                                         `log` text COLLATE utf8mb4_unicode_ci NULL,
                                         `timestamp` datetime NULL,
                                         `created_at` timestamp NULL DEFAULT NULL,
                                         `updated_at` timestamp NULL DEFAULT NULL,
                                         `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `parties`
--
DROP TABLE IF EXISTS `bld_ddosspelbord_parties`;
CREATE TABLE `bld_ddosspelbord_parties` (
                                            `id` bigint(20) UNSIGNED NOT NULL,
                                            `name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
                                            `logo` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
                                            `created_at` timestamp NULL DEFAULT NULL,
                                            `updated_at` timestamp NULL DEFAULT NULL,
                                            `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


INSERT INTO `bld_ddosspelbord_parties` (`id`, `name`, `logo`, `created_at`, `updated_at`) VALUES
                                                                                              (1, 'Belastingdienst', '', '2021-08-12 06:41:00', '2021-08-12 06:41:00'),
                                                                                              (2, 'SSC-ICT', '', '2021-08-12 06:41:00', '2021-08-12 06:41:00'),
                                                                                              (3, 'SIDN', '', '2021-08-12 06:41:00', '2021-08-12 06:41:00'),
                                                                                              (4, 'Logius', '', '2021-08-12 06:41:00', '2021-08-12 06:41:00'),
                                                                                              (5, 'Rijkswaterstaat', '', NULL, '2022-04-04 11:42:17'),
                                                                                              (6, 'AntiDDoS Coalitie', '', NULL, '2022-04-05 11:35:26');


-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `roles`
--

DROP TABLE IF EXISTS bld_ddosspelbord_roles;
CREATE TABLE `bld_ddosspelbord_roles` (
                                          `id` bigint(20) UNSIGNED NOT NULL,
                                          `name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
                                          `display_name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
                                          `created_at` timestamp NULL DEFAULT NULL,
                                          `updated_at` timestamp NULL DEFAULT NULL,
                                          `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Gegevens worden geëxporteerd voor tabel `roles`
--

INSERT INTO `bld_ddosspelbord_roles` (`id`, `name`, `display_name`, `created_at`, `updated_at`) VALUES
                                                                                                    (1, 'purple', 'PURPLE team', NULL, NULL),
                                                                                                    (2, 'blue', 'BLUE TEAM', NULL, NULL),
                                                                                                    (3, 'red', 'RED TEAM', NULL, NULL),
                                                                                                    (4, 'observer', 'Observer', NULL, NULL);

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `spelbordusers`
--

DROP TABLE IF EXISTS bld_ddosspelbord_users;
CREATE TABLE `bld_ddosspelbord_users` (
                                          `id` bigint(20) UNSIGNED NOT NULL,
                                          `user_id` bigint(20) UNSIGNED NOT NULL,
                                          `role_id` bigint(20) UNSIGNED DEFAULT NULL,
                                          `party_id` bigint(20) UNSIGNED NOT NULL,
                                          `name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
                                          `email` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
                                          `avatar` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT 'users/default.png',
                                          `email_verified_at` timestamp NULL DEFAULT NULL,
                                          `password` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
                                          `api_token` varchar(80) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                          `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                          `settings` text COLLATE utf8mb4_unicode_ci,
                                          `heartbeat` datetime NULL,
                                          `created_at` timestamp NULL DEFAULT NULL,
                                          `updated_at` timestamp NULL DEFAULT NULL,
                                          `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `transactions`
--
DROP TABLE IF EXISTS bld_ddosspelbord_transactions;
CREATE TABLE `bld_ddosspelbord_transactions` (
                                                 `id` bigint(20) UNSIGNED NOT NULL,
                                                 `hash` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL,
                                                 `type` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
                                                 `data` text COLLATE utf8mb4_unicode_ci NOT NULL,
                                                 `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                                                 `updated_at` timestamp NULL DEFAULT NULL,
                                                 `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Indexen voor geëxporteerde tabellen
--

--
-- Indexen voor tabel `actions`
--
ALTER TABLE `bld_ddosspelbord_actions`
    ADD PRIMARY KEY (`id`);


--
-- Indexen voor tabel `attacks`
--
ALTER TABLE `bld_ddosspelbord_attacks`
    ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT voor geëxporteerde tabellen
--

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

--
-- AUTO_INCREMENT voor geëxporteerde tabellen
--

--
-- AUTO_INCREMENT voor een tabel `actions`
--
ALTER TABLE `bld_ddosspelbord_actions`
    MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT voor een tabel `attacks`
--
ALTER TABLE `bld_ddosspelbord_attacks`
    MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;


--
-- AUTO_INCREMENT voor een tabel `logs`
--
ALTER TABLE `bld_ddosspelbord_logs`
    MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT voor een tabel `parties`
--
ALTER TABLE `bld_ddosspelbord_parties`
    MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT voor een tabel `roles`
--
ALTER TABLE `bld_ddosspelbord_roles`
    MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT voor een tabel `spelbordusers`
--
ALTER TABLE `bld_ddosspelbord_users`
    MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT voor een tabel `transactions`
--
ALTER TABLE `bld_ddosspelbord_transactions`
    MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

