<?php

declare(strict_types=1);

namespace BlackBonjour\SlimRouteRegistry\Exception;

use BlackBonjour\SlimRouteRegistry\Contract\Exception\RouteRegistryExceptionInterface;
use BlackBonjour\SlimRouteRegistry\Redirect;
use InvalidArgumentException;

final class RedirectExceptionRoute extends InvalidArgumentException implements RouteRegistryExceptionInterface
{
    public static function fromRedirect(Redirect $redirect): self
    {
        return new self(
            sprintf(
                'The redirect attribute for "%s" requires a "to" parameter unless it is attached to a route attribute.',
                $redirect->from,
            ),
        );
    }
}
