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

use Bld\Ddosspelbord\Controllers\Feeds;
use Bld\Ddosspelbord\Models\Settings;
use Bld\Ddosspelbord\Models\Spelbordusers;
use Input;
use Config;
use League\Csv\Exception;
use Session;
use Redirect;
use Response;
use Request;
use Cms\Classes\ComponentBase;
use bld\ddosspelbord\helpers\hLog;
use Bld\Ddosspelbord\Models\Logs as Logs;
use Bld\Ddosspelbord\Models\Attack as Attack;

class ddosspelbord_attack extends ComponentBase
{

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

    public function init()
    {
        hLog::logLine("D-ddosspelbord_setting.init; version=" . ddosspelbord_data::getVersion());
    }

    public function onRun()
    {
    }

    /**
     * When an attack is started, stopped, paused or resumed this function handles the changes
     * It will create a log entry with the attack status, and changes the status in the database table: attacks
     * @return Response
     */
    public static function handleAttackChanges()
    {
        $errmessage = '';
        $anattack = '';
        $result = false;
        try {
            // get gameboard user
            if ($user = Spelbordusers::verifyAccess()) {
                $userId =  post('user_id');
                if ($user->role != 'observer') {
                    $time = post('timestamp', '');
                    if ($time && strtotime($time) !== false) {
                        $timestamp = Settings::get('startdate');
                        $timestamp = str_replace('00:00:00', $time, $timestamp);

                        $id = post('id', '');

                        if (!empty($id)) {
                            hLog::logLine("D-ddosspelbord_attack.handleAttackChanges; userId = $userId, =id=$id, update attack");
                            $attack = Attack::find($id);
                        } else {
                            hLog::logLine("D-ddosspelbord_attack.handleAttackChanges; userId=$userId create new attack");
                            if ($user->id == $userId) {
                                $attack = new Attack();
                                $attack->user_id = $user->id;
                                $attack->party_id = $user->party_id;
                            }
                            else {
                                throw new ApplicationException('You are not allowed to start an attack under a different users name');
                            }

                        }

                        // Check if user had correct party id
                        $partyiscorrect = $attack->party_id === $user->party_id;
                        $errmessage = (!$partyiscorrect) ? "can\'t update attack from other party" : null;

                        if ($partyiscorrect) {
                            $attackname = post('name', '');
                            // Strip all tags
                            $attackname = strip_tags($attackname);
                            // Strip dangerous tags
                            $attackname = preg_replace('#<script(.*?)>(.*?)</script>#is', '', $attackname);
                            $attackname = preg_replace('#function(.*?)[(][)](.*?)#is', '', $attackname);
                            $attackname = preg_replace('#http:[/][/](.*?)#is', '', $attackname);
                            // Completely sanitize input
                            $attackname = filter_var($attackname, FILTER_SANITIZE_STRING);

                            $attack->name = $attackname;
                            $attack->status = post('status', '');
                            $attack->party_id = post('party_id', '');
                            $attack->user_id = post('user_id', '');
                            $attack->timestamp = $timestamp;
                            $attack->save();

                            // Create a log entry
                            $log = new Logs();
                            $log->user_id = $user->id;
                            $log->log = ucfirst($attack->status . ': ' . $attack->name);
                            $log->timestamp = $timestamp;
                            $log->save();

                            // Update Vue with the logs
                            $alog = ddosspelbord_data::getSpelbordLog($log);
                             hlog::logLine("submitLog.alog=" . print_r($alog,true ));
                            (new Feeds())->createTransaction(TRANSACTION_TYPE_LOG, $alog);

                            // Get vue code values & create transaction
                            $anattack = ddosspelbord_data::getSpelbordAttack($attack);

                            hlog::logLine("submitLog.anttack=" . print_r($anattack,true ));
                            (new Feeds())->createTransaction(TRANSACTION_TYPE_ATTACK, $anattack);

                            $result = true;
                        }
                    }
                }
            }
        } catch (Exception $err) {
            $errmessage = "Cannot initate or change attack: " . $err->getMessage();
            hLog::logLine("E-$errmessage");
            $result = false;
        }
        if ($errmessage) {
            hLog::logLine("W-$errmessage");
        }

        return Response::json([
            'result' => $result,
            'attack' => $anattack,
        ]);
    }
}
