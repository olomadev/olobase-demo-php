<?php

declare(strict_types=1);

namespace App\Filter\Account;

use Psr\Container\ContainerInterface;
use Laminas\Db\Adapter\AdapterInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

class PasswordChangeFilterFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        return new PasswordChangeFilter($container->get(AdapterInterface::class));
    }
}
