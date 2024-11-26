<?php

namespace bld\ddosspelbord\classes\api\v1;

use Backend\Classes\Controller;
use bld\ddosspelbord\helpers\hLog;
use Bld\Ddosspelbord\Models\Settings;
use bld\ddosspelbord\models\Target;
use Illuminate\Http\Request;
use Db;

class GameboardController extends Controller {

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function GetData( Request $request ) {

        $id = '1'; // for now, in future interesting when we have multiple gamboards setup

        $startdate = Settings::get('startdate', '1970-01-01 00:00:00');
        $enddate = Settings::get('enddate', '1970-01-01 00:00:00');

        $firsttime = Settings::get('firsttime', '1970-01-01 00:00:00');
        $endtime = Settings::get('endtime', '1970-01-01 00:00:00');

        $startDateTime = $startdate . ' ' . $firsttime;
        $endDateTime = $enddate . ' ' . $endtime;

        // Convert to epoch timestamp, fetching UTC times from db
        $epochStart = $this->getStartDateTime(); strtotime($startDateTime);  // Epoch for start date + time
        $epochEnd = $this->getEndDateTime(); strtotime($endDateTime);      // Epoch for end date + time

        $mActive = Settings::get('measurements_active', '1');

        $updated_at = $this->getLatestUpdatedAt();

        $response = (object)[
            "id"         => 1,
            "start"      => $epochStart,
            "end"        => $epochEnd,
            "activated"  => $mActive,
            "updated_at" => $updated_at
        ];

        try {
            // The Request
            return response()->json(( $response ), 200);
        }
        catch ( \GuzzleHttp\Exception\GuzzleException $e ) {
            return response()->json([ 'error' => "Failed to obtain DDOSTEST data: " . $e->getMessage() ], 500);
        }
        catch (\Exception $e) {
            return response()->json(['error' => get_class($this) . ':  An error occurred: ' . $e->getMessage()], 500);
        }
    }

    public function getDataByID($id, Request $request)
    {
        if (!is_numeric($id) || intval($id) <= 0) {
            return response()->json(['error' => "Please provide an id as a positive number"], 400);
        }

        // Note: always id=1 for this version
        return $this->GetData($request);
    }


    /**
     * @return false|int
     */
    private function getLatestUpdatedAt(){
        $dData = Db::table(function ( $query ) {
            $query->select('updated_at')->from('bld_ddosspelbord_targets')
                ->unionAll(Db::table('bld_ddosspelbord_target_groups')->select('updated_at'))
                ->unionAll(Db::table('bld_ddosspelbord_measurement_api')->select('updated_at'))
                ->unionAll(Db::table('bld_ddosspelbord_measurement_api_data')->select('updated_at'))
                ->unionAll(Db::table('bld_ddosspelbord_nodelists')->select('updated_at'))
                ->unionAll(Db::table('bld_ddosspelbord_target_groups')->select('updated_at'));
        })->orderByDesc('updated_at')->first();
        return strtotime($dData->updated_at);
    }

    /**
     * @return false|int
     */
    private function getStartDateTime() {
        $startdate = Settings::get('startdate', '1970-01-01 00:00:00');
        $firsttime = Settings::get('firsttime', '1970-01-01 00:00:00');
        $startdateOnly = date('Y-m-d', strtotime($startdate));
        $firsttimeOnly = date('H:i:s', strtotime($firsttime));

        // Warning these times are UTC!
        return strtotime($startdateOnly . ' ' . $firsttimeOnly);
    }

    /**
     * @return false|int
     */
    private function getEndDateTime() {
        // Get the full datetime strings from Settings
        $enddate = Settings::get('enddate', '1970-01-01 00:00:00');
        $endtime = Settings::get('endtime', '1970-01-01 00:00:00');
        $enddateOnly = date('Y-m-d', strtotime($enddate));
        $endtimeOnly = date('H:i:s', strtotime($endtime));

        // Warning these times are UTC!
        return strtotime($enddateOnly . ' ' . $endtimeOnly);
    }
}
