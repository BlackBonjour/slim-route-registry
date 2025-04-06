<?php

declare(strict_types=1);

namespace BlackBonjour\SlimRouteRegistry;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE)]
final readonly class Route
{
    /** @var array<string> */
    public array $methods;

    /**
     * @param array<string>|string $methods
     * @param array<Redirect>      $redirects
     */
    public function __construct(
        array|string $methods,
        public string $path,
        public ?string $name = null,
        public array $redirects = [],
    ) {
        $this->methods = is_string($methods) ? [$methods] : $methods;
    }
}
