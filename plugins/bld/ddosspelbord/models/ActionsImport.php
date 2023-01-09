<?php namespace Bld\Ddosspelbord\Models;

use \Backend\Models\ImportModel;
use bld\ddosspelbord\helpers\hLog;

class ActionsImport extends ImportModel {

    /**
     * @var array The rules to be applied to the data.
     */
    public $rules = [];

    public function importData($results, $sessionKey = null) {

        // holds party from which the action time fields are changed
        $resetTimes = [];
        // the specific action time fields
        $actiontimefields = (new Actions())->getTimeFields();

        // check if clear
        if ($this->cleartable) {
            hLog::logLine("D-Clear table before import");
            $del = Actions::where('id','>',0)->delete();
        }

        // loop through the import lines
        foreach ($results as $row => $data) {

            try {

                // note: unique for one row is party_id and start time

                if (!empty($data) && isset($data['party']) && isset($data['start'])) {

                    // check & get party
                    $partyName = $data['party'];
                    $party = Parties::where('name',$partyName)->first();
                    if ($party) {
                        $data['party_id'] = $party->id;
                    } else {
                        $this->logError($row,"Cannot find key of party '$partyName'!?!");
                        $data['party_id'] = 0;
                    }
                    unset($data['party']);

                    $data['start'] = date('Y-m-d H:i:s',strtotime($data['start']));

                    $action = Actions::where([
                        ['party_id',$data['party_id']],
                        ['start',$data['start']],
                    ])->first();

                    // detect if create or update
                    if ($action) {
                        // setup arrays for comparing -> strip/add special fields
                        $actionarr = $action->toArray();
                        $actionarr['party_id'] = $data['party_id'];
                        // check if diff in values
                        $diff = $difftime = false;
                        foreach ($data AS $field => $value) {
                            // check if changed
                            if ($actionarr[$field] != $value) {
                                $diff = true;
                            }
                            // check if time fields changed -> then forceResetStartTimes (below)
                            if (in_array($field,$actiontimefields) && $actionarr[$field] != $value) {
                                $difftime = true;
                            }
                        }
                        if ($diff) {
                            $this->logUpdated();
                            if ($difftime) $resetTimes[$data['party_id']] = true;
                        } else {
                            $this->logSkipped($row,"No change in action");
                        }

                    } else {
                        $action = new Actions();
                        $resetTimes[$data['party_id']] = true;
                        $this->logCreated();
                    }

                    $action->fill($data);
                    // skip change detection
                    $action->setSkip();
                    $action->save();

                } else {

                    $this->logSkipped($row,"Skip empty or not valid import file row ");

                }
            }
            catch (\Exception $ex) {
                $this->logError($row, $ex->getMessage());
            }

        }

        if (count($resetTimes) > 0) {
            // reset starttimes of actions -> only from party_id in resetTimes
            (neW Actions())->forceResetStartTimes($resetTimes);
        }

    }

}
