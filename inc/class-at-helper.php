<?php

class ATHelper {

    /**
     * Limits the max size of a string to a certain number of chars
     *
     * @param string $string String to cut
     * @param int    $to     max length for this string to be
     *
     * @return string
     */
    public static function limit_string_to($string, $to) {

        return substr($string, 0, $to);
    }

    public static function get_null_if_empty($valueToCheck) {

        if (empty($valueToCheck))
            return null;

        return $valueToCheck;
    }

    public static function get_client_ip() {

        if (isset($_SERVER['HTTP_CLIENT_IP']) && $ipaddress = self::filter_ip($_SERVER['HTTP_CLIENT_IP'])) :
        elseif (isset($_SERVER['HTTP_X_FORWARDED_FOR']) && $ipaddress = self::filter_ip($_SERVER['HTTP_X_FORWARDED_FOR'])) :
        elseif (isset($_SERVER['HTTP_X_FORWARDED']) && $ipaddress = self::filter_ip($_SERVER['HTTP_X_FORWARDED'])) :
        elseif (isset($_SERVER['HTTP_FORWARDED_FOR']) && $ipaddress = self::filter_ip($_SERVER['HTTP_FORWARDED_FOR'])) :
        elseif (isset($_SERVER['HTTP_FORWARDED']) && $ipaddress = self::filter_ip($_SERVER['HTTP_FORWARDED'])) :
        elseif (isset($_SERVER['REMOTE_ADDR']) && $ipaddress = self::filter_ip($_SERVER['REMOTE_ADDR'])) :
        elseif (isset($_SERVER['REMOTE_ADDR']) && $ipaddress = self::filter_ip($_SERVER['REMOTE_ADDR'], true)) :
        else :
            $ipaddress = 'UNKNOWN';
        endif;

        return $ipaddress;

    }

    public static function filter_ip($ip, $allowLocalIP = false) {

        $ip = trim($ip);

        if (!self::is_valid_ip($ip, $allowLocalIP))
            return false;

        return $ip;
    }


    public static function is_valid_ip($ip, $allowLocalIP = false) {

        $ip = trim($ip);

        $flags = FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE;

        if($allowLocalIP)
            $flags = null;


        /**
         * Check if valid and public IPv4 or IPv6
         */
        if (!filter_var($ip, FILTER_VALIDATE_IP, $flags))
            return false;

        return true;
    }

}
