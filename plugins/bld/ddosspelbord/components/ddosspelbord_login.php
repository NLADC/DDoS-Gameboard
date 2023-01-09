<?php
namespace bld\ddosspelbord\components;

use Db;
use Auth;
use Bld\Ddosspelbord\Controllers\Feeds;
use Bld\Ddosspelbord\Models\Spelbordusers;
use Input;
use Config;
use League\Csv\Exception;
use Session;
use Url;
use Redirect;
use Response;
use Cms\Classes\ComponentBase;
use bld\ddosspelbord\helpers\hLog;

class ddosspelbord_login extends ComponentBase {

    public function componentDetails()
    {
        return [
            'name' => 'Anti-DDoS Coalitie DDoS spelbord',
            'description' => 'Handle backend calls'
        ];
    }

    public function defineProperties()
    {
        return [
        ];
    }

    public function init() {
        hLog::logLine("D-ddosspelbord_login.init; version=".ddosspelbord_data::getVersion());
    }
    public function onRun() {
    }

    /**
     * @return Response\
     */
    public static function login() {

        $login = post('email');
        $password = post('password');

        hLog::logLine("D-ddosspelbord_login.login; login=$login ");

        $user = $alreadyloggedin = false;

        // to-do; check _token?!

        try {

            /**
             * Strange; de-activated users give unknown error with Auth::authenticate
             * Workaround with looking at deletd_at first
             *
             */

            $user = Db::table('users')->where('email',$login)->first();
            if ($user) {

                if ($user->deleted_at==null) {

                    $user = Auth::authenticate([
                        'login' => $login,
                        'password' => $password,
                    ]);

                } else {
                    $user = '';
                    hLog::logLine("W-ddosspelbord_login.login; $login has no access");
                }

            }

        } catch (\Exception $err) {
            hLog::logLine("E-ddosspelbord_login.login; error login: ".$err->getMessage());
            $user = '';
        }

        if ($user) {
            hLog::logLine("D-ddosspelbord_login.login; login of $login");
        }


        $data = [
            'result' => ($user)?true:false,
            'alreadyloggedin' => $alreadyloggedin,
        ];

        return Response::json($data);
    }

    /**
     * @return Response\
     */
   public static function logout() {

       // reset last hash
       Feeds::resetLastsettingHash();
       // mark as logout
       if ($userauth = Auth::getUser()) {
           Spelbordusers::doLogout($userauth->id);
       }
       hLog::logLine("D-ddosspelbord_login.logout");
       return Response::json(['result' => true]);
   }

    public static function onRedirectToLogin() {
        $baseUrl = Url::to('/');
        return Redirect::to($baseUrl);
    }

}
