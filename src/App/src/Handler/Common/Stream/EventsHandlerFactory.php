<?php

declare(strict_types=1);

namespace App\Handler\Common\Stream;

use Psr\Container\ContainerInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\SimpleCache\CacheInterface as SimpleCacheInterface;

class EventsHandlerFactory
{
    public function __invoke(ContainerInterface $container): RequestHandlerInterface
    {
        $simpleCache = $container->get(SimpleCacheInterface::class);
        return new EventsHandler($simpleCache);
    }
}
