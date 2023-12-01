<?php

declare(strict_types=1);

namespace App\Filter\Auth;

use Psr\Container\ContainerInterface;
use Psr\SimpleCache\CacheInterface as SimpleCacheInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

class ChangePasswordFilterFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        return new ChangePasswordFilter(
            $container->get(SimpleCacheInterface::class)
        );
    }
}
