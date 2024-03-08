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

namespace bld\ddosspelbord\components;

use Auth;
use Bld\Ddosspelbord\Models\Settings;
use Input;
use Config;
use Session;
use Redirect;
use Response;
use Bld\Ddosspelbord\Controllers\Feeds;
use Bld\Ddosspelbord\Models\Transactions;
use Bld\Ddosspelbord\Models\spelbordusers;
use Cms\Classes\ComponentBase;
use bld\ddosspelbord\helpers\hLog;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ddosspelbord_feed extends ComponentBase {

    public function componentDetails()
    {
        return [
            'name' => 'Anti-DDoS Coalitie DDoS spelbord',
            'description' => 'Handle backend calls'
        ];
    }

    public function defineProperties()
    {
        return [
            'hash' => [
                'title'   => 'hash',
                'description' => 'active hash',
                'default' => '',
                'type'    => 'string',
            ],
        ];
    }

    public function getVersion()
    {
        return Config::get('bld.ddosspelbord::release.version', '0.9.?') . ' - ' . Config::get('bld.ddosspelbord::release.build', 'build 1');
    }

    public function init() {
        hLog::logLine("D-ddosspelbord_feed.init; version=".ddosspelbord_data::getVersion());
    }
    public function onRun() {
    }

    /**
     * getFeeds
     *
     * Two modes;
     * - (default) streamfeeds; support client stream event session, push transactions when found, handle max execution time
     * - readfeeds; client interval polling new feeds
     *
     * @return StreamedResponse
     */

    public function getFeed() {

        // Get execution (time)
        $maxExecution = Settings::get('maxexecutiontime','25');
        // Reconnect time -> force before nginx/apache timeout (normally 30 secs)
        set_time_limit($maxExecution);
        hLog::logLine("D-ddosspelbord_feed; set execution on $maxExecution");

        if (Spelbordusers::verifyAccess(false)) {

            // Get property (first time) hash
            $hash = $this->property('hash');
            if ($hash=='') {
                $hash = (new Transactions())->getLastTransactionHash();
            }

            $mode = post('mode','streamfeeds');

            // New feeds
            $feed = new Feeds();

            // Last hash for this user
            $lasthash = $feed->getLastsettingHash();
            if ($lasthash) {
                $hash = $lasthash;
                hLog::logLine("D-ddosspelbord_feed; mode=$mode; read from last (user) hash=$hash");
            } else {
                hLog::logLine("D-ddosspelbord_feed; mode=$mode; read from hash=$hash");
            }

            if ($mode == 'streamfeeds') {
                // start stream
                $response = $feed->startStream($hash);

                // set headers
                $response->headers->set('Content-Type', 'text/event-stream');
                $response->headers->set('X-Accel-Buffering', 'no');
                $response->headers->set('Cache-Control', 'no-cache');

            } elseif ($mode == 'readfeeds') {
                $lastTransaction = $lasthash;
                $newtransactions = $feed->getNewTransactions($lastTransaction);

                $response = Response::json([
                    'result' => true,
                    'message' => '',
                    'lastTransaction' => $lastTransaction,
                    'transactions' => json_encode($newtransactions)
                ]);
                $response->headers->set('Content-Type', 'application/json');

                if ($lastTransaction != $lasthash) $feed->setLastsettingHash($lastTransaction);

            }

        } else {
            // response -> force reload (logout) screen
            $response = new StreamedResponse(function() {

                hLog::logLine("D-ddosspelbord_feed; return empty response with not logged in");
                $result = [
                    'login' => false,
                ];
                echo "data: ".json_encode($result)."\n\n";
                // force direct output
                echo str_repeat(' ',4096)."\n";

                ob_flush();
                flush();
                sleep(1);

            });

            // Set headers
            $response->headers->set('Content-Type', 'text/event-stream');
            $response->headers->set('X-Accel-Buffering', 'no');
            $response->headers->set('Cache-Control', 'no-cache');

        }

        // get here after max execution time

        return $response;
    }

    /**
     * TEST; static function for direct read of feeds
     *
     * @return mixed
     */

    public static function readFeeds() {

        // new feeds
        $feed = new Feeds();

        $lasthash = $feed->getLastsettingHash();
        hLog::logLine("D-ddosspelbord_feed; read from last (user) hash=$lasthash");

        $lastTransaction = $lasthash;
        $newtransactions = $feed->getNewTransactions($lastTransaction);

        $response = Response::json([
            'result' => true,
            'message' => '',
            'lastTransaction' => $lastTransaction,
            'transactions' => json_encode($newtransactions)
        ]);
        $response->headers->set('Content-Type', 'application/json');

        if ($lastTransaction != $lasthash) $feed->setLastsettingHash($lastTransaction);

        return $response;
    }

}
