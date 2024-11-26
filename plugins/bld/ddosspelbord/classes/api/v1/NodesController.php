<?php

namespace bld\ddosspelbord\classes\api\v1;

use Backend\Classes\Controller;
use bld\ddosspelbord\models\MeasurementNode;
use bld\ddosspelbord\models\NodeList;
use Illuminate\Http\Request;
use Db;
use DateTime;

class NodesController extends Controller {

    public function putNodes( Request $request ) {
        $nodes = $request->getContent();
        $nodes = json_decode($nodes);

        if (!is_array($nodes)) {
            return response()->json([ 'error' => "supplied 'nodelist' not a valid Array" ], 500);
        }

        $totalNodes = MeasurementNode::count();
        $nodesCount = count($nodes);
        $newCount = 0;
        $existCount = 0;

        foreach ( $nodes as $index => $node ) {
            $existingNode = MeasurementNode::where('name', 'like', $node)->first();
            if ( empty($existingNode) ) {
                $nodeModel = new MeasurementNode();
                $nodeModel->name = $node;
                $nodeModel->save();
                $newCount++;
            }
            else {
                $dateTime = new DateTime;
                $existingNode->updated_at = $dateTime->format('Y-m-d H:i:s');
                $existingNode->save();
                $existCount++;
            }
        }

        $msg = "Reveived ". $nodesCount . " Nodes. Created " . $newCount . " new nodes. Updated timestamp of" . " $existCount " . "existing nodes. Total amount of nodes: " . $totalNodes;

        $return = (object)[
            'succes' => $msg
        ];

        try {
            return response()->json(($return ), 200);
        }
        catch (\GuzzleHttp\Exception\GuzzleException $e) {
            return response()->json(['error' => 'Unknown request for the DdosTest API putNodes(): ' . $e->getMessage()], 500);
        } catch (\Exception $e) {
            return response()->json(['error' => get_class($this) . ': : An error occurred: ' . $e->getMessage()], 500);
        }
    }

    public function getAllNodes(Request $request)
    {
        return $this->fetchAndRespond();
    }

    public function getNodesByID($id, Request $request)
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
            $nodes = $this->getNodes($id);

            // Handle no nodes found
            if ($id !== null && $nodes->isEmpty()) {
                return response()->json(['error' => "NodeList with id '$id' does not exist"], 404);
            } else {
                // Convert to array and respond
                // Note: also when empty
                return response()->json($nodes->toArray(), 200);
            }

        } catch (\GuzzleHttp\Exception\GuzzleException $e) {
            return response()->json(['error' => 'Unknown request for fetching nodes in the the DdosTest API: ' . $e->getMessage()], 500);
        } catch (\Exception $e) {
            return response()->json(['error' => get_class($this) . ':  An error occurred: ' . $e->getMessage()], 500);
        }
    }

    public function getNodes($id = null)
    {
        // Build the query and eager load 'nodes' relationship
        $query = Nodelist::whereNull('deleted_at')->select('id', 'name')->with('nodes');

        // Apply the ID filter if provided
        if ($id !== null) {
            $query->where('id', intval($id));
        }

        // Fetch the nodelists with their related 'nodes' through the pivot table
        $nodelists = $query->get();

        $nodelists = $nodelists->map(function ($nodelist) {
            // Extract the 'name' of each node and replace the 'nodes' relation with a simple array of names
            $nodename[] = $nodelist->nodes->map(function ($node) {
                return $node->name;
            })->toArray();
            unset($nodelist->nodes);
            $nodelist->list = $nodename[0];
            // Convert collection to array of names
            return $nodelist;
        });

        // Return the final result as a JSON response
        return $nodelists;
    }



}
