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
              post_id bigint(20) unsigned NULL,
              archive_ptype varchar(45) NULL,
              user_id int(11) unsigned NULL,
              404_query varchar(45) NULL,
              search_query varchar(45) NULL,
              is_home tinyint(1) DEFAULT 0 NOT NULL,
              date_archive varchar(45) NULL,
              author_archive int(11) unsigned NULL,
              page smallint(1) unsigned DEFAULT 0 NOT NULL,
              ip varchar(45) NOT NULL,
              visited datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
              PRIMARY KEY (ID),
              KEY user_id (user_id),
              KEY ip (ip)
            ) $charset_collate;";

        dbDelta( $sql );


        $table_tax_visits = $wpdb->prefix . AwesomeTracker::TBL_TAXVISITS;

        $sql = "CREATE TABLE $table_tax_visits (
              ID int(11) unsigned NOT NULL AUTO_INCREMENT,
              term_id bigint(20) unsigned NULL,
              taxonomy varchar(32) NULL,
              user_id int(11) unsigned NULL,
              page smallint(1) unsigned DEFAULT 0 NOT NULL,
              ip varchar(45) NOT NULL,
              visited datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
              PRIMARY KEY (ID),
              KEY user_id (user_id),
              KEY ip (ip)
            ) $charset_collate;";

        dbDelta( $sql );

    }
}