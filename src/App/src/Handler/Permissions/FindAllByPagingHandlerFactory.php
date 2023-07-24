<?php

declare(strict_types=1);

namespace App\Handler\Permissions;

use App\Model\PermissionModel;
use Psr\Container\ContainerInterface;
use Psr\Http\Server\RequestHandlerInterface;

class FindAllByPagingHandlerFactory
{
    public function __invoke(ContainerInterface $container): RequestHandlerInterface
    {
        $roleModel = $container->get(PermissionModel::class);
        return new FindAllByPagingHandler($roleModel);
    }
}
