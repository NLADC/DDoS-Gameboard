<?php namespace Bld\Ddosspelbord\Controllers;

use Backend\Classes\Controller;
use BackendMenu;

class Parties extends Controller
{
    public $implement = [
        \Backend\Behaviors\ListController::class,
        \Backend\Behaviors\RelationController::class,
        \Backend\Behaviors\FormController::class
    ];

    public $listConfig = 'config_list.yaml';
    public $formConfig = 'config_form.yaml';
    public $relationConfig = 'config_relations.yaml';

    public function __construct() {
        parent::__construct();
        // needed to set menu highlighted
        BackendMenu::setContext('bld.ddosspelbord', 'Parties');
    }

}
