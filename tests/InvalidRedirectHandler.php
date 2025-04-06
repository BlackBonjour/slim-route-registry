<?php

declare(strict_types=1);

namespace BlackBonjourTest\SlimRouteRegistry;

use BlackBonjour\SlimRouteRegistry\Redirect;

#[Redirect('/old')]
final class InvalidRedirectHandler {}
