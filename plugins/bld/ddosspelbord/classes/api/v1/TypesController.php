<?php

namespace bld\ddosspelbord\classes\api\v1;

use Backend\Classes\Controller;
use bld\ddosspelbord\helpers\hLog;
use bld\ddosspelbord\models\MeasurementType;
use bld\ddosspelbord\models\Target;
use Illuminate\Http\Request;
use Db;

class TypesController extends Controller
{
    public function GetMeasurementTypes( Request $request)
    {
        return $this->fetchAndRespond();
    }

    public function GetMeasurementTypesByID($id, Request $request)
    {
        if (!is_numeric($id) || intval($id) <= 0) {
            return response()->json(['error' => "Please provide an id as a positive number"], 400);
        }

        return $this->fetchAndRespond($id);
    }

    /**
     * Private method to handle fetching nodes and responding.
     *
     * @param int|null $id
     * @return \Illuminate\Http\JsonResponse
     */
    private function fetchAndRespond($id = null)
    {
        try {
            $types = ($id) ? MeasurementType::where('id',$id) : MeasurementType::where('id','>',0);
            $types = $types->select('id', 'name', 'nodelist_id')->get();
            // Handle no nodes found
            if ($id !== null && $types->isEmpty()) {
                return response()->json(['error' => "MeasurementType with id '$id' does not exist"], 404);
            } else {
                // Convert to array and respond
                // Note: also when empty
                return response()->json($types->toArray(), 200);
            }

        } catch (\GuzzleHttp\Exception\GuzzleException $e) {
            return response()->json(['error' => 'Unknown request for fetching measurementTypes: ' . $e->getMessage()], 500);
        } catch (\Exception $e) {
            return response()->json(['error' => get_class($this) . ':  An error occurred: ' . $e->getMessage()], 500);
        }
    }


}
