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
use Input;
use Config;
use Session;
use Redirect;
use Response;
use Bld\Ddosspelbord\Models\spelbordusers;
use Cms\Classes\ComponentBase;
use bld\ddosspelbord\helpers\hLog;

class ddosspelbord_setting extends ComponentBase {

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
        ];
    }

    public function getVersion()
    {
        return Config::get('bld.ddosspelbord::release.version', '0.9.?') . ' - ' . Config::get('bld.ddosspelbord::release.build', 'build 1');
    }

    public function init() {
        hLog::logLine("D-ddosspelbord_setting.init; version=".ddosspelbord_data::getVersion());
    }
    public function onRun() {
    }

    /**
     * Sets setting and return response to client
     * @return Response\
     */
    public static function submitSetting() {
        // get spelbord user
        if ($user = Spelbordusers::verifyAccess()) {

            $mode = post('mode', '');

            if ($mode == 'setParties') {

                $partyId = post('partyId', '');
                $show = post('show', true);
                hLog::logLine("D-ddosspelbord_setting.submitSetting; mode=$mode, partyId=$partyId, show=$show");

                $excluded = Session::get(SESSION_EXCLUDED_PARTIES,'');
                $excluded = ($excluded) ? unserialize($excluded) : [];
                $key = array_search($partyId,$excluded);
                if ($key===false) {
                    if (!$show) $excluded[] = $partyId;
                } else {
                    if ($show) {
                        if ($key!==false) {
                            unset($excluded[$key]);
                        }
                    }
                }
                Session::put(SESSION_EXCLUDED_PARTIES,serialize($excluded));

            } elseif ($mode == 'setScroll') {

                $scroll = post('scroll', true);
                Session::put(SESSION_SET_SCROLL,$scroll);
                hLog::logLine("D-ddosspelbord_setting.submitSetting; mode=$mode, scroll=".(($scroll)?'true':'false') );

            } else {
                hLog::logLine("E-ddosspelbord_setting.submitSetting; unknown mode=$mode");
            }

        } else {
            hLog::logLine("E-ddosspelbord_setting.submitSetting; no access");
            $alog = [];
        }

        return Response::json([
            'result' => true
        ]);
    }

}
