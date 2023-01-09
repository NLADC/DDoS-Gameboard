<?php

namespace bld\ddosspelbord\components;

use Input;
use Config;
use League\Csv\Exception;
use Session;
use Redirect;
use Response;
use Request;
use Cms\Classes\ComponentBase;
use bld\ddosspelbord\helpers\hLog;
use bld\ddosspelbord\components\ddosspelbord_data as ddosspelbord_data;
use Bld\Ddosspelbord\Models\Logs as Logs;
class ddosspelbord_attachments extends ComponentBase
{

    public function componentDetails()
    {
        return [
            'name' => 'bld.ddosspelbord::lang.plugin.title',
            'description' => 'Handle backend calls'
        ];
    }

    public function defineProperties()
    {
        return [
        ];
    }

    public function getVersion()
    {
        return Config::get('bld.ddosspelbord::release.version', '0.9.?') . ' - ' . Config::get('bld.ddosspelbord::release.build', 'build 1');
    }

    public function init()
    {
        hLog::logLine("D-ddosspelbord_setting.init; version=" . ddosspelbord_data::getVersion());
    }

    public function onRun()
    {
    }

    /**
     * Fetch attachments from database and returning base64 data, filename and extension
     * @return Response
     */
    public static function FetchAttachments()
    {
        $result = false;
        $message = '';
        try {
            if (!empty(post('id', ''))) {
                $id = post('id', '');
                if (!empty($attachment = ddosspelbord_data::getLogAttachment($id))) {
                    $attachedlog = Logs::find($attachment->attachment_id);
                    $file = new \stdClass();
                    $file->filename = $attachment->file_name;
                    $file->created_at = $attachment->getAttribute('created_at');
                    $file->extension = $attachment->extension;
                    $content_type = $attachment->content_type;
                    $file->exportablebase64 = '';
                    foreach ($attachedlog->attachments as $attachment) {
                        if ($attachment->id == $id) {
                            $filepath = $attachment->getLocalPath();
                            if (file_exists($filepath)) {
                                $jsonData = file_get_contents($filepath);
                                if (empty($jsonData)) {
                                    $message = "The server can't open the requested file (file_get_contents = null)";
                                    continue;
                                }
                                $base64_encode = base64_encode($jsonData);
                                unset($jsonData);
                                $file->exportablebase64 = "data:" . $content_type . ";base64," . $base64_encode;
                                unset($base64_encode);
                            } else {
                                $message = "Attachment doesn't exist or wrong filepath";
                            }
                        }
                    }
                    if (!empty($file->exportablebase64)) {
                        $result = true;
                    } else {
                        $result = false;
                    }

                } else {
                    $message = "Can't fetch attachments";
                }
            } else {
                $message = "No id supplied to FetchAttachments";
            }
            if (empty($file->filename)) {
                $message = "Can't find filename in system";
                $result = false;
            }
        } catch (Exception $err) {
            $message = "Cannot show attachment: " . $err->getMessage();
            hLog::logLine("E-$message");
            $result = false;
        }
        if ($message) {
            hLog::logLine("W-$message");
        }


        return Response::json([
            'result' => $result,
            'message' => $message,
            'file' => $file,
        ]);
    }
}
