<?php

declare(strict_types=1);

namespace App\Handler\Users;

use App\Model\UserModel;
use Oloma\Php\DataManagerInterface;
use Psr\Container\ContainerInterface;
use Psr\Http\Server\RequestHandlerInterface;

class FindOneByIdHandlerFactory
{
    public function __invoke(ContainerInterface $container): RequestHandlerInterface
    {
        $userModel = $container->get(UserModel::class);
        $dataManager = $container->get(DataManagerInterface::class);
        return new FindOneByIdHandler($userModel, $dataManager);
    }
}
