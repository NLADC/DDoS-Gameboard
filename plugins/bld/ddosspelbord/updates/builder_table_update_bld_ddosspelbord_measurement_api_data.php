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

class BuilderTableUpdateBldDdosspelbordMeasurementApiData extends Migration
{
    public function up()
    {
        Schema::table('bld_ddosspelbord_measurement_api_data', function($table)
        {
            $table->integer('target_id')->unsigned();
            $table->dateTime('start_at')->nullable();
            $table->dateTime('end_at')->nullable();
            $table->renameColumn('measurement_datajson', 'datajson');
        });
    }
    
    public function down()
    {
        Schema::table('bld_ddosspelbord_measurement_api_data', function($table)
        {
            $table->dropColumn('target_id');
            $table->dropColumn('start_at');
            $table->dropColumn('end_at');
            $table->renameColumn('datajson', 'measurement_datajson');
        });
    }
}
