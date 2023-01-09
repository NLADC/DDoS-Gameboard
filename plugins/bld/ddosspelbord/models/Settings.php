<?php namespace Bld\Ddosspelbord\Models;

use bld\ddosspelbord\helpers\hLog;
use Model;
use Db;
use BackendAuth;


/**
 * Model
 */
class Settings extends Model {

    use \Winter\Storm\Database\Traits\Validation;

    /**
     * @var array Validation rules
     */
    public $rules = [
        'startdate' => 'required',
        'firsttime' => 'required',
        'starttime' => 'required',
        'endtime' => 'required',
        'enddate' => 'required',
        'granularity'    => 'numeric|min:0',
        'maxexecutiontime'    => 'numeric|min:0',
        'logmaxfilesize'    => 'numeric|min:0',
        'logmaxfiles'    => 'numeric|min:0',
    ];

    public $implement = ['System.Behaviors.SettingsModel'];

    /**
     * @var string The database table used by the model.
     */
    public $settingsCode  = 'bld_ddosspelbord_settings';

    // Reference to field configuration
    public $settingsFields = 'fields.yaml';

    public function afterSave() {
        parent::afterSave();

        $user = BackendAuth::getUser();
        hLog::setUser($user);

        // bepaal start
        $startdate = Settings::get('startdate');
        $starttime = Settings::get('starttime');
        $starttime = date('H:i:s',strtotime(Settings::get('starttime','03:00:00')));
        $start = str_replace('00:00:00',$starttime,$startdate);
        // controleer of start gewijzigd -> zo ja, voer door in acties
        (new Actions())->resetStartTime($start);
    }

}


