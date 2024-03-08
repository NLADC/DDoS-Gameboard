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

class base64Helper {

    /**
     * A base 64 image straight from the client has an header, the wintercms new File->fromData function doesn't work with this header.
     * This function helps with removing it.
     * @param $data
     * @return string
     *
     */
    public static function RemoveBase64Header ($data) {
        $checkpatter = "/(;base64)./i";

        if (preg_match($checkpatter, $data)) {
            // Header is always in the first 100, way beter performance to only just search in there.
            $data = self::split_on($data, 100);
            $trimpattern = "/^(data:).*(base64),/i";
            $data[0] = preg_replace($trimpattern, "", $data[0]);
            $rawdatatrimmed = $data[0] . $data[1];
        }
        else {
            $rawdatatrimmed = '';
        }
        return ($rawdatatrimmed);
    }

    /**
     * Splits an arroy on a specified point.
     * @param $string
     * @param $num
     * @return array
     */
    private static function split_on($string, $num) {
        $length = strlen($string);
        $output[0] = substr($string, 0, $num);
        $output[1] = substr($string, $num, $length );
        return $output;
    }

}
