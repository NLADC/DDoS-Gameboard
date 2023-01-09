<?php namespace Bld\Ddosspelbord\Models;

use \Backend\Models\ExportModel;

class ActionsExport extends ExportModel {

    public function exportData($columns, $sessionKey = null) {
        $actions = Actions::all();
        $actions->each(function($action) use ($columns) {
            $action->addVisible($columns);
            // fill as field value from hasOne relation parties
            $action->party = $action->parties->name;
        });
        return $actions->toArray();
    }


}
