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
use Bld\Ddosspelbord\models\Settings;
use bld\ddosspelbord\models\Target;
use Bld\Ddosspelbord\models\Parties;
use Db;
use Illuminate\Support\Facades\Artisan;
use Schema;
use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use bld\ddosspelbord\classes\measurements\ripe\ripeMeasurements;

class testMeasurements extends Command
{

    protected $version = '0.9';

    /**
     * @var string The console command name.
     */
    protected $name = 'ddosgameboard:testMeasurements';
    protected $signature = 'ddosgameboard:testMeasurements {mode? : reset, run}
        {--s|sec= : timestamp interval seconds (default 60)}
        {--c|clip= : clipping ms (default 200ms)}
        ';

    /**
     * @var string The console command description.
     */
    protected $description = 'Generate DDoS gameboard TEST measurements';

    /**
     * Execute the console command.
     * @return void
     */
    public function handle() {

        hLog::setEcho(true);

        // log console options
        $this->info('DDoS gameboard generate TEST measurements '.$this->version);

        $mode = $this->argument('mode');
        if (empty($mode)) $mode = 'run';
        $showhelp = false;

        if (in_array($mode,['reset','run'])) {

            hLog::logLine("D-Generate test measurements; mode=$mode");

            // get exercise settings

            $title = Settings::get('description', '');
            if (empty($title)) $title = 'DDoS gameboard';   // always filled

            $exercise = Settings::getStartStopexercise();
            hLog::logLine("D-Found exercise '$title' with begin on '$exercise->first' and end on '$exercise->end'");

            switch ($mode) {

                case 'reset':

                    $del = Measurement::where('id','>',0)->delete();
                    hLog::logLine("D-Measurement record(s) deleted");

                    break;

                case 'run':

                    $sec = $this->option('sec');
                    if (empty($sec) || $sec < 1) $sec = 60;
                    $clip = $this->option('clip');
                    if (empty($clip) || $clip < 1) $clip = 200;
                    hLog::logLine("D-Go running filling measurements with interval $sec seconds, clipping $clip ms");

                    $date = new DateTime('now', new DateTimeZone('Europe/Amsterdam'));
                    $interval = $date->format('I') > 0 ? 2 : 1;
                    hLog::logLine("D-Current daylight saving interval=$interval (Europe/Amsterdam)");

                    while (true) {

                        $currenttime = date('Y-m-d H:i:s',strtotime("+$interval hours"));

                        hLog::logLine("D-Fill measurements with TEST responsetimes on $currenttime");

                        $cnt = 0;
                        $targets = Target::where('enabled',true)->get();
                        foreach ($targets AS $target) {

                            // rand values with  some clipping
                            $responsetime = round(mt_rand(10,($clip * 15)) / 11,2);

                            hLog::logLine("D-Add responsetime=$responsetime for $target->name at $currenttime");
                            Measurement::create([
                                'timestamp' => $currenttime,
                                'target_id' => $target->id,
                                'ipv' => 4,
                                'responsetime' => $responsetime,
                                'measurement_api_data_id' => 0,
                                'number_of_probes' => 1,
                            ]);
                            $cnt += 1;

                        }

                        hLog::logLine("D-Add $cnt measurement records; Sleep for $sec seconds...");
                        sleep($sec);

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

            Artisan::call('ddosgameboard:testMeasurements -h');
            $this->info(Artisan::output());

        }

    }

}
