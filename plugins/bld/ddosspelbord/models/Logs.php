<?php namespace Bld\Ddosspelbord\Models;

use bld\ddosspelbord\helpers\hLog;
use Model;
use Session;
use System\Models\File as File;

/**
 * Model
 */
class Logs extends Model {

    use \Winter\Storm\Database\Traits\Validation;

    /**
     * @var string The database table used by the model.
     */
    public $table = 'bld_ddosspelbord_logs';

    /**
     * @var array Validation rules
     */
    public $rules = [
        'log' => 'required',
    ];

    protected $hidden = [
        'created_at', 'updated_at'
    ];

    public $hasOne = [
        'user' => [
            'bld\ddosspelbord\models\Spelbordusers',
            'key' => 'id',
            'otherKey' => 'user_id',
        ],
        'party' => [
            'bld\ddosspelbord\models\Parties',
            'key' => 'id',
            'otherKey' => 'bld_ddosspelbord_users.party_id',
        ]
    ];

    public $attachMany = [
        'attachments' => ['System\Models\File']
    ];

    public static function getAttachments($id) {
        $logmaxfiles = Settings::get('logmaxfiles');

        $attachments = File::where('attachment_id', $id)
            ->orderBy('file_name')
            ->take($logmaxfiles)
            ->get();

        return($attachments);
    }

    public static function getAttachment($id) {

        $attachment = File::where('id', $id)->first();

        return($attachment);
    }

    public static function getAttachmentrawdata($id) {
        self::attachments()->output();
    }



    public function getPartyAttribute($value) {

        $party = '';
        $user = Spelbordusers::find($this->user_id);
        if ($user) {
            $party = Parties::find($user->party_id);
        }
        return ($party) ? $party->name : '';
    }


    public function scopeSelectParty($query,$party_ids) {

        //hlog::logLine("D-scopeParty; party_id=".print_r($party_ids,true));
        if (is_array($party_ids)) {
            $query->join('bld_ddosspelbord_users','bld_ddosspelbord_users.id','=','bld_ddosspelbord_logs.user_id')
                ->whereIn('bld_ddosspelbord_users.party_id',$party_ids);
            hlog::logLine("D-scopeSelectParty; set array");
            Session::put(SESSION_LOGS_FILTER_PARTIES,serialize($party_ids));
        }
        return $query;
    }

    public function scopeSelectUser($query,$user_ids) {

        //hlog::logLine("D-scopeParty; party_id=".print_r($party_ids,true));
        if (is_array($user_ids)) {
            $query->whereIn('bld_ddosspelbord_logs.user_id',$user_ids);
            hlog::logLine("D-scopeSelectUser; set array");
            Session::put(SESSION_LOGS_FILTER_USERS,serialize($user_ids));
        }
        return $query;
    }

}


