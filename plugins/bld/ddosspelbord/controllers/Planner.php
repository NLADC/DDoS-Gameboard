<?php namespace Bld\Ddosspelbord\Controllers;

use Backend\Classes\Controller;
use Session;
use Flash;
use Bld\Ddosspelbord\Models\Parties;
use BackendMenu;

class Planner extends Controller {

    public $implement = [    ];

    public function __construct() {
        parent::__construct();
        BackendMenu::setContext('bld.ddosspelbord', 'Planner');
    }

    /**
     * Own index
     */
    public function index() {

        //$appUrl = url('/');
        //$this->addCss($appUrl.'/plugins/bld/ddosspelbord/assets/css/ddosspelbord.css');

        $this->pageTitle = 'Planner';

        $this->bodyClass = 'compact-container ';

        $parties = Parties::get();

        if ($parties) {

            $colsize = round(12 / count($parties),0);
            $this->vars['partiescol'] = $colsize;
            $this->vars['parties'] = $parties;

        } else {

            $this->vars['partiescol'] = $colsize = 12;
            $this->vars['parties'] = [
                [
                    'name' => '(empty)',
               ]
            ];

        }
        $this->vars['partiescolextra'] = ($colsize % 2 != 0);


    }




}
