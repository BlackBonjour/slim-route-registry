<?php

declare(strict_types=1);

namespace BlackBonjour\SlimRouteRegistry;

use BlackBonjour\SlimRouteRegistry\Contract\ClassProviderInterface;
use BlackBonjour\SlimRouteRegistry\Contract\RouteRegistryInterface;
use BlackBonjour\SlimRouteRegistry\Exception\ClassReflectionException;
use BlackBonjour\SlimRouteRegistry\Exception\RedirectExceptionRoute;
use Psr\Container\ContainerInterface;
use ReflectionAttribute;
use ReflectionClass;
use ReflectionException;
use ReflectionMethod;
use Slim\App;

/**
 * @implements RouteRegistryInterface<ContainerInterface>
 */
final readonly class RouteRegistry implements RouteRegistryInterface
{
    /**
     * @param array<string> $paths
     */
    public function __construct(
        private array $paths,
        private ClassProviderInterface $classProvider = new ComposerClassProvider(),
    ) {}

    public function register(App $app): void
    {
        foreach ($this->paths as $path) {
            foreach ($this->classProvider->provideClasses($path) as $class) {
                $this->registerClassRoutes($app, $class);
            }
        }
    }

    /**
     * @param App<ContainerInterface> $app
     * @param class-string            $class
     *
     * @throws ClassReflectionException
     * @throws RedirectExceptionRoute
     */
    private function registerClassRoutes(App $app, string $class): void
    {
        try {
            $reflectionClass = new ReflectionClass($class);
        } catch (/** @phpstan-ignore-line */ ReflectionException $e) {
            throw ClassReflectionException::fromClass($class, $e);
        }

        // Register redirects defined on the class before registering actual routes
        $this->registerRedirects($app, $reflectionClass->getAttributes(Redirect::class));

        // Register routes defined on the class
        $classAttributes = $reflectionClass->getAttributes(Route::class);

        foreach ($classAttributes as $attribute) {
            /** @var Route $routeAttribute */
            $routeAttribute = $attribute->newInstance();

            // Register redirects defined within the route
            $this->registerRedirects($app, $routeAttribute->redirects, $routeAttribute);

            // Register the class as a route
            $this->registerRoute($app, $routeAttribute, $class, null);
        }

        // Register routes on public methods
        foreach ($reflectionClass->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
            $this->registerMethodRoutes($app, $class, $method);
        }
    }

    /**
     * @param App<ContainerInterface> $app
     * @param class-string            $class
     *
     * @throws RedirectExceptionRoute
     */
    private function registerMethodRoutes(App $app, string $class, ReflectionMethod $method): void
    {
        $methodsAttributes = $method->getAttributes(Route::class);

        // Register redirects defined on the method before registering actual routes
        $this->registerRedirects($app, $method->getAttributes(Redirect::class));

        // Register routes defined on rhe method
        foreach ($methodsAttributes as $attribute) {
            /** @var Route $routeAttribute */
            $routeAttribute = $attribute->newInstance();

            // Register redirects defined within the route
            $this->registerRedirects($app, $routeAttribute->redirects, $routeAttribute);

            // Register the method as a route
            $this->registerRoute($app, $routeAttribute, $class, $method->getName());
        }
    }

    /**
     * @param App<ContainerInterface> $app
     * @param class-string            $class
     */
    private function registerRoute(App $app, Route $routeAttribute, string $class, ?string $method): void
    {
        $route = $app->map(
            $routeAttribute->methods,
            $routeAttribute->path,
            $method ? sprintf('%s:%s', $class, $method) : $class,
        );

        if ($routeAttribute->arguments) {
            $route->setArguments($routeAttribute->arguments);
        }

        if ($routeAttribute->middlewares) {
            foreach ($routeAttribute->middlewares as $middleware) {
                $route->add($middleware);
            }
        }

        if ($routeAttribute->name) {
            $route->setName($routeAttribute->name);
        }
    }

    /**
     * @param App<ContainerInterface>                       $app
     * @param array<ReflectionAttribute<Redirect>|Redirect> $attributes
     *
     * @throws RedirectExceptionRoute
     */
    private function registerRedirects(App $app, array $attributes, ?Route $route = null): void
    {
        foreach ($attributes as $attribute) {
            if ($attribute instanceof ReflectionAttribute) {
                /** @var Redirect $redirect */
                $redirect = $attribute->newInstance();
            } else {
                $redirect = $attribute;
            }

            $to = $redirect->to ?? $route?->path;

            if ($to === null) {
                throw RedirectExceptionRoute::fromRedirect($redirect);
            }

            $app->redirect($redirect->from, $to, $redirect->status);
        }
    }
}
