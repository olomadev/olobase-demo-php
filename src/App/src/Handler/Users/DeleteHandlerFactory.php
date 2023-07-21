<?php

declare(strict_types=1);

namespace App\Handler\Users;

use App\Model\UserModel;
use App\Filter\UserDeleteFilter;
use Oloma\Php\Error\ErrorWrapperInterface as Error;
use Psr\Container\ContainerInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Laminas\InputFilter\InputFilterPluginManager;

class DeleteHandlerFactory
{
    public function __invoke(ContainerInterface $container): RequestHandlerInterface
    {
        $userModel = $container->get(UserModel::class);
        $error = $container->get(Error::class);

        $pluginManager = $container->get(InputFilterPluginManager::class);
        $inputFilter   = $pluginManager->get(UserDeleteFilter::class);

        return new CreateHandler($userModel, $inputFilter, $error);
    }
}
