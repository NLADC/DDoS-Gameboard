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

use \Backend\Models\ImportModel;
use bld\ddosspelbord\helpers\hLog;
use Bld\Ddosspelbord\Models\ActionsImport;
use Illuminate\Support\Facades\Redirect;
use Winter\Storm\Exception\ApplicationException;

class ActionPlansImport extends ActionsImport {



    /**
     * @var array The rules to be applied to the data.
     */
    public $rules = [];

    public function importData( $results, $sessionKey = null ) {
        $rows = [];
        foreach ( $results as $rowNumber => $data ) {
            try {
                $rows[] = $this->processImportedRow($rowNumber, $data);
            }
            catch ( \Exception $ex ) {
                $this->logError($rowNumber, $ex->getMessage());
            }
        }

        $this->insertImportedData($rows);
    }


    /**
     * Processes a single row from the import.
     *
     * @param int $rowNumber
     * @param array $data
     * @param array $actionTimeFields
     */
    private function processImportedRow( $rowNumber, $data ) {
        if ( empty($data) || !isset($data['party'], $data['start']) ) {
            $this->logSkipped($rowNumber, "Skip empty or invalid import row");
            return;
        }

        // Assign party_id (0 if not found)
        $data['party_id'] = parent::getPartyId($rowNumber, $data['party']);
        unset($data['party']);
        $data['start'] = date('Y-m-d H:i:s', strtotime($data['start'])); // Make sure format correct

        $this->logCreated();

        return $data;
    }


    /**
     * @description Find ActionPlan or create new one and insert the Imported Data
     * @param $rows
     * @return void
     * @throws ApplicationException
     */
    public function insertImportedData($rows) {
        $actionPlanId = post('ImportOptions')['actionplan'];
        $newActionPlan = post('ImportOptions')['newActionPlan'];
        $clearExisting = intval(post('ImportOptions')['clearcurrent']);

        if (empty($actionPlanId) && empty($newActionPlan)) {
            throw new ApplicationException('Cannot find ActionPlan to Import data to');
        }

        if ($actionPlanId > 0 && $plan = ActionPlan::find($actionPlanId)) {
            // Join existing data with imported data when asked
            $rows = ($clearExisting > 0) ? $rows : array_merge($rows, $plan->plandata);
            $plan->plandata = $rows;
            $plan->save();
        }
        elseif (!empty($newActionPlan)) {
            $plan = new ActionPlan();
            $plan->name = $newActionPlan;
            $plan->plandata  = $rows;
            $plan->save();
        }
    }

    /**
     * @description Return array of all Action plans or
     * limit its to only the one in the url param ?actionplan=2
     * @return array|string[]
     */
    public function getActionplanOptions(){
        $planId = intval(get('actionplan'));
        $model = ($planId > 0) ? ActionPlan::find($planId) : false;

        if ($model){
            $ret = [$model->id => $model->name];
        }
        else {
            $recs = ActionPlan::select('id', 'name')->get();
            $ret = [
                0 => 'Create new Action Plan'
            ];
            foreach ($recs AS $rec) {
                $ret[$rec->id] = $rec->name;
            }

        }


        return $ret;
    }


}
