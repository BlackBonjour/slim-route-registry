<?php

declare(strict_types=1);

namespace BlackBonjourTest\SlimRouteRegistry;

use BlackBonjour\SlimRouteRegistry\Route;

/**
 * Example handler with middleware support for testing.
 */
#[Route('GET', '/middleware', 'middleware-route', middlewares: [TestMiddleware::class])]
final class MiddlewareHandler {}
