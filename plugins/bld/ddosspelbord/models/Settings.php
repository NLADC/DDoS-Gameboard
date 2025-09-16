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
        'measurements_active'    => 'required|boolean',
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

        $timezone = new DateTimeZone(config('cms.backendTimezone'));
        $startdate = new DateTime(Settings::get('startdate', date('Y-m-d 00:00:00')), $timezone);
        $starttime = new DateTime(Settings::get('starttime', '03:00:00'));
        $starttime = $starttime->setTimezone($timezone); // Adjust to the backend timezone
        $start = str_replace('00:00:00', $starttime->format('H:i:s'), $startdate->format('Y-m-d 00:00:00'));

        hLog::logLine("D-afterSave; start=$start");
        (new Action())->resetStartTime($start);
    }

    public static function getStartStopexercise() {

        $startstop = new \stdClass();


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


        $startstop->first = str_replace('00:00:00', $firsttime->format('H:i:s'), $startdate->format('Y-m-d 00:00:00'));
        $startstop->start = str_replace('00:00:00', $starttime->format('H:i:s'), $startdate->format('Y-m-d 00:00:00'));
        $startstop->end = str_replace('00:00:00', $endtime->format('H:i:s'), $enddate->format('Y-m-d 00:00:00'));
        // get UTC version
        $date = new DateTime('now', new DateTimeZone('Europe/Amsterdam'));
        $interval = $date->format('I') > 0 ? 2 : 1;
        hLog::logLine("D-getStartStopexercise; current daylight saving interval=$interval (Europe/Amsterdam)");
        $startstop->firstUTC = date('Y-m-d H:i:s',strtotime($startstop->first ." -$interval hour"));
        $startstop->endUTC = date('Y-m-d H:i:s',strtotime($startstop->end ." -$interval hour"));
        return $startstop;
    }

    /**
     * Ensures default values are set after fetching settings.
     */
    public function afterFetch()
    {
        if (is_null($this->allowedInactivityInSeconds)) {
            $this->allowedInactivityInSeconds = 3600; // Set default value
        }

        if (is_null($this->allowedNonVisibleInSeconds)) {
            $this->allowedNonVisibleInSeconds = 3600; // Set default value
        }
    }

}


