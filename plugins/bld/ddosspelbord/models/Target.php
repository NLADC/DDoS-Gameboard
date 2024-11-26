<?php namespace bld\ddosspelbord\models;
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

use bld\ddosspelbord\models\Measurement_api;
use Model;
use Bld\Ddosspelbord\models\Parties;
use bld\ddosspelbord\models\Target_groups;

/**
 * Model
 */
class Target extends Model
{
    use \Winter\Storm\Database\Traits\Validation;

    use \Winter\Storm\Database\Traits\SoftDelete;

    protected $dates = ['deleted_at'];

    /**
     * @var string The database table used by the model.
     */
    public $table = 'bld_ddosspelbord_targets';

    /**
     * @var array Validation rules
     */
    public $rules = [
    ];

    public $hasOne = [
        'measurement_api' => [
            'bld\ddosspelbord\models\Measurement_api',
            'key' => 'id',
            'otherKey' => 'measurement_api_id'
        ],
        'parties' => [
            'bld\ddosspelbord\models\Parties',
            'key' => 'id',
            'otherKey' => 'party_id'
        ],
    ];

    public $belongsTo = [
        'measurementtype' => [
            'bld\ddosspelbord\models\MeasurementType',
            'key' => 'measurement_type_id',
            'otherKey' => 'id',
        ],
    ];
    /*
    public $belongsToMany = [
        'groups' => [
            ['bld\ddosspelbord\models\Target_groups','table' => 'bld_ddosspelbord_target_groups']
        ],
    ];
    */

    public function filterFields($fields, $context = null) {
    }

    public function getMeasurementApiIdOptions($value, $formData) {
        $opt = [];
        if (empty($this->measurement_type_id)) {
            $recs = Measurement_api::all();
        }
        else {
            $recs = Measurement_api::where('measurement_type_id', $this->measurement_type_id)->get();
        }

        foreach ($recs AS $rec) {
            $opt[$rec->id] = $rec->name;
        }


        return $opt;
    }

    public function getPartyIdOptions($value, $formData) {

        $recs = Parties::get();
        $opt = [];
        foreach ($recs AS $rec) {
            $opt[$rec->id] = $rec->name;
        }
        return $opt;
    }

    public function getGroupsOptions($value, $formData) {

        $recs = Target_groups::get();
        $opt = [];
        $opt[''] = '(not in group)';
        foreach ($recs AS $rec) {
            $opt[$rec->name] = $rec->name;
        }
        return $opt;
    }

}
