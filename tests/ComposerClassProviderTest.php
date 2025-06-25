<?php

declare(strict_types=1);

namespace BlackBonjourTest\SlimRouteRegistry;

use BlackBonjour\SlimRouteRegistry\ComposerClassProvider;
use BlackBonjour\SlimRouteRegistry\Exception\ClassMapGeneratorExceptionClass;
use BlackBonjour\SlimRouteRegistry\Exception\DirectoryNotFoundExceptionClass;
use Composer\ClassMapGenerator\ClassMap;
use Composer\ClassMapGenerator\ClassMapGenerator;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Throwable;

final class ComposerClassProviderTest extends TestCase
{
    /**
     * Verifies that the constructor accepts a custom ClassMapGenerator instance.
     *
     * @throws Throwable
     */
    public function testConstructorWithCustomClassMapGenerator(): void
    {
        $classMapGenerator = $this->createMock(ClassMapGenerator::class);
        $classMapGenerator
            ->expects($this->once())
            ->method('avoidDuplicateScans');

        new ComposerClassProvider($classMapGenerator);
    }

    /**
     * Verifies that an exception is thrown when the ClassMapGenerator encounters an error while scanning a directory path.
     *
     * @throws Throwable
     */
    public function testProvideClassesWithClassMapGeneratorException(): void
    {
        $this->expectException(ClassMapGeneratorExceptionClass::class);
        $this->expectExceptionMessage('Class map generator failed for path: tests');

        $classMapGenerator = $this->createMock(ClassMapGenerator::class);
        $classMapGenerator
            ->expects($this->once())
            ->method('avoidDuplicateScans');

        $classMapGenerator
            ->expects($this->once())
            ->method('scanPaths')
            ->with('tests')
            ->willThrowException(new RuntimeException('Test exception'));

        $classProvider = new ComposerClassProvider($classMapGenerator);
        $classProvider->provideClasses('tests');
    }

    /**
     * Verifies that an exception is thrown when attempting to provide classes from an invalid directory path.
     *
     * @throws Throwable
     */
    public function testProvideClassesWithInvalidDirectory(): void
    {
        $this->expectException(DirectoryNotFoundExceptionClass::class);
        $this->expectExceptionMessage('Directory not found: invalid_directory');

        $classMapGenerator = $this->createMock(ClassMapGenerator::class);
        $classMapGenerator
            ->expects($this->once())
            ->method('avoidDuplicateScans');

        $classProvider = new ComposerClassProvider($classMapGenerator);
        $classProvider->provideClasses('invalid_directory');
    }

    /**
     * Verifies that provideClasses returns the correct list of classes for a valid directory path.
     *
     * @throws Throwable
     */
    public function testProvideClassesWithValidDirectory(): void
    {
        $classMap = $this->createMock(ClassMap::class);
        $classMap
            ->expects($this->once())
            ->method('getMap')
            ->willReturn(
                [
                    'TestClass1' => '/path/to/TestClass1.php',
                    'TestClass2' => '/path/to/TestClass2.php',
                ],
            );

        $classMapGenerator = $this->createMock(ClassMapGenerator::class);
        $classMapGenerator
            ->expects($this->once())
            ->method('avoidDuplicateScans');

        $classMapGenerator
            ->expects($this->once())
            ->method('scanPaths')
            ->with('tests');

        $classMapGenerator
            ->expects($this->once())
            ->method('getClassMap')
            ->willReturn($classMap);

        $classProvider = new ComposerClassProvider($classMapGenerator);
        $classes = $classProvider->provideClasses('tests');

        self::assertEquals(['TestClass1', 'TestClass2'], $classes);
    }
}
