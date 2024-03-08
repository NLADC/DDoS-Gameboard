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

class BuilderTableUpdateBldDdosspelbordTargets5 extends Migration
{
    public function up()
    {
        Schema::table('bld_ddosspelbord_targets', function($table)
        {
            $table->double('threshold_orange', 10, 0)->default(0);
            $table->double('threshold_red', 10, 0)->default(0);
        });
    }
    
    public function down()
    {
        Schema::table('bld_ddosspelbord_targets', function($table)
        {
            $table->dropColumn('threshold_orange');
            $table->dropColumn('threshold_red');
        });
    }
}
