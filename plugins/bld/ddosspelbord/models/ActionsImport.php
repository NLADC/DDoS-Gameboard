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

class ActionsImport extends ImportModel {

    /**
     * @var array The rules to be applied to the data.
     */
    public $rules = [];

    public function importData($results, $sessionKey = null)
    {
        // Keep track of which parties need their action times reset
        $resetTimes = [];
        $actionTimeFields = (new Action())->getTimeFields();

        if ($this->cleartable) {
            hLog::logLine("D-Clear table before import");
            Action::where('id', '>', 0)->delete();
        }

        foreach ($results as $rowNumber => $data) {
            try {
                $this->processImportedRow($rowNumber, $data, $actionTimeFields, $resetTimes);
            } catch (\Exception $ex) {
                $this->logError($rowNumber, $ex->getMessage());
            }
        }

        if (count($resetTimes) > 0) {
            (new Action())->forceResetStartTimes($resetTimes);
        }
    }


    /**
     * Processes a single row from the import.
     *
     * @param int   $rowNumber
     * @param array $data
     * @param array $actionTimeFields
     * @param array $resetTimes  Reference array to track parties needing a reset
     */
    private function processImportedRow( $rowNumber, $data, $actionTimeFields, &$resetTimes)
    {
        if (empty($data) || !isset($data['party'], $data['start'])) {
            $this->logSkipped($rowNumber, "Skip empty or invalid import row");
            return;
        }

        // Assign party_id (0 if not found)
        $data['party_id'] = $this->getPartyId($rowNumber, $data['party']);
        unset($data['party']);
        $data['start'] = date('Y-m-d H:i:s', strtotime($data['start']));

        $action = Action::where([
                                     ['party_id', $data['party_id']],
                                     ['start',    $data['start']],
                                 ])->first();

        if ($action) {
            $changedTimeFields = $this->updateExistingAction($rowNumber, $action, $data, $actionTimeFields);
            if ($changedTimeFields) {
                $resetTimes[$data['party_id']] = true;
            }
        } else {
            $this->createNewAction($data);
            $resetTimes[$data['party_id']] = true;
        }
    }

    /**
     * Finds the Party by name, logs an error if not found, and returns party ID (or 0).
     *
     * @param  int    $rowNumber
     * @param  string $partyName
     * @return int
     */
    public function getPartyId($rowNumber, $partyName)
    {
        $party = Parties::where('name', $partyName)->first();
        if (!$party) {
            $this->logError($rowNumber, "Cannot find key of party '$partyName'!?!");
            return 0;
        }
        return $party->id;
    }

    /**
     * Updates an existing Actions record. Returns true if any of the time fields changed.
     *
     * @param  int    $rowNumber
     * @param  Action $action
     * @param  array   $data
     * @param  array   $actionTimeFields
     * @return bool    Whether any time field changed
     */
    private function updateExistingAction(
        int    $rowNumber,
        Action $action,
        array  $data,
        array  $actionTimeFields
    ) {
        $original = $action->toArray();
        $original['party_id'] = $data['party_id'];

        $diff      = false;
        $diffTime  = false;

        foreach ($data as $field => $value) {
            if (array_key_exists($field, $original) && $original[$field] != $value) {
                $diff = true;
                if (in_array($field, $actionTimeFields)) {
                    $diffTime = true;
                }
            }
        }

        if ($diff) {
            $this->logUpdated();
        } else {
            $this->logSkipped($rowNumber, "No change in action");
        }


        $action->fill($data);
        $action->setSkip();
        $action->save();

        return $diffTime;
    }

    /**
     * Creates a new Actions record from data.
     *
     * @param array $data
     */
    private function createNewAction($data)
    {
        $action = new Action();
        $action->fill($data);
        $action->setSkip();
        $action->save();

        $this->logCreated();
    }


}
