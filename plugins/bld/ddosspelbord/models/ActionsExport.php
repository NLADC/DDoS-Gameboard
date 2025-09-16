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

namespace Bld\Ddosspelbord\Models;

use \Backend\Models\ExportModel;
use bld\ddosspelbord\classes\helpers\ddosspelbordUsers;
use bld\ddosspelbord\models\Action;

class ActionsExport extends ExportModel {

    public function exportData($columns, $sessionKey = null) {
        $query = Action::query();
        if (ddosspelbordUsers::filterOnParty()) {
            $partyId = ddosspelbordUsers::getBackendPartyId();
            $query->where('party_id',$partyId);
        }
        $actions = $query->get();
        $actions->each(function($action) use ($columns) {
            $action->addVisible($columns);
            // fill as field value from hasOne relation parties
            $action->party = (!empty($action->parties)) ? $action->parties->name : '';
        });
        return $actions->toArray();
    }


}
