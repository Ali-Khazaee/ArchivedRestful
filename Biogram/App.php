<?php

namespace Biogram;

use Biogram\Http\MyClass;
use InvalidArgumentException;
use Biogram\Router\RouteTest;
use Biogram\Router\RouteCollector;
use Biogram\Router\Dispatcher;
/**
 * App
 * Biogram server's primary class.

 */
class App
{


    // Current version of Biogram Server
    const VERSION = '1.00';


    // testing
    public function tesClass()
    {

        $c = new MyClass();
        $c->myFunction();
    }


    public function get($pattern, $callable)
    {
        return $this->map($pattern, $callable);
    }



//    public function map($pattern, $callable)
//    {
//        echo $pattern;
//
//        if (!is_callable($callable)) {
//            throw new InvalidArgumentException('Param must be callable.');
//        }
//
//        $callable = $callable->bindTo($this);
//        $callable();
//
//    }


    // Testing FastRoute
    public function testRouter()
    {
        $route = new RouteTest();

        $dispatcher = $route->simpleDispatcher(function(RouteCollector $r) {


            $r->addRoute('GET', '/{id:\d+}', function(){
                echo "Test Ok! router is working";
            });


        });

// Fetch method and URI from somewhere
        $httpMethod = $_SERVER['REQUEST_METHOD'];
        $uri = $_SERVER['REQUEST_URI'];

// Strip query string (?foo=bar) and decode URI
        if (false !== $pos = strpos($uri, '?')) {
            $uri = substr($uri, 0, $pos);
        }
        $uri = rawurldecode($uri);

        $routeInfo = $dispatcher->dispatch($httpMethod, $uri);
        switch ($routeInfo[0]) {
            case Dispatcher::NOT_FOUND:
                echo "404 Not Found!";
                break;
            case Dispatcher::METHOD_NOT_ALLOWED:
                $allowedMethods = $routeInfo[1];
                // ... 405 Method Not Allowed
                break;
            case Dispatcher::FOUND:
                $handler = $routeInfo[1];
                $vars = $routeInfo[2];
                $handler();
                break;
        }

    }


}


