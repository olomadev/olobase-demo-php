<?php

declare(strict_types=1);

namespace App\Handler\Auth;

use Psr\Container\ContainerInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\SimpleCache\CacheInterface as SimpleCacheInterface;

class SessionUpdateHandlerFactory
{
    public function __invoke(ContainerInterface $container): RequestHandlerInterface
    {
        return new SessionUpdateHandler(
            $container->get('config'),
            $container->get(SimpleCacheInterface::class)
        );
    }
}
