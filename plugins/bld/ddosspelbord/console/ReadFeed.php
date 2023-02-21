<?php namespace Bld\Ddosspelbord\Console;

use Auth;
use bld\ddosspelbord\helpers\hLog;
use Bld\Ddosspelbord\Models\Spelbordusers;
use Bld\Ddosspelbord\Models\Transactions;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Symfony\Component\Console\Input\InputOption;

/**
 * TESTTOOL
 *
 * Tool om performance testen mee uit te voeren. Het is een tool, dus onderstaande is vrij recht-toe-aan code.
 * In een relatief korte tijd moest een omgeving met minimaal 90 ingelogde gebruikers worden gesimuleerd.
 *
 * Het basisprincipe is dat met "readfeed" via curl een client wordt gestimuleerd wat een normale frontend in
 * vue ook doet; het wachten op transacties via een stream. En via deze stream transacties door krijgen die
 * verwerkt worden.
 *
 * Met deze tool kunnen (generateLogins) ook een # aantal testusers worden aangemaakt (met een standaard wachtwoord,
 * DUS puur voor een TEST server omgeving).
 *
 * Met runFeedProcesses worden # processen tegelijk gestart die ieder een readfeed uitvoeren als frontend client.
 * De ontvangen transacties worden samenvattend op het scherm (stdout) getoond.
 *
 * Met runLogoutProcesses worden # processen uitgelogd, wat moet gebeuren wanneer een process crashed of de tool met
 * running processen wordt gestopt.
 *
 * Gs.
 *
 */

class ReadFeed extends Command
{
    /**
     * @var string The console command name.
     */
    protected $name = 'ddosgameboard:readfeed';

    /**
     * @var string The console command description.
     */
    protected $description = 'Immitate client readFeeds';

