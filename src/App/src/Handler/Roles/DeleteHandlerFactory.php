<?php

declare(strict_types=1);

namespace App\Handler\Roles;

use App\Model\RoleModel;
use App\Filter\Roles\DeleteFilter;
use Oloma\Mezzio\Error\ErrorWrapperInterface as Error;
use Psr\Container\ContainerInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Laminas\InputFilter\InputFilterPluginManager;

class DeleteHandlerFactory
{
    public function __invoke(ContainerInterface $container): RequestHandlerInterface
    {
        $roleModel = $container->get(RoleModel::class);
        $error = $container->get(Error::class);

        $pluginManager = $container->get(InputFilterPluginManager::class);
        $inputFilter   = $pluginManager->get(DeleteFilter::class);

        return new DeleteHandler($roleModel, $inputFilter, $error);
    }
}
