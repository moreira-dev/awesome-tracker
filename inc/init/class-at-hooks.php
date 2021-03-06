<?php

class AwesomeTrackerHooks{
    public static function add_actions(){
        add_action( 'template_redirect', 'AwesomeTrackerLog::add_template_visit', 0, 200 );

        add_action( 'admin_menu', 'AwesomeTrackerPageMain::init_menu', 9);
        add_action( 'admin_menu', 'AwesomeTrackerPageRecords::init_menu', 9);
        add_action( 'admin_menu', 'AwesomeTrackerPageRoutes::init_menu', 9);
        add_action( 'admin_menu', 'AwesomeTrackerPageSettings::init_menu', 9);

        add_action( 'rest_api_init', 'AwesomeTrackerApi::register_routes');

        add_action( AwesomeTrackerCron::HOOK_DAILY, 'AwesomeTrackerCron::remove_old_records' );
        add_action( AwesomeTrackerCron::HOOK_HOURLY, 'AwesomeTrackerCron::update_ip_data' );

        add_action( 'plugins_loaded', 'AwesomeTracker::load_textdomain' );
    }

    public static function add_filters(){
        add_filter( 'rest_request_after_callbacks', 'AwesomeTrackerLog::add_api_visit', 10, 3 );
    }

}
