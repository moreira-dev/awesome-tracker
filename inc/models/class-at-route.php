<?php

class AwesomeTracker_Route {

    /**
     * Delimiter for ID construction
     */
    const DELIMITER = '_-_';

    const KEY_OPTION = 'awesometracker_routes';

    /**
     * @var string
     */
    public $ID;

    public $apiRoute;

    public $method;

    public $apiArg;


    /**
     * Return the list of configured routes from DB
     *
     * @param bool $raw If raw, instead of an array of AwesomeTracker_Route objects,
     *                  the array from the DB will be returned
     *
     * @return array|bool|mixed
     */
    public static function get_current_routes($raw = false) {

        if($raw)
            return get_option(self::KEY_OPTION, array());

        $routes = wp_cache_get(self::KEY_OPTION, 'awesome-tracker-td');

        if ($routes !== false)
            return $routes;

        $routes = array();
        $dbRoutes = get_option(self::KEY_OPTION, array());

        foreach ($dbRoutes as $apiRoute => $methods)
            foreach ($methods as $method => $apiArg) {
                $objRoute = new AwesomeTracker_Route($apiRoute, $method, $apiArg);
                $routes[$objRoute->ID] = $objRoute;
            }

        wp_cache_set(self::KEY_OPTION, $routes, 'awesome-tracker-td');

        return $routes;
    }


    /**
     * Get existing instance from DB or create a new one
     *
     * @param string $apiRouteOrID
     * @param string $method
     *
     * @return AwesomeTracker_Route
     */
    public static function get_instance($apiRouteOrID, $method = '') {

        if (self::isID($apiRouteOrID)) {
            $data = self::extractDataFromID($apiRouteOrID);
            if ($data) {
                $apiRouteOrID = $data['apiRoute'];
                $method = $data['method'];
            }
        }

        $ID = self::generate_ID($apiRouteOrID, $method);

        $routes = self::get_current_routes();

        if (isset($routes[$ID]))
            return $routes[$ID];

        return new AwesomeTracker_Route($apiRouteOrID, $method);
    }


    /**
     * Constructor.
     *
     * @param string          $apiRoute
     * @param string          $method
     * @param string|int|null $apiArg
     */
    public function __construct($apiRoute, $method, $apiArg = null) {

        $this->apiRoute = $apiRoute;
        $this->method = $method;
        $this->apiArg = $apiArg;

        $this->ID = self::generate_ID($apiRoute, $method);

    }

    public function save() {

        $dbRoutes = get_option(self::KEY_OPTION, array());

        if (!isset($dbRoutes[$this->apiRoute]))
            $dbRoutes[$this->apiRoute] = array();

        $dbRoutes[$this->apiRoute][$this->method] = $this->apiArg;

        if (!update_option(self::KEY_OPTION, $dbRoutes))
            return false;

        $newRoutes = self::get_current_routes();

        $newRoutes[$this->ID] = $this;

        wp_cache_set(self::KEY_OPTION, $newRoutes, 'awesome-tracker-td');

        return true;

    }

    public function delete() {

        $dbRoutes = get_option(self::KEY_OPTION, array());

        if (!isset($dbRoutes[$this->apiRoute]))
            return true;

        unset($dbRoutes[$this->apiRoute][$this->method]);

        if (empty($dbRoutes[$this->apiRoute]))
            unset($dbRoutes[$this->apiRoute]);

        if (!update_option(self::KEY_OPTION, $dbRoutes))
            return false;

        $newRoutes = self::get_current_routes();

        unset($newRoutes[$this->ID]);

        wp_cache_set(self::KEY_OPTION, $newRoutes, 'awesome-tracker-td');

        return true;

    }

    private static function generate_ID($apiRoute, $method) {

        return $apiRoute . self::DELIMITER . $method;
    }

    private static function isID($IDtoCheck) {

        // 0 is a valid false in this case
        return (bool)strpos($IDtoCheck, self::DELIMITER);
    }

    /**
     * @param $ID
     *
     * @return array|false Returns an array with apiRoute and method or false on error
     */
    private static function extractDataFromID($ID) {

        $data = explode(self::DELIMITER, $ID);

        if (!isset($data[1]))
            return false;

        return array(
            'apiRoute' => $data[0],
            'method' => $data[1]
        );
    }

}
