<?php

declare(strict_types=1);

namespace BlackBonjour\SlimRouteRegistry\Contract;

use BlackBonjour\SlimRouteRegistry\Contract\Exception\ClassProviderExceptionInterface;
use BlackBonjour\SlimRouteRegistry\Contract\Exception\RouteRegistryExceptionInterface;
use Psr\Container\ContainerInterface;
use Slim\App;

/**
 * @template T of ContainerInterface
 */
interface RouteRegistryInterface
{
    /**
     * @param App<T> $app
     *
     * @throws ClassProviderExceptionInterface
     * @throws RouteRegistryExceptionInterface
     */
    public function register(App $app): void;
}
