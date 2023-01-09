<?php namespace Bld\Ddosspelbord\Models;

use Db;
use Auth;
use Hash;
use Model;
use October\Rain\Support\Facades\Flash;
use Session;
use Mail;
use Request;
use URL;
use Winter\User\Models\User;
use bld\ddosspelbord\helpers\hLog;

/**
 * Model
 */
class Spelbordusers extends Model
{
    use \Winter\Storm\Database\Traits\Validation;

    /**
     * @var string The database table used by the model.
     */
    public $table = 'bld_ddosspelbord_users';

    /**
     * @var array Validation rules
     */
    public $rules = [
        'name' => 'required',
        'email'    => 'required',
        'password'    => 'required',
        'party_id'    => 'required',
        'role_id'    => 'required',
    ];

    protected $hidden = [
        'email_verified_at', 'settings',
        'password', 'remember_token', 'api_token',
        'avatar', 'created_at', 'updated_at'
    ];

    protected $fillable = [
        'name','email','party_id','role_id','password',
    ];

    public $hasOne = [
        'parties' => [
            'bld\ddosspelbord\models\Parties',
            'key' => 'id',
            'otherKey' => 'party_id'
        ],
        'roles' => [
            'bld\ddosspelbord\models\Roles',
            'key' => 'id',
            'otherKey' => 'role_id'
        ],
    ];

    public function onResetPassword() {
        $user = User::findByEmail( $this->email );
        $pwd = 'P@$$w0rd';    // Retreive password in the request
        $user->password = $pwd;
        $user->password_confirmation = $pwd;
        $user->save();
    }

    public function getPartyIdOptions() {

        $recs = Parties::orderBy('name')->select('id','name')->get();
        $ret = ['0' => '(no party)'];
        foreach ($recs AS $rec) {
            $ret[$rec->id] = $rec->name;
        }
        return $ret;
    }

    public function getRoleIdOptions() {

        $recs = Roles::orderBy('id')->select('id','name','display_name')->get();
        $ret = array();
        foreach ($recs AS $rec) {
            $ret[$rec->id] = $rec->name . ' - '.$rec->display_name;
        }
        return $ret;
    }


    /**
     * @param $scopes
     * @return array
     */
    public function getUserOptions($scopes = null) {

        if (!empty($scopes['party_id']->value)) {
            $party_ids = array_keys($scopes['party_id']->value);
            hLog::logLine("D-getUserOptions scopes value: " . print_r($party_ids, true));
            $recs = Spelbordusers::whereIn('party_id', $party_ids);
        } else {
            $recs = Spelbordusers::where('id','>','0');
        }
        $recs = $recs->orderBy('id')->select('id','name','email')->get();
        $ret = array();
        foreach ($recs AS $rec) {
            $ret[$rec->id] = $rec->name . ' - '.$rec->email;
        }
        return $ret;
    }

    // ** function for sync spelborduser with (wintercms plugin) users

    /**
     * @return false|void
     */
    public function beforeCreate() {

        if (($user = User::findByEmail($this->email)) == 0) {
            // insert user in users table
            $user = Auth::register([
                'name' => $this->name,
                'email' => $this->email,
                'password' => $this->password,
                'password_confirmation' => $this->password,
                ],true
            );
            $user->convertToRegistered();
            $user = User::find($user->id);
        }
        //hLog::logLine("beforeCreate; user=" . print_r($user->toArray(),true));
        if ($user) {
            // connect
            $this->user_id = $user->id;
            $this->password = $user->password;
        } else {
            Flash::error('Error create user');
            return false;
        }
    }

    public function afterCreate() {

    }

    public function beforeUpdate() {

        if ($this->user_id) {
            // always sync
            $user = User::find($this->user_id);
            if ($user) {
                $user->name = $this->name;
                $user->email = $this->email;
                if ($this->password) $user->password = $this->password;
                $user->password_confirmation = $this->password;
                $user->username = $this->email;
                $user->save();

                $this->password = $user->password;
            }

        } else {
            // error
            Flash::error('Error update user');
            return false;
        }

    }

    public function beforeDelete() {

        if ($this->user_id) {
            $user = User::find($this->user_id);
            if ($user) {
                $user->forceDelete();
            }
        }

    }

    // ** spelbord data functions for frontend **

    public static function getOnAuth($log=false) {

        $user = false;
        $userauth = Auth::getUser();
        if ($userauth && $userauth->deleted_at == null) {
            //hLog::logLine("D-getOnAuth=" . print_r($userauth->toArray(),true));
            $user = Spelbordusers::where('user_id', $userauth->id)->first();
            if ($user) {
                $user->partyId = $user->party_id;
                $role = Roles::find($user->role_id);
                $user->role = ($role) ? $role->name : DB_ROLE_BLUE;
                // update heartbeat without model overhead
                $upd = Db::table('bld_ddosspelbord_users')
                    ->where('id',$user->id)
                    ->update(['heartbeat' => date('Y-m-d H:i:s')]);
            } else {
                if ($log) hLog::logLine("W-userSpelbord; can not find (spelbord) user with email=$userauth->email");
            }
        } else {
            if ($log) hLog::logLine("D-userSpelbord; no access");
        }
        if (!$user) {
            $user = new \stdClass();
            $user->id = 0;
            $user->partyId = 0;
            $user->name = 'guest';
            $user->role = 'blue';
            $user->settings = '';
        }
        //hLog::logLine("D-userSpelbord; user=".print_r($user->toArray(),true));
        return $user;
    }

    public static function verifyAccess($checktoken=true) {

        // check if logged in
        $user = Spelbordusers::getOnAuth();
        if ($user && $user->id!=0) {

            if ($checktoken) {
                // check token
                $token = post('_token', '');
                if ($token != Session::token()) {
                    hLog::logLine("W-verifyAccess; token from '$user->email' not valid; _token='$token'");
                    $user = false;
                }
            }

            // do heartbeat -> monitoring/verify of logged in


        } else {
            hLog::logLine("W-verifyAccess; no access (not logged in)");
            $user = false;
        }

        return $user;

    }

    /**
     * Every login (getOnAuth) heartbeat is set
     * When heartbeat is more then USER_ACTIVE_MAX_SEC ago, then user is not active anymore
     *
     * @return bool
     */
    public static function verifyActive() {

        $active = false;
        $userauth = Auth::getUser();
        if ($userauth) {
            $user = Spelbordusers::where('user_id', $userauth->id)->first();
            if (!empty($user->heartbeat)) {
                $active = (time() - strtotime($user->heartbeat) <= USER_ACTIVE_MAX_SEC);
            }
        }
        return $active;
    }


    static function doLogout($userauth_id) {

        // update heartbeat
        $upd = Db::table('bld_ddosspelbord_users')
            ->where('user_id',$userauth_id)
            ->update(['heartbeat' => null]);
        // logout
        Auth::logout();
    }


}


