<?php

declare(strict_types=1);

namespace App\Handler\Roles;

use App\Model\RoleModel;
use Psr\Container\ContainerInterface;
use Psr\Http\Server\RequestHandlerInterface;

class FindOneByIdHandlerFactory
{
    public function __invoke(ContainerInterface $container): RequestHandlerInterface
    {
        $roleModel = $container->get(RoleModel::class);
        return new FindOneByIdHandler($roleModel);
    }
}
