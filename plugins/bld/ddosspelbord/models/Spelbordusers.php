<?php
/*
 * Copyright (C) 2024 Anti-DDoS Coalitie Netherlands (ADC-NL)
 *
 * This file is part of the DDoS gameboard.
 *
 * DDoS gameboard is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * DDoS gameboard is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; If not, see @link https://www.gnu.org/licenses/.
 *
 */

namespace Bld\Ddosspelbord\Models;

use bld\ddosspelbord\classes\base\baseModel;
use bld\ddosspelbord\classes\middleware\ReqAccesToken;
use Db;
use Auth;
use Hash;
use Model;
use File;
use Winter\Storm\Auth\AuthenticationException;
use Winter\Storm\Exception\ApplicationException;
use Winter\Storm\Support\Facades\Flash;
use Session;
use Mail;
use Request;
use URL;
use Winter\User\Models\User;
use bld\ddosspelbord\helpers\hLog;
use Laravel\Passport\TokenRepository;
use League\OAuth2\Server\ResourceServer;
use Psr\Http\Message\ServerRequestInterface;


/**
 * Model
 */
class Spelbordusers extends baseModel
{
    use \Winter\Storm\Database\Traits\Validation;

    /**
     * @var string The database table used by the model.
     */
    public $table = 'bld_ddosspelbord_users';
    protected $roleLocked = true;

    /**
     * @var array Validation rules
     */
    public $rules = [
        'name' => 'required',
        'email'    => 'required',
        'password'    => 'required:create',
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

    /**
     * @return void
     */
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
        parent::beforeCreate();
        $user = User::findByEmail($this->email);
        if (empty($user)) {
            // insert user in users table
            $user = Auth::register([
                'name' => $this->name,
                'email' => $this->email,
                'password' => $this->password,
                'password_confirmation' => $this->password,
                ],true);
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

        $newRole = $this->role_id;
        $oldRole = $this->original['role_id'];
        if (intval($newRole) !== intval($oldRole)) {
            throw new ApplicationException('Role change must be done via the change role functionality');
            return false;
        }
    }

    public function changeRoleAndDeleteData($roleId) {

        $this->deleteUserData();
        Db::table('bld_ddosspelbord_users')->where('id', $this->id)->update(['role_id' => intval($roleId)]);
    }

    private function deleteUserData() {

        Attack::where('user_id', $this->id)->delete();
        foreach (Logs::where('user_id',$this->id)->get() as $log) {
            File::where('attachment_type', 'Bld\Ddosspelbord\Models\Logs')->where('attachment_id',$log->id)->delete();
        }
        Logs::where('user_id', $this->id)->delete();
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
        }
        else {
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

        // Check if user is from the API and has a valid bearer token
        $request = request(); // Get the current request instance
        $user = null;

        // Check if this is an API request and has a laravel/passport Bearer token
        if (!empty($request->bearerToken())) {
            if (self::validateSystemUser()) {
                $user = self::createSystemUserObject();
                $checktoken = false;
            }
        }
        else {
            // check if logged in on the Gameboard application as as USER
            $user = Spelbordusers::getOnAuth();
        }

        if ($user) {
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

    private static function validateSystemUser(){
        try {
            // Use Passport's ResourceServer to validate the Bearer token
            $server = app(ResourceServer::class);
            $psr = app(ServerRequestInterface::class); // Convert Laravel request to PSR-7 request
            $validatedRequest = $server->validateAuthenticatedRequest($psr);

            // Get token ID from the validated request
            $tokenId = $validatedRequest->getAttribute('oauth_access_token_id');

            $tokenRepository = app(TokenRepository::class);
            $token = $tokenRepository->find($tokenId);

            if ($token && !$token->revoked) {
                return true;
            } else {
                throw new AuthenticationException('Invalid or missing Bearer token.');
            }

        } catch (AuthenticationException $e) {
            return response()->json(['error' => 'Bearer token authentication failed: ' . $e->getMessage()], 401);
        }
    }

    public static function createSystemUserObject(){
        return (object)[
            'id' => 0,
            'partyId' => 0,
            'name' => 'System',
            'role' => '',
            'settings' => '',
        ];
    }

    public static function createSystemUserArray(){
        return [
            'id' => 0,
            'partyId' => 0,
            'name' => 'System',
            'role' => '',
            'settings' => '',
        ];
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


