<?php

declare(strict_types=1);

namespace BlackBonjourTest\SlimRouteRegistry;

use BlackBonjour\NamespaceHandler\NamespaceHandler;
use BlackBonjour\SlimRouteRegistry\RouteRegistry;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Slim\App;
use Slim\Routing\Route;
use Throwable;

final class RouteRegistryTest extends TestCase
{
    /**
     * Verifies the registration of routes and handlers within a specified namespace.
     *
     * @throws Throwable
     */
    public function testRegister(): void
    {
        $app = $this->createMock(App::class);
        $app
            ->expects($this->once())
            ->method('map')
            ->with(['GET'], '/', 'BlackBonjourTest\\SlimRouteRegistry\\ExampleHandler');

        $app
            ->expects($this->once())
            ->method('redirect')
            ->with('/old', '/new', 302);

        $namespaceHandler = $this->createMock(NamespaceHandler::class);
        $namespaceHandler
            ->expects($this->once())
            ->method('getClassNamesByNamespace')
            ->with('BlackBonjourTest\\SlimRouteRegistry')
            ->willReturn(['BlackBonjourTest\\SlimRouteRegistry\\ExampleHandler']);

        $routeRegistry = new RouteRegistry(['BlackBonjourTest\\SlimRouteRegistry'], $namespaceHandler);
        $routeRegistry->register($app);
    }

    /**
     * Verifies the behavior of the RouteRegistry when attempting to register classes in a namespace that contains no classes.
     *
     * @throws Throwable
     */
    public function testRegisterNoClassesInNamespace(): void
    {
        $namespaceHandler = $this->createMock(NamespaceHandler::class);
        $namespaceHandler
            ->expects($this->once())
            ->method('getClassNamesByNamespace')
            ->with('TestNamespace')
            ->willReturn([]);

        $routeRegistry = new RouteRegistry(['TestNamespace'], $namespaceHandler);
        $routeRegistry->register($this->createMock(App::class));
    }

    /**
     * Verifies that an exception is thrown when attempting to register a route with an invalid redirect.
     *
     * @throws Throwable
     */
    public function testRegisterThrowsExceptionForInvalidRedirect(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage(
            'Redirect attribute requires a "to" parameter unless attached to a Route attribute!',
        );

        $namespaceHandler = $this->createMock(NamespaceHandler::class);
        $namespaceHandler
            ->expects($this->once())
            ->method('getClassNamesByNamespace')
            ->with('BlackBonjourTest\\SlimRouteRegistry')
            ->willReturn(['BlackBonjourTest\\SlimRouteRegistry\\InvalidRedirectHandler']);

        $routeRegistry = new RouteRegistry(['BlackBonjourTest\\SlimRouteRegistry'], $namespaceHandler);
        $routeRegistry->register($this->createMock(App::class));
    }

    /**
     * Verifies the registration of routes with `arguments` support.
     *
     * @throws Throwable
     */
    public function testRegisterWithArguments(): void
    {
        $route = $this->createMock(Route::class);
        $route
            ->expects($this->once())
            ->method('setArguments')
            ->with(['arg1' => 'value1', 'arg2' => 'value2']);

        $route
            ->expects($this->once())
            ->method('setName')
            ->with('arguments-route');

        $app = $this->createMock(App::class);
        $app
            ->expects($this->once())
            ->method('map')
            ->with(['GET'], '/arguments', 'BlackBonjourTest\\SlimRouteRegistry\\ArgumentsHandler')
            ->willReturn($route);

        $namespaceHandler = $this->createMock(NamespaceHandler::class);
        $namespaceHandler
            ->expects($this->once())
            ->method('getClassNamesByNamespace')
            ->with('BlackBonjourTest\\SlimRouteRegistry')
            ->willReturn(['BlackBonjourTest\\SlimRouteRegistry\\ArgumentsHandler']);

        $routeRegistry = new RouteRegistry(['BlackBonjourTest\\SlimRouteRegistry'], $namespaceHandler);
        $routeRegistry->register($app);
    }

    /**
     * Verifies that no arguments are set when the `arguments` array is empty.
     *
     * @throws Throwable
     */
    public function testRegisterWithEmptyArguments(): void
    {
        $route = $this->createMock(Route::class);
        $route->expects($this->never())->method('setArguments');

        $app = $this->createMock(App::class);
        $app
            ->expects($this->once())
            ->method('map')
            ->with(['GET'], '/', 'BlackBonjourTest\\SlimRouteRegistry\\ExampleHandler')
            ->willReturn($route);

        $namespaceHandler = $this->createMock(NamespaceHandler::class);
        $namespaceHandler
            ->expects($this->once())
            ->method('getClassNamesByNamespace')
            ->with('BlackBonjourTest\\SlimRouteRegistry')
            ->willReturn(['BlackBonjourTest\\SlimRouteRegistry\\ExampleHandler']);

        $routeRegistry = new RouteRegistry(['BlackBonjourTest\\SlimRouteRegistry'], $namespaceHandler);
        $routeRegistry->register($app);
    }

    /**
     * Verifies the registration of routes with middleware support.
     *
     * @throws Throwable
     */
    public function testRegisterWithMiddleware(): void
    {
        $route = $this->createMock(Route::class);
        $route
            ->expects($this->once())
            ->method('add')
            ->with('BlackBonjourTest\\SlimRouteRegistry\\TestMiddleware');

        $route
            ->expects($this->once())
            ->method('setName')
            ->with('middleware-route');

        $app = $this->createMock(App::class);
        $app
            ->expects($this->once())
            ->method('map')
            ->with(['GET'], '/middleware', 'BlackBonjourTest\\SlimRouteRegistry\\MiddlewareHandler')
            ->willReturn($route);

        $namespaceHandler = $this->createMock(NamespaceHandler::class);
        $namespaceHandler
            ->expects($this->once())
            ->method('getClassNamesByNamespace')
            ->with('BlackBonjourTest\\SlimRouteRegistry')
            ->willReturn(['BlackBonjourTest\\SlimRouteRegistry\\MiddlewareHandler']);

        $routeRegistry = new RouteRegistry(['BlackBonjourTest\\SlimRouteRegistry'], $namespaceHandler);
        $routeRegistry->register($app);
    }
}
