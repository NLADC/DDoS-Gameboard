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
use Winter\User\Models\User;

class SpelbordusersImport extends ImportModel {

    /**
     * @var array The rules to be applied to the data.
     */
    public $rules = [];

    public function importData($results, $sessionKey = null)
    {
        foreach ($results as $rowNumber => $data) {
            try {
                $this->processRow($rowNumber, $data);
            } catch (\Exception $ex) {
                $this->logError($rowNumber, $ex->getMessage());
            }
        }
    }

    /**
     * @param int   $rowNumber The row index in the import data
     * @param array $data      The columns/values for this row
     */
    public function processRow( $rowNumber, $data)
    {
        if (!$this->isValidRow($data)) {
            $this->logSkipped($rowNumber, 'Skip empty or invalid import row');
            return;
        }

        $data = $this->assignPartyAndRole($rowNumber, $data);
        $this->updateInsertSpelbordUser($rowNumber, $data);
    }

    /**
     * @param  array $data
     * @return bool
     */
    private function isValidRow(array $data)
    {
        return (
            !empty($data) &&
            isset($data['party'], $data['name'], $data['email'], $data['role'])
        );
    }

    /**
     * @param  int   $rowNumber
     * @param  array $data
     * @return array
     */
    private function assignPartyAndRole($rowNumber, $data)
    {
        $partyName = $data['party'];
        $data['party_id'] = 0; // default
        if (!empty($partyName)) {
            $party = Parties::where('name', $partyName)->first();
            if ($party) {
                $data['party_id'] = $party->id;
            } else {
                $this->logError($rowNumber, "Cannot find key of party '$partyName'!?!");
            }
        }
        unset($data['party']);

        $roleName = $data['role'];
        $data['role_id'] = 0;
        $role = Roles::where('name', $roleName)->first();
        if ($role) {
            $data['role_id'] = $role->id;
        } else {
            $this->logError($rowNumber, "Cannot find key of role '$roleName'!?!");
        }
        unset($data['role']);

        return $data;
    }

    /**
     * @param int   $rowNumber
     * @param array $data
     */
    private function updateInsertSpelbordUser( $rowNumber, $data)
    {
        $spelbordUser = Spelbordusers::where([
                                                 ['email', $data['email']],
                                             ])->first();

        if ($spelbordUser) {
            $this->updateSpelbordUser($spelbordUser, $rowNumber, $data);

        } else {
            $this->createSpelbordUsder($data);
        }
    }

    /**
     * @param $spelbordUser
     * @param $rowNumber
     * @param $data
     * @return void
     */
    private function updateSpelbordUser($spelbordUser, $rowNumber, $data) {
        $user = User::where([
                                ['email', $data['email']],
                            ])->first();
        if (!$user) {
            $this->logError($rowNumber, 'Cannot get backend user from spelborduser');
            return;
        }

        $spelbordUser->name    = $data['name'];
        $spelbordUser->role_id = $data['role_id'];
        $spelbordUser->password = $data['password'] ?? $user->password;

        $spelbordUser->save();
        $this->logUpdated();
    }

    /**
     * @param $data
     * @return void
     */
    private function createSpelbordUsder($data){
        $spelbordUser = new Spelbordusers();
        $spelbordUser->fill($data);
        $spelbordUser->password = $this->generateRandomPassword(12); // Generate a random password, we cannot leave it NULL
        $spelbordUser->save();
        $this->logCreated();
    }

    private function generateRandomPassword($length = 12) {
        return substr(str_shuffle(str_repeat($x='0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ', ceil($length/strlen($x)) )),1,$length);
    }

}
