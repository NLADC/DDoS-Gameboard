<?php namespace bld\ddosspelbord\Controllers;

use Backend\Classes\Controller;
use BackendMenu;
use Bld\Ddosspelbord\models\Parties;
use bld\ddosspelbord\Models\Target;
use Illuminate\Support\Facades\Redirect;

class Targets extends Controller
{
    public $implement = [
        'Backend\Behaviors\ListController',
        'Backend\Behaviors\FormController'
    ];

    public $listConfig = 'config_list.yaml';
    public $formConfig = 'config_form.yaml';

    public function __construct()
    {
        parent::__construct();
        BackendMenu::setContext('bld.ddosspelbord', 'Monitor', 'Targets');
    }

    public function onCopyRecords() {

        if (($checkedIds = post('checked')) &&
            is_array($checkedIds) &&
            count($checkedIds)
        ) {

            foreach ($checkedIds AS $checkedId) {

                $fromtarget = Target::find($checkedId);
                if ($fromtarget) {

                    $newtarget = new Target();
                    $newtarget->name = $fromtarget->name . " (KOPIED)";
                    $newtarget->target = $fromtarget->target;
                    $newtarget->ipv = $fromtarget->ipv;
                    $newtarget->type = $fromtarget->type;
                    $newtarget->party_id = $fromtarget->party_id;
                    $newtarget->measurement_api_id = $fromtarget->measurement_api_id;
                    $newtarget->enabled  = $fromtarget->enabled;
                    $newtarget->threshold_orange = $fromtarget->threshold_orange;
                    $newtarget->threshold_red = $fromtarget->threshold_red;
                    $newtarget->groups = $fromtarget->groups;
                    $newtarget->save();

                }


            }


            return Redirect::refresh();

        }


    }

    public function onDisableEnable() {

        if (($checkedIds = post('checked')) &&
            is_array($checkedIds) &&
            count($checkedIds)
        ) {

            foreach ($checkedIds as $checkedId) {
                $target = Target::find($checkedId);
                if ($target) {
                    $target->enabled = !$target->enabled;
                    $target->save();
                }
            }

            return Redirect::refresh();
        }
    }

}
