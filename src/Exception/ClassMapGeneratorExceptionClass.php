<?php

declare(strict_types=1);

namespace BlackBonjour\SlimRouteRegistry\Exception;

use BlackBonjour\SlimRouteRegistry\Contract\Exception\ClassProviderExceptionInterface;
use RuntimeException;
use Throwable;

final class ClassMapGeneratorExceptionClass extends RuntimeException implements ClassProviderExceptionInterface
{
    public static function fromPath(string $path, Throwable $previous): self
    {
        return new self(sprintf('Class map generator failed for path: %s', $path), previous: $previous);
    }
}
