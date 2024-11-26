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

/**
 * DDoS Spelbord
 * DDoS Gameboard
 *
 */

use Auth;
use Bld\Ddosspelbord\Models\Logs as Logs;
use Bld\Ddosspelbord\Controllers\Logs as LogsController;
use Bld\Ddosspelbord\Models\Roles;
use Bld\Ddosspelbord\Models\Settings;
use Input;
use Config;
use Session;
use Redirect;
use Response;
use DateTime;
use DateTimeZone;
use Bld\Ddosspelbord\Controllers\Feeds;
use Bld\Ddosspelbord\Models\Attack as Attack;
use Bld\Ddosspelbord\Models\Transactions;
use Bld\Ddosspelbord\Models\spelbordusers;
use Cms\Classes\ComponentBase;
use bld\ddosspelbord\helpers\hLog;
use Bld\Ddosspelbord\Models\Parties;
use Yaml;
use stdClass;

class ddosspelbord_data extends ComponentBase
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

    public static function getVersion()
    {
        return Config::get('bld.ddosspelbord::release.version', '0.9.?') . ' - ' . Config::get('bld.ddosspelbord::release.build', 'build 1');
    }

    public function init()
    {
        hLog::logLine("D-ddosspelbord_data.init; version=" . self::getVersion());
    }

    /**
     * Called on opening home page
     * When not logged in then only simple display with login button
     * When logged in then return page vars with dynamic exercise data
     *
     */

    public function onRun()
    {
        $this->page['gameboard'] = $this->getGameboardData();
    }

    /** VUE DATA HANDLING **/

    /**
     * Based on the original code the VUE code is depending on different data bricks and also converting (e.g. party_id -> partyId)
     *
     * Below are the function which convert (model) data to frontend data
     *
     * @TO-DO; convert/strip/re-organize VUE code so direct data handling from the models can be done
     *
     */

    /**
     * getGameboardData()
     *
     * Called by loading home page
     *
     * @return array
     */
    public function getGameboardData()
    {
        hLog::logLine("D-ddosspelbord_data.getData");

        // -1- last transaction
        $hash = (new Transactions())->getLastTransactionHash();

        // empty object
        $empty = json_encode((object)[]);

        if (spelbordusers::verifyAccess(false)) {

            $title = Settings::get('description', '');
            if (empty($title)) $title = 'DDoS gameboard';   // always filled

            $timezone = new DateTimeZone(config('cms.backendTimezone'));

            // Fetching start and end dates, and applying the timezone correctly
            $startdate = new DateTime(Settings::get('startdate', date('Y-m-d 00:00:00')), $timezone);
            $enddate = new DateTime(Settings::get('enddate', date('Y-m-d 00:00:00')), $timezone);

            // Fetch and adjust 'firsttime' to the appropriate timezone
            $firsttime = new DateTime(Settings::get('firsttime', '02:30:00'));
            $firsttime = $firsttime->setTimezone($timezone); // Adjust to the backend timezone
            $first = str_replace('00:00:00', $firsttime->format('H:i:s'), $startdate->format('Y-m-d 00:00:00'));

            // Fetch and adjust 'starttime' to the appropriate timezone
            $starttime = new DateTime(Settings::get('starttime', '03:00:00'));
            $starttime = $starttime->setTimezone($timezone); // Adjust to the backend timezone
            $start = str_replace('00:00:00', $starttime->format('H:i:s'), $startdate->format('Y-m-d 00:00:00'));

            // Fetch and adjust 'endtime' to the appropriate timezone
            $endtime = new DateTime(Settings::get('endtime', '08:00:00'));
            $endtime = $endtime->setTimezone($timezone); // Adjust to the backend timezone
            $end = str_replace('00:00:00', $endtime->format('H:i:s'), $enddate->format('Y-m-d 00:00:00'));

            // Scroll (session)
            $scroll = Session::get(SESSION_SET_SCROLL, true);
            $scroll = ($scroll) ? 'true' : 'false';

            // Note: in minutes
            $granularity = Settings::get('granularity', '1');

            $logmaxfilesizeinmb = Settings::get('logmaxfilesize');
            $logmaxfilesize = $logmaxfilesizeinmb * 1024 * 1024;

            $logmaxfiles = Settings::get('logmaxfiles');

            $acceptedfiletypes = LogsController::GetAllowedFiletypes();

            hLog::logLine("D-ddosspelbord_data; title=$title, first=$first, start=$start, end=$end");

            // Save hash current user session
            Feeds::setLastsettingHash($hash);

            $targetdasboardurl = Settings::get('external_target_monitor_url', false);

            $allowedInactivityInSeconds = Settings::get('allowedInactivityInSeconds', 3600);
            $allowedNonVisibleInSeconds = Settings::get('allowedNonVisibleInSeconds', 3600);

            // -1- lang
            $lang = $this->getLangStrings();

            // -2- user
            $user = Spelbordusers::getOnAuth();

            // -3- parties
            $parties = $this->getPartiesWithActions($user);

            // -4- logs
            $logs = $this->getLogs($user);
            if (count($logs) == 0) {
                $logs = (object)[];
            }

            // -5- Attacks
            $attacks = $this->getAttacks($user);
            if (count($attacks) == 0) {
                $attacks = (object)[];
            }

            $data = [
                'hash' => $hash,
                'lang' => json_encode($lang),
                'parties' => $parties,
                'user' => addslashes(json_encode($user)),
                'logs' => addslashes(json_encode($logs)),
                'attacks' => addslashes(json_encode($attacks)),
                'csrftoken' => Session::token(),
                'loggedin' => true,
                'scroll' => $scroll,
                'logmaxfilesize' => $logmaxfilesize,
                'logmaxfiles' => $logmaxfiles,
                'acceptedfiletypes' => implode(',', $acceptedfiletypes),
                'targetdasboardurl' => $targetdasboardurl,
                'allowedInactivityInSeconds' => $allowedInactivityInSeconds,
                'allowedNonVisibleInSeconds' => $allowedNonVisibleInSeconds,
            ];

            // let's get to work
            $gameboard = [
                'title' => $title,
                'firsttime' => $first,
                'starttime' => $start,
                'endtime' => $end,
                'granularity' => $granularity,
                'version' => self::getVersion(),
                'data' => $data,
            ];

        } else {

            // -1- lang
            $lang = $this->getLangStrings();

            hLog::logLine("D-ddosspelbord_data; no access data");

            $data = [
                'hash' => $hash,
                'lang' => json_encode($lang),
                'parties' => $empty,
                'user' => $empty,
                'logs' => $empty,
                'attacks' => $empty,
                'csrftoken' => Session::token(),
                'loggedin' => false,
                'scroll' => 'false',
                'logmaxfilesize' => 0,
                'logmaxfiles' => 0,
                'acceptedfiletypes' => $empty,

            ];

            // note; empty title is indication for not showing any data (html)
            $gameboard = [
                'title' => '',
                'data' => $data,
            ];

        }

        return $gameboard;
    }


    public function getPartiesWithActions($user)
    {

        $parties = Parties::all();

        // Session holds (optional) excluded parties
        $excluded = Session::get(SESSION_EXCLUDED_PARTIES, '');
        $excluded = ($excluded) ? unserialize($excluded) : [];

        // Make associative array (needed for Gameboard vue code)

        $oparties = [];
        $sort = ($user->partyId == 0) ? 1 : 2;
        foreach ($parties as $key => $party) {

            if ($party->id == $user->partyId) {
                $sortkey = 1;
            } else {
                $sortkey = $sort;
                $sort += 1;
            }

            // Show if not excluded
            $party->show = !in_array($party->id, $excluded);

            // Put in work array
            $oparty = $party->toArray();

            $startdate = Settings::get('startdate');

            // Do the action by ourself, not by relation
            $oactions = [];
            foreach ($party->actions as $action) {
                // Convert for vue code
                $oaction = $action->toArray();
                // Disable sending DDoS NAME to browser if no rights
                $oaction['name'] = ($user->role == 'red' || $user->role == 'purple') ? $action->name : '';
                $oaction['partyId'] = $party->id;
                $oaction['hasIssues'] = $action->has_issues;
                $oaction['isCancelled'] = $action->is_cancelled;
                // NOTE: the key of action array has to be the id of the record
                $oactions[$action->id] = $oaction;
            }
            // Put in associative array
            $oparty['actions'] = $oactions;
            $oparty['sortkey'] = $sortkey;

            // Key = partyId
            $oparties[$party->id] = $oparty;
        }

        return addslashes(json_encode($oparties));
    }

    public static function getSpelborduser($user_id, $auth = false)
    {
        if ($user_id == 0 && $auth == false) {
               $spelborduser = Spelbordusers::createSystemUserArray();
        }
        else {
            // Convert to vue code
            if ($auth) {
                $spelborduser = Spelbordusers::where('user_id', $user_id)->first();
            }
            else {
                $spelborduser = Spelbordusers::find($user_id);
            }
            if ($spelborduser) {
                $spelborduser = $spelborduser->toArray();
                $spelborduser['partyId'] = $spelborduser['party_id'];
                $role = Roles::find($spelborduser['role_id']);
                $spelborduser['role'] = ($role) ? $role->name : DB_ROLE_BLUE;
                foreach (['user_id', 'role_id', 'party_id'] as $skip) {
                    unset($spelborduser[$skip]);
                }
            }
        }

        return $spelborduser;
    }

    public static function getLogAttachments($log_id)
    {
        $attachments = Logs::getAttachments($log_id);
        return $attachments;
    }

    public static function getLogAttachment($attachment_id)
    {
        $attachment = Logs::getAttachment($attachment_id);
        return $attachment;
    }

    public static function exportLogAttachments($models)
    {
        $models = $models->toArray();
        $array = [];
        for ($i = 0; $i < count($models); ++$i) {
            $array[$i]['id'] = $models[$i]['id'];
            $array[$i]['file_name'] = $models[$i]['file_name'];
            $array[$i]['file_size'] = $models[$i]['file_size'];
            $array[$i]['extension'] = $models[$i]['extension'];
            $array[$i]['created_at'] = $models[$i]['created_at'];
            $array[$i]['updated_at'] = $models[$i]['updated_at'];
        }

        return $array;
    }

    public function getLogs($user)
    {
        $ologs = [];

        if ($user && $user->id != 0 && $user->role != 'observer') {

            $userRole = $user->role;
            $party_id = $user->partyId;
            $attachments = [];

            hLog::logLine("D-ddosspelbord_data; getLogs; userRole=$userRole, party_id=$party_id");

            // Fetch logs based on party_id by joining the users table
            $logs = Logs::leftJoin(DB_TABLE_USERS, DB_TABLE_USERS . '.id', '=', DB_TABLE_LOGS . '.user_id')
                ->where(DB_TABLE_USERS . '.party_id', '=', $party_id)
                ->orWhere(DB_TABLE_LOGS . '.user_id', '=', 0) // Include logs where user_id = 0
                ->select(DB_TABLE_LOGS . '.*')
                ->get();


            if (count($logs) > 0) {
                $party = Parties::find($party_id);

                // Into associative array
                $ologs = [];
                foreach ($logs as $log) {
                    $spelborduser = self::getSpelborduser($log->user_id);
                    // Only authorized logs
                    $attachmentsmodels = self::getLogAttachments($log['id']);
                    $attachments = self::exportLogAttachments($attachmentsmodels);
                    if ($userRole == DB_ROLE_PURPLE || $spelborduser['role'] == $userRole || $spelborduser['id'] == 0) {
                        $spelbordlog = $log->toArray();
                        $spelbordlog['user'] = $spelborduser;
                        $spelbordlog['user']['party'] = ($party) ? $party->toArray() : self::createEmptyParty();
                        $spelbordlog['partyId'] = $party_id;
                        $spelbordlog['attachments'] = $attachments;
                        $ologs[$log->id] = $spelbordlog;
                    }
                }

            }

        } else {
            hLog::logLine("D-ddosspelbord_data; getLogs; no user or party ");
        }

        return $ologs;
    }

    /**
     * @param $user
     * @return array
     */
    public function getAttacks($user)
    {
        $oattacks = [];

        if ($user && $user->id != 0 && $user->role != 'observer') {

            $userRole = $user->role;
            $party_id = $user->partyId;
            $attachments = [];

            hlog::logLine("D-ddosspelbord_data; getAttacks; userRole=$userRole, party_id=$party_id");

            $attacks = Attack::join(DB_TABLE_USERS, DB_TABLE_USERS . '.id', '=', DB_TABLE_ATTACKS . '.user_id')
                ->where(DB_TABLE_USERS . '.party_id', '=', $party_id)
                ->select(DB_TABLE_ATTACKS . '.*')
                ->get();

            if (count($attacks) > 0) {

                $party = Parties::find($party_id);

                // Into associative array
                $oattacks = [];
                foreach ($attacks as $attack) {
                    $spelborduser = self::getSpelborduser($attack->user_id);
                    if ($userRole == DB_ROLE_PURPLE || $spelborduser['role'] == $userRole) {
                        $spelboardattack = $attack->toArray();
                        $spelboardattack['user'] = $spelborduser;
                        $spelboardattack['user']['party'] = ($party) ? $party->toArray() : self::createEmptyParty();
                        $spelboardattack['partyId'] = $party_id;
                        $timestamp = new DateTime($attack->updated_at);
                        $spelboardattack['lastUpdated'] = $timestamp->format('m-d / H:i');
                        $spelboardattack['attachments'] = $attachments;
                        $oattacks[$attack->id] = $spelboardattack;
                    }
                }
            }

        } else {
            hlog::logLine("D-ddosspelbord_data; getAttacks; no user or party ");
        }

        return $oattacks;
    }

    /**
     * @param $log
     * @param $hasattachments
     * @return Object
     */
    public static function getSpelbordlog($log, $hasattachments = false)
    {
        // Array values
        $log = $log->toArray();
        $user = self::getSpelborduser($log['user_id']);
        $partyId = intval($user['partyId']);
        $party = ($partyId == 0) ? false : Parties::find($partyId); //If the party id is 0 than it is the system user
        if ($hasattachments) {
            $attachmentsmodels = self::getLogAttachments($log['id']);
            $attachments = self::exportLogAttachments($attachmentsmodels);
            $log['attachments'] = $attachments;
        }
        $log['user'] = $user;
        $log['user']['party'] = ($party) ? $party->toArray() : self::createEmptyParty();
        $log['partyId'] = $user['partyId'];

        return $log;
    }

    public static function createEmptyParty(){
        return [
            'id' => 0,
            'deleted_at' => NULL,
            'name' => NO_PARTY_NAME,
            'logo', ''
        ];
    }

    /**
     * @param $attack
     * @return object
     */
    public static function getSpelbordAttack($attack)
    {
        // Array values
        $attack = $attack->toArray();
        $user = self::getSpelborduser($attack['user_id']);
        $party = Parties::find($user['partyId']);

        $attack['user'] = $user;
        $attack['user']['party'] = ($party) ? $party->toArray() : self::createEmptyParty();
        $attack['partyId'] = $user['partyId'];
        $timestamp = (!empty($attack->updated_at)) ? new DateTime($attack->updated_at) : new DateTime();
        $attack['lastUpdated'] = $timestamp->format('m-d / H:i');

        return $attack;
    }

    /**
     * @return object
     */
    public static function getLangStrings() {
        $strings = new stdClass();
        if (file_exists(themes_path() . '/ddos-gameboard/lang/lang.yaml')){
            $yamlpath = themes_path() . '/ddos-gameboard/lang/lang.yaml';
            Yaml::parseFile($yamlpath);
            $strings = Yaml::parseFile($yamlpath);
        }
        return $strings;
    }


}
