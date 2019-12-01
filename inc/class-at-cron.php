<?php

class AwesomeTrackerCron {

    const HOOK_DAILY = 'awesometracker_cron_daily';

    const HOOK_HOURLY = 'awesometracker_cron_hourly';

    const IP_LIMIT = 1000;

    public static function remove_old_records() {

        $days = AwesomeTrackerOptions::get_daysToPurgeDB();

        AwesomeTracker_Record::delete_old_recordsDB($days);
    }

    public static function update_ip_data() {

        global $wpdb;

        $tableTrack = $wpdb->prefix . AwesomeTracker::TBL_VISITS;

        $limit = apply_filters('awesometracker_ip_call_limit', self::IP_LIMIT);

        $ips = $wpdb->get_results(
            "SELECT DISTINCT(ip) as ip 
                            FROM {$tableTrack} 
                            WHERE country_code IS NULL 
                            ORDER BY ID DESC 
                            LIMIT {$limit}"
        );

        foreach ($ips as $record)
            if(AwesomeTrackerHelper::is_valid_ip($record->ip)){
                try {
                    $pageContent = file_get_contents('https://api.iplocate.app/json/' . $record->ip);
                    $parsedJson = json_decode($pageContent);

                    if(!empty($parsedJson) && !empty($parsedJson->country_code)){
                        $wpdb->update(
                            $tableTrack,
                            array(
                                'country_code' => $parsedJson->country_code,
                                'country_name' => $parsedJson->country_name
                            ),
                            array(
                                'ip' => $record->ip
                            )
                        );
                    }

                } catch (Exception $e) {
                    error_log($e->getMessage());
                }
            }



    }

}
