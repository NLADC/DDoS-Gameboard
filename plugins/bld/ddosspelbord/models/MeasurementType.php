<?php namespace bld\ddosspelbord\models;

use Model;

/**
 * Model
 */
class MeasurementType extends Model
{
    use \Winter\Storm\Database\Traits\Validation;

    use \Winter\Storm\Database\Traits\SoftDelete;

    protected $dates = ['deleted_at'];


    /**
     * @var string The database table used by the model.
     */
    public $table = 'bld_ddosspelbord_measurement_types';

    /**
     * @var array Validation rules
     */
    public $rules = [
    ];

    public $belongsTo = [
        'nodelist' => [
            'bld\ddosspelbord\models\NodeList',
            'key' => 'nodelist_id',
            'otherKey' => 'id'
        ],
    ];
}
