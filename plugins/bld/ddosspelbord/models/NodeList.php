<?php namespace bld\ddosspelbord\models;

use Model;
use DateTime;
use bld\ddosspelbord\models\MeasurementNode;
/**
 * Model
 */
class NodeList extends Model
{
    use \Winter\Storm\Database\Traits\Validation;

    use \Winter\Storm\Database\Traits\SoftDelete;

    protected $dates = ['deleted_at'];


    /**
     * @var string The database table used by the model.
     */
    public $table = 'bld_ddosspelbord_nodelists';

    /**
     * @var array Validation rules
     */
    public $rules = [
    ];

    public $belongsToMany = [
        'nodes' => [
            'bld\ddosspelbord\models\MeasurementNode',
            'table'    => 'bld_ddosspelbord_measurement_node_pivot',
            'key'      => 'measurement_nodelist_id',
            'otherKey' => 'measurement_type_id',
        ]
    ];

    public function getNodesOptions() {
        $recs = MeasurementNode::orderBy('updated_at', 'desc')->select('id', 'name', 'updated_at')->get();
        $ret = array();

        foreach ($recs as $rec) {
            $age = '';
            if ($timestamp = new DateTime($rec->updated_at)) {
                // Get the current date and time
                $now = new DateTime();

                // Calculate the difference in days
                $daysAgo = $now->diff($timestamp)->days;

                // Add "days ago" label
                $age = "({$daysAgo} days ago)";
            }

            // The actual return value
            $ret[$rec->id] = $rec->name . " " . $age;
        }

        return $ret;
    }


}
