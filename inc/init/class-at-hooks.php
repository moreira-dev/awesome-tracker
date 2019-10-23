<?php

class AwesomeTrackerHooks{
    public static function add_actions(){
        add_action( 'template_redirect', 'AwesomeTrackerLog::add_template_visit', 0, 200 );
    }

    public static function add_filters(){

    }

}