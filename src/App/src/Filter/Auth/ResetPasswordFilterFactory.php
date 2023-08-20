<?php

declare(strict_types=1);

namespace App\Filter\Auth;

use Psr\Container\ContainerInterface;
use Laminas\Db\Adapter\AdapterInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

class ResetPasswordFilterFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        return new ResetPasswordFilter(
            $container->get(AdapterInterface::class)
        );
    }
}
