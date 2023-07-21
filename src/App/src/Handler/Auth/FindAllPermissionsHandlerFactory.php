<?php

declare(strict_types=1);

namespace App\Handler\Auth;

use App\Model\AuthModel;
use Psr\Container\ContainerInterface;
use Psr\Http\Server\RequestHandlerInterface;

class FindAllPermissionsHandlerFactory
{
    public function __invoke(ContainerInterface $container): RequestHandlerInterface
    {
        $authModel = $container->get(AuthModel::class);
        return new FindAllPermissionsHandler($authModel);
    }
}
