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

        return self::log(array('query_404' => $query));

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

    /**
     * Filter in the middle of an api call used to track the visit if conditions apply
     *
     * @param WP_HTTP_Response|WP_Error $response Result to send to the client. Usually a WP_REST_Response or WP_Error.
     * @param array                     $handler  Route handler used for the request.
     * @param WP_REST_Request           $request  Request used to generate the response.
     *
     * @return WP_HTTP_Response|WP_Error $response
     */
    static public function add_api_visit($response, $handler, $request){

        $currentRoute = $request->get_route();
        $currentMethod = strtolower($request->get_method());

        $allRoutes = AwesomeTracker_Route::get_current_routes(true);

        foreach ($allRoutes as $route => $methods ){
            if(!preg_match('@^' . $route . '$@i', $currentRoute))
                continue;

            foreach ($methods as $method => $fieldToGet){
                if(strpos($method,$currentMethod) === false)
                    continue;

                self::log_api_visit($request, $fieldToGet);

                return $response;
            }
        }

        return $response;
    }

    /**
     * @param WP_REST_Request $request  Request used to generate the response.
     * @param string          $fieldToGet Aditional field to get as post ID.
     */
    static private function log_api_visit($request, $fieldToGet){

        $argsToLog = array(
            'api_route' => $request->get_route(),
            'api_method' => $request->get_method(),
        );

        if(!empty($fieldToGet) && !is_numeric($fieldToGet)){
            $ID = $request->get_param( $fieldToGet );
            if(is_numeric($ID) && !empty($ID))
                $argsToLog['post_id'] = $ID;
        }

        self::log($argsToLog);
    }

    static private function log($args = array()){


        if( (!isset($args['user_id']) || empty($args['user_id']))
            && is_user_logged_in() ){
            $args['user_id'] = get_current_user_id()? get_current_user_id() : null;
        }

        $record = new AwesomeTracker_Record($args);

        if($record->save())
            return true;

        return false;
    }

}
