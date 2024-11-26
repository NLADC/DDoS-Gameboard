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

namespace bld\ddosspelbord\console;

use DateTime;
use DateTimeZone;
use bld\ddosspelbord\helpers\hLog;
use bld\ddosspelbord\models\Measurement;
use bld\ddosspelbord\models\Measurement_api;
use bld\ddosspelbord\models\Measurement_api_data;
use Bld\Ddosspelbord\models\Settings;
use bld\ddosspelbord\models\Target;
use Db;
use Illuminate\Support\Facades\Artisan;
use Schema;
use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use bld\ddosspelbord\classes\measurements\ripe\ripeMeasurements;

class measurementAPI extends Command
{

    protected $version = '0.9';

    /**
     * @var string The console command name.
     */
    protected $name = 'ddosspelbord:measurementAPI';
    protected $signature = 'ddosspelbord:measurementAPI {mode? : show, create or measure}
        {--m|mid= : measurement ID}
        {--k|key= : the API key}
        {--s|start= : start time (yyyy-mm-dd hh:mm) (UTC)}
        ';

    /**
     * @var string The console command description.
     */
    protected $description = 'DDoS gameboard measurements';

    /**
     * Execute the console command.
     * @return void
     */
    public function handle() {

        $startrun = microtime(true);

        hLog::setEcho(true);

        // log console options
        $this->info('DDoS gameboard measurementAPI interface version '.$this->version);

        $mode = $this->argument('mode');
        if (empty($mode)) $mode = 'show';
        hLog::logLine("D-measurementAPI; mode=$mode");

        $showhelp = false;

        if (in_array($mode,['create','measure','show'])) {

            // get exercise settings

            $title = Settings::get('description', '');
            if (empty($title)) $title = 'DDoS gameboard';   // always filled

            $exercise = Settings::getStartStopexercise();

            // get UTC version
            $date = new DateTime('now', new DateTimeZone('Europe/Amsterdam'));
            $interval = $date->format('I') > 0 ? 2 : 1;

            $currenttime = date('Y-m-d H:i:s',strtotime("+$interval hours"));

            switch ($mode) {

                case 'create':

                    hLog::logLine("D-Found exercise '$title' with begin on '$exercise->first' and end on '$exercise->end'");

                    if ($currenttime < $exercise->first) {

                        $targets = Target::where('enabled',true)->get();

                        foreach ($targets AS $target) {

                            // UTC
                            $data_api = Measurement_api_data::where('target_id',$target->id)->where('start_at',$exercise->firstUTC);

                            if ($data_api->doesntExist()) {

                                $measurement_api = Measurement_api::where('id',$target->measurement_api_id)->first();

                                if ($measurement_api) {

                                    $configjson = json_decode($measurement_api->configjson);
                                    //hLog::logLine("D-Configjson=".print_r($configjson,true));

                                    $key = $measurement_api->apikey;
                                    $billing = $measurement_api->billingemail;

                                    hLog::logLine("D-CREATE measurement [key=$key] for target '$target->name' for $exercise->firstUTC (UTC) till $exercise->endUTC (UTC)");

                                    // replace target fields
                                    $configjson->definitions[0]->target = $target->target;
                                    $configjson->definitions[0]->hostname = $target->target;

                                    $configjson->definitions[0]->af = $target->ipv;
                                    $configjson->definitions[0]->description = $target->name;
                                    $configjson->is_oneoff = false;
                                    $configjson->start_time = strtotime($exercise->firstUTC);
                                    $configjson->stop_time = strtotime($exercise->endUTC);
                                    if ($billing) $configjson->bill_to = $billing;

                                    if (isset($configjson->definitions[0]->query_argument)) {
                                        // fill DNS query type
                                        $configjson->definitions[0]->query_argument = $target->target;
                                    }

                                    hLog::logLine("D-NEW Configjson=".print_r($configjson,true));

                                    $configjson = json_encode($configjson);

                                    ripeMeasurements::setKey($key);
                                    $result = ripeMeasurements::createMeasurement($configjson);
                                    //hLog::logLine("D-Result=".print_r($result,true));

                                    if ($result) {

                                        $data_api = new Measurement_api_data();
                                        $data_api->measurement_api_id = $target->measurement_api_id;
                                        $data_api->target_id = $target->id;
                                        $data_api->start_at = $exercise->firstUTC;
                                        $data_api->end_at = $exercise->endUTC;
                                        $data_api->datajson = $result;
                                        $data_api->save();

                                        if (isset($data_api->datajson['measurements'])) {
                                            hLog::logLine("D-Measurement id '".$data_api->datajson['measurements'][0]."' created");
                                        }

                                    } else {
                                        hLog::logLine("E-Error create measurement; result=".print_r($result,true));
                                    }

                                } else {
                                    hLog::logLine("W-Cannot find measurement API for target '$target->name'!? (id=$target->measurement_api_id)");
                                }

                            } else {
                                $data_api = $data_api->first();
                                //hLog::logLine("D-Datajson=".print_r($data_api->datajson,true));
                                $mid = (isset($data_api->datajson['measurements']) ? $data_api->datajson['measurements'][0] : '???');
                                hLog::logLine("D-Measurement for target '$target->name' for $data_api->start_at (UTC) till $data_api->end_at (UTC) exist; MID=$mid");
                            }

                        }

                    } else {
                        hLog::logLine("D-Exercise already started - skip create");
                    }

                    break;

                case 'measure':

                    // get measurements based on measure api data and save in bld_ddosspelbord_measurement_api

                    hLog::logLine("D-get measurements from targets from exercise '$title' with begin on '$exercise->first' and end on '$exercise->end'");

                    $endtime = date('Y-m-d H:i:s',strtotime($exercise->end. " +1 hour"));
                    if ($currenttime >= $exercise->first && $currenttime <= $endtime) {

                        $targets = Target::where('enabled',true)->get();

                        foreach ($targets AS $target) {

                            $data_api = Measurement_api_data::where('target_id', $target->id)->where('start_at', $exercise->firstUTC);

                            if ($data_api->exists()) {

                                $cnt = 0;

                                $data_apis = $data_api->get();
                                foreach ($data_apis as $data_api) {

                                    $datajson = $data_api->datajson;

                                    $mid = (isset($datajson['measurements']) ? $datajson['measurements'][0] : '');
                                    $last_fetch = (isset($datajson['last_fetch']) ? $datajson['last_fetch'] : '');

                                    if ($mid) {

                                        hLog::logLine("D-Target '$target->name', mid=$mid, last_fetch=$last_fetch ");

                                        if ($last_fetch) $last_fetch = strtotime($last_fetch." +1 minute");
                                        // Note: RIPE ATLAS measurements in UTC
                                        $results = ripeMeasurements::getMeasurementsTimedResult($mid, $last_fetch);
                                        if ($results) {

                                            foreach ($results as $result) {

                                                // get actual timezone timestamp
                                                $timestamp = date('Y-m-d H:i:s',strtotime($result['timestamp']. " +$interval hours"));

                                                // only one measurement for each timestamp
                                                $measurement = Measurement::where('timestamp', $timestamp)
                                                    ->where('target_id', $target->id)
                                                    ->where('ipv', $result['ipversion'])
                                                    ->where('measurement_api_data_id', $data_api->id);
                                                if ($measurement->doesntExist()) {

                                                    Measurement::create([
                                                        'timestamp' => $timestamp,
                                                        'target_id' => $target->id,
                                                        'ipv' => $result['ipversion'],
                                                        'responsetime' => $result['rtt'],
                                                        'measurement_api_data_id' => $data_api->id,
                                                        'number_of_probes' => $result['probe_count'],
                                                    ]);
                                                    $cnt += 1;
                                                }

                                            }

                                            // get last time -> save this for next time
                                            $last = Measurement::where('target_id', $target->id)
                                                ->where('ipv', $result['ipversion'])
                                                ->where('measurement_api_data_id', $data_api->id)
                                                ->orderBy('timestamp', 'DESC')
                                                ->first();
                                            if ($last) {
                                                // get UTC timestamp
                                                $timestampUTC = date('Y-m-d H:i:s',strtotime($last->timestamp. " -$interval hours"));
                                                $datajson['last_fetch'] = $timestampUTC;
                                                $data_api->datajson = $datajson;
                                                $data_api->save();
                                            }

                                        } else {

                                            /*

                                            // no results -> check last_fetch -> if set and 2 minutes ago, then RED

                                            if ($last_fetch) {

                                                $nextfetch = date('Y-m-d H:i:s',strtotime($last_fetch." +2 minute"));
                                                if ($currenttime >= $nextfetch) {

                                                    // deze minuut op ROOD zetten

                                                    // zet op de minuut
                                                    $timestamp = date('Y-m-d H:i:00',strtotime($currenttime));
                                                    $measurement = Measurement::where('timestamp', $timestamp)
                                                        ->where('target_id', $target->id)
                                                        ->where('ipv', $target->ipv)
                                                        ->where('measurement_api_data_id', 0);
                                                    if ($measurement->doesntExist()) {
                                                        hLog::logLine("D-Insert RED minute for target '$target->name' on $timestamp");
                                                        Measurement::create([
                                                            'timestamp' => $timestamp,
                                                            'target_id' => $target->id,
                                                            'ipv' => $target->ipv,
                                                            'responsetime' => ($target->threshold_red + 60000),     // extreem value
                                                            'measurement_api_data_id' => 0,
                                                            'number_of_probes' => 0,
                                                        ]);
                                                        $cnt += 1;
                                                    }

                                                    // do NOT set last_fetch here -> measurements are behind actual time

                                                }

                                            }
                                            */

                                        }

                                    } else {
                                        hLog::logLine("D-measurementAPI; no mid found!?");
                                    }

                                }

                                hLog::logLine("D-For target $cnt measurement records inserted");

                            } else {
                                hLog::logLine("D-measurementAPI; no api data (record) found");
                            }
                        }

                    } else {
                        hLog::logLine("D-Exercise not yet started or over; currenttime=$currenttime");
                    }

                    break;

                case 'show':

                    // show (measure) -> can be one specified of

                    $mid = $this->option('mid');
                    $key = $this->option('key','');
                    $start = $this->option('start','');

                    // set if set
                    if ($start) $start = strtotime($start);

                    // for public measurements the key can be empty
                    if ($key) ripeMeasurements::setKey($key);

                    if ($mid) {

                        hLog::logLine("D-measurementAPI; $mode; key=$key, mid=$mid, start=$start");

                        $result = ripeMeasurements::getMeasurementsTimedResult($mid,$start);
                        if ($result) {
                            $this->table(['type','timestamp','ipv','response (ms)','probes'],$result);
                        } else {
                            hLog::logLine("D-measurementAPI; no results");
                        }

                    } else {

                        // get all targets find mid -> show

                        $targets = Target::where('enabled',true)->get();
                        hLog::logLine("D-Show targets from exercise '$title' with begin on '$exercise->first' and end on '$exercise->end'; target count=".count($targets));

                        foreach ($targets AS $target) {

                            hLog::logLine("D-Show results for target '$target->name'");

                            $data_api = Measurement_api_data::where('target_id', $target->id)->where('start_at', $exercise->firstUTC);

                            if ($data_api->exists()) {

                                $data_apis = $data_api->get();

                                foreach ($data_apis AS $data_api) {

                                    $mid = (isset($data_api->datajson['measurements']) ? $data_api->datajson['measurements'][0] : '');

                                    if ($mid) {

                                        $results = ripeMeasurements::getMeasurementsTimedResult($mid,$start);
                                        if ($results) {
                                            $this->table(['type','timestamp','ipv','response (ms)','probes'],$results);
                                        } else {
                                            hLog::logLine("D-measurementAPI; no results");
                                        }

                                    } else {
                                        hLog::logLine("D-measurementAPI; no mid found!?");
                                    }

                                }

                            } else {
                                hLog::logLine("D-measurementAPI; no api data (record) found");
                            }

                        }

                    }

                    break;

                default:
                    $showhelp = true;
                    break;

            }

        } else {
            $showhelp = true;
        }

        if ($showhelp)  {

            Artisan::call('ddosspelbord:measurementAPI -h');
            $this->info(Artisan::output());

        }

        $secs = round(microtime(true) - $startrun,3);
        hLog::logLine("D-End; $secs sec needed");

    }

}
