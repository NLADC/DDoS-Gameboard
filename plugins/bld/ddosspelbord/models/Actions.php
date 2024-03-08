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

namespace Bld\Ddosspelbord\Models;

use bld\ddosspelbord\helpers\hLog;
use Db;
use Bld\Ddosspelbord\Controllers\Feeds;
use Model;

/**
 * Model
 */
class Actions extends Model
{
    use \Winter\Storm\Database\Traits\Validation;

    /**
     * @var string The database table used by the model.
     */
    public $table = 'bld_ddosspelbord_actions';

    /**
     * @var array Validation rules
     */
    public $rules = [
        'party_id' => 'required',
        'length'    => 'numeric|min:0',
        'delay'    => 'numeric|min:0',
        'extension'    => 'numeric|min:0',
    ];

    public $fillable = [
        'party_id',
        'start',
        'length',
        'tag',
        'name',
        'description',
        'delay',
        'extension',
        'has_issues',
        'is_cancelled',
    ];

    protected $hidden = [
        'party_id',
        'created_at', 'updated_at'
    ];

    public $hasOne = [
        'parties' => [
            'bld\ddosspelbord\models\Parties',
            'key' => 'id',
            'otherKey' => 'party_id'
        ],
    ];

    public function getPartyIdOptions() {

        $recs = Parties::orderBy('name')->select('id','name')->get();
        $ret = array();
        foreach ($recs AS $rec) {
            $ret[$rec->id] = $rec->name;
        }
        return $ret;
    }

    private $_skipchangedetection = false;
    private $_isChanged = false;
    private $_updated = [];
    private $_timeIsChanged = false;
    private $_timefields = ['start','length','delay','extension'];
    private $_oldstart = '';

    public function setSkip($skip=true) {
        $this->_skipchangedetection = $skip;
    }

    public function forceTimeChanged($timechanged=true) {
        $this->_timeIsChanged = $timechanged;
    }

    public function getTimeFields() {
        return $this->_timefields;
    }

    function isChanged() {

        $this->_isChanged = false;
        $this->_updated = [];
        if (!$this->_skipchangedetection) {
            $oldrec = Db::table('bld_ddosspelbord_actions')->where('id',$this->id)->first();
            if ($oldrec){
                foreach (['name','description','tag','start','length','delay','extension','has_issues','is_cancelled'] AS $field) {
                    if ($oldrec->$field != $this->$field) {
                        $this->_isChanged = true;
                        $this->_updated[$field] = $this->$field;
                    }
                }
            }
        }
    }

    function timeIsChanged() {

        $this->_timeIsChanged = false;
        if (!$this->_skipchangedetection) {
            $oldrec = Db::table('bld_ddosspelbord_actions')->where('id',$this->id)->first();
            if ($oldrec){
                $this->_oldstart = $oldrec->start;
                foreach ($this->_timefields AS $field) {
                    if ($oldrec->$field != $this->$field) {
                        $this->_timeIsChanged  = true;
                        break;
                    }
                }
            }
        }
    }

    public function beforeSave() {

        //hLog::logLine("D-Action beforeSave");

        // if start/delay/extension is changed
        $this->isChanged();
        $this->timeIsChanged();
    }

    public function afterSave() {

        //hLog::logLine("D-Action afterSave");

        $updates = [];
        $reversesort = false;

        if ($this->_isChanged) {

            hLog::logLine("D-Action is changed");

            $this->_updated['id'] = $this->id;
            $this->_updated['partyId'] = $this->party_id;
            if (isset($this->_updated['has_issues'])) $this->_updated['hasIssues'] = ($this->has_issues != 0);
            if (isset($this->_updated['is_cancelled'])) $this->_updated['isCancelled'] = ($this->is_cancelled != 0);

            // triggers clients with new action data
            $this->_updated['transactionType'] = 'action';
            $updates[$this->start] = $this->_updated;

            //(new Feeds())->createTransaction('action', $this->_updated);
        }

        if ($this->_timeIsChanged) {

            hLog::logLine("D-Action.times are changed; reset start times from actions (party_id=$this->party_id); new start$this->start, old=$this->_oldstart");

            $actions = Actions::where('party_id',$this->party_id)
                ->where('id','<>',$this->id)                // not ourself  (can be updated with futher time)
                ->where('start','>',$this->_oldstart)       // every older action
                ->orderBy('start','ASC')
                ->get();

            // shift next actions
            $next = strtotime($this->start) + $this->length + $this->delay + $this->extension;
            foreach ($actions AS $action) {

                hLog::logLine("D-Action [$action->tag] start=$action->start, next=".date('Y-m-d H:i:s',$next));

                // wanneer overlap dan opschuiven
                if ($next != strtotime($action->start)) {

                    $reversesort = ($next > strtotime($action->start));

                    $start = date('Y-m-d H:i:s',$next);
                    $upd = Db::table('bld_ddosspelbord_actions')->where('id',$action->id)
                        ->update([
                            'start' => $start,
                        ]);
                    hLog::logLine("D-Action [$action->tag] updated to start=$start");

                    $update = [
                        'id' => $action->id,
                        'start' => $start,
                        'partyId' => $action->party_id,
                        'transactionType' => 'actionsilent',
                    ];
                    $updates[$start] = $update;

                }

                $next = $next + $action->length + $action->delay + $action->extension;
            }
        }

        if (count($updates) > 0) {

            // van achter naar voren

            if ($reversesort) {
                krsort($updates, SORT_STRING);
            } else {
                ksort($updates, SORT_STRING );
            }

            foreach ($updates AS $start => $update) {
                hLog::logLine("D-Push action ".$update['id']." with start=$start to clients");
                (new Feeds())->createTransaction($update['transactionType'], $update);
            }

            /*
            $update = [
                'command' => 'refreshAll',
            ];
            (new Feeds())->createTransaction(TRANSACTION_TYPE_SYSTEM, $update);
            */

        }


    }

    /**
     * Reset START time of first action of every party
     *
     * @param $start
     */
    public function resetStartTime($start) {

        hLog::logLine("D-resetStartTime; start=$start");
        $parties = Parties::get();
        foreach ($parties AS $party) {

            // get first action
            $action = Actions::where('party_id',$party->id)
                ->orderBy('start','ASC')
                ->first();

            if ($action && $action->start != $start) {
                hLog::logLine("D-resetStartTime; party=$party->name; first action [$action->tag]; UPDATE; action start=$action->start");

                $action->start = $start;
                // trigger the reset job
                $action->save();
            } else {
                if ($action) hLog::logLine("D-resetStartTime; party=$party->name; first action [$action->tag], NO UPDATE needed");
            }

        }

    }

    /**
     * Reset start times actions of resetParties[party_id]=true
     *
     * @param array $resetParties
     */

    public function forceResetStartTimes($resetParties=[]) {

        hLog::logLine("D-forceResetStartTimes; number of parties with changed time(s): ".count($resetParties));
        $keys = array_keys($resetParties);
        $parties = Parties::whereIn('id',$keys)->get();
        foreach ($parties AS $party) {
            // get first action
            $action = Actions::where('party_id',$party->id)
                ->orderBy('start','ASC')
                ->first();
            if ($action) {
                hLog::logLine("D-forceResetStartTimes; reset start time from party=$party->name; first action [$action->tag]");
                $action->forceTimeChanged();
                $action->updated_at = date('Y-m-d H:i:s');  // force update record
                // trigger the reset job
                $action->save();
            } else {
                hLog::logLine("W-forceResetStartTimes; party=$party->name; NO actions found");
            }
        }

    }

}


