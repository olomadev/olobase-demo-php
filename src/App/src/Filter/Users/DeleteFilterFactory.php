<?php

declare(strict_types=1);

namespace App\Filter\Users;

use Psr\Container\ContainerInterface;
use Laminas\Db\Adapter\AdapterInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

class DeleteFilterFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        return new DeleteFilter(
            $container->get(AdapterInterface::class)
        );
    }
}
