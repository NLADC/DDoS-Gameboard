<?php

namespace bld\ddosspelbord\components;

use Input;
use Config;
use Session;
use Redirect;
use Response;
use Request;
use Cms\Classes\ComponentBase;
use bld\ddosspelbord\helpers\hLog;
use bld\ddosspelbord\components\ddosspelbord_data as ddosspelbord_data;
use Bld\Ddosspelbord\Models\Logs;

class ddosspelbord_attachments extends ComponentBase
{
    public function componentDetails()
    {
        return [
            'name' => 'bld.ddosspelbord::lang.plugin.title',
            'description' => 'Handle backend calls for fetching attachments'
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

    /**
     * Fetch attachments from database and returning base64 data, filename and extension
     * @return Response
     */
    public static function FetchAttachments()
    {
        // These 3 wil always be returned to the html
        $result = false;
        $error_messages = [];
        $file = '';

        $id = post('id', '');

        if ( is_int($id) && $id > 0) $attachment = ddosspelbord_data::getLogAttachment($id);
        // We must go through the log model or winter won't accept our request for a filepath.
        if ( !empty($attachment)) $attachedlog = Logs::find($attachment->attachment_id);

        // Setting error messages for the client to read if a crucial object is empty
        if ((!is_int($id)) || empty($attachment) || empty($attachedlog)) {
            if (!is_int($id) || !$id > 0) $error_messages[] = 'ID is not a valid number or empty';
            if ( empty($attachment) ) $error_messages[] = 'Attachment cannot be found';
            if ( empty($attachedlog) ) $error_messages[] = 'Attached log can not be found';
        }
        else {
            $file = self::createEmptyFileClass($attachment);

            // Inject our File with base64 data
            $injectionResult = (empty($error_messages)) ? self::injectFileWithData($file, $attachedlog, $id) : [];
            if (!empty($injectionResult)) $file = $injectionResult['file'];

            // Merge errors from injectFileWithData with the ones in this function
            $error_messages = array_merge($error_messages ?? array(), $injectionResult['error_messages']);

            // If there is an exportable base64 with a filename then we have a result = true, otherwise result will remain false
            $result = !empty($file->exportablebase64) && $file->filename;
        }

        // If there are error messages convert them to a comma seperated string
        $error_messages = ($error_messages[0]) ? implode(', ', $error_messages) : '';

        if (!empty($error_messages)) {
            hLog::logLine("Error message(s):" . $error_messages);
        }

        return Response::json([
            'result' => $result,
            'message' => $error_messages,
            'file' => $file,
        ]);
    }

    /**
     * @param $attachment
     * @return object
     */
    private static function createEmptyFileClass($attachment) {
        return (object)[
            'filename' => $attachment->file_name,
            'created_at' => $attachment->getAttribute('created_at'),
            'extension' => $attachment->extension,
            'exportablebase64' => ''
        ];
    }

    private static function injectFileWithData($file, $attachedlog, $id)
    {
        $error_messages = [];

        foreach ($attachedlog->attachments as $attachment) {
            // Get the correct attachment from the attachedlog
            if (!$attachment->id == $id) continue;

            // Finally we can fetch the attachment, the function getLocalPath() only works if you do it through the correct attached log model.
            $filepath = $attachment->getLocalPath();
            if (!file_exists($filepath)) {
                $error_messages[] = "Attachment doesn't exist or wrong filepath";
                break;
            }

            // Jsondata is a long string where we load the contents of the file in a string.
            $jsonData = file_get_contents($filepath);
            $content_type = $attachment->content_type;
            $error_messages[] = empty($jsonData) ? "The server can't open the requested file (file_get_contents = null)" : [];

            // Creating a string that the browser can read. note the base64_encode() of the $jsondata
            $file->exportablebase64 = !empty($jsonData) ? "data:" . $content_type . ";base64," . base64_encode($jsonData) : null;
            break;
        }

        return [
            'file' => $file,
            'error_messages' => $error_messages,
        ];
    }
}
