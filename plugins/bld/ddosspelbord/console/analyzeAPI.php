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
use bld\ddosspelbord\Models\Measurement;
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

class analyzeAPI extends Command
{

    protected $version = '0.9';

    /**
     * @var string The console command name.
     */
    protected $name = 'ddosgameboard:analyzeAPI';
    protected $signature = 'ddosgameboard:analyzeAPI {mode? : save|analyze}
        {--f|file= : file with the RIPE ATLAS json data}
        {--s|start= : start time (yyyy-mm-dd hh:mm) (UTC)}
        ';

    /**
     * @var string The console command description.
     */
    protected $description = 'DDoS gameboard analyze RIPE measurements';

    /**
     * Execute the console command.
     * @return void
     */
    public function handle() {

        $startrun = microtime(true);

        hLog::setEcho(true);

        // log console options
        $this->info('DDoS gameboard analyze RIPE measurements version '.$this->version);

        $mode = $this->argument('mode');
        if (empty($mode)) $mode = 'show';
        hLog::logLine("D-analyzeAPI; mode=$mode");

        $showhelp = false;

        if (in_array($mode,['save','analyze'])) {

            // get exercise settings

            $title = Settings::get('description', '');
            if (empty($title)) $title = 'DDoS gameboard';   // always filled

            $exercise = Settings::getStartStopexercise();

            // get UTC version
            $date = new DateTime('now', new DateTimeZone('Europe/Amsterdam'));
            $interval = $date->format('I') > 0 ? 2 : 1;

            $currenttime = date('Y-m-d H:i:s',strtotime("+$interval hours"));

            switch ($mode) {

                case 'save':

                    // get measurements based on measure api data and save in bld_ddosspelbord_measurement_api

                    hLog::logLine("D-SAVE RIPE measurements from targets from exercise '$title' with begin on '$exercise->first' and end on '$exercise->end'");

                    if ($currenttime >= $exercise->first) {

                        $targets = Target::where('enabled',true)->get();

                        foreach ($targets AS $target) {

                            $data_api = Measurement_api_data::where('target_id', $target->id)->where('start_at', $exercise->firstUTC);

                            if ($data_api->exists()) {

                                $data_apis = $data_api->get();
                                foreach ($data_apis as $data_api) {

                                    $datajson = $data_api->datajson;

                                    $mid = (isset($datajson['measurements']) ? $datajson['measurements'][0] : '');

                                    if ($mid) {

                                        hLog::logLine("D-Target '$target->name', mid=$mid ");

                                        // get all results
                                        $results = ripeMeasurements::getResults($mid);

                                        if ($results) {

                                            $results = json_encode($results);

                                            // save into filename with reference to measurement-id and target
                                            $name = str_replace([' ', '/'], '_', $target->name);
                                            $filename = date('YmdHi',strtotime($exercise->first)) . '_' . $mid . '_' . $target->id . '_' . $name . '.ripe';

                                            hLog::logLine("D-Save results from '$target->name' in '$filename'");
                                            file_put_contents($filename, $results);

                                        }

                                    }
                                }
                            }
                        }

                    } else {
                        hLog::logLine("D-Exercise not yet started; currenttime=$currenttime");
                    }

                    break;

                case 'analyze':

                    $file = $this->option('file');

                    if ($file) {

                        $filearr = explode('_',$file);
                        $timestamp = array_shift($filearr);
                        $mid = array_shift($filearr);
                        $target_id = array_shift($filearr);
                        $target_name = implode(' ',$filearr);
                        $target_name = str_replace('.ripe','',$target_name);

                        hLog::logLine("D-File from exercise first=$timestamp, mid=$mid, target_id=$target_id, name=$target_name");

                        $json = file_get_contents($file);
                        $data = json_decode($json);
//                        $this->info(print_r($data[0],true));

                        $result = ripeMeasurements::getMeasurementsTimedResult('','',$data,true);
                        if ($result) {

                            $this->table(['type','timestamp','ipv','response (ms)','probes'],$result);

                            hLog::logLine("D-Number of results: ".count($result));


                        } else {
                            hLog::logLine("D-measurementAPI; no results");
                        }

                        /*
                        $probedata = [];
                        foreach ($data AS $probe) {

                            $type = (isset($probe->type)) ? $probe->type : '';
                            $timestamp = (isset($probe->timestamp)) ? $probe->timestamp : '';
                            $ipversion = (isset($probe->af)) ? $probe->af : '';

                            if ($timestamp) {
                                $timestamp = date('Y-m-d H:i:s',$timestamp);
                                $probedata[] = [$type,$timestamp,$ipversion];
                            }
                        }

                        $this->table(['type','timestamp','ipv'],$probedata);
                        */

                    } else {
                        hLog::logLine("W-Input file with json data is missing");
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

            Artisan::call('ddosgameboard:analyzeAPI -h');
            $this->info(Artisan::output());

        }

        $secs = round(microtime(true) - $startrun,3);
        hLog::logLine("D-End; $secs sec needed");

    }

}
