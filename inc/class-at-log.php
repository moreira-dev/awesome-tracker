<?php

Class AwesomeTrackerLog{

    static public function add_template_visit(){

        if(is_front_page())
            self::log_front_page();
        elseif(is_404())
            self::log_404();
        elseif(is_search())
            self::log_search();
        elseif(is_singular())
            self::log_single();
        elseif(is_archive())
            self::log_archive();

    }


    static public function log_404(){

        global $wp;

        $query = $wp->request;

        if(empty($query))
            $query = 1;

        return self::log(array('404_query' => $query));

    }

    static public function log_archive(){

        $queried_object = get_queried_object();

        $paged = (get_query_var('paged')) ? get_query_var('paged') : 1;

        if ( $queried_object instanceof WP_Post_Type )
            return self::log(array(
                'archive_ptype' => $queried_object->name,
                'page' => $paged
            ));
        elseif( $queried_object instanceof WP_Term )
            return self::log(array(
                'term_id' => $queried_object->term_id,
                'taxonomy' => $queried_object->taxonomy,
                'page' => $paged
            ));
        elseif( $queried_object instanceof  WP_User)
            return self::log(array(
                'author_archive' => $queried_object->ID,
                'page' => $paged
            ));
        elseif( is_date() ){
            $query = get_query_var('year').
                (get_query_var('monthnum')? '-'.get_query_var('monthnum') : '').
                (get_query_var('day')? '-'.get_query_var('day') : '');

            if(empty($query))
                $query = 1;

            return self::log(array(
                'date_archive' => $query,
                'page' => $paged
            ));
        }

        return false;

    }

    static public function log_front_page(){

        $post_id = NULL;
        if(is_page())
            $post_id = get_queried_object_id();

        return self::log(array('post_id' => $post_id, 'is_home' => 1));
    }

    static public function log_single(){

        return self::log(array('post_id' => get_queried_object_id()));

    }

    static public function log_search(){

        $query = get_search_query();

        if(empty($query))
            $query = 1;

        return self::log(array('search_query' => $query));

    }

    static private function log($args = array()){

        global $wpdb;
        $table = $wpdb->prefix . AwesomeTracker::TBL_VISITS;
        $is_tax = false;

        if( (isset($args['taxonomy']) && $args['taxonomy'])
            || (isset($args['term_id']) && $args['term_id']) ){
                $table = $wpdb->prefix . AwesomeTracker::TBL_TAXVISITS;
                $is_tax = true;
        }

        if(!isset($args['visited']) || empty($args['visited'])){
            $args['visited'] = current_time('mysql');
        }

        if(!isset($args['ip']) || empty($args['ip'])){
            $args['ip'] = self::get_client_ip();
        }

        if( (!isset($args['user_id']) || empty($args['user_id']))
            && is_user_logged_in() ){
            $args['user_id'] = get_current_user_id()? get_current_user_id() : null;
        }

        if(!isset($args['page']) || empty($args['page'])){
            $args['page'] = 0;
        }

        $data = array(
            'user_id'   => $args['user_id'],
            'ip'        => substr($args['ip'], 0, 45),
            'visited'   => $args['visited'],
            'page'      => $args['page']
        );

        $format = array('%d','%s','%s','%d');

        if($is_tax){
            $data['term_id'] = isset($args['term_id'])? $args['term_id'] : null;
            $format[] = '%d';
            $data['taxonomy'] = isset($args['taxonomy'])? substr($args['taxonomy'], 0, 32) : null;
            $format[] = '%s';
        }else{
            $data['is_home'] = isset($args['is_home'])? $args['is_home'] : 0;
            $format[] = '%d';
            $data['post_id'] = isset($args['post_id'])? $args['post_id'] : null;
            $format[] = '%d';
            $data['archive_ptype'] = isset($args['archive_ptype'])? substr($args['archive_ptype'], 0, 45)  : null;
            $format[] = '%s';
            $data['404_query'] = isset($args['404_query'])? substr($args['404_query'], 0, 45) : null;
            $format[] = '%s';
            $data['search_query'] = isset($args['search_query'])? substr($args['search_query'], 0, 45) : null;
            $format[] = '%s';
            $data['author_archive'] = isset($args['author_archive'])? $args['author_archive'] : null;
            $format[] = '%d';
            $data['date_archive'] = isset($args['date_archive'])? substr($args['date_archive'], 0, 45) : null;
            $format[] = '%s';
        }

        //TODO prettify this

        return $wpdb->insert(
            $table,
            $data,
            $format
            );

    }


    static private function get_client_ip(){

        $ipaddress = '';

        if (isset($_SERVER['HTTP_CLIENT_IP']) && $ipaddress = self::validate_ip($_SERVER['HTTP_CLIENT_IP'])) :
        elseif(isset($_SERVER['HTTP_X_FORWARDED_FOR']) && $ipaddress = self::validate_ip($_SERVER['HTTP_X_FORWARDED_FOR'])) :
        elseif(isset($_SERVER['HTTP_X_FORWARDED']) && $ipaddress = self::validate_ip($_SERVER['HTTP_X_FORWARDED'])) :
        elseif(isset($_SERVER['HTTP_FORWARDED_FOR']) && $ipaddress = self::validate_ip($_SERVER['HTTP_FORWARDED_FOR'])) :
        elseif(isset($_SERVER['HTTP_FORWARDED']) && $ipaddress = self::validate_ip($_SERVER['HTTP_FORWARDED'])) :
        elseif(isset($_SERVER['REMOTE_ADDR']) && $ipaddress = self::validate_ip($_SERVER['REMOTE_ADDR'])) :
        else :
            $ipaddress = 'UNKNOWN';
        endif;

        return $ipaddress;

    }

    static private function validate_ip($ip){

        $ip = trim($ip);


        /**
         * Check if valid and public IPv4 or IPv6
         */
        if(!filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE))
            return false;

        return $ip;
    }

}