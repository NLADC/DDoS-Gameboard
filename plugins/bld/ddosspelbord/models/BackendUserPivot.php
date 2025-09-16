<?php namespace bld\ddosspelbord\Models;

use Backend\Models\UserRole;
use bld\ddosspelbord\classes\base\baseModel;
use bld\ddosspelbord\classes\helpers\ddosspelbordUsers;
use Model;

/**
 * Model
 */
class BackendUserPivot extends baseModel {
    use \Winter\Storm\Database\Traits\Validation;

    /**
     * @var string The database table used by the model.
     */
    public $table = 'bld_ddosspelbord_backend_user_pivot';

    /**
     * @var array Validation rules
     */
    public $rules = [];

    /**
     * @var array Attribute names to encode and decode using JSON.
     */
    public $jsonable = [];

    public $belongsTo = [
        'backenduser' => [
            'Backend\Models\User',
            'key'      => 'be_user_id',
            'otherKey' => 'id',
        ],
        'party'       => [
            'bld\ddosspelbord\models\Parties',
            'key'      => 'party_id',
            'otherKey' => 'id',
        ]
    ];

    public function getRoleAttribute()
    {
        return (!empty($this->backenduser) && !empty($this->backenduser->role)) ? $this->backenduser->role->name : '';
    }

    public function getFullNameAttribute(){
        return (!empty($this->backenduser)) ? $this->backenduser->first_name . ' ' .  $this->backenduser->last_name: '';
    }

    public function getRoleOptions($value,$formData) {

        $recs = ddosspelbordUsers::getBackendRoles();
        $ret = array();
        foreach ($recs AS $rec) {
            $ret[$rec->id] = [$rec->name, $rec->description];
        }

        // Check if user is editing themselves, return an empty array if true
        $userId = ddosspelbordUsers::getId();
        $ret = (!empty($userId) && !empty($this->be_user_id) && $this->be_user_id == $userId) ? [] : $ret;

        return $ret;
    }

}
