
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
-- Database: `gameboard`
--

--
-- Gegevens worden geëxporteerd voor tabel `actions`
--

TRUNCATE bld_ddosspelbord_actions;

INSERT INTO `bld_ddosspelbord_actions` (`id`, `party_id`, `name`, `description`, `tag`, `start`, `length`, `delay`, `extension`, `has_issues`, `is_cancelled`, `highlight`, `created_at`, `updated_at`) VALUES
(1, 1, 'TESTING', 'Start + Comm check', 'RED_0', '2021-10-09 02:50:00', 600, 0, 0, 0, 0, '', '2021-10-07 08:12:40', '2021-10-08 08:27:04'),
(2, 2, '', 'Start + Comm check', 'RED_0', '2021-10-09 02:50:00', 600, 0, 0, 0, 0, '', '2021-10-07 08:12:40', '2021-10-07 08:12:40'),
(3, 3, '', 'Start + Comm check', 'RED_0', '2021-10-09 02:50:00', 600, 0, 0, 0, 0, '', '2021-10-07 08:12:40', '2021-10-07 08:12:40'),
(4, 4, '', 'Start + Comm check', 'RED_0', '2021-10-09 02:50:00', 600, 0, 0, 0, 0, '', '2021-10-07 08:12:40', '2021-10-07 08:12:40'),
(5, 5, '', 'Start + Comm check', 'RED_0', '2021-10-09 02:50:00', 600, 0, 0, 0, 0, '', '2021-10-07 08:12:40', '2021-10-07 08:12:40'),
(6, 6, '', 'Start + Comm check', 'RED_0', '2021-10-09 02:50:00', 600, 0, 0, 0, 0, '', '2021-10-07 08:12:40', '2021-10-07 08:12:40'),
(7, 1, 'aanval - inloggen EH (SAML)', 'Henri & Michel', 'RED_1', '2021-10-09 03:00:00', 900, 0, 0, 0, 0, '', '2021-10-07 08:12:40', '2021-10-07 08:12:40'),
(8, 1, 'aanval - inloggen EH (SAML)', 'Henri & Michel', 'RED_2', '2021-10-09 03:15:00', 900, 0, 0, 0, 0, '', '2021-10-07 08:12:40', '2021-10-07 08:12:40'),
(9, 1, 'get-flood/post-flood op bezwaarportaal', 'Henri & Michel', 'RED_3', '2021-10-09 03:30:00', 900, 0, 0, 0, 0, '', '2021-10-07 08:12:40', '2021-10-07 08:12:40'),
(10, 1, 'get-flood/post-flood op bezwaarportaal', 'Henri & Michel', 'RED_4', '2021-10-09 03:45:00', 900, 0, 0, 0, 0, '', '2021-10-07 08:12:40', '2021-10-07 08:12:40'),
(11, 1, '', 'Pauze', 'RED_5', '2021-10-09 04:00:00', 900, 0, 0, 0, 0, '', '2021-10-07 08:12:40', '2021-10-07 08:12:40'),
(12, 1, 'sockets vol (via big-post/slow post aangifte)', 'Asif & Ernst', 'RED_6', '2021-10-09 04:15:00', 900, 0, 0, 0, 0, '', '2021-10-07 08:12:40', '2021-10-07 08:12:40'),
(13, 1, 'sockets vol (via big-post/slow post aangifte)', 'Asif & Ernst', 'RED_7', '2021-10-09 04:30:00', 900, 0, 0, 0, 0, '', '2021-10-07 08:12:40', '2021-10-07 08:12:40'),
(14, 1, 'get-flood/post-flood op bezwaarportaal', 'Asif & Ernst', 'RED_8', '2021-10-09 04:45:00', 900, 0, 0, 0, 0, '', '2021-10-07 08:12:40', '2021-10-07 08:12:40'),
(15, 1, 'get-flood/post-flood op bezwaarportaal', 'Asif & Ernst', 'RED_9', '2021-10-09 05:00:00', 900, 0, 0, 0, 0, '', '2021-10-07 08:12:40', '2021-10-07 08:12:40'),
(16, 1, '', 'Pauze', 'RED_10', '2021-10-09 05:15:00', 900, 0, 0, 0, 0, '', '2021-10-07 08:12:40', '2021-10-07 08:12:40'),
(17, 1, 'aanval - sessie limit id vol', 'Henri & Asif', 'RED_11', '2021-10-09 05:30:00', 900, 0, 0, 0, 0, '', '2021-10-07 08:12:40', '2021-10-07 08:12:40'),
(18, 1, 'aanval - sessie limit id vol', 'Michel & Ernst', 'RED_12', '2021-10-09 05:45:00', 900, 0, 0, 0, 0, '', '2021-10-07 08:12:40', '2021-10-07 08:12:40'),
(19, 1, 'Inloggen, zwaar klantbeeld ophalen', 'Ernst & Henri', 'RED_13', '2021-10-09 06:00:00', 900, 0, 0, 0, 0, '', '2021-10-07 08:12:40', '2021-10-07 08:12:40'),
(20, 1, 'Inloggen, zwaar klantbeeld ophalen', 'Michel & Asif', 'RED_14', '2021-10-09 06:15:00', 900, 0, 0, 0, 0, '', '2021-10-07 08:12:40', '2021-10-07 08:12:40'),
(21, 1, '', 'Pauze', 'RED_15', '2021-10-09 06:30:00', 900, 0, 0, 0, 0, '', '2021-10-07 08:12:40', '2021-10-07 08:12:40'),
(22, 1, 'DNS-WaterTorture', 'Anton', 'RED_16', '2021-10-09 06:45:00', 900, 0, 0, 0, 0, '', '2021-10-07 08:12:40', '2021-10-07 08:12:40'),
(23, 1, 'DNS-WaterTorture', 'Anton', 'RED_17', '2021-10-09 07:00:00', 900, 0, 0, 0, 0, '', '2021-10-07 08:12:40', '2021-10-07 08:12:40'),
(24, 1, 'DNS-WaterTorture', 'Anton', 'RED_18', '2021-10-09 07:15:00', 900, 0, 0, 0, 0, '', '2021-10-07 08:12:40', '2021-10-07 08:12:40'),
(25, 1, '', 'Game Over - Stop alle aanvallen', 'RED_19', '2021-10-09 07:30:00', 900, 0, 0, 0, 0, '', '2021-10-07 08:12:40', '2021-10-07 08:12:40'),
(26, 2, 'UDP 10G', 'Daniël', 'RED_1', '2021-10-09 03:00:00', 900, 0, 0, 0, 0, '', '2021-10-07 08:12:40', '2021-10-07 08:12:40'),
(27, 2, 'UDP/L 5G', 'Daniël', 'RED_2', '2021-10-09 03:15:00', 900, 0, 0, 0, 0, '', '2021-10-07 08:12:40', '2021-10-07 08:12:40'),
(28, 2, 'UDL/L 10G', 'Daniël', 'RED_3', '2021-10-09 03:30:00', 900, 0, 0, 0, 0, '', '2021-10-07 08:12:40', '2021-10-07 08:12:40'),
(29, 2, 'UDL/L 5G', 'Daniël', 'RED_4', '2021-10-09 03:45:00', 900, 0, 0, 0, 0, '', '2021-10-07 08:12:40', '2021-10-07 08:12:40'),
(30, 2, '', 'Pauze', 'RED_5', '2021-10-09 04:00:00', 900, 0, 0, 0, 0, '', '2021-10-07 08:12:40', '2021-10-07 08:12:40'),
(31, 2, 'TCP 10G', 'Daniël', 'RED_6', '2021-10-09 04:15:00', 900, 0, 0, 0, 0, '', '2021-10-07 08:12:40', '2021-10-07 08:12:40'),
(32, 2, 'TCP 5G', 'Daniël', 'RED_7', '2021-10-09 04:30:00', 900, 0, 0, 0, 0, '', '2021-10-07 08:12:40', '2021-10-07 08:12:40'),
(33, 2, 'TCP 10G', 'Daniël', 'RED_8', '2021-10-09 04:45:00', 900, 0, 0, 0, 0, '', '2021-10-07 08:12:40', '2021-10-07 08:12:40'),
(34, 2, 'UDP 10G', 'Daniël', 'RED_9', '2021-10-09 05:00:00', 900, 0, 0, 0, 0, '', '2021-10-07 08:12:40', '2021-10-07 08:12:40'),
(35, 2, '', 'Pauze', 'RED_10', '2021-10-09 05:15:00', 900, 0, 0, 0, 0, '', '2021-10-07 08:12:40', '2021-10-07 08:12:40'),
(36, 2, 'Kerstboom 5G', 'Daniël', 'RED_11', '2021-10-09 05:30:00', 900, 0, 0, 0, 0, '', '2021-10-07 08:12:40', '2021-10-07 08:12:40'),
(37, 2, 'Kerstboom 10G', 'Daniël', 'RED_12', '2021-10-09 05:45:00', 900, 0, 0, 0, 0, '', '2021-10-07 08:12:40', '2021-10-07 08:12:40'),
(38, 2, 'Kerstboom 10G', 'Daniël', 'RED_13', '2021-10-09 06:00:00', 900, 0, 0, 0, 0, '', '2021-10-07 08:12:40', '2021-10-07 08:12:40'),
(39, 2, 'Kerstboom 10G', 'Daniël', 'RED_14', '2021-10-09 06:15:00', 900, 0, 0, 0, 0, '', '2021-10-07 08:12:40', '2021-10-07 08:12:40'),
(40, 2, '', 'Pauze', 'RED_15', '2021-10-09 06:30:00', 900, 0, 0, 0, 0, '', '2021-10-07 08:12:40', '2021-10-07 08:12:40'),
(41, 2, 'Regenboog 5G', 'Daniël', 'RED_16', '2021-10-09 06:45:00', 900, 0, 0, 0, 0, '', '2021-10-07 08:12:40', '2021-10-07 08:12:40'),
(42, 2, 'Regenboog 10G', 'Daniël', 'RED_17', '2021-10-09 07:00:00', 900, 0, 0, 0, 0, '', '2021-10-07 08:12:40', '2021-10-07 08:12:40'),
(43, 2, 'Regenboog 10G', 'Daniël', 'RED_18', '2021-10-09 07:15:00', 900, 0, 0, 0, 0, '', '2021-10-07 08:12:40', '2021-10-07 08:12:40'),
(44, 2, '', 'Game Over - Stop alle aanvallen', 'RED_19', '2021-10-09 07:30:00', 900, 0, 0, 0, 0, '', '2021-10-07 08:12:40', '2021-10-07 08:12:40'),
(45, 3, 'Fileshare', 'Surf VMs', 'RED_1', '2021-10-09 03:00:00', 900, 0, 0, 0, 0, '', '2021-10-07 08:12:40', '2021-10-07 08:12:40'),
(46, 3, 'Fileshare', 'Surf VMs', 'RED_2', '2021-10-09 03:15:00', 900, 0, 0, 0, 0, '', '2021-10-07 08:12:40', '2021-10-07 08:12:40'),
(47, 3, 'RSC Live check', 'Surf VMs', 'RED_3', '2021-10-09 03:30:00', 900, 0, 0, 0, 0, '', '2021-10-07 08:12:40', '2021-10-07 08:12:40'),
(48, 3, 'RSC Live check', 'Surf VMs', 'RED_4', '2021-10-09 03:45:00', 900, 0, 0, 0, 0, '', '2021-10-07 08:12:40', '2021-10-07 08:12:40'),
(49, 3, '', 'Pauze', 'RED_5', '2021-10-09 04:00:00', 900, 0, 0, 0, 0, '', '2021-10-07 08:12:40', '2021-10-07 08:12:40'),
(50, 3, 'F5-DOS (portal2.sidn.nl)', 'Anton', 'RED_6', '2021-10-09 04:15:00', 900, 0, 0, 0, 0, '', '2021-10-07 08:12:40', '2021-10-07 08:12:40'),
(51, 3, 'F5-DOS (portal2.sidn.nl)', 'Anton', 'RED_7', '2021-10-09 04:30:00', 900, 0, 0, 0, 0, '', '2021-10-07 08:12:40', '2021-10-07 08:12:40'),
(52, 3, 'Regenboog extranet', 'Daniël', 'RED_8', '2021-10-09 04:45:00', 900, 0, 0, 0, 0, '', '2021-10-07 08:12:40', '2021-10-07 08:12:40'),
(53, 3, 'Regenboog extranet', 'Daniël', 'RED_9', '2021-10-09 05:00:00', 900, 0, 0, 0, 0, '', '2021-10-07 08:12:40', '2021-10-07 08:12:40'),
(54, 3, '', 'Pauze', 'RED_10', '2021-10-09 05:15:00', 900, 0, 0, 0, 0, '', '2021-10-07 08:12:40', '2021-10-07 08:12:40'),
(55, 3, 'IRMA', 'Surf VM', 'RED_11', '2021-10-09 05:30:00', 900, 0, 0, 0, 0, '', '2021-10-07 08:12:40', '2021-10-07 08:12:40'),
(56, 3, 'IRMA', 'Surf VM', 'RED_12', '2021-10-09 05:45:00', 900, 0, 0, 0, 0, '', '2021-10-07 08:12:40', '2021-10-07 08:12:40'),
(57, 3, 'Uitlokken (SIDN -> Nikhef)', 'Daniël', 'RED_13', '2021-10-09 06:00:00', 900, 0, 0, 0, 0, '', '2021-10-07 08:12:40', '2021-10-07 08:12:40'),
(58, 3, 'Uitlokken (SIDN -> Nikhef)', 'Daniël', 'RED_14', '2021-10-09 06:15:00', 900, 0, 0, 0, 0, '', '2021-10-07 08:12:40', '2021-10-07 08:12:40'),
(59, 3, '', 'Pauze', 'RED_15', '2021-10-09 06:30:00', 900, 0, 0, 0, 0, '', '2021-10-07 08:12:40', '2021-10-07 08:12:40'),
(60, 3, 'Jabber', 'Surf VM', 'RED_16', '2021-10-09 06:45:00', 900, 0, 0, 0, 0, '', '2021-10-07 08:12:40', '2021-10-07 08:12:40'),
(61, 3, 'Jabber', 'Surf VM', 'RED_17', '2021-10-09 07:00:00', 900, 0, 0, 0, 0, '', '2021-10-07 08:12:40', '2021-10-07 08:12:40'),
(62, 3, '', 'Leeg blok', 'RED_18', '2021-10-09 07:15:00', 900, 0, 0, 0, 0, '', '2021-10-07 08:12:40', '2021-10-07 08:12:40'),
(63, 3, '', 'Game Over - Stop alle aanvallen', 'RED_19', '2021-10-09 07:30:00', 900, 0, 0, 0, 0, '', '2021-10-07 08:12:40', '2021-10-07 08:12:40'),
(64, 4, 'HTTPS Post Flood (Tele2)', 'Anton', 'RED_1', '2021-10-09 03:00:00', 900, 0, 0, 0, 0, '', '2021-10-07 08:12:40', '2021-10-07 08:12:40'),
(65, 4, 'HTTPS Get Flood (Tele2)', 'Anton', 'RED_2', '2021-10-09 03:15:00', 900, 0, 0, 0, 0, '', '2021-10-07 08:12:40', '2021-10-07 08:12:40'),
(66, 4, 'HTTPS Post&Get Flood (Tele2)', 'Anton', 'RED_3', '2021-10-09 03:30:00', 900, 0, 0, 0, 0, '', '2021-10-07 08:12:40', '2021-10-07 08:12:40'),
(67, 4, 'TCP SA A (Tele2)', 'Tristan', 'RED_4', '2021-10-09 03:45:00', 900, 0, 0, 0, 0, '', '2021-10-07 08:12:40', '2021-10-07 08:12:40'),
(68, 4, '', 'Omschakelen naar KPN', 'RED_5', '2021-10-09 04:00:00', 900, 0, 0, 0, 0, '', '2021-10-07 08:12:40', '2021-10-07 08:12:40'),
(69, 4, 'TCP SA A (KPN)', 'Tristan', 'RED_6', '2021-10-09 04:15:00', 900, 0, 0, 0, 0, '', '2021-10-07 08:12:40', '2021-10-07 08:12:40'),
(70, 4, 'HTTPS Get Flood (KPN)', 'Anton', 'RED_7', '2021-10-09 04:30:00', 900, 0, 0, 0, 0, '', '2021-10-07 08:12:40', '2021-10-07 08:12:40'),
(71, 4, 'HTTPS Post&Get Flood (KPN)', 'Anton', 'RED_8', '2021-10-09 04:45:00', 900, 0, 0, 0, 0, '', '2021-10-07 08:12:40', '2021-10-07 08:12:40'),
(72, 4, 'HTTPS Post Flood (KPN)', 'Anton', 'RED_9', '2021-10-09 05:00:00', 900, 0, 0, 0, 0, '', '2021-10-07 08:12:40', '2021-10-07 08:12:40'),
(73, 4, '', 'Omschakeling uitzetten (KPN en Tele2)', 'RED_10', '2021-10-09 05:15:00', 900, 0, 0, 0, 0, '', '2021-10-07 08:12:40', '2021-10-07 08:12:40'),
(74, 4, 'UDP Kleine packets', 'Tristan', 'RED_11', '2021-10-09 05:30:00', 900, 0, 0, 0, 0, '', '2021-10-07 08:12:40', '2021-10-07 08:12:40'),
(75, 4, 'TCP SA', 'Tristan', 'RED_12', '2021-10-09 05:45:00', 900, 0, 0, 0, 0, '', '2021-10-07 08:12:40', '2021-10-07 08:12:40'),
(76, 4, 'Regenboog', 'Tristan', 'RED_13', '2021-10-09 06:00:00', 900, 0, 0, 0, 0, '', '2021-10-07 08:12:40', '2021-10-07 08:12:40'),
(77, 4, 'TCP A', 'Tristan', 'RED_14', '2021-10-09 06:15:00', 900, 0, 0, 0, 0, '', '2021-10-07 08:12:40', '2021-10-07 08:12:40'),
(78, 4, '', 'Omschakeling blijft uit (KPN en Tele2)', 'RED_15', '2021-10-09 06:30:00', 900, 0, 0, 0, 0, '', '2021-10-07 08:12:40', '2021-10-07 08:12:40'),
(79, 4, 'DJ Paul Elstak', 'Tristan/Anton', 'RED_16', '2021-10-09 06:45:00', 900, 0, 0, 0, 0, '', '2021-10-07 08:12:40', '2021-10-07 08:12:40'),
(80, 4, '', 'Leeg blok', 'RED_17', '2021-10-09 07:00:00', 900, 0, 0, 0, 0, '', '2021-10-07 08:12:40', '2021-10-07 08:12:40'),
(81, 4, '', 'Leeg blok', 'RED_18', '2021-10-09 07:15:00', 900, 0, 0, 0, 0, '', '2021-10-07 08:12:40', '2021-10-07 08:12:40'),
(82, 4, '', 'Game Over - Stop alle aanvallen', 'RED_19', '2021-10-09 07:30:00', 900, 0, 0, 0, 0, '', '2021-10-07 08:12:40', '2021-10-07 08:12:40'),
(83, 5, 'TCP 20G/UDP 20G', 'Daniël', 'RED_1', '2021-10-09 03:00:00', 900, 0, 0, 0, 0, '', '2021-10-07 08:12:40', '2021-10-07 08:12:40'),
(84, 5, 'Applicatief Overstroomik', 'RWS', 'RED_2', '2021-10-09 03:15:00', 900, 0, 0, 0, 0, '', '2021-10-07 08:12:40', '2021-10-07 08:12:40'),
(85, 5, 'Applicatief Overstroomik', 'RWS', 'RED_3', '2021-10-09 03:30:00', 900, 0, 0, 0, 0, '', '2021-10-07 08:12:40', '2021-10-07 08:12:40'),
(86, 5, 'HTTPS Post&Get flood overstroomik', 'Anton', 'RED_4', '2021-10-09 03:45:00', 900, 0, 0, 0, 0, '', '2021-10-07 08:12:40', '2021-10-07 08:12:40'),
(87, 5, '', 'Pauze', 'RED_5', '2021-10-09 04:00:00', 900, 0, 0, 0, 0, '', '2021-10-07 08:12:40', '2021-10-07 08:12:40'),
(88, 5, 'HTTPS Post&Get flood wabinfo', 'Anton', 'RED_6', '2021-10-09 04:15:00', 900, 0, 0, 0, 0, '', '2021-10-07 08:12:40', '2021-10-07 08:12:40'),
(89, 5, 'applicatief wabinfo', 'RWS', 'RED_7', '2021-10-09 04:30:00', 900, 0, 0, 0, 0, '', '2021-10-07 08:12:40', '2021-10-07 08:12:40'),
(90, 5, 'applicatief wabinfo', 'RWS', 'RED_8', '2021-10-09 04:45:00', 900, 0, 0, 0, 0, '', '2021-10-07 08:12:40', '2021-10-07 08:12:40'),
(91, 5, 'Regenboog 5G', 'Daniël', 'RED_9', '2021-10-09 05:00:00', 900, 0, 0, 0, 0, '', '2021-10-07 08:12:40', '2021-10-07 08:12:40'),
(92, 5, '', 'Pauze', 'RED_10', '2021-10-09 05:15:00', 900, 0, 0, 0, 0, '', '2021-10-07 08:12:40', '2021-10-07 08:12:40'),
(93, 5, 'Applicatief overstroomik en wabinfo', 'RWS', 'RED_11', '2021-10-09 05:30:00', 900, 0, 0, 0, 0, '', '2021-10-07 08:12:40', '2021-10-07 08:12:40'),
(94, 5, 'TCP 35G/UDP 35G', 'Daniël', 'RED_12', '2021-10-09 05:45:00', 900, 0, 0, 0, 0, '', '2021-10-07 08:12:40', '2021-10-07 08:12:40'),
(95, 5, 'DJ Paul Elstak', 'Anton/Daniël', 'RED_13', '2021-10-09 06:00:00', 900, 0, 0, 0, 0, '', '2021-10-07 08:12:40', '2021-10-07 08:12:40'),
(96, 5, 'Kerstboom 5G', 'Daniël', 'RED_14', '2021-10-09 06:15:00', 900, 0, 0, 0, 0, '', '2021-10-07 08:12:40', '2021-10-07 08:12:40'),
(97, 5, '', 'Pauze', 'RED_15', '2021-10-09 06:30:00', 900, 0, 0, 0, 0, '', '2021-10-07 08:12:40', '2021-10-07 08:12:40'),
(98, 5, 'HTTPS Post&Get flood overstroomik en wabinfo', 'Anton', 'RED_16', '2021-10-09 06:45:00', 900, 0, 0, 0, 0, '', '2021-10-07 08:12:40', '2021-10-07 08:12:40'),
(99, 5, 'DJ Paul Elstak', 'Daniël/Anton', 'RED_17', '2021-10-09 07:00:00', 900, 0, 0, 0, 0, '', '2021-10-07 08:12:40', '2021-10-07 08:12:40'),
(100, 5, '', 'Leeg blok', 'RED_18', '2021-10-09 07:15:00', 900, 0, 0, 0, 0, '', '2021-10-07 08:12:40', '2021-10-07 08:12:40'),
(101, 5, '', 'Game Over - Stop alle aanvallen', 'RED_19', '2021-10-09 07:30:00', 900, 0, 0, 0, 0, '', '2021-10-07 08:12:40', '2021-10-07 08:12:40'),
(102, 6, 'UDP klein max 30G', 'Tristan', 'RED_1', '2021-10-09 03:00:00', 900, 0, 0, 0, 0, '', '2021-10-07 08:12:40', '2021-10-07 08:12:40'),
(103, 6, 'UDP; dns simul; size 1450', 'Tristan', 'RED_2', '2021-10-09 03:15:00', 900, 0, 0, 0, 0, '', '2021-10-07 08:12:40', '2021-10-07 08:12:40'),
(104, 6, 'TCP Ack adv.', 'Tristan', 'RED_3', '2021-10-09 03:30:00', 900, 0, 0, 0, 0, '', '2021-10-07 08:12:40', '2021-10-07 08:12:40'),
(105, 6, 'TCP Ack adv.', 'Tristan', 'RED_4', '2021-10-09 03:45:00', 900, 0, 0, 0, 0, '', '2021-10-07 08:12:40', '2021-10-07 08:12:40'),
(106, 6, '', 'Pauze', 'RED_5', '2021-10-09 04:00:00', 900, 0, 0, 0, 0, '', '2021-10-07 08:12:40', '2021-10-07 08:12:40'),
(107, 6, 'nnb', '?', 'RED_6', '2021-10-09 04:15:00', 900, 0, 0, 0, 0, '', '2021-10-07 08:12:40', '2021-10-07 08:12:40'),
(108, 6, 'Replay tcpdump1', 'Tristan', 'RED_7', '2021-10-09 04:30:00', 900, 0, 0, 0, 0, '', '2021-10-07 08:12:40', '2021-10-07 08:12:40'),
(109, 6, 'Replay tcpdump2', 'Tristan', 'RED_8', '2021-10-09 04:45:00', 900, 0, 0, 0, 0, '', '2021-10-07 08:12:40', '2021-10-07 08:12:40'),
(110, 6, 'Regenboog', 'Tristan', 'RED_9', '2021-10-09 05:00:00', 900, 0, 0, 0, 0, '', '2021-10-07 08:12:40', '2021-10-07 08:12:40'),
(111, 6, '', 'Pauze', 'RED_10', '2021-10-09 05:15:00', 900, 0, 0, 0, 0, '', '2021-10-07 08:12:40', '2021-10-07 08:12:40'),
(112, 6, 'HTTPS GET', 'Anton', 'RED_11', '2021-10-09 05:30:00', 900, 0, 0, 0, 0, '', '2021-10-07 08:12:40', '2021-10-07 08:12:40'),
(113, 6, 'HTTPS Post', 'Anton', 'RED_12', '2021-10-09 05:45:00', 900, 0, 0, 0, 0, '', '2021-10-07 08:12:40', '2021-10-07 08:12:40'),
(114, 6, 'HTTPS Post&Get nomoreddos', 'Anton', 'RED_13', '2021-10-09 06:00:00', 900, 0, 0, 0, 0, '', '2021-10-07 08:12:40', '2021-10-07 08:12:40'),
(115, 6, 'DJ Paul Elstak', 'Tristan', 'RED_14', '2021-10-09 06:15:00', 900, 0, 0, 0, 0, '', '2021-10-07 08:12:40', '2021-10-07 08:12:40'),
(116, 6, '', 'Pauze', 'RED_15', '2021-10-09 06:30:00', 900, 0, 0, 0, 0, '', '2021-10-07 08:12:40', '2021-10-07 08:12:40'),
(117, 6, 'verzoeknummer', '?', 'RED_16', '2021-10-09 06:45:00', 900, 0, 0, 0, 0, '', '2021-10-07 08:12:40', '2021-10-07 08:12:40'),
(118, 6, 'verzoeknummer', '?', 'RED_17', '2021-10-09 07:00:00', 900, 0, 0, 0, 0, '', '2021-10-07 08:12:40', '2021-10-07 08:12:40'),
(119, 6, '', 'Leeg blok', 'RED_18', '2021-10-09 07:15:00', 900, 0, 0, 0, 0, '', '2021-10-07 08:12:40', '2021-10-07 08:12:40'),
(120, 6, '', 'Game Over - Stop alle aanvallen', 'RED_19', '2021-10-09 07:30:00', 900, 0, 0, 0, 0, '', '2021-10-07 08:12:40', '2021-10-07 08:12:40');




