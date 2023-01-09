<?php namespace Bld\Ddosspelbord\Models;

use Model;

/**
 * Model
 */
class Parties extends Model {

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
            'bld\ddosspelbord\models\Actions',
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


