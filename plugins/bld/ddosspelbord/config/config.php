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
        'version' => '2.3',
        'build'   => 'Build 3',
    ],

    'mail' => [
        'host'        => env('MAIL_HOST', 'support.svsnet.nl'),
        'port'        => env('MAIL_PORT', 25),
        'encryption'  => env('MAIL_ENCRYPTION', 'tls'),
        'username'    => env('MAIL_USERNAME', ''),
        'password'    => env('MAIL_PASSWORD', ''),
        'overrule_to' => env('MAIL_OVERRULE_TO', ''),
    ],

    'reports' => [
        'email' => env('REPORTS_EMAIL', 'support@nomoreddos.org'),
    ],

    'errors' => [
        'active'             => env('ERROR_ACTIVE', 1),
        'domain'             => env('ERROR_DOMAIN', 'nomoreddos.org'),
        'from'               => env('ERROR_FROM', ''),
        'email'              => env('ERROR_EMAIL', 'support@nomoreddos.org'),
        'error_display_user' => 'found: please contact support',
    ],

    'acceptedfiletypes' => [
        "zip",
        "rar",
        "png",
        "webp",
        "apng",
        "jpg",
        "jpeg",
        "gif",
        "svg",
        "tiff",
        "txt",
        "rtf",
        "mp4",
        "Ogg",
        "mp3",
        "wav",
        "Ogg",
        "avi",
        "bmp",
        "css",
        "csv",
        "doc",
        "gif",
        "json",
        "pdf",
    ],

    'measurements' => [
        'ripe' => [
            'ripe_atlas_api'    => env('MEASUREMENTS_RIPE_ATLAS_API', 'https://atlas.ripe.net/api/v2/'),
            'ripe_atlas_stream' => env('MEASUREMENTS_RIPE_ATLAS_STREAM', 'https://atlas-stream.ripe.net/stream/'),
        ],
    ],

    'packages' => [
        // Already loaded in by laravel/framework
        'illuminate-auth'  => [
            'providers' => [
                '\Illuminate\Auth\AuthServiceProvider',
            ],

            'config_namespace' => 'auth',

            'config' => [
                /*
                |--------------------------------------------------------------------------
                | Authentication Defaults
                |--------------------------------------------------------------------------
                |
                | This option controls the default authentication "guard" and password
                | reset options for your application. You may change these defaults
                | as required, but they're a perfect start for most applications.
                |
                */

                'defaults' => [
                    'guard'     => 'web',
                    'passwords' => 'users',
                ],

                /*
                |--------------------------------------------------------------------------
                | Authentication Guards
                |--------------------------------------------------------------------------
                |
                | Next, you may define every authentication guard for your application.
                | Of course, a great default configuration has been defined for you
                | here which uses session storage and the Eloquent user provider.
                |
                | All authentication drivers have a user provider. This defines how the
                | users are actually retrieved out of your database or other storage
                | mechanisms used by this application to persist your user's data.
                |
                | Supported: "session", "token", "passport"
                |
                */

                'guards' => [
                    'web' => [
                        'driver'   => 'session',
                        'provider' => 'users',
                    ],

                    'api' => [
                        'driver'   => 'passport',
                        'provider' => 'users',
                    ],
                ],

                /*
                |--------------------------------------------------------------------------
                | User Providers
                |--------------------------------------------------------------------------
                |
                | All authentication drivers have a user provider. This defines how the
                | users are actually retrieved out of your database or other storage
                | mechanisms used by this application to persist your user's data.
                |
                | If you have multiple user tables or models you may configure multiple
                | sources which represent each model / table. These sources may then
                | be assigned to any extra authentication guards you have defined.
                |
                | Supported: "database", "eloquent"
                |
                */

                'providers' => [
                    'users' => [
                        'driver' => 'eloquent',
                        'model'  => \bld\ddosspelbord\models\BackendUser::class,
                    ],

                    // 'users' => [
                    //     'driver' => 'database',
                    //     'table' => 'users',
                    // ],
                ],

                /*
                |--------------------------------------------------------------------------
                | Resetting Passwords
                |--------------------------------------------------------------------------
                |
                | You may specify multiple password reset configurations if you have more
                | than one user table or model in the application and you want to have
                | separate password reset settings based on the specific user types.
                |
                | The expire time is the number of minutes that the reset token should be
                | considered valid. This security feature keeps tokens short-lived so
                | they have less time to be guessed. You may change this as needed.
                |
                */

                // **NOTE**: May not be currently necessary as October implements this separately
                //
                // 'passwords' => [
                //     'users' => [
                //         'provider' => 'users',
                //         'table' => 'backend_users_password_resets',
                //         'expire' => 60,
                //     ],
                // ],
            ],
        ],
        'laravel-passport' => [
            'aliases' => [
                'Passport' => '\Laravel\Passport\Passport',
            ],

            'providers' => [
                '\Laravel\Passport\PassportServiceProvider',
            ],
        ],

    ],

];
