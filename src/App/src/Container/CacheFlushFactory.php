<?php

declare(strict_types=1);

namespace App\Container;

use App\Utils\CacheFlush;
use Laminas\Cache\Storage\StorageInterface;
use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

class CacheFlushFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        return new CacheFlush;
    }
}
