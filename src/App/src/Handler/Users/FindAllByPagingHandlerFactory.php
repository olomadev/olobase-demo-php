<?php

declare(strict_types=1);

namespace App\Handler\Users;

use App\Model\UserModel;
use Psr\Container\ContainerInterface;
use Psr\Http\Server\RequestHandlerInterface;

class FindAllByPagingHandlerFactory
{
    public function __invoke(ContainerInterface $container): RequestHandlerInterface
    {
        $userModel = $container->get(UserModel::class);
        return new FindAllByPagingHandler($userModel);
    }
}
