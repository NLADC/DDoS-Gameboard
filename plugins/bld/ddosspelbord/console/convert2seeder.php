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

use Db;
use Schema;
use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class convert2seeder extends Command
{
    /**
     * @var string The console command name.
     */
    protected $name = 'ddosspelbord:convert2seeder';

    /**
     * @var string The console command description.
     */
    protected $description = 'Convert table records to seeder';

    /**
     * Execute the console command.
     * @return void
     */
    public function handle() {

        // log console options
        $this->info('Convert2seeder');

        $table = $this->option('table','');
        $withindex = $this->option('index','n');
        $withindex = ($withindex=='y');

        if ($table) {


            $excludefields = [
                'created_at', 'updated_at', 'deleted_at',
            ];
            if (!$withindex) {
                $excludefields[] = 'id';
            }

            $columns = Schema::getColumnListing($table);
            foreach ($columns AS $key => $column) {
                if (in_array($column,$excludefields)) {
                    unset($columns[$key]);
                }
            }

            $this->info(print_r($columns, true));

            $records = Db::table($table)->get();
            $seeder = "Db::table('$table')->insert([ \n";
            foreach ($records AS $record) {
                $seeder .= '[';
                foreach ($columns AS $key => $column) {
                    $value = $record->$column;
                    $value = str_replace("\n",'\n',$value);
                    $value = str_replace("\r",'\r',$value);
                    $value = str_replace('"','\\"',$value);
                    $seeder .= "'$column' => ".'"'.$value.'"'.",";
                }
                $seeder .= "], \n";
            }
            $seeder .= "]);";

            $this->info($seeder);


        } else {

           // $this->info("Use: abuseio:convert2seeder -t <table> -i y\n-t required; database table name\n-i optional; if y then also index (id) in output" );

        }


    }

    /**
     * Get the console command arguments.
     * @return array
     */
    protected function getArguments()
    {
        return [];
    }

    /**
     * Get the console command options.
     * @return array
     */
    protected function getOptions()
    {
        return [
            ['table', 't', InputOption::VALUE_REQUIRED, 'table', ''],
            ['index', 'i', InputOption::VALUE_OPTIONAL, 'index', 'n'],
        ];
    }


}
