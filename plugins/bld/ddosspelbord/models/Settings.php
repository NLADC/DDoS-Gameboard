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
use Model;
use Db;
use BackendAuth;
use DateTime;
use DateTimeZone;


/**
 * Model
 */
class Settings extends Model {

    use \Winter\Storm\Database\Traits\Validation;

    /**
     * @var array Validation rules
     */
    public $rules = [
        'startdate' => 'required',
        'firsttime' => 'required',
        'starttime' => 'required',
        'endtime' => 'required',
        'enddate' => 'required',
        'granularity'    => 'numeric|min:0',
        'maxexecutiontime'    => 'numeric|min:0',
        'logmaxfilesize'    => 'numeric|min:0',
        'logmaxfiles'    => 'numeric|min:0',
        'graphmaxresponsetime'    => 'numeric|min:0',
    ];

    public $implement = ['System.Behaviors.SettingsModel'];

    /**
     * @var string The database table used by the model.
     */
    public $settingsCode  = 'bld_ddosspelbord_settings';

    // Reference to field configuration
    public $settingsFields = 'fields.yaml';

    public function afterSave() {
        parent::afterSave();

        $user = BackendAuth::getUser();
        hLog::setUser($user);

        // force only date

        // bepaal start
        $startdate = date('Y-m-d',strtotime(Settings::get('startdate'))).' 00:00:00';
        $starttime = date('H:i:s',strtotime(Settings::get('starttime','03:00:00')));
        $start = str_replace('00:00:00',$starttime,$startdate);
        hLog::logLine("D-afterSave; startdate=$startdate, starttime=$starttime, start=$start");
        // controleer of start gewijzigd -> zo ja, voer door in acties
        (new Actions())->resetStartTime($start);
    }

    public static function getStartStopexercise() {

        $startstop = new \stdClass();

        // first (start)
        $startdate = date('Y-m-d 00:00:00',strtotime(Settings::get('startdate',date('Y-m-d'))));
        $enddate = date('Y-m-d 00:00:00',strtotime(Settings::get('enddate',date('Y-m-d'))));
        $firsttime = date('H:i:s', strtotime(Settings::get('firsttime', '02:30:00')));
        $startstop->first = str_replace('00:00:00', $firsttime, $startdate);
        $starttime = date('H:i:s', strtotime(Settings::get('starttime', '02:30:00')));
        $startstop->start = str_replace('00:00:00', $starttime, $startdate);
        $endtime = date('H:i:s', strtotime(Settings::get('endtime', '08:00:00')));
        $startstop->end = str_replace('00:00:00', $endtime, $enddate);
        // get UTC version
        $date = new DateTime('now', new DateTimeZone('Europe/Amsterdam'));
        $interval = $date->format('I') > 0 ? 2 : 1;
        hLog::logLine("D-getStartStopexercise; current daylight saving interval=$interval (Europe/Amsterdam)");
        $startstop->firstUTC = date('Y-m-d H:i:s',strtotime($startstop->first ." -$interval hour"));
        $startstop->endUTC = date('Y-m-d H:i:s',strtotime($startstop->end ." -$interval hour"));
        return $startstop;
    }


}


