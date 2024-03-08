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

namespace bld\ddosspelbord\classes\measurements\ripe;

use bld\ddosspelbord\helpers\hCurl;
use bld\ddosspelbord\helpers\hLog;
use Config;

class ripeMeasurements {

    private static $_key = '';

    public static function setKey($key) {
        self::$_key = $key;
    }

    public static function getResults($mid,$start='') {

        $result = [];
        if ($mid) {

            $ripeurl = Config::get('bld.ddosspelbord::measurements.ripe.ripe_atlas_api','');

            if ($ripeurl) {

                $url = $ripeurl . 'measurements/'.$mid.'/results/';
                if (self::$_key) {
                    $url .= '?key='.self::$_key;
                }
                if ($start) {
                    $url .= (self::$_key?'&':'?') .'start='. $start;
                }

                hLog::logLine("D-ripeMeasurements; getResults; url=$url");

                $s = microtime(true);
                $curl = new hCurl();
                $result = $curl->get($url);
                $s = microtime(true) - $s;
                hLog::logLine("D-ripeMeasurements; getResults; $s sec needed for ripe atlas call");

            } else {
                hLog::logLine("E-ripeMeasurements; getResults; RIPE ATLAS API url must be set (config)");
            }

        } else {
            hLog::logLine("E-ripeMeasurements; getResults; measurement-id must be set");
        }
        return $result;
    }

    public static function postJson($json) {

        $result = false;
        if ($json) {

            $ripeurl = Config::get('bld.ddosspelbord::measurements.ripe.ripe_atlas_api','');

            if ($ripeurl) {

                $url = $ripeurl . 'measurements/';
                $url .= '?key='.self::$_key;

                hLog::logLine("D-ripeMeasurements; postJson; url=$url");

                $s = microtime(true);
                $curl = new hCurl();
                $result = $curl->post($url,$json);
                $s = microtime(true) - $s;
                hLog::logLine("D-ripeMeasurements; postJson; $s sec needed for ripe atlas call");

            } else {
                hLog::logLine("E-ripeMeasurements; postJson; RIPE ATLAS API url must be set (config)");
            }

        } else {
            hLog::logLine("E-ripeMeasurements; postJson; JSON must be set");
        }
        return $result;
    }

    // ** get alle measurements form MID, optional from start

