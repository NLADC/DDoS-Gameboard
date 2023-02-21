<?php namespace bld\ddosspelbord\Models;

use Model;
use bld\ddosspelbord\models\Measurement_api;
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

        if (empty($this->type)) $this->type = 'server';

        $recs = Measurement_api::where('type',$this->type)->get();
        $opt = [];
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
