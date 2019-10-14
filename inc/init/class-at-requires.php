<?php

class AwesomeTrackerRequires{
    public static function load(){
        require_once plugin_dir_path( __FILE__ ) . 'inc/init/class-at-activator.php';
    }
}