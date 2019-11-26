<?php

class AwesomeTrackerCron {

    const HOOK = 'awesometracker_cron';

    public static function remove_old_records(){

        $days = AwesomeTrackerOptions::get_daysToPurgeDB();

        AwesomeTracker_Record::delete_old_recordsDB($days);
    }

}
