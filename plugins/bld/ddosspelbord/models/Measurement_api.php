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

use bld\ddosspelbord\classes\base\baseModel;
use Model;

/**
 * Model
 */
class Measurement_api extends baseModel
{
    use \Winter\Storm\Database\Traits\Validation;

    use \Winter\Storm\Database\Traits\SoftDelete;

    protected $dates = ['deleted_at'];

    protected $jsonable = ['configjson'];

    /**
     * @var string The database table used by the model.
     */
    public $table = 'bld_ddosspelbord_measurement_api';

    /**
     * @var array Validation rules
     */
    public $rules = [

    ];

    public $belongsTo = [
        'measurementtype' => [
            'bld\ddosspelbord\models\MeasurementType',
            'key' => 'measurement_type_id',
            'otherKey' => 'id',
        ],
    ];
}
