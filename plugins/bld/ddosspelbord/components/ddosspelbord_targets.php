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
use Bld\Ddosspelbord\Controllers\Feeds;
use Bld\Ddosspelbord\Models\Settings;
use bld\ddosspelbord\models\Target_groups;
use Bld\Ddosspelbord\Models\Transactions;
use Input;
use Config;
use Session;
use Redirect;
use Response;
use Bld\Ddosspelbord\Models\Measurement as Measurement;
use Bld\Ddosspelbord\Models\spelbordusers;
use Cms\Classes\ComponentBase;
use bld\ddosspelbord\helpers\hLog;
use Bld\Ddosspelbord\Models\Parties;
use Bld\Ddosspelbord\Models\Target;
use stdClass;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Yaml;

class ddosspelbord_targets extends ComponentBase
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
        hLog::logLine("D-ddosspelbord_targets.init; version=" . self::getVersion());
    }

    /**
     */

    public function onRun()
    {

        $this->page['targetsdashboard'] = $this->getGameboardTargets();
    }

    /** VUE targets HANDLING **/

    /**
     * This is based on the ddosspelbord_data component
     *
     */

    /**
     * getGameboardTargets()
     *
     * Called by loading targetsdashboard
     *
     * @return array
     */
    public function getGameboardTargets()
    {
        hLog::logLine("D-ddosspelbord_targets.getTargets");


        // empty object
        $empty = json_encode((object)[]);

        if (spelbordusers::verifyAccess(false)) {

            $title = Settings::get('description', '');
            if (empty($title)) $title = 'DDoS gameboard';   // always filled

            // get first/start/stop/utc times
            $exercise = Settings::getStartStopexercise();

            // get global max responsetime
            $graphmaxresponsetime = Settings::get('graphmaxresponsetime');

            // -1- lang
            $lang = $this->getLangStrings();

            // -2- Measurements
            $measurements = $this->getMeasurements();
            if (empty($measurements)) {
                $measurements = (object)[];
            }

            // -3- Latest timestamp
            $latesttimestamp = $this->getLatestTimestamp();

            // -4- groups
            $groups = $this->getGroups();

            $data = [
                'loggedin' => true,
                'lang' => json_encode($lang),
                'graphmaxresponsetime' => $graphmaxresponsetime,
                'measurements' => json_encode($measurements),
                'latesttimestamp' => $latesttimestamp,
                'access' => true,
                'csrftoken' => Session::token(),
                'groups' => $groups,
            ];

            // let's get to work
            $targetsdashboard = [
                'title' => $title,
                'firsttime' => $exercise->first,
                'starttime' => $exercise->start,
                'endtime' => $exercise->end,
                'version' => self::getVersion(),
                'data' => $data,
            ];

        } else {

            hLog::logLine("D-ddosspelbord_data; no access targetsdata");

            $data = [
                'loggedin' => false,
                'firsttime' => $empty,
                'starttime' => $empty,
                'endtime' => $empty,
                'version' => $empty,
                'acces' => false,
                'data' => $empty,
                'csrftoken' => Session::token(),
                'groups' => $empty,
            ];

            // note; empty title is indication for not showing any data (html)
            $targetsdashboard = [
                'title' => '',
                'data' => $data,
            ];

        }

        return $targetsdashboard;
    }

    /**
     * This will return an interpretable object that the Front-end theme can create a nice dashboard from
     * Also the function that the api calls for updating the charts
     * @return array
     */
    public function getMeasurements() {

        $omeasurements = [];

        $groups = Target_groups::orderBy('sortnr','ASC')->get();

        $cnt = 0;

        foreach ($groups AS $group) {
            $targetcollection = [];
            $targets = Target::where('enabled',true)->where('groups',$group->name)->get();

            foreach ($targets AS $target) {
                $thresholds = (object)[
                    'orange' => $target->threshold_orange,
                    'red' => $target->threshold_red,
                ];

                $targetbundle = (object)[
                    'name' => $target->name,
                    'target' => $target->target,
                    'thresholds' => $thresholds,
                    'data' => $this->createMeasurementsDataArray($target->id),
                ];

                $cnt += count($targetbundle->data);
                array_push($targetcollection, $targetbundle);
            }

            // show only when targets
            if (count($targetcollection) > 0) {
                $targetwrapper = (object)[
                    'name' => $group->name,
                    'cliprt' => $group->graphresponsetimeclipvalue,  // Clipwaarde van de grafiek
                    'data' => $targetcollection,
                ];

                array_push($omeasurements,$targetwrapper);
            }

        }

        hlog::logLine("D-ddosspelbord_data; getmeasurements; number of measurments=$cnt");

        return $omeasurements;
    }

    /**
     * This is the function that will actually retrieve timestamps and responsetimes, the X and Y in the graph
     * @return array
     */
    public function createMeasurementsDataArray($targetid)
    {
        $measurements = Measurement::where('target_id',$targetid)->orderBy('timestamp','ASC')->get();
        $return = [];
        foreach ($measurements as $measurement) {
            if ($measurement->responsetime > 0) {

                $data = (object)[
                    'timestamp' => $measurement->timestamp,
                    'responsetime' => $measurement->responsetime,
                ];

                array_push($return, $data);
            }
        }
        return $return;
    }

    /**
     * Wil return the latest timestamp for referencing by the graph
     * @return string
     */
    public function getLatestTimestamp()
    {
        $measurement = Measurement::where('id', '>', 0)->orderBy('timestamp','DESC')->get()->first();
        $timestamp = (!empty($measurement) && !empty($measurement->timestamp)) ? $measurement->timestamp: "1970-01-01 00:00:00";

        return $timestamp;
    }


    /**
     * This will return an interpretable object that the Front-end theme can create a nice dashboard from
     * Also the function that the api calls for updating the charts
     * @return array
     */
    public function streamMeasurements()
    {
        if (Spelbordusers::verifyAccess(false)) {

            // Get property (first time) hash
            $hash = $this->property('hash');
            if ($hash=='') {
                $hash = (new Transactions())->getLastTransactionHash();
            }

            $mode = post('mode','streamfeeds');

            // New feeds
            $feed = new Feeds();

            if ($mode == 'streamfeeds') {
                // start stream
                $response = $feed->startStream($hash);
                // set headers
                $response->headers->set('Content-Type', 'text/event-stream');
                $response->headers->set('X-Accel-Buffering', 'no');
                $response->headers->set('Cache-Control', 'no-cache');
            } elseif ($mode == 'readfeeds') {
                $measurements = $this->getMeasurements();

                $feed = new Feeds();

                $lasthash = $feed->getLastsettingHash();
                hLog::logLine("D-ddosspelbord_feed; read from last (user) hash=$lasthash");

                // Latest timestamp
                $latesttimestamp = $this->getLatestTimestamp();


                $response = Response::json([
                    'result' => true,
                    'message' => '',
                    'measurements' => $measurements,
                    'latesttimestamp' => $latesttimestamp
                ]);
                $response->headers->set('Content-Type', 'application/json');

                return $response;
            }
        }
        else {
            // response -> force reload (logout) screen
            $response = new StreamedResponse(function() {

                hLog::logLine("D-ddosspelbord_targets; return empty response with not logged in");
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
    }

    public function getGroups() {

        $ogroups = [];
        $groups = Target_groups::orderBy('sortnr','ASC')->get();
        foreach ($groups AS $group) {
            $targets = Target::where('enabled',true)->where('groups',$group->name)->count();
            if ($targets) {
                $ogroup = $group->toArray();
                $ogroup['show'] = true;
                $ogroups[$group->id] = $ogroup;
            }
        }
        hLog::logLine("D-ddosspelbord_data; getGroups; ".count($ogroups)." found");
        return addslashes(json_encode($ogroups));
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
