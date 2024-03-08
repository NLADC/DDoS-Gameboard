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
