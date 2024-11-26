<?php

namespace bld\ddosspelbord\classes\api\v1;

use Backend\Classes\Controller;
use bld\ddosspelbord\components\ddosspelbord_log;
use Bld\Ddosspelbord\Models\Parties;
use bld\ddosspelbord\models\Target;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Db;

class TargetsController extends Controller {

    public function GetTargets( Request $request ) {

        $types = Target::whereNull('deleted_at')
            ->select('target', 'ipv', 'enabled', 'measurement_type_id', 'party_id')
            ->with('parties')
            ->get();

        if ($types->isEmpty()) {
            return response()->json([], 200);
        }

        // Manually load the hasOne 'parties' relationship for each Target
        $types->each(function ($type) {
            $type->load('parties');
        });


        // Convert to array and append the measurementtype name
        $response = $types->map(function ($type) {
            $typeArray = $type->toArray();
            // Remove the full measurementtype and keep only the name

            // Get Party
            $typeArray['party'] = $type->parties ? $type->parties->name : null;
            unset($typeArray['party_id']);
            unset($typeArray['parties']);

            return $typeArray;
        });
        try {
            // The Request
            return response()->json($response, 200);
        }
        catch ( \GuzzleHttp\Exception\GuzzleException $e ) {
            return response()->json([ 'error' => "Failed to fetch targets: " . $e->getMessage() ], 500);
        }
        catch ( \Exception $e ) {
            return response()->json([ 'error' => get_class($this) . ': : An error occurred: ' . $e->getMessage() ], 500);
        }

    }

    public function PostStatusTarget( $targetId, $state, Request $request ) {
        if ( empty($targetId) ) {
            return response()->json([ 'error' => 'No TargetID has been specified in the API call' ], 500);
        }
        elseif ( !isset($state) || intval($state) > 1 || intval($state) < 0 ) {
            return response()->json([ 'error' => 'No state has or state has been wrongly been specified in the API call' ], 500);
        }
        else {
            $target = Target::find($targetId);

            if ( empty($target) ) {
                return response()->json([ 'error' => 'Could not find target with id: ' . $targetId ], 500);
            }

            $status = ( intval($state) == 1 ) ? 'up' : 'down';
            $LogLine = "Target: " . $target->target . " status is: " . $status;

            // Get the current time in the application's configured timezone
            $timestamp = Carbon::now()->setTimezone(config('cms.backendTimezone'))->format('H:i:s');

            // Prepare the data that would have been sent via POST
            $postData = [
                'log' => $LogLine,
                'timestamp' => $timestamp, // Use current timestamp or set your desired time
            ];

            try {
                // Call the submitLog method directly
                $result = ddosspelbord_log::submitLog($postData); // Assuming this method accepts an array
                $log = json_decode($result->original['log']);

                $return = (object)[
                    'succes' => $log->log
                ];

                return response()->json($return, 200);
            } catch (\Exception $e) {
                return response()->json(['error' => 'Failed to submit log: ' . $e->getMessage()], 500);
            }
        }

    }
}
