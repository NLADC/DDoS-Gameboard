<?php namespace bld\ddosspelbord\Controllers;

use Backend\Classes\Controller;
use BackendMenu;
use bld\ddosspelbord\classes\base\baseController;
use bld\ddosspelbord\helpers\hLog;
use bld\ddosspelbord\models\Attack;
use bld\ddosspelbord\models\Measurement;
use bld\ddosspelbord\models\Measurement_api;
use bld\ddosspelbord\models\Measurement_api_data;
use bld\ddosspelbord\models\Action;
use bld\ddosspelbord\models\MeasurementNode;
use bld\ddosspelbord\models\MeasurementType;
use bld\ddosspelbord\models\NodeList;
use bld\ddosspelbord\models\Target;
use Bld\Ddosspelbord\Models\Transactions;
use bld\ddosspelbord\models\Target_groups;
use bld\ddosspelbord\models\Parties;
use Flash;
use Db;
use Bld\Ddosspelbord\Models\Logs;
use System\Models\File;
use Bld\Ddosspelbord\Models\Spelbordusers;
use Winter\User\Models\User;

class Settings extends baseController {

    /**
     * Never Delete This function or all ajax calls underneath will fail
     * @return string
     */
    public function index() {
        return '';
    }

    public function onDeleteAllGameboardData() {

        $mode = input('mode');

        try {

            if ( empty($mode) && !is_string($mode) ) {

                Flash::error('Please select specify a mode ofr deleting gameboard data');

            } else {

                hLog::logLine("D-onDeleteAllGameboardData; mode=$mode");

                switch ($mode) {

                    case 'logData':
                        // clear also attachments in system files table
                        File::where('attachment_type', 'Bld\Ddosspelbord\Models\Logs')->delete();
                        // truncate log and attack data
                        Logs::truncate();

                        Flash::success('Succesfully deleted all logging data');
                        break;

                    case 'userData':
                        // delete spelbord users with log
                        foreach (Spelbordusers::all() as $spelborduser) {
                            foreach (Logs::where('user_id',$spelborduser->id)->get() as $log) {
                                File::where('attachment_type', 'Bld\Ddosspelbord\Models\Logs')->where('attachment_id',$log->id)->delete();
                            }
                            Logs::where('user_id',$spelborduser->user_id)->forceDelete();
                            User::where('id', $spelborduser->user_id)->forceDelete();
                        }
                        Spelbordusers::truncate();
                        Attack::truncate();
                        Transactions::truncate();

                        Flash::success('Succesfully deleted all Gameboard user, there logging, attacks and transaction data');
                        break;

                    case 'measurementsData':

                        Measurement::truncate();
                        MeasurementNode::truncate();
                        MeasurementType::truncate();
                        Measurement_api::truncate();
                        Measurement_api_data::truncate();
                        NodeList::truncate();
                        Target::truncate();
                        Target_groups::truncate();
                        Db::table('bld_ddosspelbord_measurement_node_pivot')->truncate();

                        Flash::success('Succesfully deleted all measurement data');
                        break;

                    case 'setupData':

                        Action::truncate();
                        Parties::truncate();

                        Flash::success('Succesfully deleted all setupData');
                        break;

                    default:
                        Flash::error('Unknown mode for deleting gameboard data');
                        break;

                }

            }


        } catch (\Exception $e) {

            hLog::logLine("E-onDeleteAllGameboardData; error: ".$e->getMessage());

            Flash::error('Due to error not all was deleted in Gameboard: ' . $e->getMessage());
        }

        return true;
    }

}
