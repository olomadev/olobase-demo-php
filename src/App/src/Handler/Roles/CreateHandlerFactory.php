<?php

declare(strict_types=1);

namespace App\Handler\Roles;

use App\Model\RoleModel;
use App\Filter\RoleSaveFilter;
use Oloma\Php\DataManagerInterface;
use Oloma\Php\Error\ErrorWrapperInterface as Error;
use Mezzio\Authentication\AuthenticationInterface;
use Psr\Container\ContainerInterface;
use Laminas\Db\Adapter\AdapterInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Laminas\InputFilter\InputFilterPluginManager;

class CreateHandlerFactory
{
    public function __invoke(ContainerInterface $container): RequestHandlerInterface
    {
        $roleModel = $container->get(RoleModel::class);
        $error = $container->get(Error::class);
        $dataManager = $container->get(DataManagerInterface::class);

        $pluginManager = $container->get(InputFilterPluginManager::class);
        $inputFilter   = $pluginManager->get(RoleSaveFilter::class);

        return new FindMeHandler($roleModel, $dataManager, $inputFilter, $error);
    }
}
