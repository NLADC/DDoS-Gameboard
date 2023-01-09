<?php namespace Bld\Ddosspelbord\Controllers;

use bld\ddosspelbord\helpers\hLog;
use Bld\Ddosspelbord\Models\Transactions;
use Flash;
use Config;
use Session;
use BackendMenu;
use BackendAuth;
use Backend\Classes\Controller;
use Bld\Ddosspelbord\Models\Parties;
use Bld\Ddosspelbord\Models\Logs;
use Bld\Ddosspelbord\Models\Spelbordusers;

class Startpage extends Controller {

    public $implement = [
    ];

    public function __construct() {
        parent::__construct();
        // needed to set menu highlighted
        BackendMenu::setContext('bld.ddosspelbord', 'startpage');
    }

    /**
     * Own index
     */
    public function index() {

        // set logging user (backend here)
        $user = BackendAuth::getUser();
        hLog::setUser($user);

        //$appUrl = url('/');
        //$this->addCss($appUrl.'/plugins/bld/ddosspelbord/assets/css/ddosspelbord.css');

        $this->pageTitle = 'Startpage';
        $this->bodyClass = 'compact-container ';

        $this->vars['release'] = Config::get('bld.ddosspelbord::release.version', '0.0a') . ' - ' . Config::get('bld.ddosspelbord::release.build', 'UNKNOWN');
        $this->vars['cacheReload'] = date('Y-m-d H:i:s');

        $parties = Parties::get();

        $logtotal = $usertotal = 0;
        $logparties = $userparties = [];

        $count = Logs::join('bld_ddosspelbord_users','bld_ddosspelbord_users.id','bld_ddosspelbord_logs.user_id')
            ->where('bld_ddosspelbord_users.party_id',0)
            ->count();
        $logtotal += $count;
        $logparties[] = [
            'name' => NO_PARTY_NAME,
            'count' => $count,
        ];

        $count = Spelbordusers::where('party_id',0)->count();
        $usertotal += $count;

        $last5min = date('Y-m-d H:i:s',strtotime(now()) - (5 * 60));
        $loggedincount = Spelbordusers::where('party_id',0)
            ->where('heartbeat','>=',$last5min)
            ->count();
        $userparties[] = [
            'name' => NO_PARTY_NAME,
            'count' => $count,
            'loggedincount' => $loggedincount,
        ];

        foreach ($parties AS $party) {

            $count = Logs::join('bld_ddosspelbord_users','bld_ddosspelbord_users.id','bld_ddosspelbord_logs.user_id')
                ->where('bld_ddosspelbord_users.party_id',$party->id)
                ->count();
            $logtotal += $count;
            $logparties[] = [
                'name' => $party->name,
                'count' => $count,
            ];

            $count = Spelbordusers::where('party_id',$party->id)->count();
            $usertotal += $count;

            $last5min = date('Y-m-d H:i:s',strtotime(now()) - (5 * 60));
            $loggedincount = Spelbordusers::where('party_id',$party->id)
                ->where('heartbeat','>=',$last5min)
                ->count();
            $userparties[] = [
                'name' => $party->name,
                'count' => $count,
                'loggedincount' => $loggedincount,
            ];

        }
        $this->vars['logparties'] = $logparties;
        $this->vars['logtotal'] = $logtotal;
        $this->vars['userparties'] = $userparties;
        $this->vars['usertotal'] = $usertotal;
        $this->vars['loglines'] = '';

        $feeds = new Feeds();
        $feeds->setLastSessionHash('');

    }

    public function onFeedLogs() {

        // set logging user (backend here)
        $user = BackendAuth::getUser();
        hLog::setUser($user);

        $feeds = new Feeds();
        $lastTransaction = $feeds->getLastSessionHash();
        hLog::logLine("D-startStream; readLogs from hash: $lastTransaction");
        $transactions = [];

        if ($lastTransaction=='') {

            // in volgorde van updates
            $logs = Logs::orderBy('updated_at','ASC')->get();
            foreach ($logs AS $log) {
                $spelborduser = Spelbordusers::find($log->user_id);
                if ($spelborduser) {
                    $party = Parties::find($spelborduser->party_id);
                    $partyName = ($party) ? $party->name : NO_PARTY_NAME;
                    // fill like feed transactions
                    $transactions[] = [
                        'data' => [
                            'log' => $log->log,
                            'timestamp' => $log->timestamp,
                            'user' => [
                                'name' => $spelborduser->name,
                                'party' => [
                                    'id' => 0,
                                    'name' => $partyName,
                                ],
                            ],
                        ],
                    ];
                }
            }
            $lastTransaction = $feeds->getLastTransactionHash();

        } else {
            //hLog::logLine("D-onFeedLogs; watch transactions after (updated) lasthash=$lastTransaction");

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
                        if ($transaction->type == 'log') {
                            $newtransactions[] = $transaction;
                        }
                        // save last transaction hash
                        $lastTransaction = $transaction->hash;
                    }

                }
            }

        }
        if (count($transactions) > 0) {
            //hLog::logLine("newTransaction=".print_r($newtransactions[count($newtransactions) - 1],true) );
            $feeds->setLastSessionHash($lastTransaction);
            hLog::logLine("D-onFeedLogs; user=$user->email; new transaction count=".count($transactions));
        }
        return [
            'status' => true,
            'logs' => $transactions,
        ];
    }

    public function onCountdown() {

        $message = post('message', '');
        $type = post('type', '');

        $command = [
            'command' => 'Countdown',
            'type' => $type,
            'message' => $message,
        ];
        (new Feeds())->createTransaction(TRANSACTION_TYPE_SYSTEM, $command);

    }

    public function onMessage() {

        $message = post('message', '');
        $type = post('type', '');

        if ($message) {
            $command = [
                'command' => 'sentNotify',
                'type' => $type,
                'message' => $message,
            ];
            (new Feeds())->createTransaction(TRANSACTION_TYPE_SYSTEM, $command);

        } else {
            Flash::warning('No message specified');
        }


    }


}
