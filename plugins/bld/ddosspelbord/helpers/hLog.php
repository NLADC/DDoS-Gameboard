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

namespace bld\ddosspelbord\helpers;

use Auth;
use Config;
use BackendAuth;
use Log;
use Mail;

if (!defined('CRLF_NEWLINE')) define('CRLF_NEWLINE', "\n");
if (!defined('CRLF_HTML_NEWLINE')) define('CRLF_HTML_NEWLINE', "<br />\n");

class hLog {

    static $_loglines = [];
    static $_hasError = false;
    static $_errLines = [];
    static $_logLevels = array('D-','I-','W-','E-');
    static $_logLevel = 0;  // 0=Debug, 1=Info, 2=Warning, 3=Error
    static $_echo = false;
    static $_user = '';

    public static function setLogLevel($setlevel) {
        SELF::$_logLevel = $setlevel;
    }

    public static function resetLog() {
        SELF::$_loglines = [];
        SELF::$_hasError = false;
    }

    public static function setEcho($echo=true) {
        self::$_echo = $echo;
    }

    public static function setUser($user) {
        self::$_user = $user;
    }

    /**
     * logLine(text): text-prefix;
     * 0 (D-)   : debug
     * 1 (I-)   : info
     * 2 (W-)   : warning
     * 3 (E-)   : error
     *
     * @param $text
     *
     */
    public static function logLine($text, $echo=false) {

        // get level
        $level = array_search(substr($text,0,2),SELF::$_logLevels);
        if ($level >= SELF::$_logLevel) {
            // front user
            if (self::$_user) {
                $user = self::$_user;
            } else {
                $user = Auth::getUser();
            }
            if ($user) {
                $user = $user->email;
            } else {
                $user = 'not_logged_in';
            }
            $text = "($user): " . $text;
            // log depending on level
            if ($level==0) {
                Log::debug($text);
            } elseif ($level==1) {
                Log::info($text);
            } elseif ($level==2) {
                Log::warning($text);
            } elseif ($level>=3) {
                Log::error($text);
            }
            // build own logmessages
            $line = date('Ymd H:i:s').'> '.$text."\n";
            self::$_loglines[] = $line;
            if (self::$_echo) $echo = true;
            if ($echo) echo $line;
            // if error set error flag
            if ($level>=3) {
                SELF::$_hasError = true;
                SELF::$_errLines[] = $line;
            }
        }
    }

    public static function hasError() {
        return SELF::$_hasError;
    }

    public static function returnLoglines() {

        $lines = '';
        foreach (self::$_loglines AS $logline) {
            $lines .= $logline.CRLF_NEWLINE;
        }
        return $lines;
    }

    public static function returnErrlines() {

        $lines = '';
        foreach (self::$_errLines AS $errline) {
            $lines .= $errline.CRLF_NEWLINE;
        }
        return $lines;
    }

    /**
     * Log Error mail
     *
     * @param $errorText
     */
    public static function errorMail($errorText, $excep = null, $errorSubject='') {

        $errormail  = Config::get('bld.ddosspelbord::errors.email','support@sendMailRaw');
        $subject    = Config::get('bld.ddosspelbord::errors.domain','') . ' - error: ' . (($errorSubject) ? $errorSubject : $errorText);
        $active     = (bool) Config::get('bld.ddosspelbord::errors.active', 1);

        Log::warning("W-ErrorMail: to=$errormail, subject=$subject");

        //$body = 'Error: '.$errorText.CRLF_NEWLINE;
        $body = 'Error: '.$errorText.CRLF_NEWLINE;
        /*
        $user = hUsers::getUser();
        if ($user) {
            $body .= 'User: login='.$user->login.', email='.$user->email.CRLF_NEWLINE;
        }
        */
        $body .= 'Error lines:'.CRLF_NEWLINE;
        $body .= self::returnErrlines();
        $body .= CRLF_NEWLINE.CRLF_NEWLINE;

        $body .= "Loglines:".CRLF_NEWLINE;
        $body .= self::returnLoglines();
        $body .= CRLF_NEWLINE.CRLF_NEWLINE;

        // dump traceback
        if ($excep!=null) {
            $body .= "Traceback:".CRLF_NEWLINE.$excep->getTraceAsString();
        }

        if($active) {

            // convert to html CRLF
            $body = str_replace(CRLF_NEWLINE,CRLF_HTML_NEWLINE,$body);

            hMail::sendMailRaw($errormail, $subject, $body);
        }
    }
}
