<?php

declare(strict_types=1);

namespace App\Handler\Auth;

use App\Model\AuthModel;
use App\Filter\Auth\ResetPasswordFilter;
use Psr\Container\ContainerInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Oloma\Php\Error\ErrorWrapperInterface as Error;
use Laminas\InputFilter\InputFilterPluginManager;

class ResetPasswordHandlerFactory
{
    public function __invoke(ContainerInterface $container): RequestHandlerInterface
    {
        $authModel = $container->get(AuthModel::class);
        $error = $container->get(Error::class);

        $pluginManager = $container->get(InputFilterPluginManager::class);
        $inputFilter   = $pluginManager->get(ResetPasswordFilter::class);

        return new ResetPasswordHandler($authModel, $inputFilter, $error);
    }
}
