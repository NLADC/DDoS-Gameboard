<?php namespace Bld\Ddosspelbord\Models;

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
