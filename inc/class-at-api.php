<?php

class AwesomeTrackerApi {

    /**
     * Namespace for this API
     */
    const NAME_SPACE = 'awesome-tracker/v1';

    public static function register_routes() {

        register_rest_route(self::NAME_SPACE, '/route/', array(
            'methods' => array('POST'),
            'callback' => 'AwesomeTrackerApi::add_route',
            'permission_callback' => 'AwesomeTrackerApi::check_permission',
            'args' => array(
                'route' => array(
                    'required' => true,
                    'validate_callback' => 'AwesomeTrackerApi::validate_route'
                )
            )
        ));

        register_rest_route(self::NAME_SPACE, '/route/', array(
            'methods' => array('PUT', 'PATCH'),
            'callback' => 'AwesomeTrackerApi::update_route',
            'permission_callback' => 'AwesomeTrackerApi::check_permission',
            'args' => array(
                'route' => array(
                    'required' => true,
                    'validate_callback' => 'AwesomeTrackerApi::validate_route_with_ID'
                )
            )
        ));

        register_rest_route(self::NAME_SPACE, '/route/', array(
            'methods' => array('DELETE'),
            'callback' => 'AwesomeTrackerApi::delete_route',
            'permission_callback' => 'AwesomeTrackerApi::check_permission',
            'args' => array(
                'route' => array(
                    'required' => true,
                    'validate_callback' => 'AwesomeTrackerApi::validate_route_ID'
                )
            )
        ));

        register_rest_route(self::NAME_SPACE, '/options/', array(
            'methods' => array('POST'),
            'callback' => 'AwesomeTrackerApi::save_settings',
            'permission_callback' => 'AwesomeTrackerApi::check_permission'
        ));
    }

    /**
     * Handles API save request
     *
     * array(
     *   '/wp/v2/posts/1' => array(
     *     'get' => 0,
     *     'post-put-patch' => 'ID'
     *   )
     * );
     *
     * @param WP_REST_Request $request
     *
     * @return array|WP_Error
     */
    public static function add_route($request) {

        $routeToSave = $request->get_param('route');

        $routeObj = AwesomeTracker_Route::get_instance($routeToSave['apiRoute'], $routeToSave['method']);

        $apiArg = $routeToSave['apiArg'];

        if (isset($routeToSave['at_other']) && !empty($routeToSave['at_other']))
            $apiArg = $routeToSave['at_other'];

        $routeObj->apiArg = $apiArg;

        if ($routeObj->save()) {

            return self::getResponse(array('ID' => $routeObj->ID));
        }

        return new WP_Error('cant_save',
                            __('There was an error trying to save the data', AwesomeTracker::TEXT_DOMAIN)
        );
    }

    /**
     * @param WP_REST_Request $request
     *
     * @return array|WP_Error
     */
    public static function update_route($request) {

        $response = self::delete_route($request);

        if(is_wp_error($response))
            return $response;

        return self::add_route($request);
    }

    /**
     * Handles API delete request
     *
     * @param WP_REST_Request $request
     *
     * @return array|WP_Error
     */
    public static function delete_route($request) {

        $routeToSave = $request->get_param('route');

        $routeOnDB = AwesomeTracker_Route::get_instance($routeToSave['ID']);

        if (!$routeOnDB->delete())
            return new WP_Error('cant_delete',
                                __('There was an error trying to delete the data', AwesomeTracker::TEXT_DOMAIN)
            );

        return self::getResponse();
    }

    /**
     * @param WP_REST_Request $request
     *
     * @return array|WP_Error
     */
    public static function save_settings($request){

        $recordsDB = $request->get_param('recordsDB');

        if(!is_null($recordsDB)){
            return self::save_recordsDB($recordsDB);
        }

        $deleteAllRecords = $request->get_param('deleteAllRecords');

        if(!is_null($deleteAllRecords) && !empty($deleteAllRecords)){
            AwesomeTracker_Record::delete_all_records();
            return self::getResponse();
        }

        return self::getResponse();
    }

    private static function save_recordsDB($recordsDB){

        $error = new WP_Error('cant_save',
                              __('There was an error trying to save the data', AwesomeTracker::TEXT_DOMAIN)
        );

        if(!is_numeric($recordsDB))
            return $error;

        $recordsDB = +$recordsDB;
        if(!is_int($recordsDB) || $recordsDB < 0)
            return $error;

        AwesomeTrackerOptions::set_daysToPurgeDB($recordsDB);

        if($recordsDB > 0){

            AwesomeTracker_Record::delete_old_recordsDB($recordsDB);

        }

        return self::getResponse();
    }

    private static function getResponse($payload = ''){
        $response = array(
            'status' => 'OK',
            'payload' => $payload
        );

        return $response;
    }

    public static function get_api_routelist() {

        $rest_server = rest_get_server();

        $routes = $rest_server->get_routes();

        $to_js_routes = array();

        foreach ($routes as $key => $route) {
            $to_js_routes[$key] = array();
            foreach ($route as $rcontent) {
                $methods = array_keys($rcontent['methods']);
                $to_js_routes[$key][strtolower(implode("-", $methods))] = array(
                    'methods' => implode(", ", $methods),
                    'args' => isset($rcontent['args']) ? array_combine(array_keys($rcontent['args']), array_keys($rcontent['args'])) : null
                );
            }
        }

        return $to_js_routes;
    }

    /**
     * @param WP_REST_Request $request  Request used to generate the response.
     *
     * @return bool
     */
    public static function check_permission($request){
        $allow = false;
        if(is_user_logged_in() && current_user_can('manage_options'))
            $allow = true;

        return apply_filters('awesometracker_api_permission_callback', $allow, $request);
    }

    public static function validate_route($route, $request, $key) {

        if (empty($route['apiRoute']) || empty($route['method']))
            return false;
        if ($route['apiArg'] == 'at_other' && empty($route['at_other']))
            return false;

        $wordpressRouteList = self::get_api_routelist();

        if (!isset($wordpressRouteList[$route['apiRoute']]) || !isset($wordpressRouteList[$route['apiRoute']][$route['method']]))
            return false;

        return true;
    }

    public static function validate_route_with_ID($route, $request, $key) {

        if (empty($route['ID']))
            return false;

        return self::validate_route($route, $request, $key);
    }

    public static function validate_route_ID($route, $request, $key) {

        if (empty($route['ID']))
            return false;

        return true;
    }

}
