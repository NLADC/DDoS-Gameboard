<?php
namespace bld\ddosspelbord\classes\base;

use bld\ddosspelbord\classes\helpers\ddosspelbordUsers;
use bld\ddosspelbord\models\Input;
use Backend\Classes\Controller;
use BackendMenu;
use BackendAuth;
use Lang;
use Winter\Storm\Auth\AuthException;
use Winter\Storm\Exception\ApplicationException;
use bld\ddosspelbord\helpers\hLog;

class baseController extends Controller {
    public $checkedIds;

    /**
     * @description Add Controller Namespaces to Model Controller systems that use "party_id" to filter records!
     * @var string[]
     */
    private $partySpecific = [
        'BackendUserPivots',
        'Actions',
        'Attacks',
        'Spelbordusers',
        'Targets',
        'Startpage',
        'Logs',
    ];


    public function __construct() {
        parent::__construct();
    }

    public function getParams() {
        return $this->params;
    }

    public function getProperty( $propertyName ) {
        return $this->$propertyName;
    }

    public function getCurrentId() {
        return ( is_array($this->params) && count($this->params) > 0 ) ? current($this->params) : false;
    }

    public function getControllerName() {
        $reflect = new \ReflectionClass($this);
        return $reflect->getShortName();
    }

    public function listExtendQuery( $query, $definition ) {
        /**
         * Hide records from other partys
         */
        if ( $this->checkPartySpecific() ) {
            if ( ddosspelbordUsers::filterOnParty() ) {
                $partyId = ddosspelbordUsers::getBackendPartyId();
                if ( $partyId > 0 ) {
                    // Special treatment for quering logs
                    if ($this->getChildClassName() == 'Logs') {
                        $query
                            ->with(['user.parties'])
                            ->whereRelation('user.parties', 'id', $partyId);
                    }
                    else {
                        $query->where('party_id', $partyId);
                    }
                }
            }
        }
    }

    public function formExtendModel( $model ) {

    }

    public function listFilterExtendScopes($filter)
    {
        // If you can only see one party remove filtering on party
        if (ddosspelbordUsers::filterOnParty()) {
            $filter->removeScope('party');
        }
    }

    public function formExtendFields( $form, $fields ) {
        if ( $this->checkPartySpecific() ) {
            if ( !ddosspelbordUsers::filterOnParty() && !empty($fields['party']) ) {
                $fields['party']->hidden = false;
            }
            if ( !ddosspelbordUsers::filterOnParty() && !empty($fields['party_id']) ) {
                $fields['party_id']->hidden = false;
            }
        }
    }

    public function listExtendColumns( $list ) {
        /**
         * Remove the party column user that can not switch partys
         */
        if ( $this->checkPartySpecific() ) {
            if ( ddosspelbordUsers::filterOnParty() ) {
                $list->removeColumn('party');
            }
        }
    }

    /**
     * @return bool
     */
    private function checkPartySpecific() {
        return in_array($this->getChildClassName(), $this->partySpecific);
    }

    /**
     * @return string
     */
    private function getChildClassName() {
        return ( new \ReflectionClass($this) )->getShortName();
    }
}
