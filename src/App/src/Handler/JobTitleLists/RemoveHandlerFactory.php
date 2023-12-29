<?php

declare(strict_types=1);

namespace App\Handler\JobTitleLists;

use Psr\Container\ContainerInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\SimpleCache\CacheInterface as SimpleCacheInterface;

class RemoveHandlerFactory
{
    public function __invoke(ContainerInterface $container): RequestHandlerInterface
    {
        $simpleCache = $container->get(SimpleCacheInterface::class);
        return new RemoveHandler($simpleCache);
    }
}
