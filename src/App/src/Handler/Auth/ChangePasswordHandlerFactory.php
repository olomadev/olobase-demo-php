<?php

declare(strict_types=1);

namespace App\Handler\Auth;

use App\Model\UserModel;
use App\Model\FailedLoginModel;
use App\Filter\Auth\ChangePasswordFilter;
use Psr\Container\ContainerInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Laminas\InputFilter\InputFilterPluginManager;
use Olobase\Mezzio\Error\ErrorWrapperInterface as Error;

class ChangePasswordHandlerFactory
{
    public function __invoke(ContainerInterface $container): RequestHandlerInterface
    {
        $userModel = $container->get(UserModel::class);
        $failedLoginModel = $container->get(FailedLoginModel::class);
        $error = $container->get(Error::class);

        $pluginManager = $container->get(InputFilterPluginManager::class);
        $inputFilter   = $pluginManager->get(ChangePasswordFilter::class);

        return new ChangePasswordHandler($userModel, $failedLoginModel, $inputFilter, $error);
    }
}
