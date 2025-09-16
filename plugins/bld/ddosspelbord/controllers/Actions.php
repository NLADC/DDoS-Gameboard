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

namespace Bld\Ddosspelbord\Controllers;

use Backend\Classes\Controller;
use Backend\Classes\FormField;
use Backend\FormWidgets\DataTable;
use BackendMenu;
use bld\ddosspelbord\classes\base\baseController;
use bld\ddosspelbord\classes\helpers\ddosspelbordUsers;
use bld\ddosspelbord\Models\ActionPlan;
use Redirect;
use Flash;
use Bld\Ddosspelbord\Models\Action;

class Actions extends baseController {
    public $requiredPermissions = [ 'bld.ddosspelbord.actions' ];

    public $implement = [
        'Backend\Behaviors\ListController',
        'Backend\Behaviors\FormController',
        'Backend\Behaviors\ImportExportController'
    ];

    public $listConfig = 'config_list.yaml';
    public $formConfig = 'config_form.yaml';
    public $importExportConfig = 'project_import_export.yaml';

    public $editAsPlanForm;

    public function __construct() {
        parent::__construct();
        // neede to set menu highlighted
        BackendMenu::setContext('bld.ddosspelbord', 'Actions');
        $this->initEditAsPlanForm();
    }

    /**
     * @return void
     */
    private function initEditAsPlanForm() {
        $this->editAsPlanForm = $this->makeConfig("$/bld/ddosspelbord/models/action/fields_edit_as_plan.yaml");
        $this->editAsPlanForm->model = new ActionPlan();
        $this->editAsPlanForm = $this->makeWidget('Backend\Widgets\Form', $this->editAsPlanForm);
        $this->editAsPlanForm->alias = 'editAsPlanForm';
        $this->editAsPlanForm->bindToController();
    }

    /**
     * @return false|mixed
     */
    public function onEditAsPlanForm() {


        return $this->makePartial('$/bld/ddosspelbord/controllers/actions/_edit_as_actionplan.php', [
            'FormWidget'  => $this->editAsPlanForm
        ]);
    }

    /**
     * @return mixed
     */
    public function onCreatePlan() {
        $saveData = $this->editAsPlanForm->getSaveData();

        $actionsData = Action::with('parties')
            ->select('party_id', 'name', 'description', 'start', 'tag', 'length', 'delay', 'extension', 'has_issues', 'is_cancelled', 'highlight')
            ->get()
            ->map(function ($action) {
                // Convert the action (and its relationships) to an array
                $array = $action->toArray();
                // If we have a related parties record, set 'party' to its name
                $array['party_id'] = $action->parties->id ?? null;

                // Remove the old keys you don’t want
                unset($array['parties']);
                return $array;
            })
            ->toArray();

        if (empty($actionsData)) return Flash::warning('There are no actions to create a plan from');

        $actionPlan = new ActionPlan();
        $actionPlan->name = $saveData['name'] ?? '';
        $actionPlan->description = $saveData['description'] ?? '';
        $actionPlan->plandata = $actionsData ?? [];
        $actionPlan->save();

        return Redirect::to('/backend/bld/ddosspelbord/actionplans/update/' . $actionPlan->id);
    }


}
