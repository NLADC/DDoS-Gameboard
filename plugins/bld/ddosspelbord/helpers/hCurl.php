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

namespace bld\ddosspelbord\helpers;

class hCurl {

    // default value options
    private $curl_options = [
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 60,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_COOKIESESSION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_HTTPHEADER => array('Content-Type: application/json'),
    ];

    private $channel = null;

    /**
     * @description Set credentials for ICCAM
     * ScartCurl constructor.
     */
    function __construct() {
        $this->channel = null;
    }

    public function setCurlOption($key, $value)
    {
        $this->curl_options[$key] = $value;
    }

    protected function connect($url)
    {
        if ($this->channel==null) {
            $this->channel = curl_init($url);
            curl_setopt_array($this->channel,$this->curl_options);
        }
        return $this->channel;
    }


    public function get($url) {

        $result = false;

        try {

            $this->setCurlOption(CURLOPT_POST,false);

            $channel = $this->connect($url);

            $result = curl_exec($channel);

            if ($result) {

                $info = curl_getinfo($channel);
                if (isset($info['http_code'])) {
                    if ($info['http_code'] != '200') {
                        hLog::logLine("E-Curl httpcode!=200; info: ".print_r($info,true));
                        $result = false;
                    }
                }

                if ($result) {
                    // decode json if positief result
                    $result = json_decode($result);
                }

            }

            $this->close();

        } catch (\Exception $err) {
            hLog::logLine("E-Curl error: ".$err->getMessage());
        }

        return $result;
    }

    public function post($url, $postfields) {

        $this->setCurlOption(CURLOPT_POST,true);
        $this->setCurlOption(CURLOPT_POSTFIELDS,$postfields);
        $this->setCurlOption(CURLOPT_HTTPHEADER,array('Content-Type:application/json'));

        try {

            $channel = $this->connect($url);

            $result = curl_exec($channel);

            if ($result) {

                $info = curl_getinfo($channel);
                if (isset($info['http_code'])) {
                    if (!in_array($info['http_code'],['200','201','202'])) {
                        hLog::logLine("E-Curl httpcode!=200/201/202; info: " . print_r($info, true));
                        if ($result)  hLog::logLine("E-Curl httpcode!=200; result: " . print_r($result, true));
                        $result = false;
                    }
                }

                if ($result) {
                    // decode json if positief result
                    $result = json_decode($result);
                }

            }

            $this->close();

        } catch (\Exception $err) {
            hLog::logLine("E-Curl error: ".$err->getMessage());
        }

        return $result;
    }

    public function close() {

        if ($this->channel!=null) {
            curl_close($this->channel);
        }
    }



}
