<?php
// If uninstall not called from WordPress, then exit.
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

delete_option('awesome_tracker_version');


global $wpdb;

$table_visits = $wpdb->prefix . AwesomeTracker::TBL_VISITS;

$wpdb->query("DROP TABLE IF EXISTS {$table_visits}");
