<?php namespace Bld\Ddosspelbord\Models;

use Model;

/**
 * Model
 */
class Roles extends Model
{
    use \Winter\Storm\Database\Traits\Validation;


    /**
     * @var string The database table used by the model.
     */
    public $table = 'bld_ddosspelbord_roles';

    /**
     * @var array Validation rules
     */
    public $rules = [
    ];

    protected $hidden = [
        'created_at',
    ];


}


