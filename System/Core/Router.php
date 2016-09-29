<?php

    class Router
    {
        public static $routes = array();
        public static $methods = array();
        public static $callbacks = array();

        // Defines a route & callback function
        public static function run()
        {
            $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
            $method = $_SERVER['REQUEST_METHOD'];
            $found_route = false;

            self::$routes = preg_replace('/\/+/', '/', self::$routes);

            // Check if route is defined without regex
            if (in_array($uri, self::$routes)) {

                // find route index key in all application defined routes
                $i = array_keys(self::$routes, $uri)[0];

                // if request method matches the route's method
                if (self::$methods[$i] == $method) {
                    $found_route = true;
                    call_user_func(self::$callbacks[$i]);
                }
            }

            // if route not found
            if ($found_route == false) {
                die("Route Not Found!");
            }
        }

        //  create function and call it based on methods (get, post, . . . )
        public function __call($method, $params)
        {
            $uri = dirname($_SERVER['PHP_SELF']) . '/' . $params[0];
            $callback = $params[1];
            array_push(self::$routes, $uri);
            array_push(self::$methods, strtoupper($method));
            array_push(self::$callbacks, $callback);
        }

    }