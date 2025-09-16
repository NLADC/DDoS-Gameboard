<?php
namespace Bld\Ddosspelbord\Updates;

use Db;
use Seeder;
use Winter\Storm\Database\Updates\Migration;

class RolesAdjustPersmissions2 extends Migration {
    public function up() {
        $adminPermissions = (object)[
            'bld.ddosspelbord.parties' => "1",
            'bld.ddosspelbord.startpage' => "1",
            'bld.ddosspelbord.spelbordusers' => "1",
            'bld.ddosspelbord.actions' => "1",
            'bld.ddosspelbord.actionplans' => "1",
            'bld.ddosspelbord.logs' => "1",
            'bld.ddosspelbord.attacks' => "1",
            'bld.ddosspelbord.access_settings' => "1",
            'bld.ddosspelbord.backendusers' => "1", // new
        ];
        $adminPermissions = json_encode($adminPermissions);

        Db::table('backend_user_roles')->updateOrInsert([ 'code' => "ddosgameboard-admin", ], [
            'name'        => "DDOS Gameboard Administrator",
            'description' => "Can configure everything, including the general Settings",
            'permissions' => "$adminPermissions",
            'is_system'   => "0",
        ]);

    }
}