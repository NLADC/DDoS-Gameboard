<?php namespace Bld\Ddosspelbord\Controllers;

use Backend\Classes\Controller;
use BackendMenu;

class Actions extends Controller
{
    public $implement = [
        'Backend\Behaviors\ListController',
        'Backend\Behaviors\FormController',
        'Backend\Behaviors\ImportExportController'
    ];

    public $listConfig = 'config_list.yaml';
    public $formConfig = 'config_form.yaml';
    public $importExportConfig = 'config_import_export.yaml';

    public function __construct()
    {
        parent::__construct();
        // neede to set menu highlighted
        BackendMenu::setContext('bld.ddosspelbord', 'Actions');
    }
}
