<?php

class AwesomeTrackerOptions {

    const OPTION_RECORDSDB = 'at_settings_recordsDB';

    public static function get_daysToPurgeDB(){
        return intval(get_option(self::OPTION_RECORDSDB,0));
    }

    public static function set_daysToPurgeDB($days){
        return update_option('at_settings_recordsDB',intval($days));
    }

}
