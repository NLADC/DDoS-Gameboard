<?php namespace bld\ddosspelbord\Models;

use Model;

/**
 * Model
 */
class Measurement_api_data extends Model
{
    use \Winter\Storm\Database\Traits\Validation;

    use \Winter\Storm\Database\Traits\SoftDelete;

    protected $dates = ['deleted_at'];

    protected $jsonable = ['datajson'];

    /**
     * @var string The database table used by the model.
     */
    public $table = 'bld_ddosspelbord_measurement_api_data';

    /**
     * @var array Validation rules
     */
    public $rules = [
    ];
}
