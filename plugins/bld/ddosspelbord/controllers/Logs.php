<?php namespace Bld\Ddosspelbord\Controllers;

use Db;
use App\Action;
use Bld\Ddosspelbord\Models\Roles;
use ApplicationException;
use Redirect;
use Session;

use Response;
use stdClass;
use DateTime;
use Backend\Classes\Controller;
use BackendMenu;
use Bld\Ddosspelbord\Models\Parties;
use Bld\Ddosspelbord\Models\Spelbordusers;
use bld\ddosspelbord\helpers\hLog;
use \Bld\Ddosspelbord\Models\Actions;
use Backend\FormWidgets\FileUpload;
use Winter\Storm\Filesystem\Zip;
use System\Models\File as File;
use Winter\Storm\Assetic\Util\FilesystemUtils;

class Logs extends Controller
{
    public $implement = [
        'Backend\Behaviors\ListController',
        'Backend\Behaviors\FormController'
    ];

    public $listConfig = 'config_list.yaml';
    public $formConfig = 'config_form.yaml';

    public function __construct()
    {
        parent::__construct();
        // neede to set menu highlighted
        BackendMenu::setContext('bld.ddosspelbord', 'Logs');
    }

    public function listExtendQueryBefore($query)
    {
        hlog::logLine("D-listExtendQueryBefore; init empty array");
        Session::put(SESSION_LOGS_FILTER_PARTIES, serialize([]));
        Session::put(SESSION_LOGS_FILTER_USERS, serialize([]));
    }

    public function listExtendQuery($query)
    {
        //$sql = $query->toSql();
        //hlog::logLine("D-listExtendQuery; sql=$sql");
    }

    public function onDownload()
    {
        // redirect for forcing browser into download
        return Redirect::to('/backend/bld/ddosspelbord/logs/download/');
    }

