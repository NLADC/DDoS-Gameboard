<?php namespace bld\ddosspelbord\Controllers;

use Backend\Classes\Controller;
use BackendMenu;
use bld\ddosspelbord\classes\base\baseController;

class BackendUserPivots extends baseController
{
    public $requiredPermissions = [ 'bld.ddosspelbord.backendusers' ];

    public $implement = [        'Backend\Behaviors\ListController',        'Backend\Behaviors\FormController'    ];

    public $listConfig = 'config_list.yaml';
    public $formConfig = 'config_form.yaml';

    public function __construct()
    {
        parent::__construct();
        BackendMenu::setContext('bld.ddosspelbord', 'BackendUsers');
    }
}
