<?php
namespace Bld\Ddosspelbord\Updates;

use Db;
use Seeder;
use Winter\Storm\Database\Updates\Migration;

class RolesAdjustPersmissions5 extends Migration {
    public function up() {
        $managerPermissions = (object)[
            'bld.ddosspelbord.spelbordusers' => "1",
            'bld.ddosspelbord.actions' => "1",
            'bld.ddosspelbord.export_actions' => "1", // new
            'bld.ddosspelbord.actionplans' => "1",
            'bld.ddosspelbord.logs' => "1",
            'bld.ddosspelbord.attacks' => "1",
        ];
        $managerPermissions = json_encode($managerPermissions);


        Db::table('backend_user_roles')->updateOrInsert([ 'code' => "ddosgameboard-manager", ], [
            'name'        => "DDOS Gameboard Manager",
            'description' => "Manager bound to everything related within their respective party",
            'permissions' => "$managerPermissions",
            'is_system'   => "0",
        ]);

        $adminPermissions = (object)[
            'bld.ddosspelbord.parties' => "1",
            'bld.ddosspelbord.startpage' => "1",
            'bld.ddosspelbord.spelbordusers' => "1",
            'bld.ddosspelbord.actions' => "1",
            'bld.ddosspelbord.import_actions' => "1", // new
            'bld.ddosspelbord.export_actions' => "1", // new
            'bld.ddosspelbord.actionplans' => "1",
            'bld.ddosspelbord.apply_actionplans' => "1", // new
            'bld.ddosspelbord.logs' => "1",
            'bld.ddosspelbord.attacks' => "1",
            'bld.ddosspelbord.access_settings' => "1",
            'bld.ddosspelbord.backendusers' => "1",
            'bld.ddosspelbord.monitor' => "1",
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
