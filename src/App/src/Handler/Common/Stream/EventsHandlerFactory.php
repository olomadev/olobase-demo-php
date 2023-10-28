<?php

declare(strict_types=1);

namespace App\Handler\Common\Stream;

use Laminas\Cache\Storage\StorageInterface;
use Psr\Container\ContainerInterface;
use Psr\Http\Server\RequestHandlerInterface;

class EventsHandlerFactory
{
    public function __invoke(ContainerInterface $container): RequestHandlerInterface
    {
        $cache = $container->get(StorageInterface::class);
        return new EventsHandler($cache);
    }
}