    /**
     * Execute the console command.
     * @return void
     */
    public function handle() {

        hLog::setEcho(true);

        $mode = $this->option('mode', 'readfeed');
        $username = $this->option('username', '');
        $number = $this->option('count', 10);
        $password = $this->option('password', '');
        //$host = 'https://exercises.tst.nomoreddos.org/';
        //$host = 'http://beheer.bioffice01.nl:92/';
        $host = $this->option('host');
        if ($host=='') {
            $host = env('APP_URL', '').'/';
            hlog::logLine("D-ReadFeed; host not specified; take APP_URL from env file: $host");
        }


        $showhelp = false;

        if ($username=='' || $host=='') {

            hlog::logLine("E-ReadFeed; incorrect parameters");
            $showhelp = true;

        } else {

            hlog::logLine("D-ReadFeed; mode=$mode, number=$number, username=$username, host=$host");

            if ($mode == 'logout') {

                try {
                    $userauth = Auth::authenticate([
                        'login' => $username,
                        'password' => $password,
                    ]);

                    if ($userauth) {
                        Spelbordusers::doLogout($userauth->id);
                    }

                    hlog::logLine("D-Logout of '$username'");

                } catch (\Exception $err) {
                    hlog::logLine("E-ddosspelbord_login.login; error login: ".$err->getMessage());
                    $user = '';
                }

            } elseif ($mode == 'generateLogins') {

                $tag = $username;
                for ($i=0;$i<$number;$i++) {

                    $login = 'testuser_'.$tag.$i;
                    if (Spelbordusers::where('name',$login)->doesntExist()) {
                        $spelborduser = new Spelbordusers();
                        $spelborduser->name = $login;
                        $spelborduser->password = 'Gerald13';
                        $spelborduser->email = $spelborduser->name . '@nomoreddos.org';
                        $spelborduser->party_id = 6;
                        $spelborduser->role_id = 2;
                        $spelborduser->save();
                        hlog::logLine("D-Spelbord user '$login' with password '$spelborduser->password' created");
                    } else {
                        hlog::logLine("D-Spelbord user '$login' already exists");
                    }
                }

            } elseif ($mode == 'removeLogins') {

                $tag = $username;
                for ($i=0;$i<$number;$i++) {

                    $login = 'testuser_'.$tag.$i;
                    $spelborduser = Spelbordusers::where('name',$login)->first();
                    if ($spelborduser) {
                        $spelborduser->delete();
                        hlog::logLine("D-Spelbord user '$login' removed");
                    } else {
                        hlog::logLine("D-Spelbord user '$login' not exists");
                    }
                }

            } elseif ($mode == 'runFeedProcesses') {

                $cmdtmp = 'php artisan ddosspelbord:readFeed -m readfeed -u [user] -p Gerald13 --host='.$host;
                $this->runProcesses($cmdtmp,$username,$number);

            } elseif ($mode == 'runLogoutProcesses') {

                $cmdtmp = 'php artisan ddosspelbord:readFeed -m readfeed -u [user] -p Gerald13 -m logout --host='.$host;
                $this->runProcesses($cmdtmp,$username,$number,false);

            } elseif ($mode == 'readfeed' ) {

                if ($username && $password) {

                    try {

                        $channel = curl_init();

                        // INIT with cookie
                        $cookiefile = tempnam(sys_get_temp_dir(), "SPELBORD");
                        hlog::logLine("D-Cookiefile=$cookiefile");
                        $options = array(
                            CURLOPT_RETURNTRANSFER => true,   // return web page
                            CURLOPT_HEADER => false,  // don't return headers
                            CURLOPT_FOLLOWLOCATION => true,   // follow redirects
                            CURLOPT_MAXREDIRS => 10,     // stop after 10 redirects
                            CURLOPT_USERAGENT => 'TEST CLIENT',
                            CURLOPT_AUTOREFERER => true,   // set referrer on redirect
                            CURLOPT_CONNECTTIMEOUT => 20,    // time-out on connect
                            CURLOPT_TIMEOUT => 20,    // time-out on response
                            CURLOPT_COOKIESESSION => true,
                            CURLOPT_COOKIEJAR => $cookiefile,
                            CURLOPT_COOKIEFILE => $cookiefile,
                            CURLOPT_POST => true,
                        );
                        curl_setopt_array($channel, $options);

                        // LOGIN
                        $post = [
                            'email' => $username,
                            'password' => $password,
                        ];
                        //$post = "login=$username&password=$password";
                        $url = $host . 'api/user/login';
                        curl_setopt($channel, CURLOPT_POSTFIELDS, $post);
                        curl_setopt($channel, CURLOPT_URL, $url);
                        hlog::logLine("D-Call url=$url ");
                        $result = curl_exec($channel);

                        if ($json = json_decode($result)) {

                            hlog::logLine("Result json=" . print_r($json, true));
                            sleep(1);

                            if ($json->result) {

                                $validrun = true;

                                curl_setopt($channel, CURLOPT_WRITEFUNCTION, function ($curl, $data) use ($username,&$validrun) {
                                    $result = trim(substr($data, 6));
                                    if ($result) {
                                        if ($json = json_decode($result)) {
                                            if (isset($json->data)) {
                                                $transaction = $json->data;
                                                if (isset($transaction->user)) {
                                                    $user = $transaction->user;
                                                    hlog::logLine("D-Spelbord user '$username'; get transaction type=$json->type, hash=$json->hash, from=$user->name");
                                                } else {
                                                    hlog::logLine("D-Spelbord user '$username'; json data=" . print_r($json, true));
                                                }
                                            } else {
                                                hlog::logLine("D-Spelbord user '$username'; json data=" . print_r($json, true));
                                            }
                                        } else {
                                            $validrun = false;
                                            hlog::logLine("D-Spelbord user '$username'; No json data!?; result(0..)=".substr(print_r($data,true),0,120));
                                        }
                                    }
                                    return strlen($data);
                                });

                                hlog::logLine("D-Waiting for output...");

                                while ($validrun) {

                                    $hash = (new Transactions())->getLastTransactionHash();
                                    $url = $host . 'api/feed/' . $hash;
                                    curl_setopt($channel, CURLOPT_URL, $url);
                                    $result = curl_exec($channel);
                                }

                            } else {
                                $this->error("E-readfeed; no login: ");
                            }

                        } else {
                            hlog::logLine("D-Spelbord login; got no json data!?; result(0..)=".substr(print_r($result,true),0,120));
                        }

                        $this->error("D-readfeed; stop running");

                    } catch (\Exception $err) {
                        $this->error("E-readfeed; error: " . $err->getMessage());
                    }



                } else {
                    $this->error('E-No username and/or password given');
                    $showhelp = true;
                }

            } else {
                $this->error('E-Unknown mode');
                $showhelp = true;
            }

        }

        if ($showhelp) {
            Artisan::call('ddosspelbord:readfeed -h');
            $this->info(Artisan::output());
            //hlog::logLine("D-Readfeed usage:\n-m [readfeed|logout|generateLogins|runFeedProcesses:runLogoutProcesses]*\n-u [username]*\n-p [password]\n-c [number]*");
        }

    }

