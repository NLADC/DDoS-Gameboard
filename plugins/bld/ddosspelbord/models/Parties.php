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

namespace Bld\Ddosspelbord\Models;

use bld\ddosspelbord\classes\base\baseModel;
use Model;

/**
 * Model
 */
class Parties extends baseModel {

    use \Winter\Storm\Database\Traits\Validation;

    /**
     * @var string The database table used by the model.
     */
    public $table = 'bld_ddosspelbord_parties';

    /**
     * @var array Validation rules
     */
    public $rules = [
        'name' => 'required',
    ];

    protected $hidden = [
        'created_at', 'updated_at'
    ];

    public $hasMany = [
        'actions' => [
            'bld\ddosspelbord\models\Action',
            'key' => 'party_id',
            'order' => 'start ASC'
        ],
        'users' => [
            'bld\ddosspelbord\models\Spelbordusers',
            'key' => 'party_id',
            'order' => 'name ASC'
        ],
    ];

    public function getPartyOptions() {
        $recs = Parties::orderBy('name')->select('id','name')->get();
        $options = array();
        $options[0] = NO_PARTY_NAME;
        foreach ($recs AS $rec) {
            $options[$rec->id] = $rec->name;
        }
        return $options;
    }


}


