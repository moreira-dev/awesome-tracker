<?php

class AwesomeTrackerActivator {

    public static function create_tables() {

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        global $wpdb;

        $charset_collate = $wpdb->get_charset_collate();

        $table_visits = $wpdb->prefix . AwesomeTracker::TBL_VISITS;

        $sql = "CREATE TABLE $table_visits (
              ID int(11) unsigned NOT NULL AUTO_INCREMENT,
              post_id bigint(20) unsigned NULL,
              archive_ptype varchar(45) NULL,
              term_id bigint(20) unsigned NULL,
              taxonomy varchar(32) NULL,
              user_id int(11) unsigned NULL,
              query_404 varchar(150) NULL,
              search_query varchar(100) NULL,
              is_home tinyint(1) DEFAULT 0 NOT NULL,
              date_archive varchar(45) NULL,
              author_archive int(11) unsigned NULL,
              api_route varchar(150) NULL,
              api_method varchar(50) NULL,
              page smallint(1) unsigned DEFAULT 0 NOT NULL,
              ip varchar(45) NOT NULL,
              country_code varchar(5) NULL,
              country_name varchar(100) NULL,
              ip_data text NULL,
              visited datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
              PRIMARY KEY (ID),
              KEY user_id (user_id),
              KEY ip (ip),
              KEY country_code (country_code)
            ) $charset_collate;";

        dbDelta($sql);

    }

    public static function uninstall() {

        delete_option('awesome_tracker_version');
        delete_option(AwesomeTracker_Route::KEY_OPTION);


        global $wpdb;

        $table_visits = $wpdb->prefix . AwesomeTracker::TBL_VISITS;

        $wpdb->query("DROP TABLE IF EXISTS {$table_visits}");

        self::unregister_cron();

    }

    public static function register_cron() {

        if(!wp_next_scheduled(AwesomeTrackerCron::HOOK_DAILY)){
            wp_schedule_event(time(), 'daily', AwesomeTrackerCron::HOOK_DAILY);
        }
        if(!wp_next_scheduled(AwesomeTrackerCron::HOOK_HOURLY)){
            wp_schedule_event(time(), 'hourly', AwesomeTrackerCron::HOOK_HOURLY);
        }
    }

    public static function unregister_cron() {

        $hooksToRemove = array(AwesomeTrackerCron::HOOK_DAILY, AwesomeTrackerCron::HOOK_HOURLY);

        foreach ($hooksToRemove as $removeHook) {
            $timestamp = wp_next_scheduled($removeHook);
            wp_unschedule_event($timestamp, $removeHook);
            wp_clear_scheduled_hook($removeHook);
        }

    }

}
