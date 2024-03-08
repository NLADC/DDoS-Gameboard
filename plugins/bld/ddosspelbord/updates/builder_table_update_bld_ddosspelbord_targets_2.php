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

class BuilderTableUpdateBldDdosspelbordTargets2 extends Migration
{
    public function up()
    {
        Schema::table('bld_ddosspelbord_targets', function($table)
        {
            $table->string('type', 40);
            $table->dropColumn('probes');
            $table->dropColumn('apikey');
            $table->dropColumn('protocol');
            $table->dropColumn('port');
            $table->dropColumn('query_argument');
            $table->dropColumn('interval');
            $table->dropColumn('one_off');
        });
    }
    
    public function down()
    {
        Schema::table('bld_ddosspelbord_targets', function($table)
        {
            $table->dropColumn('type');
            $table->string('probes', 191)->nullable();
            $table->string('apikey', 191)->nullable();
            $table->string('protocol', 191)->nullable();
            $table->integer('port')->nullable();
            $table->string('query_argument', 191)->nullable();
            $table->integer('interval')->nullable();
            $table->boolean('one_off')->nullable();
        });
    }
}
