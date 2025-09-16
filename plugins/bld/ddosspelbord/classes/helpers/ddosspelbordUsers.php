<?php

namespace bld\ddosspelbord\classes\helpers;

use BackendAuth;
use bld\ddosspelbord\Models\BackendUserPivot;
use Bld\Ddosspelbord\Models\Parties;
use Bld\Ddosspelbord\Models\Spelbordusers;
use Config;
use Db;
use Log;
use bld\ddosspelbord\helpers\hLog;
use Translator;

class ddosspelbordUsers {

    static $_unknown = '(unknown)';
    static $_SPELBORDadmin = 'ddosgameboard-admin';
    static $_SPELBORDmanager = 'ddosgameboard-manager';
    static $_SPELBORDapi = 'ddosgameboard-api';

    static $_SPELBORDroleIds = [];

    public static function getBackendRoles() {
        $query = Db::table('backend_user_roles')
            ->where('is_system','0')
            ->where('code','<>','ddosgameboard')
            ->orderBy('id')
            ->select('id','code', 'name','description');

        // No Admin Role when you don't have the permission
        $user = self::getUser();

        if (!empty($user)) {
            if( !($user->hasAccess('bld.ddosspelbord.backendusers'))) {
                $query->whereNot('code', self::$_SPELBORDadmin);
                $query->whereNot('code', self::$_SPELBORDapi);
            }
            if( !($user->hasAccess('bld.ddosspelbord.spelbordusers'))) {
                $query->whereNot('code', self::$_SPELBORDmanager);
            }
        }

        return $query->get();
    }

    public static function roleId($code) {
        if (!isset(self::$_SPELBORDroleIds[$code])) {
            $rec = Db::table('backend_user_roles')->where('code',$code)->first();
            self::$_SPELBORDroleIds[$code] = ($rec) ? $rec->id : 0;
        }
        return self::$_SPELBORDroleIds[$code];
    }


    public static function getWorkuserId($login) {

        $user = Db::table('backend_users')->where('login', $login)->first();
        return ($user) ? $user->id : 0;
    }

    public static function getWorkuserLogin($id) {

        $user = Db::table('backend_users')->where('id', $id)->first();
        return ($user) ? $user->login : self::$_unknown;
    }

    public static function getUserPartyId($userId) {
        $id = 0;
        $user = Spelbordusers::where('be_user_id', $userId)->first();
        if (!empty($user->party_id)) {
            $id = $user->party_id;
        }
        return $id;
    }



    public static function getId() {
        $user = self::getUser();
        if (!empty($user->id)) {
            $return = $user->id;
        } else {
            hLog::logLine("W-getId method of user id; no user found");
            $return = false;
        }
        return $return;
    }

    public static function getPartyId() {
        $user = self::getUser();
        if (!empty($user->party_id)) {
            $return = $user->party_id;
        } else {
            hLog::logLine("W-getId method of user id; no user found");
            $return = false;
        }
        return $return;
    }
    public static function getBackendPartyId() {
        $user = self::getBackendPivotUser();
        if (!empty($user->party_id)) {
            $return = $user->party_id;
        } else {
            hLog::logLine("W-getId method of user id; no user found");
            $return = false;
        }
        return $return;
    }

    public static function getUser() {
        return BackendAuth::getUser();
    }

    public static function getBackendPivotUser() {
        $user = BackendAuth::getUser();
        $backendPivotUser = BackendUserPivot::where('be_user_id', $user->id)->first();

        return (!empty($backendPivotUser)) ? $backendPivotUser : false;
    }

    public static function getUserId() {
        return BackendAuth::getUser()->id;
    }



    public static function isManager($workuser_id = 0)
    {
        return self::checkRoleOrSuperUser(self::$_SPELBORDmanager, $workuser_id);
    }

    public static function isSpelbordAdmin($workuser_id = 0)
    {
        return self::checkRoleOrSuperUser(self::$_SPELBORDadmin, $workuser_id);
    }

    public static function isSuperUser()
    {
        $user = BackendAuth::getUser();
        return $user && $user->isSuperUser();
    }

    /**
     * Checks if the user is a super user and return true or has a specific role.
     *
     * @param  string  $roleName
     * @param  int     $workuser_id  If 0, use the currently authenticated user
     * @return bool
     */
    private static function checkRoleOrSuperUser($roleName, $workuser_id = 0)
    {
        // If the current user is a super user, no further checks needed
        if (self::isSuperUser()) {
            return true;
        }

        // Fall back to the logged-in user if none provided
        $userId = $workuser_id ?: self::getId();
        if (!$userId) {
            return false;
        }

        // Look up the numerical ID for the role
        $roleId = self::roleId($roleName);
        if (!$roleId) {
            return false;
        }

        // Check if that user row has the matching role_id
        return DB::table('backend_users')
            ->where('id', $userId)
            ->where('role_id', $roleId)
            ->exists();
    }


    public static function isApiUser($workuser_id=0) {

        $userid = ($workuser_id==0) ?  self::getId() : $workuser_id;
        $chk = Db::table('backend_users')->where('id',$userid)->where('role_id',self::roleId(self::$_SPELBORDapi))->first();
        return ($chk!='');
    }

    public static function isAdmin() {
        $user = BackendAuth::getUser();
        return ($user) ? ($user->is_superuser==1) : false;
    }

    /**
     * @return bool
     */
    public static function filterOnParty() {
        $user = BackendAuth::getUser();
        return !($user->hasAccess('bld.ddosspelbord.parties', false));
    }



    public static function getFullName($id=0) {

        if ($id==0) $id = self::getId();
        $user = Db::table('backend_users')->where('id', $id)->first();
        return ( ($user) ? $user->first_name . ' ' . $user->last_name : self::$_unknown);
    }

    public static function getLogin() {

        $user = self::getUser();
        $login = ($user) ? $user->login : self::$_unknown;
        return $login;
    }

    // ** locale ** //

    public static function getLocale() {

        $trans = Trans::instance();
        return $trans->getLocale();
    }

}
