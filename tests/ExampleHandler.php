<?php

declare(strict_types=1);

namespace BlackBonjourTest\SlimRouteRegistry;

use BlackBonjour\SlimRouteRegistry\Redirect;
use BlackBonjour\SlimRouteRegistry\Route;

#[Redirect('/old', '/new')]
#[Route('GET', '/')]
final class ExampleHandler {}
