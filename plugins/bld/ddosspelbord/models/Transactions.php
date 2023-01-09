<?php namespace Bld\Ddosspelbord\Models;

use Model;

/**
 * Model
 */
class Transactions extends Model
{
    use \Winter\Storm\Database\Traits\Validation;


    /**
     * @var string The database table used by the model.
     */
    public $table = 'bld_ddosspelbord_transactions';

    /**
     * @var array Validation rules
     */
    public $rules = [
    ];

    protected $hidden = [
        'created_at',
    ];


    public function getLastTransactionHash() {
        // note: sort desc on ID, timestamp can be the same
        $data = Transactions::select('hash')->latest()->orderBy('id','DESC')->first();
        return $data ? $data->hash : '';
    }


}


