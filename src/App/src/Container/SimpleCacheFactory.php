<?php

declare(strict_types=1);

namespace App\Container;

use Laminas\Cache\StorageFactory;
use Psr\Container\ContainerInterface;
use Laminas\Cache\Storage\StorageInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Laminas\Cache\Psr\SimpleCache\SimpleCacheDecorator;
/**
 * https://docs.laminas.dev/laminas-cache/psr16/
 */
class SimpleCacheFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $storage = $container->get(StorageInterface::class);
        return new SimpleCacheDecorator($storage);
    }
}
