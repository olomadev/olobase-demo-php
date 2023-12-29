<?php

declare(strict_types=1);

namespace App\Handler\JobTitleLists;

use Psr\Container\ContainerInterface;
use Laminas\Cache\Storage\StorageInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\SimpleCache\CacheInterface as SimpleCacheInterface;

class PreviewHandlerFactory
{
    public function __invoke(ContainerInterface $container): RequestHandlerInterface
    {
        $simpleCache = $container->get(SimpleCacheInterface::class);
        return new PreviewHandler($simpleCache);
    }
}
