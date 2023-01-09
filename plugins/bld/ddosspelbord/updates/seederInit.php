<?php namespace Bld\Ddosspelbord\Updates;

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
