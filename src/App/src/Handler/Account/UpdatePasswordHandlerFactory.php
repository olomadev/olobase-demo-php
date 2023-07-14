<?php

declare(strict_types=1);

namespace App\Handler\Account;

use App\Model\UserModel;
use App\Filter\PasswordUpdateFilter;
use Oloma\Php\Error\ErrorWrapperInterface as Error;
use Mezzio\Authentication\AuthenticationInterface;
use Psr\Container\ContainerInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Laminas\InputFilter\InputFilterPluginManager;

class UpdatePasswordHandlerFactory
{
    public function __invoke(ContainerInterface $container): RequestHandlerInterface
    {
        $userModel = $container->get(UserModel::class);
        $error = $container->get(Error::class);

        $pluginManager = $container->get(InputFilterPluginManager::class);
        $inputFilter   = $pluginManager->get(PasswordUpdateFilter::class);

        return new UpdatePasswordHandler($userModel, $inputFilter, $error);
    }
}
