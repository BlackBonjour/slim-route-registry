<?php

declare(strict_types=1);

namespace BlackBonjour\SlimRouteRegistry\Exception;

use BlackBonjour\SlimRouteRegistry\Contract\Exception\ClassProviderExceptionInterface;
use RuntimeException;

final class DirectoryNotFoundExceptionClass extends RuntimeException implements ClassProviderExceptionInterface
{
    public static function fromPath(string $path): self
    {
        return new self(sprintf('Directory not found: %s', $path));
    }
}
