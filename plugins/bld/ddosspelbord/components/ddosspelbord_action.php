<?php
/*
 * Copyright (C) 2024 Anti-DDoS Coalitie Netherlands (ADC-NL)
 *
 * This file is part of the DDoS gameboard.
 *
 * DDoS gameboard is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * DDoS gameboard is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; If not, see @link https://www.gnu.org/licenses/.
 *
 */

namespace bld\ddosspelbord\components;

use Auth;
use Input;
use Config;
use Session;
use Redirect;
use Response;
use Bld\Ddosspelbord\Controllers\Feeds;
use Bld\Ddosspelbord\Models\Actions;
use Bld\Ddosspelbord\Models\spelbordusers;
use Cms\Classes\ComponentBase;
use bld\ddosspelbord\helpers\hLog;

class ddosspelbord_action extends ComponentBase {

    public function componentDetails()
    {
        return [
            'name' => 'Anti-DDoS Coalitie DDoS spelbord',
            'description' => 'Handle backend calls'
        ];
    }

    public function defineProperties()
    {
        return [
            'action_id' => [
                'title'   => 'Action id',
                'description' => 'action id',
                'default' => '',
                'type'    => 'string',
            ],
        ];
    }

    /**
     * @return string
     */
    public function getVersion()
    {
        return Config::get('bld.ddosspelbord::release.version', '0.9.?') . ' - ' . Config::get('bld.ddosspelbord::release.build', 'build 1');
    }

    /**
     * @return void
     */
    public function init() {
        hLog::logLine("D-ddosspelbord_action.init; version=".ddosspelbord_data::getVersion());
    }

    /**
     * @return mixed
     */
    public function submitAction() {
        // get gameboard user
        if ($user = Spelbordusers::verifyAccess()) {

            // Note: POST action for id;
            $action_id = post('id');
            hLog::logLine("D-ddosspelbord_action.submitAction; id=$action_id");

            if ($action_id) {
                $action = Actions::find($action_id);

                $fields = [
                    'name', 'tag', 'description',
                    'start', 'length', 'delay', 'extension',
                    'has_issues', 'is_cancelled'
                ];

                $all = post();

                $update = [];
                foreach ($fields as $field) {
                    $post = post($field,'');
                    hLog::logLine("D-ddosspelbord_action.submitAction; action->$field=".$action->$field);
                    if ($post!=='' && ($post != $action->$field)) {
                        $action->$field = $update[$field] = $post;
                    }
                }

                if (count($update) > 0) {
                    $action->save();

                    $update['id'] = $action->id;
                    $update['partyId'] = $action->party_id;
                    $update['hasIssues'] = $action->has_issues;
                    $update['isCancelled'] = $action->is_cancelled;
                    unset($update['has_issues']);
                    unset($update['is_cancelled']);

                    (new Feeds())->createTransaction('action', $update);
                }


            } else {
                hLog::logLine("D-ddosspelbord_action.submitAction; no action_id");
            }

        }

        return Response::json([
            'result' => true,
        ]);
    }

}
