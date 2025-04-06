<?php

declare(strict_types=1);

namespace BlackBonjour\SlimRouteRegistry;

use BlackBonjour\NamespaceHandler\NamespaceHandler;
use InvalidArgumentException;
use Psr\Container\ContainerInterface;
use ReflectionAttribute;
use ReflectionClass;
use ReflectionException;
use ReflectionMethod;
use RuntimeException;
use Slim\App;

final readonly class RouteRegistry
{
    /**
     * @param array<string> $namespaces
     */
    public function __construct(
        private array $namespaces,
        private NamespaceHandler $namespaceHandler,
    ) {}

    /**
     * @param App<ContainerInterface> $app
     *
     * @throws InvalidArgumentException
     * @throws ReflectionException
     * @throws RuntimeException
     */
    public function register(App $app): void
    {
        foreach ($this->namespaces as $namespace) {
            foreach ($this->namespaceHandler->getClassNamesByNamespace($namespace) as $class) {
                $this->registerClassRoutes($app, $class);
            }
        }
    }

    /**
     * @param App<ContainerInterface> $app
     * @param class-string            $class
     *
     * @throws InvalidArgumentException
     * @throws ReflectionException
     */
    private function registerClassRoutes(App $app, string $class): void
    {
        $reflectionClass = new ReflectionClass($class);

        // Register redirects defined on the class before registering actual routes
        $this->registerRedirects($app, $reflectionClass->getAttributes(Redirect::class));

        // Register routes defined on the class
        $classAttributes = $reflectionClass->getAttributes(Route::class);

        foreach ($classAttributes as $attribute) {
            /** @var Route $routeAttribute */
            $routeAttribute = $attribute->newInstance();

            // Register redirects defined within the route
            $this->registerRedirects($app, $routeAttribute->redirects, $routeAttribute);

            // Register class as route
            $route = $app->map($routeAttribute->methods, $routeAttribute->path, $class);

            if ($routeAttribute->name) {
                $route->setName($routeAttribute->name);
            }
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
     * @throws InvalidArgumentException
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

            // Register method as route
            $route = $app->map(
                $routeAttribute->methods,
                $routeAttribute->path,
                sprintf('%s:%s', $class, $method->getName()),
            );

            if ($routeAttribute->name) {
                $route->setName($routeAttribute->name);
            }
        }
    }

    /**
     * @param App<ContainerInterface>                       $app
     * @param array<ReflectionAttribute<Redirect>|Redirect> $attributes
     *
     * @throws InvalidArgumentException
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
                throw new InvalidArgumentException(
                    'Redirect attribute requires a "to" parameter unless attached to a Route attribute!',
                );
            }

            $app->redirect($redirect->from, $to, $redirect->status);
        }
    }
}
