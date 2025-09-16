<?php namespace bld\ddosspelbord\Controllers;
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

use Backend\Classes\Controller;
use BackendMenu;
use bld\ddosspelbord\classes\base\baseController;
use bld\ddosspelbord\classes\helpers\ddosspelbordUsers;
use bld\ddosspelbord\models\Action;
use bld\ddosspelbord\Models\ActionPlan;
use Bld\Ddosspelbord\Models\Parties;
use Flash;
use Lang;
use ApplicationException;
use Illuminate\Support\Facades\Redirect;

class ActionPlans extends baseController {
    public $implement = [
        'Backend\Behaviors\ListController',
        'Backend\Behaviors\FormController',
        'Backend\Behaviors\ImportExportController'
    ];
    public $listConfig = 'config_list.yaml';
    public $formConfig = 'config_form.yaml';
    public $importExportConfig = 'actionplans_import_export.yaml';

    public $applyActionsForm;

    public $requiredPermissions = [ 'bld.ddosspelbord.actionplans' ];

    public function __construct() {
        parent::__construct();
        BackendMenu::setContext('bld.ddosspelbord', 'ActionPlans');

        $this->initActionFormPlan();
    }

    private function initActionFormPlan() {
        $this->applyActionsForm = $this->makeConfig("$/bld/ddosspelbord/models/actionplan/fields_apply_plan.yaml");

        $currentId = intval($this->getCurrentId());
        $this->applyActionsForm->model = ($currentId > 0) ? ActionPlan::find($currentId): new ActionPlan();
        $this->applyActionsForm = $this->makeWidget('Backend\Widgets\Form', $this->applyActionsForm);
        $this->applyActionsForm->alias = 'ApplyActionPlanForm';
        $this->applyActionsForm->bindToController();
    }


    public function onApplyActionPlanModal() {
        return $this->makePartial('$/bld/ddosspelbord/controllers/actionplans/_apply_actionplan.php', [
            'FormWidget'   => $this->applyActionsForm,
            'actionPlanId' => $this->getCurrentId(),
        ]);
    }

    /**
     * @description This function has the ability to truncate the actiksn table if asked
     * Then it replaces it with the actionplan->plandata actions that are defined there
     * @return array
     */
    public function onApplyActionPlan(){
        $errorMsg = 'Something wen\'t wrong applying Actions';
        $success  = true;

        $user = ddosspelbordUsers::getUser();
        if (!$user->hasAccess('bld.ddosspelbord.apply_actionplans')) {
            $errorMsg = 'You don\'t have the required permission to Apply an actionplan';
            $success = false;
        }

        $actionPlanId = intval(post('ActionPlanId'));
        $actionplan = ActionPlan::find($actionPlanId);
        $clearCurrent = boolval(post('clearcurrent'));

        // For multi tenant purposes
        $partyId = ddosspelbordUsers::getBackendPartyId();

        if (empty($actionplan->plandata)) {
            $errorMsg = 'No Action Data provided!';
        }

        if (!$this->checkFillActionData($actionplan->plandata[0])) {
            $errorMsg = 'Due to unknow error can\'t save the Action Data';
        }

        if ($clearCurrent) {
            if (!ddosspelbordUsers::filterOnParty()) {
                Action::truncate();
            }
            else {
                if (Parties::where('id', $partyId)->exists()) {
                    Action::where('party_id', $partyId)->delete();
                }
                else {
                    $errorMsg = 'Can\'t find a Party with that Id';
                    $success = false;
                }
            }
        }
        if ($success) {
            $plandata = (ddosspelbordUsers::filterOnParty()) ? $this->filterOnPartyId($actionplan->plandata, $partyId) : $actionplan->plandata;

            foreach ($plandata as $data)
            {
                try {
                    $data = $this->fillEmpties($data);
                    $action = new Action();
                    $action->fill($data);
                    $action->save();
                    $success = true;
                }catch (\Exception $e) {
                    $errorMsg = $e->getMessage();
                    $success = false;
                    break;
                }
            }

        }

        if ($success) {
            Flash::success(Lang::get('bld.ddosspelbord::lang.apply_actions_success'));
            return Redirect::to('/backend//bld/ddosspelbord/actions');
        }
        else {
            Flash::error($errorMsg);
        }
    }

    private function filterOnPartyId($plandata, $partyId){
        return array_filter($plandata, function ($data) use ($partyId) {
            return isset($data['party_id']) && (int) $data['party_id'] === $partyId;
        });
    }

    public function onActionPlanSucces(){
        Flash::success('Successfully imported Actions from Action Plan');
        Redirect::to('/backend//bld/ddosspelbord/actions');
    }

    /**
     * In a datatable certain types that are unmodified can be value '';
     * This will Cause a crash in the $model->fill function of wintercms
     * This Function fills it with at least an empty 0 to prevent errors
     * @param $data
     * @return mixed
     */
    private function fillEmpties($data){
        foreach ($data as $key => $value){
            if($value == '') {
                $data[$key] = '0';
            }
        }
        return $data;
    }

    /**
     * @param $planData
     * @return bool
     */
    private function checkFillActionData($planData){
        $action = new Action();
        $action->fill($planData);
        // Dont save, this is just trying out
        return true;
    }

    private function getCurrentModel() {
        $modelId = intval($this->getCurrentId());
        return ($modelId > 0) ? ActionPlan::find($modelId) : false;
    }

}
