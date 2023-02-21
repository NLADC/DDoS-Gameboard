<?php namespace bld\ddosspelbord\Models;

use Model;

/**
 * Model
 */
class Measurement extends Model {


    public $timestamps = false;

    /**
     * @var string The database table used by the model.
     */
    public $table = 'bld_ddosspelbord_measurements';

    protected $fillable = ['timestamp', 'target_id','ipv','responsetime','measurement_api_data_id','number_of_probes'];

    public $hasOne = [
        'target' => [
            'bld\ddosspelbord\models\Target',
            'key' => 'id',
            'otherKey' => 'target_id'
        ],

    ];

}
