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

namespace bld\ddosspelbord\Controllers;

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
