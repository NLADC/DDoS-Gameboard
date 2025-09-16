<?php namespace bld\ddosspelbord\Models;
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

use bld\ddosspelbord\classes\base\baseModel;
use bld\ddosspelbord\classes\helpers\ddosspelbordUsers;
use Model;
use BackendAuth;

/**
 * Model
 */
class ActionPlan extends baseModel
{
    use \Winter\Storm\Database\Traits\Validation;

    use \Winter\Storm\Database\Traits\SoftDelete;

    protected $dates = ['deleted_at'];

    protected $jsonable = ['plandata'];

    /**
     * @var string The database table used by the model.
     */
    public $table = 'bld_ddosspelbord_action_plans';

    /**
     * @var array Validation rules
     */
    public $rules = [
        'name' => 'required',
    ];

    public $fillable = [
        'description',
        'length',
        'user_last_edited_id',
        'plandata',
    ];


    public $belongsTo = [
        'user_last_edited' => [
            'bld\ddosspelbord\models\Spelbordusers',
            'key' => 'user_last_edited_id',
            'otherKey' => 'id',
        ]
    ];

    public function filterFields($fields, $context = null) {

        // Show only Own party ID records
        if (ddosspelbordUsers::filterOnParty()) {
            $partyId = ddosspelbordUsers::getBackendPartyId();
            if ( isset($fields->plandata) && !empty($this->plandata)) {
                $fields->plandata->value = array_filter($this->plandata, function ($data) use ($partyId) {
                    return isset($data['party_id']) && (int) $data['party_id'] === $partyId;
                });
            }
        }

        if ( isset($fields->plandata) && isset($fields->plandata->config['columns']['party_id']['option']) ) {
            $fields->plandata->config['columns']['party_id']['option'] = $this->getPartiesAsArray();
        }
    }

    /**
     * Dropdown/autocomplete option callback handler
     *
     * Looks at the model for getXXXDataTableOptions or getDataTableOptions methods
     * to obtain values for autocomplete and dropdown column types.
     *
     * @param string $columnName The name of the column to pass through to the callback.
     * @param array $rowData The data provided for the current row in the datatable.
     * @return array The options to make available to the dropdown or autocomplete, in format ["value" => "label"]
     */
    public function getDataTableOptions($columnName, $rowData)
    {
        $result = [];

        // Multi tenant switch to show only own party
        if (ddosspelbordUsers::filterOnParty()) {
            $partyId = ddosspelbordUsers::getBackendPartyId();
            $party = Parties::find($partyId);
            $result = (!empty($party)) ? [$party->id => $party->name] : [];
        }
        elseif ($columnName == 'plandata' && $rowData == 'party_id') {
            $result = $this->getPartiesAsArray();
        }

        return $result;
    }

    private function getPartiesAsArray(){
        $recs = Parties::orderBy('name')->select('id', 'name')->get();
        $ret = array();
        foreach ($recs AS $rec) {
            $ret[$rec->id] = $rec->name;
        }
        return $ret;
    }

    public function beforeUpdate() {
        $user = BackendAuth::getUser();
        $this->user_last_edited_id = (!empty($user)) ?  $user->id : 0;

        /** We stripped the other parties earlier from the main array,
        * when we are dealing with a user that is in a tenant,
        * dont allow them to clear the rest of the plan! */
        if (ddosspelbordUsers::filterOnParty()) {
            $orgPlandata = json_decode($this->original['plandata'], true); // decode as array
            $newPlanData = post('ActionPlan')['plandata'];

            if (!empty($orgPlandata) && !empty($newPlanData)) {
                $partyId = ddosspelbordUsers::getBackendPartyId();
                // Strip old $user party related from original array
                $orgPlandata = array_filter($orgPlandata, function ($data) use ($partyId) {
                    return (int) $data['party_id'] !== $partyId;
                });
                // Combine the stripped one with the new $user party specific values
                $mergedPlanData = array_merge($orgPlandata, $newPlanData);
                if (!empty($mergedPlanData)) {
                    $this->plandata = $mergedPlanData;
                    return true;
                }
                return false; // Anything goes wrong, dont save!
            }
            else {
                return false; // Prevent any saving when trhis is not done properly
            }


        }
    }

    /**
     * @return string
     */
    public function makePlanDataSummary(){
        $return = '';
        if (!empty($this->plandata)) {
            $return .= 'Found ' . count($this->plandata) . ' rows to save';
        }
        return $return;
    }

}
