<?php

declare(strict_types=1);

namespace App\Handler\Users;

use App\Model\UserModel;
use App\Filter\Users\SaveFilter;
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
        $userModel = $container->get(UserModel::class);
        $error = $container->get(Error::class);
        $dataManager = $container->get(DataManagerInterface::class);

        $pluginManager = $container->get(InputFilterPluginManager::class);
        $inputFilter   = $pluginManager->get(SaveFilter::class);

        return new CreateHandler($userModel, $dataManager, $inputFilter, $error);
    }
}
