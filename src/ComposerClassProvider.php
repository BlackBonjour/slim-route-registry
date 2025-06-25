<?php

declare(strict_types=1);

namespace BlackBonjour\SlimRouteRegistry;

use BlackBonjour\SlimRouteRegistry\Contract\ClassProviderInterface;
use Composer\ClassMapGenerator\ClassMapGenerator;
use RuntimeException;

final class ComposerClassProvider implements ClassProviderInterface
{
    public function provideClasses(string $path): array
    {
        if (is_dir($path) === false) {
            throw Exception\DirectoryNotFoundExceptionClass::fromPath($path);
        }

        $classes = [];

        try {
            foreach (array_keys(ClassMapGenerator::createMap($path)) as $class) {
                $classes[] = $class;
            }
        } catch (RuntimeException $e) {
            throw Exception\ClassMapGeneratorExceptionClass::fromPath($path, $e);
        }

        return $classes;
    }
}
