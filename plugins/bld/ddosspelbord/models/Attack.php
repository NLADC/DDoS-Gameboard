<?php namespace bld\ddosspelbord\Models;

use Model;
use Session;

/**
 * Model
 */
class Attack extends Model
{
    use \Winter\Storm\Database\Traits\Validation;


    /**
     * @var string The database table used by the model.
     */
    public $table = 'bld_ddosspelbord_attacks';

    /**
     * @var array Validation rules
     */
    public $rules = [
        'name' => 'required',
        'user'    => 'required',
    ];

    public $belongsTo = [
        'user' => [
            'bld\ddosspelbord\models\Spelbordusers',
            'key' => 'user_id',
            'otherKey' => 'id',
        ],
        'party' => [
            'bld\ddosspelbord\models\Parties',
            'key' => 'party_id',
            'otherKey' => 'id',
        ]
    ];
}
