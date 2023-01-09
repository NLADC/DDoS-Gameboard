<?php
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

    private static $_backupsmtp = '';

    private static function _getSettings() {
        return [
            'host' => Config::get('bld.ddosspelbord::mail.host',''),
            'port' => Config::get('bld.ddosspelbord::mail.port','25'),
            'encryption' => Config::get('bld.ddosspelbord::mail.encryption', null),
            'username' => Config::get('bld.ddosspelbord::mail.username',''),
            'password' => Config::get('bld.ddosspelbord::mail.password',''),
        ];
    }

    public static function openSmptmailer() {

        // Backup default mailer
        self::$_backupsmtp = Mail::getSwiftMailer();

        $setting = self::_getSettings();
        //Log::info("D-openSmptmailer.settings; host=" . $setting['host'] . ", port=" . $setting['port'] . ", encryption=" . $setting['encryption'] );

        // Setup own smtp mailer
        $transport = new Swift_SmtpTransport(
            $setting['host'],
            $setting['port'],
            $setting['encryption']);
        $transport->setUsername($setting['username']);
        $transport->setPassword($setting['password']);
        // can use self signed certs
        $transport->setStreamOptions([
            'ssl' => [
                'verify_peer' => false,
                'allow_self_signed' => true,
            ]
        ]);
        $smtpmail = new Swift_Mailer($transport);

        // Set the mailer
        Mail::setSwiftMailer($smtpmail);
    }

    public static function closeSmptmailer() {

        // Restore your original mailer
        Mail::setSwiftMailer(self::$_backupsmtp);
    }

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

        self::openSmptmailer();

        try {
            // Send your message
            Mail::sendTo($to, $mailview, $params, function($message) use ($bcc,$from) {
                $message->from($from, $name = null);
                // bcc for testing
                if ($bcc) $message->bcc($bcc);
            });

            //Log::info("D-sendMail succes");

        } catch(\Exception $err) {

            // NB: \Expection is important, else not in this catch when error in Mail
            Log::error("Error sendMail(to=$to,from=$from, mailview=$mailview): error=" . $err->getMessage() );

        }

        self::closeSmptmailer();

    }

    public static function sendMailRaw($to,$subject,$body,$from='',$attachment='',$attachmentname='') {

        if ($from=='') $from = Config::get('bld.ddosspelbord::errors.from','support@ddosgameboard.com');

        $message_id = '';

        $alt_email  = Config::get('bld.ddosspelbord::mail.overrule_to','');
        if ($alt_email) {
            Log::info("D-Alternate email address (TEST MODE); use '$alt_email' for '$to' ");
            $subject = "[ALT_EMAIL active; org=$to] $subject";
            $to = $alt_email;
        }

        // use own smtp mailer
        self::openSmptmailer();

        try {

            Mail::raw(['text' => strip_tags($body),'html' => $body], function($message) use ($to,$subject,$from,$attachment,$attachmentname,&$message_id) {

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

                $message_id = $message->getId();

            });

            Log::info("D-sendMailRaw succes");

        } catch(\Exception $err) {

            // NB: \Expection is important, else not in this catch when error in Mail
            Log::error("Error sendMailRaw(to=$to,from=$from,subject=$subject): error=" . $err->getMessage() );

        }

        self::closeSmptmailer();

        return $message_id;
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
