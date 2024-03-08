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

use App;
use Log;
use Mail;
use Config;
use \Swift_Mailer;
use \Swift_SmtpTransport;
use Geekdevs\SwiftMailer\Transport\FileTransport;

class hMail {

    /**
     * Central class for send formated mail
     *
     * Note: Set OWN SMTP handler so we don't need to fill the System setting with our user and password (!)
     *
     * @param $to
     * @param $mailview
     * @param $params
     */

    public static function sendMail($to,$mailview,$params, $bcc='' ) {

        $from = 'noreply@' . Config::get('bld.ddosspelbord::errors.domain','ddosgameboard.nl');

        $alt_email  = Config::get('bld.ddosspelbord::mail.overrule_to','');
        if ($alt_email) {
            Log::info("D-Alternate email address (TEST MODE); use '$alt_email' for '$to' ");
            $to = $alt_email;
        }

        if (file_exists(plugins_path() . '/bld/ddosspelbord/views/mail')) {
            $locale = App::getLocale();
            // Get the last wordt of the mailview, that is the name of the htm template.
            $templatename = substr($mailview, strrpos($mailview, ".") + 1);

            if (file_exists(plugins_path() . '/bld/ddosspelbord/views/mail/' . $locale)) {
                //insert the locale 2 letters word in the mailview.
                $mailview = 'bld.ddosspelbord::mail.' . $locale . '.' . $templatename;
            }
        }

        try {
            // Send your message
            Mail::sendTo($to, $mailview, $params, function($message) use ($bcc,$from) {
                $message->from($from, $name = null);
                // bcc for testing
                if ($bcc) $message->bcc($bcc);
            });

            Log::info("D-sendMail from=$from to=$to ");

        } catch(\Exception $err) {

            // NB: \Expection is important, else not in this catch when error in Mail
            Log::error("E-Error sendMail(to=$to,from=$from, mailview=$mailview): error=" . $err->getMessage() );

        }

    }

    public static function sendMailRaw($to,$subject,$body,$from='',$attachment='',$attachmentname='') {

        if ($from=='') $from = Config::get('bld.ddosspelbord::errors.from','support@ddosgameboard.com');

        $alt_email  = Config::get('bld.ddosspelbord::mail.overrule_to','');
        if ($alt_email) {
            Log::info("D-Alternate email address (TEST MODE); use '$alt_email' for '$to' ");
            $subject = "[ALT_EMAIL active; org=$to] $subject";
            $to = $alt_email;
        }

        try {

            Mail::raw(['text' => strip_tags($body),'html' => $body], function($message) use ($to,$subject,$from,$attachment,$attachmentname) {

                $message->to($to, $name = null);
                $message->subject($subject);
                $message->from($from, $name = null);

                // add attachment
                if ($attachment) {
                    if ($attachmentname) {
                        $message->attach($attachment,['as' => $attachmentname]);
                    } else {
                        $message->attach($attachment);
                    }
                }

            });

            Log::info("D-sendMailRaw succes");

        } catch(\Exception $err) {

            // NB: \Expection is important, else not in this catch when error in Mail
            Log::error("E-Error sendMailRaw(to=$to,from=$from,subject=$subject): error=" . $err->getMessage() );

        }

        return true;
    }

    /**
     * sendAlert -> use mailview
     *
     * @param $mailview
     * @param array $mailprms
     */

    public static function sendAlert($alertlevel,$mailview,$mailprms=[]) {

        $level = Config::get('bld.ddosspelbord::alerts.level');
        if ($alertlevel >= $level) {

            $to = Config::get('bld.ddosspelbord::alerts.recipient');
            $bcc = Config::get('bld.ddosspelbord::alerts.bcc_recipient');

            clsLog::logLine("D-sendAlert; send report '$mailview' to: $to");
            clsMail::sendMail($to, $mailview, $mailprms, $bcc);

        } else {
            clsLog::logLine("D-Skip sending alert (alertlevel=$alertlevel < $level) of mailview=$mailview");
        }

    }

}
