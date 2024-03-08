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

    public function importData($results, $sessionKey = null) {

        // loop through the import lines
        foreach ($results as $row => $data) {

            try {

                // note: unique for one row is party_id and start time

                if (!empty($data) && isset($data['party']) && isset($data['name'])  && isset($data['email'])  && isset($data['role'])) {

                    // check & get party
                    $partyName = $data['party'];
                    if (empty($partyName)) {
                        $data['party_id'] = 0;
                    } else {
                        $party = Parties::where('name',$partyName)->first();
                        if ($party) {
                            $data['party_id'] = $party->id;
                        } else {
                            $this->logError($row,"Cannot find key of party '$partyName'!?!");
                            $data['party_id'] = 0;
                        }
                    }
                    unset($data['party']);

                    // check & get role
                    $rolename = $data['role'];
                    $role = Roles::where('name',$rolename)->first();
                    if ($role) {
                        $data['role_id'] = $role->id;
                    } else {
                        $this->logError($row,"Cannot find key of role '$rolename'!?!");
                        $data['role_id'] = 0;
                    }
                    unset($data['role']);

                    $spelborduser = Spelbordusers::where([
                        ['party_id',$data['party_id']],
                        ['email',$data['email']],
                    ])->first();

                    // detect if create or update
                    if ($spelborduser) {

                        $user = User::find($spelborduser->user_id);
                        if ($user) {

                            $spelborduser->name = $data['name'];
                            $spelborduser->role_id = $data['role_id'];
                            if (isset($data['password'])) {
                                $spelborduser->password = $data['password'];
                            } else {
                                $spelborduser->password = $user->password;
                            }

                            $spelborduser->save();

                        } else {

                            $this->logError($row,"Cannot get backend user from spelborduser");

                        }

                        $this->logUpdated();

                    } else {

                        $spelborduser = new Spelbordusers();
                        $spelborduser->fill($data);
                        $spelborduser->save();

                        $this->logCreated();
                    }

                } else {

                    $this->logSkipped($row,"Skip empty or not valid import file row ");

                }
            }
            catch (\Exception $ex) {
                $this->logError($row, $ex->getMessage());
            }

        }

    }

}
