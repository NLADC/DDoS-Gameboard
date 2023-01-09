<?php

namespace bld\ddosspelbord\components;

/**
 * DDoS Spelbord
 * DDoS Gameboard
 *
 */

use Auth;
use Bld\Ddosspelbord\Models\Roles;
use Bld\Ddosspelbord\Models\Settings;
use Input;
use Config;
use Session;
use Redirect;
use Response;
use Bld\Ddosspelbord\Controllers\Feeds;
use Bld\Ddosspelbord\Models\Logs as Logs;
use Bld\Ddosspelbord\Models\Attack as Attack;
use Bld\Ddosspelbord\Models\Transactions;
use Bld\Ddosspelbord\Models\spelbordusers;
use Cms\Classes\ComponentBase;
use bld\ddosspelbord\helpers\hLog;
use Bld\Ddosspelbord\Models\Parties;

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
            $startdate = Settings::get('startdate', date('Y-m-d'));
            $enddate = Settings::get('enddate', date('Y-m-d'));

            $firsttime = date('H:i:s', strtotime(Settings::get('firsttime', '02:30:00')));
            $first = str_replace('00:00:00', $firsttime, $startdate);
            $starttime = date('H:i:s', strtotime(Settings::get('starttime', '03:00:00')));
            $start = str_replace('00:00:00', $starttime, $startdate);

            $endtime = date('H:i:s', strtotime(Settings::get('endtime', '08:00:00')));
            $end = str_replace('00:00:00', $endtime, $enddate);

            // Scroll (session)
            $scroll = Session::get(SESSION_SET_SCROLL, false);
            $scroll = ($scroll) ? 'true' : 'false';

            // Note: in minutes
            $granularity = Settings::get('granularity', '1');

            $logmaxfilesizeinmb = Settings::get('logmaxfilesize');
            $logmaxfilesize = $logmaxfilesizeinmb * 1024 * 1024;

            $logmaxfiles = Settings::get('logmaxfiles');

            hLog::logLine("D-ddosspelbord_data; title=$title, first=$first, start=$start, end=$end");

            // Save hash current user session
            Feeds::setLastsettingHash($hash);

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
                'parties' => $parties,
                'user' => addslashes(json_encode($user)),
                'logs' => addslashes(json_encode($logs)),
                'attacks' => addslashes(json_encode($attacks)),
                'csrftoken' => Session::token(),
                'loggedin' => true,
                'scroll' => $scroll,
                'logmaxfilesize' => $logmaxfilesize,
                'logmaxfiles' => $logmaxfiles,
                'acceptedfiletypes' =>  implode(',',Config::get('bld.ddosspelbord::acceptedfiletypes')),
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

            hLog::logLine("D-ddosspelbord_data; no access data");

            $data = [
                'hash' => $hash,
                'parties' => $empty,
                'user' => $empty,
                'logs' => $empty,
                'attacks' => $empty,
                'csrftoken' => Session::token(),
                'loggedin' => false,
                'scroll' => 'false',
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
        // Convert to vue code
        if ($auth) {
            $spelborduser = Spelbordusers::where('user_id', $user_id)->first();
        } else {
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

            $logs = Logs::join(DB_TABLE_USERS, DB_TABLE_USERS . '.id', '=', DB_TABLE_LOGS . '.user_id')
                ->where(DB_TABLE_USERS . '.party_id', '=', $party_id)
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
                    if ($userRole == DB_ROLE_PURPLE || $spelborduser['role'] == $userRole) {
                        $spelbordlog = $log->toArray();
                        $spelbordlog['user'] = $spelborduser;
                        $spelbordlog['user']['party'] = ($party) ? $party : [
                            'id' => 0,
                            'name' => NO_PARTY_NAME,
                        ];
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
                        $spelboardattack['user']['party'] = ($party) ? $party : [
                            'id' => 0,
                            'name' => NO_PARTY_NAME,
                        ];
                        $spelboardattack['partyId'] = $party_id;
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
    public static function getSpelbordlog($log, $hasattachments = false) {
        // Array values
        $log = $log->toArray();
        $user = self::getSpelborduser($log['user_id']);
        $party = Parties::find($user['partyId']);
        if ($hasattachments) {
            $attachmentsmodels = self::getLogAttachments($log['id']);
            $attachments = self::exportLogAttachments($attachmentsmodels);
            $log['attachments'] = $attachments;
        }
        $log['user'] = $user;
        $log['user']['party'] = ($party) ? $party->toArray() : [
            'id' => 0,
            'name' => NO_PARTY_NAME,
        ];
        $log['partyId'] = $user['partyId'];

        return $log;
    }

    /**
     * @param $attack
     * @return object
     */
    public static function getSpelbordAttack($attack)  {
        // Array values
        $attack = $attack->toArray();
        $user = self::getSpelborduser($attack['user_id']);
        $party = Parties::find($user['partyId']);

        $attack['user'] = $user;
        $attack['user']['party'] = ($party) ? $party->toArray() : [
            'id' => 0,
            'name' => NO_PARTY_NAME,
        ];
        $attack['partyId'] = $user['partyId'];

        return $attack;
    }
}
