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

namespace Bld\Ddosspelbord\Controllers;

use Auth;
use Session;
use Backend\Classes\Controller;
use bld\ddosspelbord\helpers\hLog;
use Bld\Ddosspelbord\Models\Transactions;
use Bld\Ddosspelbord\Models\Spelbordusers;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class Feeds extends Controller {

    private $_user = '';

    /**
     * Handle the incoming request.
     *
     * note: not used anay
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke(Request $request) {
        return $this->startStream($request->hash);
    }

    /**
     * Check if rights for access transaction
     *
     * @param $transaction
     * @return null
     */

    public function authFilter($transaction) {

        $result = null;

        if (Spelbordusers::verifyAccess(false)) {

            // only when logged in

            if ($transaction->type == 'action' || $transaction->type == 'actionsilent') {

                $user = Spelbordusers::getOnAuth();
                if($user) {

                    // Only RED and PURPLE can see name of action field
                    $userRole = $user->role;
                    $action = $transaction->data;
                    // remove
                    if(($userRole == 'blue' || $userRole == 'guest') && array_key_exists('name', $action))
                        unset($action['name']);

                    $result = $transaction;

                }

            } elseif ($transaction->type == 'log') {

                // GUEST cannot see any logs
                // Only RED can see RED logs
                // Only BLUE can see BLUE logs
                // Only PURPLE can see RED and BLUE logs

                $user = Spelbordusers::getOnAuth();
                if($user) {
                    $userRole = $user->role;
                    $logRole = $transaction->data['user']['role'];
                    $logPartyId = $transaction->data['user']['partyId'];
                    // only from own party
                    if (($user->partyId == $logPartyId) && ($userRole == 'purple' || $userRole == $logRole)) {
                        hLog::logLine("D-Process log transaction; for current user(role=$userRole, partyId=$user->partyId)");
                        $result = $transaction;
                    } else {
                        hLog::logLine("D-Ignore log transaction; log (role=$logRole, partyId=$logPartyId)) not for current user(role=$userRole and/or partyId=$user->partyId)");
                    }
                }

            } elseif ($transaction->type == 'attack') {

                // GUEST cannot see any attacks
                // Only RED can see RED attacks
                // Only BLUE can see BLUE attacks
                // Only PURPLE can see RED and BLUE attacks

                $user = Spelbordusers::getOnAuth();
                if($user) {
                    $userRole = $user->role;
                    $logRole = $transaction->data['user']['role'];
                    $logPartyId = $transaction->data['user']['partyId'];
                    // only from own party
                    if (($user->partyId == $logPartyId) && ($userRole == 'purple' || $userRole == $logRole)) {
                        hLog::logLine("D-Process log transaction; for current user(role=$userRole, partyId=$user->partyId)");
                        $result = $transaction;
                    } else {
                        hLog::logLine("D-Ignore log transaction; log (role=$logRole, partyId=$logPartyId)) not for current user(role=$userRole and/or partyId=$user->partyId)");
                    }
                }

            } elseif ($transaction->type == 'system') {

                // system always
                $result = $transaction;

            }

        }

        return $result;
    }

    public function createTransaction($type, $data) {

        $transaction = new Transactions;
        $transaction->hash = bin2hex(random_bytes(16));
        $transaction->created_at = Carbon::now()->toDateTimeString();
        $transaction->type = $type;
        $transaction->data = serialize($data);
        $transaction->save();

        hLog::logLine("D-CreateTransaction with hash=$transaction->hash");
        //$this->publishTransaction($transaction);
    }

    public function getNewTransactions(&$lastTransaction) {

        $newtransactions = [];

        // get last one we know
        $transaction = Transactions::select('id')->where('hash', $lastTransaction)->first();

        if ($transaction) {

            // Note: fetch with NO CACHING
            $transactions = Transactions::where('id', '>', $transaction->id)->orderBy('id')->disableDuplicateCache()->get();

            if ($count = count($transactions)) {

                hLog::logLine("D-startStream; found $count new transaction(s) ");

                foreach($transactions as $transaction) {

                    // get transaction data object
                    $transaction->data = unserialize($transaction->data);
                    // check (always) if autorized for this transaction
                    if ($transauth = $this->authFilter($transaction)) {
                        $newtransactions[] = $transauth;
                    }
                    // save last transaction hash
                    $lastTransaction = $transaction->hash;
                }

            }
        }

        return $newtransactions;
    }

    public function startStream($hash = null) {

        $start = time();
        $maxExecution = ini_get('max_execution_time');

        $response = new StreamedResponse(function() use ($hash, $start, $maxExecution) {

            $lastTransaction = $hash;

            // Start loop with polling (last) transactions -> hash = last has transaction

            hLog::logLine("D-startStream; watch transactions after (updated) lasthash=$lastTransaction");

            while (true) {

                $hash = $lastTransaction;
                $newtransactions = $this->getNewTransactions($lastTransaction);

                if (count($newtransactions) > 0) {

                    foreach ($newtransactions AS $transauth) {
                        //hLog::logLine("D-Push data to stream; hash=$transauth->hash; data=" . print_r($transauth->data,true) );
                        hLog::logLine("D-New transaction; push data to stream; hash=$transauth->hash");
                        echo "data: ".json_encode($transauth)."\n\n";
                        // force direct output
                        echo str_repeat(' ',4096)."\n";
                    }

                }

                // check if changed save hash in user settings
                if ($lastTransaction != $hash) $this->setLastsettingHash($lastTransaction);

                // Flush everything and sleep
                ob_flush();
                flush();
                sleep(1);

                if (time() > $start + $maxExecution) {
                    hLog::logLine("D-startStream; stop loop (max execution reached)");
                    break;
                } else {
                    //hLog::logLine("D-startStream.polling");
                }
            }
        });

        return $response;
    }

    public function getLastTransactionHash() {
        $data = Transactions::select('hash')->orderBy('id','DESC')->first();
        return $data ? $data->hash : '';
    }


    /**
     * Save last hash
     *
     * In session is not working because there is a session timeout and the stream is running for a couple of hours
     * Put into database settings data of spelborduser
     * Static function/data to cache calling within same stream
     * note: only working when logged in
     *
     */

    private static $_settings = '';

    public static function getLastsettingHash() {

        $hash = '';
        $user = Spelbordusers::getOnAuth();
        if ($user) {
            if (empty(self::$_settings)) {
                self::$_settings = unserialize($user->settings);
            }
            if (self::$_settings) {
                if (isset(self::$_settings['lasthash'])) {
                    $hash = self::$_settings['lasthash'];
                }
            }
        }
        return $hash;
    }

    public static function setLastsettingHash($hash) {

        $user = Spelbordusers::getOnAuth();
        if ($user) {
            if (!isset(self::$_settings['lasthash'])) {
                self::$_settings = [
                    'lasthash' => $hash,
                ];
            } else {
                self::$_settings['lasthash'] = $hash;
            }
            Spelbordusers::where('id',$user->id)->update([
                'updated_at' => date('Y-m-d H:i:s'),
                'settings' => serialize(self::$_settings),
            ]);
            hlog::logLine("D-setLastsettingHash; hash=$hash");
        }
    }

    public static function resetLastsettingHash() {

        $user = Spelbordusers::getOnAuth();
        if ($user) {
            Spelbordusers::where('id',$user->id)->update([
                'updated_at' => date('Y-m-d H:i:s'),
                'settings' => '',
            ]);
            hlog::logLine("D-resetLastsettingHash");
        }
    }

    // OBSOLUTE - not working with streaming

    public function getLastSessionHash() {
        $hash = Session::get(SESSION_LAST_HASH,'');
        return $hash;
    }

    public function setLastSessionHash($hash) {
        hlog::logLine("D-setLastSessionHash; hash=$hash");
        Session::put(SESSION_LAST_HASH,$hash);
    }


}
