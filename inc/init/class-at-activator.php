<?php


class AwesomeTrackerActivator
{
    public static function create_tables(){

        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
        global $wpdb;

        $charset_collate = $wpdb->get_charset_collate();

        $table_visits = $wpdb->prefix . AwesomeTracker::TBL_VISITS;

        $sql = "CREATE TABLE $table_visits (
              ID int(11) unsigned NOT NULL AUTO_INCREMENT,
              post_id bigint(20) unsigned NOT NULL,
              user_id int(11) unsigned NULL,
              ip varchar(45) NOT NULL,
              visited datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
              PRIMARY KEY (ID),
              KEY user_id (user_id),
              KEY ip (ip)
            ) $charset_collate;";

        dbDelta( $sql );

    }
}