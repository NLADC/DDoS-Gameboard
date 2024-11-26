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
use Bld\Ddosspelbord\Controllers\Logs as LogsController;
use Bld\Ddosspelbord\Models\spelbordusers;
use Cms\Classes\ComponentBase;
use bld\ddosspelbord\helpers\hLog;
use System\Models\File as File;
use bld\ddosspelbord\helpers\base64Helper;

class ddosspelbord_log extends ComponentBase {

    public function componentDetails() {
        return [
            'name'        => 'Anti-DDoS Coalitie DDoS spelbord',
            'description' => 'Handle backend calls'
        ];
    }

    public function defineProperties() {
        return [];
    }

    public function getVersion() {
        return Config::get('bld.ddosspelbord::release.version', '0.9.?') . ' - ' . Config::get('bld.ddosspelbord::release.build', 'build 1');
    }

    public function init() {
        hLog::logLine("D-ddosspelbord_log.init; version=" . ddosspelbord_data::getVersion());
    }

    public function onRun() {
    }

    /***
     * This function is called when the user submits a log via the Log api,
     * it wil save the log to the database,
     * but it wil also return the log to the client directly and to the transaction controller.
     * The DB will be updated, the sender of the log directly and the party members as well by transaction
     * @return Response\
     */
    public static function submitLog( $data = [] ) {
        hLog::logLine("D-ddosspelbord_log.submitLog");

        $alog = [];
        $result = false;
        $message = '';

        // Use the provided data array instead of relying on post() or $_POST
        $dataLog = isset($data['log']) ? $data['log'] : post('log', '');
        $dataTimestamp = isset($data['timestamp']) ? $data['timestamp'] : post('timestamp', '');

        $acceptedfiletypes = LogsController::GetAllowedFiletypes();

        try {
            // get gameboard user
            if ( $user = Spelbordusers::verifyAccess() ) {

                if ( $user->role != 'observer' ) {

                    $time = $dataTimestamp;

                    if ( $time && strtotime($time) !== false ) {

                        $startdate = Settings::get('startdate');
                        $timestamp = preg_replace('/\d{2}:\d{2}:\d{2}/', $time, $startdate);
                        hLog::logLine("D-ddosspelbord_log.submitLog; startdate=$startdate, time=$time, timestamp=$timestamp");

                        $logtext = $dataLog;
                        // analyze input
                        $logtext = htmlspecialchars($logtext, ENT_NOQUOTES | ENT_SUBSTITUTE | ENT_IGNORE);

                        $id = post('id', '');

                        if ( !empty($id) ) {
                            hLog::logLine("D-ddosspelbord_log.submitLog; id=$id, update log");
                            $log = Logs::find($id);
                        }
                        else {
                            hLog::logLine("D-ddosspelbord_log.submitLog; create new log");
                            $log = new Logs();
                            $log->user_id = $user->id;
                        }
                        if ( !empty($log) && !empty($logtext) || !( empty(post('attachments', '')) ) ) {

                            if ( $log->user_id != $user->id ) {
                                $message = 'You cannot update a log from another user';
                            }
                            else {
                                $log->log = $logtext;
                                $log->timestamp = $timestamp;
                                $log->save();

                                if ( !( empty(post('deletefilesbyid', '')) ) ) {
                                    $deletefilesbyid = post('deletefilesbyid', '');
                                    for ( $i = 0; $i < count($deletefilesbyid); ++$i ) {
                                        if ( empty(File::find($deletefilesbyid[ $i ])) ) {
                                            throw new ApplicationException(sprintf('File to be deleted by id:' . $deletefilesbyid[ $i ] . 'Doesn\'t exist'));
                                        }
                                        else {
                                            File::find($deletefilesbyid[ $i ])->delete();
                                        }
                                    }
                                }
                                $hasattachments = false;

                                if ( !( empty(post('hasorgattachments', '')) ) ) {
                                    $hasattachments = true;
                                }

                                if ( !( empty(post('attachments', '')) ) ) {

                                    $logattachments = post('attachments', '');

                                    $maxfiles = Settings::get('logmaxfiles');
                                    if ( count($logattachments) > $maxfiles ) {
                                        throw new ApplicationException(sprintf('You can only upload ' . $maxfiles . 'files to the server'));
                                    }
                                    for ( $i = 0; $i < count($logattachments); ++$i ) {
                                        // When no rawdata is suplied, te file already exists
                                        if ( !empty($logattachments[ $i ]['rawdata']) ) {
                                            $raw64data = base64Helper::RemoveBase64Header($logattachments[ $i ]['rawdata']);
                                            $rawdata = base64_decode($raw64data);
                                            $filename = $logattachments[ $i ]['filename'];
                                            $ext = pathinfo($filename, PATHINFO_EXTENSION);
                                            if ( in_array($ext, $acceptedfiletypes) ) {
                                                $file = ( new File )->fromData($rawdata, $filename);
                                                $logmaxfilesizeinmb = Settings::get('logmaxfilesize');
                                                $logmaxfilesize = $logmaxfilesizeinmb * 1024 * 1024;
                                                if ( $file->file_size > $logmaxfilesize ) {
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
                                //hlog::logLine("submitLog.alog=" . print_r($alog,true ));

                                try {
                                    ( new Feeds() )->createTransaction(TRANSACTION_TYPE_LOG, $alog);
                                }
                                catch (\Throwable $err)  {
                                    $message = "Cannot create transaction error: " . $err->getMessage();
                                    hLog::logLine("E-$message");
                                }

                                $result = true;
                            }
                        }
                        else {
                            $message = 'Error create/update log';
                        }

                    }
                    else {
                        $message = "No valid log timestamp; input timestamp='$time' ";
                    }

                }
                else {
                    $message = "No logging rights";
                }

            }

        }
        catch ( Exception $err ) {
            $message = "Cannot save log; error: " . $err->getMessage();
            hLog::logLine("E-$message");
            $result = false;
        }

        if ( $message ) {
            hLog::logLine("W-$message");
        }

        return Response::json([
                                  'result'  => $result,
                                  'message' => $message,
                                  'log'     => json_encode($alog),
                              ]);
    }

    public static function downloadLogPurple() {
        $message = '';
        $file = '';
        $result = false;

        $user = Spelbordusers::verifyAccess();
        $postUser = post('user');

        if ( empty($user) ) {
            $message = 'unauthenticated user';
        }

        if ( $user = Spelbordusers::verifyAccess() ) {
            if ( $user->party_id !== $postUser['party_id'] ) {
                $message = 'unauthenticated';
            }
            else {
                $logsController = new LogsController();
                return $logsController->getdownload([ $user->party_id ]);
            }
        }

        return Response::json([
                                  'result'  => $result,
                                  'message' => $message,
                              ]);


    }

}
