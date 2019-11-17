<?php

Class AwesomeTrackerApi {

    /**
     * Namespace for this API
     */
    const NAME_SPACE = 'awesome-tracker/v1';

    public static function register_routes() {

        register_rest_route(self::NAME_SPACE, '/route/', array(
            'methods' => array('POST'),
            'callback' => 'AwesomeTrackerApi::add_route',
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
            'args' => array(
                'route' => array(
                    'required' => true,
                    'validate_callback' => 'AwesomeTrackerApi::validate_route_ID'
                )
            )
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

        $response = array(
            'status' => 'OK',
            'payload' => array()
        );

        $routeToSave = $request->get_param('route');

        $routeObj = AwesomeTracker_Route::get_instance($routeToSave['apiRoute'], $routeToSave['method']);

        $apiArg = $routeToSave['apiArg'];

        if (isset($routeToSave['at_other']) && !empty($routeToSave['at_other']))
            $apiArg = $routeToSave['at_other'];

        $routeObj->apiArg = $apiArg;

        if ($routeObj->save()) {
            $response['payload']['ID'] = $routeObj->ID;

            return $response;
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

        $response = array(
            'status' => 'OK',
            'payload' => array()
        );

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

        $response = array(
            'status' => 'OK',
            'payload' => ''
        );

        $routeToSave = $request->get_param('route');

        $routeOnDB = AwesomeTracker_Route::get_instance($routeToSave['ID']);

        if (!$routeOnDB->delete())
            return new WP_Error('cant_delete',
                                __('There was an error trying to delete the data', AwesomeTracker::TEXT_DOMAIN)
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
