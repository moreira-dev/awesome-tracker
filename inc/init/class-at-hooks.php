<?php

class AwesomeTrackerHooks{
    public static function add_actions(){
        add_action( 'template_redirect', 'AwesomeTrackerLog::add_template_visit', 0, 200 );

        add_action( 'admin_menu', 'AwesomeTrackerPageMain::init_menu', 9);
        add_action( 'admin_menu', 'AwesomeTrackerPageRecords::init_menu', 9);
        add_action( 'admin_menu', 'AwesomeTrackerPageRoutes::init_menu', 9);

        add_action( 'rest_api_init', 'AwesomeTrackerApi::register_routes');
    }

    public static function add_filters(){
        add_filter( 'rest_request_after_callbacks', 'AwesomeTrackerLog::add_api_visit', 10, 3 );
    }

}
