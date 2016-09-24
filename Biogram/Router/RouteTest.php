<?php


namespace Biogram\Router;


class RouteTest {


    function simpleDispatcher(callable $routeDefinitionCallback, array $options = []) {
        $options += [
            'routeParser' => 'Biogram\\Router\\RouteParser\\Std',
            'dataGenerator' => 'Biogram\\Router\\DataGenerator\\GroupCountBased',
            'dispatcher' => 'Biogram\\Router\\Dispatcher\\GroupCountBased',
            'routeCollector' => 'Biogram\\Router\\RouteCollector',
        ];

        /** @var RouteCollector $routeCollector */
        $routeCollector = new $options['routeCollector'](
            new $options['routeParser'], new $options['dataGenerator']
        );
        $routeDefinitionCallback($routeCollector);

        return new $options['dispatcher']($routeCollector->getData());
    }

}