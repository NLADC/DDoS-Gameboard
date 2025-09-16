<?php namespace bld\ddosspelbord\Controllers;

use Backend\Classes\Controller;
use BackendMenu;
use bld\ddosspelbord\classes\base\baseController;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use League\Csv\Exception;
use Winter\storm\Support\Facades\Flash;

class MeasurementApi extends baseController
{
    public $requiredPermissions = ['bld.ddosspelbord.monitor'];

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


    public function receive($type = null)
    {
        switch ($type) {
            case 0:
            default;
                return response()->json(['message' => 'Please specify a type of request']);
            case 'probes':
                return response()->json(['message' => 'Yes i have received probes']);
            case 'get_targets':
                return response()->json(['message' => 'The targets are:::! ']);
            case 'put_measurements':
                return response()->json(['message' => 'Thank you for supplying the data! ']);
        }
    }
}