    function runProcesses($cmdtmp,$tag,$number,$sleepafter10=true) {

        $stdout = fopen('php://stdout', 'w');
        $descriptorspec = array(
            0 => array("pipe", "r"),  // stdin is a pipe that the child will read from
            1 => $stdout,  // stdin is a pipe that the child will read from
            //1 => array("pipe", "w"),  // stdout is a pipe that the child will write to
            2 => array("file", "error-output.txt", "a") // stderr is a file to write to
        );

        $wrkdir = base_path();
        hlog::logLine("D-Working dir: $wrkdir");

        $processes = [];
        for ($i=0;$i<$number;$i++) {

            $pipes = [];
            $cmd = str_replace('[user]','testuser_'.$tag.$i.'@nomoreddos.org',$cmdtmp);
            hlog::logLine("D-process[$i]; start process with command: '$cmd' ");
            $process = proc_open($cmd,$descriptorspec,$pipes,$wrkdir);
            $processes[] = [
                'process' => $process,
                'pipes' => $pipes,
            ];

            if ($sleepafter10 && ($i % 10 == 0)) {
                hlog::logLine("D-Number of started processes is $i; give system some seconds air...");
                sleep(3);
            }

        }
        $proccnt = count($processes);

        hlog::logLine("D-Let $proccnt processes start...");

        $procrunning = $showmin = true;
        $procesclosed = [];

        while ($procrunning) {

            //hlog::logLine("D-Poll for process output");

            foreach ($processes AS $key => $process) {

                $closed = false;
                if (!in_array($key,$procesclosed)) {

                    //$stdin = $process['pipes'][0];
                    //$stdout = $process['pipes'][1];

                    /*
                    $buffer = '';
                    while (!feof($stdout)) {
                        $out = trim(fgets($stdout));
                        if ($out) $buffer .= $out . "\n";
                    }
                    if ($buffer) {
                        hlog::logLine("****\nD-process[$key]; stdout: \n$buffer\n**** ");
                    }
                    */

                    // check if still running
                    $sts = proc_get_status($process['process']);
                    if (!$sts['running']) {
                        hlog::logLine("D-process[$key]; not running anymore, close process");
                        //fclose($stdin);
                        //fclose($stdout);
                        //$ret = proc_close($process['process']);
                        hlog::logLine("process[$key]; exit return: ".$sts['exitcode']);
                        $procesclosed[] = $key;
                        $closed = true;
                    }

                }

            }

            if (count($procesclosed) == count($processes)) {
                $procrunning = false;
                hlog::logLine("D-All running processes stopped ");
            } else {
                $min = date('i');
                if ($closed || $min % 2 == 0) {
                    if ($showmin) {
                        hLog::logLine("D-Started $proccnt processes; closed processes count=".count($procesclosed).", closed keys: ".implode(',',$procesclosed));
                        $showmin = false;
                    }
                } else {
                    $showmin = true;
                }
                sleep(3);
            }

        }

    }



    /**
     * Get the console command arguments.
     * @return array
     */
    protected function getArguments()
    {
        return [];
    }

    /**
     * Get the console command options.
     * @return array
     */
    protected function getOptions()
    {
        return [
            ['username', 'u', InputOption::VALUE_OPTIONAL, 'username', ''],
            ['password', 'p', InputOption::VALUE_OPTIONAL, 'password', ''],
            ['mode', 'm', InputOption::VALUE_OPTIONAL, 'mode [readfeed|logout|generateLogins|removeLogins|runFeedProcesses:runLogoutProcesses]', ''],
            ['count', 'c', InputOption::VALUE_OPTIONAL, 'count', '10'],
            ['host', '', InputOption::VALUE_OPTIONAL, 'host', ''],
        ];
    }
}
