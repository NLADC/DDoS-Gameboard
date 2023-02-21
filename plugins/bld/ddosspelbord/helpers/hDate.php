<?php
namespace bld\ddosspelbord\helpers;

use DateTime;
use DateTimeZone;

class hDate {

    public static function Utc2Est($stime,$format='Y-m-d H:i:s') {
        $date = new DateTime($stime);
        // Convert
        $date->setTimezone(new DateTimeZone('Europe/Amsterdam'));
        return $date->format($format);
    }
}
