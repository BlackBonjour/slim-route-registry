<?php

declare(strict_types=1);

namespace BlackBonjour\SlimRouteRegistry;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE)]
final readonly class Redirect
{
    public function __construct(
        public string $from,
        public ?string $to = null,
        public int $status = 302,
    ) {}
}
