<?php

declare(strict_types=1);

namespace BlackBonjour\SlimRouteRegistry\Contract;

use BlackBonjour\SlimRouteRegistry\Contract\Exception\ClassProviderExceptionInterface;

interface ClassProviderInterface
{
    /**
     * @return list<class-string>
     * @throws ClassProviderExceptionInterface
     */
    public function provideClasses(string $path): array;
}
