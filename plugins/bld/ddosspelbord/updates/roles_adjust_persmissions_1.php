<?php
namespace Bld\Ddosspelbord\Updates;

use Db;
use Seeder;
use Winter\Storm\Database\Updates\Migration;

class RolesAdjustPersmissions1 extends Migration {
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
        ];
        $adminPermissions = json_encode($adminPermissions);

        $managerPermissions = (object)[
            'bld.ddosspelbord.startpage' => "1",
            'bld.ddosspelbord.spelbordusers' => "1",
            'bld.ddosspelbord.actions' => "1",
            'bld.ddosspelbord.actionplans' => "1",
            'bld.ddosspelbord.logs' => "1",
            'bld.ddosspelbord.attacks' => "1",
        ];
        $managerPermissions = json_encode($managerPermissions);

        $apiPermissions = (object)[
            'bld.ddosspelbord.access_api' => "1"
        ];
        $apiPermissions = json_encode($apiPermissions);

        Db::table('backend_user_roles')->updateOrInsert([ 'code' => "ddosgameboard-admin", ], [
            'name'        => "DDOS Gameboard Administrator",
            'description' => "Can configure everything, including the general Settings",
            'permissions' => "$adminPermissions",
            'is_system'   => "0",
        ]);

        Db::table('backend_user_roles')->updateOrInsert([ 'code' => "ddosgameboard-manager", ], [
            'name'        => "DDOS Gameboard Manager",
            'description' => "Manager bound to everything related within their respective party",
            'permissions' => "$managerPermissions",
            'is_system'   => "0",
        ]);

        Db::table('backend_user_roles')->updateOrInsert([ 'code' => "ddosgameboard-api", ], [
            'name'        => "DDOS Gameboard Api",
            'description' => "You need 1 user with API permissions in your system to utilise the API at all",
            'permissions' => "$apiPermissions",
            'is_system'   => "0",
        ]);

    }
}
