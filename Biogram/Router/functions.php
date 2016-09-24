<?php

namespace Biogram\Router;

if (!function_exists('Biogram\Router\simpleDispatcher')) {
    /**
     * @param callable $routeDefinitionCallback
     * @param array $options
     *
     * @return Dispatcher
     */
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

    /**
     * @param callable $routeDefinitionCallback
     * @param array $options
     *
     * @return Dispatcher
     */
    function cachedDispatcher(callable $routeDefinitionCallback, array $options = []) {
        $options += [
            'routeParser' => 'Biogram\\Router\\RouteParser\\Std',
            'dataGenerator' => 'Biogram\\Router\\DataGenerator\\GroupCountBased',
            'dispatcher' => 'Biogram\\Router\\Dispatcher\\GroupCountBased',
            'routeCollector' => 'Biogram\\Router\\RouteCollector',
            'cacheDisabled' => false,
        ];

        if (!isset($options['cacheFile'])) {
            throw new \LogicException('Must specify "cacheFile" option');
        }

        if (!$options['cacheDisabled'] && file_exists($options['cacheFile'])) {
            $dispatchData = require $options['cacheFile'];
            if (!is_array($dispatchData)) {
                throw new \RuntimeException('Invalid cache file "' . $options['cacheFile'] . '"');
            }
            return new $options['dispatcher']($dispatchData);
        }

        $routeCollector = new $options['routeCollector'](
            new $options['routeParser'], new $options['dataGenerator']
        );
        $routeDefinitionCallback($routeCollector);

        /** @var RouteCollector $routeCollector */
        $dispatchData = $routeCollector->getData();
        file_put_contents(
            $options['cacheFile'],
            '<?php return ' . var_export($dispatchData, true) . ';'
        );

        return new $options['dispatcher']($dispatchData);
    }
}
