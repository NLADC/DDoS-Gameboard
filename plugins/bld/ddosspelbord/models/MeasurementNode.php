<?php namespace bld\ddosspelbord\models;

use Model;

/**
 * Model
 */
class MeasurementNode extends Model
{
    use \Winter\Storm\Database\Traits\Validation;

    use \Winter\Storm\Database\Traits\SoftDelete;

    protected $dates = ['deleted_at'];


    /**
     * @var string The database table used by the model.
     */
    public $table = 'bld_ddosspelbord_measurement_nodes';

    /**
     * @var array Validation rules
     */
    public $rules = [
    ];

}
