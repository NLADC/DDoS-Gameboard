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
use BackendMenu;
use bld\ddosspelbord\classes\base\baseController;

class Parties extends baseController
{
    public $requiredPermissions = ['bld.ddosspelbord.parties'];

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
