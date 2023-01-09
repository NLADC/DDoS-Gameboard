<?php

namespace bld\ddosspelbord\components;

use Auth;
use Bld\Ddosspelbord\Models\Settings;
use Input;
use Config;
use League\Csv\Exception;
use Session;
use Redirect;
use Response;
use Bld\Ddosspelbord\Controllers\Feeds;
use Bld\Ddosspelbord\Models\Logs;
use Bld\Ddosspelbord\Models\spelbordusers;
use Cms\Classes\ComponentBase;
use bld\ddosspelbord\helpers\hLog;
use System\Models\File as File;
use bld\ddosspelbord\helpers\base64Helper as base64Helper;

class ddosspelbord_log extends ComponentBase
{

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

    public function getVersion()
    {
        return Config::get('bld.ddosspelbord::release.version', '0.9.?') . ' - ' . Config::get('bld.ddosspelbord::release.build', 'build 1');
    }

    public function init()
    {
        hLog::logLine("D-ddosspelbord_log.init; version=" . ddosspelbord_data::getVersion());
    }

    public function onRun()
    {
    }

    /***
     * This function is called when the user submits a log via the Log api,
     * it wil save the log to the database,
     * but it wil also return the log to the client directly and to the transaction controller.
     * The DB will be updated, the sender of the log directly and the party members as well by transaction
     * @return Response\
     */
    public static function submitLog()
    {
        hLog::logLine("D-ddosspelbord_log.submitLog");

        $alog = [];
        $result = false;
        $message = '';

        try {
            // get gameboard user
            if ($user = Spelbordusers::verifyAccess()) {

                if ($user->role != 'observer') {

                    $time = post('timestamp', '');

                    if ($time && strtotime($time) !== false) {

                        $timestamp = Settings::get('startdate');
                        $timestamp = str_replace('00:00:00', $time, $timestamp);

                        $logtext = post('log', '');
                        // strip all tags
                        $logtext = strip_tags($logtext);
                        // Strip dangerous tags
                        $logtext = preg_replace('#<script(.*?)>(.*?)</script>#is', '', $logtext);
                        $logtext = preg_replace('#function(.*?)[(][)](.*?)#is', '', $logtext);
                        $logtext = preg_replace('#http:[/][/](.*?)#is', '', $logtext);
                        // Completely sanitize input
                        $logtext = filter_var($logtext, FILTER_SANITIZE_STRING);

                        $id = post('id', '');

                        if (!empty($id)) {
                            hLog::logLine("D-ddosspelbord_log.submitLog; id=$id, update log");
                            $log = Logs::find($id);
                        } else {
                            hLog::logLine("D-ddosspelbord_log.submitLog; create new log");
                            $log = new Logs();
                            $log->user_id = $user->id;
                        }
                        if (!empty($log) && !empty($logtext)) {

                            if ($log->user_id != $user->id) {
                                $message = 'You cannot update a log from another user';
                            } else {
                                $log->log = $logtext;
                                $log->timestamp = $timestamp;
                                $log->save();

                                if (!(empty(post('deletefilesbyid', '')))) {
                                    $deletefilesbyid = post('deletefilesbyid', '');
                                    for ($i = 0; $i < count($deletefilesbyid); ++$i) {
                                        if (empty(File::find($deletefilesbyid[$i]))) {
                                            throw new ApplicationException(sprintf('File to be deleted by id:'. $deletefilesbyid[$i] . 'Doesn\'t exist'));
                                        }
                                        else {
                                            File::find($deletefilesbyid[$i])->delete();
                                        }
                                    }
                                }
                                $hasattachments = false;

                                if (!(empty(post('hasorgattachments', '')))) {
                                    $hasattachments = true;
                                }

                                if (!(empty(post('attachments', '')))) {

                                    $logattachments = post('attachments', '');

                                    $maxfiles = Settings::get('logmaxfiles');
                                    if (count($logattachments) > $maxfiles) {
                                        throw new ApplicationException(sprintf('You can only upload ' . $maxfiles . 'files to the server'));
                                    }
                                    for ($i = 0; $i < count($logattachments); ++$i) {
                                        // When no rawdata is suplied, te file already exists
                                        if (!empty($logattachments[$i]['rawdata'])) {
                                            $raw64data = base64Helper::RemoveBase64Header($logattachments[$i]['rawdata']);
                                            $rawdata = base64_decode($raw64data);
                                            $filename = $logattachments[$i]['filename'];
                                            $ext = pathinfo($filename, PATHINFO_EXTENSION);
                                            if (in_array($ext, Config::get('bld.ddosspelbord::acceptedfiletypes'))) {
                                                $file = (new File)->fromData($rawdata, $filename);
                                                $logmaxfilesizeinmb = Settings::get('logmaxfilesize');
                                                $logmaxfilesize = $logmaxfilesizeinmb * 1024 * 1024;
                                                if ($file->file_size > $logmaxfilesize) {
                                                    throw new ApplicationException(sprintf('You can only upload ' . $logmaxfilesizeinmb . 'files to the server'));
                                                }
                                                $file->is_public = true;
                                                $file->save();
                                                $log->attachments()->add($file);
                                            }
                                            else {
                                                $message = 'Error create/update log, one or more files have forbidden extension';
                                            }

                                        }
                                    }
                                    $hasattachments = true;
                                }
                                
                                // get vue code values & create transaction
                                $alog = ddosspelbord_data::getSpelbordLog($log, $hasattachments);
                                hlog::logLine("submitLog.alog=" . print_r($alog,true ));
                                (new Feeds())->createTransaction(TRANSACTION_TYPE_LOG, $alog);

                                $result = true;
                            }

                        } else {
                            $message = 'Error create/update log';
                        }

                    } else {
                        $message = "No valid log timestamp; input timestamp='$time' ";
                    }

                } else {
                    $message = "No logging rights";
                }

            }

        } catch (Exception $err) {
            $message = "Cannot save log; error: " . $err->getMessage();
            hLog::logLine("E-$message");
            $result = false;
        }

        if ($message) {
            hLog::logLine("W-$message");
        }

        return Response::json([
            'result' => $result,
            'message' => $message,
            'log' => json_encode($alog),
        ]);
    }

}
