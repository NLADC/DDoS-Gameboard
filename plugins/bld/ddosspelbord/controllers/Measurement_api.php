<?php namespace bld\ddosspelbord\Controllers;

use Backend\Classes\Controller;
use BackendMenu;

class Measurement_api extends Controller
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
        BackendMenu::setContext('bld.ddosspelbord', 'Monitor', 'Measurement_api');
    }
}
