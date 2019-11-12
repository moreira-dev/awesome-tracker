<?php

class AwesomeTrackerRequires{
    public static function load(){
        require_once AwesomeTracker::$plugin_dir . 'inc/class-at-helper.php';
        require_once AwesomeTracker::$plugin_dir . 'inc/models/class-at-record.php';
        require_once AwesomeTracker::$plugin_dir . 'inc/models/class-at-route.php';
        require_once AwesomeTracker::$plugin_dir . 'inc/init/class-at-activator.php';
        require_once AwesomeTracker::$plugin_dir . 'inc/class-at-log.php';
        require_once AwesomeTracker::$plugin_dir . 'inc/class-at-api.php';
        require_once AwesomeTracker::$plugin_dir . 'inc/class-at-export.php';
        require_once AwesomeTracker::$plugin_dir . 'inc/init/class-at-hooks.php';

        if(is_admin()){
            self::load_admin();
        }
    }

    public static function load_admin(){
        require_once AwesomeTracker::$plugin_dir . 'inc/tables/class-wp-at-table.php';
        require_once AwesomeTracker::$plugin_dir . 'inc/tables/class-at-log-table.php';
        require_once AwesomeTracker::$plugin_dir . 'inc/admin/class-at-main.php';
        require_once AwesomeTracker::$plugin_dir . 'inc/admin/class-at-routes.php';
        require_once AwesomeTracker::$plugin_dir . 'inc/admin/class-at-records.php';
    }
}
