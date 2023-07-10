<?php

declare(strict_types=1);

namespace App\Container;

use App\Model\PermissionModel;
use App\Authorization\Authorization;
use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

class AuthorizationFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        return new Authorization($container->get(PermissionModel::class));
    }
}
