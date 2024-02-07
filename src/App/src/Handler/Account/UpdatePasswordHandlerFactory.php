<?php

declare(strict_types=1);

namespace App\Handler\Account;

use App\Model\UserModel;
use App\Filter\Account\PasswordChangeFilter;
use Olobase\Mezzio\Error\ErrorWrapperInterface as Error;
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
        $inputFilter   = $pluginManager->get(PasswordChangeFilter::class);

        return new UpdatePasswordHandler($userModel, $inputFilter, $error);
    }
}
