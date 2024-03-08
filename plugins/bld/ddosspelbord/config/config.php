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

return [

    'release' => [
        'version' => '2.2',
        'build' => 'Build 2',
    ],

    'mail' => [
        'host' => env('MAIL_HOST', 'support.svsnet.nl'),
        'port' => env('MAIL_PORT', 25),
        'encryption' => env('MAIL_ENCRYPTION', 'tls'),
        'username' => env('MAIL_USERNAME', ''),
        'password' => env('MAIL_PASSWORD', ''),
        'overrule_to' => env('MAIL_OVERRULE_TO', ''),
    ],

    'reports' => [
        'email' => env('REPORTS_EMAIL', 'support@nomoreddos.org'),
    ],

    'errors' => [
        'active' => env('ERROR_ACTIVE', 1),
        'domain' => env('ERROR_DOMAIN', 'nomoreddos.org'),
        'from' => env('ERROR_FROM', 'support@nomoreddos.org'),
        'email' => env('ERROR_EMAIL', 'support@nomoreddos.org'),
        'error_display_user' => 'found: please contact support',
    ],

    'acceptedfiletypes' => [
        "png", "jpg", "jpeg", "gif", "svg", "tiff", "txt", "rtf", "mp4", "Ogg",
        "mp3", "wav", "Ogg", "avi", "bmp", "css", "csv", "doc", "gif", "json", "pdf",
    ],

    'measurements' => [
        'ripe' => [
            'ripe_atlas_api' => env('MEASUREMENTS_RIPE_ATLAS_API', 'https://atlas.ripe.net/api/v2/'),
            'ripe_atlas_stream' => env('MEASUREMENTS_RIPE_ATLAS_STREAM', 'https://atlas-stream.ripe.net/stream/'),
        ],
    ],


];
