<?php

class AwesomeTrackerRequires{
    public static function load(){
        require_once AwesomeTracker::$plugin_dir . 'inc/init/class-at-activator.php';
        require_once AwesomeTracker::$plugin_dir . 'inc/class-at-log.php';
        require_once AwesomeTracker::$plugin_dir . 'inc/init/class-at-hooks.php';
    }
}