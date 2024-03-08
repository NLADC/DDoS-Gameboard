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

namespace bld\ddosspelbord\Updates;

use Schema;
use Winter\Storm\Database\Updates\Migration;

class BuilderTableUpdateBldDdosspelbordMeasurements extends Migration
{
    public function up()
    {
        Schema::table('bld_ddosspelbord_measurements', function($table)
        {
            $table->string('ipv', 10)->nullable();
            $table->dateTime('timestamp')->nullable();
            $table->double('responsetime', 10, 0)->nullable();
            $table->integer('measurement_api_data_id')->nullable()->unsigned();
            $table->dropColumn('average_ttc');
            $table->dropColumn('average_rt');
            $table->dropColumn('succesrate');
            $table->dropColumn('errorcodes');

            $table->index(['timestamp','target_id'],'timestamp_target');
        });
    }

    public function down()
    {
        Schema::table('bld_ddosspelbord_measurements', function($table)
        {
            $table->dropColumn('ipv');
            $table->dropColumn('timestamp');
            $table->dropColumn('responsetime');
            $table->dropColumn('measurement_api_data_id');
            $table->integer('average_ttc')->nullable();
            $table->integer('average_rt')->nullable();
            $table->integer('succesrate')->nullable();
            $table->integer('errorcodes')->nullable();
        });
    }
}
