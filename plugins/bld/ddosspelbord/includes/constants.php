<?php

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

define ('DB_ROLE_PURPLE', 'purple');
define ('DB_ROLE_BLUE', 'blue');
define ('DB_ROLE_RED', 'red');
define ('DB_ROLE_OBSERVER', 'observer');

define('USER_ACTIVE_MAX_SEC', 15 * 60); // seconds active

define('NO_PARTY_NAME','No party');

