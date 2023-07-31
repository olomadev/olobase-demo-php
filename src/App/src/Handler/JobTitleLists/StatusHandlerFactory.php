<?php

declare(strict_types=1);

namespace App\Handler\JobTitleLists;

use Psr\Container\ContainerInterface;
use Laminas\Cache\Storage\StorageInterface;
use Psr\Http\Server\RequestHandlerInterface;

class StatusHandlerFactory
{
    public function __invoke(ContainerInterface $container): RequestHandlerInterface
    {
        $storage = $container->get(StorageInterface::class);
        return new StatusHandler($storage);
    }
}
