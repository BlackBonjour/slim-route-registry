<?php

declare(strict_types=1);

namespace BlackBonjour\SlimRouteRegistry\Exception;

use BlackBonjour\SlimRouteRegistry\Contract\Exception\RouteRegistryExceptionInterface;
use RuntimeException;
use Throwable;

final class ClassReflectionException extends RuntimeException implements RouteRegistryExceptionInterface
{
    public static function fromClass(string $class, Throwable $t): self
    {
        return new self(sprintf('Class reflection failed for class "%s".', $class), previous: $t);
    }
}
