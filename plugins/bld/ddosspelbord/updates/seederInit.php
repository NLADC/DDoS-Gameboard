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

namespace Bld\Ddosspelbord\Updates;

use Seeder;
use Db;

class seederInit extends Seeder
{
    public function run()
    {
        // ROLES
        Db::table('bld_ddosspelbord_roles')->truncate();
        Db::table('bld_ddosspelbord_roles')->insert([
            ['name' => 'purple','display_name' => 'PURPLE team'],
            ['name' => 'blue','display_name' => 'BLUE team'],
            ['name' => 'red','display_name' => 'RED team'],
            ['name' => 'observer','display_name' => 'observer'],
        ]);

        // FIRST TRANSACTION
        Db::table('bld_ddosspelbord_transactions')->truncate();
        Db::table('bld_ddosspelbord_transactions')->insert([
            ['hash' => '78aeddcc63bd1bf725538b665d259650','type' => 'system', 'data' => ''],
        ]);

    }
}
