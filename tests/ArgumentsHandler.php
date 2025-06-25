<?php

declare(strict_types=1);

namespace BlackBonjourTest\SlimRouteRegistry;

use BlackBonjour\SlimRouteRegistry\Route;

/**
 * Example handler with `arguments` support for testing.
 */
#[Route('GET', '/arguments', 'arguments-route', arguments: ['arg1' => 'value1', 'arg2' => 'value2'])]
final class ArgumentsHandler {}
