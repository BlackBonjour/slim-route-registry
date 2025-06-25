<?php

declare(strict_types=1);

namespace BlackBonjourTest\SlimRouteRegistry;

use BlackBonjour\SlimRouteRegistry\Contract\ClassProviderInterface;
use BlackBonjour\SlimRouteRegistry\Exception\RedirectExceptionRoute;
use BlackBonjour\SlimRouteRegistry\RouteRegistry;
use PHPUnit\Framework\TestCase;
use Slim\App;
use Slim\Routing\Route;
use Throwable;

final class RouteRegistryTest extends TestCase
{
    /**
     * Verifies the registration of routes and handlers within a specified directory path.
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

        $classProvider = $this->createMock(ClassProviderInterface::class);
        $classProvider
            ->expects($this->once())
            ->method('provideClasses')
            ->with('tests')
            ->willReturn(['BlackBonjourTest\\SlimRouteRegistry\\ExampleHandler']);

        $routeRegistry = new RouteRegistry(['tests'], $classProvider);
        $routeRegistry->register($app);
    }

    /**
     * Verifies the behavior of the RouteRegistry when attempting to register classes in a directory path that contains no classes.
     *
     * @throws Throwable
     */
    public function testRegisterNoClassesInDirectory(): void
    {
        $classProvider = $this->createMock(ClassProviderInterface::class);
        $classProvider
            ->expects($this->once())
            ->method('provideClasses')
            ->with('tests/TestDirectory')
            ->willReturn([]);

        $routeRegistry = new RouteRegistry(['tests/TestDirectory'], $classProvider);
        $routeRegistry->register($this->createMock(App::class));
    }

    /**
     * Verifies that an exception is thrown when attempting to register a route with an invalid redirect from a directory path.
     *
     * @throws Throwable
     */
    public function testRegisterThrowsExceptionForInvalidRedirect(): void
    {
        $this->expectException(RedirectExceptionRoute::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage(
            'The redirect attribute for "/old" requires a "to" parameter unless it is attached to a route attribute.',
        );

        $classProvider = $this->createMock(ClassProviderInterface::class);
        $classProvider
            ->expects($this->once())
            ->method('provideClasses')
            ->with('tests')
            ->willReturn(['BlackBonjourTest\\SlimRouteRegistry\\InvalidRedirectHandler']);

        $routeRegistry = new RouteRegistry(['tests'], $classProvider);
        $routeRegistry->register($this->createMock(App::class));
    }

    /**
     * Verifies the registration of routes with `arguments` support from a directory path.
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

        $classProvider = $this->createMock(ClassProviderInterface::class);
        $classProvider
            ->expects($this->once())
            ->method('provideClasses')
            ->with('tests')
            ->willReturn(['BlackBonjourTest\\SlimRouteRegistry\\ArgumentsHandler']);

        $routeRegistry = new RouteRegistry(['tests'], $classProvider);
        $routeRegistry->register($app);
    }

    /**
     * Verifies that no arguments are set when the `arguments` array is empty for routes from a directory path.
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

        $classProvider = $this->createMock(ClassProviderInterface::class);
        $classProvider
            ->expects($this->once())
            ->method('provideClasses')
            ->with('tests')
            ->willReturn(['BlackBonjourTest\\SlimRouteRegistry\\ExampleHandler']);

        $routeRegistry = new RouteRegistry(['tests'], $classProvider);
        $routeRegistry->register($app);
    }

    /**
     * Verifies the registration of routes with middleware support from a directory path.
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

        $classProvider = $this->createMock(ClassProviderInterface::class);
        $classProvider
            ->expects($this->once())
            ->method('provideClasses')
            ->with('tests')
            ->willReturn(['BlackBonjourTest\\SlimRouteRegistry\\MiddlewareHandler']);

        $routeRegistry = new RouteRegistry(['tests'], $classProvider);
        $routeRegistry->register($app);
    }
}
