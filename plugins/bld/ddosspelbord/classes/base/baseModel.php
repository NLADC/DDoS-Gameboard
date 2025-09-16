<?php

namespace bld\ddosspelbord\classes\base;

use bld\ddosspelbord\classes\helpers\ddosspelbordUsers;
use Log;
use Backend\Classes\BackendController;
use Db;
use Illuminate\Support\Facades\Request;
use Lang;
use Model;
use Session;
use Winter\Storm\Exception\ValidationException;
use function PHPUnit\Framework\throwException;
use bld\ddosspelbord\helpers\hLog;

class baseModel extends Model {

    private $partySpecific = [
        'BackendUserPivot',
        'Action',
        'Attack',
        'Spelbordusers',
        'Target',
    ];

    private $viaUserPartySpecific = [
        'Logs',
    ];


    public function __construct( array $attributes = [] ) {
        parent::__construct($attributes);
    }


    public function beforeCreate() {
        /**
         * Check if it's an Party specific model and set the party id to it
         */
        if ( $this->checkPartySpecific() ) {
            if ( ddosspelbordUsers::filterOnParty() ) {
                $this->party_id = ddosspelbordUsers::getBackendPartyId();
            }
        }
    }



    public function getInputId() {
        $Paramarray = BackendController::$params; // inspired by main controller of Winter
        return ( is_array($Paramarray) && count($Paramarray) > 0 ) ? current($Paramarray) : false;
    }

    public function HasParamInRequest( $needle ) {
        return ( strpos(Request::path(), $needle) !== false );
    }


    /**
     * @return bool
     */
    public function checkPartySpecific() {
        return in_array($this->getChildClassName(), $this->partySpecific);
    }

    /**
     * @return string
     */
    private function getChildClassName() {
        return ( new \ReflectionClass($this) )->getShortName();
    }

    /**
     * @param $fields
     * @return bool
     */
    public function checkIfAnyFieldsChanged( $fields ) {
        foreach ( $fields as $field ) {
            if ( $this->isDirty($field) ) {
                return true;
            }
        }
        return false;
    }

    /**
     * @description Useful when converting certain models from serialize type to the Json type, when there is existing serialised data
     * @param $table
     * @param $column
     * @return void
     */
    public static function serializeToJson($table, $column) {
        hLog::logLine("D-baseModel.serializeToJson() Processing table: $table, column: $column");

        $rows = DB::table($table)->select('id', $column)->whereNotNull($column)->get();
        foreach ( $rows as $row ) {
            $id = $row->id;
            $serializedData = $row->$column;

            if ( is_null($serializedData) || $serializedData === '' ) {
                hLog::logLine("D-baseModel.serializeToJson() Skipping row ID: $id (NULL or empty value)");
                continue;
            }

            try {
                // Clean and sanitize the serialized data
                $unserializedData = baseModel::unserializeData($serializedData);

                if ( $unserializedData !== false || $serializedData === serialize(false) ) {
                    $jsonEncodedData = json_encode($unserializedData);
                    DB::table($table)->where('id', $id)->update([ $column => $jsonEncodedData ]);
                    hLog::logLine("D-baseModel.serializeToJson() Updated row ID: $id");
                }
                else {
                    $errorInfo = error_get_last();
                    hLog::logLine("D-baseModel.serializeToJson(): Failed to unserialize data for row ID: $id. Data: " . $serializedData . ". Error: " . print_r($errorInfo, true));
                }
            }
            catch ( \Exception $e ) {
                hLog::logLine("D-baseModel.serializeToJson(): Error processing row ID: $id - " . $e->getMessage());
            }
        }
    }

    /**
     * @description This unserializedata function takes many things into account like slashes, sanitizing etc..
     *
     * @param $serializedData
     * @return mixed
     */
    public static function unserializeData($serializedData)
    {
        hLog::logLine("D-baseModel.serializeToJson(): serializeddata = $serializedData");

        // Check if the data is surrounded by slashes and strip them if present
        if (substr($serializedData, 0, 1) === '/' && substr($serializedData, -1) === '/') {
            $serializedData = stripslashes(substr($serializedData, 1, -1));
        }

        $serializedData = stripslashes($serializedData);

        // Check if the data is surrounded by quotes and strip them if present
        if ((substr($serializedData, 0, 1) === '"' && substr($serializedData, -1) === '"') ||
            (substr($serializedData, 0, 1) === "'" && substr($serializedData, -1) === "'")) {
            $serializedData = substr($serializedData, 1, -1);
        }

        // Unserialize the data
        $unserializedData = unserialize($serializedData);

        return $unserializedData;
    }

}
