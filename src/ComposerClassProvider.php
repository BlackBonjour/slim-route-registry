<?php

declare(strict_types=1);

namespace BlackBonjour\SlimRouteRegistry;

use BlackBonjour\SlimRouteRegistry\Contract\ClassProviderInterface;
use Composer\ClassMapGenerator\ClassMapGenerator;
use RuntimeException;

final readonly class ComposerClassProvider implements ClassProviderInterface
{
    private ClassMapGenerator $classMapGenerator;

    public function __construct(ClassMapGenerator $classMapGenerator = new ClassMapGenerator())
    {
        $this->classMapGenerator = $classMapGenerator;
        $this->classMapGenerator->avoidDuplicateScans();
    }

    public function provideClasses(string $path): array
    {
        if (is_dir($path) === false) {
            throw Exception\DirectoryNotFoundExceptionClass::fromPath($path);
        }

        try {
            $this->classMapGenerator->scanPaths($path);
        } catch (RuntimeException $e) {
            throw Exception\ClassMapGeneratorExceptionClass::fromPath($path, $e);
        }

        $classes = [];

        foreach (array_keys($this->classMapGenerator->getClassMap()->getMap()) as $class) {
            $classes[] = $class;
        }

        return $classes;
    }
}