    public function download()
    {
        // download file
        hLog::logLine("D-Download...");

        // Setting temp CSV path
        $csvfilename = 'LoggingExportOn:' . date('YmdHis') . '.csv';
        $tmpcsv = temp_path($csvfilename);

        // Temp directory for the attachments
        $tmpdirname = 'Attachments_' . date('YmdHis');
        $tmpdir = temp_path() . "/". $tmpdirname;
        mkdir($tmpdir);
        // Already creating subdirectory where the attachments will be nested in the end zip
        mkdir($tmpdir . "/attachments/");

        $party_ids = Session::get(SESSION_LOGS_FILTER_PARTIES);
        if ($party_ids) $party_ids = unserialize($party_ids);
        $logs = \Bld\Ddosspelbord\Models\Logs::orderBy('timestamp');
        if (is_array($party_ids) && count($party_ids) > 0) {
            hLog::logLine("D-Use parties filter");
            $logs = $logs->join('bld_ddosspelbord_users', 'bld_ddosspelbord_users.id', '=', 'bld_ddosspelbord_logs.user_id')
                ->whereIn('bld_ddosspelbord_users.party_id', $party_ids);
        } else {
            hLog::logLine("D-No party filter");
        }

        $user_ids = Session::get(SESSION_LOGS_FILTER_USERS);
        if ($user_ids) $user_ids = unserialize($user_ids);
        if (is_array($user_ids) && count($user_ids) > 0) {
            hLog::logLine("D-Use users filter");
            $logs = $logs->whereIn('user_id', $user_ids);
        } else {
            hLog::logLine("D-No user filter");
        }

        $logs = $logs->get();
        $parties = $users = [];
        $loglines = 'timestamp;party;user;role;log;attachments' . "\n";
        foreach ($logs as $log) {
            if (!isset($users[$log->user_id])) {
                $user = Spelbordusers::find($log->user_id);
                $users[$user->id] = $user;
            }
            $user = $users[$log->user_id];

            if (!isset($parties[$user->party_id])) {
                $party = Parties::find($user->party_id);
                if ($party) {
                    $parties[$party->id] = $party;
                } else {
                    // observer log?!
                    $parties[0] = (object)[
                        'name' => NO_PARTY_NAME,
                    ];
                }
            }
            $party = $parties[$user->party_id];

            $role = Roles::find($user->role_id);
            $f = [];

            // Getting all attachments from a log
            foreach ($log->attachments as $attachment) {
                    $orgattachmentname = $attachment->file_name;
                    $filepath = $attachment->getLocalPath();
                    $file = new stdClass();
                    if (file_exists($filepath)) {
                        if ($username = Spelbordusers::find($log->user_id)->name) {
                            $file->filename = preg_replace('/\s+/', '_', $attachment->created_at) . "_" .  $username . ":" .  $attachment->file_name;
                            $file->filename = preg_replace('/\:/', '-', $file->filename);
                        }
                        else {
                            $file->filename = preg_replace('/\s+/', '_', $attachment->created_at) . ":" .  $attachment->file_name;
                            $file->filename = preg_replace('/\:/', '-', $file->filename);
                        }
                    }
                    // The file must exists or we will get errors in stead of an icomplete download. TODO: create a nice error message when a file is not found.
                    else {
                        continue;
                    }

                // This part is all about creating attachment files for the downloadable zip
                $tmpattachment = temp_path() . "/". $tmpdirname . "/attachments/" . $file->filename;
                file_put_contents($tmpattachment, file_get_contents($filepath));

                // Removing the tmp
                unset($tmpattachment);

                // This is a temporary variable wich collect filenames to add in the CSV
                $f[] = $orgattachmentname;
            }
            // These will land in the CSV
            $attachmentnames =  implode(", ", $f);
            unset($f);

            // Building the CSV
            $loglines .= "$log->timestamp;";
            $loglines .= "$party->name;";
            $loglines .= "$user->name;";
            $loglines .= ($role) ? "$role->name;" : '';
            $log = str_replace("\n", '[CR]', $log->log);
            $log = addslashes($log);
            $loglines .= "$log;";
            $loglines .= "$attachmentnames\n";
        }

        // Creating the actual CSV with the logging in it
        file_put_contents($tmpcsv, $loglines);

        // Generating a temp ZIP filename
        $zipfilename = 'download-' . date('YmdHis') . '.zip';

        // Setting the path in memory where the zip is going to be created
        $tmpzip = temp_path($zipfilename);

        // Creating the zip that will be downloaded
        Zip::make($tmpzip, [$tmpcsv, $tmpdir]);

        // Cleaning temporary attachments, not the zip
        FilesystemUtils::removeDirectory($tmpdir);

        // Removing the duplicate CSV it is already in the zip
        if (file_exists($tmpcsv)) {
            unlink($tmpcsv);
        }

        // Starting the download
        return Response::download($tmpzip, $zipfilename);
    }

    public function onResetTimestampsToday()
    {
        $logs = \Bld\Ddosspelbord\Models\Logs::orderBy('timestamp');
        $logs = $logs->get();
        for ($i = 0; $i < count($logs); ++$i) {
            $oldtimestamp = $logs[$i]->timestamp;

            $d = strtotime("now");
            $now = date('Y-m-d H:i:s', $d);

            //extract parameters to set todays time
            $pattern = "/([0-9]{4}-[0-9]{2}-[0-9]{2})/i";
            preg_match($pattern, $now, $matches);
            $today = str_replace('-', ',', $matches[0]);
            $todayinpar = explode(',', $today);

            //extract parameters of original time
            $pattern = "/[0-9]{2}:[0-9]{2}:[0-9]{2}/i";
            preg_match($pattern, $oldtimestamp, $matches);
            $olttime = str_replace(':', ',', $matches[0]);
            $olttimeinpar = explode(',', $olttime);

            if (sizeof($todayinpar) == 3 && sizeof($olttimeinpar) == 3) {
                $date = new DateTime();
                $date->setDate($todayinpar[0], $todayinpar[1], $todayinpar[2]);
                $date->setTime($olttimeinpar[0], $olttimeinpar[1], $olttimeinpar[2], null);
                $newtimestamp = $date->format('Y-m-d H:i:s');

                $logs[$i]->timestamp = $newtimestamp;
                $logs[$i]->save();
            }
        }
    }
}