    public static function getMeasurementsTimedResult($mid,$start='',$data='',$debug=false) {

        if ($data=='') {
            $data = self::getResults($mid,$start);
        }

        if ($debug) hLog::logLine("D-In debug mode; log alerts/errors/empty values");

        // break down in [time (minute), ipversion, type, avg response time]


        if ($data) {

            $rts = $rtscount = [];

            // get the probe data; get average for (type,timestamp(minute),ipversion)

            $workcnt = 0;
            $probecnt = count($data);
            foreach ($data AS $probe) {

                //hLog::logLine("D-Probe object:" . print_r($probe,true));

                $timestamp = $ipversion = $responsetime = '';
                $type = (isset($probe->type)) ? $probe->type : '';
                $responsetime = 0;

                if ($type) {

                    switch ($type) {

                        // Note: in comment only the usable fields

                        case 'ping':

                            /**
                             * af: 4 or 6
                             * avg: (float) average round-trip
                             * max: (float) max round-trip
                             * min: (float) min round-trip
                             *
                             */

                            $timestamp = (isset($probe->timestamp)) ? $probe->timestamp : '';
                            $ipversion = (isset($probe->af)) ? $probe->af : '';
                            $responsetime = (isset($probe->avg)) ? $probe->avg : '';

                            $workcnt += 1;

                            break;

                        case 'traceroute':

                            /**
                             * af: 4 or 6
                             * timestamp: (int) timestamp for start of the measurement
                             * endtime: (int) timestamp for end of the measurement
                             * result[]:
                             *   error: if set error -> skip
                             *   result: (3x) roundtrips with rtt
                             */

                            $hops = (isset($probe->result)) ? $probe->result : [];

                            if (count($hops) > 0) {

                                $lasthop = $hops[count($hops) - 1];

                                if (!isset($lasthop->error)) {
                                    $roundtrips = $lasthop->result;
                                    if (isset($lasthop->result) && count($lasthop->result) > 0) {
                                        $timestamp = (isset($probe->endtime)) ? $probe->endtime : '';
                                        $ipversion = (isset($probe->af)) ? $probe->af : '';
                                        $responsetime = 0; $roundlog = '';
                                        foreach ($roundtrips AS $roundtrip) {
                                            if (isset($roundtrip->rtt)) {
                                                $responsetime += $roundtrip->rtt;
                                                $roundlog .= (($roundlog!='')?',':'').$roundtrip->rtt;
                                            }
                                        }
                                        $responsetime = ($responsetime / count($roundtrips));
                                        //hLog::logLine("D-ProbeID=$probe->prb_id, timestamp=".date('Y-m-d H:i',$timestamp).", roundtrips: $roundlog, avg(responsetime)=$responsetime");
                                        $workcnt += count($roundtrips);
                                    } else {
                                        if ($debug) hLog::logLine("W-[type=$type,prb_id=$probe->prb_id] no last hop result");
                                    }
                                } else {
                                    if ($debug) hLog::logLine("W-[type=$type,prb_id=$probe->prb_id] error: ".print_r($lasthop->error,true));
                                }

                            } else {
                                if ($debug) hLog::logLine("W-[type=$type,prb_id=$probe->prb_id] no HOPS!?");
                            }

                            break;

                        case 'dns':

                            /**
                             *
                             * af: 4 or 6
                             * error: if set error -> ignore
                             * timestamp: time
                             * result:
                             *   rt: response time in ms
                             *
                             */

                            if (empty($probe->error)) {
                                $responsetime = (isset($probe->result->rt)) ? $probe->result->rt : '';
                                if ($responsetime) {
                                    $timestamp = (isset($probe->timestamp)) ? $probe->timestamp : '';
                                    $ipversion = (isset($probe->af)) ? $probe->af : '';
                                    $workcnt += 1;
                                } else {
                                    if ($debug) hLog::logLine("W-[type=$type,prb_id=$probe->prb_id] responsetime is empty!?");
                                }
                            } else {
                                if ($debug) hLog::logLine("W-[type=$type,prb_id=$probe->prb_id] error: ".print_r($probe->error,true));
                            }

                            break;

                        case 'ntp':

                            /**
                             *
                             * af: 4 or 6
                             * error: if set error -> ignore
                             * timestamp: time
                             * result:
                             *   rt: response time in ms
                             *
                             */
                            $hops = (isset($probe->result)) ? $probe->result : [];

                            if (count($hops) > 0) {

                                $lasthop = $hops[count($hops) - 1];

                                $responsetime = (isset($lasthop->rtt)) ? $lasthop->rtt : '';
                                if ($responsetime) {
                                    $timestamp = (isset($probe->timestamp)) ? $probe->timestamp : '';
                                    $ipversion = (isset($probe->af)) ? $probe->af : '';
                                    $workcnt += 1;
                                }

                            }

                            break;


                        case 'sslcert':

                            /**
                             * af: 4 or 6
                             * alert and error skip
                             * timestamp: time
                             * rt: response time in ms
                             *
                             */

                            if (empty($probe->alert)) {

                                if (!emptY($probe->err) && $probe->err =='connect: Connection refused') {
                                    //hLog::logLine("D-Probe error: $probe->err");
                                    $probe->rt = 60000;  // Connection refused -> one minute response
                                } elseif (!emptY($probe->err)) {
                                    if ($debug) hLog::logLine("W-[type=$type,prb_id=$probe->prb_id] Probe has error: '$probe->err' ");
                                }

                                $responsetime = (isset($probe->rt)) ? $probe->rt : '';
                                if ($responsetime) {
                                    $timestamp = (isset($probe->timestamp)) ? $probe->timestamp : '';
                                    $ipversion = (isset($probe->af)) ? $probe->af : '';
                                    $workcnt += 1;
                                } else {
                                    if ($debug) hLog::logLine("W-[type=$type,prb_id=$probe->prb_id] responsetime is empty!?");
                                }

                            } else {
                                if ($debug) hLog::logLine("W-[type=$type,prb_id=$probe->prb_id] Skip use of probe; alert: '$probe->alert' ");
                            }

                            break;

                        default:
                            hLog::logLine("E-Unknown probe type: $type");
                            break;

                    }

                    // skip if no valid result
                    if ($timestamp) {

                        // each minute
                        $timestamp = date('Y-m-d H:i',$timestamp);

                        // summarize based on (type,ipversion,timestamp)
                        $resultkey = $type.'_'.$ipversion.'_'.$timestamp;
                        if (!isset($rts[$resultkey])) {
                            $rts[$resultkey] = $responsetime;
                            $rtscount[$resultkey] = 1;
                        } else {
                            $rts[$resultkey] += $responsetime;
                            $rtscount[$resultkey] += 1;
                        }

                    }


                } else {
                    hLog::logLine("E-getMeasurementsTimedResult; unkown type in probe: ".print_r($probe,true));
                }

            }

            $result = [];
            foreach ($rts AS $key => $rtsum) {
                list($type,$ipversion,$timestamp) = explode('_',$key);
                $responsetime = ($rtsum / $rtscount[$key]);
                $result[] = [
                    'type' => $type,
                    'timestamp' => $timestamp,
                    'ipversion' => $ipversion,
                    'rtt' => round($responsetime,3),
                    'probe_count' => $rtscount[$key],
                ];
            }

            hLog::logLine("D-getMeasurementsTimedResult; number of probes=$probecnt, number used=$workcnt, count(result)=".count($result));

        } else {

            // not additonal logging needed, enough complains in other functions (curl)
            $result = false;

        }

        return $result;
    }

    public static function createMeasurement($configjson)
    {

        return self::postJson($configjson);
    }

}
