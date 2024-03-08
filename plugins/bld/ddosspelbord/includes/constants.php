<?php
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

define ('CRLF_NEWLINE', "<br />\n");

define ('TRANSACTION_TYPE_LOG', 'log');
define ('TRANSACTION_TYPE_ATTACK', 'attack');
define ('TRANSACTION_TYPE_SYSTEM', 'system');
define ('TRANSACTION_TYPE_ACTION', 'action');

define ('SESSION_LAST_HASH', 'session_last_hash');
define ('SESSION_EXCLUDED_PARTIES', 'session_excluded_parties');
define ('SESSION_SET_SCROLL', 'session_set_scroll');
define ('SESSION_LOGS_FILTER_PARTIES', 'session_logs_filter_parties');
define ('SESSION_LOGS_FILTER_USERS', 'session_logs_filter_users');

define('DB_TABLE_ACTIONS','bld_ddosspelbord_actions');
define('DB_TABLE_LOGS','bld_ddosspelbord_logs');
define('DB_TABLE_ATTACKS','bld_ddosspelbord_attacks');
define('DB_TABLE_PARTIES','bld_ddosspelbord_parties');
define('DB_TABLE_ROLES','bld_ddosspelbord_roles');
define('DB_TABLE_USERS','bld_ddosspelbord_users');
define('DB_TABLE_TRANSACTIONS','bld_ddosspelbord_transactions');
define('DB_TABLE_MEASUREMENTS','bld_ddosspelbord_measurements');

define ('DB_ROLE_PURPLE', 'purple');
define ('DB_ROLE_BLUE', 'blue');
define ('DB_ROLE_RED', 'red');
define ('DB_ROLE_OBSERVER', 'observer');

define('USER_ACTIVE_MAX_SEC', 15 * 60); // seconds active

define('NO_PARTY_NAME','No party');

