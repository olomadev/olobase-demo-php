<?php

declare(strict_types=1);

namespace App\Handler\Auth;

use Psr\Container\ContainerInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Laminas\Cache\Storage\StorageInterface;

class SessionUpdateHandlerFactory
{
    public function __invoke(ContainerInterface $container): RequestHandlerInterface
    {
        return new SessionUpdateHandler(
            $container->get('config'),
            $container->get(StorageInterface::class)
        );
    }
}
