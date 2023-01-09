<?php namespace Bld\Ddosspelbord\Models;

use \Backend\Models\ExportModel;

class SpelbordusersExport extends ExportModel {

    public function exportData($columns, $sessionKey = null) {

        $spelbordusers = Spelbordusers::all();

        $spelbordusers->each(function($spelborduser) use ($columns) {
            $spelborduser->addVisible($columns);
            // fill as field value from hasOne relation parties
            $spelborduser->party = (isset($spelborduser->parties->name)) ? $spelborduser->parties->name : '';
            $spelborduser->role = $spelborduser->roles->name;
        });
        return $spelbordusers->toArray();
    }


}
