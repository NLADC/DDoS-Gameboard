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
use ApplicationException;
use bld\ddosspelbord\classes\helpers\ddosspelbordUsers;

class ActionPlansExport extends ExportModel {

    public function exportData( $columns, $sessionKey = null ) {

        $planId = intval(get('actionplan'));
        $model = ( $planId > 0 ) ? ActionPlan::find($planId) : false;

        if ( !$model ) {
            throw new ApplicationException('No Valid Plan found with planId = ' . $planId);
        }

        if ( empty($model->plandata) ) {
            throw new ApplicationException('Action Plan: ' . $model->name . 'has no data to export');
        }

        $plandata = $model->plandata;

        if(ddosspelbordUsers::filterOnParty()) {
            $partyId = ddosspelbordUsers::getBackendPartyId();
            $plandata = $this->filterOnPartyId($plandata, $partyId);
        }

        $plandata = $this->addRelationValues($plandata);


        return $plandata;
    }

    private function filterOnPartyId($plandata, $partyId){
        return array_filter($plandata, function ($data) use ($partyId) {
            return isset($data['party_id']) && (int) $data['party_id'] === $partyId;
        });
    }

    /**
     * For each item in $plandata, replace 'party_id' with 'party' => 'PartyName'.
     * Does a single database query instead of one per row.
     *
     * @param  array $plandata
     * @return array
     */
    private function addRelationValues(array $plandata): array
    {
        if (ddosspelbordUsers::filterOnParty()) {
            $partyId = ddosspelbordUsers::getBackendPartyId();
            $party = Parties::findOrFail($partyId);
            $parties = [$party->id => $party->name];
        }
        else {
            $parties = Parties::get()->pluck('name', 'id')
                ->all();
        }

        // 3) Map party name into each array item, removing 'party_id'
        foreach ($plandata as $key => &$data) {
            $partyId = (int) ($data['party_id'] ?? 0);
            if ($partyId && isset($parties[$partyId])) {
                $data['party'] = $parties[$partyId];
            }
            unset($data['party_id']);
        }
        unset($data); // remove reference

        return $plandata;
    }


    public function getActionplanOptions() {
        $recs = ActionPlan::select('id', 'name')->get();
        $ret = [
            0 => 'Create new Action Plan'
        ];
        foreach ( $recs as $rec ) {
            $ret[ $rec->id ] = $rec->name;
        }

        return $ret;
    }


}
